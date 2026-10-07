<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\ServiceBooking;
use App\Models\User;
use App\Notifications\InvoiceIssued;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    /**
     * Display a listing of all invoices.
     */
    public function index(): View
    {
        $invoices = Invoice::with(['customer', 'booking.vehicle'])->latest()->paginate(15);

        return view('admin.invoices.index', compact('invoices'));
    }

    /**
     * Show the form to create an invoice by hand.
     */
    public function create(): View
    {
        $customers = User::role('customer')->orderBy('name')->get(['id', 'name', 'email']);

        $bookings = ServiceBooking::with('vehicle')
            ->where('status', '!=', ServiceBooking::STATUS_CANCELLED)
            ->latest()
            ->get()
            ->map(fn (ServiceBooking $booking) => [
                'id' => $booking->id,
                'customer_id' => $booking->customer_id,
                'label' => "#{$booking->id} — {$booking->vehicle->display_name} · {$booking->requested_service}",
            ])
            ->values();

        return view('admin.invoices.create', [
            'customers' => $customers,
            'bookings' => $bookings,
            'stock' => $this->stockOptions(),
        ]);
    }

    /**
     * Create the invoice, take the billed parts out of stock, and email it to the customer.
     */
    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $invoice = DB::transaction(function () use ($request) {
            $invoice = Invoice::create([
                'customer_id' => $request->validated('customer_id'),
                'booking_id' => $request->validated('booking_id'),
                'status' => Invoice::STATUS_UNPAID,
            ]);

            $invoice->syncItems($request->validated('items'), (float) $request->validated('tax'));

            return $invoice;
        });

        $emailed = InvoiceIssued::sendFor($invoice);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('status', $emailed
                ? 'Invoice created and emailed to the customer.'
                : 'Invoice created, but the email could not be sent. Check the mail settings.');
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load('items', 'customer', 'booking.vehicle');

        return view('admin.invoices.show', compact('invoice'));
    }

    /**
     * Show the form to edit the invoice's line items and tax before it's paid.
     */
    public function edit(Invoice $invoice): View
    {
        abort_if($invoice->status === Invoice::STATUS_PAID, 422, 'Paid invoices can no longer be edited.');

        $invoice->load('items');

        return view('admin.invoices.edit', [
            'invoice' => $invoice,
            'stock' => $this->stockOptions($invoice),
        ]);
    }

    /**
     * Update the invoice's line items and tax, recalculating the totals and adjusting stock.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        abort_if($invoice->status === Invoice::STATUS_PAID, 422, 'Paid invoices can no longer be edited.');

        DB::transaction(function () use ($request, $invoice) {
            $invoice->syncItems($request->validated('items'), (float) $request->validated('tax'));
        });

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('status', 'Invoice updated successfully.');
    }

    /**
     * Record that the customer paid the invoice in cash at the workshop.
     */
    public function markPaid(Invoice $invoice): RedirectResponse
    {
        // Return to wherever the button was clicked: the invoice list or the invoice itself.
        $back = redirect()->back(fallback: route('admin.invoices.show', $invoice));

        if ($invoice->status === Invoice::STATUS_PAID) {
            return $back->with('status', 'This invoice was already marked as paid.');
        }

        $invoice->update([
            'status' => Invoice::STATUS_PAID,
            'payment_method' => Invoice::METHOD_CASH,
            'paid_at' => now(),
        ]);

        return $back->with('status', "Cash payment recorded. Invoice #{$invoice->id} is now paid.");
    }

    /**
     * Email the invoice to its customer again.
     */
    public function resend(Invoice $invoice): RedirectResponse
    {
        $email = $invoice->customer->email;

        return redirect()->back(fallback: route('admin.invoices.show', $invoice))
            ->with('status', InvoiceIssued::sendFor($invoice)
                ? "Invoice #{$invoice->id} emailed to {$email}."
                : "The email to {$email} could not be sent. Check the mail settings in .env and try again.");
    }

    /**
     * Download the invoice as a PDF.
     */
    public function download(Invoice $invoice): Response
    {
        $invoice->load('items', 'booking.vehicle', 'customer');

        return Pdf::loadView('invoices.pdf', compact('invoice'))
            ->download("invoice-{$invoice->id}.pdf");
    }

    /**
     * Inventory items for the line-item picker. "available" is what this invoice may bill:
     * current stock plus whatever the invoice's own lines are already holding.
     */
    private function stockOptions(?Invoice $invoice = null): Collection
    {
        $held = $invoice
            ? $invoice->items->whereNotNull('inventory_item_id')->groupBy('inventory_item_id')->map->sum('quantity')
            : collect();

        return InventoryItem::orderBy('name')->get()->map(fn (InventoryItem $item) => [
            'id' => $item->id,
            'name' => $item->name,
            'unit_price' => (float) $item->unit_price,
            'available' => $item->quantity + $held->get($item->id, 0),
        ])->values();
    }
}
