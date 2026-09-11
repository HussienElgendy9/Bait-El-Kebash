<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\LoginRequest;
use App\Http\Requests\V1\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create($request->safe()->except('password_confirmation'));
        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return (new UserResource($user->refresh()))->response()->setStatusCode(201);
    }

    public function login(LoginRequest $request)
    {
        if (! Auth::guard('web')->attempt($request->validated())) {
            throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
        }
        $request->session()->regenerate();

        return new UserResource(Auth::guard('web')->user());
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->noContent();
    }
}
