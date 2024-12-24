<?php

namespace App\Models;

use App\Http\Response\SolutionResponse;
use App\Traits\CustomPaginatorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskSolution extends Model
{
    use HasFactory, HasUuids, CustomPaginatorTrait;
    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'title',
        'task_id',
        'taskee_id',
        'status',
        'submitted_at',
        'github',
        'live_github',
    ];
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }
    public function taskee()
    {
        return $this->belongsTo(Taskee::class);
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    public function scopeGetValidTasks($query)
    {
        $query->join('tasks', 'task_solutions.task_id', '=', 'tasks.id')->where(function ($query) {
            $query->where('tasks.status', '!=', 'deleted')
                ->orWhereNull('tasks.status');
        })->select('task_solutions.*');
    }
    public static function getAll(
        $status = null,
        $taskee_id = null,
        $task_id = null,
        $submitted = true
    ) {
        // Khởi tạo query
        $query = self::query();

        // Lọc theo 'status' nếu được cung cấp
        if (!is_null($status)) {
            $query->where('status', $status);
        }

        // Lọc theo 'taskee_id' nếu được cung cấp
        if (!is_null($taskee_id)) {
            $query->where('taskee_id', $taskee_id);
        }

        // Lọc theo 'task_id' nếu được cung cấp
        if (!is_null($task_id)) {
            $query->where('task_id', $task_id);
        }

        // Lọc theo 'submitted_at' nếu yêu cầu chỉ lấy các bài đã nộp
        if ($submitted) {
            $query->whereNotNull('submitted_at');
        }

        // Phân trang kết quả
        $results = self::customSolutionPaginate(request(), $query);
        $data['solutions'] = [];

        // Định dạng dữ liệu trả về
        foreach ($results as $result) {
            $data['solutions'][] = SolutionResponse::task($result);
        }

        $data['total'] = $results->total();
        $data['currentPage'] = $results->currentPage();
        $data['lastPage'] = $results->lastPage();
        $data['perPage'] = $results->perPage();

        return $data;
    }
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($taskSolution) {
            foreach ($taskSolution->comments as $comment) {
                $comment->delete();
            }
        });
    }
}
