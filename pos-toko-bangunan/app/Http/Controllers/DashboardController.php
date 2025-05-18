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
        
        // Total income
        $totalIncome = Transaksi::sum('total_harga');

        // Penjualan per bulan (7 bulan terakhir)
        $penjualanBulanan = Transaksi::selectRaw("to_char(tanggal_waktu, 'YYYY-MM') as bulan, SUM(total_harga) as total")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->take(7)
            ->get();

        // Barang hampir habis stok
        $barangHampirHabis = Barang::where('jumlah_barang', '<=', 5)->orderBy('jumlah_barang')->get();

        // Barang terpopuler
        $barangTerpopuler = DetailTransaksi::select('id_barang', DB::raw('SUM(jumlah) as total_terjual'))
            ->groupBy('id_barang')
            ->orderByDesc('total_terjual')
            ->with('barang')
            ->take(5)
            ->get();

        return view('dashboard', compact('totalIncome', 'penjualanBulanan', 'barangHampirHabis', 'barangTerpopuler'));

        
    }

    
}
