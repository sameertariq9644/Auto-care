<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Mechanic;
use App\Models\ServiceBooking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class ServiceHistoryAndInventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_mechanic_completing_a_booking_creates_a_service_record_and_deducts_inventory(): void
    {
        Notification::fake();

        [$mechanicUser, $mechanic] = $this->createMechanic();
        $customer = User::factory()->create()->assignRole('customer');
        $vehicle = $this->createVehicle($customer);
        $booking = $this->createInProgressBooking($vehicle, $customer, $mechanic);

        $oilFilter = InventoryItem::create([
            'name' => 'Oil Filter',
            'sku' => 'OIL-001',
            'quantity' => 10,
            'unit_price' => 12.50,
            'reorder_level' => 2,
        ]);

        $this->actingAs($mechanicUser)
            ->post(route('mechanic.bookings.complete.store', $booking), [
                'description' => 'Replaced oil filter and topped up fluids.',
                'labor_cost' => 60,
                'items' => [
                    ['inventory_item_id' => $oilFilter->id, 'quantity' => 3],
                ],
            ])
            ->assertRedirect(route('mechanic.bookings.show', $booking));

        $booking->refresh();
        $this->assertEquals(ServiceBooking::STATUS_COMPLETED, $booking->status);

        $record = $booking->serviceRecords()->firstOrFail();
        $this->assertEquals('Replaced oil filter and topped up fluids.', $record->description);
        $this->assertEquals(1, $record->items()->count());

        $this->assertEquals(7, $oilFilter->fresh()->quantity);

        // The vehicle's public history page (what the QR code opens) now shows the work done.
        $this->get(route('vehicles.history', $vehicle->qr_uuid))
            ->assertOk()
            ->assertSee('Replaced oil filter and topped up fluids.')
            ->assertSee('Oil Filter');
    }

    public function test_mechanic_cannot_use_more_stock_than_is_available(): void
    {
        [$mechanicUser, $mechanic] = $this->createMechanic();
        $customer = User::factory()->create()->assignRole('customer');
        $vehicle = $this->createVehicle($customer);
        $booking = $this->createInProgressBooking($vehicle, $customer, $mechanic);

        $brakePads = InventoryItem::create([
            'name' => 'Brake Pads',
            'sku' => 'BRK-001',
            'quantity' => 2,
            'unit_price' => 40,
            'reorder_level' => 1,
        ]);

        $this->actingAs($mechanicUser)
            ->post(route('mechanic.bookings.complete.store', $booking), [
                'description' => 'Brake job',
                'labor_cost' => 80,
                'items' => [
                    ['inventory_item_id' => $brakePads->id, 'quantity' => 5],
                ],
            ])
            ->assertSessionHasErrors('items.0.quantity');

        $this->assertEquals(2, $brakePads->fresh()->quantity);
        $this->assertEquals(ServiceBooking::STATUS_IN_PROGRESS, $booking->fresh()->status);
    }

    public function test_admin_can_manage_inventory_items(): void
    {
        $admin = User::factory()->create()->assignRole('admin');

        $this->actingAs($admin)->post(route('admin.inventory.store'), [
            'name' => 'Air Filter',
            'sku' => 'AIR-001',
            'quantity' => 5,
            'unit_price' => 15,
            'reorder_level' => 2,
        ])->assertRedirect(route('admin.inventory.index'));

        $item = InventoryItem::where('sku', 'AIR-001')->firstOrFail();
        $this->assertEquals('Air Filter', $item->name);

        $this->actingAs($admin)
            ->patch(route('admin.inventory.update', $item), [
                'name' => 'Air Filter',
                'sku' => 'AIR-001',
                'quantity' => 1,
                'unit_price' => 15,
                'reorder_level' => 2,
            ])
            ->assertRedirect(route('admin.inventory.index'));

        $this->assertTrue($item->fresh()->isLowStock());
    }

    public function test_non_admin_cannot_manage_inventory(): void
    {
        $customer = User::factory()->create()->assignRole('customer');

        $this->actingAs($customer)
            ->get(route('admin.inventory.index'))
            ->assertForbidden();
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
            'plate_number' => 'SVC-'.Str::random(5),
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
}
