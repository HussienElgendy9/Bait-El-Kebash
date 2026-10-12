<?php

namespace Tests\Feature\Api;

use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
    }

    public function test_store_lists_products_and_categories(): void
    {
        $this->getJson('/api/products')->assertOk()->assertJsonCount(10, 'data');
        $this->getJson('/api/categories')->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_store_filters_by_category_and_price(): void
    {
        $this->getJson('/api/products?category_id=3')->assertJsonCount(3, 'data');
        $this->getJson('/api/products?min_price=10000&max_price=15000')->assertJsonCount(2, 'data');
        $this->getJson('/api/products?category_id=999')->assertJsonValidationErrors('category_id');
    }

    public function test_product_seeder_can_run_twice(): void
    {
        $this->seed(ProductSeeder::class);

        $this->getJson('/api/products')->assertJsonCount(10, 'data');
    }
}
