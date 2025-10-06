<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perbaikans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_perbaikan', 50)->unique();

            // Relasi barang - wajib ada
            $table->foreignId('barang_id')
                  ->constrained('barangs')
                  ->onDelete('cascade');

            // Relasi peminjaman - boleh null dan aman dihapus
            $table->foreignId('peminjaman_id')
                  ->nullable()
                  ->constrained('peminjamans')
                  ->onDelete('set null');

            $table->integer('jumlah_rusak');
            $table->enum('tingkat_kerusakan', ['Rusak Ringan', 'Rusak Berat']);
            $table->text('keterangan_kerusakan')->nullable();
            $table->date('tanggal_masuk');
            $table->date('tanggal_selesai')->nullable();
            $table->enum('status', ['Menunggu', 'Dalam Perbaikan', 'Selesai'])->default('Menunggu');
            $table->text('catatan_perbaikan')->nullable();
            $table->decimal('biaya_perbaikan', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perbaikans');
    }
};
