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
        Schema::table('wilayahs', function (Blueprint $table) {
            $table->text('keterangan_kendala')->nullable()->after('bobot_kendala');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wilayahs', function (Blueprint $table) {
            //
        });
    }
};
