<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDataController extends Controller
{
    public function dataset(Request $request)
    {
        $kabupatens = DB::table('wilayah_master')
        ->select('kode_kab', 'nama_kab')
        ->distinct()
        ->orderBy('nama_kab')
        ->get();

        $kecamatans = collect();
        $desas      = collect();

        // Cek apakah ada filter yang diisi
        $hasFilter = $request->filled('kab')  || $request->filled('kec')
                || $request->filled('desa') || $request->filled('search');

        // Kalau tidak ada filter → tabel kosong, tidak perlu query ke subsls

        $query = DB::table('subsls');

        // FIX FORMAT KODE:
        if ($request->filled('kab')) {
            // kode_kab di subsls = 2 digit terakhir
            $kodeKab = substr($request->kab, -2);
            $query->where('kode_kab', $kodeKab);
        }

        if ($request->filled('kec')) {
            // kode_kec di subsls = 3 digit terakhir
            $kodeKec = substr($request->kec, -3);
            $query->where('kode_kec', $kodeKec);
        }

        if ($request->filled('desa')) {
            // kode_desa di subsls = 3 digit terakhir
            $kodeDesa = substr($request->desa, -3);
            $query->where('kode_desa', $kodeDesa);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_sls', 'like', '%' . $request->search . '%')
                  ->orWhere('nama_ketua_sls', 'like', '%' . $request->search . '%')
                  ->orWhere('id_subsls', 'like', '%' . $request->search . '%');
            });
        }

        // hitung count SEBELUM paginate
        $count    = $query->count();
        $datasets = $query->paginate(50)->withQueryString();

        $kabupatens = DB::table('wilayah_master')
            ->select('kode_kab', 'nama_kab')
            ->distinct()
            ->orderBy('nama_kab')
            ->get();

        // Kirim kecamatan & desa yang aktif ke view
        $kecamatans = collect();
        $desas      = collect();

        if ($request->filled('kab')) {
            $kecamatans = DB::table('wilayah_master')
                ->select('kode_kec as id', 'nama_kec as nama')
                ->where('kode_kab', (string) $request->kab)
                ->distinct()
                ->orderBy('nama_kec')
                ->get();
        }

        if ($request->filled('kec')) {
            $desas = DB::table('wilayah_master')
                ->select('id_desa as id', 'nama_desa as nama')
                ->where('kode_kec', (string) $request->kec)
                ->distinct()
                ->orderBy('nama_desa')
                ->get();
        }

        return view('admin.master.dataset', compact(
            'datasets', 'count', 'kabupatens', 'kecamatans', 'desas'
        ));
    }

    // Endpoint AJAX: ambil kecamatan by kabupaten
    public function getKecamatan($kodeKab)
    {
        $data = DB::table('wilayah_master')
            ->select('kode_kec as id', 'nama_kec as nama')
            ->where('kode_kab', (string) $kodeKab)
            ->distinct()
            ->orderBy('nama_kec')
            ->get();

        return response()->json($data);
    }

    // Endpoint AJAX: ambil desa by kecamatan
    public function getDesa($kodeKec)
    {
        $data = DB::table('wilayah_master')
            ->select('id_desa as id', 'nama_desa as nama')
            ->where('kode_kec', (string) $kodeKec)
            ->distinct()
            ->orderBy('nama_desa')
            ->get();

        return response()->json($data);
    }


    public function wilayah(Request $request)
    {
        $query = DB::table('wilayah_master');

        if ($request->filled('kab')) {
            $query->where('kode_kab', $request->kab);
        }
        if ($request->filled('kec')) {
            $query->where('kode_kec', $request->kec);
        }
        if ($request->filled('desa')) {
            $query->where('id_desa', $request->desa);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama_kec', 'like', '%' . $request->search . '%')
                ->orWhere('nama_desa', 'like', '%' . $request->search . '%')
                ->orWhere('id_desa', 'like', '%' . $request->search . '%');
            });
        }

        $count = $query->count(); 
        $wilayahs = $query->paginate(50)->withQueryString();
        
        // Ambil data kabupaten
        $kabupatens = DB::table('wilayah_master')
                        ->select('kode_kab', 'nama_kab')
                        ->distinct()
                        ->orderBy('nama_kab', 'asc')
                        ->get();

        $kecamatans = collect();
        $desas      = collect();

        if ($request->filled('kab')) {
            $kecamatans = DB::table('wilayah_master')
                ->select('kode_kec as id', 'nama_kec as nama')
                ->where('kode_kab', (string) $request->kab)
                ->distinct()
                ->orderBy('nama_kec')
                ->get();
        }

        if ($request->filled('kec')) {
            $desas = DB::table('wilayah_master')
                ->select('id_desa as id', 'nama_desa as nama')
                ->where('kode_kec', (string) $request->kec)
                ->distinct()
                ->orderBy('nama_desa')
                ->get();
        }

        return view('admin.master.wilayah', compact('wilayahs', 'count', 'kabupatens', 'kecamatans', 'desas'));
    }

    public function kendala()
    {
        $kendalas = DB::table('kendala_master')->orderBy('bobot', 'asc')->get();
        $count = $kendalas->count();

        return view('admin.master.kendala', compact('kendalas', 'count'));
    }

    public function importDataset()
    {
        $path = storage_path('app/public/subsls.csv');

        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'File tidak ditemukan di storage/app/public/subsls.csv');
        }

        $file = fopen($path, 'r');
        fgetcsv($file, 1000, ';');
        DB::table('subsls')->truncate();

        while (($data = fgetcsv($file, 1000, ';')) !== FALSE) {
            if (!isset($data[1])) continue;

            DB::table('subsls')->insert([
                'id_subsls'      => $data[1],
                'nama_sls'       => $data[2],
                'nama_ketua_sls' => $data[3],
                'jenis'          => $data[4],
                'kode_kab'       => $data[6],
                'kode_kec'       => $data[7],
                'kode_desa'      => $data[8],
                'kode_sls'       => $data[9],
                'jumlah_kk'      => (int) $data[11],
                'jumlah_muatan'  => (int) $data[17],
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        fclose($file);
        return redirect()->back()->with('success', 'Data Master SLS berhasil diimpor!');
    }

    public function destroyDataset($id)
    {
        DB::table('subsls')->where('id', $id)->delete();
        return redirect()->back()->with('success', 'Data SLS berhasil dihapus!');
    }
}