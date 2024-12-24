<?php

namespace App\Models;

use App\Scopes\IsRemove;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskComment extends Model
{
    use HasFactory, HasUuids;
    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'user_id',
        'task_solution_id',
        'comment_id',
        'parent_id',
        'content',
        'left',
        'right',
        'is_edit',
        'is_remove',
    ];
    protected static function booted()
    {
        static::addGlobalScope(new IsRemove());
    }
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function task_solution()
    {
        return $this->belongsTo(TaskSolution::class);
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    public function parent()
    {
        return $this->belongsTo(TaskComment::class, 'parent_id');
    }
    public function replies()
    {
        return $this->hasMany(TaskComment::class, 'parent_id')->with('replies');
    }
    public static function countParent($task_solution_id)
    {
        return TaskComment::where('task_solution_id', $task_solution_id)->whereNull('parent_id')->count();
    }
}
