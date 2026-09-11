<?php

use App\Services\Phone\UnavailablePhoneSender;

return [
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),
    'phone_sender' => env('PHONE_SENDER', UnavailablePhoneSender::class),
    'mail_enabled' => (bool) env('API_MAIL_ENABLED', false),
    'login_per_minute' => (int) env('API_LOGIN_PER_MINUTE', 5),
    'register_per_minute' => (int) env('API_REGISTER_PER_MINUTE', 5),
    'phone_requests_per_ten_minutes' => (int) env('API_PHONE_REQUESTS_PER_TEN_MINUTES', 3),
    'phone_verifications_per_minute' => (int) env('API_PHONE_VERIFICATIONS_PER_MINUTE', 10),
];
