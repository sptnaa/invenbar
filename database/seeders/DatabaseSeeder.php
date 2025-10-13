<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Lokasi;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Jalankan seeder lain terlebih dahulu
        $this->call([
            RolePermissionSeeder::class,
            KategoriSeeder::class,
            LokasiSeeder::class,
            BarangSeeder::class,
        ]);

        // =====================================================
        // ADMIN
        // =====================================================
        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@email.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        // =====================================================
        // PETUGAS BERDASARKAN LOKASI
        // =====================================================
        $lokasiGudang = Lokasi::where('nama_lokasi', 'Gudang Arsip')->first();
        $lokasiLobi   = Lokasi::where('nama_lokasi', 'Lobi Depan')->first();
        $lokasiKepala = Lokasi::where('nama_lokasi', 'Ruang Kepala Dinas')->first();
        $lokasiRapat  = Lokasi::where('nama_lokasi', 'Ruang Rapat Utama')->first();

        // Daftar petugas sesuai lokasi
        $petugasList = [
            [
                'name'  => 'Petugas Inventaris',
                'email' => 'petugas1g@email.com',
                'lokasi' => $lokasiGudang,
            ],
            [
                'name'  => 'Petugas Inventaris',
                'email' => 'petugas2@email.com',
                'lokasi' => $lokasiLobi,
            ],
            [
                'name'  => 'Petugas Inventaris',
                'email' => 'petugas3@email.com',
                'lokasi' => $lokasiKepala,
            ],
            [
                'name'  => 'Petugas Inventaris',
                'email' => 'petugas4@email.com',
                'lokasi' => $lokasiRapat,
            ],
        ];

        // Buat user petugas berdasarkan daftar di atas
        foreach ($petugasList as $data) {
            if ($data['lokasi']) {
                $user = User::factory()->create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => bcrypt('password'),
                    'lokasi_id' => $data['lokasi']->id,
                ]);
                $user->assignRole('petugas');
            }
        }
    }
}
