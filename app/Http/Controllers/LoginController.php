<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // Menampilkan halaman form login
    public function showLoginForm()
    {
        return view('auth.login'); 
    }

    // Memproses data yang diketik 
    public function authenticate(Request $request)
    {
        // Validasi inputan: Username, password, dan role tidak boleh kosong
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
            'role'     => ['required'], 
        ]);

        // Auth::attempt sekarang akan mengecek apakah username, password dAN role yang dipilih di dropdown semuanya cocok dengan yang ada di database
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard')->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '!');
        }

        return back()->withErrors([
            'login_failed' => 'Kombinasi Username, Password, atau Peran tidak sesuai.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}