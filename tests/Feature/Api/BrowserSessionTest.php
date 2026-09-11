<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BrowserSessionTest extends ApiTestCase
{
    private array $jar = [];

    private function browser(string $method, string $uri, array $data = [], bool $csrf = true)
    {
        // Enable the framework's real CSRF branch; keep all test DB/mail configuration.
        $this->app['env'] = 'local';
        Auth::forgetGuards();
        $headers = ['HTTP_ORIGIN' => 'http://localhost:5173', 'HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json'];
        if ($csrf && isset($this->jar['XSRF-TOKEN'])) {
            $headers['HTTP_X_XSRF_TOKEN'] = $this->jar['XSRF-TOKEN'];
        }
        $response = $this->call($method, $uri, [], $this->jar, [], $headers, json_encode($data));
        foreach ($response->headers->getCookies() as $cookie) {
            $this->jar[$cookie->getName()] = $cookie->getValue();
        }

        return $response;
    }

    public function test_real_cookie_csrf_session_rotation_and_logout(): void
    {
        $user = User::factory()->create();
        $this->browser('GET', '/sanctum/csrf-cookie')->assertNoContent();
        $cookieName = config('session.cookie');
        $oldSession = $this->jar[$cookieName];
        $oldSessionId = app('session.store')->getId();
        $this->browser('POST', '/api/v1/auth/login', ['email' => $user->email, 'password' => 'password'], false)->assertStatus(419);
        $this->browser('POST', '/api/v1/auth/login', ['email' => $user->email, 'password' => 'password'])->assertOk();
        $this->assertNotSame($oldSession, $this->jar[$cookieName]);
        $this->assertNotSame($oldSessionId, app('session.store')->getId());
        $this->browser('GET', '/api/v1/me')->assertOk()->assertJsonPath('data.id', $user->id);
        $this->browser('PATCH', '/api/v1/me', ['address' => 'New address'], false)->assertStatus(419);
        $this->browser('PATCH', '/api/v1/me', ['address' => 'New address'])->assertOk();
        $oldJar = $this->jar;
        $this->browser('POST', '/api/v1/auth/logout')->assertNoContent();
        $this->browser('GET', '/api/v1/me')->assertUnauthorized();
        $this->jar = $oldJar;
        $this->browser('GET', '/api/v1/me')->assertUnauthorized();
    }

    public function test_cors_allowlist_credentials_and_untrusted_origin(): void
    {
        $headers = ['HTTP_ORIGIN' => 'http://localhost:5173', 'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST', 'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'content-type,x-xsrf-token,idempotency-key'];
        $this->call('OPTIONS', '/api/v1/orders', [], [], [], $headers)->assertNoContent()->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173')->assertHeader('Access-Control-Allow-Credentials', 'true');
        $headers['HTTP_ORIGIN'] = 'https://untrusted.example';
        $response = $this->call('OPTIONS', '/api/v1/orders', [], [], [], $headers);
        $this->assertNotSame('https://untrusted.example', $response->headers->get('Access-Control-Allow-Origin'));
        $this->withHeader('Origin', 'https://untrusted.example')->postJson('/api/v1/auth/login', ['email' => 'test@example.test', 'password' => 'password'])->assertStatus(419);
    }

    public function test_password_change_invalidates_other_browser_sessions(): void
    {
        $user = User::factory()->create();
        $this->browser('GET', '/sanctum/csrf-cookie');
        $this->browser('POST', '/api/v1/auth/login', ['email' => $user->email, 'password' => 'password'])->assertOk();
        $this->browser('GET', '/api/v1/me')->assertOk();
        $firstBrowser = $this->jar;
        $this->jar = [];
        $this->browser('GET', '/sanctum/csrf-cookie');
        $this->browser('POST', '/api/v1/auth/login', ['email' => $user->email, 'password' => 'password'])->assertOk();
        $this->browser('PUT', '/api/v1/me/password', ['current_password' => 'password', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertNoContent();
        $this->browser('GET', '/api/v1/me')->assertOk();
        $this->jar = $firstBrowser;
        $this->browser('GET', '/api/v1/me')->assertUnauthorized();
    }
}
