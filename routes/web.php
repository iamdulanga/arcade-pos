<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\PosTerminal;
use App\Livewire\Admin\Categories;
use App\Livewire\Admin\Products;
use App\Http\Controllers\SaleController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {

    // POS Terminal
    Route::get('/pos', PosTerminal::class)->name('pos');

    // Admin panel
    Route::prefix('admin')->name('admin.')->group(function () {

        Route::get('/categories', Categories::class)->name('categories.index');
        Route::get('/products', Products::class)->name('products.index');

        // Placeholders — to be built
        Route::get('/suppliers', fn() => redirect()->route('admin.products.index'))->name('suppliers.index');
        Route::get('/sales', fn() => redirect()->route('dashboard'))->name('sales.index');
        Route::get('/customers', fn() => redirect()->route('dashboard'))->name('customers.index');

    });

    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');

});