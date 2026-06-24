<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\PosTerminal;
use App\Livewire\Admin\Categories;
use App\Livewire\Admin\Products;
use App\Http\Controllers\SaleController;
use App\Livewire\Admin\StockManagement;

Route::view('/', 'welcome');

Route::get('dashboard', \App\Livewire\Admin\Dashboard::class)
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
        Route::get('/suppliers', \App\Livewire\Admin\Suppliers::class)->name('suppliers.index');

        Route::get('/sales', \App\Livewire\Admin\Sales::class)->name('sales.index');

        Route::get('/customers', fn() => redirect()->route('dashboard'))->name('customers.index');

        Route::get('/stock', StockManagement::class)->name('stock.index');
    });

    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');

});