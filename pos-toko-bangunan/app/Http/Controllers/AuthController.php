<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    public function showLoginForm()
    {
        $karyawans = Karyawan::all();
        return view('login', compact('karyawans'));
    }

    public function processLogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
    
        $karyawan = Karyawan::where('username', $request->username)->first();
    
        if ($karyawan && Hash::check($request->password, $karyawan->password)) {
            Session::put('id_karyawan', $karyawan->id_karyawan);
            Session::put('username', $karyawan->username);
            Session::put('role', $karyawan->role);
    
            // Redirect berdasarkan role
            if ($karyawan->role === 'owner') {
                return redirect()->route('dashboard');
            } else {
                return redirect()->route('transaksi');
            }
        }
    
        return back()->with('error', 'Username atau password salah!');
    }
    

    public function logout()
    {
        Session::flush();
        return redirect()->route('login');
    }
}
