<?php

namespace App\Http\Controllers;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Transaksi;            
use Illuminate\Http\Request;
class LaporanController extends Controller
{
    
public function export(Request $request)
{
    $query = Transaksi::with('detailTransaksi.barang');

    // Terapkan filter jika ada
    if ($request->start_date) {
        $query->whereDate('tanggal_waktu', '>=', $request->start_date);
    }

    if ($request->end_date) {
        $query->whereDate('tanggal_waktu', '<=', $request->end_date);
    }

    if ($request->keyword) {
        $query->where(function ($q) use ($request) {
            $q->where('id_transaksi', 'ILIKE', "%{$request->keyword}%")
              ->orWhere('note', 'ILIKE', "%{$request->keyword}%");
        });
    }

    if ($request->metode) {
        $query->where('metode_pembayaran', $request->metode);
    }

    if ($request->harga_min) {
        $query->where('total_harga', '>=', $request->harga_min);
    }

    if ($request->harga_max) {
        $query->where('total_harga', '<=', $request->harga_max);
    }

    $data = $query->orderBy('tanggal_waktu', 'desc')->get();

    $format = $request->format;

    if ($format === 'pdf') {
        $pdf = Pdf::loadView('laporan.export_pdf', ['data' => $data]);
        return $pdf->download('laporan_transaksi.pdf');
    }

    // Default Excel
    return Excel::download(new LaporanExport($data), 'laporan_transaksi.xlsx');
}
}