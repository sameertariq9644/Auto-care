<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMechanicRequest;
use App\Models\Mechanic;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MechanicController extends Controller
{
    public function index(): View
    {
        $mechanics = Mechanic::with('user')->latest()->paginate(15);

        return view('admin.mechanics.index', compact('mechanics'));
    }

    public function create(): View
    {
        return view('admin.mechanics.create');
    }

    public function store(StoreMechanicRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('mechanic');

        Mechanic::create([
            'user_id' => $user->id,
            'specialty' => $validated['specialty'] ?? null,
        ]);

        return redirect()->route('admin.mechanics.index')
            ->with('status', 'Mechanic account created successfully.');
    }
}
