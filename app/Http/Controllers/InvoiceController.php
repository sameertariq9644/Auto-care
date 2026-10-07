<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Invoice::class, 'invoice');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $invoices = Auth::user()->invoices()->with('booking.vehicle')->latest()->paginate(10);

        return view('customer.invoices.index', compact('invoices'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load('items', 'booking.vehicle');

        return view('customer.invoices.show', compact('invoice'));
    }

    /**
     * Download the invoice as a PDF.
     */
    public function download(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        $invoice->load('items', 'booking.vehicle', 'customer');

        return Pdf::loadView('invoices.pdf', compact('invoice'))
            ->download("invoice-{$invoice->id}.pdf");
    }

    /**
     * Show the payment page for an unpaid invoice.
     */
    public function pay(Invoice $invoice): View|RedirectResponse
    {
        $this->authorize('view', $invoice);

        if ($invoice->status === Invoice::STATUS_PAID) {
            return redirect()->route('invoices.show', $invoice)
                ->with('status', 'This invoice has already been paid.');
        }

        $invoice->load('items', 'booking.vehicle');

        return view('customer.invoices.pay', compact('invoice'));
    }
}
