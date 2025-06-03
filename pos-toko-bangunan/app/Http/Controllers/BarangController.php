<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\History;
use Illuminate\Support\Facades\Auth;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::query();

        if ($request->filled('search')) {
            $query->where('nama_barang', 'like', "%{$request->search}%")
                  ->orWhere('kode_barang', 'like', "%{$request->search}%");
        }

        if ($request->filter == 'stok_habis') {
            $query->where('jumlah_barang', '<=', 0);
        } elseif ($request->filter == 'stok_tersedia') {
            $query->where('jumlah_barang', '>', 0);
        }

        $barang = $query->orderBy('nama_barang')->paginate(10);

        return view('master.barang', compact('barang'));
    }

    public function transaksi()
    {
        $barang = Barang::all();
        return view('transaksi', compact('barang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_barang' => 'required|unique:barang,kode_barang',
            'nama_barang' => 'required|string|max:255',
            'harga_barang' => 'required|numeric',
            'jumlah_barang' => 'required|numeric|min:0',
            'satuan_barang' => 'nullable|string|max:50',
        ]);

        $barang = new Barang();
        $barang->kode_barang = $request->kode_barang;
        $barang->nama_barang = $request->nama_barang;
        $barang->harga_barang = $request->harga_barang;
        $barang->jumlah_barang = $request->jumlah_barang;
        $barang->satuan_barang = $request->satuan_barang;
        $barang->save();

        $this->logHistory($barang->id_barang, 'tambah');

        return redirect()->route('barang')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'kode_barang' => 'required|string|max:255|unique:barang,kode_barang,' . $id . ',id_barang',
            'nama_barang' => 'required|string|max:255',
            'harga_barang' => 'required|numeric',
            'jumlah_barang' => 'required|numeric|min:0',
            'satuan_barang' => 'nullable|string|max:50',
        ]);

        $barang = Barang::find($id);

        if (!$barang) {
            return redirect()->route('barang')->with('error', 'Barang tidak ditemukan.');
        }

        $barang->kode_barang = $request->kode_barang;
        $barang->nama_barang = $request->nama_barang;
        $barang->harga_barang = $request->harga_barang;
        $barang->jumlah_barang = $request->jumlah_barang;
        $barang->satuan_barang = $request->satuan_barang;
        $barang->save();

        $this->logHistory($barang->id_barang, 'update');

        return redirect()->route('barang')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $barang = Barang::find($id);

        if (!$barang) {
            return redirect()->route('barang')->with('error', 'Barang tidak ditemukan.');
        }

        $this->logHistory($barang->id_barang, 'hapus');
        $barang->delete();

        return redirect()->route('barang')->with('success', 'Barang berhasil dihapus.');
    }

    // ✅ Method bantu untuk mencatat history
    private function logHistory($id_barang, $aksi)
    {
        $id_karyawan = session('id_karyawan');

        if (!$id_karyawan) return; // Jangan log jika tidak login

        History::create([
            'id_barang' => $id_barang,
            'id_karyawan' => $id_karyawan,
            'aksi' => $aksi
        ]);
    }

    public function tambahPembelian()
    {
        $barang = Barang::orderBy('nama_barang')->get();
        return view('pembelian.tambah', compact('barang'));
    }
}