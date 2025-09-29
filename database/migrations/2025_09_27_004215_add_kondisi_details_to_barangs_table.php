<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->integer('jumlah_baik')->default(0)->after('jumlah');
            $table->integer('jumlah_rusak_ringan')->default(0)->after('jumlah_baik');
            $table->integer('jumlah_rusak_berat')->default(0)->after('jumlah_rusak_ringan');
        });
    }

    public function down()
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropColumn(['jumlah_baik', 'jumlah_rusak_ringan', 'jumlah_rusak_berat']);
        });
    }
};