<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk membuat tabel cache dan cache_locks.
     */
    public function up(): void
    {
        // Membuat tabel cache untuk menyimpan data yang di-cache
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary(); // Kunci unik untuk data cache
            $table->mediumText('value'); // Nilai cache dalam format teks menengah
            $table->integer('expiration'); // Waktu kedaluwarsa cache dalam bentuk timestamp
        });

        // Membuat tabel cache_locks untuk mekanisme penguncian cache
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary(); // Kunci unik untuk penguncian cache
            $table->string('owner'); // Pemilik kunci (misalnya, ID proses)
            $table->integer('expiration'); // Waktu kedaluwarsa kunci dalam bentuk timestamp
        });
    }

    /**
     * Reverse the migrations (menghapus tabel cache dan cache_locks).
     */
    public function down(): void
    {
        Schema::dropIfExists('cache'); // Hapus tabel cache jika ada
        Schema::dropIfExists('cache_locks'); // Hapus tabel cache_locks jika ada
    }
};
