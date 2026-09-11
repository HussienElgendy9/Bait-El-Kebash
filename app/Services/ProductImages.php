<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductImages
{
    public function save(Product $product, array $data): Product
    {
        $old = $product->image;
        $new = isset($data['image']) ? $data['image']->store('products', 'public') : null;
        abort_if(isset($data['image']) && ! $new, 503, 'Image storage is unavailable.');
        $remove = $data['remove_image'] ?? false;
        unset($data['image'], $data['remove_image']);
        if ($new || $remove) {
            $data['image'] = $new;
        }
        try {
            $product->fill($data)->save();
        } catch (\Throwable $e) {
            if ($new) {
                Storage::disk('public')->delete($new);
            } throw $e;
        }
        if ($old && ($new || $remove)) {
            Storage::disk('public')->delete($old);
        }

        return $product;
    }

    public function delete(Product $product): void
    {
        $path = $product->image;
        $product->delete();
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
