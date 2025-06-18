<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
{
    $totalIncome = Transaksi::sum('total_harga');

    $penjualanBulanan = Transaksi::selectRaw("to_char(tanggal_waktu, 'YYYY-MM') as bulan, SUM(total_harga) as total")
        ->groupBy('bulan')
        ->orderBy('bulan')
        ->take(7)
        ->get();

    $startOfMonth = Carbon::now()->startOfMonth();
    $endOfMonth = Carbon::now()->endOfMonth();

    $barangTerpopuler = DetailTransaksi::select('id_barang', DB::raw('SUM(jumlah) as total_terjual'))
        ->whereHas('transaksi', function ($query) use ($startOfMonth, $endOfMonth) {
            $query->whereBetween('tanggal_waktu', [$startOfMonth, $endOfMonth]);
        })
        ->groupBy('id_barang')
        ->orderByDesc('total_terjual')
        ->with('barang')
        ->take(5)
        ->get();

        $totalTransaksiBulanIni = Transaksi::whereBetween('tanggal_waktu', [$startOfMonth, $endOfMonth])->count();

        $jumlahBarangTerjual = DetailTransaksi::whereHas('transaksi', function ($q) use ($startOfMonth, $endOfMonth) {
            $q->whereBetween('tanggal_waktu', [$startOfMonth, $endOfMonth]);
        })->sum('jumlah');

        $jumlahProdukAktif = Barang::count();

        return view('dashboard', compact(
        'totalIncome',
        'penjualanBulanan',
        'barangTerpopuler',
        'totalTransaksiBulanIni',
        'jumlahBarangTerjual',
        'jumlahProdukAktif'
    ));




}


    
}
