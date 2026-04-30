<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyProgressSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Memulai suntik data simulasi...');

        // Ambil semua data wilayah yang muatannya lebih dari 0
        $wilayahs = DB::table('wilayahs')->where('muatan', '>', 0)->get();

        if ($wilayahs->isEmpty()) {
            $this->command->warn('Tabel wilayahs kosong atau tidak ada muatan!');
            return;
        }

        foreach ($wilayahs as $w) {
            $muatan = $w->muatan;
            $rand = rand(1, 100);
            $keterangan_kendala = ''; 

            // Logika Simulasi 
            if ($rand <= 40) {
                // 40% Kemungkinan: LANCAR (Progres tinggi, kendala rendah)
                $selesai = rand(round($muatan * 0.70), $muatan);
                $diperiksa = rand(round($selesai * 0.75), $selesai);
                $bobot_kendala = (rand(1, 100) <= 75) ? 0 : 1; 

            } elseif ($rand <= 75) {
                // 35% Kemungkinan: WASPADA (Progres sedang, kendala sedang)
                $selesai = rand(round($muatan * 0.35), round($muatan * 0.70));
                $diperiksa = rand(round($selesai * 0.35), round($selesai * 0.75));
                $bobot_kendala = (rand(1, 100) <= 45) ? 1 : 2; 

            } else {
                // 25% Kemungkinan: TERKENDALA (Progres rendah, kendala berat)
                $selesai = rand(0, round($muatan * 0.35));
                $diperiksa = rand(0, round($selesai * 0.50));
                $bobot_kendala = (rand(1, 100) <= 35) ? 2 : 3; 
            }

            // agar nilai tidak melebihi muatan atau kurang dari 0
            $selesai = min($selesai, $muatan);
            $diperiksa = min($diperiksa, $selesai);

            // BIKIN TEKS DUMMY KENDALA BERDASARKAN BOBOT
            if ($bobot_kendala == 1) {
                $keterangan_kendala = "Responden sulit ditemui siang hari.\nJanji temu diulang pada malam hari.";
            } elseif ($bobot_kendala == 2) {
                $keterangan_kendala = "Akses jalan menuju SLS rusak karena hujan.\nKetua RT tidak berada di tempat.\nBeberapa warga menolak memberikan data.";
            } elseif ($bobot_kendala == 3) {
                $keterangan_kendala = "Banjir merendam sebagian wilayah SLS.\nPetugas tidak bisa masuk ke lokasi.\nButuh koordinasi dengan Kades/Lurah setempat segera.";
            } else {
                $keterangan_kendala = null; // Bobot 0 (Lancar) tidak ada kendala
            }

            // Update ke database
            DB::table('wilayahs')
                ->where('id_sub_sls', $w->id_sub_sls)
                ->update([
                    'selesai' => $selesai,
                    'diperiksa' => $diperiksa,
                    'bobot_kendala' => $bobot_kendala,
                    'keterangan_kendala' => $keterangan_kendala, 
                ]);
        }

        $this->command->info('Berhasil! Ratusan data simulasi (beserta log kendala) telah masuk ke database.');
    }
}