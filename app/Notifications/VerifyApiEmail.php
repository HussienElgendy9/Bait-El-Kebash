<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;

class VerifyApiEmail extends VerifyEmail
{
    protected function verificationUrl($notifiable)
    {
        $url = URL::temporarySignedRoute('api.v1.verification.verify', now()->addMinutes(60), ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]);

        return rtrim(config('api.frontend_url'), '/').'/verify-email?'.http_build_query(['verification_url' => $url]);
    }
}
