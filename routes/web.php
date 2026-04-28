<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemAssistantController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'createLogin'])->name('login');
    Route::post('login', [AuthController::class, 'storeLogin'])->name('login.store');
    Route::get('register', [AuthController::class, 'createRegister'])->name('register');
    Route::post('register', [AuthController::class, 'storeRegister'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');
    Route::get('operasional', [DashboardController::class, 'operational'])
        ->middleware('permission:dashboard.operational')
        ->name('dashboard.operational');

    Route::get('profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::post('ai/barang-chat', [ItemAssistantController::class, 'store'])
        ->middleware('permission:assistant.use')
        ->name('ai.barang-chat');

    Route::get('barang', [ItemController::class, 'index'])
        ->middleware('permission:items.view')
        ->name('barang.index');
    Route::get('barang/create', [ItemController::class, 'create'])
        ->middleware('permission:items.manage')
        ->name('barang.create');
    Route::post('barang', [ItemController::class, 'store'])
        ->middleware('permission:items.manage')
        ->name('barang.store');
    Route::get('barang/{barang}', [ItemController::class, 'show'])
        ->middleware('permission:items.view')
        ->name('barang.show');
    Route::get('barang/{barang}/edit', [ItemController::class, 'edit'])
        ->middleware('permission:items.manage')
        ->name('barang.edit');
    Route::put('barang/{barang}', [ItemController::class, 'update'])
        ->middleware('permission:items.manage')
        ->name('barang.update');
    Route::delete('barang/{barang}', [ItemController::class, 'destroy'])
        ->middleware('permission:items.manage')
        ->name('barang.destroy');

    Route::get('permintaan-barang', [ItemRequestController::class, 'index'])
        ->middleware('permission:requests.manage')
        ->name('permintaan-barang.index');
    Route::get('permintaan-barang/create', [ItemRequestController::class, 'create'])
        ->middleware('permission:requests.manage')
        ->name('permintaan-barang.create');
    Route::post('permintaan-barang', [ItemRequestController::class, 'store'])
        ->middleware('permission:requests.manage')
        ->name('permintaan-barang.store');
    Route::patch('permintaan-barang/{permintaan}/status', [ItemRequestController::class, 'updateStatus'])
        ->middleware('permission:requests.manage')
        ->name('permintaan-barang.update-status');

    Route::get('pembelian-barang', [PurchaseController::class, 'index'])
        ->middleware('permission:purchases.manage')
        ->name('pembelian-barang.index');
    Route::get('pembelian-barang/create', [PurchaseController::class, 'create'])
        ->middleware('permission:purchases.manage')
        ->name('pembelian-barang.create');
    Route::post('pembelian-barang', [PurchaseController::class, 'store'])
        ->middleware('permission:purchases.manage')
        ->name('pembelian-barang.store');

    Route::get('laporan-pengeluaran', [PurchaseReportController::class, 'index'])
        ->middleware('permission:purchases.manage')
        ->name('laporan-pengeluaran.index');
    Route::get('laporan-pengeluaran/excel', [PurchaseReportController::class, 'exportExcel'])
        ->middleware('permission:purchases.manage')
        ->name('laporan-pengeluaran.excel');
    Route::get('laporan-pengeluaran/pdf', [PurchaseReportController::class, 'exportPdf'])
        ->middleware('permission:purchases.manage')
        ->name('laporan-pengeluaran.pdf');

    Route::get('mutasi-stok/create', [StockMovementController::class, 'create'])
        ->middleware('permission:stock-movements.manage')
        ->name('stock-movements.create');
    Route::post('mutasi-stok', [StockMovementController::class, 'store'])
        ->middleware('permission:stock-movements.manage')
        ->name('stock-movements.store');
    Route::prefix('pengaturan')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/', [SettingController::class, 'update'])->name('settings.update');

        // Akun Manajemen
        Route::get('akun', [SettingController::class, 'accounts'])
            ->middleware('permission:accounts.manage')
            ->name('settings.accounts');
        Route::post('akun', [SettingController::class, 'storeAccount'])
            ->middleware('permission:accounts.manage')
            ->name('settings.accounts.store');
        Route::put('akun/{managedUser}', [SettingController::class, 'updateAccount'])
            ->middleware('permission:accounts.manage')
            ->name('settings.accounts.update');

        // Akses Manajemen
        Route::get('akses', [SettingController::class, 'access'])
            ->middleware('permission:access.manage')
            ->name('settings.access');
        Route::put('akses', [SettingController::class, 'updateAccess'])
            ->middleware('permission:access.manage')
            ->name('settings.access.update');
    });

    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
});
