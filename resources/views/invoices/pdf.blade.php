<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Invoice #{{ $invoice->id }}</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; margin: 0; }
            .content { padding: 0 36px 36px; }
            .muted { color: #6b7280; }
            table { width: 100%; border-collapse: collapse; }
            th, td { text-align: left; padding: 6px 4px; border-bottom: 1px solid #e5e7eb; }
            th { color: #6b7280; font-weight: normal; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; }
            .text-right { text-align: right; }
            .totals { margin-top: 16px; }
            .totals td { border-bottom: none; padding: 4px; }
            .totals .label { color: #6b7280; }
            .total-row td { font-weight: bold; font-size: 14px; border-top: 2px solid #4f46e5; padding-top: 10px; color: #0f172a; }
            .header-band { background-color: #0f172a; padding: 28px 36px; margin-bottom: 28px; }
            .header-band h1 { font-size: 20px; margin: 0; color: #ffffff; }
            .header-band .sub { color: #a5b4fc; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; margin: 2px 0 0; }
            .header-band .invoice-no { color: #ffffff; font-size: 16px; font-weight: bold; margin: 0; }
            .header-band .invoice-date { color: #94a3b8; font-size: 11px; margin: 2px 0 8px; }
            .badge { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 11px; font-weight: bold; }
            .badge-paid { background: #d1fae5; color: #065f46; }
            .badge-unpaid { background: #fef3c7; color: #92400e; }
            .section-title { color: #94a3b8; font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; margin: 0 0 4px; }
            .bill-to { margin-top: 4px; }
            .footer-note { margin-top: 40px; color: #94a3b8; font-size: 11px; border-top: 1px solid #e5e7eb; padding-top: 16px; }
        </style>
    </head>
    <body>
        <table class="header-band">
            <tr>
                <td>
                    <p class="sub">{{ __('Vehicle Service Management') }}</p>
                    <h1>AutoCare Pro</h1>
                </td>
                <td class="text-right">
                    <p class="invoice-no">{{ __('Invoice') }} #{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</p>
                    <p class="invoice-date">{{ $invoice->created_at->format('F j, Y') }}</p>
                    <span class="badge {{ $invoice->status === 'paid' ? 'badge-paid' : 'badge-unpaid' }}">
                        {{ $invoice->paymentSummary() ?? ucfirst($invoice->status) }}
                    </span>
                </td>
            </tr>
        </table>

        <div class="content">
            <p class="section-title">{{ __('Billed To') }}</p>
            <p class="bill-to">
                <strong>{{ $invoice->customer->name }}</strong><br>
                {{ $invoice->customer->email }}
            </p>

            @if ($invoice->booking)
                <p class="muted">
                    {{ $invoice->booking->vehicle->display_name }}
                    ({{ $invoice->booking->vehicle->plate_number }}) — {{ $invoice->booking->requested_service }}
                </p>
            @endif

            <table style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th>{{ __('Description') }}</th>
                        <th class="text-right">{{ __('Qty') }}</th>
                        <th class="text-right">{{ __('Unit Price') }}</th>
                        <th class="text-right">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td class="text-right">{{ $item->quantity }}</td>
                            <td class="text-right">Rs {{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right">Rs {{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="totals" style="width: 250px; margin-left: auto;">
                <tr>
                    <td class="label">{{ __('Subtotal') }}</td>
                    <td class="text-right">Rs {{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">{{ __('Tax') }}</td>
                    <td class="text-right">Rs {{ number_format($invoice->tax, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>{{ __('Total') }}</td>
                    <td class="text-right">Rs {{ number_format($invoice->total, 2) }}</td>
                </tr>
            </table>

            <p class="footer-note">{{ __('Thank you for choosing AutoCare Pro.') }}</p>
        </div>
    </body>
</html>
