<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Login uses the session, which Sanctum only starts for requests from a known frontend.
        config(['sanctum.stateful' => ['localhost']]);
        $this->withHeader('Referer', 'http://localhost');
    }

    public function test_register_creates_a_logged_in_customer(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Ahmed',
            'email' => 'ahmed@example.com',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
            'phone_number' => '01012345678',
            'address' => 'القاهرة',
        ])->assertCreated()->assertJsonMissingPath('token');

        $this->assertSame('customer', User::where('email', 'ahmed@example.com')->value('role'));
        $this->assertAuthenticated();
    }

    public function test_register_cannot_choose_a_role(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
            'phone_number' => '01012345678',
            'address' => 'القاهرة',
            'role' => 'super_admin',
        ])->assertCreated();

        $this->assertSame('customer', User::where('email', 'sneaky@example.com')->value('role'));
    }

    public function test_register_rejects_a_used_phone_number(): void
    {
        User::factory()->create(['phone_number' => '01012345678']);

        $this->postJson('/api/register', [
            'name' => 'Ahmed',
            'email' => 'ahmed@example.com',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
            'phone_number' => '01012345678',
            'address' => 'القاهرة',
        ])->assertJsonValidationErrors('phone_number');
    }

    public function test_login_and_logout(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('user.email', $user->email);
        $this->assertAuthenticatedAs($user);

        $this->postJson('/api/logout')->assertOk();
        $this->assertGuest('web');
    }

    public function test_login_rejects_a_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertJsonValidationErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong']);
        }

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_guest_gets_401_from_protected_routes(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
        $this->getJson('/api/orders')->assertUnauthorized();
    }

    public function test_user_can_update_profile_but_not_role(): void
    {
        Sanctum::actingAs($user = User::factory()->create());

        $this->patchJson('/api/user', ['name' => 'New Name', 'address' => 'الإسكندرية', 'role' => 'admin'])
            ->assertOk();

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('الإسكندرية', $user->address);
        $this->assertSame('customer', $user->role);
    }
}
