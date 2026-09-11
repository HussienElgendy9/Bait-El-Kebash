<?php

namespace Tests\Feature\Api;

use App\Models\OrderItem;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Services\CheckoutService;
use App\Services\OrderEditor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CheckoutTest extends ApiTestCase
{
    public function test_retry_still_returns_order_after_admin_replaces_and_deletes_original_product(): void
    {
        $customer = User::factory()->create();
        $original = $this->product();
        $replacement = $this->product(['name' => 'Replacement']);
        $body = ['items' => [['product_id' => $original->id, 'quantity' => 1]]];
        $order = app(CheckoutService::class)->create($customer, $body['items'], 'historical-retry');
        app(OrderEditor::class)->updateItem($order, $order->orderitems->first()->id, ['product_id' => $replacement->id, 'quantity' => 1]);
        $original->delete();
        $this->actingAs($customer)->withHeader('Idempotency-Key', 'historical-retry')->postJson('/api/v1/orders', $body)->assertOk()->assertJsonPath('data.id', $order->id);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_exact_totals_snapshots_and_idempotent_retries(): void
    {
        $user = User::factory()->create();
        $product = $this->product();
        $this->actingAs($user)->withHeader('Idempotency-Key', 'checkout-1');
        $body = ['items' => [['product_id' => $product->id, 'quantity' => 3]], 'user_id' => 999, 'status' => 'completed', 'total' => '0.01'];
        $response = $this->postJson('/api/v1/orders', $body)->assertCreated()->assertJsonPath('data.total', '30.30')->assertJsonPath('data.status', 'pending')->assertJsonPath('data.user_id', $user->id)->assertJsonPath('data.items.0.quantity', 3);
        $id = $response->json('data.id');
        $product->update(['name' => 'Changed', 'unit_price' => '99.99', 'unit' => '1kg']);
        $user->update(['address' => 'Changed']);
        $this->postJson('/api/v1/orders', $body)->assertOk()->assertJsonPath('data.id', $id)->assertJsonPath('data.total', '30.30');
        $this->get('/api/v1/orders/'.$id)->assertJsonPath('data.items.0.product_name', 'Lamb')->assertJsonPath('data.items.0.product_unit', '0.5kg')->assertJsonPath('data.delivery.address', $response->json('data.delivery.address'));
        $body['items'][0]['quantity'] = 2;
        $this->postJson('/api/v1/orders', $body)->assertConflict();
        $this->assertDatabaseCount('orders', 1);
        $this->get('/api/v1/orders?per_page=1')->assertJsonPath('meta.total', 1);
        $this->actingAs(User::factory()->create())->get('/api/v1/orders/'.$id)->assertForbidden();
        $this->get('/api/v1/orders')->assertJsonCount(0, 'data');
    }

    public function test_checkout_validation_limits(): void
    {
        $product = $this->product();
        $this->actingAs(User::factory()->create());
        $this->postJson('/api/v1/orders', ['items' => [['product_id' => $product->id, 'quantity' => 1]]])->assertUnprocessable()->assertJsonValidationErrors('idempotency_key');
        $this->withHeader('Idempotency-Key', 'validation');
        foreach ([[], [['product_id' => 999, 'quantity' => 1]], [['product_id' => $product->id, 'quantity' => 0]], [['product_id' => $product->id, 'quantity' => 1.5]], [['product_id' => $product->id, 'quantity' => 1001]], [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 1]], array_fill(0, 2, ['product_id' => $product->id, 'quantity' => 1]), array_fill(0, 101, ['product_id' => $product->id, 'quantity' => 1])] as $items) {
            $this->postJson('/api/v1/orders', ['items' => $items])->assertUnprocessable();
        }
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_line_failure_rolls_back_order_and_all_lines(): void
    {
        $user = User::factory()->create();
        $first = $this->product();
        $second = $this->product(['name' => 'Second']);
        OrderItem::creating(function ($item) use ($second) {
            if ($item->product_id === $second->id) {
                throw new \RuntimeException('Injected line failure');
            }
        });
        try {
            app(CheckoutService::class)->create($user, [['product_id' => $first->id, 'quantity' => 1], ['product_id' => $second->id, 'quantity' => 1]], 'rollback');
            $this->fail('Expected failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('Injected line failure', $e->getMessage());
        } finally {
            OrderItem::flushEventListeners();
        }
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        Notification::assertNothingSent();
    }

    public function test_notifications_run_after_commit_only_once(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create();
        $product = $this->product();
        DB::beginTransaction();
        $order = app(CheckoutService::class)->create($user, [['product_id' => $product->id, 'quantity' => 2]], 'notice');
        Notification::assertNothingSent();
        DB::commit();
        Notification::assertSentTo($admin, NewOrderNotification::class);
        app(CheckoutService::class)->create($user, [['product_id' => $product->id, 'quantity' => 2]], 'notice');
        Notification::assertSentToTimes($admin, NewOrderNotification::class, 1);
    }

    public function test_notification_failure_does_not_fail_committed_checkout(): void
    {
        $this->admin();
        $product = $this->product();
        Notification::shouldReceive('sendNow')->andThrow(new \RuntimeException('Delivery failed'));
        $this->actingAs(User::factory()->create())->withHeader('Idempotency-Key', 'delivery-failure')->postJson('/api/v1/orders', ['items' => [['product_id' => $product->id, 'quantity' => 1]]])->assertCreated();
        $this->assertDatabaseCount('orders', 1);
    }
}
