<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\Taskee;
use App\Models\Tasker;
use Illuminate\Support\Str;
use App\Services\MailService;
use App\Http\Response\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Response\UserResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserService extends Service
{
    private $mailService;
    public function __construct()
    {
        $this->mailService = new MailService();
    }
    public function login($email, $password)
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            if (password_verify($password, $user->password)) {
                $token = auth()->guard()->login($user);
                return $token;
            } else {
                return ApiResponse::UNAUTHORIZED('Invalid password');
            }
        } else {
            return ApiResponse::UNAUTHORIZED('email is not exist');
        }
    }
    //GET USER
    public function getAllUsers()
    {
        $users = User::all();
        return $users;
    }
    public function getUserToSendMail($email)
    {
        $user = User::where('email', '=', $email)->first();
        if (!$user) {
            return null;
        }
        return ['id' => $user->id, 'username' => $user->username, 'email' => $user->email];
    }
    public function getUserByUsername($username, $role = null)
    {
        $user = null;
        if ($role !== null) {
            $user = User::where('username', $username)->where('role', $role)->first();
        } else
            $user = User::where('username', '=', $username)->first();
        if (!$user) {
            return null;
        }
        $getRole = $user->role;
        $role = $user[$getRole];
        return UserResponse::user($user, $role);
    }
    public function getIdByUsername($username, $role)
    {
        $user = User::where('username', $username)->where('role', $role)->first();
        if (!$user) {
            return null;
        }
        return $user->id;
    }
    public function getUserById($id)
    {
        $user = User::findOrFail($id);
        $getRole = $user->role;
        $role = $user[$getRole];
        return UserResponse::user($user, $role);
    }
    public static function getTTById($id)
    {
        $user = User::findOrFail($id);
        $getRole = $user->role;
        $role = $user[$getRole];
        return UserResponse::userInComment($user, $role);
    }

    public static function getTaskeeById($id)
    {
        $user = Taskee::findOrFail($id);
        return UserResponse::taskee($user);
    }
    /**
     * Get User by Role, $role = admin || tasker || taskee
     * @param string $role
     * @return array
     */
    public function getUsersByRole(string $role = 'taskee')
    {
        $users = User::getUsersByRole($role);
        return $users;
    }

    //CREATE USER
    public function createAdmin($req)
    {
        $password = $req['password'];
        $req['password'] = bcrypt($req['password']);
        $userRequest = [
            'username' => $req['username'],
            'email' => $req['email'],
            'password' => $req['password'],
            'role' => $req['role'],
            'email_verified_at' => Carbon::now()
        ];
        $adminRequest = [
            'fullname' => $req['fullname'],
            'role' => $req['adminRole'],
        ];
        DB::beginTransaction();
        try {

            $uuid = Str::uuid();
            $user = User::create(array_merge(['id' => $uuid, 'firstLogin' => true], $userRequest));
            $admin = Admin::create(array_merge(['id' => $uuid], $adminRequest));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::BAD_REQUEST('Failed to create user');
        }
        $content = "Tài khoản FRONTICE có vai trò là {$req['adminRole']}  của bạn đã được tạo với email: {$req['email']}, password: {$password}\nSau khi đăng nhập hãy đổi mật khẩu.";
        Mail::raw($content, function ($mail) use ($req) {
            $mail->to($req['email'])
                ->subject('Thông tin tài khoản FRONTICE');
        });
        return UserResponse::user($user, $admin);
    }
    public function createTasker($req)
    {
        $req['password'] = bcrypt($req['password']);
        $userRequest = [
            'username' => strtolower($req['username']),
            'email' => $req['email'],
            'password' => $req['password'],
            'role' => $req['role'],
        ];
        $taskerRequest = [
            'firstname' => $req['firstname'],
            'lastname' => $req['lastname'],
            'company' => $req['company'],
            'phone' => $req['phone'] ?? null,
        ];
        DB::beginTransaction();
        try {
            $uuid = Str::uuid();
            $user = User::create(array_merge(['id' => $uuid], $userRequest));
            $tasker = Tasker::create(array_merge(['id' => $uuid], $taskerRequest));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::BAD_REQUEST('Failed to create user');
        }
        return UserResponse::user($user, $tasker);
    }

    public function createTaskee($req)
    {
        $req['password'] = bcrypt($req['password']);
        $userRequest = [
            'username' => strtolower($req['username']),
            'email' => $req['email'],
            'password' => $req['password'],
            'role' => $req['role'],
        ];
        $taskeeRequest = [
            'firstname' => $req['firstname'],
            'lastname' => $req['lastname'],
            'github' => $req['github'] ?? null,
            'phone' => $req['phone'] ?? null,
            'bio' => $req['bio'] ?? null,
        ];
        DB::beginTransaction();
        try {
            $uuid = Str::uuid();
            $user = User::create(array_merge(['id' => $uuid], $userRequest));
            $taskee = Taskee::create(array_merge(['id' => $uuid], $taskeeRequest));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::BAD_REQUEST('Failed to create user');
        }
        return UserResponse::user($user, $taskee);
    }
    public function resendOTP($email)
    {
        $user = User::where('email', '=', $email)->first();
        if ($user) {
            $this->mailService->sendOTP(['id' => $user->id, 'email' => $user->email, 'username' => $user->username]);
            return ApiResponse::OK(null, 'OTP sent successfully. Please check your email to proceed');
        } else {
            return ApiResponse::NOT_FOUND('User does not exist');
        }
    }
    public function changePassword(User $user, $password, $newPassword)
    {
        if ($user && Hash::check($password, $user->password)) {
            DB::beginTransaction();
            try {
                if ($user->role == 'admin') {
                    $user->admin->update(['first_login' => false]);
                }
                $user->update(['password' => bcrypt($newPassword)]);
                DB::commit();
                return ApiResponse::OK(null, 'Password changed successfully');
            } catch (\Exception $e) {
                DB::rollback();
                return ApiResponse::ERROR('Failed to change password');
            }
        } else {
            return ApiResponse::FORBIDDEN('Invalid password');
        }
    }
    public static function getUsernameById($id)
    {
        $user = User::find($id);
        return $user->username ?? null;
    }
    public function verifyByOTP($email, $otp)
    {
        $user = User::where('email', '=', $email)->where('otp', '=', $otp)->first();
        if ($user) {
            $expired = Carbon::parse($user->otp_expired);
            if (Carbon::now()->lessThan($expired)) {
                $user->email_verified_at = Carbon::now();
                $user->save();
                return true;
            }
            return ['message' => 'OTP expired'];
        }
        return false;
    }
    public function forgotPasswordByOTP($email, $token, $otp)
    {
        $user = User::where('email', '=', $email)->where("token_verify_password", '=', $token)->first();
        $expired = Carbon::parse($user->token_verify_expired);
        if ($user->otp == $otp && Carbon::now()->lessThan($expired)) {
            $token = Str::random(60);
            $user->otp = null;
            $user->otp_expired = null;
            $user->token_verify_password = null;
            $user->token_verify_expired = null;
            $user->token_reset_password = $token;
            $user->token_reset_expired = Carbon::now()->addMinutes(5);
            $user->save();
            return ['url' => env('APP_URL') . '/api/auth/forgotPassword/reset?token=' . $token . '&email=' . $email];
        }
        return false;
    }
    public function resetPassword($email, $token, $password)
    {
        $user = User::where('email', '=', $email)->where("token_reset_password", '=', $token)->where('token_reset_expired', '>', now())->first();
        if ($user) {
            $user->password = bcrypt($password);
            $user->token_reset_password = null;
            $user->token_reset_expired = null;
            $user->save();
            return true;
        }
        return false;
    }

    //FOLLOWER
    public function followTasker(Taskee $taskee, $tasker_id)
    {
        $followed = $taskee->following()->where('tasker_id', $tasker_id)->exists();
        if (!$followed) {
            $taskee->following()->attach($tasker_id, ['created_at' => now(), 'updated_at' => now()]);
            return true;
        } else {
            return false;
        }
    }
    public function unfollowTasker(Taskee $taskee, $tasker_id)
    {
        $followed = $taskee->following()->where('tasker_id', $tasker_id)->exists();
        if ($followed) {
            $taskee->following()->detach($tasker_id);
            return true;
        } else {
            return false;
        }
    }
    public function getFollowers(Tasker $tasker)
    {
        $taskees =  $tasker->taskees()->paginate(request()->get('per_page', 10));
        $data['taskee'] = [];
        foreach ($taskees as $ta) {
            $data['taskee'][] = UserResponse::taskee($ta);
        }
        $data['total'] = $taskees->total();
        $data['currentPage'] = $taskees->currentPage();
        $data['lastPage'] = $taskees->lastPage();
        $data['perPage'] = $taskees->perPage();
        return ApiResponse::OK($data);
    }
    public function getFollows(Taskee $tasker)
    {
        $taskers =  $tasker->followingTasker()->paginate(request()->get('per_page', 10));
        foreach ($taskers as $ta) {
            $data['tasker'][] = UserResponse::tasker($ta);
        }
        $data['total'] = $taskers->total();
        $data['currentPage'] = $taskers->currentPage();
        $data['lastPage'] = $taskers->lastPage();
        $data['perPage'] = $taskers->perPage();
        return ApiResponse::OK($data);
    }
}
