<?php

namespace App\Http\Controllers;

use App\Http\Response\ApiResponse;
use App\Models\Taskee;
use App\Services\UserService;

class TaskeeController extends Controller
{
    private $users;
    public function __construct(UserService $userService)
    {
        $this->users = $userService;
    }
    public function getTaskeeByUsername($username)
    {
        $taskee = $this->users->getUserByUsername($username, 'taskee');
        if ($taskee) {
            return ApiResponse::OK($taskee);
        } else {
            return ApiResponse::NOT_FOUND();
        }
    }
    public  function followTasker($username)
    {
        $id = $this->users->getIdByUsername($username, 'tasker');
        if (!$id) {
            return ApiResponse::NOT_FOUND();
        }
        $result = $this->users->followTasker(auth()->guard()->user()->taskee, $id);
        if ($result) {
            return ApiResponse::OK($result);
        } else {
            return ApiResponse::BAD_REQUEST("You have followed {$username}");
        }
    }
    public function unfollowTasker($username)
    {
        $id = $this->users->getIdByUsername($username, 'tasker');
        if (!$id) {
            return ApiResponse::NOT_FOUND();
        }
        $result = $this->users->unfollowTasker(auth()->guard()->user()->taskee, $id);
        if ($result) {
            return ApiResponse::OK();
        } else {
            return ApiResponse::BAD_REQUEST("You have not followed {$username}");
        }
    }
    public function getFollows()
    {
        return $this->users->getFollows(auth()->guard()->user()->taskee);
    }
    public function getPremiumAccounts()
    {
        return Taskee::premiumAccounts();
    }
}
