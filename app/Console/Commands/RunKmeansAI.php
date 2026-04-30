<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunKmeansAI extends Command
{
    // Nama perintah yang dipanggil
    protected $signature = 'ai:run-kmeans';

    // Deskripsi tugas
    protected $description = 'Menjalankan algoritma K-Means dan mencatat history progres per jam';

    public function handle()
    {
        $this->info('Memulai pemrosesan AI K-Means & Snapshot History...');

        try {
            // 1. Ambil data dari tabel wilayahs
            $dataSubSls = DB::table('wilayahs')->where('muatan', '>', 0)->get();

            if ($dataSubSls->isEmpty()) {
                $this->warn('Aman: Belum ada data baru untuk diproses.');
                return;
            }

            // 2. Tulis JSON untuk diolah Python
            $inputPath = storage_path('app/python/data_input.json');
            file_put_contents($inputPath, json_encode($dataSubSls));

            // 3. Eksekusi Python
            $scriptPath = storage_path('app/python/kmeans_clustering.py');
            $pythonPath = 'C:\Users\msysa\AppData\Local\Programs\Python\Python313\python.exe';
            
            $command = '"' . $pythonPath . '" "' . $scriptPath . '" "' . $inputPath . '" 2>&1';
            $outputRaw = shell_exec($command);
            $output = json_decode($outputRaw, true);

            // 4. Update Database & Simpan ke History
            if (isset($output['status']) && $output['status'] == 'ok') {
                foreach ($output['hasil'] as $hasil) {
                    // Ambil ID dari Python
                    $idSubSls = $hasil['id_subsls']; 
                
                    // 1. UPDATE LABEL KLASTER DI WILAYAHS
                    DB::table('wilayahs')
                        ->where('id_sub_sls', $idSubSls)
                        ->update([
                            'cluster_label' => $hasil['cluster_label']
                        ]);
                
                    // 2. AMBIL SEMUA DATA TERBARU DARI DATABASE 
                    $dataAsli = DB::table('wilayahs')->where('id_sub_sls', $idSubSls)->first();
                
                    if ($dataAsli) {
                        DB::table('history_kendala')->updateOrInsert(
                            [
                                'id_sub_sls'    => $idSubSls,
                                'tanggal_catat' => date('Y-m-d'),
                            ],
                            [
                                'selesai'            => $dataAsli->selesai,
                                'diperiksa'          => $dataAsli->diperiksa, 
                                'muatan'             => $dataAsli->muatan,
                                'keterangan_kendala' => $dataAsli->keterangan_kendala,
                                'bobot_kendala'      => $dataAsli->bobot_kendala,
                                'cluster_label'      => $dataAsli->cluster_label, 
                                'updated_at'         => now(),
                            ]
                        );
                    }
                }
                $this->info('Berhasil! Klaster diupdate dan history progres telah disimpan.');
            } else {
                $this->error('Gagal: ' . $outputRaw);
            }

            file_put_contents(storage_path('app/python/last_ai_run.txt'), now());

        } catch (\Exception $e) {
            $this->error('Error Sistem: ' . $e->getMessage());
        }
    }
}