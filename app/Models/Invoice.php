<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

#[Fillable(['booking_id', 'customer_id', 'subtotal', 'tax', 'total', 'status', 'payment_method', 'paid_at', 'pdf_path'])]
class Invoice extends Model
{
    use HasFactory;

    public const STATUS_UNPAID = 'unpaid';

    public const STATUS_PAID = 'paid';

    public const METHOD_CASH = 'cash';

    public const TAX_RATE = 0.08;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * "Paid in cash on Oct 2, 2026" for a paid invoice, or null while it is unpaid.
     * Invoices paid before payment details were recorded just read "Paid".
     */
    public function paymentSummary(): ?string
    {
        if ($this->status !== self::STATUS_PAID) {
            return null;
        }

        return 'Paid'
            .($this->payment_method ? ' in '.$this->payment_method : '')
            .($this->paid_at ? ' on '.$this->paid_at->format('M j, Y') : '');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(ServiceBooking::class, 'booking_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Replace the invoice's line items and recalculate its totals, keeping inventory in step:
     * stock held by the old lines is returned, then stock for the new lines is taken.
     * Call inside a DB transaction so a stock shortage rolls everything back.
     *
     * @param  array<int, array{inventory_item_id?: int|string|null, description: string, quantity: int|string, unit_price: float|string}>  $lines
     *
     * @throws ValidationException
     */
    public function syncItems(array $lines, float $tax): void
    {
        foreach ($this->items()->whereNotNull('inventory_item_id')->get() as $old) {
            InventoryItem::whereKey($old->inventory_item_id)->increment('quantity', $old->quantity);
        }

        $this->items()->delete();

        $subtotal = 0;

        foreach ($lines as $index => $line) {
            $inventoryItemId = $line['inventory_item_id'] ?? null;

            if ($inventoryItemId) {
                $part = InventoryItem::lockForUpdate()->findOrFail($inventoryItemId);

                if ($line['quantity'] > $part->quantity) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" => "Not enough stock for {$part->name}: only {$part->quantity} available.",
                    ]);
                }

                $part->decrement('quantity', $line['quantity']);
            }

            $this->items()->create([
                'inventory_item_id' => $inventoryItemId ?: null,
                'description' => $line['description'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
            ]);

            $subtotal += $line['quantity'] * $line['unit_price'];
        }

        $this->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
        ]);
    }

    /**
     * Build an invoice (with line items) from a completed service record's labor and parts.
     */
    public static function generateFromServiceRecord(ServiceRecord $record): self
    {
        $lines = [];

        if ($record->labor_cost > 0) {
            $lines[] = [
                'description' => 'Labor: '.($record->booking?->requested_service ?? 'Service'),
                'quantity' => 1,
                'unit_price' => $record->labor_cost,
            ];
        }

        foreach ($record->items as $partUsed) {
            $lines[] = [
                'inventory_item_id' => $partUsed->inventory_item_id,
                'description' => $partUsed->inventoryItem->name,
                'quantity' => $partUsed->quantity_used,
                'unit_price' => $partUsed->unit_price,
            ];
        }

        $subtotal = collect($lines)->sum(fn (array $line) => $line['quantity'] * $line['unit_price']);
        $tax = round($subtotal * self::TAX_RATE, 2);

        $invoice = self::create([
            'booking_id' => $record->booking_id,
            'customer_id' => $record->vehicle->user_id,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
            'status' => self::STATUS_UNPAID,
        ]);

        foreach ($lines as $line) {
            $invoice->items()->create($line);
        }

        return $invoice;
    }
}
