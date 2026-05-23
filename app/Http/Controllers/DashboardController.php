<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $listKab = DB::table('wilayah_master')
        ->select('kode_kab', 'nama_kab')
        ->distinct()
        ->orderBy('kode_kab', 'asc')
        ->get();

        $listKec = collect();
        if ($request->filled('kab')) {
            $kodeKab = trim($request->kab);
            $listKec = DB::table('wilayah_master')
                    ->select('kode_kec', 'nama_kec')
                    ->where('kode_kab', $request->kab)
                    ->whereNotNull('nama_kec')
                    ->where('nama_kec', '!=', '')
                    ->distinct()
                    ->orderBy('kode_kec', 'asc')
                    ->get();
        }

        $query = DB::table('wilayahs')
        ->leftJoin('kendala_master', 'wilayahs.bobot_kendala', '=', 'kendala_master.bobot')
        ->select('wilayahs.*', 'kendala_master.nama_kendala');

        // Filter Geografis
        if ($request->filled('kab')) {
            $query->where('kode_kab', $request->kab);
        }
        if ($request->filled('kec')) {
            $query->where('kode_kec', $request->kec);
        }
        if ($request->filled('search')) {
            $cari     = trim($request->search);
            $cariLow  = strtolower($cari);
            $isAngka  = is_numeric($cari);
          
            $query->where(function ($q) use ($cari, $cariLow, $isAngka) {
          
                // 1. Cari nama SLS langsung (contoh: "RT 01 DUSUN 02")
                $q->where('nama_sls', 'LIKE', "%{$cari}%")
                  ->orWhere('nama_desa',  'LIKE', "%{$cari}%")
                  ->orWhere('nama_kec',   'LIKE', "%{$cari}%")
                  ->orWhere('nama_kab',   'LIKE', "%{$cari}%")
                  ->orWhere('id_sub_sls', 'LIKE', "%{$cari}%")
                  ->orWhere('cluster_label', 'LIKE', "%{$cari}%");
          
                // 2. FIX: Normalize angka — "RT 1" juga bisa ketemu "RT 01"
                $cariNormalized = preg_replace_callback('/\b(\d+)\b/', function ($m) {
                    // Pad angka 1 digit jadi 2 digit (1->01, 2->02, dst)
                    return strlen($m[1]) === 1 ? str_pad($m[1], 2, '0', STR_PAD_LEFT) : $m[1];
                }, $cari);
          
                if ($cariNormalized !== $cari) {
                    $q->orWhere('nama_sls', 'LIKE', "%{$cariNormalized}%");
                }
          
                // input (RT 01 -> cari juga RT 1)
                $cariStripped = preg_replace('/\b0+(\d)/', '$1', $cari);
                if ($cariStripped !== $cari) {
                    $q->orWhere('nama_sls', 'LIKE', "%{$cariStripped}%");
                }
                if ($isAngka) {
                    $angka        = (int) $cari;
                    $angkaPadded  = str_pad($angka, 2, '0', STR_PAD_LEFT); // 1 → "01", 2 → "02"
          
                    // Cari nama SLS yang mengandung angka
                    $q->orWhere('nama_sls', 'LIKE', "%{$angka}%")
                      ->orWhere('nama_sls', 'LIKE', "%{$angkaPadded}%");
                }
            });
        }
            
        // HITUNG STATS BERDASARKAN WILAYAH ---
        $dataSesuaiWilayah = $query->get();
        
        $totalSelesai   = $dataSesuaiWilayah->sum('selesai');
        $totalDiperiksa = $dataSesuaiWilayah->sum('diperiksa');
        $totalMuatan    = $dataSesuaiWilayah->sum('muatan');
        $totalLancar     = $dataSesuaiWilayah->where('cluster_label', 'Lancar')->count();
        $totalWaspada    = $dataSesuaiWilayah->where('cluster_label', 'Waspada')->count();
        $totalTerkendala = $dataSesuaiWilayah->where('cluster_label', 'Terkendala')->count();

        // Hitung Detail Kendala Berbobot
        $countRingan = $dataSesuaiWilayah->where('bobot_kendala', 1)->count();
        $countSedang = $dataSesuaiWilayah->where('bobot_kendala', 2)->count();
        $countBerat  = $dataSesuaiWilayah->where('bobot_kendala', '>=', 3)->count();
        
        // REVISI: Hitung Jumlah Kendala Manual yang BELUM Diverifikasi (Bobotnya masih 0 tapi teks ada)
        $countBelumVerifikasi = $dataSesuaiWilayah->where('bobot_kendala', 0)
            ->whereNotNull('keterangan_kendala')
            ->filter(function($item) {
                return trim($item->keterangan_kendala) !== '' && str_contains($item->keterangan_kendala, 'B0 (Manual)');
            })->count();
    
        // 3. Filter Tabel berdasarkan Klaster
        if ($request->filled('cluster')) {
            $query->where('cluster_label', $request->cluster);
        }

        // Filter Berdasarkan Bobot (Jika rincian di kotak indikator diklik)
        if ($request->filled('bobot')) {
            $query->where('bobot_kendala', $request->bobot);
        }

        // Filter Khusus 100% dan 0%
        if ($request->filled('status_progres')) {
            if ($request->status_progres == '100') {
                $query->whereRaw('selesai >= muatan')->where('muatan', '>', 0);
            } elseif ($request->status_progres == '0') {
                $query->where('selesai', 0)->where('muatan', '>', 0);
            }
        }

        // REVISI UTAMA: Menyaring baris data tabel agar hanya menampilkan kendala manual B0 yang belum diverifikasi
        if ($request->filled('belum_verifikasi') && $request->belum_verifikasi == '1') {
            $query->where('wilayahs.bobot_kendala', 0)
                  ->whereNotNull('wilayahs.keterangan_kendala')
                  ->where('wilayahs.keterangan_kendala', 'LIKE', '%B0 (Manual)%');
        }
    
        $persentaseTotal = ($totalMuatan > 0) ? round(($totalSelesai / $totalMuatan) * 100, 1) : 0;
        
        // Ambil waktu laporan
        $lastUpdate = DB::table('wilayahs')->max('updated_at');
        $waktuUpdate = $lastUpdate ? Carbon::parse($lastUpdate)->format('H:i') : '--:--';
        $waktuLaporan = $lastUpdate ? Carbon::parse($lastUpdate)->format('d M, H:i') : '--:--';

        // Ambil waktu Analisis AI
        $aiRunPath = storage_path('app/python/last_ai_run.txt');
        $waktuAI = file_exists($aiRunPath) 
                ? Carbon::parse(file_get_contents($aiRunPath))->format('d M, H:i') 
                : '--:--';

        // Paginasi Tabel
        $wilayahs = $query->orderBy('id_sub_sls', 'asc')->paginate(10)->withQueryString();

        return view('dashboard', compact(
            'wilayahs', 'listKab', 'listKec', 'totalSelesai', 'totalDiperiksa', 
            'totalMuatan', 'persentaseTotal', 'totalLancar', 'totalWaspada', 
            'totalTerkendala', 'waktuUpdate','waktuLaporan', 'waktuAI',
            'countRingan', 'countSedang', 'countBerat', 'countBelumVerifikasi'
        ));
    }

    // --- REVISI BPS: FUNGSI UNTUK VERIFIKASI BOBOT KENDALA MANUAL OLEH PENGAWAS ---
    public function verifikasiBobotManual(Request $request, $id)
    {
        $data = DB::table('wilayahs')->where('id_sub_sls', $id)->first();
        if (!$data) return back()->with('error', 'Data SLS tidak ditemukan.');

        $request->validate([
            'bobot_pilihan' => 'required|in:1,2,3'
        ]);

        $bobotPilihan = (int) $request->bobot_pilihan; // Ambil nilai angka 1, 2, atau 3

        // Ganti label string teks 'B0 (Manual)' menjadi 'B1', 'B2', atau 'B3' sesuai keputusan Pengawas BPS
        $keteranganEksisting = $data->keterangan_kendala;
        $keteranganBaru = str_replace('B0 (Manual)', 'B' . $bobotPilihan, $keteranganEksisting);

        // Hitung ulang penentuan label Klaster Sementara (sebelum tombol Klaster AI memproses ulang)
        $labelBaru = 'Lancar';
        if ($bobotPilihan >= 3) {
            $labelBaru = 'Terkendala';
        } elseif ($bobotPilihan == 2 || $bobotPilihan == 1) {
            $labelBaru = 'Waspada';
        }

        $payload = [
            'keterangan_kendala' => $keteranganBaru,
            'bobot_kendala' => $bobotPilihan,
            'cluster_label' => $labelBaru,
            'updated_at' => now()
        ];

        // Update ke tabel utama wilayahs
        DB::table('wilayahs')->where('id_sub_sls', $id)->update($payload);
        
        // Sinkronkan ke tabel history harian biar grafik data time-series tidak galat
        DB::table('history_kendala')
            ->where('id_sub_sls', $id)
            ->where('tanggal_catat', date('Y-m-d'))
            ->update($payload);

        return back()->with('success', 'Kendala manual berhasil diverifikasi menjadi Bobot ' . $bobotPilihan . '!');
    }

    public function selesaikanKendala(Request $request, $id)
    {
        $data = DB::table('wilayahs')->where('id_sub_sls', $id)->first();
        if (!$data) return back();

        $riwayats = explode("\n", trim($data->keterangan_kendala));
        $selectedIndexes = $request->input('selected_lines', []);

        // 1. Tandai centang pada baris yang dipilih
        foreach ($selectedIndexes as $idx) {
            if (isset($riwayats[$idx]) && !str_contains($riwayats[$idx], '✅')) {
                $riwayats[$idx] = trim($riwayats[$idx]) . " ✅";
            }
        }

        $keteranganBaru = implode("\n", $riwayats);

        // 2. LOGIKA PINTAR: Hitung bobot dari kendala yang BELUM beres
        $bobotBaru = 0;
        foreach($riwayats as $line) {
            if(trim($line) != '' && !str_contains($line, '✅')) {
                // Cek kode B1, B2, atau B3 di dalam teks
                if (str_contains($line, 'B3')) {
                    $bobotBaru = max($bobotBaru, 3); // Kalau ada B3, minimal bobot 3
                } elseif (str_contains($line, 'B2')) {
                    $bobotBaru = max($bobotBaru, 2);
                } elseif (str_contains($line, 'B1')) {
                    $bobotBaru = max($bobotBaru, 1);
                }
            }
        }

        // 3. Tentukan Label Klaster berdasarkan bobot baru
        $labelBaru = 'Lancar';
        if ($bobotBaru >= 3) $labelBaru = 'Terkendala';
        elseif ($bobotBaru == 2 || $bobotBaru == 1) $labelBaru = 'Waspada';

        // 4. Update ke Database
        $payload = [
            'keterangan_kendala' => $keteranganBaru,
            'bobot_kendala' => $bobotBaru,
            'cluster_label' => $labelBaru,
            'updated_at' => now()
        ];

        DB::table('wilayahs')->where('id_sub_sls', $id)->update($payload);
        
        // Update History juga biar sinkron hari ini
        DB::table('history_kendala')
            ->where('id_sub_sls', $id)
            ->where('tanggal_catat', date('Y-m-d'))
            ->update($payload);

        return back()->with('success', 'Bobot berhasil diupdate secara otomatis!')->with('open_modal', $id);
    }
}