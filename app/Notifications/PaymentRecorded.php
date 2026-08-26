<?php

namespace App\Notifications;

use App\Models\RentPayment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentRecorded extends Notification
{
    public function __construct(public RentPayment $payment) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment received — GH₵ '.number_format($this->payment->amount_paid, 2))
            ->greeting('Hi '.$notifiable->name.',')
            ->line('We\'ve recorded a payment of GH₵ '.number_format($this->payment->amount_paid, 2).' on '.$this->payment->payment_date->format('M j, Y').'.')
            ->line('Unit: '.$this->payment->rental->room->property->property_name.' — '.$this->payment->rental->room->room_number)
            ->line('Method: '.$this->payment->payment_method)
            ->action('View Payment History', route('portal.payments'));
    }
}
