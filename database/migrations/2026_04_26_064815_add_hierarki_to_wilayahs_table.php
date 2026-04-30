<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wilayahs', function (Blueprint $table) {
            // Kita taruh di paling awal tabel saja biar rapi urutan kodenya
            $table->string('kode_kab', 4)->first()->nullable();
            $table->string('nama_kab')->after('kode_kab')->nullable();
            $table->string('kode_kec', 7)->after('nama_kab')->nullable();
            $table->string('nama_kec')->after('kode_kec')->nullable();
            $table->string('nama_desa')->after('id_desa')->nullable(); // Pastikan id_desa ada di tabel
        });
    }

    public function down(): void
    {
        Schema::table('wilayahs', function (Blueprint $table) {
            $table->dropColumn(['kode_kab', 'nama_kab', 'kode_kec', 'nama_kec', 'nama_desa']);
        });
    }
};
