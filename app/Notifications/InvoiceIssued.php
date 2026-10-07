<?php

namespace App\Notifications;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceIssued extends Notification
{
    use Queueable;

    public function __construct(
        public Invoice $invoice,
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
        $invoice = $this->invoice->loadMissing('items', 'booking.vehicle', 'customer');
        $number = str_pad($invoice->id, 5, '0', STR_PAD_LEFT);

        $message = (new MailMessage)
            ->subject("Your AutoCare Pro invoice #{$number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Invoice #{$number} has been issued for your vehicle service.");

        foreach ($invoice->items as $item) {
            $message->line("• {$item->description} × {$item->quantity} — Rs ".number_format($item->quantity * $item->unit_price, 2));
        }

        $paid = $invoice->paymentSummary();

        return $message
            ->line('**'.($paid ? 'Total' : 'Total due').': Rs '.number_format($invoice->total, 2).'**'.($paid ? " — {$paid}" : ''))
            ->action($paid ? 'View Invoice' : 'View & Pay Invoice', route('invoices.show', $invoice))
            ->line('A PDF copy of the invoice is attached. Thank you for choosing AutoCare Pro.')
            ->attachData(
                Pdf::loadView('invoices.pdf', compact('invoice'))->output(),
                "invoice-{$invoice->id}.pdf",
                ['mime' => 'application/pdf'],
            );
    }

    /**
     * Email the invoice to its customer. A mail failure must never undo the invoice itself,
     * so it is reported and signalled through the return value instead of thrown.
     */
    public static function sendFor(Invoice $invoice): bool
    {
        try {
            $invoice->customer->notify(new self($invoice));

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
