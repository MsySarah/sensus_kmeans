<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Buat Tabel Kecamatan dulu
        Schema::create('kecamatans', function (Blueprint $table) {
            $table->string('id_kec', 7)->primary(); // Kode BPS
            $table->string('nama_kec');
            $table->timestamps();
        });

        // 2. Buat Tabel Desa/Kelurahan
        Schema::create('desas', function (Blueprint $table) {
            $table->string('id_desa', 10)->primary(); // Kode BPS
            $table->string('id_kec', 7);
            $table->string('nama_desa');
            $table->timestamps();
            
            // Relasi: 1 Kecamatan punya banyak Desa
            $table->foreign('id_kec')->references('id_kec')->on('kecamatans')->onDelete('cascade');
        });

        // 3. Modifikasi Tabel Wilayahs (Ini Sub-SLS/RT yang asli)
        Schema::create('wilayahs', function (Blueprint $table) {
            $table->string('id_sub_sls', 16)->primary(); // Kode untuk diketik di Telegram
            $table->string('id_desa', 10)->nullable(); // Relasi ke tabel desas
            $table->string('nama_sls');
            $table->integer('muatan')->default(0);
            
            // --- TAMBAHAN KOLOM UNTUK K-MEANS ---
            $table->integer('selesai')->default(0);
            $table->integer('diperiksa')->default(0);
            $table->integer('bobot_kendala')->default(0); // Angka Max-Rule
            $table->string('cluster_label')->default('Lancar'); // Hasil Python
            $table->integer('cluster_id')->default(1);
            
            $table->timestamps();

            // Relasi: 1 Desa punya banyak RT/SubSLS
            $table->foreign('id_desa')->references('id_desa')->on('desas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus dari yang paling bawah (anak) ke atas (induk) biar ga error relasi
        Schema::dropIfExists('wilayahs');
        Schema::dropIfExists('desas');
        Schema::dropIfExists('kecamatans');
    }
};