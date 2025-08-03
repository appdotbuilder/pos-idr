<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/health-check', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
    ]);
})->name('health-check');

// Welcome page with POS info
Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// Dashboard redirects to POS
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return redirect()->route('pos.index');
    })->name('dashboard');

    // POS System Routes
    Route::controller(PosController::class)->group(function () {
        Route::get('/pos', 'index')->name('pos.index');
        Route::post('/pos', 'store')->name('pos.store');
        Route::get('/pos/receipt/{sale}', 'show')->name('pos.receipt');
    });

    // Product Management
    Route::resource('products', ProductController::class);

    // Customer Management
    Route::resource('customers', CustomerController::class);

    // Reports
    Route::controller(ReportController::class)->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/inventory', 'show')->defaults('type', 'inventory')->name('inventory');
        Route::get('/customers', 'update')->defaults('type', 'customers')->name('customers');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';