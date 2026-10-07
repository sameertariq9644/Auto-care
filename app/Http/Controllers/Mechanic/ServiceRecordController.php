<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRecordRequest;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\ServiceBooking;
use App\Notifications\BookingStatusUpdated;
use App\Notifications\InvoiceIssued;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServiceRecordController extends Controller
{
    /**
     * Show the form to log the completed work and close out the booking.
     */
    public function create(ServiceBooking $booking): View
    {
        $this->authorizeBooking($booking);

        $inventoryItems = InventoryItem::where('quantity', '>', 0)->orderBy('name')->get();

        return view('mechanic.service-records.create', compact('booking', 'inventoryItems'));
    }

    /**
     * Log the completed work: create the service record, deduct inventory, and complete the booking.
     */
    public function store(StoreServiceRecordRequest $request, ServiceBooking $booking): RedirectResponse
    {
        $this->authorizeBooking($booking);

        $invoice = DB::transaction(function () use ($request, $booking) {
            $record = $booking->serviceRecords()->create([
                'vehicle_id' => $booking->vehicle_id,
                'mechanic_id' => $booking->mechanic_id,
                'description' => $request->validated('description'),
                'labor_cost' => $request->validated('labor_cost'),
                'serviced_at' => now(),
            ]);

            foreach ($request->validated('items', []) as $item) {
                $inventoryItem = InventoryItem::lockForUpdate()->findOrFail($item['inventory_item_id']);

                abort_if($item['quantity'] > $inventoryItem->quantity, 422, "Not enough stock for {$inventoryItem->name}.");

                $record->items()->create([
                    'inventory_item_id' => $inventoryItem->id,
                    'quantity_used' => $item['quantity'],
                    'unit_price' => $inventoryItem->unit_price,
                ]);

                $inventoryItem->decrement('quantity', $item['quantity']);
            }

            $booking->transitionTo(ServiceBooking::STATUS_COMPLETED, Auth::user());

            return Invoice::generateFromServiceRecord($record->fresh(['items.inventoryItem', 'booking']));
        });

        $booking->customer->notify(new BookingStatusUpdated($booking));
        InvoiceIssued::sendFor($invoice);

        return redirect()->route('mechanic.bookings.show', $booking)
            ->with('status', 'Service logged and booking marked as completed.');
    }

    private function authorizeBooking(ServiceBooking $booking): void
    {
        abort_unless($booking->mechanic_id === Auth::user()->mechanic->id, 403);
        abort_unless($booking->status === ServiceBooking::STATUS_IN_PROGRESS, 422, 'Only in-progress bookings can be completed.');
    }
}
