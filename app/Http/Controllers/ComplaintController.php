<?php

namespace App\Http\Controllers;


use App\Http\Requests\Complaints\StoreComplaintRequest;
use App\Http\Requests\Complaints\UpdateComplaintRequest;
use App\Models\Complaint;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Resources\ComplaintResource;


class ComplaintController extends Controller
{
    use ApiResponse;
    // قائمة الشكاوى للمستخدم الحالي
    public function index(Request $request)
    {
        $complaints = $request->user()
            ->complaints()
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->success(ComplaintResource::collection($complaints), 'Complaints retrieved successfully.');
    }

    // إنشاء شكوى جديدة
    public function store(StoreComplaintRequest $request)
    {
        $complaint = Complaint::create([
            'user_id'  => $request->user()->id,
            'subject'  => $request->subject,
            'message'  => $request->message,
            'status'   => 'pending',
        ]);

        return $this->success(new ComplaintResource($complaint), 'The complaint has been created.', 201);
    }

    // عرض شكوى واحدة
    public function show(Request $request, Complaint $complaint)
    {
        $this->authorize('view', $complaint);
        return $this->success(new ComplaintResource($complaint), 'Complaint retrieved successfully.');
    }

    // تعديل الشكوى (بس ازا اكنت pending)
    public function update(UpdateComplaintRequest $request, Complaint $complaint)
    {
        $this->authorize('update', $complaint);

        if ($complaint->status !== 'pending') {
            return $this->error('You cannot update a complaint that is already being processed.', 403);
        }

        if ($request->filled('subject')) {
            $complaint->subject = $request->subject;
        }

        if ($request->filled('message')) {
            $complaint->message = $request->message;
        }

        $complaint->save();

        return $this->success(new ComplaintResource($complaint), 'The complaint has been updated.');
    }

    // حذف الشكوى ( بس ازا pending)
    public function destroy(Request $request, Complaint $complaint)
    {
        $this->authorize('delete', $complaint);

        if ($complaint->status !== 'pending') {
            return $this->error('You cannot delete a complaint that is already being processed.', 403);
        }

        $complaint->delete();

        return $this->success('The complaint has been deleted.');
    }
}
