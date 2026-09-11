<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PhoneRequestRequest;
use App\Http\Requests\V1\PhoneVerifyRequest;
use App\Models\User;
use App\Services\Phone\PhoneVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone_number' => ['required', 'unique:users', 'regex:/^01[0-9]{9}$/'],
            'address' => ['required', 'string', 'max:1000'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'role' => 'customer',
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'address' => ['sometimes', 'required', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ]);
    }

    public function requestPhoneChange(PhoneRequestRequest $request, PhoneVerificationService $service)
    {
        $verification = $service->request($request->user(), $request->validated('phone_number'));

        return response()->json(['message' => 'Verification delivery accepted.', 'verification_id' => $verification->id]);
    }

    public function verifyPhoneChange(PhoneVerifyRequest $request, PhoneVerificationService $service)
    {
        $service->verify($request->user(), $request->integer('verification_id'), $request->validated('code'));

        return response()->json(['message' => 'Phone number verified and updated successfully.', 'user' => $request->user()->refresh()]);
    }

    public function logout(Request $request)
    {
        app(V1\AuthController::class)->logout($request);

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
