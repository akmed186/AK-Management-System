<?php

namespace App\Notifications;

use App\Models\ComplaintComment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintCommentAdded extends Notification
{
    public function __construct(public ComplaintComment $comment) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New update on your complaint: '.$this->comment->complaint->title)
            ->greeting('Hi '.$notifiable->name.',')
            ->line('There\'s a new update on your complaint "'.$this->comment->complaint->title.'":')
            ->line($this->comment->comment_text)
            ->action('View My Complaints', route('portal.complaints.index'));
    }
}
