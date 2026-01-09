<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ComplaintPolicy
{
    public function view(User $user, Complaint $complaint): Response
    {
        return ($user->id === $complaint->user_id || $user->hasRole('admin'))
            ? Response::allow()
            : Response::deny('You are not allowed to access this complaint.');
    }

    public function update(User $user, Complaint $complaint): Response
    {
        return ($user->id === $complaint->user_id || $user->hasRole('admin'))
            ? Response::allow()
            : Response::deny('You are not allowed to update this complaint.');
    }

    public function delete(User $user, Complaint $complaint): Response
    {
        return ($user->id === $complaint->user_id || $user->hasRole('admin'))
            ? Response::allow()
            : Response::deny('You are not allowed to delete this complaint.');
    }
}
