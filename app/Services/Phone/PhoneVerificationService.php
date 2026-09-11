<?php

namespace App\Services\Phone;

use App\Models\PhoneVerification;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PhoneVerificationService
{
    public function __construct(private PhoneSender $sender) {}

    public function request(User $user, string $phone): PhoneVerification
    {
        abort_unless($this->sender->available(), 503, 'Phone delivery is unavailable.');
        $code = (string) random_int(100000, 999999);
        $verification = DB::transaction(function () use ($user, $phone, $code) {
            if (DB::getDriverName() === 'sqlite') {
                DB::table('users')->where('id', $user->id)->update(['id' => DB::raw('id')]);
            }
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            PhoneVerification::where('user_id', $user->id)->whereNull('invalidated_at')->update(['invalidated_at' => now()]);

            return PhoneVerification::create([
                'user_id' => $user->id, 'phone_number' => $phone, 'code' => Hash::make($code),
                'expires_at' => now()->addMinutes(10),
            ]);
        }, 3);
        try {
            $this->sender->send($phone, $code);
        } catch (\Throwable $e) {
            $verification->update(['invalidated_at' => now()]);
            abort(503, 'Phone delivery is unavailable.');
        }

        return $verification;
    }

    public function verify(User $user, int $id, #[\SensitiveParameter] string $code): void
    {
        try {
            $result = DB::transaction(function () use ($user, $id, $code) {
                if (DB::getDriverName() === 'sqlite') {
                    DB::table('users')->where('id', $user->id)->update(['id' => DB::raw('id')]);
                }
                $owner = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                $verification = PhoneVerification::whereKey($id)->where('user_id', $owner->id)->lockForUpdate()->first();
                if (! $verification || $verification->verified_at || $verification->invalidated_at || $verification->expires_at->lessThanOrEqualTo(now())) {
                    return 422;
                }
                if ($verification->attempts >= 5) {
                    return 429;
                }
                if (! Hash::check($code, $verification->code)) {
                    $verification->attempts++;
                    if ($verification->attempts >= 5) {
                        $verification->invalidated_at = now();
                    }
                    $verification->save();

                    return $verification->attempts >= 5 ? 429 : 422;
                }
                $owner->update(['phone_number' => $verification->phone_number]);
                $verification->update(['verified_at' => now(), 'code' => '']);

                return 200;
            }, 3);
        } catch (UniqueConstraintViolationException $e) {
            abort(409, 'That phone number is already in use.');
        }
        if ($result === 429) {
            abort(429, 'Too many attempts. Request a new code.');
        }
        if ($result !== 200) {
            throw ValidationException::withMessages(['code' => ['Invalid or expired verification.']]);
        }
    }
}
