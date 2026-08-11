<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $fillable = [
        'user_id',
        'status',
    ];
    public function orderitems(){
        return $this->hasmany(OrderItem::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
