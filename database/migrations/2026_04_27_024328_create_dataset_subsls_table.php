<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dataset_subsls', function (Blueprint $table) {
            $table->id();
            $table->string('id_subsls')->unique(); // ID unik dari BPS
            $table->string('nama_sls');
            $table->string('nama_ketua_sls')->nullable();
            $table->string('jenis')->nullable();
            $table->string('kode_kec');
            $table->string('kode_desa');
            $table->string('kode_sls');
            $table->integer('jumlah_kk')->default(0);
            $table->integer('jumlah_muatan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dataset_subsls');
    }
};
