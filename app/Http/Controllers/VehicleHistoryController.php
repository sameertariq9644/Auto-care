<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\View\View;

class VehicleHistoryController extends Controller
{
    /**
     * Display the public, read-only service history for a vehicle (reached by scanning its QR code).
     */
    public function __invoke(Vehicle $vehicle): View
    {
        $serviceRecords = $vehicle->serviceRecords()->with('mechanic.user', 'items.inventoryItem')->latest('serviced_at')->get();

        return view('vehicles.history', compact('vehicle', 'serviceRecords'));
    }
}
