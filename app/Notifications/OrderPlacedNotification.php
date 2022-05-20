<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrderPlacedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $notifiable->verification_code = rand(100000,999999);
        $notifiable->save();

        $array['subject'] = translate('Your order has been placed') . ' - ' . $this->order->code;
        $array['order'] = $this->order;

        return (new MailMessage)
            ->view('emails.invoice', ['array' => $array, 'order' => $this->order])
            ->from(env('MAIL_FROM_ADDRESS'))
            ->subject(translate('Order Placed').' - '.env('APP_NAME'));
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
