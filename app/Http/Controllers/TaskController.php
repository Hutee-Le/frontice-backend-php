<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use App\Http\Response\ApiResponse;
use App\Http\Response\TaskResponse;
use App\Services\FirebaseService;
use Carbon\Carbon;

class TaskController extends Controller
{
    private $tasks;
    private $taskResponse;
    public function __construct(TaskService $taskService)
    {
        $this->tasks = $taskService;
        $this->taskResponse = new TaskResponse(new FirebaseService);
    }
    public function getFilters()
    {
        $filter = $this->tasks->getFilters();
        return $filter;
    }
    public function create()
    {
        $req = $this->validator(request()->post(), [
            'title' => 'required|max:255',
            'image' => 'required|string',
            'source' => 'required|string',
            'figma' => 'nullable|string',
            'required_point' => 'required|integer|max:10000',
            'short_des' => 'required|string|max:255',
            'desc' => 'required|json',
            'expired' => 'required|date|after:now',
            'technical' => 'required|array'
        ]);
        if (array_key_exists('error', $req)) {
            return ApiResponse::ERROR($req);
        }
        $req['technical'] = ['technical' => $req['technical']];
        $req['expired'] = Carbon::parse($req['expired']);
        $task = $this->tasks->create(array_merge($req, ['tasker_id' => auth()->guard()->user()->id]));
        return $task;
    }
    public function getTaskByID($id)
    {
        $task = $this->tasks->getTaskByID($id);
        if ($task == null) {
            return ApiResponse::NOT_FOUND('Task not found');
        }
        return ApiResponse::OK($task);
    }
    public function getAll()
    {
        $tasks = $this->tasks->get(true);
        return ApiResponse::OK($tasks);
    }
    public function getTasks()
    {
        $tasks = $this->tasks->get();
        return ApiResponse::OK($tasks);
    }
    public function taskeeGetTasks()
    {
        $tasks = $this->tasks->taskeeGetTask(auth()->guard()->user()->taskee);
        return ApiResponse::OK($tasks);
    }
    public function taskeeGetTasksByTaskerUsername($username)
    {
        $tasks = $this->tasks->taskeeGetTasksByTaskerUsername($username);
        return $tasks;
    }
    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $request = $this->validator(request()->all(), [
            'title' => 'required|max:255',
            'image' => 'nullable|string',
            'source' => 'nullable|string',
            'figma' => 'nullable|string',
            'required_point' => 'nullable|integer|max:10000',
            'short_des' => 'nullable|string|max:255',
            'desc' => 'nullable|string',
            'expired' => 'nullable|date',
            'technical' => 'nullable|array'
        ]);
        if (array_key_exists("error", $request)) {
            return ApiResponse::BAD_REQUEST($request);
        }
        if (array_key_exists("technical", $request)) {
            $request['technical'] = ['technical' => $request['technical']];
        }
        $result = $this->tasks->update($id, $request);
        if (!$result) {
            return ApiResponse::BAD_REQUEST('Can not update task');
        }
        return ApiResponse::OK($result);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $result = $this->tasks->delete($id);
        return $result ? ApiResponse::OK(['deleted' => $result]) : ApiResponse::BAD_REQUEST('Unable to delete');
    }
    public function reportTask()
    {
        $val = $this->validator(request()->post(), [
            'task_id' => 'required|exists:tasks,id',
            'reason' => 'required|string'
        ]);
        if (array_key_exists('error', $val)) {
            return ApiResponse::BAD_REQUEST($val);
        }
        $result = $this->tasks->reportTask($val['task_id'], auth()->guard()->user()->taskee, $val['reason']);
        return $result;
    }
    public function getTaskReport()
    {
        $taskResport = $this->tasks->getTasksReport();
        return $taskResport;
    }
    public function getTasksReportDetail($id)
    {
        $taskResportDetail = $this->tasks->getTasksReportDetail($id);
        return $taskResportDetail;
    }
    public function valid()
    {
        $val = $this->validator(request()->post(), [
            'task_id' => 'required|exists:tasks,id'
        ]);
        if (array_key_exists('error', $val)) {
            return ApiResponse::BAD_REQUEST($val);
        }

        $task = $this->tasks->valid(request()->post('task_id'));
        return $task;
    }
    public function invalid()
    {
        $val = $this->validator(request()->post(), [
            'task_id' => 'required|exists:tasks,id'
        ]);
        if (array_key_exists('error', $val)) {
            return ApiResponse::BAD_REQUEST($val);
        }

        $task = $this->tasks->invalid(request()->post('task_id'));
        return $task;
    }
}
