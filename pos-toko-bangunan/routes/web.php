<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\SalesForecastController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\DetailPrediksiController;
use App\Http\Controllers\KategoriController;
use App\Models\Kategori;
Route::get('/', fn () => redirect()->route('login'));

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Auth
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'processLogin'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Master Barang - Using resource routes with custom index route
Route::get('/master/barang', [BarangController::class, 'index'])->name('barang');
Route::resource('barang', BarangController::class)->except(['index']);

// Master Karyawan - Using resource routes with custom index route
Route::get('/master/karyawan', [KaryawanController::class, 'index'])->name('karyawan');
Route::resource('karyawan', KaryawanController::class)->except(['index']);

Route::get('/master/kategori', [KategoriController::class, 'index'])->name('kategori');
Route::resource('kategori', KategoriController::class)->except(['index']);

// Transaksi
Route::get('/transaksi', [BarangController::class, 'transaksi'])->name('transaksi');
Route::post('/transaksi/store', [TransaksiController::class, 'store'])->name('transaksi.store');

// Laporan
Route::get('/laporan', [TransaksiController::class, 'laporan'])->name('laporan');
Route::get('/laporan/{id}/detail', [TransaksiController::class, 'detail']);
Route::get('/laporan/export', [LaporanController::class, 'export'])->name('laporan.export');
Route::get('/laporan/export/excel', [TransaksiController::class, 'exportExcel'])->name('laporan.export.excel');
Route::get('/laporan/export/pdf', [TransaksiController::class, 'exportPdf'])->name('laporan.export.pdf');

// History
Route::get('/history', [HistoryController::class, 'index'])->name('history');

// Create Views (Form Tambah)


Route::get('/master/create/create_barang', function () {
    $kategori = Kategori::orderBy('nama_kategori')->get();
    return view('master.create.create_barang', compact('kategori'));
})->name('create_barang');

Route::get('/master/create/create_karyawan', fn () => view('master.create.create_karyawan'))->name('create_karyawan');
Route::get('/master/create/create_kategori', fn () => view('master.create.create_kategori'))->name('create_kategori');
// Pembelian - Menampilkan daftar pembelian
Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian');
// Tambah Pembelian - View ambil data dari BarangController
Route::get('/pembelian/tambah', [BarangController::class, 'tambahPembelian'])->name('pembelian.tambah');

// Simpan Pembelian
Route::post('/pembelian/simpan', [PembelianController::class, 'simpan'])->name('pembelian.simpan');
Route::get('/pembelian/{id}/detail', [PembelianController::class, 'detail']);

Route::get('/forecast', [SalesForecastController::class, 'index'])->name('forecast.index');
Route::post('/api/forecast/single', [SalesForecastController::class, 'predictSingle']);
Route::post('/api/forecast/all', [SalesForecastController::class, 'predictAll']);
// Route tambahan untuk melihat riwayat prediksi
Route::get('/api/forecast/history', [SalesForecastController::class, 'getHistoricalPredictions'])->name('forecast.history');
Route::get('/api/forecast/{id}', [SalesForecastController::class, 'getPredictionById'])->name('forecast.detail');
Route::get('/api/forecast/available-dates/{item}', [SalesForecastController::class, 'getAvailableDates'])->name('forecast.available-dates');

Route::get('/transaksi/detail/{id}', [TransaksiController::class, 'getDetail']);

// Route untuk halaman utama forecast
Route::get('/forecast', [SalesForecastController::class, 'index'])->name('forecast.index');

// Route yang sudah ada (pastikan seperti ini)
Route::get('/cek_prediksi', fn () => view('cek_prediksi'))->name('cek_prediksi');
Route::get('/cek-prediksi/data', [SalesForecastController::class, 'cekPrediksi']);

// Route baru untuk detail prediksi
Route::get('/prediksi/{id_prediksi}', [DetailPrediksiController::class, 'show'])->name('detail-prediksi');
Route::get('/prediksi/{id_prediksi}/data', [DetailPrediksiController::class, 'getData'])->name('detail-prediksi-data');