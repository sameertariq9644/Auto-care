<?php

namespace Tests\Unit;

use App\Models\ServiceBooking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ServiceBookingStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transition_to_updates_the_booking_status(): void
    {
        $booking = $this->createBooking();

        $booking->transitionTo(ServiceBooking::STATUS_CONFIRMED);

        $this->assertEquals(ServiceBooking::STATUS_CONFIRMED, $booking->fresh()->status);
    }

    public function test_transition_to_records_a_status_history_entry_with_the_actor_and_note(): void
    {
        $booking = $this->createBooking();
        $admin = User::factory()->create()->assignRole('admin');

        $booking->transitionTo(ServiceBooking::STATUS_CANCELLED, $admin, 'Cancelled by admin.');

        $history = $booking->statusHistories()->firstOrFail();
        $this->assertEquals(ServiceBooking::STATUS_CANCELLED, $history->status);
        $this->assertEquals($admin->id, $history->changed_by);
        $this->assertEquals('Cancelled by admin.', $history->note);
    }

    public function test_multiple_transitions_accumulate_a_full_timeline(): void
    {
        $booking = $this->createBooking();

        $booking->transitionTo(ServiceBooking::STATUS_CONFIRMED);
        $booking->transitionTo(ServiceBooking::STATUS_IN_PROGRESS);
        $booking->transitionTo(ServiceBooking::STATUS_COMPLETED);

        $this->assertEquals(3, $booking->statusHistories()->count());
        $this->assertEquals(ServiceBooking::STATUS_COMPLETED, $booking->fresh()->status);
    }

    private function createBooking(): ServiceBooking
    {
        $customer = User::factory()->create()->assignRole('customer');

        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'plate_number' => 'TRANS-'.Str::random(5),
            'qr_uuid' => (string) Str::uuid(),
        ]);

        return ServiceBooking::create([
            'vehicle_id' => $vehicle->id,
            'customer_id' => $customer->id,
            'requested_service' => 'Oil Change',
            'preferred_date' => now(),
            'status' => ServiceBooking::STATUS_PENDING,
        ]);
    }
}
