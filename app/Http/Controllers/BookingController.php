<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\ServiceBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ServiceBooking::class, 'booking');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $bookings = Auth::user()->bookings()->with(['vehicle', 'mechanic.user'])->latest('preferred_date')->paginate(10);

        return view('customer.bookings.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $vehicles = Auth::user()->vehicles()->get();
        $serviceTypes = ServiceBooking::SERVICE_TYPES;

        return view('customer.bookings.create', compact('vehicles', 'serviceTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $booking = ServiceBooking::create([
            ...$request->validated(),
            'customer_id' => Auth::id(),
            'status' => ServiceBooking::STATUS_PENDING,
        ]);

        $booking->statusHistories()->create([
            'status' => ServiceBooking::STATUS_PENDING,
            'changed_by' => Auth::id(),
        ]);

        return redirect()->route('bookings.show', $booking)
            ->with('status', 'Booking submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceBooking $booking): View
    {
        $booking->load(['vehicle', 'mechanic.user', 'statusHistories.changedBy']);

        return view('customer.bookings.show', compact('booking'));
    }

    /**
     * Cancel the specified booking.
     */
    public function cancel(ServiceBooking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        $booking->transitionTo(ServiceBooking::STATUS_CANCELLED, Auth::user(), 'Cancelled by customer.');

        return redirect()->route('bookings.show', $booking)
            ->with('status', 'Booking cancelled.');
    }
}
