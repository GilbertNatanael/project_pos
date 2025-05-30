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
    if ($request->ajax()) {
        $pembelian = Pembelian::with('detailPembelian')
            ->orderByDesc('tanggal')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id_pembelian,
                    'tanggal' => date('Y-m-d', strtotime($p->tanggal)),
                    'total_item' => $p->detailPembelian->sum('jumlah'),
                    'total_harga' => number_format($p->total, 0, ',', '.'),
                ];
            });

        return response()->json(['data' => $pembelian]);
    }

    return view('pembelian.pembelian');
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
    $details = DetailPembelian::where('id_pembelian', $id)->get();
    return response()->json($details);
}

}

