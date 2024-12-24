<?php

namespace App\Models;

use App\Scopes\IsRemove;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
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
        'taskee_id',
        'challenge_solution_id',
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
    public function solution()
    {
        return $this->belongsTo(ChallengeSolution::class);
    }
    public function taskee()
    {
        return $this->belongsTo(Taskee::class);
    }
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->with('replies');
    }
    public static function countParent($challenge_solution_id)
    {
        return Comment::where('challenge_solution_id', $challenge_solution_id)->whereNull('parent_id')->whereNot('is_remove', true)->count();
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
