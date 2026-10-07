<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InventoryItem;
use App\Models\Mechanic;
use App\Models\ServiceBooking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_dashboard_shows_their_vehicles_bookings_and_invoices(): void
    {
        $customer = User::factory()->create()->assignRole('customer');
        $vehicle = $this->createVehicle($customer);

        $booking = ServiceBooking::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'requested_service' => 'Oil Change',
            'preferred_date' => now()->addDay(),
            'status' => ServiceBooking::STATUS_PENDING,
        ]);

        Invoice::create([
            'customer_id' => $customer->id,
            'subtotal' => 50,
            'tax' => 4,
            'total' => 54,
            'status' => Invoice::STATUS_UNPAID,
        ]);

        $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertRedirect(route('customer.dashboard'));

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Oil Change')
            ->assertSee('54.00');
    }

    public function test_mechanic_dashboard_shows_todays_and_upcoming_jobs(): void
    {
        $mechanicUser = User::factory()->create()->assignRole('mechanic');
        $mechanic = Mechanic::create(['user_id' => $mechanicUser->id, 'status' => 'active']);
        $customer = User::factory()->create()->assignRole('customer');
        $vehicle = $this->createVehicle($customer);

        ServiceBooking::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'mechanic_id' => $mechanic->id,
            'requested_service' => 'Brake Inspection',
            'preferred_date' => now()->setTime(15, 0),
            'status' => ServiceBooking::STATUS_CONFIRMED,
        ]);

        $this->actingAs($mechanicUser)
            ->get(route('dashboard'))
            ->assertRedirect(route('mechanic.dashboard'));

        $this->actingAs($mechanicUser)
            ->get(route('mechanic.dashboard'))
            ->assertOk()
            ->assertSee('Brake Inspection')
            ->assertSee('Start');
    }

    public function test_admin_dashboard_shows_overview_revenue_and_low_stock(): void
    {
        $admin = User::factory()->create()->assignRole('admin');
        $customer = User::factory()->create()->assignRole('customer');
        $vehicle = $this->createVehicle($customer);

        ServiceBooking::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'requested_service' => 'Diagnostic',
            'preferred_date' => now()->addDay(),
            'status' => ServiceBooking::STATUS_PENDING,
        ]);

        Invoice::create([
            'customer_id' => $customer->id,
            'subtotal' => 100,
            'tax' => 8,
            'total' => 108,
            'status' => Invoice::STATUS_PAID,
        ]);

        InventoryItem::create([
            'name' => 'Brake Fluid',
            'sku' => 'BF-001',
            'quantity' => 1,
            'unit_price' => 10,
            'reorder_level' => 5,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Diagnostic')
            ->assertSee('Brake Fluid')
            ->assertSee('108.00');
    }

    private function createVehicle(User $owner): Vehicle
    {
        return Vehicle::create([
            'user_id' => $owner->id,
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'plate_number' => 'DASH-'.Str::random(5),
            'qr_uuid' => (string) Str::uuid(),
        ]);
    }
}
