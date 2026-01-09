<?php

namespace App\Http\Controllers;

use App\Http\Requests\Plants\StorePlantLogRequest;
use App\Http\Requests\Plants\StorePlantRequest;
use App\Http\Requests\Plants\UpdatePlantRequest;
use App\Http\Resources\PlantLogResource;
use App\Http\Resources\PlantResource;
use App\Models\Plant;
use App\Models\PlantLog;
use App\Services\PlantAiService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class PlantController extends Controller
{
    use ApiResponse;
    // عرض قائمة نباتات المستخدم الحالي

    public function index(Request $request)
    {
        $plants = Plant::query()
            ->where('user_id', $request->user()->id)
            ->with('plantReference')
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->success(PlantResource::collection($plants), 'Plants retrieved successfully.');
    }

    //عرض تفاصيل نبتة واحدة
    public function show(Request $request, Plant $plant)
    {
        $this->authorize('view', $plant);

        $plant->load('plantReference');

        return $this->success(new PlantResource($plant), 'Plant retrieved successfully.');
    }

    //إنشاء نبتة جديدة للمستخدم الحالي
    //أول مرة يضيف النبتة

    // public function store(StorePlantRequest $request)
    // {
    //     $imagePath = null;
    //
    //     if ($request->hasFile('image')) {
    //         // تأكد أنك عامل: php artisan storage:link
    //         $imagePath = $request->file('image')->store('plants', 'public');
    //     }
    //
    //     $plant = Plant::create([
    //         'user_id'                => $request->user()->id,
    //         'plant_reference_id'     => $request->plant_reference_id,
    //         'custom_name'            => $request->custom_name,
    //         'image'                  => $imagePath,
    //         'watering_frequency_days' => $request->watering_frequency_days,
    //         'next_watering_date'     => $request->next_watering_date,
    //         'notes'                  => $request->notes,
    //     ]);
    //
    //     $plant->load('plantReference');
    //
    //     return $this->success(new PlantResource($plant), 'Plant has been created.', 201);
    // }

    public function store(StorePlantRequest $request, PlantAiService $plantAiService)
    {
        $user = $request->user();

        // 1) Save image first (file system) then save plant inside DB transaction.
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('plants', 'public');
        }

        try {
            $plant = DB::transaction(function () use ($request, $user, $imagePath) {
                return Plant::create([
                    'user_id'                 => $user->id,
                    'plant_reference_id'      => $request->plant_reference_id,
                    'custom_name'             => $request->custom_name,
                    'image'                   => $imagePath,
                    'watering_frequency_days' => $request->watering_frequency_days,
                    'next_watering_date'      => $request->next_watering_date,
                    'notes'                   => $request->notes,
                ]);
            });
        } catch (\Throwable $e) {
            // DB failed => cleanup stored file to avoid orphaned uploads.
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $e;
        }

        // 2) Call AI and store ai_results (do not break plant creation if AI fails)
        if ($imagePath) {
            $absolutePath = Storage::disk('public')->path($imagePath);
            $ai = $plantAiService->predictFromPath($absolutePath, basename($imagePath));

            if ($ai) {
                DB::transaction(function () use ($plant, $ai) {
                    $plant->aiResults()->create([
                        'plant_name' => $ai['plant_name'] ?? null,
                        'confidence' => $ai['confidence'] ?? null,
                        'recommendation' => $ai['recommendation'] ?? null,
                        'raw_response' => $ai['raw_response'] ?? null,
                    ]);
                });
            }
        }

        $plant->load(['plantReference', 'latestAiResult']);

        return $this->success(new PlantResource($plant), 'Plant has been created.', 201);
    }

    //تعديل نبتة موجودة
    public function update(UpdatePlantRequest $request, Plant $plant)
    {

        $this->authorize('update', $plant); // للتعديل/إضافة log

        if ($request->file('image') && $request->file('image')->isValid()) {
            $oldPath = $plant->getRawOriginal('image');

            if ($oldPath && ! Plant::where('id', '!=', $plant->id)->where('image', $oldPath)->exists()) {
                Storage::disk('public')->delete($oldPath); // delete() already checks existence
            }

            $newPath = $request->file('image')->store('plants', 'public');
            $plant->image = $newPath;
        }


        if ($request->has('plant_reference_id')) {
            $plant->plant_reference_id = $request->plant_reference_id;
        }

        if ($request->has('custom_name')) {
            $plant->custom_name = $request->custom_name;
        }

        if ($request->has('watering_frequency_days')) {
            $plant->watering_frequency_days = $request->watering_frequency_days;
        }

        if ($request->has('next_watering_date')) {
            $plant->next_watering_date = $request->next_watering_date;
        }

        if ($request->has('notes')) {
            $plant->notes = $request->notes;
        }

        $plant->save();
        $plant->load('plantReference');

        return $this->success(new PlantResource($plant), 'Plant has been updated.');
    }



    //حذف نبتة
    public function destroy(Request $request, Plant $plant)
    {
        $this->authorize('delete', $plant); // للحذف

        DB::transaction(function () use ($plant) {
            // حذف اللوجات أولاً (حتى لو ما عندك FK cascade)
            $plant->logs()->delete();

            $oldPath = $plant->getRawOriginal('image');

            if ($oldPath && ! Plant::where('id', '!=', $plant->id)->where('image', $oldPath)->exists()) {
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $plant->delete();
        });

        return $this->success(null, 'Plant has been deleted.');
    }

    //نشاء Log جديد للنبتة (سقاية، تسميد، ... إلخ)
    //  مثال action_type:
    //- watering
    //- fertilizing
    //- pruning
    //- ai_analysis

    public function storeLog(StorePlantLogRequest $request, Plant $plant)
    {
        $this->authorize('update', $plant); // للتعديل/إضافة log

        $loggedAt = $request->logged_at ? Carbon::parse($request->logged_at) : now();

        /** @var PlantLog $log */
        $log = $plant->logs()->create([
            'action_type' => $request->action_type,
            'details'     => $request->details,
            'logged_at'   => $loggedAt,
        ]);

        // D logic
        if ($log->action_type === 'watering' && $plant->watering_frequency_days) {
            $plant->next_watering_date = $loggedAt->copy()->addDays((int) $plant->watering_frequency_days);
            $plant->save();
        }

        return $this->success(new PlantLogResource($log), 'Log has been created successfully.', 201);
    }

    public function indexLogs(Request $request, Plant $plant)
    {
        $this->authorize('view', $plant);

        $logs = $plant->logs()
            ->orderByDesc('logged_at')
            ->paginate(50);

        return $this->success(PlantLogResource::collection($logs), 'Plant logs retrieved successfully.');
    }


    public function updateLog(Request $request, Plant $plant, PlantLog $log)
    {
        $this->authorize('update', $plant);
        $this->ensureLogBelongsToPlant($plant, $log);

        $validated = $request->validate([
            'action_type' => 'nullable|string|max:50',
            'details' => 'nullable|string',
            'logged_at' => 'nullable|date',
        ]);

        if (isset($validated['action_type'])) {
            $log->action_type = $validated['action_type'];
        }

        if (array_key_exists('details', $validated)) {
            $log->details = $validated['details'];
        }

        if (isset($validated['logged_at'])) {
            $log->logged_at = $validated['logged_at'];
        }

        $log->save();

        return response()->json([
            'status' => true,
            'message' => 'The event has been updated.',
            'data' => $log,
        ]);
    }

    public function destroyLog(Request $request, Plant $plant, PlantLog $log)
    {
        $this->authorize('delete', $plant);
        $this->ensureLogBelongsToPlant($plant, $log);

        if ($log->plant_id !== $plant->id) {
            return $this->error('This log does not belong to the specified plant.', 422);
        }

        $log->delete();

        return $this->success(null, 'Log has been deleted.');
    }

    private function ensureLogBelongsToPlant(Plant $plant, PlantLog $log): void
    {
        if ($log->plant_id !== $plant->id) {
            throw (new ModelNotFoundException())->setModel(PlantLog::class, [$log->id]);
        }
    }
}
