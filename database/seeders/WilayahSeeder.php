<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('wilayahs')->truncate();

        // --- 1. MEMBACA MASTER WILAYAH (wilayah_master.csv) ---
        $masterPath = storage_path('app/wilayah_master.csv');
        $mFile = fopen($masterPath, 'r');
        $delimM = (strpos(fgets($mFile), ';') !== false) ? ';' : ',';
        rewind($mFile);
        fgetcsv($mFile, 0, $delimM); // Skip header

        $kabMap = []; $kecMap = []; $desaMap = [];
        $lastKabCode = ''; $lastKabName = '';
        $lastKecCode = ''; $lastKecName = '';

        while (($row = fgetcsv($mFile, 0, $delimM)) !== FALSE) {
            // Bersihkan spasi/anomali
            $row = array_map('trim', $row);
            
            if (!empty($row[0])) { $lastKabCode = $row[0]; $lastKabName = $row[1]; }
            if (!empty($row[2])) { $lastKecCode = $row[2]; $lastKecName = $row[3]; }
            
            $kabMap[$lastKabCode] = $lastKabName;
            $kecMap[$lastKecCode] = $lastKecName;
            if (!empty($row[4])) {
                $desaMap[$row[4]] = $row[5]; 
            }
        }
        fclose($mFile);

        // --- 2. MEMBACA DATA PROGRES (subsls.csv) ---
        $subPath = storage_path('app/subsls.csv');
        $sFile = fopen($subPath, 'r');
        $delimS = (strpos(fgets($sFile), ';') !== false) ? ';' : ',';
        rewind($sFile);
        fgetcsv($sFile, 0, $delimS); // Skip header

        $this->command->info("Menyambungkan kode wilayah BPS...");

        $count = 0;
        while (($row = fgetcsv($sFile, 0, $delimS)) !== FALSE) {
            if (empty($row) || count($row) < 10) continue;

            // Logika Gabung Kode (Padding agar formatnya seragam)
            $prov = trim($row[5]); // 16
            $kab  = str_pad(trim($row[6]), 2, "0", STR_PAD_LEFT); // 01
            $kec  = str_pad(trim($row[7]), 3, "0", STR_PAD_LEFT); // 052
            $desa = str_pad(trim($row[8]), 3, "0", STR_PAD_LEFT); // 001

            $fullKab  = $prov . $kab;        // Jadi 1601
            $fullKec  = $prov . $kab . $kec; // Jadi 1601052
            $fullDesa = $prov . $kab . $kec . $desa; // Jadi 1601052001

            DB::table('wilayahs')->insert([
                'kode_kab'      => $fullKab,
                'nama_kab'      => $kabMap[$fullKab] ?? 'Kab. ' . $fullKab,
                'kode_kec'      => $fullKec,
                'nama_kec'      => $kecMap[$fullKec] ?? 'Kecamatan ' . $fullKec,
                'id_desa'       => $fullDesa,
                'nama_desa'     => $desaMap[$fullDesa] ?? 'Desa ' . $fullDesa,
                'id_sub_sls'    => trim($row[1]),
                'nama_sls'      => trim($row[2]),
                'muatan'        => (int)($row[17] ?? 0),
                'selesai'       => (int)($row[18] ?? 0),
                'diperiksa'     => (int)($row[19] ?? 0),
                'bobot_kendala' => 0,
                'cluster_label' => 'Lancar',
                'updated_at'    => now(),
                'created_at'    => now(),
            ]);
            $count++;
        }
        fclose($sFile);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info("Berhasil! $count data sekarang sudah pakai nama asli.");
    }
}