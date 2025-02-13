<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk membuat tabel antrian pekerjaan (jobs).
     */
    public function up(): void
    {
        // Membuat tabel jobs untuk menyimpan daftar pekerjaan yang akan dieksekusi oleh queue worker
        Schema::create('jobs', function (Blueprint $table) {
            $table->id(); // Primary key dengan auto-increment
            $table->string('queue')->index(); // Nama antrian pekerjaan (diindeks untuk performa)
            $table->longText('payload'); // Data pekerjaan yang akan dieksekusi
            $table->unsignedTinyInteger('attempts'); // Jumlah percobaan eksekusi pekerjaan
            $table->unsignedInteger('reserved_at')->nullable(); // Waktu pekerjaan dipesan untuk dieksekusi
            $table->unsignedInteger('available_at'); // Waktu pekerjaan tersedia untuk dieksekusi
            $table->unsignedInteger('created_at'); // Waktu pekerjaan dibuat
        });

        // Membuat tabel job_batches untuk menyimpan informasi batch job (pekerjaan yang dijalankan secara bersamaan)
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary(); // Primary key unik untuk batch pekerjaan
            $table->string('name'); // Nama batch pekerjaan
            $table->integer('total_jobs'); // Total pekerjaan dalam batch ini
            $table->integer('pending_jobs'); // Jumlah pekerjaan yang belum selesai
            $table->integer('failed_jobs'); // Jumlah pekerjaan yang gagal
            $table->longText('failed_job_ids'); // ID pekerjaan yang gagal
            $table->mediumText('options')->nullable(); // Opsi tambahan terkait batch
            $table->integer('cancelled_at')->nullable(); // Waktu batch dibatalkan
            $table->integer('created_at'); // Waktu batch dibuat
            $table->integer('finished_at')->nullable(); // Waktu batch selesai
        });

        // Membuat tabel failed_jobs untuk menyimpan pekerjaan yang gagal dieksekusi
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id(); // Primary key dengan auto-increment
            $table->string('uuid')->unique(); // UUID unik untuk pekerjaan yang gagal
            $table->text('connection'); // Nama koneksi yang digunakan untuk pekerjaan
            $table->text('queue'); // Nama antrian pekerjaan
            $table->longText('payload'); // Data pekerjaan yang gagal
            $table->longText('exception'); // Detail exception yang menyebabkan kegagalan
            $table->timestamp('failed_at')->useCurrent(); // Waktu pekerjaan gagal dieksekusi
        });
    }

    /**
     * Reverse the migrations (menghapus tabel terkait antrian pekerjaan).
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs'); // Hapus tabel jobs jika ada
        Schema::dropIfExists('job_batches'); // Hapus tabel job_batches jika ada
        Schema::dropIfExists('failed_jobs'); // Hapus tabel failed_jobs jika ada
    }
};
