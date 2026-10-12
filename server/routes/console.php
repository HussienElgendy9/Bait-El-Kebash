<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
//added by CHETGPT AI
Artisan::command('user:make-super-admin {email}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error("No user with email {$email}. Register in the store first.");
        return 1;
    }

    $user->update(['role' => 'super_admin']);
    $this->info("{$user->name} is now a super admin.");
})->purpose('Promote an existing user to super admin');