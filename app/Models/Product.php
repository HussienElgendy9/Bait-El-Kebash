<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $casts = ['unit_price' => 'decimal:2'];

    protected static function booted(): void
    {
        static::deleting(fn (Product $product) => abort_if($product->orderitem()->exists(), 409, 'Products in order history cannot be deleted.'));
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'unit_price',
        'image',
        'unit',
    ];

    // public function product()
    // {
    //     return $this->belongsTo(Product::class);
    // }
    public function orderitem()
    {
        return $this->hasmany(OrderItem::class);
    }
}
