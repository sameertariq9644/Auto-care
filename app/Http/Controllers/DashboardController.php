<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Redirect the authenticated user to their role-specific dashboard.
     */
    public function index(): RedirectResponse
    {
        $user = Auth::user();

        return match (true) {
            $user->hasRole('admin') => redirect()->route('admin.dashboard'),
            $user->hasRole('mechanic') => redirect()->route('mechanic.dashboard'),
            default => redirect()->route('customer.dashboard'),
        };
    }
}
