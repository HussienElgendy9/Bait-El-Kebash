<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Notifications\VerifyApiEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

class AccountTest extends ApiTestCase
{
    public function test_registration_login_profile_and_privilege_injection(): void
    {
        $this->spa()->postJson('/api/v1/auth/register', ['name' => 'Customer', 'email' => 'customer@example.test', 'phone_number' => '01012345678', 'address' => 'Cairo', 'password' => 'password', 'password_confirmation' => 'password', 'role' => 'admin'])
            ->assertCreated()->assertJsonPath('data.role', 'customer')->assertJsonMissingPath('data.password')->assertJsonMissingPath('token');
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards();
        $this->get('/api/v1/me')->assertOk()->assertJsonPath('data.email', 'customer@example.test');
        $this->patchJson('/api/v1/me', ['name' => 'Changed', 'address' => 'New address', 'email' => 'changed@example.test', 'role' => 'admin', 'phone_number' => '01111111111'])->assertOk()->assertJsonPath('data.role', 'customer')->assertJsonPath('data.phone_number', '01012345678')->assertJsonPath('data.email_verified_at', null);
        $this->post('/api/v1/auth/logout')->assertNoContent();
        $this->app['auth']->forgetGuards();
        $this->get('/api/v1/me')->assertUnauthorized();
        $this->postJson('/api/v1/auth/login', ['email' => 'changed@example.test', 'password' => 'wrong'])->assertUnprocessable();
        $this->postJson('/api/v1/auth/login', ['email' => 'changed@example.test', 'password' => 'password'])->assertOk();
    }

    public function test_registration_validation_and_login_throttle(): void
    {
        $this->spa()->postJson('/api/v1/auth/register', [])->assertUnprocessable()->assertJsonValidationErrors(['phone_number', 'address']);
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', ['email' => 'no@example.test', 'password' => 'wrong'])->assertUnprocessable();
        }
        $this->postJson('/api/v1/auth/login', ['email' => 'no@example.test', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_legacy_bearer_authentication_and_revocation(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('existing')->plainTextToken;
        $this->withToken($token)->get('/api/user')->assertOk()->assertJsonPath('user.id', $user->id);
        $this->app['auth']->forgetGuards();
        $this->get('/api/v1/me')->assertOk()->assertJsonPath('data.id', $user->id);
        $this->post('/api/logout')->assertOk();
        $this->app['auth']->forgetGuards();
        $this->get('/api/user')->assertUnauthorized();
    }

    public function test_password_change_confirm_and_self_deletion(): void
    {
        $user = User::factory()->create();
        $user->createToken('old');
        $this->spa()->actingAs($user)->putJson('/api/v1/me/password', ['current_password' => 'wrong', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertUnprocessable();
        $this->putJson('/api/v1/me/password', ['current_password' => 'password', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertNoContent();
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->postJson('/api/v1/auth/confirm-password', ['password' => 'new-password'])->assertNoContent();
        $this->deleteJson('/api/v1/me', ['password' => 'wrong'])->assertUnprocessable();
        $this->deleteJson('/api/v1/me', ['password' => 'new-password'])->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_password_reset_delivery_unavailable_and_token_replay(): void
    {
        $user = User::factory()->create();
        $this->spa()->postJson('/api/v1/auth/forgot-password', ['email' => $user->email])->assertStatus(503);
        config(['api.mail_enabled' => true]);
        $this->postJson('/api/v1/auth/forgot-password', ['email' => $user->email])->assertOk();
        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'unknown@example.test'])->assertOk();
        Notification::assertSentTo($user, ResetPassword::class);
        $token = Password::createToken($user);
        $body = ['email' => $user->email, 'token' => $token, 'password' => 'reset-password', 'password_confirmation' => 'reset-password'];
        $this->postJson('/api/v1/auth/reset-password', $body)->assertNoContent();
        $this->postJson('/api/v1/auth/reset-password', $body)->assertUnprocessable();
        $this->assertTrue(Hash::check('reset-password', $user->fresh()->password));
    }

    public function test_email_verification_requires_owner_hash_and_signature(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->post('/api/v1/me/email/verification-notification')->assertStatus(503);
        config(['api.mail_enabled' => true]);
        $this->post('/api/v1/me/email/verification-notification')->assertOk();
        Notification::assertSentTo($user, VerifyApiEmail::class);
        $url = URL::temporarySignedRoute('api.v1.verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->get($url.'&tampered=1')->assertForbidden();
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        $this->actingAs($user)->get($url)->assertNoContent();
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->get($url)->assertNoContent();
    }

    public function test_reset_and_email_verification_expire(): void
    {
        $user = User::factory()->unverified()->create();
        $token = Password::createToken($user);
        $url = URL::temporarySignedRoute('api.v1.verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->travel(61)->minutes();
        $this->spa()->postJson('/api/v1/auth/reset-password', ['email' => $user->email, 'token' => $token, 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertUnprocessable();
        $this->actingAs($user)->get($url)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
