<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ConfirmPasswordRequest;
use App\Http\Requests\V1\PasswordChangeRequest;
use App\Http\Requests\V1\ProfileRequest;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AccountController extends Controller
{
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    public function update(ProfileRequest $request)
    {
        $user = $request->user();
        $user->fill($request->validated());
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();

        return new UserResource($user);
    }

    public function password(PasswordChangeRequest $request)
    {
        $request->user()->forceFill(['password' => $request->validated('password'), 'remember_token' => Str::random(60)])->save();
        $request->user()->tokens()->delete();
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->noContent();
    }

    public function confirm(ConfirmPasswordRequest $request)
    {
        $request->session()->put('auth.password_confirmed_at', time());

        return response()->noContent();
    }

    public function destroy(ConfirmPasswordRequest $request)
    {
        $request->user()->delete();
        Auth::guard('web')->logoutCurrentDevice();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
