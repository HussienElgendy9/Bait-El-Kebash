<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\EmailRequest;
use App\Http\Requests\V1\PasswordResetRequest;
use App\Models\User;
use App\Services\EmailDelivery;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    public function store(EmailRequest $request)
    {
        abort_unless(EmailDelivery::available(), 503, 'Email delivery is unavailable.');
        try {
            Password::sendResetLink($request->validated());
        } catch (\Throwable $e) {
            abort(503, 'Email delivery is unavailable.');
        }

        return response()->json(['message' => 'If the account exists, a password reset link has been requested.']);
    }

    public function update(PasswordResetRequest $request)
    {
        $status = Password::reset($request->validated(), function (User $user, string $password) {
            $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            $user->tokens()->delete();
            event(new PasswordReset($user));
        });
        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        return response()->noContent();
    }
}
