<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConfirmBookingRequest;
use App\Models\Mechanic;
use App\Models\ServiceBooking;
use App\Notifications\BookingStatusUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Display a listing of all bookings, pending ones first.
     */
    public function index(): View
    {
        $bookings = ServiceBooking::with(['vehicle', 'customer', 'mechanic.user'])
            ->orderByRaw("CASE status
                WHEN 'pending' THEN 0
                WHEN 'confirmed' THEN 1
                WHEN 'in_progress' THEN 2
                WHEN 'completed' THEN 3
                WHEN 'cancelled' THEN 4
                ELSE 5 END")
            ->latest('preferred_date')
            ->paginate(15);

        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * Display the specified booking, with mechanic assignment.
     */
    public function show(ServiceBooking $booking): View
    {
        $booking->load(['vehicle', 'customer', 'mechanic.user', 'statusHistories.changedBy']);
        $mechanics = Mechanic::with('user')->where('status', 'active')->get();

        return view('admin.bookings.show', compact('booking', 'mechanics'));
    }

    /**
     * Confirm a pending booking and assign a mechanic.
     */
    public function confirm(ConfirmBookingRequest $request, ServiceBooking $booking): RedirectResponse
    {
        // A double-click or a stale tab can resubmit after the booking has already moved on.
        if ($booking->status !== ServiceBooking::STATUS_PENDING) {
            return redirect()->route('admin.bookings.show', $booking)
                ->with('status', 'This booking is already '.str_replace('_', ' ', $booking->status).' — nothing was changed.');
        }

        $booking->update(['mechanic_id' => $request->validated('mechanic_id')]);
        $booking->transitionTo(ServiceBooking::STATUS_CONFIRMED, Auth::user());
        $booking->customer->notify(new BookingStatusUpdated($booking));

        return redirect()->route('admin.bookings.show', $booking)
            ->with('status', 'Booking confirmed and mechanic assigned.');
    }

    /**
     * Cancel a booking that hasn't been completed yet.
     */
    public function cancel(ServiceBooking $booking): RedirectResponse
    {
        if (in_array($booking->status, [ServiceBooking::STATUS_COMPLETED, ServiceBooking::STATUS_CANCELLED], true)) {
            return redirect()->route('admin.bookings.show', $booking)
                ->with('status', 'This booking is already '.$booking->status.' and can no longer be cancelled.');
        }

        $booking->transitionTo(ServiceBooking::STATUS_CANCELLED, Auth::user(), 'Cancelled by admin.');
        $booking->customer->notify(new BookingStatusUpdated($booking));

        return redirect()->route('admin.bookings.show', $booking)
            ->with('status', 'Booking cancelled.');
    }
}
