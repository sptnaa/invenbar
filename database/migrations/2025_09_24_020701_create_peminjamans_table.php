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
        Schema::create('peminjamans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi', 50)->unique();
            $table->string('nama_peminjam', 100);
            $table->string('email_peminjam', 100)->nullable();
            $table->string('telepon_peminjam', 20)->nullable();
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            $table->integer('jumlah_pinjam');
            $table->dateTime('tanggal_pinjam');
            $table->dateTime('tanggal_kembali_rencana');
            $table->dateTime('tanggal_kembali_aktual')->nullable();
            $table->enum('status', ['Sedang Dipinjam', 'Sudah Dikembalikan', 'Terlambat'])->default('Sedang Dipinjam');
            $table->text('keperluan')->nullable();
            $table->text('keterangan')->nullable();
            $table->integer('denda')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjamans');
    }
};
