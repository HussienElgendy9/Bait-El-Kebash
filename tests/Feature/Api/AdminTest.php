<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminTest extends ApiTestCase
{
    public function test_customer_cannot_mutate_admin_resources(): void
    {
        $product = $this->product();
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->postJson('/api/v1/admin/products', [])->assertForbidden();
        $this->patchJson('/api/v1/admin/products/'.$product->id, ['unit_price' => '1.00'])->assertForbidden();
        $this->delete('/api/v1/admin/products/'.$product->id)->assertForbidden();
        $this->postJson('/api/v1/admin/categories', ['name' => 'Forbidden'])->assertForbidden();
        $this->patchJson('/api/v1/admin/users/'.$user->id, ['role' => 'admin'])->assertForbidden();
        $this->assertFalse($user->fresh()->isAdmin());
    }

    public function test_catalog_crud_upload_replacement_removal_and_validation(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        $category = $this->postJson('/api/v1/admin/categories', ['name' => 'Fresh'])->assertCreated()->json('data.id');
        $this->patchJson('/api/v1/admin/categories/'.$category, ['name' => 'Fresh meat'])->assertOk();
        // Actual PNG bytes avoid depending on the optional GD extension.
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/l9sAAAAASUVORK5CYII=');
        $response = $this->post('/api/v1/admin/products', ['name' => 'Lamb', 'category_id' => $category, 'unit_price' => '12.34', 'image' => UploadedFile::fake()->createWithContent('lamb.png', $png)])->assertCreated();
        $id = $response->json('data.id');
        $product = Product::find($id);
        $old = $product->image;
        Storage::disk('public')->assertExists($old);
        $this->post('/api/v1/admin/products/'.$id, ['_method' => 'PATCH', 'image' => UploadedFile::fake()->createWithContent('new.png', $png)])->assertOk();
        Storage::disk('public')->assertMissing($old);
        $this->patchJson('/api/v1/admin/products/'.$id, ['remove_image' => true, 'unit' => '1kg'])->assertOk()->assertJsonPath('data.image_url', null)->assertJsonPath('data.unit', '1kg');
        $this->patchJson('/api/v1/admin/products/'.$id, ['unit_price' => '1.234'])->assertUnprocessable();
        $this->post('/api/v1/admin/products', ['name' => 'Bad', 'category_id' => $category, 'unit_price' => '1', 'image' => UploadedFile::fake()->createWithContent('evil.svg', '<svg></svg>')])->assertUnprocessable();
        $this->delete('/api/v1/admin/categories/'.$category)->assertConflict();
        $this->delete('/api/v1/admin/products/'.$id)->assertNoContent();
        $this->delete('/api/v1/admin/categories/'.$category)->assertNoContent();
    }

    public function test_nested_order_item_ownership_and_historical_price_rules(): void
    {
        $product = $this->product();
        $other = $this->product(['name' => 'Other', 'unit_price' => '20.20']);
        $customer = User::factory()->create();
        $service = app(CheckoutService::class);
        $order = $service->create($customer, [['product_id' => $product->id, 'quantity' => 1]]);
        $foreignOrder = $service->create($customer, [['product_id' => $other->id, 'quantity' => 1]]);
        $item = $order->orderitems->first();
        $this->actingAs($this->admin());
        $this->patchJson('/api/v1/admin/orders/'.$order->id.'/items/'.$foreignOrder->orderitems->first()->id, ['product_id' => $product->id, 'quantity' => 2])->assertNotFound();
        $product->update(['unit_price' => '50.00']);
        $this->patchJson('/api/v1/admin/orders/'.$order->id.'/items/'.$item->id, ['product_id' => $product->id, 'quantity' => 3])->assertOk()->assertJsonPath('data.line_total', '30.30');
        $this->patchJson('/api/v1/admin/orders/'.$order->id.'/items/'.$item->id, ['product_id' => $other->id, 'quantity' => 2])->assertOk()->assertJsonPath('data.line_total', '40.40')->assertJsonPath('data.product_name', 'Other');
        $this->patchJson('/api/v1/admin/orders/'.$order->id, ['status' => 'completed'])->assertOk()->assertJsonPath('data.status', 'completed');
        $this->patchJson('/api/v1/admin/orders/'.$order->id, ['status' => 'paid'])->assertUnprocessable();
        $this->get('/api/v1/admin/orders?status=completed&per_page=1')->assertJsonPath('meta.total', 1);
        $this->get('/api/v1/admin/dashboard')->assertJsonPath('data.completed_orders', 1)->assertJsonCount(6, 'data.sales')->assertJsonPath('data.sales.5.total', '40.40');
    }

    public function test_user_roles_and_protected_deletion_across_api_and_database(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create();
        $this->actingAs($admin);
        $this->patchJson('/api/v1/admin/users/'.$admin->id, ['role' => 'customer'])->assertForbidden();
        $this->patchJson('/api/v1/admin/users/'.$customer->id, ['role' => 'admin'])->assertOk()->assertJsonPath('data.role', 'admin');
        $this->delete('/api/v1/admin/users/'.$customer->id)->assertForbidden();
        $this->patchJson('/api/v1/admin/users/'.$customer->id, ['role' => 'customer'])->assertOk();
        $product = $this->product();
        app(CheckoutService::class)->create($customer, [['product_id' => $product->id, 'quantity' => 1]]);
        $this->delete('/api/v1/admin/users/'.$customer->id)->assertConflict();
        $this->delete('/api/v1/admin/products/'.$product->id)->assertConflict();
        $this->delete('/api/v1/admin/categories/'.$product->category_id)->assertConflict();
        $this->spa()->actingAs($customer)->deleteJson('/api/v1/me', ['password' => 'password'])->assertConflict();
        foreach ([['users', $customer->id], ['products', $product->id], ['categories', $product->category_id]] as [$table,$id]) {
            try {
                DB::table($table)->where('id', $id)->delete();
                $this->fail('Expected FK restriction');
            } catch (QueryException $e) {
                $this->assertStringContainsString('FOREIGN KEY', $e->getMessage());
            }
        }
        $this->assertDatabaseCount('orders', 1);
        $this->actingAs($admin)->delete('/api/v1/admin/users/'.User::factory()->create()->id)->assertNoContent();
    }

    public function test_notification_listing_and_read_operations_are_recipient_scoped(): void
    {
        $admin = $this->admin();
        $other = $this->admin();
        $own = $admin->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'order', 'data' => ['order_id' => 1, 'message' => 'New order']]);
        $foreign = $other->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'order', 'data' => ['order_id' => 2, 'message' => 'New order']]);
        $this->actingAs($admin)->get('/api/v1/admin/notifications?unread=1')->assertJsonCount(1, 'data');
        $this->patch('/api/v1/admin/notifications/'.$foreign->id)->assertNotFound();
        $this->patch('/api/v1/admin/notifications/'.$own->id)->assertOk();
        $this->get('/api/v1/admin/notifications?unread=1')->assertJsonCount(0, 'data');
        $this->post('/api/v1/admin/notifications/read-all')->assertNoContent();
        $this->assertNull($foreign->fresh()->read_at);
    }
}
