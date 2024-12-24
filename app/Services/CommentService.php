<?php

namespace App\Services;

use App\Models\User;
use App\Models\Taskee;
use App\Models\Comment;
use App\Models\TaskComment;
use App\Models\Notification;
use App\Models\TaskSolution;
use App\Models\ChallengeSolution;
use App\Http\Response\ApiResponse;
use App\Http\Response\CommentResponse;
use Illuminate\Http\JsonResponse;

class CommentService extends Service
{
    public function adminGetComment()
    {
        $comments = Comment::where('is_remove', false)->with('replies')->paginate(request()->query('per_page') ?? 10);
        $formattedComments = $comments->map(function ($comment) {
            $formattedComment = CommentResponse::comment($comment); // Chuẩn hóa comment gốc
            // Chuẩn hóa replies nếu có
            $formattedComment['replies'] =
                $comment->replies->count();
            return $formattedComment;
        });
        return ApiResponse::OK([
            'comments' => $formattedComments,
            'total' => $comments->total(),
            'current_page' => $comments->currentPage(),
            'per_page' => $comments->perPage(),
            'last_page' => $comments->lastPage(),
        ]);
    }
    public function getComments($challenge_solution_id, $admin = false): JsonResponse
    {
        try {
            ChallengeSolution::findOrFail($challenge_solution_id);
        } catch (\Exception $e) {
            return ApiResponse::NOT_FOUND('Challenge solution not found');
        }
        // Lấy các comment gốc hoặc theo parent_id
        if ($admin) {
            $comments = Comment::where('challenge_solution_id', $challenge_solution_id)->where('is_remove', false)->with('replies')
                ->paginate(request()->query('per_page') ?? 10);
        } else {
            $comments = Comment::where('challenge_solution_id', $challenge_solution_id)->where('is_remove', false)->whereNull('parent_id')
                ->with('replies') // Nạp replies cho mỗi comment
                ->paginate(request()->query('per_page') ?? 10);
        }
        $formattedComments = $comments->map(function ($comment) {
            $formattedComment = CommentResponse::comment($comment); // Chuẩn hóa comment gốc
            // Chuẩn hóa replies nếu có
            $formattedComment['replies'] =
                $comment->replies->count();
            return $formattedComment;
        });
        return ApiResponse::OK([
            'comments' => $formattedComments,
            'total' => $comments->total(),
            'current_page' => $comments->currentPage(),
            'per_page' => $comments->perPage(),
            'last_page' => $comments->lastPage(),
        ]);
    }
    public function getReply($id)
    {
        try {
            Comment::findOrFail($id);
        } catch (\Exception $e) {
            return ApiResponse::NOT_FOUND('Comment parent not found');
        }
        $comments = Comment::where('parent_id', $id)->where('is_remove', false)
            ->with('replies') // Nạp replies cho mỗi comment
            ->paginate(request()->query('per_page') ?? 10);
        $formattedComments = $comments->map(function ($comment) {
            $formattedComment = CommentResponse::comment($comment); // Chuẩn hóa comment gốc

            // Chuẩn hóa replies nếu có
            $formattedComment['replies'] =
                $comment->replies->count();

            return $formattedComment;
        });

        return ApiResponse::OK([
            'comments' => $formattedComments,
            'total' => $comments->total(),
            'current_page' => $comments->currentPage(),
            'per_page' => $comments->perPage(),
            'last_page' => $comments->lastPage(),
        ]);
    }

