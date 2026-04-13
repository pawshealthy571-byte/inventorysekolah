<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('barang', ItemController::class);

Route::get('mutasi-stok/create', [StockMovementController::class, 'create'])->name('stock-movements.create');
Route::post('mutasi-stok', [StockMovementController::class, 'store'])->name('stock-movements.store');
