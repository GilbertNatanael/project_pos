<?php

namespace App\Http\Controllers;

use App\Models\History;

class HistoryController extends Controller
{
    public function index()
    {
        // Ambil semua data history dan kirim ke view
        $histories = History::with('barang', 'karyawan')->latest()->paginate(10); // Menampilkan 10 history
        return view('history', compact('histories'));
    }
    public function history()
{
    // Ambil data history dengan relasi barang dan karyawan
    $histories = History::with('barang', 'karyawan')->latest()->paginate(10);  // Menampilkan 10 data history

    // Kirim data ke view
    return view('history', compact('histories'));
}

}