    public function challenge($content, Taskee $taskee, $challenge_solution_id, $parent_id = null)
    {
        if ($parent_id == null) {
            $new = Comment::create([
                'content' => $content,
                'taskee_id' => $taskee->id,
                'challenge_solution_id' => $challenge_solution_id,
                'comment_id' => (Comment::countParent($challenge_solution_id) + 1) . '.1.2',
                'parent_id' => null,
                'left' => 1,
                'right' => 2,
                'is_edit' => false,
                'is_remove' => false,
            ]);
            Notification::create([
                'from' => $taskee->id,
                'challenge_solution_id' => $challenge_solution_id,
                'to' => ChallengeSolution::findOrFail($challenge_solution_id)->taskee_id,
                'type' => 'Challenge Solution comment',
                'message' => "{$taskee->name} đã bình luận vào Challenge Solution của bạn.",
            ]);
            return CommentResponse::comment($new);
        } else {
            $parent = Comment::findOrFail($parent_id);
            $comment_id = explode('.', $parent->comment_id);
            $key = $comment_id[0];
            $left = $parent->right;
            $right = $parent->right + 1;
            $new = $parent->replies()->create([
                'content' => $content,
                'taskee_id' => $taskee->id,
                'challenge_solution_id' => $challenge_solution_id,
                'comment_id' => "{$key}.{$left}.{$right}",
                'parent_id' => $parent_id,
                'left' => $left,
                'right' => $right,
                'is_edit' => false,
                'is_remove' => false,
            ]);
            $parent->update(['right' => $left + 2]);
            $parent->update(['comment_id' => "{$key}.{$parent->left}.{$parent->right}"]);
            $comments = Comment::where('comment_id', "Like", "{$key}.%")->where('right', '>=', $left)->where('id', '!=', $new->id)->where('id', '!=', $parent->id)->get();
            if ($comments) {
                foreach ($comments as $comment) {
                    $comment->update(['right' => $comment->right + 2]);
                    if ($comment->left >= $left) {
                        $comment->update(['left' => $comment->left + 2]);
                    }
                    $comment->update(['comment_id' => "{$key}.{$comment->left}.{$comment->right}"]);
                }
            }
            Notification::create([
                'from' => $taskee->id,
                'challenge_solution_id' => $challenge_solution_id,
                'to' => ChallengeSolution::findOrFail($challenge_solution_id)->taskee_id,
                'type' => 'Challenge Solution comment',
                'message' => "{$taskee->user->username} đã bình luận vào Challenge Solution của bạn.",
            ]);
            return CommentResponse::comment($new);
        }
        return false;
    }
    public function edit($comment_id, $content, Taskee $taskee)
    {
        $comment = Comment::findOrFail($comment_id);
        if ($comment->taskee_id === $taskee->id && !$comment->is_remove) {
            $comment->update(['content' => $content, 'is_edit' => true]);
            return true;
        }
        return false;
    }
    public function remove($comment_id, Taskee $taskee)
    {
        $comment = Comment::findOrFail($comment_id);

        if (
            $comment->taskee_id === $taskee->id ||
            $comment->challenge_solution_id === $taskee->challenge_solutions()->where('id', $comment->challenge_solution_id)->value('id')
        ) {
            // Gọi hàm đệ quy để xóa comment và các comment con
            $this->removeRecursive($comment);
            return true;
        }

        return false;
    }
    public function adminRemove($comment_id)
    {
        $comment = Comment::findOrFail($comment_id);
        $this->removeRecursive($comment);
        return true;
    }

    private function removeRecursive(Comment $comment)
    {
        // Đánh dấu comment hiện tại là is_remove = true
        $comment->update(['is_remove' => true]);

        // Lấy danh sách các comment con
        $children = Comment::where('parent_id', $comment->id)->get();

        // Đệ quy xử lý từng comment con
        foreach ($children as $child) {
            $this->removeRecursive($child);
        }
    }

