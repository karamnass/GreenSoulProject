<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Users\ToggleUserActiveRequest;
use App\Http\Requests\Admin\Users\UpdateUserRequest;
use App\Http\Requests\Admin\Users\UpdateUserRoleRequest;
use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class AdminUserController extends Controller
{
    /**
     * عرض قائمة المستخدمين مع فلترة بسيطة:
     * - ?role=user أو ?role=admin
     * - ?is_active=1 أو 0
     * - ?search=karam (يبحث في name و phone)
     */
    use ApiResponse;

    public function index(Request $request)
    {
        $query = User::query()->with('role');

        if ($request->filled('role')) {
            $roleName = $request->get('role');
            $query->whereHas('role', function ($q) use ($roleName) {
                $q->where('role_name', $roleName);
            });
        }

        if ($request->has('is_active')) {
            $isActive = filter_var(
                $request->get('is_active'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            if (! is_null($isActive)) {
                $query->where('is_active', $isActive);
            }
        }

        if ($request->filled('search')) {
            $search = $request->get('search');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->success(UserResource::collection($users), 'Users list retrieved successfully.');
    }

    /**
     * عرض تفاصيل مستخدم واحد.
     */
    public function show(User $user)
    {
        $user->load('role');
        return $this->success(new UserResource($user), 'User details retrieved successfully.');
    }

    /**
     * تعديل بيانات بسيطة للمستخدم (الاسم حالياً).
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if (array_key_exists('name', $validated)) {
            $user->name = $validated['name'];
        }

        $user->save();

        $user->load('role');
        return $this->success(new UserResource($user), 'User has been updated.');
    }

    /**
     * تغيير دور المستخدم (user / admin).
     */
    public function updateRole(UpdateUserRoleRequest $request, User $user)
    {
        $validated = $request->validated();

        $role = Role::where('role_name', $validated['role'])->first();

        $user->role_id = $role->id;
        $user->save();

        $user->load('role');
        return $this->success(new UserResource($user), 'User role has been updated.');
    }

    /**
     * تفعيل/تعطيل المستخدم.
     * يمكن إرسال is_active=true/false في الـ body.
     */
    public function toggleActive(ToggleUserActiveRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->is_active = $validated['is_active'];
        $user->save();

        $user->load('role');
        return $this->success(new UserResource($user), 'User active status has been updated.');
    }
}
