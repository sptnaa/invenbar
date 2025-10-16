<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangSeeder extends Seeder
{
    public function run(): void
    {
        // Barang induk (parent)
        $parent = [
            'kode_barang' => 'BTP01',
            'kode_dasar' => null, // tidak punya induk
            'nama_barang' => 'Handphone',
            'kategori_id' => 1,
            'lokasi_id' => 2,
            'jumlah_baik' => 1, // induk hanya 1 unit
            'jumlah_rusak_ringan' => 0,
            'jumlah_rusak_berat' => 0,
            'satuan' => 'Unit',
            'kondisi' => 'Baik',
            'tanggal_pengadaan' => '2024-07-10',
            'is_pinjaman' => true,
            'mode_input' => 'unit',
            'gambar' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('barangs')->insert($parent);

        // Barang anak (2 unit dari barang induk)
        $children = [
            [
                'kode_barang' => 'BTP02',
                'kode_dasar' => 'BTP01', // induknya BTP01
                'nama_barang' => 'Handphone',
                'kategori_id' => 1,
                'lokasi_id' => 2,
                'jumlah_baik' => 1,
                'jumlah_rusak_ringan' => 0,
                'jumlah_rusak_berat' => 0,
                'satuan' => 'Unit',
                'kondisi' => 'Baik',
                'tanggal_pengadaan' => '2024-07-10',
                'is_pinjaman' => true,
                'mode_input' => 'unit',
                'gambar' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_barang' => 'BTP03',
                'kode_dasar' => 'BTP01', // induknya sama
                'nama_barang' => 'Handphone',
                'kategori_id' => 1,
                'lokasi_id' => 2,
                'jumlah_baik' => 1,
                'jumlah_rusak_ringan' => 0,
                'jumlah_rusak_berat' => 0,
                'satuan' => 'Unit',
                'kondisi' => 'Baik',
                'tanggal_pengadaan' => '2024-07-10',
                'is_pinjaman' => true,
                'mode_input' => 'unit',
                'gambar' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('barangs')->insert($children);

        // Barang lain (contoh mode masal)
        DB::table('barangs')->insert([
            'kode_barang' => 'LP001',
            'kode_dasar' => null,
            'nama_barang' => 'Laptop Dell Latitude 5420',
            'kategori_id' => 1,
            'lokasi_id' => 4,
            'jumlah_baik' => 5,
            'jumlah_rusak_ringan' => 0,
            'jumlah_rusak_berat' => 0,
            'satuan' => 'Unit',
            'kondisi' => 'Baik',
            'tanggal_pengadaan' => '2023-05-15',
            'is_pinjaman' => true,
            'mode_input' => 'masal',
            'gambar' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
