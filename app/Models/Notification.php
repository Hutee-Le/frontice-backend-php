<?php

namespace App\Models;

use App\Traits\CustomPaginatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory, CustomPaginatorTrait;
    protected $fillable = [
        'from',
        'to',
        'comment_id',
        'challenge_solution_id',
        'task_id',
        'task_solution_id',
        'type',
        'message',
        'status',
    ];
    public function toUser()
    {
        return $this->belongsTo(User::class, 'to');
    }
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from');
    }
    public function comment()
    {
        return $this->belongsTo(TaskComment::class);
    }
    public function challenge_solution()
    {
        return $this->belongsTo(ChallengeSolution::class);
    }
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    public function task_solution()
    {
        return $this->belongsTo(TaskSolution::class);
    }
}
