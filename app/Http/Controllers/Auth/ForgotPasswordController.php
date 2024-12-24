<?php

namespace App\Http\Controllers\Auth;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use App\Services\MailService;
use App\Services\UserService;
use App\Http\Response\ApiResponse;
use App\Http\Controllers\Controller;

class ForgotPasswordController extends Controller
{
    private $users;
    private $mailService;
    public function __construct(UserService $userService)
    {
        $this->users = $userService;
        $this->mailService = new MailService();
    }
    public function forgot()
    {
        $validate = $this->validator(request()->post(), [
            'email' => 'required|email',
        ]);
        //Send OTP to email
        $user = User::where('email', $validate['email'])->first();
        $token = Str::random(60);
        if ($user) {
            $user->token_verify_password = $token;
            $user->token_verify_expired = Carbon::now()->addMinutes(5);
            $user->save();
            $user = null;
        } else {
            return ApiResponse::NOT_FOUND('Can not send OTP');
        }
        $this->mailService->sendForgotOTP($this->users->getUserToSendMail($validate['email']));
        return ApiResponse::OK(['url' => env('APP_URL') . '/api/auth/forgotPassword/verify?token=' . $token . '&email=' . $validate['email']], "OTP has been sent");
    }
    public function verifyEmail()
    {
        $validator = $this->validator(request()->post(), [
            'otp' => 'required|string|max:6',
        ]);
        if (array_key_exists('error', $validator)) {
            return ApiResponse::ERROR($validator);
        }
        $verify = $this->users->forgotPasswordByOTP(request()->query('email'), request()->query('token'), $validator['otp']);
        if ($verify) {
            return ApiResponse::OK($verify);
        } else {
            return ApiResponse::ERROR("OTP validation failed");
        }
    }
    public function resetPassword()
    {
        $validate = $this->validator(request()->post(), [
            'password' => 'required|string|min:8|confirmed',
        ]);
        if (array_key_exists('error', $validate)) {
            return ApiResponse::BAD_REQUEST($validate);
        }
        $result = $this->users->resetPassword(request()->query('email'), request()->query('token'), $validate['password']);
        if ($result) {
            return ApiResponse::OK(null, 'reset password successfully');
        } else {
            return ApiResponse::ERROR('Token expired or invalid');
        }
    }
}
