<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            if (!Schema::hasColumn('barangs', 'jumlah_diperbaiki')) {
                $table->integer('jumlah_diperbaiki')
                      ->default(0)
                      ->after('jumlah_rusak_berat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            if (Schema::hasColumn('barangs', 'jumlah_diperbaiki')) {
                $table->dropColumn('jumlah_diperbaiki');
            }
        });
    }
};
