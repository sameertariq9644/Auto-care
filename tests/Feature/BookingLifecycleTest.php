<?php

namespace Tests\Feature;

use App\Models\Mechanic;
use App\Models\ServiceBooking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_book_a_service_for_their_own_vehicle(): void
    {
        $customer = User::factory()->create()->assignRole('customer');
        $vehicle = $this->createVehicle($customer);

        $response = $this->actingAs($customer)->post('/bookings', [
            'vehicle_id' => $vehicle->id,
            'requested_service' => 'Oil Change',
            'preferred_date' => now()->addDay()->format('Y-m-d\TH:i'),
        ]);

        $booking = ServiceBooking::firstOrFail();

        $response->assertRedirect(route('bookings.show', $booking));
        $this->assertEquals(ServiceBooking::STATUS_PENDING, $booking->status);
        $this->assertEquals(1, $booking->statusHistories()->count());
    }

    public function test_customer_cannot_book_a_service_for_someone_elses_vehicle(): void
    {
        $owner = User::factory()->create()->assignRole('customer');
        $other = User::factory()->create()->assignRole('customer');
        $vehicle = $this->createVehicle($owner);

        $this->actingAs($other)->post('/bookings', [
            'vehicle_id' => $vehicle->id,
            'requested_service' => 'Oil Change',
            'preferred_date' => now()->addDay()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('vehicle_id');
    }

    public function test_confirming_an_already_confirmed_booking_changes_nothing(): void
    {
        Notification::fake();

        $admin = User::factory()->create()->assignRole('admin');
        $customer = User::factory()->create()->assignRole('customer');
        $first = Mechanic::create(['user_id' => User::factory()->create()->assignRole('mechanic')->id, 'status' => 'active']);
        $second = Mechanic::create(['user_id' => User::factory()->create()->assignRole('mechanic')->id, 'status' => 'active']);

        $booking = ServiceBooking::create([
            'vehicle_id' => $this->createVehicle($customer)->id,
            'customer_id' => $customer->id,
            'requested_service' => 'Oil Change',
            'preferred_date' => now()->addDay(),
            'status' => ServiceBooking::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.bookings.confirm', $booking), ['mechanic_id' => $first->id])
            ->assertRedirect(route('admin.bookings.show', $booking));

        // Resubmitting (double-click, stale tab) lands back on the booking instead of an error page.
        $this->actingAs($admin)
            ->patch(route('admin.bookings.confirm', $booking), ['mechanic_id' => $second->id])
            ->assertRedirect(route('admin.bookings.show', $booking))
            ->assertSessionHas('status');

        $booking->refresh();
        $this->assertEquals(ServiceBooking::STATUS_CONFIRMED, $booking->status);
        $this->assertEquals($first->id, $booking->mechanic_id);
        Notification::assertSentToTimes($customer, \App\Notifications\BookingStatusUpdated::class, 1);

        $this->actingAs($admin)->patch(route('admin.bookings.cancel', $booking));
        $this->actingAs($admin)
            ->patch(route('admin.bookings.cancel', $booking))
            ->assertRedirect(route('admin.bookings.show', $booking));

        $this->assertEquals(ServiceBooking::STATUS_CANCELLED, $booking->fresh()->status);
    }

    public function test_full_booking_lifecycle_from_pending_to_completed(): void
    {
        Notification::fake();

        $admin = User::factory()->create()->assignRole('admin');
        $customer = User::factory()->create()->assignRole('customer');
        $mechanicUser = User::factory()->create()->assignRole('mechanic');
        $mechanic = Mechanic::create(['user_id' => $mechanicUser->id, 'status' => 'active']);
        $vehicle = $this->createVehicle($customer);

        $booking = ServiceBooking::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'requested_service' => 'Oil Change',
            'preferred_date' => now()->addDay(),
            'status' => ServiceBooking::STATUS_PENDING,
        ]);

        // Admin confirms and assigns the mechanic.
        $this->actingAs($admin)
            ->patch(route('admin.bookings.confirm', $booking), ['mechanic_id' => $mechanic->id])
            ->assertRedirect(route('admin.bookings.show', $booking));

        $booking->refresh();
        $this->assertEquals(ServiceBooking::STATUS_CONFIRMED, $booking->status);
        $this->assertEquals($mechanic->id, $booking->mechanic_id);

        // A different mechanic cannot act on this booking.
        $otherMechanicUser = User::factory()->create()->assignRole('mechanic');
        Mechanic::create(['user_id' => $otherMechanicUser->id, 'status' => 'active']);

        $this->actingAs($otherMechanicUser)
            ->patch(route('mechanic.bookings.start', $booking))
            ->assertForbidden();

        // The assigned mechanic starts the job.
        $this->actingAs($mechanicUser)
            ->patch(route('mechanic.bookings.start', $booking))
            ->assertRedirect(route('mechanic.bookings.show', $booking));

        $booking->refresh();
        $this->assertEquals(ServiceBooking::STATUS_IN_PROGRESS, $booking->status);

        // The assigned mechanic logs the completed work.
        $this->actingAs($mechanicUser)
            ->post(route('mechanic.bookings.complete.store', $booking), [
                'description' => 'Changed the oil and filter.',
                'labor_cost' => 45,
                'items' => [],
            ])
            ->assertRedirect(route('mechanic.bookings.show', $booking));

        $booking->refresh();
        $this->assertEquals(ServiceBooking::STATUS_COMPLETED, $booking->status);
        // confirmed, in_progress, completed (this test seeds the booking directly, skipping the
        // initial "pending" history entry that BookingController::store() would normally create).
        $this->assertEquals(3, $booking->statusHistories()->count());

        // Completed bookings can no longer be cancelled by the customer.
        $this->actingAs($customer)
            ->patch(route('bookings.cancel', $booking))
            ->assertForbidden();
    }

    public function test_customer_can_cancel_a_pending_booking(): void
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

        $this->actingAs($customer)
            ->patch(route('bookings.cancel', $booking))
            ->assertRedirect(route('bookings.show', $booking));

        $this->assertEquals(ServiceBooking::STATUS_CANCELLED, $booking->fresh()->status);
    }

    private function createVehicle(User $owner): Vehicle
    {
        return Vehicle::create([
            'user_id' => $owner->id,
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'plate_number' => 'BOOK-'.Str::random(5),
            'qr_uuid' => (string) Str::uuid(),
        ]);
    }
}
