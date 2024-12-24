<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Admin;
use App\Models\Taskee;
use App\Models\Tasker;
use App\Models\Comment;
use App\Scopes\IsRemove;
use App\Models\TaskComment;
use App\Models\TaskSolution;
use App\Scopes\StatusDelete;
use App\Models\ChallengeSolution;
use App\Http\Response\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Response\UserResponse;
use Illuminate\Support\Facades\Mail;

class AdminService extends Service
{
    private $firebase;
    public function __construct()
    {
        $this->firebase = new FirebaseService();
    }
    public function updateAdmin($username, $req)
    {
        $userRequest = array_filter([
            'username' => $req['username'] ?? null,
            'image' => $req['image'] ?? null,
            'email' => $req['email'] ?? null,
            'password' => $req['password'] ?? null,
        ]);
        $adminRequest = array_filter([
            'fullname' => $req['fullname'] ?? null,
        ]);
        $user = User::where('username', $username)->first();
        if (!$user) {
            return ApiResponse::NOT_FOUND('User not found');
        }
        $admin = $user->admin;


        DB::beginTransaction();
        try {
            $user->update($userRequest);
            $admin->update($adminRequest);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::BAD_REQUEST('Failed to update user');
        }
        return UserResponse::user($user, $admin);
    }
    public function updateTasker($username, $req)
    {
        $user = User::where('username', $username)->first();
        if (!$user) {
            return ApiResponse::NOT_FOUND('User not found');
        }
        $tasker = $user->tasker;
        $userRequest = array_filter([
            'username' => strtolower($req['username'] ?? null) ?? null,
            'email' => $req['email'] ?? null,
            'password' => $req['password'] ?? null,
            'image' => $req['image'] ?? null,
        ]);
        $taskerRequest = array_filter([
            'firstname' => $req['firstname'] ?? null,
            'lastname' => $req['lastname'] ?? null,
            'company' => $req['company'] ?? null,
            'phone' => $req['phone'] ?? null,
            'bio' => $req['bio'] ?? null,

        ]);

        DB::beginTransaction();
        try {
            $user->update($userRequest);
            $tasker->update($taskerRequest);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::BAD_REQUEST('Failed to update user');
        }
        return UserResponse::user($user, $tasker);
    }
    public function updateTaskee($username, $req)
    {
        $user = User::where('username', $username)->first();
        if (!$user) {
            return ApiResponse::NOT_FOUND('User not found');
        }
        $taskee = $user->taskee;
        $userRequest = array_filter([
            'username' => strtolower($req['username'] ?? null) ?? null,
            'email' => $req['email'] ?? null,
            'password' => $req['password'] ?? null,
            'image' => $req['image'] ?? null,
        ]);
        $taskeeRequest = array_filter([
            'firstname' => $req['firstname'] ?? null,
            'lastname' => $req['lastname'] ?? null,
            'github' => $req['github'] ?? null,
            'phone' => $req['phone'] ?? null,
            'bio' => $req['bio'] ?? null,
            'cv' => $req['cv'] ?? null,
        ]);

        DB::beginTransaction();
        try {
            $user->update($userRequest);
            $taskee->update($taskeeRequest);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::BAD_REQUEST('Failed to update user');
        }
        return UserResponse::user($user, $taskee);
    }
    public function approveTasker(Admin $admin, $id)
    {
        $tasker = Tasker::findOrFail($id);
        $tasker->is_approved = true;
        $tasker->admin_id = $admin->id;
        $tasker->save();
        $to = $tasker->user->email;
        $subject = 'Your Tasker account has been approved';
        $body = 'Hello, The new Tasker account. Welcome to be a part of FRONTICE';

        Mail::raw($body, function ($message) use ($to, $subject) {
            $message->to($to)
                ->subject($subject);
        });
        return ApiResponse::OK('Tasker approved successfully');
    }
    public function deleteUser($user)
    {
        try {
            $me = auth()->guard()->user();
            if ($me->admin->role == 'root') {
                if ($me === $user) {
                    return ApiResponse::FORBIDDEN('You cannot delete yourself');
                }
                if ($user->role == 'admin' && $user->admin->role == 'root' && Carbon::parse($me->created_at)->lessThan(Carbon::parse($user->created_at))) {
                    return ApiResponse::FORBIDDEN('You cannot delete this Admin');
                }
                if ($user->role == 'admin' && ($user->admin->role == 'challenge' || $user->admin->role == 'root')) {
                    $challenges = $user->admin->challenges;
                    foreach ($challenges as $challenge) {
                        $root = Admin::oldest()->first();
                        $challenge->admin_id = $root->id;
                        $challenge->save();
                    }
                }
                if ($user->role == 'tasker') {
                    TaskComment::where('user_id', $user->id)->delete();

                    // Lấy tất cả các task IDs của tasker
                    $taskIds = Task::where('tasker_id', $user->tasker->id ?? null)->pluck('id');

                    // Lấy tất cả solution IDs liên quan
                    $solutionIds = TaskSolution::whereIn('task_id', $taskIds)->pluck('id');

                    // Xóa comments trong solutions
                    TaskComment::withoutGlobalScope(IsRemove::class)->whereIn('task_solution_id', $solutionIds)->delete();

                    // Xóa solutions
                    TaskSolution::whereIn('task_id', $taskIds)->delete();

                    // Xóa tasks
                    Task::withoutGlobalScope(StatusDelete::class)->where('tasker_id', $user->tasker->id ?? null)->delete();
                }
                if ($user->role == 'taskee') {
                    $tSID = TaskSolution::where('taskee_id', $user->tasker->id ?? null)->pluck('id');
                    TaskComment::withoutGlobalScope(IsRemove::class)->whereIn('task_solution_id', $tSID)->delete();
                    TaskSolution::where('taskee_id', $user->tasker->id ?? null)->delete();
                    $challengeSID = ChallengeSolution::where('taskee_id', $user->tasker->id ?? null)->pluck('id');
                    Comment::withoutGlobalScope(IsRemove::class)->whereIn('challenge_solution_id', $challengeSID)->delete();
                    ChallengeSolution::where('taskee_id', $user->tasker->id ?? null)->delete();
                }
                $user->delete();
            }
            return ApiResponse::OK('User deleted successfully');
        } catch (\Exception $e) {
            return $e;
        }
    }
    public function showTaskerDisapprove()
    {
        $data['taskers'] = [];
        $query = Tasker::query();
        $query->where('is_approved', 0);
        $taskers = Tasker::customPaginateStatic(request(), $query);
        foreach ($taskers as $tasker) {
            $data['taskers'][] = UserResponse::taskerForAdmin($tasker);
        }
        $data['total'] = $taskers->total();
        $data['currentPage'] = $taskers->currentPage();
        $data['lastPage'] = $taskers->lastPage();
        $data['perPage'] = $taskers->perPage();
        return ApiResponse::OK($data);
    }
}
