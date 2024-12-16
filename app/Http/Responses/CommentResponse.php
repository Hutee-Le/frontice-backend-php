<?php

namespace App\Http\Response;

use App\Models\Comment;
use App\Models\TaskComment;
use App\Services\UserService;

class CommentResponse
{
    public static function comment(Comment $comment)
    {
        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'taskee' => UserService::getTaskeeById($comment->taskee_id),
            'comment_id' => $comment->comment_id,
            'parent_id' => $comment->parent_id,
            'left' => $comment->left,
            'right' => $comment->right,
            'challenge_solution_id' => $comment->challenge_solution_id,
            'is_edit' => $comment->is_edit ? true : false,
            'created_at' => strtotime($comment->created_at),
        ];
    }
    public static function task(TaskComment $comment)
    {
        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'user' => UserService::getTTById($comment->user_id),
            'comment_id' => $comment->comment_id,
            'parent_id' => $comment->parent_id,
            'left' => $comment->left,
            'right' => $comment->right,
            'task_solution_id' => $comment->task_solution_id,
            'is_edit' => $comment->is_edit ? true : false,
            'created_at' => strtotime($comment->created_at),
        ];
    }
}
