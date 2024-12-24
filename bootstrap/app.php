<?php

use Illuminate\Http\Request;
use App\Http\Response\ApiResponse;
use App\Http\Exception\AuthException;
use Illuminate\Foundation\Application;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Exception\HandlingExceptions;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\HandleApiExceptions;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Illuminate\Database\Eloquent\ModelNotFoundException;


return Application::configure(basePath: dirname(__DIR__))
    // ->loadEnvironmentFrom('.env')
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => AuthMiddleware::class,
            'admin' => AdminMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 401);
            }
        });
        $exceptions->render(function (\InvalidArgumentException $e, Request $request) {
            return response()->json(['message' => $e->getMessage()], 400);
        });
        $exceptions->renderable(function (Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            return ApiResponse::NOT_FOUND();
        });

        $exceptions->render(function (JWTException $e, Request $request) {
            return response()->json(['message' => 'Unauthorized'], $e->getCode());
        });
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            return ApiResponse::UNAUTHORIZED();
        });
        $exceptions->render(function (Illuminate\Database\QueryException $e, Request $request) {
            return ApiResponse::ERROR(($e->getMessage()));
        });
        $exceptions->render(function (\Exception $e, Request $request) {
            return ApiResponse::ERROR([$e->getMessage(), get_class($e)]);
        });
    })->create();
