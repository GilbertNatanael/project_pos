<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\DetailPembelian;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PembelianController extends Controller
{
    public function index()
    {
        $pembelian = Pembelian::with('detail.barang')->orderBy('tanggal_waktu', 'desc')->get();
        return view('pembelian.index', compact('pembelian'));
    }

    public function create()
    {
        $barang = Barang::orderBy('nama_barang')->get();
        return view('pembelian.create', compact('barang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_barang.*' => 'required|exists:barang,id_barang',
            'jumlah.*' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();
        try {
            $pembelian = Pembelian::create(); // tanggal otomatis

            foreach ($request->id_barang as $index => $id_barang) {
                $jumlah = $request->jumlah[$index];

                DetailPembelian::create([
                    'id_pembelian' => $pembelian->id_pembelian,
                    'id_barang' => $id_barang,
                    'jumlah' => $jumlah
                ]);

                // Tambah stok barang
                $barang = Barang::find($id_barang);
                $barang->increment('jumlah_barang', $jumlah);
            }

            DB::commit();
            return redirect()->route('pembelian.index')->with('success', 'Pembelian berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan pembelian.');
        }
    }
}



