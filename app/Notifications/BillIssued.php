<?php

namespace App\Notifications;

use App\Models\UtilityBill;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BillIssued extends Notification
{
    public function __construct(public UtilityBill $bill) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New bill — '.$this->bill->utilityType->utility_name.' (GH₵ '.number_format($this->bill->total_amount, 2).')')
            ->greeting('Hi '.$notifiable->name.',')
            ->line('A new '.$this->bill->utilityType->utility_name.' bill has been added to your account.')
            ->line('Amount: GH₵ '.number_format($this->bill->total_amount, 2))
            ->line('Usage: '.$this->bill->consumption_units.' '.$this->bill->utilityType->unit_of_measure)
            ->line('Due: '.$this->bill->due_date->format('M j, Y'))
            ->action('View My Bills', route('portal.bills'));
    }
}
