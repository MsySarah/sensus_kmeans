<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
{
    $kabupaten = DB::table('wilayah_master')
        ->select('kode_kab', 'nama_kab')
        ->distinct()->orderBy('nama_kab')->get();
 
    $query = DB::table('users');
 
    if ($request->filled('search')) {
        $cari = $request->search;
        $query->where(function ($q) use ($cari) {
            $q->where('name',     'LIKE', "%{$cari}%")
              ->orWhere('username','LIKE', "%{$cari}%");
        });
    }
 
    if ($request->filled('role')) {
        $query->where('role', $request->role);
    }
 
    $users = $query->orderBy('id', 'desc')->get();
 
    // Setelah dapat semua user, resolve nama wilayah satu per satu
    // berdasarkan PANJANG kode_wilayah_tugas:
    //   4 digit  = kode kabupaten  (contoh: 1601)
    //   7 digit  = kode kecamatan  (contoh: 1601052)
    //   10 digit = kode desa       (contoh: 1601052001)
    //   16 digit = id_subsls       (contoh: 1601052001000100)
 
    $users = $users->map(function ($u) {
        $u->nama_wilayah = null;
        $u->nama_kec     = null;
        $u->nama_kab     = null;
 
        if (!$u->kode_wilayah_tugas) return $u;
 
        $kode = $u->kode_wilayah_tugas;
        $len  = strlen((string) $kode);
 
        if ($len <= 4) {
            // Kode kabupaten
            $w = DB::table('wilayah_master')
                ->where('kode_kab', $kode)
                ->select('nama_kab')->first();
            $u->nama_wilayah = $w ? $w->nama_kab : null;
 
        } elseif ($len <= 7) {
            // Kode kecamatan
            $w = DB::table('wilayah_master')
                ->where('kode_kec', $kode)
                ->select('nama_kec', 'nama_kab')->first();
            if ($w) {
                $u->nama_wilayah = 'Kec. ' . $w->nama_kec;
                $u->nama_kab     = $w->nama_kab;
            }
 
        } elseif ($len <= 10) {
            // Kode desa
            $w = DB::table('wilayah_master')
                ->where('id_desa', $kode)
                ->select('nama_desa', 'nama_kec', 'nama_kab')->first();
            if ($w) {
                $u->nama_wilayah = $w->nama_desa;
                $u->nama_kec     = $w->nama_kec;
                $u->nama_kab     = $w->nama_kab;
            }
 
        } else {
            // id_subsls (16 digit) — cari via tabel subsls
            $sls = DB::table('subsls')
                ->where('id_subsls', $kode)
                ->select('nama_sls', 'kode_kab', 'kode_kec', 'kode_desa')
                ->first();
            if ($sls) {
                $u->nama_wilayah = $sls->nama_sls;
                // kode_kec di subsls = "052" (pendek)
                // kode_kec di wilayah_master = "1601052" (panjang)
                // Solusi: cari wilayah_master yang kode_kec-nya DIAKHIRI kode_kec dari subsls
                $w = DB::table('wilayah_master')
                    ->where('kode_kec', 'LIKE', '%' . $sls->kode_kec)
                    ->where('kode_kab', 'LIKE', '%' . $sls->kode_kab)
                    ->select('nama_kec', 'nama_kab')
                    ->first();
                if ($w) {
                    $u->nama_kec = $w->nama_kec;
                    $u->nama_kab = $w->nama_kab;
                }
            }
        }
 
        return $u;
    });
 
    // Filter wilayah (dilakukan setelah resolve nama)
    if ($request->filled('f_kab') || $request->filled('f_kec') || $request->filled('f_desa')) {
        $users = $users->filter(function ($u) use ($request) {
            $kode = (string) ($u->kode_wilayah_tugas ?? '');
            if ($request->filled('f_kab')) {
                if (!str_starts_with($kode, $request->f_kab)) return false;
            }
            if ($request->filled('f_kec')) {
                if (!str_starts_with($kode, $request->f_kec)) return false;
            }
            if ($request->filled('f_desa')) {
                if (!str_starts_with($kode, $request->f_desa)) return false;
            }
            return true;
        })->values();
    }
 
    $kecamatans = collect();
    $desas      = collect();
 
    if ($request->filled('f_kab')) {
        $kecamatans = DB::table('wilayah_master')
            ->select('kode_kec as id', 'nama_kec as nama')
            ->where('kode_kab', $request->f_kab)
            ->distinct()->orderBy('nama_kec')->get();
    }
    if ($request->filled('f_kec')) {
        $desas = DB::table('wilayah_master')
            ->select('id_desa as id', 'nama_desa as nama')
            ->where('kode_kec', $request->f_kec)
            ->distinct()->orderBy('nama_desa')->get();
    }
 
    return view('admin.users.index',
        compact('users', 'kabupaten', 'kecamatans', 'desas'));
}

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'role'     => 'required',
        ]);

        $kodeWilayah = null;
        if ($request->filled('id_sub_sls'))  $kodeWilayah = $request->id_sub_sls;
        elseif ($request->filled('kode_desa')) $kodeWilayah = $request->kode_desa;
        elseif ($request->filled('kode_kec'))  $kodeWilayah = $request->kode_kec;
        elseif ($request->filled('kode_kab'))  $kodeWilayah = $request->kode_kab;

        DB::table('users')->insert([
            'name'               => $request->name,
            'username'           => $request->username,
            'password'           => Hash::make($request->password),
            'role'               => $request->role,
            'kode_wilayah_tugas' => $kodeWilayah,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        return redirect()->route('users.index')
            ->with('success', "Akun '{$request->name}' berhasil dibuat.");
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'username' => "required|string|max:50|unique:users,username,{$id}",
            'role'     => 'required',
            'password' => 'nullable|string|min:6',
        ]);

        $user           = User::findOrFail($id);
        $user->name     = $request->name;
        $user->username = $request->username;
        $user->role     = $request->role;

        $kodeWilayah = null;
        if ($request->filled('id_sub_sls'))    $kodeWilayah = $request->id_sub_sls;
        elseif ($request->filled('kode_desa')) $kodeWilayah = $request->kode_desa;
        elseif ($request->filled('kode_kec'))  $kodeWilayah = $request->kode_kec;
        elseif ($request->filled('kode_kab'))  $kodeWilayah = $request->kode_kab;

        if ($kodeWilayah) {
            $user->kode_wilayah_tugas = $kodeWilayah;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        return redirect()->route('users.index')
            ->with('success', "Data '{$user->name}' berhasil diperbarui.");
    }

    public function destroy($id)
    {
        // Cari user berdasarkan ID
        $user = User::findOrFail($id);
    
        // Proteksi: Jangan biarkan admin menghapus dirinya sendiri
        if ($id == Auth::id()) {
            return redirect()->back()->with('error', 'Gagal! Anda tidak diperbolehkan menghapus akun sendiri.');
        }
    
        // Simpan nama untuk notifikasi sebelum dihapus
        $nama = $user->name;
        
        // Hapus permanen dari database
        $user->delete();
    
        return redirect()->route('users.index')
            ->with('success', "Akun petugas '{$nama}' telah berhasil dihapus dari sistem.");
    }

    public function getKecamatan($kodeKab)
    {
        $data = DB::table('wilayah_master')
            ->select('kode_kec as id', 'nama_kec as nama')
            ->where('kode_kab', $kodeKab)
            ->distinct()->orderBy('nama_kec')->get();
        return response()->json($data);
    }

    public function getDesa($kodeKec)
    {
        $data = DB::table('wilayah_master')
            ->select('id_desa as id', 'nama_desa as nama')
            ->where('kode_kec', $kodeKec)
            ->distinct()->orderBy('nama_desa')->get();
        return response()->json($data);
    }

    public function getSls($idDesa)
    {
        $kodeDesa = substr($idDesa, -3);
        $kodeKec  = substr($idDesa, -6, -3);
        $data = DB::table('subsls')
            ->select('id_subsls as id', 'nama_sls as nama')
            ->where('kode_desa', $kodeDesa)
            ->where('kode_kec',  $kodeKec)
            ->orderBy('nama_sls')->get();
        return response()->json($data);
    }
}