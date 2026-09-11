<?php

namespace App\Providers;

use App\Services\Phone\PhoneSender;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PhoneSender::class, fn ($app) => $app->make(config('api.phone_sender')));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api-login', fn ($request) => [
            Limit::perMinute(config('api.login_per_minute'))->by(strtolower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perMinute(30)->by($request->ip()),
        ]);
        RateLimiter::for('api-register', fn ($request) => Limit::perMinute(config('api.register_per_minute'))->by($request->ip()));
        RateLimiter::for('phone-request', fn ($request) => [
            Limit::perMinutes(10, config('api.phone_requests_per_ten_minutes'))->by('user:'.$request->user()->id),
            Limit::perMinutes(10, 10)->by('ip:'.$request->ip()),
        ]);
        RateLimiter::for('phone-verify', fn ($request) => Limit::perMinute(config('api.phone_verifications_per_minute'))->by($request->user()->id));
        ResetPassword::createUrlUsing(fn ($user, $token) => request()->is('api/v1/*')
            ? rtrim(config('api.frontend_url'), '/').'/reset-password?'.http_build_query(['token' => $token, 'email' => $user->email])
            : route('password.reset', ['token' => $token, 'email' => $user->email]));
    }
}
