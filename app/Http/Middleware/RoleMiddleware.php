<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    //يتحقّق من أن المستخدم لديه واحد من الأدوار المسموحة.
    //   * يمكن تمرير role واحد أو أكثر مفصولة بـ | مثل: role:admin أو role:admin|user

    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role) {
            return response()->json([
                'status'  => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        $allowedRoles = explode('|', $roles); // مثال: ['admin','user']

        if (! in_array($user->role->role_name, $allowedRoles, true)) {
            return response()->json([
                'status'  => false,
                'message' => 'Forbidden.',
            ], 403);
        }

        return $next($request);
    }
}
