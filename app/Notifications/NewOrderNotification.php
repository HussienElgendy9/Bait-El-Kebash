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
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $total = $this->order->orderitems->sum('price_snapshot');

        $mail = (new MailMessage)
            ->subject("New Order #{$this->order->id} - Bait El Kebash")
            ->greeting('New Order Received')
            ->line("Order #{$this->order->id}")
            ->line("Customer: {$this->order->user->name}")
            ->line("Phone: {$this->order->user->phone_number}")
            ->line("Address: {$this->order->user->address}");
        foreach ($this->order->orderitems as $item) {
            $mail->line(
                "{$item->product->name} — Qty: {$item->quantity} — " .
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
            'customer_name' => $this->order->user->name,
            'phone' => $this->order->user->phone_number,
            'address' => $this->order->user->address,
            'total' => $this->order->orderitems->sum('price_snapshot'),
            'message' => "New order #{$this->order->id} received.",
        ];
    }
}