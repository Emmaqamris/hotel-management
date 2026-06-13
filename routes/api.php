<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Hotel Management System — REST API
|--------------------------------------------------------------------------
|
| Base URL:    /api
| Auth:        Bearer token (Sanctum)
| Accept:      application/json
| Content-Type: application/json
|
*/

// ── Public: Authentication ────────────────────────────────────
Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// ── Protected: Require Bearer token ───────────────────────────
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    // ── Auth ─────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);
    });

    // ── Rooms ─────────────────────────────────────────────────
    Route::get('rooms/available', [RoomController::class, 'available'])->name('api.rooms.available');
    Route::apiResource('rooms', RoomController::class)->names([
        'index'  => 'api.rooms.index',
        'store'  => 'api.rooms.store',
        'show'   => 'api.rooms.show',
        'update' => 'api.rooms.update',
        'destroy'=> 'api.rooms.destroy',
    ]);

    // ── Guests ────────────────────────────────────────────────
    Route::get('guests/search',           [GuestController::class, 'search'])->name('api.guests.search');
    Route::get('guests/{guest}/bookings', [GuestController::class, 'bookings'])->name('api.guests.bookings');
    Route::apiResource('guests', GuestController::class)->only([
        'index', 'store', 'show',
    ])->names([
        'index' => 'api.guests.index',
        'store' => 'api.guests.store',
        'show'  => 'api.guests.show',
    ]);

    // ── Bookings ──────────────────────────────────────────────
    Route::apiResource('bookings', BookingController::class)->only([
        'index', 'store', 'show',
    ])->names([
        'index' => 'api.bookings.index',
        'store' => 'api.bookings.store',
        'show'  => 'api.bookings.show',
    ]);
    Route::post('bookings/{booking}/check-in',  [BookingController::class, 'checkIn'])->name('api.bookings.check-in');
    Route::post('bookings/{booking}/check-out', [BookingController::class, 'checkOut'])->name('api.bookings.check-out');
    Route::post('bookings/{booking}/cancel',    [BookingController::class, 'cancel'])->name('api.bookings.cancel');

    // ── Invoices ──────────────────────────────────────────────
    Route::get('invoices/{invoice}',           [InvoiceController::class, 'show'])->name('api.invoices.show');
    Route::post('invoices/{invoice}/charges',  [InvoiceController::class, 'addCharge'])->name('api.invoices.charges.store');
    Route::post('invoices/{invoice}/discount', [InvoiceController::class, 'applyDiscount'])->name('api.invoices.discount');
    Route::post('invoices/{invoice}/pay',      [PaymentController::class, 'store'])->name('api.payments.store');

    // ── Dashboard & Reports ───────────────────────────────────
    Route::get('dashboard',         [DashboardController::class, 'index'])->name('api.dashboard');
    Route::get('reports/revenue',   [DashboardController::class, 'revenue'])->name('api.reports.revenue');
    Route::get('reports/occupancy', [DashboardController::class, 'occupancy'])->name('api.reports.occupancy');
});