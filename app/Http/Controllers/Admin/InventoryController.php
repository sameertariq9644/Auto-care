<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $items = InventoryItem::orderBy('name')->paginate(15);

        return view('admin.inventory.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.inventory.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventoryItemRequest $request): RedirectResponse
    {
        InventoryItem::create($request->validated());

        return redirect()->route('admin.inventory.index')
            ->with('status', 'Inventory item added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryItem $inventory): View
    {
        return view('admin.inventory.edit', ['item' => $inventory]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventory): RedirectResponse
    {
        $inventory->update($request->validated());

        return redirect()->route('admin.inventory.index')
            ->with('status', 'Inventory item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryItem $inventory): RedirectResponse
    {
        $inventory->delete();

        return redirect()->route('admin.inventory.index')
            ->with('status', 'Inventory item removed successfully.');
    }
}
