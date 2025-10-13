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
        // Drop tabel peminjaman (singular) yang tidak terpakai
        Schema::dropIfExists('peminjaman');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore tabel jika rollback (opsional)
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->string('nama_peminjam')->nullable();
            $table->string('email_peminjam')->nullable();
            $table->string('telepon_peminjam')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('barang_id')->nullable();
            $table->integer('jumlah_pinjam')->default(1);
            $table->dateTime('tanggal_pinjam');
            $table->dateTime('tanggal_kembali_rencana')->nullable();
            $table->dateTime('tanggal_kembali_aktual')->nullable();
            $table->date('tanggal_kembali')->nullable();
            $table->string('status')->default('dipinjam');
            $table->enum('kondisi_barang', ['Baik', 'Rusak Ringan', 'Rusak Berat'])->nullable();
            $table->text('keperluan')->nullable();
            $table->string('nomor_transaksi')->unique()->nullable();
            $table->timestamps();
        });
    }
};