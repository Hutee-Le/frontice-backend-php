<?php

namespace App\Http\Controllers;

use App\Services\CommentService;
use App\Http\Response\ApiResponse;

class TaskCommentController extends Controller
{
    private $comments;
    public function __construct(CommentService $commentService)
    {
        $this->comments = $commentService;
    }

    public function getComments($id)
    {
        $comment = $this->comments->getTaskComments($id);
        return $comment;
    }
    public function getReplys($id)
    {
        $comment = $this->comments->getTaskReply($id);
        return $comment;
    }
    /**
     * Display a listing of the resource.
     */
    public function create()
    {
        $val = $this->validator(request()->post(), [
            'content' => 'required|string|max:255',
            'task_solution_id' => 'required|exists:task_solutions,id',
            'parent_id' => 'nullable|exists:task_comments,id',
        ]);
        if (array_key_exists('error', $val)) {
            return ApiResponse::BAD_REQUEST($val['error']);
        }
        $comment = $this->comments->task($val['content'], auth()->guard()->user(), $val['task_solution_id'], $val['parent_id'] ?? null);
        if ($comment) {
            return ApiResponse::OK($comment);
        } else {
            return ApiResponse::BAD_REQUEST('Can not Comment');
        }
    }
    public function edit($id)
    {
        $val = $this->validator(request()->post(), [
            'content' => 'required|string|max:255',
        ]);
        if (array_key_exists('error', $val)) {
            return ApiResponse::BAD_REQUEST($val['error']);
        }
        $result = $this->comments->editTaskComment($id, $val['content'], auth()->guard()->user());
        if ($result) {
            return ApiResponse::OK(null, 'Comment edited successfully');
        } else {
            return ApiResponse::FORBIDDEN('You are not authorized to edit this comment');
        }
    }
    public function remove($id)
    {
        $result = $this->comments->removeTaskComment($id, auth()->guard()->user());
        if ($result) {
            return ApiResponse::OK(null, 'Comment removed successfully');
        } else {
            return ApiResponse::FORBIDDEN('You are not authorized to remove this comment');
        }
    }
}
