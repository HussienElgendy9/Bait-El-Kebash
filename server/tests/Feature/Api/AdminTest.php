<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminTest extends TestCase
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

    private function order(Product $product): Order
    {
        $order = Order::create([
            'user_id' => User::factory()->create()->id,
            'status' => 'pending',
            'address' => 'القاهرة',
            'phone_number' => '01000000000',
        ]);
        $order->orderItems()->create([
            'product_id' => $product->id,
            'unit_price' => $product->unit_price,
            'quantity' => 1,
            'price_snapshot' => $product->unit_price,
        ]);

        return $order;
    }

    public function test_customers_cannot_use_admin_routes(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/admin/dashboard')->assertForbidden();
        $this->getJson('/api/admin/orders')->assertForbidden();
        $this->postJson('/api/admin/products', [])->assertForbidden();
    }

    public function test_admin_pages_load(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $this->order($this->product());

        foreach (['dashboard', 'products', 'categories', 'orders', 'customers', 'admins'] as $page) {
            $this->getJson("/api/admin/{$page}")->assertOk();
        }
    }

    public function test_admin_can_create_and_update_a_product(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->admin()->create());
        $categoryId = Category::create(['name' => 'ماعز'])->id;

        $id = $this->postJson('/api/admin/products', [
            'name' => 'ماعز شامي',
            'category_id' => $categoryId,
            'unit_price' => 14200,
            // A real 1x1 PNG, so the test doesn't need PHP's GD extension.
            'image' => UploadedFile::fake()->createWithContent('goat.png', base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='
            )),
        ])->assertCreated()->assertJsonPath('product.is_available', true)->json('product.id');

        $this->assertNull(Product::find($id)->description);

        $this->patchJson("/api/admin/products/{$id}", ['is_available' => false])
            ->assertOk()
            ->assertJsonPath('product.is_available', false);
    }

    public function test_product_with_orders_cannot_be_deleted(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $ordered = $this->product();
        $this->order($ordered);
        $unused = $this->product(['name' => 'جديد']);

        $this->deleteJson("/api/admin/products/{$ordered->id}")->assertStatus(422);
        $this->deleteJson("/api/admin/products/{$unused->id}")->assertOk();
        $this->assertModelExists($ordered);
        $this->assertModelMissing($unused);
    }

    public function test_order_status_follows_allowed_transitions(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $order = $this->order($this->product());

        $this->patchJson("/api/admin/orders/{$order->id}", ['status' => 'completed'])->assertStatus(422);
        $this->patchJson("/api/admin/orders/{$order->id}", ['status' => 'processing'])->assertOk();
        $this->patchJson("/api/admin/orders/{$order->id}", ['status' => 'completed'])->assertOk();
        $this->patchJson("/api/admin/orders/{$order->id}", ['status' => 'cancelled'])->assertStatus(422);
    }

    public function test_admin_filters(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $order = $this->order($this->product());
        $order->update(['status' => 'processing']);
        $this->order($this->product(['name' => 'آخر']));
        User::factory()->create(['name' => 'Mona Ali']);

        $this->getJson('/api/admin/orders?status=processing')->assertJsonCount(1, 'data');
        $this->getJson('/api/admin/orders?status=banana')->assertJsonValidationErrors('status');
        $this->getJson('/api/admin/customers?search=Mona')->assertJsonCount(1, 'data');
        $this->getJson('/api/admin/products?search=آخر')->assertJsonCount(1, 'data');
    }

    public function test_only_super_admin_changes_roles_and_cannot_be_demoted(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $customer = User::factory()->create();

        Sanctum::actingAs(User::factory()->admin()->create());
        $this->patchJson("/api/admin/users/{$customer->id}/role", ['role' => 'admin'])->assertForbidden();

        Sanctum::actingAs($superAdmin);
        $this->patchJson("/api/admin/users/{$customer->id}/role", ['role' => 'admin'])->assertOk();
        $this->patchJson("/api/admin/users/{$customer->id}/role", ['role' => 'super_admin'])->assertJsonValidationErrors('role');
        $this->patchJson("/api/admin/users/{$superAdmin->id}/role", ['role' => 'customer'])->assertForbidden();

        $this->assertSame('admin', $customer->fresh()->role);
        $this->assertSame('super_admin', $superAdmin->fresh()->role);
    }
}
