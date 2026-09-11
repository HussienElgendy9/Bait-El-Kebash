<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\PhoneVerification;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class ConcurrencyTest extends TestCase
{
    private string $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = storage_path('framework/testing/bait-api-race-'.Str::uuid().'.sqlite');
        if (! is_dir(dirname($this->database))) {
            mkdir(dirname($this->database), 0777, true);
        }
        fclose(fopen($this->database, 'x'));
        config(['database.connections.race' => ['driver' => 'sqlite', 'database' => $this->database, 'prefix' => '', 'foreign_key_constraints' => true, 'busy_timeout' => 5000, 'transaction_mode' => 'IMMEDIATE']]);
        DB::setDefaultConnection('race');
        foreach (glob(database_path('migrations/*.php')) as $file) {
            (require $file)->up();
        }
    }

    protected function tearDown(): void
    {
        DB::purge('race');
        DB::setDefaultConnection('sqlite');
        unlink($this->database);
        parent::tearDown();
    }

    private function race(array $arguments): array
    {
        $workers = array_map(fn ($args) => new Process([PHP_BINARY, base_path('tests/Support/concurrency-worker.php'), $this->database, ...$args], base_path()), $arguments);
        foreach ($workers as $worker) {
            $worker->start();
        }
        $results = [];
        foreach ($workers as $worker) {
            $worker->wait();
            $this->assertTrue($worker->isSuccessful(), $worker->getErrorOutput().$worker->getOutput());
            $this->assertJson($worker->getOutput(), $worker->getOutput());
            $results[] = json_decode($worker->getOutput(), true, 512, JSON_THROW_ON_ERROR);
        }

        return $results;
    }

    public function test_concurrent_identical_checkout_creates_one_order(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Meat']);
        Product::create(['name' => 'Lamb', 'category_id' => $category->id, 'unit_price' => '10.10']);
        $results = $this->race([['checkout', (string) $user->id], ['checkout', (string) $user->id]]);
        $this->assertSame($results[0]['order_id'], $results[1]['order_id']);
        $this->assertSame(1, DB::table('orders')->count());
        $this->assertSame(1, DB::table('order_items')->count());
    }

    public function test_concurrent_phone_consumption_succeeds_once(): void
    {
        $user = User::factory()->create();
        $challenge = PhoneVerification::create(['user_id' => $user->id, 'phone_number' => '01012345678', 'code' => Hash::make('123456'), 'expires_at' => now()->addMinutes(10)]);
        $args = ['phone', (string) $user->id, (string) $challenge->id];
        $results = array_column($this->race([$args, $args]), 'status');
        sort($results);
        $this->assertSame([200, 422], $results);
        $this->assertSame('01012345678', $user->fresh()->phone_number);
    }

    public function test_concurrent_phone_claims_have_one_owner(): void
    {
        $args = [];
        foreach (User::factory()->count(2)->create() as $user) {
            $challenge = PhoneVerification::create(['user_id' => $user->id, 'phone_number' => '01012345678', 'code' => Hash::make('123456'), 'expires_at' => now()->addMinutes(10)]);
            $args[] = ['phone', (string) $user->id, (string) $challenge->id];
        }
        $results = array_column($this->race($args), 'status');
        sort($results);
        $this->assertSame([200, 409], $results);
        $this->assertSame(1, User::where('phone_number', '01012345678')->count());
    }
}
