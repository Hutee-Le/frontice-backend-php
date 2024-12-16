<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Interaction extends Model
{
    use HasFactory, HasUlids;
    protected $fillable = ['taskee_id', 'challenge_solution_id', 'type'];

    public $incrementing = false;
    protected $keyType = 'string';


    public function challenge_solution()
    {
        return $this->belongsTo(ChallengeSolution::class);
    }
    public function taskee()
    {
        return $this->belongsTo(Taskee::class);
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
