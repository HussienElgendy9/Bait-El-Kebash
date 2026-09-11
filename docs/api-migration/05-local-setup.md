# Local setup and executable requests

Requires PHP 8.2+, Composer, PDO SQLite (tests), PDO for the deployment database, fileinfo, mbstring and normal Laravel extensions. PHP GD is not needed by this test suite. Node is only needed for retained Blade assets and the optional OpenAPI validator.

## Isolated tests

```powershell
composer install
php vendor/bin/pest tests/Feature/Api --compact
php vendor/bin/pest --compact
php artisan route:list --path=api/v1
npm.cmd exec --cache ./storage/api-validation-cache --yes --package @apidevtools/swagger-cli@4.0.4 -- swagger-cli validate docs/api/openapi.yaml
```

Pest was installed by concurrent workspace work and retained. Tests still support PHPUnit-style classes. phpunit.xml forces in-memory SQLite and array mail; Tests/TestCase refuses a different default database before migrations. Concurrency tests create uniquely named SQLite files under storage/framework/testing, launch separate PHP processes, fake notifications, and delete only their own fixture files. Clear stale application config before testing if the safety guard rejects it.

## Separate local application database

Do not run composer setup against an existing database. Create a new local environment and a new empty SQLite file; never overwrite an existing file.

1. Copy `docs/api/.env.api-local.example` to `.env.api-local` only if it does not exist. Keep that file untracked.
2. Set DB_DATABASE to the absolute path of a **new** SQLite file, for example D:/githubProj/Bait-El-Kebash/database/api-local.sqlite. Create that empty file only if absent. This is separate from the user's ordinary .env/database.
3. Generate a key and apply migrations to that environment:

```powershell
php artisan --env=api-local key:generate
php artisan --env=api-local migrate
php artisan --env=api-local storage:link
php artisan --env=api-local serve --host=localhost --port=8000
```

For MVC assets run `npm install` and `npm run dev` in another shell, or build with `npm run build`. Do not commit application keys, credentials, database files or uploaded files.

Register a customer through the API or retained /register page. For a local administrator, open `php artisan --env=api-local tinker`, find that user's exact email, assign `$user->role = 'admin'`, and save. Role is intentionally not broadly mass assignable. Never expose a public admin-creation endpoint.

## Cookie requests

`docs/api/examples.ps1` is executable in PowerShell:

```powershell
./docs/api/examples.ps1 -Email 'local-user@example.test'
# Optional: creates a real order in your selected LOCAL API database.
./docs/api/examples.ps1 -Email 'local-user@example.test' -PlaceOrder -ProductId 1 -Quantity 2
```

The script prompts for a password, initializes cookies, sends X-XSRF-TOKEN, logs in, reads catalog/me/orders and logs out. With -PlaceOrder it generates a retry key and prints it. Keep the SAME key and body if manually retrying after a network failure. Create products/categories through admin endpoints first.

From a separate frontend using Axios:

```js
const api = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true,
  withXSRFToken: true,
  headers: { Accept: 'application/json' }
});
await api.get('/sanctum/csrf-cookie');
await api.post('/api/v1/auth/login', { email, password });
const { data } = await api.get('/api/v1/me');
const form = new FormData();
form.append('_method', 'PATCH');
form.append('image', file);
await api.post('/api/v1/admin/products/1', form);
```

Do not manually set multipart Content-Type; the browser adds its boundary. Handle 401/419 by reinitializing CSRF and prompting for login as appropriate. The SPA must not persist bearer tokens.

Reset email links land at FRONTEND_URL/reset-password with token/email. Verification links land at FRONTEND_URL/verify-email with verification_url; after login call that signed API URL unchanged using credentials. Allow only the configured API origin when consuming that URL. Existing MVC reset requests still generate MVC links.

## Delivery during development

Default PHONE_SENDER is UnavailablePhoneSender: 503 and no code returned. Implement App/Services/Phone/PhoneSender in a provider adapter, throw on rejected delivery, set PHONE_SENDER to its class and supply credentials through environment configuration. Never put the test fake in production, log codes, or claim SMS was sent without provider acceptance.

API_MAIL_ENABLED=false disables API email sending and order email notifications. Database order notices still work. Enable only with a verified SMTP/provider transport; log/array/failover-to-log transports are not accepted as production delivery. Tests fake Notification and bind FakePhoneSender.

