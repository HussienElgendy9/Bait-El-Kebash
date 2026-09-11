<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $casts = ['quantity' => 'integer', 'unit_price' => 'decimal:2', 'price_snapshot' => 'decimal:2'];

    //
    protected $fillable = [
        'order_id',
        'product_id',
        'unit_price',
        'quantity',
        'price_snapshot',
        'product_name',
        'product_unit',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
