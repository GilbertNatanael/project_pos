<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\History;
use Illuminate\Support\Facades\Auth;
use App\Models\Kategori;
class BarangController extends Controller
{
    public function create()
{
    $kategori = Kategori::orderBy('nama_kategori')->get();
    return view('master.create.create_barang', compact('kategori'));
}
   
public function index(Request $request)
{
    $query = Barang::query();

    // Filter pencarian
    if ($request->filled('search')) {
        $query->where('nama_barang', 'like', "%{$request->search}%")
              ->orWhere('kode_barang', 'like', "%{$request->search}%");
    }

    // Filter kategori - TAMBAHAN BARU
    if ($request->filled('kategori')) {
        $query->where('kategori_id', $request->kategori);
    }

    // Filter stok
    if ($request->filter == 'stok_habis') {
        $query->where('jumlah_barang', '<=', 0);
    } elseif ($request->filter == 'stok_tersedia') {
        $query->where('jumlah_barang', '>', 0);
    }

    $barang = $query->with('kategori')->orderBy('nama_barang')->paginate(10);
    $kategori = Kategori::orderBy('nama_kategori')->get();

    // Preserve query parameters untuk pagination
    $barang->appends($request->query());

    // Handle AJAX request untuk pagination
    if ($request->ajax()) {
        $html = view('master.barang', compact('barang', 'kategori'))->render();
        
        return response()->json([
            'html' => $html,
            'success' => true
        ]);
    }

    return view('master.barang', compact('barang', 'kategori'));
}

// Ganti method store() di BarangController
public function store(Request $request)
{
    $request->validate([
        'kode_barang' => 'required|unique:barang,kode_barang',
        'nama_barang' => 'required|string|max:255',
        'harga_barang' => 'required|numeric',
        'jumlah_barang' => 'required|numeric|min:0',
        'satuan_barang' => 'nullable|string|max:50',
        'kategori_id' => 'required|exists:kategori,id',
        'merek' => 'nullable|string|max:255', // Ganti dari merek_barang ke merek
    ]);

    $barang = new Barang();
    $barang->kode_barang = $request->kode_barang;
    $barang->nama_barang = $request->nama_barang;
    $barang->harga_barang = $request->harga_barang;
    $barang->jumlah_barang = $request->jumlah_barang;
    $barang->satuan_barang = $request->satuan_barang;
    $barang->merek = $request->merek; // Ganti dari merek_barang ke merek
    $barang->kategori_id = $request->kategori_id;

    $barang->save();

    $this->logHistory($barang->id_barang, 'tambah');

    return redirect()->route('barang')->with('success', 'Barang berhasil ditambahkan.');
}


public function transaksi()
    {
        $barang = Barang::all();
        return view('transaksi', compact('barang'));
    }
// Ganti method update() di BarangController
public function update(Request $request, string $id)
{
    $request->validate([
        'kode_barang' => 'required|string|max:255|unique:barang,kode_barang,' . $id . ',id_barang',
        'nama_barang' => 'required|string|max:255',
        'harga_barang' => 'required|numeric',
        'jumlah_barang' => 'required|numeric|min:0',
        'satuan_barang' => 'nullable|string|max:50',
        'merek' => 'nullable|string|max:255',
        'kategori_id' => 'required|exists:kategori,id', // Tambahkan validation kategori
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
    $barang->merek = $request->merek;
    $barang->kategori_id = $request->kategori_id; // Tambahkan update kategori
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
    if (!$id_karyawan) return;

    $barang = Barang::find($id_barang);
    if (!$barang) return;

    History::create([
        'id_barang' => $barang->id_barang,
        'id_karyawan' => $id_karyawan,
        'aksi' => $aksi,
        'nama_barang' => $barang->nama_barang // ✅ Simpan langsung nama barang
    ]);
}


public function tambahPembelian()
{
    $barang = Barang::with('kategori')
        ->leftJoin('kategori', 'barang.kategori_id', '=', 'kategori.id')
        ->select('barang.*', 'kategori.nama_kategori')
        ->orderBy('nama_barang')
        ->get();
    
    $kategoris = Kategori::orderBy('nama_kategori')->get();
    
    return view('pembelian.tambah', compact('barang', 'kategoris'));
}
}