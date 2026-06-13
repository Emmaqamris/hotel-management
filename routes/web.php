<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HousekeepingController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontDeskController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

// ── Auth ──────────────────────────────────────────────────────
Route::middleware('guest:employee')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Password Reset
    Route::get('/forgot-password',  [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth:employee');

// ── Protected Routes ──────────────────────────────────────────
Route::middleware('auth:employee')->group(function () {

    Route::get('/', fn() => redirect()->route('front-desk.index'));

    // ── Dashboard ────────────────────────────────────────────
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Front Desk ───────────────────────────────────────────
    Route::get('front-desk', [FrontDeskController::class, 'index'])->name('front-desk.index');
    Route::get('front-desk/room-board', [FrontDeskController::class, 'roomBoard'])->name('front-desk.room-board');

    // ── Rooms ────────────────────────────────────────────────
    Route::resource('rooms', RoomController::class);
    Route::patch('rooms/{room}/status', [RoomController::class, 'updateStatus'])->name('rooms.status');
    Route::patch('rooms/{room}/toggle', [RoomController::class, 'toggleActive'])->name('rooms.toggle');

    // ── Bookings ─────────────────────────────────────────────
    Route::get('bookings/room-price', [BookingController::class, 'roomPrice'])->name('bookings.room-price');
    Route::resource('bookings', BookingController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('bookings/{booking}/check-in',  [BookingController::class, 'checkIn'])->name('bookings.check-in');
    Route::post('bookings/{booking}/check-out', [BookingController::class, 'checkOut'])->name('bookings.check-out');
    Route::post('bookings/{booking}/cancel',    [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('bookings/{booking}/no-show',   [BookingController::class, 'noShow'])->name('bookings.no-show');

    // ── Guests ───────────────────────────────────────────────
    Route::get('guests/search', [GuestController::class, 'search'])->name('guests.search');
    Route::resource('guests', GuestController::class)->except(['destroy']);

    // ── Housekeeping ─────────────────────────────────────────────
Route::get('housekeeping', [HousekeepingController::class, 'index'])->name('housekeeping.index');
Route::get('housekeeping/create', [HousekeepingController::class, 'create'])->name('housekeeping.create');
Route::post('housekeeping', [HousekeepingController::class, 'store'])->name('housekeeping.store');
Route::get('housekeeping/{housekeeping}', [HousekeepingController::class, 'show'])->name('housekeeping.show');
Route::get('housekeeping/{housekeeping}/edit', [HousekeepingController::class, 'edit'])->name('housekeeping.edit');
Route::put('housekeeping/{housekeeping}', [HousekeepingController::class, 'update'])->name('housekeeping.update');
Route::delete('housekeeping/{housekeeping}', [HousekeepingController::class, 'destroy'])->name('housekeeping.destroy');
Route::patch('housekeeping/{housekeeping}/status', [HousekeepingController::class, 'updateStatus'])->name('housekeeping.update-status');
    
// ── Invoices ─────────────────────────────────────────────
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('invoices/{invoice}/charges', [InvoiceController::class, 'addCharge'])->name('invoices.charges.store');
    Route::delete('invoices/{invoice}/charges/{item}', [InvoiceController::class, 'removeCharge'])->name('invoices.charges.destroy');
    Route::post('invoices/{invoice}/discount', [InvoiceController::class, 'applyDiscount'])->name('invoices.discount');
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');

    // ── Payments ─────────────────────────────────────────────
    Route::get('invoices/{invoice}/pay',  [PaymentController::class, 'create'])->name('payments.create');
    Route::post('invoices/{invoice}/pay', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

    // ── Employees (all auth users can view, only admin/manager can mutate) ──
    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create')->middleware('role:admin,manager');
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');

    Route::middleware('role:admin,manager')->group(function () {
        Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        Route::patch('employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');

        // ── Reports ──────────────────────────────────────────
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/occupancy', [ReportController::class, 'occupancy'])->name('reports.occupancy');
        Route::get('reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
        Route::get('reports/bookings', [ReportController::class, 'bookings'])->name('reports.bookings');
        Route::get('reports/revenue/export', [ReportController::class, 'exportRevenue'])->name('reports.revenue.export');
        Route::get('reports/occupancy/export', [ReportController::class, 'exportOccupancy'])->name('reports.occupancy.export');
    });
});