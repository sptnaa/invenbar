<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Models\Lokasi;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PerbaikanController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Dashboard
    Route::get('/barang/laporan', [BarangController::class, 'cetakLaporan'])->name('barang.laporan');
    
    // User
    Route::resource('user', UserController::class);

    // Kategori
    Route::resource('kategori', KategoriController::class);

    // Lokasi
    Route::resource('lokasi', LokasiController::class);

    // Barang
    Route::resource('barang', BarangController::class);

    // Peminjaman Routes
    Route::resource('peminjaman', PeminjamanController::class);
    
    // Pengembalian barang
    Route::patch('peminjaman/{peminjaman}/pengembalian', [PeminjamanController::class, 'pengembalian'])
        ->name('peminjaman.pengembalian');
    
    // AJAX untuk mendapatkan data barang
    Route::get('peminjaman/barang/data', [PeminjamanController::class, 'getBarangData'])
        ->name('peminjaman.barang.data');
    
    // Laporan peminjaman
    Route::get('peminjaman-laporan', [PeminjamanController::class, 'laporan'])
        ->name('peminjaman.laporan');
    
    // Dashboard data (untuk AJAX)
    Route::get('dashboard/peminjaman-data', [PeminjamanController::class, 'dashboardData'])
        ->name('dashboard.peminjaman.data');

    // Perbaikan Routes
    Route::resource('perbaikan', PerbaikanController::class);
    
    // Laporan perbaikan
    Route::get('perbaikan-laporan', [PerbaikanController::class, 'laporan'])
        ->name('perbaikan.laporan');
});

require __DIR__.'/auth.php';
