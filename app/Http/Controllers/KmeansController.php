<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KmeansController extends Controller
{
    public function jalankanClustering()
    {
        try {
            // 1. Ambil data SubSLS dari tabel wilayahs
            $dataSubSls = DB::table('wilayahs')->where('muatan', '>', 0)->get();

            if ($dataSubSls->isEmpty()) {
                return redirect('/dashboard')
                    ->with('error', 'Belum ada data wilayah untuk di-cluster.');
            }

            // 2. Simpan ke file JSON sebagai input Python
            $inputPath = storage_path('app/python/data_input.json');
            file_put_contents($inputPath, json_encode($dataSubSls));

            // 3. Setup path script & Python
            $scriptPath = storage_path('app/python/kmeans_clustering.py');
            $pythonPath = 'C:\Users\msysa\AppData\Local\Programs\Python\Python313\python.exe';

            if (!file_exists($scriptPath)) {
                return redirect('/dashboard')
                    ->with('error', 'Script Python tidak ditemukan di: ' . $scriptPath);
            }

            // 4. Eksekusi Python
            $command   = '"' . $pythonPath . '" "' . $scriptPath . '" "' . $inputPath . '" 2>&1';
            $outputRaw = shell_exec($command);

            // 5. Cek output mentah sebelum decode
            if (empty($outputRaw)) {
                return redirect('/dashboard')
                    ->with('error', 'Python tidak mengembalikan output apapun. Cek path Python & script.');
            }

            $output = json_decode($outputRaw, true);

            if (!$output || !isset($output['status'])) {
                return redirect('/dashboard')
                    ->with('error', 'Output Python tidak valid: ' . substr($outputRaw, 0, 300));
            }

            if ($output['status'] !== 'ok') {
                return redirect('/dashboard')
                    ->with('error', 'Python error: ' . ($output['message'] ?? $outputRaw));
            }

            // 6. Update database
            //  pakai key dari Python ('id_subsls') untuk where ke kolom DB ('id_sub_sls')
            $berhasil = 0;
            $gagal    = 0;

            foreach ($output['hasil'] as $hasil) {
                // Ambil id dari key yang ada di output Python
                $idSubsls = $hasil['id_subsls'] ?? null;

                if (!$idSubsls) {
                    $gagal++;
                    continue;
                }

                $affected = DB::table('wilayahs')
                    ->where('id_sub_sls', $idSubsls)  // kolom di DB = id_sub_sls
                    ->update([
                        'cluster_label' => $hasil['cluster_label'],
                        'updated_at'    => now(),
                    ]);

                $affected ? $berhasil++ : $gagal++;
            }

            // 7. Simpan waktu terakhir clustering (pakai WIB)
            $waktuWIB = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s T');
            file_put_contents(storage_path('app/python/last_ai_run.txt'), $waktuWIB);

            $pesan = "Klasterisasi selesai: {$berhasil} wilayah diperbarui.";
            if ($gagal > 0) {
                $pesan .= " ({$gagal} data tidak cocok — cek id_sub_sls)";
            }

            // return redirect('/dashboard')->with('success', $pesan);
            return back()->with('success', $pesan);

        } catch (\Exception $e) {
            return redirect('/dashboard')
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}