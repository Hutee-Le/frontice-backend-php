<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Discount extends Model
{
    use HasFactory, HasUuids;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'code',
        'usage_limit',
        'value',
        'expired'
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    public function usable()
    {
        return ($this->usage_limit > $this->subscriptions()->whereNot('status', 'fail')->count() && Carbon::now()->lessThan(Carbon::parse($this->expired)));
    }
}
