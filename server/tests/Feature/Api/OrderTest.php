<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attributes = []): Product
    {
        return Product::create($attributes + [
            'category_id' => Category::firstOrCreate(['name' => 'خراف'])->id,
            'name' => 'خروف بلدي',
            'unit_price' => 1000,
            'unit' => 'رأس',
        ]);
    }

    private function placeOrder(array $items)
    {
        return $this->postJson('/api/orders', ['items' => $items]);
    }

    public function test_order_uses_server_prices_and_snapshots_the_address(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($customer = User::factory()->create());
        $sheep = $this->product();
        $goat = $this->product(['name' => 'ماعز', 'unit_price' => 500]);

        $response = $this->placeOrder([
            ['product_id' => $sheep->id, 'quantity' => 2, 'unit_price' => 1], // client price must be ignored
            ['product_id' => $goat->id, 'quantity' => 1],
        ])->assertCreated();

        $this->assertEquals(2500, $response->json('order.total'));
        $this->assertSame($customer->address, $response->json('order.address'));
        $this->assertSame('pending', $response->json('order.status'));
        Notification::assertSentTo($admin, NewOrderNotification::class);

        // Later profile and price changes must not rewrite the order.
        $customer->update(['address' => 'عنوان جديد']);
        $sheep->update(['unit_price' => 9999]);
        $order = Order::with('orderItems')->first();
        $this->assertSame($response->json('order.address'), $order->address);
        $this->assertEquals(2500, $order->orderItems->sum('price_snapshot'));
    }

    public function test_order_rejects_bad_items(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = $this->product();
        $sold = $this->product(['name' => 'مباع', 'is_available' => false]);

        $this->placeOrder([])->assertJsonValidationErrors('items');
        $this->placeOrder([['product_id' => $sold->id, 'quantity' => 1]])->assertJsonValidationErrors('items.0.product_id');
        $this->placeOrder([['product_id' => 999, 'quantity' => 1]])->assertJsonValidationErrors('items.0.product_id');
        $this->placeOrder([['product_id' => $product->id, 'quantity' => 0]])->assertJsonValidationErrors('items.0.quantity');
        $this->placeOrder([
            ['product_id' => $product->id, 'quantity' => 1],
            ['product_id' => $product->id, 'quantity' => 1],
        ])->assertJsonValidationErrors('items.0.product_id');

        $this->assertSame(0, Order::count());
    }

    public function test_customer_sees_only_their_own_orders(): void
    {
        $other = User::factory()->create();
        Sanctum::actingAs($other);
        $othersOrderId = $this->placeOrder([['product_id' => $this->product()->id, 'quantity' => 1]])->json('order.id');

        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/orders')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson("/api/orders/{$othersOrderId}")->assertForbidden();
        $this->postJson("/api/orders/{$othersOrderId}/cancel")->assertForbidden();
    }

    public function test_customer_can_cancel_only_pending_orders(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $orderId = $this->placeOrder([['product_id' => $this->product()->id, 'quantity' => 1]])->json('order.id');

        $this->postJson("/api/orders/{$orderId}/cancel")
            ->assertOk()
            ->assertJsonPath('order.status', 'cancelled');

        $this->postJson("/api/orders/{$orderId}/cancel")->assertStatus(422);
    }
}
