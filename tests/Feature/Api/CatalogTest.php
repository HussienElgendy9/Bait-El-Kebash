<?php

namespace Tests\Feature\Api;

use App\Models\User;

class CatalogTest extends ApiTestCase
{
    public function test_catalog_pagination_filters_and_explicit_resources(): void
    {
        $product = $this->product();
        $this->product(['name' => 'Beef', 'unit_price' => '20.20']);
        $this->get('/api/v1/products?per_page=1')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('meta.total', 2)->assertJsonStructure(['links', 'meta']);
        $this->get('/api/v1/products?q=Lamb&category_id='.$product->category_id)->assertJsonCount(1, 'data')->assertJsonPath('data.0.unit_price', '10.10')->assertJsonPath('data.0.unit', '0.5kg');
        $this->get('/api/v1/products?sort=price_asc')->assertJsonPath('data.0.id', $product->id);
        $this->get('/api/v1/products/'.$product->id)->assertJsonPath('data.category.name', 'Meat')->assertJsonMissingPath('data.created_at');
        $this->get('/api/v1/categories/'.$product->category_id)->assertOk()->assertJsonMissingPath('data.products');
        $this->get('/api/v1/categories')->assertJsonPath('meta.total', 1);
    }

    public function test_json_errors_without_accept_and_pagination_validation(): void
    {
        foreach (['per_page=101', 'per_page=0', 'page=-1', 'sort=bogus', 'category_id=999'] as $query) {
            $this->get('/api/v1/products?'.$query)->assertUnprocessable()->assertJsonStructure(['message', 'errors']);
        }
        $this->get('/api/v1/products/999')->assertNotFound()->assertJsonStructure(['message']);
        $this->get('/api/v1/missing')->assertNotFound()->assertJsonStructure(['message']);
        $this->get('/api/v1/me')->assertUnauthorized()->assertJsonStructure(['message']);
    }

    public function test_admin_boundaries(): void
    {
        foreach (['products', 'categories', 'orders', 'users', 'dashboard', 'notifications'] as $path) {
            $this->get('/api/v1/admin/'.$path)->assertUnauthorized();
        }
        $this->actingAs(User::factory()->create());
        foreach (['products', 'categories', 'orders', 'users', 'dashboard', 'notifications'] as $path) {
            $this->get('/api/v1/admin/'.$path)->assertForbidden();
        }
        $this->actingAs($this->admin());
        foreach (['products', 'categories', 'orders', 'users', 'dashboard', 'notifications'] as $path) {
            $this->get('/api/v1/admin/'.$path)->assertOk();
        }
    }
}
