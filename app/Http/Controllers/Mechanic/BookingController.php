<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;
use App\Models\ServiceBooking;
use App\Notifications\BookingStatusUpdated;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings assigned to the authenticated mechanic.
     */
    public function index(): View
    {
        $bookings = $this->mechanicBookings()->with('vehicle')->latest('preferred_date')->paginate(15);

        return view('mechanic.bookings.index', compact('bookings'));
    }

    /**
     * Display the specified booking.
     */
    public function show(ServiceBooking $booking): View
    {
        abort_unless($booking->mechanic_id === Auth::user()->mechanic->id, 403);

        $booking->load(['vehicle', 'customer', 'statusHistories.changedBy']);

        return view('mechanic.bookings.show', compact('booking'));
    }

    /**
     * Mark an assigned, confirmed booking as in progress.
     */
    public function start(ServiceBooking $booking): RedirectResponse
    {
        abort_unless($booking->mechanic_id === Auth::user()->mechanic->id, 403);
        abort_unless($booking->status === ServiceBooking::STATUS_CONFIRMED, 422, 'Only confirmed bookings can be started.');

        $booking->transitionTo(ServiceBooking::STATUS_IN_PROGRESS, Auth::user());
        $booking->customer->notify(new BookingStatusUpdated($booking));

        return redirect()->route('mechanic.bookings.show', $booking)
            ->with('status', 'Booking marked as in progress.');
    }

    private function mechanicBookings(): HasMany
    {
        return Auth::user()->mechanic->bookings();
    }
}
