<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InventoryItem;
use App\Models\Mechanic;
use App\Models\ServiceBooking;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $bookingCounts = ServiceBooking::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $pendingBookings = ServiceBooking::where('status', ServiceBooking::STATUS_PENDING)
            ->with('vehicle', 'customer')
            ->orderBy('preferred_date')
            ->limit(5)
            ->get();

        $revenuePaid = Invoice::where('status', Invoice::STATUS_PAID)->sum('total');
        $revenueOutstanding = Invoice::where('status', Invoice::STATUS_UNPAID)->sum('total');

        $lowStockItems = InventoryItem::whereColumn('quantity', '<=', 'reorder_level')->get();

        $mechanicWorkload = Mechanic::with('user')
            ->withCount(['bookings as active_bookings_count' => function ($query) {
                $query->whereIn('status', [ServiceBooking::STATUS_CONFIRMED, ServiceBooking::STATUS_IN_PROGRESS]);
            }])
            ->where('status', 'active')
            ->get();

        return view('admin.dashboard', compact(
            'bookingCounts',
            'pendingBookings',
            'revenuePaid',
            'revenueOutstanding',
            'lowStockItems',
            'mechanicWorkload',
        ));
    }
}