    //Task
    public function task($content, User $user, $taskSolutionId, $parent_id = null)
    {
        $taskSolution = TaskSolution::whereNotNull("submitted_at")->findOrFail($taskSolutionId);
        if ($user->id == $taskSolution->taskee_id || $user->id == $taskSolution->task->tasker_id) {
            if ($parent_id == null) {
                $new = TaskComment::create([
                    'content' => $content,
                    'user_id' => $user->id,
                    'task_solution_id' => $taskSolutionId,
                    'comment_id' => (TaskComment::countParent($taskSolutionId) + 1) . '.1.2',
                    'parent_id' => null,
                    'left' => 1,
                    'right' => 2,
                    'is_edit' => false,
                    'is_remove' => false,
                ]);
                $taskeeId = $taskSolution->taskee_id;
                if ($taskeeId !== $user->id) {
                    Notification::create([
                        'from' => $user->id,
                        'task_solution_id' => $taskSolutionId,
                        'to' => TaskSolution::findOrFail($taskSolutionId)->taskee_id,
                        'comment_id' => $new->id,
                        'type' => 'Task Solution comment',
                        'message' => "{$user->username} đã bình luận vào Challenge Solution của bạn.",
                    ]);
                }
                return CommentResponse::task($new);
            } else {
                $parent = TaskComment::findOrFail($parent_id);
                $comment_id = explode('.', $parent->comment_id);
                $key = $comment_id[0];
                $left = $parent->right;
                $right = $parent->right + 1;
                $new = $parent->replies()->create([
                    'content' => $content,
                    'user_id' => $user->id,
                    'task_solution_id' => $taskSolutionId,
                    'comment_id' => "{$key}.{$left}.{$right}",
                    'parent_id' => $parent_id,
                    'left' => $left,
                    'right' => $right,
                    'is_edit' => false,
                    'is_remove' => false,
                ]);
                $parent->update(['right' => $left + 2]);
                $parent->update(['comment_id' => "{$key}.{$parent->left}.{$parent->right}"]);
                $comments = TaskComment::where('comment_id', "Like", "{$key}.%")->where('right', '>=', $left)->where('id', '!=', $new->id)->where('id', '!=', $parent->id)->get();
                if ($comments) {
                    foreach ($comments as $comment) {
                        $comment->update(['right' => $comment->right + 2]);
                        if ($comment->left >= $left) {
                            $comment->update(['left' => $comment->left + 2]);
                        }
                        $comment->update(['comment_id' => "{$key}.{$comment->left}.{$comment->right}"]);
                    }
                }
                $taskeeId = $taskSolution->taskee_id;
                if ($taskeeId !== $user->id) {
                    Notification::create([
                        'from' => $user->id,
                        'task_solution_id' => $taskSolutionId,
                        'to' => TaskSolution::findOrFail($taskSolutionId)->taskee_id,
                        'comment_id' => $new->id,
                        'type' => 'Task Solution comment',
                        'message' => "{$user->username} đã bình luận vào Challenge Solution của bạn.",
                    ]);
                }
                return CommentResponse::task($new);
            }
        }
        return false;
    }
    public function getTaskComments($task_solution_id)
    {
        try {
            $solution = TaskSolution::findOrFail($task_solution_id);
        } catch (\Exception $e) {
            return ApiResponse::NOT_FOUND('Task solution not found');
        }
        if ($solution->taskee_id == auth()->guard()->id() || $solution->task->tasker_id == auth()->guard()->id() || (auth()->guard()->user()->role == 'admin' && (auth()->guard()->user()->admin->role == 'root' || auth()->guard()->user()->admin->role == 'challenge'))) {
            // Lấy các comment gốc hoặc theo parent_id
            $comments = TaskComment::where('task_solution_id', $task_solution_id)->where('is_remove', false)->whereNull('parent_id')
                ->with('replies') // Nạp replies cho mỗi comment
                ->paginate(request()->query('per_page') ?? 10);
            $formattedComments = $comments->map(function ($comment) {
                $formattedComment = CommentResponse::task($comment); // Chuẩn hóa comment gốc
                // Chuẩn hóa replies nếu có
                $formattedComment['replies'] =
                    $comment->replies->count();
                return $formattedComment;
            });
            return ApiResponse::OK([
                'comments' => $formattedComments,
                'total' => $comments->total(),
                'current_page' => $comments->currentPage(),
                'per_page' => $comments->perPage(),
                'last_page' => $comments->lastPage(),
            ]);
        } else {
            return ApiResponse::FORBIDDEN('You are not authorized to view this content.');
        }
    }
    public function getTaskReply($id): JsonResponse
    {
        try {
            $com = TaskComment::findOrFail($id);
            $solution = TaskSolution::findOrFail($com->task_solution_id);
        } catch (\Exception $e) {
            return ApiResponse::NOT_FOUND('Comment parent not found');
        }
        if ($solution->taskee_id == auth()->guard()->id() || $solution->task->tasker_id == auth()->guard()->id()) {
            $comments = Comment::where('parent_id', $id)->where('is_remove', false)
                ->with('replies') // Nạp replies cho mỗi comment
                ->paginate(request()->query('per_page') ?? 10);
            $formattedComments = $comments->map(function ($comment) {
                $formattedComment = CommentResponse::task($comment); // Chuẩn hóa comment gốc

                // Chuẩn hóa replies nếu có
                $formattedComment['replies'] =
                    $formattedComment['replies'] =
                    ($comment->replies->count() > 0);

                return $formattedComment;
            });

            return ApiResponse::OK([
                'comments' => $formattedComments,
                'total' => $comments->total(),
                'current_page' => $comments->currentPage(),
                'per_page' => $comments->perPage(),
                'last_page' => $comments->lastPage(),
            ]);
        } else {
            return ApiResponse::FORBIDDEN('You are not authorized to view this.');
        }
    }
    public function editTaskComment($comment_id, $content, User $user)
    {
        $comment = TaskComment::findOrFail($comment_id);
        if ($comment->user_id === $user->id && !$comment->is_remove) {
            $comment->update(['content' => $content, 'is_edit' => true]);
            return true;
        }
        return false;
    }
    public function removeTaskComment($comment_id, User $user)
    {
        $comment = TaskComment::findOrFail($comment_id);
        if ($comment->user_id === $user->id || $comment->task_solution->tasker_id === $user->id) {
            $this->removeTaskRecursive($comment);
            return true;
        }
        return false;
    }
    private function removeTaskRecursive(TaskComment $comment)
    {
        // Đánh dấu comment hiện tại là is_remove = true
        $comment->update(['is_remove' => true]);

        // Lấy danh sách các comment con
        $children = TaskComment::where('parent_id', $comment->id)->get();

        // Đệ quy xử lý từng comment con
        foreach ($children as $child) {
            $this->removeTaskRecursive($child);
        }
    }

    public function adminGetAllComments()
    {
        $comments = Comment::where('is_remove', false)->with('taskee')->paginate(request()->query('per_page') ?? 10);
        $formattedComments = $comments->map(function ($comment) {
            $formattedComment = CommentResponse::comment($comment);
            return $formattedComment;
        });
        return ApiResponse::OK([
            'comments' => $formattedComments,
            'total' => $comments->total(),
            'current_page' => $comments->currentPage(),
            'per_page' => $comments->perPage(),
            'last_page' => $comments->lastPage(),
        ]);
    }
}
