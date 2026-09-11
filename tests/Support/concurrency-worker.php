<?php

use App\Models\User;
use App\Services\CheckoutService;
use App\Services\Phone\PhoneVerificationService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

require __DIR__.'/../../vendor/autoload.php';

// Only a freshly-created test fixture is accepted; never use the configured application DB.
$database = $argv[1] ?? '';
if (! str_starts_with(basename($database), 'bait-api-race-') || ! is_file($database)) {
    exit(2);
}
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['database.default' => 'race', 'database.connections.race' => [
    'driver' => 'sqlite', 'database' => $database, 'prefix' => '',
    'foreign_key_constraints' => true, 'busy_timeout' => 5000, 'transaction_mode' => 'IMMEDIATE',
], 'api.mail_enabled' => false, 'mail.default' => 'array']);
Notification::fake();
try {
    $user = User::findOrFail((int) $argv[3]);
    if ($argv[2] === 'checkout') {
        $order = app(CheckoutService::class)->create($user, [['product_id' => 1, 'quantity' => 3]], 'concurrent-key');
        echo json_encode(['status' => 200, 'order_id' => $order->id]);
    } else {
        app(PhoneVerificationService::class)->verify($user, (int) $argv[4], '123456');
        echo json_encode(['status' => 200]);
    }
} catch (ValidationException $e) {
    echo json_encode(['status' => 422]);
} catch (HttpException $e) {
    echo json_encode(['status' => $e->getStatusCode()]);
}
