<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KaryawanController extends Controller
{
    /**
     * Display a listing of the karyawan.
     */
    public function index(Request $request)
    {
        $query = Karyawan::query();

        if ($request->filled('search')) {
            $query->where('username', 'like', "%{$request->search}%");
        }

        if ($request->filter == 'owner') {
            $query->where('role', 'owner');
        } elseif ($request->filter == 'karyawan') {
            $query->where('role', 'karyawan');
        }

        $karyawan = $query->orderBy('username')->paginate(10);

        return view('master.karyawan', compact('karyawan'));
    }

    /**
     * Show the form for creating a new karyawan.
     */
    public function create()
    {
        return view('master.karyawan_create');
    }

    /**
     * Store a newly created karyawan in storage.
     */
    public function store(Request $request)
    {
        // Validasi data
        $request->validate([
            'username' => 'required|unique:karyawan,username',
            'password' => 'required|min:6',
            'role' => 'required|in:owner,karyawan',
        ]);

        // Simpan karyawan baru
        Karyawan::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    /**
     * Update the specified karyawan in storage.
     */
    public function update(Request $request, $id)
    {
        $karyawan = Karyawan::findOrFail($id);

        // Validasi data
        $request->validate([
            'username' => 'required|unique:karyawans,username,' . $karyawan->id_karyawan,
            'password' => 'nullable|min:6',
            'role' => 'required|in:owner,karyawan',
        ]);

        $karyawan->username = $request->username;
        if ($request->password) {
            $karyawan->password = Hash::make($request->password);
        }
        $karyawan->role = $request->role;
        $karyawan->save();

        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil diperbarui.');
    }

    /**
     * Remove the specified karyawan from storage.
     */
    public function destroy($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $karyawan->delete();

        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil dihapus.');
    }
}
