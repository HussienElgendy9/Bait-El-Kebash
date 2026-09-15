<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules;
use App\Models\PhoneVerification;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone_number'=> ['required','unique:users,phone_number','regex:/^01[0125][0-9]{8}$/'],
            'address' => ['required','string','max:1000'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number'=>$request->phone_number,
            'address'=>$request->address,
            'role'=>'customer',
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

        if (!$user || !Hash::check($validated['password'], $user->password)) {
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
    public function requestPhoneChange(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => [
                'required',
                'regex:/^01[0125][0-9]{8}$/',
                'unique:users,phone_number',
            ],
        ]);

        //invalidate previous active OTPs
        PhoneVerification::where('user_id',$request->user()->id)
        ->whereNull('invalidated_at')
        ->update([
            'invalidated_at'=>now(),
        ]);

        $code = (string) random_int(100000, 999999);

        $verification = PhoneVerification::create([
            'user_id' => $request->user()->id,
            'phone_number' => $validated['phone_number'],
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        return response()->json([
            'message' => 'Verification code sent successfully.',
            'verification_id' => $verification->id,
            'code' => $code,
        ]);
    }

    public function verifyPhoneChange(Request $request)
{
    $validated = $request->validate([
        'verification_id' => ['required', 'integer', 'exists:phone_verifications,id'],
        'code' => ['required', 'string', 'size:6'],
    ]);

    $verification = PhoneVerification::where('id', $validated['verification_id'])
        ->where('user_id', $request->user()->id)
        ->whereNull('verified_at')
        ->whereNull('invalidated_at')
        ->first();

    if (!$verification) {
        return response()->json([
            'message' => 'Invalid verification request.',
        ], 422);
    }

    if ($verification->expires_at->isPast()) {
        return response()->json([
            'message' => 'Verification code has expired.',
        ], 422);
    }

    if ($verification->attempts >= 5) {
        $verification->update([
            'invalidated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Too many attempts. Please request a new code.',
        ], 429);
    }

    if ($verification->code !== $validated['code']) {
        $verification->increment('attempts');

        if ($verification->attempts >= 5) {
            $verification->update([
                'invalidated_at' => now(),
            ]);

            return response()->json([
                'message' => 'Too many attempts. Please request a new code.',
            ], 429);
        }


        return response()->json([
            'message' => 'Invalid verification code.',
        ], 422);
    }

    $user = $request->user();

    $user->update([
        'phone_number' => $verification->phone_number,
    ]);

    $verification->update([
        'verified_at' => now(),
    ]);

    return response()->json([
        'message' => 'Phone number verified and updated successfully.',
        'user' => $user,
    ]);
}

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}