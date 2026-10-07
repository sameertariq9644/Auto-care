<?php

namespace Tests\Unit;

use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\ServiceBooking;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvoiceCalculationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_invoice_totals_labor_and_parts_with_tax(): void
    {
        $record = $this->createServiceRecord(laborCost: 60, parts: [
            ['unit_price' => 12.50, 'quantity' => 2], // 25.00
            ['unit_price' => 5.00, 'quantity' => 3],  // 15.00
        ]);

        $invoice = Invoice::generateFromServiceRecord($record);

        // subtotal = 60 (labor) + 25 + 15 = 100
        $this->assertEquals(100.0, (float) $invoice->subtotal);
        $this->assertEquals(round(100 * Invoice::TAX_RATE, 2), (float) $invoice->tax);
        $this->assertEquals(108.0, (float) $invoice->total);
        $this->assertEquals(Invoice::STATUS_UNPAID, $invoice->status);
        // one line for labor + one per distinct part
        $this->assertEquals(3, $invoice->items()->count());
    }

    public function test_invoice_has_no_labor_line_when_labor_cost_is_zero(): void
    {
        $record = $this->createServiceRecord(laborCost: 0, parts: [
            ['unit_price' => 10, 'quantity' => 1],
        ]);

        $invoice = Invoice::generateFromServiceRecord($record);

        $this->assertEquals(10.0, (float) $invoice->subtotal);
        $this->assertEquals(1, $invoice->items()->count());
        $this->assertFalse($invoice->items()->where('description', 'like', 'Labor:%')->exists());
    }

    public function test_invoice_with_only_labor_and_no_parts(): void
    {
        $record = $this->createServiceRecord(laborCost: 45, parts: []);

        $invoice = Invoice::generateFromServiceRecord($record);

        $this->assertEquals(45.0, (float) $invoice->subtotal);
        $this->assertEquals(round(45 * Invoice::TAX_RATE, 2), (float) $invoice->tax);
        $this->assertEquals(1, $invoice->items()->count());
    }

    public function test_invoice_is_linked_to_the_vehicle_owner_and_booking(): void
    {
        $record = $this->createServiceRecord(laborCost: 20, parts: []);

        $invoice = Invoice::generateFromServiceRecord($record);

        $this->assertEquals($record->vehicle->user_id, $invoice->customer_id);
        $this->assertEquals($record->booking_id, $invoice->booking_id);
    }

    /**
     * @param  array<int, array{unit_price: float, quantity: int}>  $parts
     */
    private function createServiceRecord(float $laborCost, array $parts): ServiceRecord
    {
        $customer = User::factory()->create()->assignRole('customer');

        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'plate_number' => 'CALC-'.Str::random(5),
            'qr_uuid' => (string) Str::uuid(),
        ]);

        $booking = ServiceBooking::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'requested_service' => 'Oil Change',
            'preferred_date' => now(),
            'status' => ServiceBooking::STATUS_IN_PROGRESS,
        ]);

        $record = ServiceRecord::create([
            'vehicle_id' => $vehicle->id,
            'booking_id' => $booking->id,
            'description' => 'Test service',
            'labor_cost' => $laborCost,
            'serviced_at' => now(),
        ]);

        foreach ($parts as $part) {
            $item = InventoryItem::create([
                'name' => 'Part '.Str::random(5),
                'sku' => 'SKU-'.Str::random(8),
                'quantity' => 100,
                'unit_price' => $part['unit_price'],
                'reorder_level' => 1,
            ]);

            $record->items()->create([
                'inventory_item_id' => $item->id,
                'quantity_used' => $part['quantity'],
                'unit_price' => $part['unit_price'],
            ]);
        }

        return $record->fresh(['items.inventoryItem', 'booking', 'vehicle']);
    }
}
