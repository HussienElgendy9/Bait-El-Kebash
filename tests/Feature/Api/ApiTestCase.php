<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('sqlite', config('database.default'));
        Notification::fake();
        config(['sanctum.stateful' => ['localhost:5173'], 'api.mail_enabled' => false]);
    }

    protected function product(array $data = []): Product
    {
        return Product::create($data + ['name' => 'Lamb', 'unit_price' => '10.10', 'unit' => '0.5kg', 'category_id' => Category::firstOrCreate(['name' => 'Meat'])->id]);
    }

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    protected function spa(): static
    {
        return $this->withHeader('Origin', 'http://localhost:5173');
    }
}
