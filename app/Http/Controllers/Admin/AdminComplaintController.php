<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Complaints\UpdateComplaintStatusRequest;
use App\Models\Complaint;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Resources\ComplaintResource;

class AdminComplaintController extends Controller
{
    use ApiResponse;

    /**
     * قائمة الشكاوى مع فلترة حسب:
     * - status (pending | in_progress | resolved)
     * - user_id (اختياري)
     * - search (يبحث في subject / message)
     */
    public function index(Request $request)
    {
        $query = Complaint::query()->with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');

            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $complaints = $query->with('user.role')->orderByDesc('created_at')->paginate(20);
        return $this->success(ComplaintResource::collection($complaints), 'Complaints list retrieved successfully.');
    }

    /**
     * عرض شكوى واحدة مع بيانات المستخدم الذي أرسلها.
     */
    public function show(Complaint $complaint)
    {
        $complaint->load('user.role');
        return $this->success(new ComplaintResource($complaint), 'Complaint details retrieved successfully.');
    }

    /**
     * تغيير حالة الشكوى + إضافة / تعديل رد الأدمن.
     */
    public function updateStatus(UpdateComplaintStatusRequest $request, Complaint $complaint)
    {
        $validated = $request->validated();

        $complaint->status         = $validated['status'];
        $complaint->admin_response = $validated['admin_response'] ?? $complaint->admin_response;

        $complaint->save();

        $complaint->load('user.role');
        return $this->success(new ComplaintResource($complaint), 'Complaint status has been updated.');
    }
}
