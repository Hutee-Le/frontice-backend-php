<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Taskee;
use App\Models\UserOtp;
use Illuminate\Support\Str;
use App\Services\MailService;
use App\Services\UserService;
use App\Services\AdminService;
use Illuminate\Http\JsonResponse;
use App\Http\Response\ApiResponse;
use App\Http\Response\UserResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Laravel\Socialite\Facades\Socialite;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    private $users;
    private $admins;
    private $mailService;
    public function __construct(UserService $userService, AdminService $adminService, MailService $mailService)
    {
        $this->users = $userService;
        $this->admins = $adminService;
        $this->mailService = $mailService;
    }
    public function sendOtp()
    {
        request()->validate(['email' => 'required|email|unique:users']);

        $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT); // OTP 6 chữ số
        $expiresAt = now()->addMinutes(5); // Hết hạn sau 5 phút
        // Lưu OTP vào bảng
        UserOtp::updateOrCreate(
            ['email' => request()->email],
            ['otp' => $otp, 'expires_at' => $expiresAt]
        );

        // Gửi OTP qua email (hoặc SMS nếu cần)
        $this->mailService->sendOTP(['email' => request()->email, 'otp' => $otp]);

        return ApiResponse::OK();
    }
    public function verifyOtp()
    {
        request()->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric',
        ]);

        $userOtp = UserOtp::where('email', request()->email)->first();

        if (!$userOtp || $userOtp->otp !== request()->otp || now()->greaterThan($userOtp->expires_at)) {
            return ApiResponse::ERROR('OTP không hợp lệ hoặc đã hết hạn.');
        }
        $userOtp->status = 'valid';
        $userOtp->save();

        return ApiResponse::OK();
    }


    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register()
    {
        if (request()->role !== 'admin' && (request()->role === 'tasker' || request()->role === 'taskee')) {
            $response = new ApiResponse();
            if (request()->role === "tasker") {
                $validate = $this->validator_create_tasker(request()->post());
                if (array_key_exists('error', $validate)) {
                    return ApiResponse::ERROR($validate);
                }
                $result = $this->users->createTasker($validate);
                if ($result instanceof JsonResponse) {
                    return $result;
                }
                $response->setResponse('Tasker created successfully', array_merge(['verify_url' => env('APP_URL') . "/api/auth/verify?email=" . $validate['email']], $result), 201);
                return $response();
            } elseif (request()->role === 'taskee') {
                $validate = $this->validator_create_taskee(request()->post());
                if (array_key_exists('error', $validate)) {
                    return ApiResponse::ERROR($validate);
                }
                $result = $this->users->createTaskee($validate);
                if ($result instanceof JsonResponse) {
                    return $result;
                }
                return ApiResponse::OK(array_merge(['verify_url' => env('APP_URL') . "/api/auth/verify?email=" . $validate['email']], $result), 'Taskee created successfully');
            }
            return $response();
        } else {
            return ApiResponse::BAD_REQUEST("Can not resgister");
        }
    }
    public function verifyEmail()
    {
        $validator = $this->validator(request()->post(), [
            'otp' => 'required|string|max:6',
        ]);
        $verify = $this->users->verifyByOTP(request()->query('email'), $validator['otp']);
        if ($verify && is_bool($verify)) {
            return ApiResponse::OK();
        } else {
            return ApiResponse::ERROR($verify);
        }
    }
    public function resendOTP()
    {
        return $this->users->resendOTP(request()->post('email'));
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $validator = $this->validator(request()->post(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
        if (array_key_exists('error', $validator)) {
            return ApiResponse::ERROR($validator);
        }
        $tasker = User::where('role', 'tasker')->where('email', $validator['email'])
            ->first();

        if ($tasker && Hash::check($validator['password'], $tasker->password)) {
            if (!$tasker->tasker->is_approved) {
                return ApiResponse::FORBIDDEN('Tasker has not approved yet');
            }
        }

        $token = $this->users->login($validator['email'], $validator['password']);
        if ($token instanceof JsonResponse) {
            return $token;
        }
        $refreshToken = $this->createRefreshToken($token);
        $cookie = cookie('refreshToken', $refreshToken, config('jwt.refresh_ttl'), '/', null, true, true, false, 'Strict');
        return $this->respondWithToken($token, $refreshToken)->cookie($cookie);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = auth()->guard()->user();
        $role = auth()->guard()->user()[$user->role];
        $output = UserResponse::user($user, $role);
        return ApiResponse::OK($output);
    }
    public function update()
    {
        $user = auth()->guard()->user();
        $username = $user->username;
        $role = $user->role;
        $result = null;
        $file = request()->file('image');
        $response = new ApiResponse();
        if ($role == 'admin') {
            $req = $this->validator_update_admin(request()->all());
            if (array_key_exists('error', $req)) {
                return ApiResponse::BAD_REQUEST($req);
            }
            $result = $this->admins->updateAdmin($username, $req, $file);
        } elseif ($role == 'tasker') {
            $req = $this->validator_update_tasker(request()->all());
            if (array_key_exists('error', $req)) {
                return ApiResponse::BAD_REQUEST($req);
            }
            $result = $this->admins->updateTasker($username, $req, $file);
        } elseif ($role == 'taskee') {
            $req = $this->validator_update_taskee(request()->all());
            if (array_key_exists('error', $req)) {
                return ApiResponse::BAD_REQUEST($req);
            }
            $result = $this->admins->updateTaskee($username, $req, $file, request()->file('cv'));
        }
        if ($result instanceof JsonResponse) {
            return $result;
        }
        if ($result == null) {
            return ApiResponse::BAD_REQUEST();
        }
        $response->setResponse("Update Successfully", $result);
        return $response();
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $res = new ApiResponse();
        if (Cookie::get('refreshToken')) {
            Cookie::queue(Cookie::forget('refreshToken'));
        }
        $cookie = cookie('refreshToken', null, config('jwt.refresh_ttl'), '/', null, true, true, false, 'Strict');
        auth()->guard()->logout();
        $res->setResponse('Successfully logged out');
        return ApiResponse::OK(null, 'logout successfully')->cookie($cookie);
    }
    public function changePassword()
    {
        $validator = $this->validator(request()->post(), [
            'current_password' => 'required|min:8',
            'password' => 'required|confirmed|min:8|different:current_password',
        ]);
        if (array_key_exists('error', $validator)) {
            return ApiResponse::BAD_REQUEST($validator);
        }
        $user = auth()->guard()->user();
        return $this->users->changePassword($user, $validator['current_password'], $validator['password']);
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $refreshToken = request()->post('refreshToken');
        if ($refreshToken === null) {
            return ApiResponse::BAD_REQUEST('refreshToken missing');
        }
        try {
            $decoded = JWTAuth::getJWTProvider()->decode($refreshToken);
            $user = User::findOrFail($decoded['sub']);
            try {
                JWTAuth::setToken($decoded['token'])->checkOrFail();
                JWTAuth::invalidate($decoded['token']);
            } catch (TokenExpiredException) {
            }
            // JWTAuth::invalidate($decoded['token']);
            $token = auth()->guard()->login($user);

            $refresh = $this->createRefreshToken($token);
            // $cookie = cookie('refreshToken', $refresh, config('jwt.refresh_ttl'), '/', null, true, true, false, 'Strict');
            return $this->respondWithToken($token, $refresh);
        } catch (TokenInvalidException $e) {
            // Xử lý khi token không hợp lệ
            return ApiResponse::UNAUTHORIZED(['message' => 'Token is invalid']);
        } catch (TokenExpiredException $e) {
            // Xử lý khi token đã hết hạn
            return ApiResponse::UNAUTHORIZED(['message' => 'Token has expired']);
        } catch (JWTException $e) {
            // Xử lý các lỗi khác liên quan đến JWT
            return ApiResponse::UNAUTHORIZED(['message' => 'Token ERROR: ' . $e->getMessage()]);
        } catch (RequiredConstraintsViolated $e) {
            // Xử lý lỗi khi kiểm tra token
            return ApiResponse::UNAUTHORIZED(['message' => $e->getMessage()]);
        } catch (Exception $e) {
            // Xử lý các lỗi khác
            return ApiResponse::UNAUTHORIZED(['message' => $e->getMessage()]);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $refreshToken)
    {
        return ApiResponse::OK([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }
    private function createRefreshToken($token)
    {
        $id = JWTAuth::getJWTProvider()->decode($token)['sub'];
        $data = [
            "sub" => $id,
            'token' => $token,
            'exp' => time() + config('jwt.refresh_ttl')
        ];
        $refreshToken = JWTAuth::getJWTProvider()->encode($data);
        return $refreshToken;
    }

    //login by github

    public function redirectToGithub()
    {
        try {
            return Socialite::driver('github')->redirect();
        } catch (Exception $e) {
            return response()->json(array('error' => $e->getMessage()));
        }
    }

    public function handleGithubCallback()
    {
        try {
            $user = Socialite::driver('github')->stateless()->user();
            $token = null;
            // Tìm hoặc tạo người dùng
            $authUser = User::where('github_id', $user->id)->first();
            $email = User::where('email', $user->getEmail())->first();

            if ($email && $email->github_id == null) {
                $email->github_id = $user->id;
                $email->save();
                $token = JWTAuth::fromUser($email);
            } elseif (!$authUser) {
                $uuid = Str::uuid();
                $authUser = User::create([
                    'id' => $uuid,
                    'username' => $user->getNickname() . Str::random(6),
                    'email' => $user->email,
                    'role' => 'taskee',
                    'github_id' => $user->id,
                    'image' => $user->getAvatar(),
                    'email_verified_at' => Carbon::now()
                ]);
                $taskee = Taskee::create([
                    'id' => $uuid,
                    'firstname' => Str::random(6),
                    'lastname' => 'Github',
                    'github' => "https://github.com/" . $user->nickname
                ]);
                $token = JWTAuth::fromUser($authUser);
            } else {
                $token = JWTAuth::fromUser($authUser);
            }
            $refreshToken = $this->createRefreshToken($token);
            return redirect(env('FRONTEND_REDIRECT_URL_LOGIN_GITHUB') . "?token={$token}&refreshToken={$refreshToken}");
        } catch (Exception $e) {
            return ApiResponse::ERROR(array('error' => get_class($e)));
        }
    }
}
