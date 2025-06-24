<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $query = Kategori::query();

        if ($request->filled('search')) {
            $query->where('nama_kategori', 'like', '%' . $request->search . '%');
        }

        $kategori = $query->orderBy('nama_kategori')->paginate(10);

        return view('master.kategori', compact('kategori'));
    }

    public function create()
    {
        return view('master.kategori_create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|unique:kategori,nama_kategori',
        ]);

        Kategori::create($request->only('nama_kategori'));

        return redirect()->route('kategori')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('master.kategori_edit', compact('kategori'));
    }

    public function update(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required|string|unique:kategori,nama_kategori,' . $kategori->id,
        ]);

        $kategori->update($request->only('nama_kategori'));

        return redirect()->route('kategori')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();

        return redirect()->route('kategori')->with('success', 'Kategori berhasil dihapus.');
    }
}
