<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory, HasUuids;
    protected $keyType = 'string';
    protected $fillable = [
        'taskee_id',
        'service_id',
        'discount_id',
        'order_id',
        'transaction_id',
        'expired',
        'gold_expired',
        'amount_paid',
        'payment_method',
        'status',
    ];
    public function taskee()
    {
        return $this->belongsTo(Taskee::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function discount()
    {
        return $this->belongsTo(Discount::class);
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
