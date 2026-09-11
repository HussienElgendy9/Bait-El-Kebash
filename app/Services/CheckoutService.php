<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public static function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*' => ['required', 'array:product_id,quantity'],
            'items.*.product_id' => ['required', 'integer', 'min:1', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }

    public function create(User $user, array $items, ?string $key = null): Order
    {
        Validator::make(['items' => $items], self::rules())->validate();
        $items = array_map(fn ($item) => ['product_id' => (int) $item['product_id'], 'quantity' => (int) $item['quantity']], $items);
        usort($items, fn ($a, $b) => $a['product_id'] <=> $b['product_id']);
        $hash = hash('sha256', json_encode($items, JSON_THROW_ON_ERROR));
        $key = $key === null ? null : hash('sha256', $key);

        return DB::transaction(function () use ($user, $items, $key, $hash) {
            // PHP <8.4 SQLite ignores transaction_mode; acquire the writer before reading.
            if (DB::getDriverName() === 'sqlite') {
                DB::table('users')->where('id', $user->id)->update(['id' => DB::raw('id')]);
            }
            // A stable per-customer lock serializes concurrent retries even before an order exists.
            $customer = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($key !== null && $existing = $customer->orders()->where('idempotency_key', $key)->first()) {
                abort_unless(hash_equals($existing->request_hash, $hash), 409, 'Idempotency key was used with different items.');

                return $existing->load('orderitems');
            }
            $products = Product::whereIn('id', array_column($items, 'product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            if ($products->count() !== count($items)) {
                throw ValidationException::withMessages(['items' => ['A product is no longer available.']]);
            }
            $order = new Order(['user_id' => $customer->id, 'status' => 'pending']);
            $order->forceFill([
                'delivery_name' => $customer->name, 'delivery_phone' => $customer->phone_number,
                'delivery_address' => $customer->address, 'idempotency_key' => $key, 'request_hash' => $hash,
            ])->save();
            foreach ($items as $item) {
                $product = $products[$item['product_id']];
                $order->orderitems()->create([
                    'product_id' => $product->id, 'quantity' => $item['quantity'],
                    'unit_price' => $product->unit_price,
                    'price_snapshot' => Money::decimal(Money::cents($product->unit_price) * $item['quantity']),
                    'product_name' => $product->name, 'product_unit' => $product->unit,
                ]);
            }
            DB::afterCommit(fn () => app(OrderNotifier::class)->send($order->id));

            return $order->load('orderitems');
        }, 3);
    }
}
