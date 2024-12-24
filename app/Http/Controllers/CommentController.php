<?php

namespace App\Http\Controllers;

use App\Services\CommentService;
use App\Http\Response\ApiResponse;

class CommentController extends Controller
{
    private $comments;
    public function __construct(CommentService $commentService)
    {
        $this->comments = $commentService;
    }

    public function getComments($id)
    {
        $comment = $this->comments->getComments($id);
        return $comment;
    }
    public function getReply($id)
    {
        $comment = $this->comments->getReply($id);
        return $comment;
    }
    /**
     * Display a listing of the resource.
     */
    public function create()
    {
        $val = $this->validator(request()->post(), [
            'content' => 'required|string|max:255',
            'challenge_solution_id' => 'required|exists:challenge_solutions,id',
            'parent_id' => 'nullable|exists:comments,id',
        ]);
        if (array_key_exists('error', $val)) {
            return ApiResponse::BAD_REQUEST($val['error']);
        }
        $comment = $this->comments->challenge($val['content'], auth()->guard()->user()->taskee, $val['challenge_solution_id'], $val['parent_id'] ?? null);
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
        $result = $this->comments->edit($id, $val['content'], auth()->guard()->user()->taskee);
        if ($result) {
            return ApiResponse::OK(null, 'Comment edited successfully');
        } else {
            return ApiResponse::FORBIDDEN('You are not authorized to edit this comment');
        }
    }
    public function remove($id)
    {
        $result = $this->comments->remove($id, auth()->guard()->user()->taskee);
        if ($result) {
            return ApiResponse::OK(null, 'Comment removed successfully');
        } else {
            return ApiResponse::FORBIDDEN('You are not authorized to remove this comment');
        }
    }
    public function adminGetComment()
    {
        $comment = $this->comments->adminGetComment();
        return $comment;
    }
    public function adminRemove($id)
    {
        $this->comments->adminRemove($id);
        return ApiResponse::OK();
    }
}
