<?php

namespace App\Http\Response;

use App\Models\Task;
use App\Models\Taskee;
use App\Services\FirebaseService;
use Carbon\Carbon;

class TaskResponse
{
    private $firebase;
    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebase = $firebaseService;
    }
    public function task(Task $task)
    {
        $tasker = $task->tasker;
        return [
            'id' => $task->id,
            'title' => $task->title,
            'owner' => UserResponse::tasker($tasker),
            'technical' => $task->technical['technical'],
            'image' => str_starts_with($task->image, 'http') ? $task->image : $this->firebase->sign($task->image),
            'requiredPoint' => $task->required_point,
            'shortDes' => $task->short_des,
            'longDes' => $task->desc,
            'joinTotal' => $task->joinCount(),
            'submittedTotal' => $task->submittedCount(),
            'expiredAt' => strtotime($task->expired),
            'created_at' => strtotime($task->created_at),
            'updated_at' => strtotime($task->updated_at),
        ];
    }
    public static function taskReport(Task $task)
    {
        $firebase = new FirebaseService();
        $tasker = $task->tasker;
        $reports = $task->reports->count();
        return [
            'id' => $task->id,
            'title' => $task->title,
            'owner' => UserResponse::tasker($tasker),
            'technical' => $task->technical['technical'],
            'image' => str_starts_with($task->image, 'http') ? $task->image : $firebase->sign($task->image),
            'requiredPoint' => $task->required_point,
            'shortDes' => $task->short_des,
            'expiredAt' => strtotime($task->expired),
            'created_at' => strtotime($task->created_at),
            'updated_at' => strtotime($task->updated_at),
            'reports' => $reports,
        ];
    }
    public static function taskReportDetail(Task $task)
    {
        $firebase = new FirebaseService();
        $tasker = $task->tasker;
        $reports = $task->reports;
        $reports_data = [];
        if ($reports && $task->status !== 'valid') {
            foreach ($reports as $report) {
                $reports_data[] = [
                    'id' => $report->id,
                    'reportBy' => UserResponse::taskee($report->taskee),
                    'reason' => $report->reason,
                    'created_at' => strtotime($report->created_at),
                    'updated_at' => strtotime($report->updated_at),
                ];
            }
        }
        return [
            'id' => $task->id,
            'title' => $task->title,
            'owner' => UserResponse::tasker($tasker),
            'technical' => $task->technical['technical'],
            'image' => str_starts_with($task->image, 'http') ? $task->image : $firebase->sign($task->image),
            'sourceLink' => $firebase->sign($task->source, new \DateTime('+30 minutes')),
            'figmaLink' => $firebase->sign($task->figma, new \DateTime('+30 minutes')),
            'requiredPoint' => $task->required_point,
            'shortDes' => $task->short_des,
            'longDes' => $task->desc,
            'expiredAt' => strtotime($task->expired),
            'created_at' => strtotime($task->created_at),
            'updated_at' => strtotime($task->updated_at),
            'totalTasker' => $task->tasker->count(),
            'reports' => $reports_data,
            'status' => $task->status
        ];
    }
    public static function tasks(Task $task, $taskee_point)
    {
        $firebase = new FirebaseService();
        $tasker = $task->tasker;
        return [
            'id' => $task->id,
            'title' => $task->title,
            'owner' => UserResponse::tasker($tasker),
            'technical' =>  $task->technical['technical'],
            'image' => str_starts_with($task->image, 'http') ? $task->image : $firebase->sign($task->image),
            'requiredPoint' => $task->required_point,
            'enoughPoint' => $taskee_point >= $task->required_point ? true : false,
            'shortDes' => $task->short_des,
            'expiredAt' => strtotime($task->expired),
            'created_at' => strtotime($task->created_at),
            'updated_at' => strtotime($task->updated_at),
        ];
    }
    public static function taskDetail(Task $task, Taskee $taskee)
    {
        $firebase = new FirebaseService();
        $tasker = $task->tasker;
        $isJoin = $taskee->task_solutions()->where('task_id', $task->id)->exists();
        $isSubmit = $taskee->task_solutions()->where('task_id', $task->id)->whereNotNull('submitted_at')->exists();
        $isReport = $taskee->reports()->where('task_id', $task->id)->exists();
        $solutionSubmitID = null;
        if ($isSubmit) {
            $solution = $taskee->task_solutions()->where('task_id', $task->id)->first();
            $solutionSubmitID = $solution->id;
        }
        return [
            'id' => $task->id,
            'title' => $task->title,
            'owner' => UserResponse::tasker($tasker),
            'technical' => $task->technical['technical'],
            'image' => str_starts_with($task->image, 'http') ? $task->image : $firebase->sign($task->image),
            'requiredPoint' => $task->required_point,
            'enoughPoint' => $taskee->points() >= $task->required_point ? true : false,
            'shortDes' => $task->short_des,
            'longDes' => $task->desc,
            'joinTotal' => $task->joinCount(),
            'submittedTotal' => $task->submittedCount(),
            'isJoin' => $isJoin,
            'isSubmit' => $isSubmit,
            'isReport' => $isReport,
            'solutionSubmitId' => $solutionSubmitID,
            'expiredAt' => strtotime($task->expired),
            'created_at' => strtotime($task->created_at),
            'updated_at' => strtotime($task->updated_at),
        ];
    }
    public static function taskForSolution(Task $task)
    {
        $firebase = new FirebaseService();
        return [
            'id' => $task->id,
            'title' => $task->title,
            'owner' => UserResponse::tasker($task->tasker),
            'technical' => $task->technical['technical'],
            'image' => $firebase->sign($task->image),
            'requiredPoint' => $task->required_point,
        ];
    }
    public function downloadSource(Task $task)
    {
        $figma = null;
        if ($task->figma) {
            $figma = $this->firebase->sign($task['figma'], new \DateTime('+3 minutes'));
        }
        return [
            'sourceLink' => $this->firebase->sign($task->source, new \DateTime('+3 minutes')),
        ];
    }
    public function downloadFigma(Task $task)
    {
        $figma = null;
        if ($task->figma) {
            $figma = $this->firebase->sign($task['figma'], new \DateTime('+3 minutes'));
        }
        return [
            'figmaLink' => $figma,
        ];
    }
}
