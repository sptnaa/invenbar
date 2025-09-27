<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('peminjamen', function (Blueprint $table) {
            $table->string('nomor_transaksi')->unique()->after('keperluan');
        });
    }

    public function down()
    {
        Schema::table('peminjamen', function (Blueprint $table) {
            $table->dropColumn('nomor_transaksi');
        });
    }
};
