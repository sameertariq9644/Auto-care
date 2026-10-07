<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;
use App\Models\ServiceBooking;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $mechanic = Auth::user()->mechanic;

        $activeStatuses = [ServiceBooking::STATUS_CONFIRMED, ServiceBooking::STATUS_IN_PROGRESS];

        $todaysJobs = $mechanic->bookings()
            ->whereIn('status', $activeStatuses)
            ->whereDate('preferred_date', today())
            ->with('vehicle')
            ->orderBy('preferred_date')
            ->get();

        $jobQueue = $mechanic->bookings()
            ->whereIn('status', $activeStatuses)
            ->whereDate('preferred_date', '>', today())
            ->with('vehicle')
            ->orderBy('preferred_date')
            ->limit(10)
            ->get();

        return view('mechanic.dashboard', compact('todaysJobs', 'jobQueue'));
    }
}
