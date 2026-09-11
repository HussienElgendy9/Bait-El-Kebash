<?php

namespace Tests\Feature\Api;

use App\Models\PhoneVerification;
use App\Models\User;
use App\Services\Phone\PhoneSender;
use Illuminate\Support\Facades\Hash;
use Tests\Support\FakePhoneSender;

class PhoneTest extends ApiTestCase
{
    private function sender(): FakePhoneSender
    {
        $fake = new FakePhoneSender;
        $this->app->instance(PhoneSender::class, $fake);

        return $fake;
    }

    public function test_unconfigured_and_failed_delivery_are_explicit_and_never_expose_codes(): void
    {
        $this->actingAs(User::factory()->create())->postJson('/api/v1/me/phone/request', ['phone_number' => '01012345678'])->assertStatus(503)->assertJsonMissingPath('code');
        $this->assertDatabaseCount('phone_verifications', 0);
        $fake = $this->sender();
        $fake->fail = true;
        $this->postJson('/api/user/phone/request', ['phone_number' => '01012345678'])->assertStatus(503)->assertJsonMissingPath('code');
        $this->assertNotNull(PhoneVerification::first()->invalidated_at);
    }

    public function test_hashed_resend_invalidation_ownership_success_and_replay(): void
    {
        $fake = $this->sender();
        $user = User::factory()->create();
        $this->actingAs($user);
        $first = $this->postJson('/api/v1/me/phone/request', ['phone_number' => '01012345678'])->assertOk()->assertJsonMissingPath('data.code')->json('data.verification_id');
        $second = $this->postJson('/api/v1/me/phone/request', ['phone_number' => '01012345678'])->assertOk()->json('data.verification_id');
        $this->assertNotNull(PhoneVerification::find($first)->invalidated_at);
        $code = $fake->messages[1]['code'];
        $this->assertTrue(Hash::check($code, PhoneVerification::find($second)->code));
        $this->assertStringNotContainsString($code, PhoneVerification::find($second)->toJson());
        $this->postJson('/api/v1/me/phone/verify', ['verification_id' => $first, 'code' => $fake->messages[0]['code']])->assertUnprocessable();
        $this->actingAs(User::factory()->create())->postJson('/api/v1/me/phone/verify', ['verification_id' => $second, 'code' => $code])->assertUnprocessable();
        $this->actingAs($user)->postJson('/api/v1/me/phone/verify', ['verification_id' => $second, 'code' => $code])->assertOk()->assertJsonPath('data.phone_number', '01012345678');
        $this->postJson('/api/v1/me/phone/verify', ['verification_id' => $second, 'code' => $code])->assertUnprocessable();
        $this->assertSame('', PhoneVerification::find($second)->code);
    }

    public function test_expiry_and_attempt_limit_are_persisted(): void
    {
        $fake = $this->sender();
        $this->actingAs(User::factory()->create());
        $id = $this->postJson('/api/v1/me/phone/request', ['phone_number' => '01012345678'])->json('data.verification_id');
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/me/phone/verify', ['verification_id' => $id, 'code' => '000000'])->assertStatus($i === 4 ? 429 : 422);
        }
        $this->assertSame(5, PhoneVerification::find($id)->attempts);
        $this->postJson('/api/v1/me/phone/verify', ['verification_id' => $id, 'code' => $fake->messages[0]['code']])->assertUnprocessable();
        $id = $this->postJson('/api/v1/me/phone/request', ['phone_number' => '01012345678'])->json('data.verification_id');
        $this->travel(10)->minutes();
        $this->postJson('/api/v1/me/phone/verify', ['verification_id' => $id, 'code' => $fake->messages[1]['code']])->assertUnprocessable();
    }

    public function test_phone_claim_race_uses_unique_constraint_and_request_throttle(): void
    {
        $fake = $this->sender();
        $user = User::factory()->create();
        $this->actingAs($user);
        $id = $this->postJson('/api/v1/me/phone/request', ['phone_number' => '01012345678'])->json('data.verification_id');
        User::factory()->create(['phone_number' => '01012345678']);
        $this->postJson('/api/v1/me/phone/verify', ['verification_id' => $id, 'code' => $fake->messages[0]['code']])->assertConflict();
        $this->assertNull(PhoneVerification::find($id)->verified_at);
        $this->assertNotSame('01012345678', $user->fresh()->phone_number);
        $this->postJson('/api/v1/me/phone/request', ['phone_number' => '01112345678'])->assertOk();
        $this->postJson('/api/v1/me/phone/request', ['phone_number' => '01212345678'])->assertOk();
        $this->postJson('/api/v1/me/phone/request', ['phone_number' => '01512345678'])->assertStatus(429);
    }
}
