<?php

namespace App\Models;

use App\Traits\CustomPaginatorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tasker extends Model
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
        'firstname',
        'lastname',
        'phone',
        'company',
        'bio',
        'is_approved',
        'admin_id'
    ];
    protected $table = 'taskers';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Thực hiện hành động trước khi tạo model
        });

        static::updating(function ($model) {
            // Thực hiện hành động trước khi cập nhật model
            if ($model->isDirty('id')) {
                $model->id = $model->getOriginal('id');
            }
        });
        static::deleting(function ($tasker) {
            foreach ($tasker->tasks as $task) {
                $task->delete();
            }
        });
    }
    public function taskees()
    {
        return $this->belongsToMany(Taskee::class, 'followers', 'tasker_id', 'taskee_id');
    }
    public function followerCount()
    {
        return $this->taskees()->count();
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
