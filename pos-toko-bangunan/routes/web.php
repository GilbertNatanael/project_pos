<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\PembelianController;

Route::get('/', fn () => redirect()->route('login'));

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Auth
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'processLogin'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Master Barang
Route::get('/master/barang', [BarangController::class, 'index'])->name('barang');
Route::put('/master/barang/{id}', [BarangController::class, 'update'])->name('barang.update');
Route::resource('barang', BarangController::class)->except(['index', 'update']);

// Master Karyawan
Route::get('/master/karyawan', [KaryawanController::class, 'index'])->name('karyawan');
Route::put('/master/karyawan/{id}', [KaryawanController::class, 'update'])->name('karyawan.update');
Route::resource('karyawan', KaryawanController::class)->except(['index', 'update']);

// Transaksi
Route::get('/transaksi', [BarangController::class, 'transaksi'])->name('transaksi');
Route::post('/transaksi/store', [TransaksiController::class, 'store'])->name('transaksi.store');


// Pembelian
Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
Route::get('/pembelian/create', [PembelianController::class, 'create'])->name('pembelian.create'); // ← Tambahkan ini
Route::post('/pembelian/store', [PembelianController::class, 'store'])->name('pembelian.store');


// Prediksi
Route::get('/prediksi', fn () => view('prediksi'))->name('prediksi');

// Laporan
Route::get('/laporan', fn () => view('laporan'))->name('laporan');

// History
Route::get('/history', [HistoryController::class, 'index'])->name('history');

// Create Views (Form Tambah)
Route::get('/master/create/create_barang', fn () => view('master.create.create_barang'))->name('create_barang');
Route::get('/master/create/create_karyawan', fn () => view('master.create.create_karyawan'))->name('create_karyawan');
