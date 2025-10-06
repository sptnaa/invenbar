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
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->string('nama_peminjam')->after('id');
            $table->string('email_peminjam')->nullable()->after('nama_peminjam');
            $table->string('telepon_peminjam')->nullable()->after('email_peminjam');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropColumn(['nama_peminjam', 'email_peminjam', 'telepon_peminjam']);
        });
    }
};
