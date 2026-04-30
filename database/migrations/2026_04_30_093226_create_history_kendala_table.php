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
        ;
        Schema::create('history_kendala', function (Blueprint $table) {
            $table->id();
            $table->string('id_subsls'); 
            $table->integer('selesai');    
            $table->integer('diperiksa');  
            $table->integer('muatan');    
            $table->text('keterangan_kendala')->nullable();
            $table->integer('bobot_kendala'); 
            $table->string('cluster_label'); 
            $table->date('tanggal_catat');   
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_kendala');
    }
};
