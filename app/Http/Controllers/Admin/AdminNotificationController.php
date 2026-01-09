<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Notifications\BroadcastNotificationRequest;
use App\Http\Requests\Admin\Notifications\CreateNotificationForUserRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Carbon;

class AdminNotificationController extends Controller
{
    use ApiResponse;

    public function createNotificationForUser(CreateNotificationForUserRequest $request, User $user)
    {
        $data = $request->validated();

        $notification = Notification::create([
            'user_id'      => $user->id,
            'title'        => $data['title'],
            'body'         => $data['body'],
            'type'         => $data['type'] ?? 'system',
            'scheduled_at' => isset($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null,
            'is_read'      => false,
        ]);

        return $this->success(new NotificationResource($notification), 'Notification created successfully.', 201);
    }

    public function broadcastNotification(BroadcastNotificationRequest $request)
    {
        $data = $request->validated();

        $query = User::query();

        if (!empty($data['only_active'])) {
            $query->where('is_active', true);
        }

        if (!empty($data['role'])) {
            $role = Role::where('role_name', $data['role'])->first();
            if ($role) {
                $query->where('role_id', $role->id);
            } else {
                // role غير موجود -> لا نرسل لحدا
                return $this->success(['count' => 0], 'Broadcast finished (role not found).');
            }
        }

        $now = now();
        $scheduledAt = isset($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null;
        $type = $data['type'] ?? 'system';

        $count = 0;

        $query->select('id')->chunkById(500, function ($users) use (&$count, $data, $type, $scheduledAt, $now) {
            $rows = [];

            foreach ($users as $user) {
                $rows[] = [
                    'user_id'      => $user->id,
                    'title'        => $data['title'],
                    'body'         => $data['body'],
                    'type'         => $type,
                    'scheduled_at' => $scheduledAt,
                    'is_read'      => false,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }

            if (!empty($rows)) {
                Notification::query()->insert($rows);
                $count += count($rows);
            }
        });

        return $this->success(['count' => $count], 'Broadcast notification created successfully.');
    }
}
