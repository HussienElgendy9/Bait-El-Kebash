<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected static function booted(): void
    {
        static::deleting(fn (Category $category) => abort_if($category->products()->exists(), 409, 'Categories containing products cannot be deleted.'));
    }

    //
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected $fillable = [
        'name',
    ];
}
