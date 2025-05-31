<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;

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
        'note' => 'nullable|string', // Validasi untuk note (optional)
    ]);

    try {
        DB::beginTransaction();

        // Buat transaksi baru dengan menyertakan note
        $transaksi = Transaksi::create([
            'id_admin' => $adminId,
            'tanggal_waktu' => now(),
            'total_harga' => $request->total_harga,
            'metode_pembayaran' => $request->metode_pembayaran,
            'note' => $request->note, // Menyimpan note
        ]);

        // Simpan detail_transaksi untuk setiap item
        foreach ($request->items as $item) {
            DetailTransaksi::create([
                'id_transaksi' => $transaksi->id_transaksi,
                'id_barang' => $item['id_barang'],
                'jumlah' => $item['jumlah'],
                'subtotal' => $item['subtotal'],
            ]);
        }

        DB::commit();

        return response()->json(['success' => true, 'transaksi' => $transaksi], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'error' => 'Transaksi gagal disimpan',
            'message' => $e->getMessage(),
        ], 500);
    }
}
public function laporan()
{
    $transaksi = Transaksi::orderBy('tanggal_waktu', 'desc')->get();
    return view('laporan', compact('transaksi'));
}
public function getDetail($id)
{
    $transaksi = Transaksi::with(['detailTransaksi.barang'])->findOrFail($id);
    return response()->json($transaksi);
}


}
