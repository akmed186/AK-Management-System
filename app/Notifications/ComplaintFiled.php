<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintFiled extends Notification
{
    public function __construct(public Complaint $complaint) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New complaint filed: '.$this->complaint->title)
            ->greeting('New complaint / request filed')
            ->line('Tenant: '.$this->complaint->tenant->full_name)
            ->line('Unit: '.$this->complaint->room->property->property_name.' — '.$this->complaint->room->room_number)
            ->line('Priority: '.ucfirst($this->complaint->priority))
            ->line($this->complaint->description)
            ->action('Review Complaint', route('complaints.edit', $this->complaint));
    }
}
