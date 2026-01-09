<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tips\StoreTipRequest;
use App\Http\Requests\Admin\Tips\UpdateTipRequest;
use App\Models\Tip;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\TipResource;

class AdminTipController extends Controller
{
    use ApiResponse;

    /**
     * قائمة النصائح مع إمكانية البحث.
     * فلاتر اختيارية:
     * - ?search=word (يبحث في title و content)
     */
    public function index(Request $request)
    {
        $query = Tip::query();

        if ($request->filled('search')) {
            $search = $request->get('search');

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $tips = $query
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->success(TipResource::collection($tips), 'Tips list retrieved successfully.');
    }

    /**
     * عرض نصيحة واحدة (مع الصورة كـ URL).
     */
    public function show(Tip $tip)
    {
        return $this->success(new TipResource($tip), 'Tip details retrieved successfully.');
    }

    /**
     * إنشاء نصيحة جديدة (عنوان + محتوى + صورة اختيارية).
     * الجسم يكون form-data:
     * - title: text
     * - content: text
     * - image: file (اختياري)
     */
    public function store(StoreTipRequest $request)
    {
        $validated = $request->validated();

        $imagePath = null;

        if ($request->hasFile('image')) {
            // تخزين الصورة على قرص public داخل مجلد tips
            // تأكد أنك نفّذت: php artisan storage:link
            $imagePath = $request->file('image')->store('tips', 'public');
        }

        $tip = Tip::create([
            'title'   => $validated['title'],
            'content' => $validated['content'],
            'image'   => $imagePath, // سيُرجع كـ URL عبر الـ accessor في الموديل
        ]);

       return $this->success(new TipResource($tip), 'The tip has been added.', 201);
       return $this->success(new TipResource($tip), 'The tip has been updated.');

    }

    /**
     * تعديل نصيحة موجودة.
     * الجسم form-data:
     * - title: (اختياري)
     * - content: (اختياري)
     * - image: file (اختياري)
     */
    public function update(UpdateTipRequest $request, Tip $tip)
    {
        $validated = $request->validated();

        // التعامل مع الصورة لو أُرسلت صورة جديدة
        if ($request->hasFile('image')) {
            // المسار القديم الخام (بدون URL)
            $oldPath = $tip->getRawOriginal('image'); // أو $tip->image_path لو حاب

            // لا نحذف الصورة من الـ Storage إذا كانت مستخدمة في Tip أخرى
            if ($oldPath && ! Tip::where('id', '!=', $tip->id)->where('image', $oldPath)->exists()) {
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            $newPath   = $request->file('image')->store('tips', 'public');
            $tip->image = $newPath;
        }

        if (array_key_exists('title', $validated)) {
            $tip->title = $validated['title'];
        }

        if (array_key_exists('content', $validated)) {
            $tip->content = $validated['content'];
        }

        $tip->save();

       return $this->success(new TipResource($tip), 'The tip has been added.', 201);
       return $this->success(new TipResource($tip), 'The tip has been updated.');

    }

    /**
     * حذف نصيحة + حذف الصورة من الـ Storage إن لم تكن مشتركة.
     */
    public function destroy(Tip $tip)
    {
        $oldPath = $tip->getRawOriginal('image');

        if ($oldPath && ! Tip::where('id', '!=', $tip->id)->where('image', $oldPath)->exists()) {
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $tip->delete();

        return $this->success('The tip has been deleted.');
    }
}
