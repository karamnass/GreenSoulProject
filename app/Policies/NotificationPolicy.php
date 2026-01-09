<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NotificationPolicy
{
    public function view(User $user, Notification $notification): Response
    {
        return ($user->id === $notification->user_id)
            ? Response::allow()
            : Response::deny('You are not allowed to access this notification.');
    }

    public function update(User $user, Notification $notification): Response
    {
        return ($user->id === $notification->user_id)
            ? Response::allow()
            : Response::deny('You are not allowed to update this notification.');
    }

    public function delete(User $user, Notification $notification): Response
    {
        return ($user->id === $notification->user_id)
            ? Response::allow()
            : Response::deny('You are not allowed to delete this notification.');
    }
}
