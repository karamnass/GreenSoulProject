<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlantReferences\StorePlantReferenceRequest;
use App\Http\Requests\Admin\PlantReferences\UpdatePlantReferenceRequest;
use App\Models\PlantReference;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PlantReferenceResource;

class PlantReferenceController extends Controller
{
    use ApiResponse;

    /**
     * قائمة الـ Plant References مع بحث اختياري.
     * ?search=word → يبحث في description
     */
    public function index(Request $request)
    {
        $query = PlantReference::query();

        if ($request->filled('search')) {
            $search = $request->get('search');

            $query->where('description', 'like', "%{$search}%");
        }

        $references = $query
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->success(PlantReferenceResource::collection($references), 'Plant references list retrieved successfully.');
    }

    /**
     * عرض Reference واحدة.
     */
    public function show(PlantReference $plantReference)
    {
        return $this->success(new PlantReferenceResource($plantReference), 'Plant reference details retrieved successfully.');
    }

    /**
     * إنشاء Plant Reference جديدة.
     * body: form-data
     * - description (required, text)
     * - image (optional, file)
     */
    public function store(StorePlantReferenceRequest $request)
    {
        $validated = $request->validated();

        $imagePath = null;

        if ($request->hasFile('image')) {
            // تخزين الصورة في قرص public داخل مجلد plant_references
            $imagePath = $request->file('image')->store('plant_references', 'public');
        }

        $reference = PlantReference::create([
            'description' => $validated['description'],
            'image'       => $imagePath,
        ]);

        return $this->success(new PlantReferenceResource($reference), 'The plant reference has been created.', 201);
    }

    /**
     * تعديل Plant Reference.
     * body: form-data
     * - description (optional)
     * - image (optional, file)
     */
    public function update(UpdatePlantReferenceRequest $request, PlantReference $plantReference)
    {
        $validated = $request->validated();

        // تحديث الصورة إن أرسلنا صورة جديدة
        if ($request->hasFile('image')) {
            $oldPath = $plantReference->getRawOriginal('image');

            // لا نحذف الصورة إذا كانت مستخدمة في Reference أخرى (نادر، لكن احتياط)
            if ($oldPath && ! PlantReference::where('id', '!=', $plantReference->id)->where('image', $oldPath)->exists()) {
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $newPath = $request->file('image')->store('plant_references', 'public');
            $plantReference->image = $newPath;
        }

        if (array_key_exists('description', $validated)) {
            $plantReference->description = $validated['description'];
        }

        $plantReference->save();

        return $this->success(new PlantReferenceResource($plantReference), 'The plant reference has been updated.');
    }

    /**
     * حذف Plant Reference.
     * ملاحظة: لو عندك Plants مرتبطة بها، يفضّل منع الحذف إلا بعد التأكد.
     */
    public function destroy(PlantReference $plantReference)
    {
        // اختيارياً: منع الحذف لو مرتبطة بنباتات
        if ($plantReference->plants()->exists()) {
            return $this->error('Cannot delete a reference that is used by plants.', null, 422);
        }

        $oldPath = $plantReference->getRawOriginal('image');

        if ($oldPath && ! PlantReference::where('id', '!=', $plantReference->id)->where('image', $oldPath)->exists()) {
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $plantReference->delete();

        return $this->success('The plant reference has been deleted.');
    }
}
