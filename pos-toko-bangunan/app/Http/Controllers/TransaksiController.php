<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Barang;
use App\Exports\LaporanExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class TransaksiController extends Controller
{
    public function store(Request $request)
{
    // Cek apakah admin (karyawan) sudah login
    $adminId = Session::get('id_karyawan');
    if (!$adminId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Validasi input
    $request->validate([
        'total_harga' => 'required|numeric',
        'items' => 'required|array|min:1',
        'items.*.id_barang' => 'required|integer',
        'items.*.jumlah' => 'required|integer|min:1',
        'items.*.subtotal' => 'required|numeric',
        'metode_pembayaran' => 'required|string',
        'tanggal_transaksi' => 'required|date',
        'waktu_transaksi' => 'nullable|date_format:H:i',
        'note' => 'nullable|string',
        'bank' => 'nullable|string',
        'nomor_rekening' => 'nullable|string',
    ]);

    try {
        DB::beginTransaction();

        // Tentukan waktu transaksi
        if ($request->filled('waktu_transaksi')) {
            $tanggalWaktu = $request->tanggal_transaksi . ' ' . $request->waktu_transaksi . ':00';
        } else {
            $tanggalWaktu = $request->tanggal_transaksi . ' ' . now()->format('H:i:s');
        }
        
        // Gabungkan note dengan informasi pembayaran
        $combinedNote = $this->buildCombinedNote(
            $request->metode_pembayaran,
            $request->bank,
            $request->nomor_rekening,
            $request->note
        );
        
        // Buat transaksi baru dengan tanggal dan waktu yang tepat
        $transaksi = Transaksi::create([
            'id_admin' => $adminId,
            'tanggal_waktu' => $tanggalWaktu,
            'total_harga' => $request->total_harga,
            'metode_pembayaran' => $request->metode_pembayaran,
            'note' => $combinedNote,
        ]);

        // Simpan detail transaksi & kurangi stok barang
        foreach ($request->items as $item) {
            // Simpan detail transaksi
            DetailTransaksi::create([
                'id_transaksi' => $transaksi->id_transaksi,
                'id_barang' => $item['id_barang'],
                'jumlah' => $item['jumlah'],
                'subtotal' => $item['subtotal'],
            ]);

            // Kurangi stok barang
            $barang = Barang::find($item['id_barang']);
            if ($barang) {
                if ($barang->jumlah_barang < $item['jumlah']) {
                    throw new \Exception("Stok barang '{$barang->nama_barang}' tidak mencukupi.");
                }
                $barang->jumlah_barang -= $item['jumlah'];
                $barang->save();
            } else {
                throw new \Exception("Barang dengan ID {$item['id_barang']} tidak ditemukan.");
            }
        }

        DB::commit();

        return response()->json([
            'success' => true, 
            'message' => 'Transaksi berhasil disimpan pada ' . Carbon::parse($tanggalWaktu)->format('d/m/Y H:i:s'),
            'transaksi' => $transaksi
        ], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'error' => 'Transaksi gagal disimpan',
            'message' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Gabungkan note dengan informasi pembayaran
 */
private function buildCombinedNote($metodePembayaran, $bank, $nomorRekening, $noteUser)
{
    $noteParts = [];
    
    // Tambahkan informasi bank dan rekening jika ada
    if (in_array($metodePembayaran, ['card', 'transfer']) && ($bank || $nomorRekening)) {
        $paymentInfo = [];
        
        if ($bank) {
            $paymentInfo[] = "Bank: {$bank}";
        }
        
        if ($nomorRekening) {
            $paymentInfo[] = "No.Rek: {$nomorRekening}";
        }
        
        if (!empty($paymentInfo)) {
            $noteParts[] = implode(' | ', $paymentInfo);
        }
    }
    
    // Tambahkan note dari user jika ada
    if (!empty($noteUser)) {
        $noteParts[] = trim($noteUser);
    }
    
    // Gabungkan dengan separator
    return implode(' - ', $noteParts);
}

    // Ganti method laporan() di TransaksiController dengan yang ini:

public function laporan(Request $request)
{
    $query = Transaksi::query();

    // Filter tanggal
    if ($request->filled('start_date')) {
        $query->whereDate('tanggal_waktu', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('tanggal_waktu', '<=', $request->end_date);
    }

    // Filter keyword (ID atau catatan)
    if ($request->filled('keyword')) {
        $keyword = $request->keyword;
        $query->where(function ($q) use ($keyword) {
            $q->where('id_transaksi', 'like', "%$keyword%")
              ->orWhere('note', 'like', "%$keyword%");
        });
    }

    // Filter metode pembayaran
    if ($request->filled('metode')) {
        $query->where('metode_pembayaran', $request->metode);
    }

    // Filter range harga
    if ($request->filled('harga_min')) {
        $query->where('total_harga', '>=', $request->harga_min);
    }
    if ($request->filled('harga_max')) {
        $query->where('total_harga', '<=', $request->harga_max);
    }

    // Ubah dari get() ke paginate() dan tambahkan appends untuk mempertahankan filter
    $transaksi = $query->orderBy('tanggal_waktu', 'desc')->paginate(10);
    $transaksi->appends($request->query());

    return view('laporan', compact('transaksi'));
}

    public function getDetail($id)
    {
        $transaksi = Transaksi::with(['detailTransaksi.barang'])->findOrFail($id);
        return response()->json($transaksi);
    }

    // Method untuk export Excel 
    public function exportExcel(Request $request)
    {
        $filters = $this->extractFilters($request);
        
        // Buat nama file dengan format sederhana
        $filename = 'laporan_penjualan_' . now()->format('d-m-Y') . '.xlsx';
        
        return Excel::download(new LaporanExport($filters), $filename);
    }

    // Method untuk export PDF 
    public function exportPdf(Request $request)
    {
        $filters = $this->extractFilters($request);

        $query = Transaksi::with(['detailTransaksi.barang']);

        // Apply filters
        if (!empty($filters['start_date'])) {
            $query->whereDate('tanggal_waktu', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('tanggal_waktu', '<=', $filters['end_date']);
        }
        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function($q) use ($keyword) {
                $q->where('id_transaksi', 'like', "%$keyword%")
                  ->orWhere('note', 'like', "%$keyword%");
            });
        }
        if (!empty($filters['metode'])) {
            $query->where('metode_pembayaran', $filters['metode']);
        }
        if (!empty($filters['harga_min'])) {
            $query->where('total_harga', '>=', $filters['harga_min']);
        }
        if (!empty($filters['harga_max'])) {
            $query->where('total_harga', '<=', $filters['harga_max']);
        }

        $transaksi = $query->orderBy('tanggal_waktu', 'desc')->get();

        // Set PDF options
        $pdf = PDF::loadView('laporan.laporan_pdf', compact('transaksi'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'dpi' => 150,
                      'defaultFont' => 'sans-serif',
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
                  ]);

        $filename = 'laporan_penjualan_' . now()->format('d-m-Y') . '.pdf';
        
        return $pdf->download($filename);
    }

    private function extractFilters($request)
    {
        return [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'keyword' => $request->input('keyword'),
            'metode' => $request->input('metode'),
            'harga_min' => $request->input('harga_min'),
            'harga_max' => $request->input('harga_max'),
        ];
    }
}
