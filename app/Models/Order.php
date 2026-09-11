<?php

namespace App\Models;

use App\Services\Money;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $hidden = ['idempotency_key', 'request_hash'];

    public function total(): string
    {
        return Money::decimal($this->orderitems->sum(fn ($item) => Money::cents($item->price_snapshot)));
    }

    //
    protected $fillable = [
        'user_id',
        'status',
    ];

    public function orderitems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
