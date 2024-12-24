<?php

namespace App\Http\Response;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\Subscription;
use App\Models\Taskee;
use App\Models\Tasker;
use App\Services\FirebaseService;

class UserResponse
{
    /**
     * Information of user
     * @return array|int
     */
    public static function user($user, $role)
    {
        $firebase = new FirebaseService();
        switch ($user->role) {
            case 'admin':
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'fullname' => $role->fullname,
                    'firstLogin' => $role->first_login ? true : false,
                    'image' => str_starts_with($user->image, 'https://') ? $user->image : $firebase->sign($user->image),
                    'adminRole' => $role->role,
                    'createdAt' => strtotime($user->created_at),
                ];
            case 'tasker':
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'firstname' => $role->firstname,
                    'lastname' => $role->lastname,
                    'phone' => $role->phone,
                    'image' => str_starts_with($user->image, 'https://') ? $user->image : $firebase->sign($user->image),
                    'bio' => $role->bio,
                    'company' => $role->company,
                    'createdAt' => strtotime($user->created_at),
                ];
            case 'taskee':
                $goldAccount = (Carbon::now()->lessThan(Carbon::parse($role->gold_expired ?? Carbon::now()->subDay())));
                $goldExpires = null;
                if ($goldAccount) {
                    $goldExpires = $role->gold_expired;
                }
                return [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'firstname' => $role->firstname,
                    'lastname' => $role->lastname,
                    'phone' => $role->phone,
                    'image' => str_starts_with($user->image, 'https://') ? $user->image : $firebase->sign($user->image),
                    'bio' => $role->bio,
                    'cv' =>  $firebase->sign($role->cv),
                    'github' => $role->github,
                    'point' => $role->points(),
                    'challengeJoined' => $role->challenge_solutions()->count(),
                    'pendingChallenges' => $role->challenge_solutions()->whereNull('submitted_at')->count(),
                    'submittedChallenges' => $role->challenge_solutions()->whereNotNull('submitted_at')->count(),
                    "gold_account" => (Carbon::now()->lessThan(Carbon::parse($role->gold_expired ?? Carbon::now()->subDay()))),
                    "goldExpires" => $goldExpires,
                    'createdAt' => strtotime($user->created_at),
                ];
            default:
                return ApiResponse::NOT_FOUND();
        }
    }
    public static function mentor(Admin $admin)
    {
        $firebase = new FirebaseService();
        $feedback = [];
        if ($admin->solutions->count() > 0) {
            foreach ($admin->solutions as $sol) {
                $feedback[] =
                    SolutionResponse::challenge($sol);;
            }
        }
        $user = $admin->user;
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'fullname' => $admin->fullname,
            'firstLogin' => $admin->first_login ? true : false,
            'image' => str_starts_with($user->image, 'https://') ? $user->image : $firebase->sign($user->image),
            'adminRole' => $admin->role,
            'feedback' => $feedback,
            'createdAt' => strtotime($user->created_at),
        ];
    }
    public static function userInComment($user, $role)
    {
        $firebase = new FirebaseService();
        switch ($user->role) {
            case 'tasker':
                return [
                    'username' => $user->username,
                    'firstname' => $role->firstname,
                    'lastname' => $role->lastname,
                    'image' => str_starts_with($user->image, 'https://') ? $user->image : $firebase->sign($user->image),
                    'url' => env('APP_URL') . "/api/taskers/{$user->username}"
                ];
            case 'taskee':
                return [
                    'username' => $user->username,
                    'firstname' => $role->firstname,
                    'lastname' => $role->lastname,
                    'image' => str_starts_with($user->image, 'https://') ? $user->image : $firebase->sign($user->image),
                    'company' => $role->company,
                    'url' => env('APP_URL') . "/api/taskees/{$user->username}"
                ];
            default:
                return ApiResponse::NOT_FOUND();
        }
    }
    public static function taskee(Taskee $taskee)
    {
        $firebase = new FirebaseService();
        $user = $taskee->user;
        return [
            'username' => $user->username,
            'firstname' => $taskee->firstname,
            'lastname' => $taskee->lastname,
            'image' => str_starts_with($user->image, 'https://') ? $user->image : $firebase->sign($user->image),
            "gold_account" => (Carbon::now()->lessThan(Carbon::parse($taskee->gold_expired ?? Carbon::now()->subDay()))),
            'url' => env('APP_URL') . "/api/taskees/{$user->username}"

        ];
    }
    public static function subscription(Subscription $subscription)
    {
        return [
            'orderID' => $subscription->order_id,
            'name' => $subscription->service->name,
            'amountPaid' => $subscription->amount_paid,
            'isExpired' => (Carbon::now()->lessThan(Carbon::parse($subscription->gold_expired))) ? true : false,
            'expiredAt' => strtotime($subscription->gold_expired),
            'paid_at' => strtotime($subscription->created_at),
        ];
    }
    public static function tasker(Tasker $tasker)
    {
        $firebase = new FirebaseService();
        $user = $tasker->user;
        return [
            'username' => $user->username,
            'firstname' => $tasker->firstname,
            'lastname' => $tasker->lastname,
            'image' => str_starts_with($user->image, 'https://') ? $user->image : $firebase->sign($user->image),
            'url' => env('APP_URL') . "/api/taskers/{$user->username}",
            'company' => $tasker->company
        ];
    }
    public static function taskerForAdmin(Tasker $tasker)
    {
        $firebase = new FirebaseService();
        $user = $tasker->user;
        return [
            'id' => $tasker->id,
            'username' => $user->username,
            'firstname' => $tasker->firstname,
            'lastname' => $tasker->lastname,
            'email' => $tasker->user->email,
            'image' => str_starts_with($user->image, 'https://') ? $user->image : $firebase->sign($user->image),
            'url' => env('APP_URL') . "/api/taskers/{$user->username}",
            'company' => $tasker->company,
            'isApproved' => $tasker->is_approved ? true : false,
            'createdAt' => strtotime($tasker->created_at),
            'updatedAt' => strtotime($tasker->updated_at),
        ];
    }

    public static function admin(Admin $admin)
    {
        $firebase = new FirebaseService();
        $user = $admin->user;
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'fullname' => $admin->fullname,
            'firstLogin' => $admin->first_login ? true : false,
            'image' => str_starts_with($user->image, 'https://') ? $user->image : $firebase->sign($user->image),
            'adminRole' => $admin->role,
            'createdAt' => strtotime($user->created_at),
        ];
    }
    public static function adminFor(Admin $admin)
    {
        $firebase = new FirebaseService();
        $user = $admin->user;
        if ($admin->role == 'mentor') {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'fullname' => $admin->fullname,
                'firstLogin' => $admin->first_login ? true : false,
                'image' => str_starts_with($user->image, 'https://') ? $user->image : $firebase->sign($user->image),
                'adminRole' => $admin->role,
                'createdAt' => strtotime($user->created_at),
            ];
        } else {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'fullname' => $admin->fullname,
                'firstLogin' => $admin->first_login ? true : false,
                'image' => str_starts_with($user->image, 'https://') ? $user->image : $firebase->sign($user->image),
                'challengeCreated' => $admin->challenges()->count(),
                'adminRole' => $admin->role,
                'createdAt' => strtotime($user->created_at),
            ];
        }
    }
    public static function adminForFilter($admin)
    {
        $firebase = new FirebaseService();
        $user = $admin->user;
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'fullname' => $admin->fullname,
            'image' => str_starts_with($user->image, 'https://') ? $user->image : $firebase->sign($user->image),
            'adminRole' => $admin->role,
            'createdAt' => strtotime($user->created_at),
            'created_count' => $admin->challenges_count
        ];
    }
    public static function taskerForFilter($tasker)
    {
        $firebase = new FirebaseService();
        $user = $tasker->user;
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'firstname' => $tasker->firstname,
            'lastname' => $tasker->lastname,
            'image' => str_starts_with($user->image, 'https://') ? $user->image : $firebase->sign($user->image),
            'createdAt' => strtotime($user->created_at),
            'created_count' => $tasker->tasks_count
        ];
    }
}
