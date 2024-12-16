<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory, HasUuids;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'taskee_id',
        'task_id',
        'reason',
    ];

    public function taskee()
    {
        return $this->belongsTo(Taskee::class);
    }
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
