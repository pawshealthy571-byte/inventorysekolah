<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemRequestController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'createLogin'])->name('login');
    Route::post('login', [AuthController::class, 'storeLogin'])->name('login.store');
    Route::get('register', [AuthController::class, 'createRegister'])->name('register');
    Route::post('register', [AuthController::class, 'storeRegister'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('operasional', [DashboardController::class, 'operational'])->name('dashboard.operational');

    Route::resource('barang', ItemController::class);
    Route::get('permintaan-barang', [ItemRequestController::class, 'index'])->name('permintaan-barang.index');
    Route::get('permintaan-barang/create', [ItemRequestController::class, 'create'])->name('permintaan-barang.create');
    Route::post('permintaan-barang', [ItemRequestController::class, 'store'])->name('permintaan-barang.store');
    Route::patch('permintaan-barang/{permintaan}/status', [ItemRequestController::class, 'updateStatus'])->name('permintaan-barang.update-status');

    Route::get('pembelian-barang', [PurchaseController::class, 'index'])->name('pembelian-barang.index');
    Route::get('pembelian-barang/create', [PurchaseController::class, 'create'])->name('pembelian-barang.create');
    Route::post('pembelian-barang', [PurchaseController::class, 'store'])->name('pembelian-barang.store');

    Route::get('mutasi-stok/create', [StockMovementController::class, 'create'])->name('stock-movements.create');
    Route::post('mutasi-stok', [StockMovementController::class, 'store'])->name('stock-movements.store');
    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
});
