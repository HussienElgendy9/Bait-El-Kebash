<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $total = $this->order->total();

        $mail = (new MailMessage)
            ->subject("New Order #{$this->order->id} - Bait El Kebash")
            ->greeting('New Order Received')
            ->line("Order #{$this->order->id}")
            ->line('Customer: '.($this->order->delivery_name ?? 'Historical snapshot unavailable'))
            ->line('Phone: '.($this->order->delivery_phone ?? 'Historical snapshot unavailable'))
            ->line('Address: '.($this->order->delivery_address ?? 'Historical snapshot unavailable'));
        foreach ($this->order->orderitems as $item) {
            $mail->line(
                ($item->product_name ?? 'Historical product snapshot unavailable')." — Qty: {$item->quantity} — ".
                "Unit price: {$item->unit_price} — Total: {$item->price_snapshot}"
            );
        }

        return $mail
            ->line("Order Total: {$total}")
            ->action('View Order', url('/admin/orders'))
            ->line('Please review the order from the admin panel.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'customer_name' => $this->order->delivery_name,
            'phone' => $this->order->delivery_phone,
            'address' => $this->order->delivery_address,
            'total' => $this->order->total(),
            'message' => "New order #{$this->order->id} received.",
        ];
    }
}
