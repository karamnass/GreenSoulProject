<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{

    use ApiResponse;

    //كل إشعارات المستخدم (مع pagination)
    public function index(Request $request)
    {
        $notifications = Notification::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->success(
            NotificationResource::collection($notifications),
            'Notifications retrieved successfully.'
        );
    }

    //إشعارات غير مقروءة فقط
    public function unread(Request $request)
    {
        $notifications = Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->success(
            NotificationResource::collection($notifications),
            'Unread notifications retrieved successfully.'
        );
    }

    //إشعارات اليوم فقط
    public function today(Request $request)
    {
        $today = Carbon::today();

        $notifications = Notification::query()
            ->where('user_id', $request->user()->id)
            ->whereDate('created_at', $today)
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->success(
            NotificationResource::collection($notifications),
            'Today notifications retrieved successfully.'
        );
    }

    //وضع إشارة مقروء
    public function markAsRead(Request $request, Notification $notification)
    {
        $this->authorize('update', $notification);


        $notification->is_read = true;
        $notification->save();

        return $this->success(
            new NotificationResource($notification),
            'Notification marked as read.'
        );
    }

    // 3) حذف الإشعار
    public function destroy(Request $request, Notification $notification)
    {
        $this->authorize('delete', $notification);

        $notification->delete();

        return $this->success(null, 'Notification deleted successfully.');
    }
}
