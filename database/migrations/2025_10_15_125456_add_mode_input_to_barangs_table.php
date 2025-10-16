<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            // 🔹 Tambah kolom hanya jika belum ada (supaya tidak error duplikat)
            if (!Schema::hasColumn('barangs', 'mode_input')) {
                $table->enum('mode_input', ['masal', 'unit'])
                    ->default('masal')
                    ->after('is_pinjaman');
            }

            if (!Schema::hasColumn('barangs', 'kode_dasar')) {
                $table->string('kode_dasar', 50)
                    ->nullable()
                    ->after('mode_input');
                $table->index('kode_dasar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            if (Schema::hasColumn('barangs', 'kode_dasar')) {
                $table->dropIndex(['kode_dasar']);
                $table->dropColumn('kode_dasar');
            }

            if (Schema::hasColumn('barangs', 'mode_input')) {
                $table->dropColumn('mode_input');
            }
        });
    }
};
