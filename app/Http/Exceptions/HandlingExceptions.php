<?php
namespace App\Http\Exception;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;

class HandlingExceptions extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        return response()->json([
            'error' => true,
            'message' => $exception->getMessage(),
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}