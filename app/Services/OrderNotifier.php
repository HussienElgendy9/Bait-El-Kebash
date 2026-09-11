<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OrderNotifier
{
    public function send(int $orderId): void
    {
        try {
            $order = Order::with('user', 'orderitems.product')->findOrFail($orderId);
            foreach (User::where('role', 'admin')->cursor() as $admin) {
                // Isolate recipients and channels; mail failure cannot suppress the database notice.
                foreach (EmailDelivery::available() ? ['database', 'mail'] : ['database'] as $channel) {
                    try {
                        Notification::sendNow($admin, new NewOrderNotification($order), [$channel]);
                    } catch (\Throwable $e) {
                        Log::warning('Order notification delivery failed.', ['order_id' => $orderId, 'admin_id' => $admin->id, 'channel' => $channel]);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Order notification processing failed.', ['order_id' => $orderId]);
        }
    }
}
