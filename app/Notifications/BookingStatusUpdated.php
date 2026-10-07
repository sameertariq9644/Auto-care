<?php

namespace App\Notifications;

use App\Models\ServiceBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public ServiceBooking $booking,
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
        $vehicle = $this->booking->vehicle;

        return (new MailMessage)
            ->subject('Your service booking status has been updated')
            ->line("Your booking for the {$vehicle->display_name} ({$this->booking->requested_service}) is now: ".str($this->booking->status)->replace('_', ' ')->title().'.')
            ->action('View Booking', route('bookings.show', $this->booking))
            ->line('Thank you for choosing AutoCare Pro.');
    }
}
