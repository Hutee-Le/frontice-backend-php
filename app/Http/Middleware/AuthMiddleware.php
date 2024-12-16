<?php

namespace App\Http\Middleware;

use App\Http\Response\ApiResponse;
use App\Services\MailService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        $mailService = new MailService();
        if (auth()->guard()->check() && (auth()->guard()->user()->role === $role || $role === 'all' || ($role == 'tt' && (auth()->guard()->user()->role === 'taskee' || auth()->guard()->user()->role === 'tasker')))) {
            // if (auth()->guard()->user()->email_verified_at === null) {
            //     $mailService->sendOTP(['id' => auth()->guard()->id(), 'email' => auth()->guard()->user()->email, 'username' => auth()->guard()->user()->username]);
            //     return ApiResponse::OK(['URL' => env('APP_URL') . "/api/auth/verify?email=" . auth()->guard()->user()->email], 'Your OTP code has been sent. Please verify your email to proceed');
            // } else {
            return $next($request);
            // }
        } else {
            return ApiResponse::UNAUTHORIZED();
        }
    }
}
