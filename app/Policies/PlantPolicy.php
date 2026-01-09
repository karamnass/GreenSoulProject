<?php

namespace App\Policies;

use App\Models\Plant;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PlantPolicy
{
    public function view(User $user, Plant $plant): Response
    {
        return ($user->id === $plant->user_id || $user->hasRole('admin'))
            ? Response::allow()
            : Response::deny('You are not allowed to access this plant.');
    }

    public function update(User $user, Plant $plant): Response
    {
        return ($user->id === $plant->user_id || $user->hasRole('admin'))
            ? Response::allow()
            : Response::deny('You are not allowed to update this plant.');
    }

    public function delete(User $user, Plant $plant): Response
    {
        return ($user->id === $plant->user_id || $user->hasRole('admin'))
            ? Response::allow()
            : Response::deny('You are not allowed to delete this plant.');
    }
}
