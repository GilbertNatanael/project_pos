<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pembelian;
use App\Models\DetailPembelian;
use Carbon\Carbon;

class PembelianController extends Controller
{
    public function index(Request $request)
    {
        // Dapatkan data dengan pagination
        $pembelian = Pembelian::with('detailPembelian')
            ->orderByDesc('tanggal')
            ->paginate(10);

        // Jika request AJAX, kembalikan view yang sama (untuk pagination AJAX)
        if ($request->ajax()) {
            return view('pembelian.pembelian', compact('pembelian'))->render();
        }

        // Untuk request normal, return view biasa
        return view('pembelian.pembelian', compact('pembelian'));
    }

    public function tambah()
    {
        $barang = DB::table('barang')->get();
        return view('pembelian.tambah_pembelian', compact('barang'));
    }

    public function simpan(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:barang,id_barang',
            'items.*.nama' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric|min:0',
            'items.*.satuan' => 'required|string', // Tambahan validasi satuan
            'total' => 'required|numeric|min:0'
        ]);

        DB::beginTransaction();
        try {
            $pembelian = Pembelian::create([
                'tanggal' => now(),
                'jumlah_item' => count($data['items']),
                'total' => $data['total'],
            ]);

            foreach ($data['items'] as $item) {
                DetailPembelian::create([
                    'id_pembelian' => $pembelian->id_pembelian,
                    'id_barang' => $item['id'],
                    'nama_barang' => $item['nama'],
                    'jumlah' => $item['qty'],
                    'satuan' => $item['satuan'], // Tambahan field satuan
                    'subtotal' => $item['qty'] * $item['harga'],
                ]);

                DB::table('barang')->where('id_barang', $item['id'])->increment('jumlah_barang', $item['qty']);
            }

            DB::commit();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function detail($id)
    {
        // Join dengan tabel barang untuk mendapatkan satuan
        $details = DB::table('detail_pembelian')
            ->leftJoin('barang', 'detail_pembelian.id_barang', '=', 'barang.id_barang')
            ->select(
                'detail_pembelian.*',
                'barang.satuan_barang'
            )
            ->where('detail_pembelian.id_pembelian', $id)
            ->get()
            ->map(function ($detail) {
                return [
                    'nama_barang' => $detail->nama_barang,
                    'jumlah' => $detail->jumlah,
                    'satuan' => $detail->satuan ?? $detail->satuan_barang ?? 'pcs', // Prioritas: satuan dari detail_pembelian, lalu dari barang, default 'pcs'
                    'subtotal' => $detail->subtotal
                ];
            });

        return response()->json($details);
    }
}