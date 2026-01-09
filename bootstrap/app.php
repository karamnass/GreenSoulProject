<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'   => \App\Http\Middleware\RoleMiddleware::class,
            'active' => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $isApiRequest = static function (\Illuminate\Http\Request $request): bool {
            return $request->is('api/*') || $request->expectsJson();
        };

        $jsonError = static function (string $message, int $code, $errors = null) {
            return response()->json([
                'status'  => false,
                'message' => $message,
                'errors'  => $errors,
            ], $code);
        };

        // 422 Validation
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, \Illuminate\Http\Request $request) use ($isApiRequest, $jsonError) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return $jsonError('Validation error.', 422, $e->errors());
        });

        // 401 Unauthenticated
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) use ($isApiRequest, $jsonError) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return $jsonError('Unauthenticated.', 401);
        });

        // 403 Forbidden
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, \Illuminate\Http\Request $request) use ($isApiRequest, $jsonError) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return $jsonError($e->getMessage() ?: 'Forbidden.', 403);
        });

        // 404 Model not found (Route model binding)
        $exceptions->render(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, \Illuminate\Http\Request $request) use ($isApiRequest, $jsonError) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return $jsonError('Resource not found.', 404);
        });

        // 404 Route not found
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) use ($isApiRequest, $jsonError) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return $jsonError('Endpoint not found.', 404);
        });

        // 405 Method not allowed
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, \Illuminate\Http\Request $request) use ($isApiRequest, $jsonError) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return $jsonError('Method not allowed.', 405);
        });

        // 429 Too many requests (لو فعلت throttling)
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException $e, \Illuminate\Http\Request $request) use ($isApiRequest, $jsonError) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return $jsonError('Too many requests.', 429);
        });

        // 500 Fallback (بروڈكشن ما نفضح تفاصيل)
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) use ($isApiRequest, $jsonError) {
            if (! $isApiRequest($request)) {
                return null;
            }

            $message = config('app.debug')
                ? $e->getMessage()
                : 'Server error.';

            return $jsonError($message, 500);
        });
    })->create();
