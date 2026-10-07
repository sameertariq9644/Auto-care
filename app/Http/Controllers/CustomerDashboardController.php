<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\ServiceBooking;
use App\Models\ServiceRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $vehicles = $user->vehicles()->with(['latestServiceRecord', 'nextBooking'])->latest()->get();

        $upcomingBookings = $user->bookings()
            ->whereIn('status', [
                ServiceBooking::STATUS_PENDING,
                ServiceBooking::STATUS_CONFIRMED,
                ServiceBooking::STATUS_IN_PROGRESS,
            ])
            ->with('vehicle')
            ->orderBy('preferred_date')
            ->limit(5)
            ->get();

        $recentServiceRecords = ServiceRecord::whereIn('vehicle_id', $vehicles->pluck('id'))
            ->with('vehicle')
            ->latest('serviced_at')
            ->limit(5)
            ->get();

        $invoices = $user->invoices()->latest()->limit(5)->get();
        $outstandingTotal = $user->invoices()->where('status', Invoice::STATUS_UNPAID)->sum('total');

        // Chart data for the last 12 months (display only).
        $months = collect(range(11, 0))->map(fn (int $ago) => now()->startOfMonth()->subMonths($ago));
        $since = $months->first();

        $allInvoices = $user->invoices()->where('created_at', '>=', $since)->get(['total', 'created_at']);
        $allBookings = $user->bookings()->get(['requested_service', 'created_at']);

        $monthlySpend = $months->map(fn ($m) => (float) $allInvoices
            ->filter(fn ($i) => $i->created_at->isSameMonth($m))->sum('total'));
        $monthlyBookings = $months->map(fn ($m) => $allBookings
            ->filter(fn ($b) => $b->created_at->isSameMonth($m))->count());
        $monthLabels = $months->map(fn ($m) => $m->format('M'));

        $serviceBreakdown = $allBookings->countBy('requested_service')->sortDesc()->take(6);
        $totalBookings = $allBookings->count();

        return view('customer.dashboard', compact(
            'vehicles',
            'upcomingBookings',
            'recentServiceRecords',
            'invoices',
            'outstandingTotal',
            'monthlySpend',
            'monthlyBookings',
            'monthLabels',
            'serviceBreakdown',
            'totalBookings',
        ));
    }
}
