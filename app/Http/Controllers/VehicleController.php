<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\Vehicle;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Vehicle::class, 'vehicle');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $vehicles = Auth::user()->vehicles()
            ->with(['latestServiceRecord', 'nextBooking'])
            ->withCount('serviceRecords')
            ->latest()
            ->paginate(9);

        return view('customer.vehicles.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('customer.vehicles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $uuid = (string) Str::uuid();

        $vehicle = Vehicle::create([
            ...$request->safe()->except('image'),
            'image_path' => $request->file('image')?->store('vehicles', 'public'),
            'user_id' => Auth::id(),
            'qr_uuid' => $uuid,
            'qr_code_path' => $this->generateQrCode($uuid),
        ]);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('status', 'Vehicle added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle): View
    {
        $qrCodeUrl = $vehicle->qr_code_path
            ? Storage::disk('public')->url($vehicle->qr_code_path)
            : null;

        $serviceRecords = $vehicle->serviceRecords()
            ->with(['mechanic.user', 'items.inventoryItem'])
            ->latest('serviced_at')
            ->get();

        return view('customer.vehicles.show', compact('vehicle', 'qrCodeUrl', 'serviceRecords'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle): View
    {
        return view('customer.vehicles.edit', compact('vehicle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            if ($vehicle->image_path) {
                Storage::disk('public')->delete($vehicle->image_path);
            }

            $data['image_path'] = $request->file('image')?->store('vehicles', 'public');
        }

        $vehicle->update($data);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('status', 'Vehicle updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        if ($vehicle->qr_code_path) {
            Storage::disk('public')->delete($vehicle->qr_code_path);
        }

        if ($vehicle->image_path) {
            Storage::disk('public')->delete($vehicle->image_path);
        }

        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('status', 'Vehicle removed successfully.');
    }

    /**
     * Generate a QR code SVG encoding the vehicle's public history URL and store it.
     */
    private function generateQrCode(string $uuid): string
    {
        $result = (new Builder(
            writer: new SvgWriter(),
            data: route('vehicles.history', $uuid),
            size: 300,
            margin: 10,
        ))->build();

        $path = "qrcodes/{$uuid}.svg";

        Storage::disk('public')->put($path, $result->getString());

        return $path;
    }
}
