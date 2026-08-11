<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function category(){
        return $this->belongsTo(Category::class);
    }
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'unit_price',
        'image'
    ];
    // public function product()
    // {
    //     return $this->belongsTo(Product::class);
    // }
    public function orderitem(){
        return $this->hasmany(OrderItem::class);
    }
}