<?php

namespace App\Services;

class EmailDelivery
{
    public static function available(): bool
    {
        return config('api.mail_enabled') && (app()->runningUnitTests() || in_array(config('mail.mailers.'.config('mail.default').'.transport'), ['smtp', 'ses', 'postmark', 'resend', 'sendmail'], true));
    }
}
