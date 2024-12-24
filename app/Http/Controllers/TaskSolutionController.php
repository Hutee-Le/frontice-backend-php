<?php

namespace App\Http\Controllers;

use App\Http\Response\ApiResponse;
use App\Services\TaskSolutionService;

class TaskSolutionController extends Controller
{
    private $taskSolutions;
    public function __construct(TaskSolutionService $taskSolution)
    {
        $this->taskSolutions = $taskSolution;
    }
    public function getAll()
    {
        $result = $this->taskSolutions->getAll();
        return $result;
    }
    public function joinTask($id)
    {
        $result = $this->taskSolutions->joinTask(auth()->guard()->user()->taskee, $id);
        return $result;
    }
    public function downloadSource($id)
    {
        $result = $this->taskSolutions->downloadSource(auth()->guard()->user()->taskee, $id);
        return $result;
    }
    public function downloadFigma($id)
    {
        $result = $this->taskSolutions->downloadFigma(auth()->guard()->user()->taskee, $id);
        return $result;
    }
    public function submitTask($id)
    {
        $val = $this->validator(request()->post(), [
            'title' => 'required|max:254',
            'github' => 'required|url',
            'live_github' => 'required|url',
        ]);
        if (array_key_exists('error', $val)) {
            return ApiResponse::BAD_REQUEST($val['error']);
        }
        $result = $this->taskSolutions->submitTask($val, auth()->guard()->user()->taskee, $id);
        return $result;
    }
    public function getTaskSolutions($id)
    {
        $result = $this->taskSolutions->getTaskSolutions(auth()->guard()->user()->tasker, $id);
        return $result;
    }
    public function AdminGetTaskSolutionByTaskId($id)
    {
        $result = $this->taskSolutions->getTaskSolutionByTaskId($id);
        return $result;
    }
    public function adminGetTaskSolution($id)
    {
        $result = $this->taskSolutions->adminGetTaskSolution($id);
        return $result;
    }
    public function getTaskSolution($id)
    {
        $result = $this->taskSolutions->getTaskSolution(auth()->guard()->user()->tasker, $id);
        return $result;
    }
    public function taskeeGetTaskSolution($id)
    {
        $result = $this->taskSolutions->taskeeGetTaskSolution(auth()->guard()->user()->taskee, $id);
        return $result;
    }
    public function taskeeGetTaskSolutions()
    {
        $result = $this->taskSolutions->taskeeGetTaskSolution(auth()->guard()->user()->taskee);
        return $result;
    }
    public function  taskeeGetTaskSolutionsSubmitted()
    {
        $result = $this->taskSolutions->taskeeGetTaskSolutionSubmitted(auth()->guard()->user()->taskee);
        return $result;
    }

    public function updateTaskSolution($id)
    {
        $val = $this->validator(request()->all(), [
            'status' => 'required|in:Đạt,Chưa đạt',
        ]);
        if (array_key_exists('error', $val)) {
            return ApiResponse::BAD_REQUEST($val);
        }
        $result = $this->taskSolutions->changeStatus(auth()->guard()->user()->tasker, $id, $val['status']);
        return $result;
    }

    public function deleteTaskSolution($id)
    {
        $result = $this->taskSolutions->deleteTaskSolution(auth()->guard()->user()->tasker, $id);
        return $result;
    }
}
