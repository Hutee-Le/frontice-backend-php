<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskerRequest;
use App\Http\Requests\UpdateTaskerRequest;
use App\Http\Response\ApiResponse;
use App\Models\Tasker;
use App\Services\UserService;

class TaskerController extends Controller
{
    private $users;
    public function __construct(UserService $userService)
    {
        $this->users = $userService;
    }
    public function getTaskerByUsername($username)
    {
        $result = $this->users->getUserByUsername($username, 'tasker');
        if ($result) {
            return ApiResponse::OK($result);
        } else {
            return ApiResponse::NOT_FOUND();
        }
    }

    public function getFollowers()
    {
        return $this->users->getFollowers(auth()->guard()->user()->tasker);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskerRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Tasker $tasker)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tasker $tasker)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskerRequest $request, Tasker $tasker)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tasker $tasker)
    {
        //
    }
}
