<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PhoneRequestRequest;
use App\Http\Requests\V1\PhoneVerifyRequest;
use App\Http\Resources\V1\UserResource;
use App\Services\Phone\PhoneVerificationService;

class PhoneController extends Controller
{
    public function store(PhoneRequestRequest $request, PhoneVerificationService $service)
    {
        $verification = $service->request($request->user(), $request->validated('phone_number'));

        return response()->json(['data' => ['verification_id' => $verification->id, 'expires_at' => $verification->expires_at->toISOString()], 'message' => 'Verification delivery accepted.']);
    }

    public function verify(PhoneVerifyRequest $request, PhoneVerificationService $service)
    {
        $service->verify($request->user(), $request->integer('verification_id'), $request->validated('code'));

        return new UserResource($request->user()->refresh());
    }
}
