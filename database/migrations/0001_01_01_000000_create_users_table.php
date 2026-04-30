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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique(); 
            $table->string('password');
            $table->enum('role', ['admin', 'pimpinan', 'pml', 'koseka', 'pengawas_kab', 'petugas'])->default('petugas');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // --- TAMBAHAN KITA: Tabel Relasi User & Wilayah ---
        // Tabel ini untuk nyimpen "Klaim Wilayah" milik Koseka/PML
        Schema::create('user_wilayah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Tipe data ini mengikuti tabel wilayah yang kita buat sebelumnya
            $table->string('id_kec', 7)->nullable(); 
            $table->string('id_desa', 10)->nullable();
            
            $table->timestamps();
        });
        // ---------------------------------------------------
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tabel anak (user_wilayah) dulu sebelum drop tabel induk (users) biar ga error
        Schema::dropIfExists('user_wilayah'); 
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};