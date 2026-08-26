<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ComplaintStatusUpdated extends Notification
{
    public function __construct(public Complaint $complaint) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Update on your complaint: '.$this->complaint->title)
            ->greeting('Hi '.$notifiable->name.',')
            ->line('The status of your complaint "'.$this->complaint->title.'" has been updated to: '.Str::headline($this->complaint->status).'.')
            ->action('View My Complaints', route('portal.complaints.index'));
    }
}
