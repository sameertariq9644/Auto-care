<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\MechanicController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Mechanic\BookingController as MechanicBookingController;
use App\Http\Controllers\Mechanic\DashboardController as MechanicDashboardController;
use App\Http\Controllers\Mechanic\ServiceRecordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleHistoryController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/vehicles/{vehicle:qr_uuid}/history', VehicleHistoryController::class)
    ->name('vehicles.history');


// Main dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});


// Admin routes
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('mechanics', MechanicController::class)
            ->only(['index', 'create', 'store']);

        Route::get('/bookings', [AdminBookingController::class, 'index'])
            ->name('bookings.index');

        Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])
            ->name('bookings.show');

        Route::patch('/bookings/{booking}/confirm', [AdminBookingController::class, 'confirm'])
            ->name('bookings.confirm');

        Route::patch('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])
            ->name('bookings.cancel');

        Route::resource('inventory', InventoryController::class)
            ->except(['show']);

        Route::get('/invoices', [AdminInvoiceController::class, 'index'])
            ->name('invoices.index');

        Route::get('/invoices/create', [AdminInvoiceController::class, 'create'])
            ->name('invoices.create');

        Route::post('/invoices', [AdminInvoiceController::class, 'store'])
            ->name('invoices.store');

        Route::get('/invoices/{invoice}', [AdminInvoiceController::class, 'show'])
            ->name('invoices.show');

        Route::get('/invoices/{invoice}/edit', [AdminInvoiceController::class, 'edit'])
            ->name('invoices.edit');

        Route::put('/invoices/{invoice}', [AdminInvoiceController::class, 'update'])
            ->name('invoices.update');

        Route::patch('/invoices/{invoice}/mark-paid', [AdminInvoiceController::class, 'markPaid'])
            ->name('invoices.markPaid');

        Route::post('/invoices/{invoice}/resend', [AdminInvoiceController::class, 'resend'])
            ->name('invoices.resend');

        Route::get('/invoices/{invoice}/download', [AdminInvoiceController::class, 'download'])
            ->name('invoices.download');
    });


// Mechanic routes
Route::middleware(['auth', 'verified', 'role:mechanic'])
    ->prefix('mechanic')
    ->name('mechanic.')
    ->group(function () {

        Route::get('/dashboard', [MechanicDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/bookings', [MechanicBookingController::class, 'index'])
            ->name('bookings.index');

        Route::get('/bookings/{booking}', [MechanicBookingController::class, 'show'])
            ->name('bookings.show');

        Route::patch('/bookings/{booking}/start', [MechanicBookingController::class, 'start'])
            ->name('bookings.start');

        Route::get('/bookings/{booking}/complete', [ServiceRecordController::class, 'create'])
            ->name('bookings.complete.create');

        Route::post('/bookings/{booking}/complete', [ServiceRecordController::class, 'store'])
            ->name('bookings.complete.store');
    });


// Customer dashboard
Route::middleware(['auth', 'verified', 'role:customer'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {

        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])
            ->name('dashboard');
    });


// Customer workshop features
Route::middleware(['auth', 'verified', 'role:customer'])
    ->group(function () {

        Route::resource('vehicles', VehicleController::class);

        Route::resource('bookings', BookingController::class)
            ->only(['index', 'create', 'store', 'show']);

        Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])
            ->name('bookings.cancel');

        Route::resource('invoices', InvoiceController::class)
            ->only(['index', 'show']);

        Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])
            ->name('invoices.download');

        Route::get('/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])
            ->name('invoices.pay');
    });

require __DIR__.'/auth.php';