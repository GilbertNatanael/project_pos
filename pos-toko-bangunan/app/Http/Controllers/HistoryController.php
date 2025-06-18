<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Http\Request; 

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = History::query()->with('barang', 'karyawan');

        // Filter nama_barang
        if ($request->filled('nama_barang')) {
            $query->where('nama_barang', 'like', '%' . $request->nama_barang . '%');
        }

        // Filter nama_karyawan
        if ($request->filled('nama_karyawan')) {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('username', 'like', '%' . $request->nama_karyawan . '%');
            });
        }

        // Filter aksi
        if ($request->filled('aksi')) {
            $query->where('aksi', $request->aksi);
        }

        // Filter tanggal (format input: yyyy-mm-dd)
        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        // Urutkan berdasarkan tanggal terbaru dan paginate
        $histories = $query->latest()->paginate(10);

        // Append query parameters ke pagination links
        $histories->appends($request->query());

        return view('history', compact('histories'));
    }

    // Method ini bisa dihapus karena sudah digabung dengan index()
    public function history()
    {
        // Redirect ke method index untuk konsistensi
        return redirect()->route('history');
    }
}