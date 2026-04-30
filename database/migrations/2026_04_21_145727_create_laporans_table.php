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
        Schema::create('laporans', function (Blueprint $table) {
            $table->id();
            $table->string('id_sub_sls'); // Untuk nyambungin ke tabel wilayah
            $table->integer('selesai');
            $table->integer('diperiksa');
            $table->integer('bobot_kendala'); // Diisi angka 0, 1, 2, atau 3
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};
