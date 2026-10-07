<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Mechanic;
use App\Models\ServiceBooking;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\InvoiceIssued;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvoicingTest extends TestCase
{
    use RefreshDatabase;

    public function test_completing_a_booking_auto_generates_an_invoice_with_labor_and_parts(): void
    {
        Notification::fake();

        [$mechanicUser, $mechanic] = $this->createMechanic();
        $customer = User::factory()->create()->assignRole('customer');
        $vehicle = $this->createVehicle($customer);
        $booking = $this->createInProgressBooking($vehicle, $customer, $mechanic);

        $part = \App\Models\InventoryItem::create([
            'name' => 'Oil Filter',
            'sku' => 'OIL-100',
            'quantity' => 10,
            'unit_price' => 12.50,
            'reorder_level' => 2,
        ]);

        $this->actingAs($mechanicUser)->post(route('mechanic.bookings.complete.store', $booking), [
            'description' => 'Oil change',
            'labor_cost' => 40,
            'items' => [
                ['inventory_item_id' => $part->id, 'quantity' => 2],
            ],
        ]);

        $invoice = Invoice::where('customer_id', $customer->id)->firstOrFail();

        $this->assertEquals($booking->id, $invoice->booking_id);
        $this->assertEquals(Invoice::STATUS_UNPAID, $invoice->status);
        // subtotal = 40 labor + (2 * 12.50) parts = 65
        $this->assertEquals(65.0, (float) $invoice->subtotal);
        $this->assertEquals(round(65 * Invoice::TAX_RATE, 2), (float) $invoice->tax);
        $this->assertEquals((float) $invoice->subtotal + (float) $invoice->tax, (float) $invoice->total);
        $this->assertEquals(2, $invoice->items()->count());
    }

    public function test_customer_can_view_and_download_their_own_invoice(): void
    {
        $customer = User::factory()->create()->assignRole('customer');
        $invoice = $this->createInvoiceFor($customer);

        $this->actingAs($customer)
            ->get(route('invoices.show', $invoice))
            ->assertOk();

        $this->actingAs($customer)
            ->get(route('invoices.download', $invoice))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_customer_cannot_view_another_customers_invoice(): void
    {
        $owner = User::factory()->create()->assignRole('customer');
        $intruder = User::factory()->create()->assignRole('customer');
        $invoice = $this->createInvoiceFor($owner);

        $this->actingAs($intruder)
            ->get(route('invoices.show', $invoice))
            ->assertForbidden();
    }

    public function test_admin_can_edit_an_unpaid_invoice_and_mark_it_paid(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        $customer = User::factory()->create()->assignRole('customer');
        $invoice = $this->createInvoiceFor($customer);

        $this->actingAs($admin)->put(route('admin.invoices.update', $invoice), [
            'tax' => 5,
            'items' => [
                ['description' => 'Labor', 'quantity' => 1, 'unit_price' => 100],
            ],
        ])->assertRedirect(route('admin.invoices.show', $invoice));

        $invoice->refresh();
        $this->assertEquals(100.0, (float) $invoice->subtotal);
        $this->assertEquals(105.0, (float) $invoice->total);
        $this->assertEquals(1, $invoice->items()->count());

        $this->actingAs($admin)
            ->patch(route('admin.invoices.markPaid', $invoice))
            ->assertRedirect(route('admin.invoices.show', $invoice));

        $this->assertEquals(Invoice::STATUS_PAID, $invoice->fresh()->status);
    }

    public function test_admin_records_a_cash_payment_and_the_customer_sees_the_invoice_as_paid(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        $customer = User::factory()->create()->assignRole('customer');
        $invoice = $this->createInvoiceFor($customer);

        $this->actingAs($admin)->get(route('admin.invoices.index'))->assertOk()->assertSee('Cash Received');
        $this->actingAs($customer)->get(route('customer.dashboard'))->assertOk()->assertSee('Unpaid');

        // Clicked from the invoice list, so the admin lands back on the list.
        $this->actingAs($admin)
            ->from(route('admin.invoices.index'))
            ->patch(route('admin.invoices.markPaid', $invoice))
            ->assertRedirect(route('admin.invoices.index'));

        $invoice->refresh();
        $this->assertEquals(Invoice::STATUS_PAID, $invoice->status);
        $this->assertEquals(Invoice::METHOD_CASH, $invoice->payment_method);
        $this->assertNotNull($invoice->paid_at);

        $this->actingAs($admin)->get(route('admin.invoices.index'))->assertOk()->assertDontSee('Cash Received');
        $this->actingAs($admin)->get(route('admin.invoices.show', $invoice))->assertOk()->assertSee('Paid in cash');

        $this->actingAs($customer)->get(route('customer.dashboard'))->assertOk()->assertSee('Paid')->assertDontSee('Unpaid');
        $this->actingAs($customer)->get(route('invoices.index'))->assertOk()->assertDontSee(route('invoices.pay', $invoice));
        $this->actingAs($customer)->get(route('invoices.show', $invoice))->assertOk()->assertSee('Paid in cash')->assertDontSee('Pay Now');
        $this->actingAs($customer)->get(route('invoices.download', $invoice))->assertOk();

        // A second click must not move the payment date.
        $paidAt = $invoice->paid_at;
        $this->travel(1)->days();
        $this->actingAs($admin)->patch(route('admin.invoices.markPaid', $invoice));
        $this->assertEquals($paidAt, $invoice->fresh()->paid_at);
    }

    public function test_admin_can_resend_the_invoice_email(): void
    {
        Notification::fake();

        $admin = User::factory()->create()->assignRole('admin');
        $customer = User::factory()->create()->assignRole('customer');
        $invoice = $this->createInvoiceFor($customer);

        $this->actingAs($admin)->get(route('admin.invoices.show', $invoice))->assertOk()->assertSee('Resend Email');

        $this->actingAs($admin)
            ->post(route('admin.invoices.resend', $invoice))
            ->assertRedirect(route('admin.invoices.show', $invoice))
            ->assertSessionHas('status', "Invoice #{$invoice->id} emailed to {$customer->email}.");

        Notification::assertSentToTimes($customer, InvoiceIssued::class, 1);

        // Paid invoices can be resent too, without asking for payment again.
        $invoice->update(['status' => Invoice::STATUS_PAID, 'payment_method' => Invoice::METHOD_CASH, 'paid_at' => now()]);
        $this->actingAs($admin)->post(route('admin.invoices.resend', $invoice));
        Notification::assertSentToTimes($customer, InvoiceIssued::class, 2);

        $mail = (new InvoiceIssued($invoice->fresh()))->toMail($customer);
        $this->assertEquals('View Invoice', $mail->actionText);
        $this->assertStringContainsString('Paid in cash', implode(' ', $mail->introLines));

        $this->actingAs($customer)->post(route('admin.invoices.resend', $invoice))->assertForbidden();
    }

    public function test_a_mail_failure_is_reported_to_the_admin_instead_of_crashing(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1, 'mail.mailers.smtp.timeout' => 2]);

        $admin = User::factory()->create()->assignRole('admin');
        $customer = User::factory()->create()->assignRole('customer');
        $invoice = $this->createInvoiceFor($customer);

        $this->actingAs($admin)
            ->post(route('admin.invoices.resend', $invoice))
            ->assertRedirect(route('admin.invoices.show', $invoice))
            ->assertSessionHas('status', "The email to {$customer->email} could not be sent. Check the mail settings in .env and try again.");
    }

    public function test_paid_invoices_cannot_be_edited(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        $customer = User::factory()->create()->assignRole('customer');
        $invoice = $this->createInvoiceFor($customer);
        $invoice->update(['status' => Invoice::STATUS_PAID]);

        $this->actingAs($admin)
            ->get(route('admin.invoices.edit', $invoice))
            ->assertStatus(422);
    }

    public function test_admin_can_create_an_invoice_that_deducts_stock_and_emails_the_customer(): void
    {
        Notification::fake();

        $admin = User::factory()->create()->assignRole('admin');
        $customer = User::factory()->create()->assignRole('customer');
        $oil = $this->createPart('Engine Oil', 20, 100);

        $this->actingAs($admin)->get(route('admin.invoices.create'))->assertOk();

        $response = $this->actingAs($admin)->post(route('admin.invoices.store'), [
            'customer_id' => $customer->id,
            'tax' => 10,
            'items' => [
                ['inventory_item_id' => $oil->id, 'description' => 'Engine Oil', 'quantity' => 4, 'unit_price' => 100],
                ['inventory_item_id' => null, 'description' => 'Brake service labor', 'quantity' => 1, 'unit_price' => 250],
            ],
        ]);

        $invoice = Invoice::firstOrFail();
        $response->assertRedirect(route('admin.invoices.show', $invoice));

        $this->assertEquals($customer->id, $invoice->customer_id);
        $this->assertEquals(650.0, (float) $invoice->subtotal);
        $this->assertEquals(660.0, (float) $invoice->total);
        $this->assertEquals(Invoice::STATUS_UNPAID, $invoice->status);
        $this->assertEquals(16, $oil->fresh()->quantity);

        Notification::assertSentToTimes($customer, InvoiceIssued::class, 1);

        // The customer sees it on their invoices page with a Pay Now button.
        $this->actingAs($customer)->get(route('invoices.index'))->assertOk()->assertSee(route('invoices.pay', $invoice));
        $this->actingAs($customer)->get(route('invoices.show', $invoice))->assertOk()->assertSee('Pay Now');
        $this->actingAs($customer)->get(route('invoices.pay', $invoice))->assertOk();
        $this->actingAs($admin)->get(route('admin.invoices.show', $invoice))->assertOk();
        $this->actingAs($admin)->get(route('admin.invoices.edit', $invoice))->assertOk();
    }

    public function test_invoice_email_lists_the_total_and_attaches_the_pdf(): void
    {
        $customer = User::factory()->create()->assignRole('customer');
        $invoice = $this->createInvoiceFor($customer);

        $mail = (new InvoiceIssued($invoice))->toMail($customer);

        $this->assertStringContainsString('54.00', implode(' ', $mail->introLines));
        $this->assertEquals(route('invoices.show', $invoice), $mail->actionUrl);
        $this->assertCount(1, $mail->rawAttachments);
    }

    public function test_invoice_cannot_bill_more_parts_than_are_in_stock(): void
    {
        Notification::fake();

        $admin = User::factory()->create()->assignRole('admin');
        $customer = User::factory()->create()->assignRole('customer');
        $oil = $this->createPart('Engine Oil', 3, 100);

        $this->actingAs($admin)->post(route('admin.invoices.store'), [
            'customer_id' => $customer->id,
            'tax' => 0,
            'items' => [
                ['inventory_item_id' => $oil->id, 'description' => 'Engine Oil', 'quantity' => 5, 'unit_price' => 100],
            ],
        ])->assertSessionHasErrors('items.0.quantity');

        $this->assertEquals(0, Invoice::count());
        $this->assertEquals(3, $oil->fresh()->quantity);
        Notification::assertNothingSent();
    }

    public function test_editing_an_invoice_adjusts_stock_by_the_difference(): void
    {
        Notification::fake();

        $admin = User::factory()->create()->assignRole('admin');
        $customer = User::factory()->create()->assignRole('customer');
        $oil = $this->createPart('Engine Oil', 20, 100);
        $pads = $this->createPart('Brake Pads', 8, 300);

        $this->actingAs($admin)->post(route('admin.invoices.store'), [
            'customer_id' => $customer->id,
            'tax' => 0,
            'items' => [
                ['inventory_item_id' => $oil->id, 'description' => 'Engine Oil', 'quantity' => 4, 'unit_price' => 100],
            ],
        ]);

        $invoice = Invoice::firstOrFail();

        // Oil 4 -> 6, and add 2 brake pads.
        $this->actingAs($admin)->put(route('admin.invoices.update', $invoice), [
            'tax' => 0,
            'items' => [
                ['inventory_item_id' => $oil->id, 'description' => 'Engine Oil', 'quantity' => 6, 'unit_price' => 100],
                ['inventory_item_id' => $pads->id, 'description' => 'Brake Pads', 'quantity' => 2, 'unit_price' => 300],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertEquals(14, $oil->fresh()->quantity);
        $this->assertEquals(6, $pads->fresh()->quantity);
        $this->assertEquals(1200.0, (float) $invoice->fresh()->total);

        // Removing the oil line returns it to stock.
        $this->actingAs($admin)->put(route('admin.invoices.update', $invoice), [
            'tax' => 0,
            'items' => [
                ['inventory_item_id' => $pads->id, 'description' => 'Brake Pads', 'quantity' => 2, 'unit_price' => 300],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertEquals(20, $oil->fresh()->quantity);
        $this->assertEquals(6, $pads->fresh()->quantity);
    }

    public function test_invoice_booking_must_belong_to_the_chosen_customer(): void
    {
        Notification::fake();

        $admin = User::factory()->create()->assignRole('admin');
        $customer = User::factory()->create()->assignRole('customer');
        $other = User::factory()->create()->assignRole('customer');
        [, $mechanic] = $this->createMechanic();
        $booking = $this->createInProgressBooking($this->createVehicle($other), $other, $mechanic);

        $this->actingAs($admin)->post(route('admin.invoices.store'), [
            'customer_id' => $customer->id,
            'booking_id' => $booking->id,
            'tax' => 0,
            'items' => [['description' => 'Labor', 'quantity' => 1, 'unit_price' => 50]],
        ])->assertSessionHasErrors('booking_id');

        $this->assertEquals(0, Invoice::count());
    }

    public function test_customer_cannot_open_the_pay_page_of_another_customers_invoice(): void
    {
        $owner = User::factory()->create()->assignRole('customer');
        $intruder = User::factory()->create()->assignRole('customer');
        $invoice = $this->createInvoiceFor($owner);

        $this->actingAs($intruder)->get(route('invoices.pay', $invoice))->assertForbidden();

        $invoice->update(['status' => Invoice::STATUS_PAID]);
        $this->actingAs($owner)->get(route('invoices.pay', $invoice))->assertRedirect(route('invoices.show', $invoice));
        $this->actingAs($owner)->get(route('invoices.show', $invoice))->assertOk()->assertDontSee('Pay Now');
    }

    private function createPart(string $name, int $quantity, float $price): \App\Models\InventoryItem
    {
        return \App\Models\InventoryItem::create([
            'name' => $name,
            'sku' => Str::upper(Str::random(8)),
            'quantity' => $quantity,
            'unit_price' => $price,
            'reorder_level' => 1,
        ]);
    }

    private function createMechanic(): array
    {
        $user = User::factory()->create()->assignRole('mechanic');
        $mechanic = Mechanic::create(['user_id' => $user->id, 'status' => 'active']);

        return [$user, $mechanic];
    }

    private function createVehicle(User $owner): Vehicle
    {
        return Vehicle::create([
            'user_id' => $owner->id,
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'plate_number' => 'INV-'.Str::random(5),
            'qr_uuid' => (string) Str::uuid(),
        ]);
    }

    private function createInProgressBooking(Vehicle $vehicle, User $customer, Mechanic $mechanic): ServiceBooking
    {
        return ServiceBooking::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'mechanic_id' => $mechanic->id,
            'requested_service' => 'Oil Change',
            'preferred_date' => now()->addDay(),
            'status' => ServiceBooking::STATUS_IN_PROGRESS,
        ]);
    }

    private function createInvoiceFor(User $customer): Invoice
    {
        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'subtotal' => 50,
            'tax' => 4,
            'total' => 54,
            'status' => Invoice::STATUS_UNPAID,
        ]);

        $invoice->items()->create([
            'description' => 'Labor',
            'quantity' => 1,
            'unit_price' => 50,
        ]);

        return $invoice;
    }
}
