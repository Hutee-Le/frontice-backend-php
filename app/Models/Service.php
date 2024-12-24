<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'name',
        'price',
        'type',
    ];
    public function taskees()
    {
        return $this->belongsToMany(Taskee::class, 'subscriptions', 'service_id', 'taskee_id');
    }
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    public function getCreatedAtAttribute($value)
    {
        return strtotime($value);
    }

    // Định dạng updated_at thành timestamp
    public function getUpdatedAtAttribute($value)
    {
        return strtotime($value);
    }
}
