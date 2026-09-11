<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Notifications\VerifyApiEmail;
use App\Services\EmailDelivery;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->noContent();
        }
        abort_unless(EmailDelivery::available(), 503, 'Email delivery is unavailable.');
        try {
            $request->user()->notify(new VerifyApiEmail);
        } catch (\Throwable $e) {
            abort(503, 'Email delivery is unavailable.');
        }

        return response()->json(['message' => 'Verification email requested.']);
    }

    public function show(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return response()->noContent();
    }
}
