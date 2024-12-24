<?php

namespace App\Http\Middleware;

use App\Http\Response\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $requiredRole): Response
    {
        if (auth()->guard()->check()) {
            if (auth()->guard()->user()->role === 'admin') {
                $role = auth()->guard()->user()['admin']['role'];
                if ($role === 'root' || $role === $requiredRole || ($requiredRole == 'challenge&mentor' && ($role === 'challenge' || $role === 'mentor')))
                    return $next($request);
                else
                    return ApiResponse::FORBIDDEN('permission denied');
            } else
                return ApiResponse::FORBIDDEN('permission denied');
        } else {
            return ApiResponse::UNAUTHORIZED();
        }
    }
}
