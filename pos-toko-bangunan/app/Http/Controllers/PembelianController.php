<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pembelian;
use App\Models\DetailPembelian;
use App\Models\Kategori;
use Carbon\Carbon;

class PembelianController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembelian::with('detailPembelian.barang.kategori')
            ->orderByDesc('tanggal');

        // Filter berdasarkan nama barang
        if ($request->filled('nama_barang')) {
            $query->whereHas('detailPembelian', function ($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->nama_barang . '%');
            });
        }

        // Filter berdasarkan kategori
        if ($request->filled('kategori_id')) {
            $query->whereHas('detailPembelian.barang', function ($q) use ($request) {
                $q->where('kategori_id', $request->kategori_id);
            });
        }

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
        }

        $pembelian = $query->paginate(10)->appends($request->all());

        // Dapatkan data kategori untuk dropdown filter
        $kategoris = Kategori::all();

        // Jika request AJAX, kembalikan view yang sama (untuk pagination AJAX)
        if ($request->ajax()) {
            return view('pembelian.pembelian', compact('pembelian', 'kategoris'))->render();
        }

        // Untuk request normal, return view biasa
        return view('pembelian.pembelian', compact('pembelian', 'kategoris'));
    }

        public function tambah()
        {
            $barang = DB::table('barang')
                ->leftJoin('kategori', 'barang.kategori_id', '=', 'kategori.id')
                ->select('barang.*', 'kategori.nama_kategori')
                ->get();
            
            $kategoris = Kategori::all();
            
            return view('pembelian.tambah_pembelian', compact('barang', 'kategoris'));
        }

    public function simpan(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:barang,id_barang',
            'items.*.nama' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric|min:0',
            'items.*.satuan' => 'required|string',
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
                    'satuan' => $item['satuan'],
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
        // Ambil detail pembelian dengan join ke barang dan kategori
        $details = DetailPembelian::with(['barang.kategori'])
            ->where('id_pembelian', $id)
            ->get()
            ->map(function ($detail) {
                return [
                    'nama_barang' => $detail->nama_barang,
                    'jumlah' => $detail->jumlah,
                    'satuan' => $detail->satuan ?? $detail->barang->satuan_barang ?? 'pcs',
                    'merek' => $detail->barang->merek ?? '-',
                    'kategori' => $detail->barang->kategori->nama_kategori ?? '-',
                    'subtotal' => $detail->subtotal
                ];
            });

        return response()->json($details);
    }
}