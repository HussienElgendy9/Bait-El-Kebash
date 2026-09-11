<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderEditor
{
    public function updateItem(Order $order, int $itemId, array $data): OrderItem
    {
        return DB::transaction(function () use ($order, $itemId, $data) {
            if (DB::getDriverName() === 'sqlite') {
                DB::table('orders')->where('id', $order->id)->update(['id' => DB::raw('id')]);
            }
            Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $item = $order->orderitems()->whereKey($itemId)->lockForUpdate()->firstOrFail();
            $product = Product::whereKey($data['product_id'])->lockForUpdate()->firstOrFail();
            abort_if($order->orderitems()->where('product_id', $product->id)->where('id', '!=', $item->id)->exists(), 409, 'Product already exists in this order.');
            if ($item->product_id !== $product->id) {
                $item->unit_price = $product->unit_price;
                $item->product_name = $product->name;
                $item->product_unit = $product->unit;
            }
            $item->product_id = $product->id;
            $item->quantity = $data['quantity'];
            $item->price_snapshot = Money::decimal(Money::cents($item->unit_price) * $item->quantity);
            $item->save();

            return $item;
        }, 3);
    }
}
