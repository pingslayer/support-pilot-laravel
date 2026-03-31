<?php

namespace App\Notifications;

use App\Models\Tenant;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplyNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Tenant $tenant,
        protected Message $assistantMessage
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->from(config('mail.from.address'), $this->tenant->name . ' Support')
            ->subject('Update on your support ticket')
            ->greeting('Hello,')
            ->line($this->assistantMessage->content)
            ->action('View Ticket Online', url('/')) // Placeholder for real portal
            ->line('Thank you for choosing ' . $this->tenant->name . '!');
    }
}
