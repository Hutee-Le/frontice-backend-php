<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\Taskee;
use App\Models\Tasker;
use App\Models\TaskSolution;
use Illuminate\Http\JsonResponse;
use App\Http\Response\ApiResponse;
use App\Http\Response\SolutionResponse;
use App\Http\Response\TaskResponse;

class TaskSolutionService extends Service
{
    public function getAll()
    {
        $solutions = TaskSolution::getAll(request()->get('status', null), request()->get('taskee_id', null), request()->get('task_id', null), request()->get('submitted', true));
        return ApiResponse::OK($solutions);
    }
    public function joinTask(Taskee $taskee, $task_id)
    {
        $task_response = new TaskResponse(new FirebaseService);

        $task = Task::findOrFail($task_id);
        if (Carbon::now()->lessThan(Carbon::parse($task->expired))) {
            if ($taskee->points() >= $task->required_point) {
                $taskExist = $taskee->task_solutions->where('task_id', $task_id)->first();
                if ($taskExist) {
                    return ApiResponse::OK('You have joined the task already');
                } else {
                    // Thong bao
                    $taskee->task_solutions()->create(['task_id' => $task_id]);
                    return ApiResponse::OK();
                }
            } else {
                return ApiResponse::ERROR('Not enough point');
            }
        } else {
            return ApiResponse::ERROR('Task expired.');
        }
    }
    public function downloadSource(Taskee $taskee, $task_id): JsonResponse
    {
        $task_response = new TaskResponse(new FirebaseService);
        $task = Task::findOrFail($task_id);
        if (Carbon::now()->lessThan(Carbon::parse($task->expired))) {
            $taskExist = $taskee->task_solutions()->where('task_id', $task_id)->exists();
            if ($taskExist) {
                $links =  $task_response->downloadSource($task);
                return ApiResponse::OK($links);
            } else {
                return ApiResponse::FORBIDDEN('You must join this task to download source.');
            }
        } else {
            return ApiResponse::ERROR('Task expired.');
        }
    }
    public function downloadFigma(Taskee $taskee, $task_id): JsonResponse
    {
        $task_response = new TaskResponse(new FirebaseService);
        $task = Task::findOrFail($task_id);
        if (Carbon::now()->lessThan(Carbon::parse($task->expired))) {
            $taskExist = $taskee->task_solutions()->where('task_id', $task_id)->exists();
            if ($taskExist) {
                $links =  $task_response->downloadFigma($task);
                return ApiResponse::OK($links);
            } else {
                return ApiResponse::FORBIDDEN('You must join this task to download Figma.');
            }
        } else {
            return ApiResponse::ERROR('Task expired.');
        }
    }
    public function submitTask($request, Taskee $taskee, $task_id): JsonResponse
    {
        $task_solution = $taskee->task_solutions()->where('task_id', $task_id)->where('status', 'Chưa nộp')->first();
        if ($task_solution && $task_solution->task->status !== 'deleted') {
            $expires = Carbon::parse($task_solution->task->expired);
            if (Carbon::now()->lessThan($expires)) {
                $task_solution->update(array_merge($request, ['status' => 'Đã nộp', 'submitted_at' => Carbon::now()]));
                MailService::submitTask($taskee, [
                    'title' => 'Thông báo từ Frontice',
                    'email' => $task_solution->task->tasker->user->email,
                    'task_id' => $task_id,
                    'from' => $taskee->id,
                    'to' => $task_solution->task->tasker->id,
                    'username' => $task_solution->task->tasker->user->username,
                    'task_title' => $task_solution->task->title,
                    'task_solution_id' => $task_solution->id
                ]);
                return ApiResponse::OK();
            } else {
                return ApiResponse::ERROR('Task Expired to submit');
            }
        } else {
            return ApiResponse::NOT_FOUND('Task not found');
        }
    }
    /**
     * For Tasker
     */
    public function getTaskSolutions(Tasker $tasker, $task_id): JsonResponse
    {
        $task = $tasker->tasks()->where('id', $task_id)->first();
        if ($task) {
            $solutions = $task->solutions()->whereNot('status', 'Chưa nộp');
            $task_solutions = TaskSolution::customSolutionPaginate(request(), $solutions);
            $data = [];
            if ($task_solutions) {
                $count = $task->solutions()->count();
                $count_solutions = $task_solutions->count();
                foreach ($task_solutions as $task_solution) {
                    $data['solutions'][] = SolutionResponse::task($task_solution);
                }
                $data['total'] = $task_solutions->total();
                $data['currentPage'] = $task_solutions->currentPage();
                $data['lastPage'] = $task_solutions->lastPage();
                $data['perPage'] = $task_solutions->perPage();

                return ApiResponse::OK($data, "Có {$count} lượt tham gia và {$count_solutions} bài được nộp.");
            }
        } else return ApiResponse::NOT_FOUND("No solutions found");
    }
    public function getTaskSolution(Tasker $tasker, $id)
    {
        $task_solution = TaskSolution::findOrFail($id);
        if ($task_solution->task->status !== 'deleted') {
            if ($task_solution->task->tasker->id == $tasker->id) {
                if ($task_solution->status == 'Đã nộp') {
                    $task_solution->status = 'Đã xem';
                    $task_solution->save();
                }
                return ApiResponse::OK(SolutionResponse::task($task_solution));
            } else return ApiResponse::FORBIDDEN('You are not allowed to access this solution');
        } else {
            return ApiResponse::FORBIDDEN('This task has been deleted');
        }
    }
    public function adminGetTaskSolution($id)
    {
        $task_solution = TaskSolution::findOrFail($id);
        if ($task_solution->task->status !== 'deleted') {
            return ApiResponse::OK(SolutionResponse::task($task_solution));
        } else {
            return ApiResponse::FORBIDDEN('This task has been deleted');
        }
    }
    public function taskeeGetTaskSolution(Taskee $taskee, $task_id = null): JsonResponse
    {

        if ($task_id == null) {
            $data['tasks'] = [];
            $task_solution = $taskee->task_solutions()->getValidTasks()->where('task_solutions.status', 'Chưa nộp')->latest()->paginate(request()->query('per_page') ?? 10);
            foreach ($task_solution as $task_sol) {
                $data['tasks'][] = SolutionResponse::task($task_sol);
            }
            $data['total'] = $task_solution->total();
            $data['current_page'] = $task_solution->currentPage();
            $data['per_page'] = $task_solution->perPage();
            $data['last_page'] = $task_solution->lastPage();
            return ApiResponse::OK($data);
        } else {
            $task_solution = $taskee->task_solutions->where('task_id', $task_id)->first();
            if ($task_solution) {
                $data = SolutionResponse::task($task_solution);
                return ApiResponse::OK($data);
            }
        }
        return ApiResponse::NOT_FOUND('No solutions found');
    }
    public function taskeeGetTaskSolutionSubmitted(Taskee $taskee, $task_id = null): JsonResponse
    {

        if ($task_id == null) {
            $data['tasks'] = [];
            $task_solution = $taskee->task_solutions()->getValidTasks()->whereNot('task_solutions.status', 'Chưa nộp')->latest()->paginate(request()->query('per_page') ?? 10);
            foreach ($task_solution as $task_sol) {
                $data['tasks'][] = SolutionResponse::task($task_sol);
            }
            $data['total'] = $task_solution->total();
            $data['current_page'] = $task_solution->currentPage();
            $data['per_page'] = $task_solution->perPage();
            $data['last_page'] = $task_solution->lastPage();
            return ApiResponse::OK($data);
        } else {
            $task_solution = $taskee->task_solutions->where('task_id', $task_id)->first();
            if ($task_solution) {
                $data = SolutionResponse::task($task_solution);
                return ApiResponse::OK($data);
            }
        }
        return ApiResponse::NOT_FOUND('No solutions found');
    }
    public function changeStatus(Tasker $tasker, $id, $status)
    {
        $taskSolution = TaskSolution::whereNot('status', 'Chưa nộp')->findOrFail($id);
        if ($taskSolution->task->tasker_id == $tasker->id) {
            if ($status != $taskSolution->status) {
                $taskSolution->update(['status' => $status]);
                MailService::changeStatus($taskSolution->taskee->user, $tasker->user, $status, $taskSolution);
                return ApiResponse::OK();
            } else return ApiResponse::OK();
        } else return ApiResponse::FORBIDDEN('You are not allowed to change status this task solution');
    }

    public function deleteTaskSolution(Tasker $tasker, $solution_id)
    {
        $task_slt = TaskSolution::findOrFail($solution_id);
        if ($task_slt->task->tasker_id == $tasker->id) {
            $task_slt->delete();
            return ApiResponse::OK();
        } else return ApiResponse::FORBIDDEN('You are not allowed to delete this task solution');
    }

    public function getTaskSolutionByTaskId($id)
    {
        $task = Task::findOrFail($id);
        $taskSolutions = $task->solutions()->whereNotNull('submitted_at')->paginate(request()->get('per_page', 10));
        $data['solutions'] = [];
        foreach ($taskSolutions as $taskSolution) {
            $data['solutions'][] = SolutionResponse::task($taskSolution);
        }
        $data['total'] = $taskSolutions->total();
        $data['current_page'] = $taskSolutions->currentPage();
        $data['per_page'] = $taskSolutions->perPage();
        $data['last_page'] = $taskSolutions->lastPage();
        return ApiResponse::OK($data);
    }
}
