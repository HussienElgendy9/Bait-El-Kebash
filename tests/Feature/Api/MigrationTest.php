<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    private string $previous;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previous = DB::getDefaultConnection();
        config(['database.connections.migration_audit' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true]]);
        DB::setDefaultConnection('migration_audit');
        foreach (glob(database_path('migrations/*.php')) as $file) {
            if (! str_contains($file, '100000_harden_api_data')) {
                (require $file)->up();
            }
        }
    }

    protected function tearDown(): void
    {
        DB::purge('migration_audit');
        DB::setDefaultConnection($this->previous);
        parent::tearDown();
    }

    private function user(string $email, string $phone): int
    {
        return DB::table('users')->insertGetId(['name' => 'Historical user', 'email' => $email, 'phone_number' => $phone, 'address' => 'Current address', 'password' => 'unused']);
    }

    private function migrate(): void
    {
        (require database_path('migrations/2026_09_11_100000_harden_api_data.php'))->up();
    }

    public function test_duplicate_phones_stop_before_schema_changes(): void
    {
        $this->user('one@example.test', '01012345678');
        $this->user('two@example.test', '01012345678');
        try {
            $this->migrate();
            $this->fail('Expected duplicate preflight failure');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Duplicate user phone', $e->getMessage());
        }
        $this->assertFalse(Schema::hasColumn('orders', 'delivery_name'));
        $this->assertSame(2, DB::table('users')->count());
    }

    public function test_historical_data_survives_without_fabricated_snapshots_and_plaintext_is_invalidated(): void
    {
        $user = $this->user('one@example.test', '01012345678');
        $category = DB::table('categories')->insertGetId(['name' => 'Meat']);
        $product = DB::table('products')->insertGetId(['category_id' => $category, 'name' => 'Current name', 'unit_price' => '99.99']);
        $order = DB::table('orders')->insertGetId(['user_id' => $user, 'status' => 'completed']);
        DB::table('order_items')->insert(['order_id' => $order, 'product_id' => $product, 'quantity' => '3', 'unit_price' => '10.10', 'price_snapshot' => '30.30']);
        DB::table('phone_verifications')->insert(['user_id' => $user, 'phone_number' => '01112345678', 'code' => '123456', 'expires_at' => now()->addMinutes(10)]);
        $this->migrate();
        $this->assertNull(DB::table('orders')->first()->delivery_name);
        $this->assertNull(DB::table('order_items')->first()->product_name);
        $this->assertSame(3, (int) DB::table('order_items')->first()->quantity);
        $this->assertSame('30.30', number_format(DB::table('order_items')->first()->price_snapshot, 2, '.', ''));
        $this->assertSame('', DB::table('phone_verifications')->first()->code);
        $this->assertNotNull(DB::table('phone_verifications')->first()->invalidated_at);
    }

    public function test_invalid_historical_quantity_is_not_silently_rounded(): void
    {
        $user = $this->user('one@example.test', '01012345678');
        $category = DB::table('categories')->insertGetId(['name' => 'Meat']);
        $product = DB::table('products')->insertGetId(['category_id' => $category, 'name' => 'Lamb', 'unit_price' => '10.00']);
        $order = DB::table('orders')->insertGetId(['user_id' => $user, 'status' => 'pending']);
        DB::table('order_items')->insert(['order_id' => $order, 'product_id' => $product, 'quantity' => '1.5', 'unit_price' => '10.00', 'price_snapshot' => '15.00']);
        try {
            $this->migrate();
            $this->fail('Expected quantity preflight failure');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Invalid historical order item', $e->getMessage());
        }
        $this->assertFalse(Schema::hasColumn('orders', 'delivery_name'));
        $this->assertSame('1.5', DB::table('order_items')->first()->quantity);
    }
}
