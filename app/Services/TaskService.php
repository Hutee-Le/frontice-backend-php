<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\Taskee;
use App\Models\Tasker;
use Illuminate\Support\Str;
use App\Http\Response\ApiResponse;
use App\Http\Response\SolutionResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Response\TaskResponse;
use App\Models\Notification;

class TaskService extends Service
{
    private $firebase;
    private $taskResponse;
    public function __construct()
    {
        $this->firebase = new FirebaseService();
        $this->taskResponse = new TaskResponse($this->firebase);
    }
    public function getFilters()
    {
        $owners = Task::getTaskers();
        $techniques = Task::getTechnical();
        $data = [];
        $data['techniques'] = $techniques->values();
        $data['owners'] = $owners;
        $data['required_point'] = [
            'min' => Task::min('required_point'),
            'max' => Task::max('required_point'),
        ];
        $data['createdAt'] = [
            'min' => strtotime(Task::min('created_at')),
            'max' => strtotime(Task::max('created_at')),
        ];
        $data['canJoin'] = [
            'yes' => Task::where('expired', ">=", now())->count(),
            'no' => Task::where('expired', "<", now())->count(),
        ];

        return ApiResponse::OK($data);
    }
    public function create($request)
    {
        $id = Str::uuid();
        $result = Task::create(array_merge($request, ['id' => $id]));

        $linkFileSuccess = $this->firebase->filesExists([$request['source'], $request['image'], $request['figma']]);
        if (!$linkFileSuccess['results']) {
            return ApiResponse::BAD_REQUEST($linkFileSuccess['filePath'] . " does not exist");
        }
        return $this->taskResponse->task($result);
    }
    public function taskeeGetTask(Taskee $taskee)
    {
        $tasks = Task::getAllTasksByTaskee($taskee, request()->query('technical'));
        return $tasks;
    }
    public function taskeeGetTasksByTaskerUsername($username)
    {
        $data['tasks'] = [];
        $query = Task::getTaskByUsername($username);
        $tasks = Task::customPaginateStatic(request(), $query);
        foreach ($tasks as $task) {
            $data['tasks'][] = $this->taskResponse->tasks($task, auth()->guard()->user()->taskee->points());
        }
        $data['total'] = $tasks->total();
        $data['current_page'] = $tasks->currentPage();
        $data['last_page'] = $tasks->lastPage();
        $data['per_page'] = $tasks->perPage();
        return ApiResponse::OK($data);
    }
    public function getTaskByID($id)
    {
        $task = Task::findOrFail($id);
        if (auth()->guard()->user()->role == 'taskee') {
            return $this->taskResponse->taskDetail($task, auth()->guard()->user()->taskee);
        } else {
            return $this->taskResponse->task($task);
        }
    }
    public function get(bool $isAdmin = false)
    {
        $data['tasks'] = [];
        if ($isAdmin) {
            $tasks = Task::customTaskPaginate(request());
        } else
            $tasks = Task::customTaskPaginate(request(), Task::where('tasker_id', auth()->guard()->user()->id));
        foreach ($tasks as $task) {
            $data['tasks'][] = $this->taskResponse->task($task);
        }
        $data['total'] = $tasks->total();
        $data['currentPage'] = $tasks->currentPage();
        $data['lastPage'] = $tasks->lastPage();
        $data['perPage'] = $tasks->perPage();
        return $data;
    }

    public function update($id, $request): array|false
    {
        $task = Task::findOrFail($id);
        if ($task->tasker_id == auth()->guard()->id()) {
            $linkFileSuccess = $this->firebase->filesExists([$request['source'], $request['image'], $request['figma']]);
            if (!$linkFileSuccess['results']) {
                return ApiResponse::BAD_REQUEST($linkFileSuccess['filePath'] . " does not exist");
            }
            if ($request['source'] ?? false) {
                $source = $task->source;
                $this->firebase->delete($source);
            }
            if ($request['image'] ?? false) {
                $img = $task->image;
                $this->firebase->delete($img);
            }
            if ($request['figma'] ?? false) {
                $figma = $task->figma;
                $this->firebase->delete($figma);
            }

            $task->update(array_filter($request));
            return $this->taskResponse->task($task);
        }
        return false;
    }
    public function delete($id)
    {
        try {
            $task = Task::findOrFail($id);
            if ($task->tasker_id == auth()->guard()->id()) {
                $this->firebase->delete($task->source);
                $this->firebase->delete($task->figma);
                $this->firebase->delete($task->image);
                foreach ($task->solutions as $solution) {
                    foreach ($solution->comments as $comment) {
                        $comment->delete();
                    }
                    $solution->delete();
                }
                $task->delete();
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    public function getTasksReport()
    {
        $data = Task::showTaskReports();
        return ApiResponse::OK($data);
    }
    public function getTasksReportDetail($id)
    {
        $task = Task::findOrFail($id);
        $data = TaskResponse::taskReportDetail($task);
        return ApiResponse::OK($data);
    }
    public function reportTask($id, Taskee $taskee, $reason)
    {
        $task = Task::findOrFail($id);
        if (Carbon::now()->lessThan(Carbon::parse($task->expired))) {
            $taskee->reports()->updateOrCreate(
                [
                    'taskee_id' => $taskee->id,
                    'task_id' => $id
                ],
                ['reason' => $reason]
            );
            $task->status = 'pending';
            $task->save();
            return ApiResponse::OK(null, 'report successfully');
        } else {
            return ApiResponse::ERROR('Task expired');
        }
    }
    public function valid($id)
    {
        $task = $task = Task::whereNotNull('status')->findOrFail($id);
        if ($task && Carbon::now()->lessThan(Carbon::parse($task->expired))) {
            $task->status = 'valid';
            $task->save();
            return ApiResponse::OK(['status' => $task->status]);
        }
        return ApiResponse::ERROR('Task expired');
    }
    public function invalid($id)
    {
        $task = Task::whereNotNull('status')->findOrFail($id);
        if ($task && Carbon::now()->lessThan(Carbon::parse($task->expired))) {
            foreach ($task->solutions as $solution) {
                foreach ($solution->comments as $comment) {
                    $comment->is_remove = true;
                }
                $solution->status = 'deleted';
            }
            $task->status = 'deleted';
            $task->save();
            Notification::create([
                'message' => "Task {$task->title} của bạn đã bị xóa bởi vi phạm quy tắc cộng đồng",
                'from' => auth()->guard()->id(),
                'to' => $task->tasker_id,
                'type' => 'Task',
                'task_id' => $task->id
            ]);
            return ApiResponse::OK(['status' => $task->status]);
        }
        return ApiResponse::ERROR('Task expired');
    }
}
