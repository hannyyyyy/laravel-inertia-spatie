<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk membuat tabel database.
     */
    public function up(): void
    {
        // Membuat tabel users untuk menyimpan data pengguna
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Primary key dengan auto-increment
            $table->string('name'); // Nama pengguna
            $table->string('email')->unique(); // Email unik untuk setiap pengguna
            $table->timestamp('email_verified_at')->nullable(); // Waktu verifikasi email (opsional)
            $table->string('password'); // Password pengguna
            $table->rememberToken(); // Token untuk "remember me"
            $table->timestamps(); // Kolom created_at dan updated_at
        });

        // Membuat tabel password_reset_tokens untuk menyimpan token reset password
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary(); // Email sebagai primary key
            $table->string('token'); // Token reset password
            $table->timestamp('created_at')->nullable(); // Waktu pembuatan token
        });

        // Membuat tabel sessions untuk menyimpan sesi pengguna
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // ID sesi sebagai primary key
            $table->foreignId('user_id')->nullable()->index(); // ID pengguna (opsional)
            $table->string('ip_address', 45)->nullable(); // Alamat IP pengguna
            $table->text('user_agent')->nullable(); // Informasi perangkat pengguna
            $table->longText('payload'); // Data sesi pengguna
            $table->integer('last_activity')->index(); // Waktu aktivitas terakhir
        });
    }

    /**
     * Reverse the migrations (menghapus tabel yang dibuat).
     */
    public function down(): void
    {
        Schema::dropIfExists('users'); // Hapus tabel users
        Schema::dropIfExists('password_reset_tokens'); // Hapus tabel password_reset_tokens
        Schema::dropIfExists('sessions'); // Hapus tabel sessions
    }
};
