<?php

namespace App\Models;

use App\Traits\CustomPaginatorTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory, HasUuids, CustomPaginatorTrait;
    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;
    protected $fillable = ['id', 'fullname', 'role', 'first_login'];
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
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }
    public function challenges()
    {
        return $this->hasMany(Challenge::class, 'admin_id', 'id');
    }
    public function solutions()
    {
        return $this->hasMany(ChallengeSolution::class);
    }
}
