<?php

namespace App\Http\Response;

use App\Models\ChallengeSolution;
use App\Models\Notification;

class NotificationResponse
{
    public static function notification(Notification $notification)
    {
        $notice = [
            'id' => $notification->id,
            'message' => $notification->message,
            'createdAt' => strtotime($notification->created_at),
            'status' => $notification->status,
            'type' => $notification->type,
        ];
        if ($notification->comment_id) {
            $notice['commentContent'] = $notification->comment->content;
        }
        if ($notification->challenge_solution_id) {
            $notice['challengeSolution'] = $notification->challenge_solution;
        }
        if ($notification->task_id) {
            $notice['taskId'] = $notification->task->id;
        }
        if ($notification->task_solution_id) {
            $notice['taskSolution'] = $notification->task_solution_id;
        }
        if ($notification->from) {
            $notice['from'] = $notification->fromUser->username;
        }
        return $notice;
    }
}
