<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\User;

class CompatibilityTest extends ApiTestCase
{
    public function test_mvc_pages_and_checkout_use_server_prices_and_nested_item_ids(): void
    {
        $product = $this->product();
        $user = User::factory()->create();
        $this->get('/')->assertOk();
        $this->get('/store')->assertOk();
        $this->get('/store/product/'.$product->id)->assertOk();
        $this->actingAs($user)->withSession(['cart' => [$product->id => ['id' => $product->id, 'qty' => 3, 'price' => '0.01', 'unit_price' => '0.01']]])->post('/cart/checkout')->assertRedirect(route('store.home'));
        $order = Order::first();
        $this->assertSame('30.30', $order->total());
        $this->actingAs($this->admin());
        foreach (['/admin', '/admin/products', '/admin/categories', '/admin/users', '/admin/orders', '/admin/orders/'.$order->id, '/admin/orders/'.$order->id.'/edit'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->put('/admin/orders/'.$order->id.'/items/'.$order->orderitems->first()->id, ['product_id' => $product->id, 'quantity' => 2, 'status' => 'completed'])->assertRedirect();
        $this->assertSame('20.20', $order->fresh()->total());
    }

    public function test_legacy_checkout_and_phone_routes_preserve_compatibility(): void
    {
        $product = $this->product();
        $user = User::factory()->create();
        $this->withToken($user->createToken('legacy')->plainTextToken)->postJson('/api/orders', ['items' => [['product_id' => $product->id, 'quantity' => 3]]])->assertCreated()->assertJsonPath('order.orderitems.0.price_snapshot', '30.30');
        $this->get('/api/orders')->assertOk();
        $this->get('/api/products')->assertOk();
        $this->get('/api/categories')->assertOk();
    }

    public function test_session_cart_add_change_remove_clear_and_live_quote(): void
    {
        $product = $this->product();
        $this->spa()->actingAs(User::factory()->create());
        $this->get('/api/v1/cart')->assertOk()->assertJsonPath('data.total', '0.00');
        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2])->assertOk()->assertJsonPath('data.total', '20.20');
        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1])->assertOk()->assertJsonPath('data.items.0.quantity', 3);
        $product->update(['unit_price' => '20.00']);
        $this->get('/api/v1/cart')->assertJsonPath('data.total', '60.00');
        $this->patchJson('/api/v1/cart/items/'.$product->id, ['quantity' => 4])->assertOk()->assertJsonPath('data.total', '80.00');
        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1000])->assertUnprocessable();
        $this->delete('/api/v1/cart/items/'.$product->id)->assertOk()->assertJsonCount(0, 'data.items');
        $this->delete('/api/v1/cart')->assertNoContent();
    }
}
