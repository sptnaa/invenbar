<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        // Bersihkan cache permission lama
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // === BARANG ===
        Permission::firstOrCreate(['name' => 'manage barang']);
        Permission::firstOrCreate(['name' => 'delete barang']);

        // === KATEGORI ===
        Permission::firstOrCreate(['name' => 'view kategori']);
        Permission::firstOrCreate(['name' => 'manage kategori']);

        // === LOKASI ===
        Permission::firstOrCreate(['name' => 'view lokasi']);
        Permission::firstOrCreate(['name' => 'manage lokasi']);

        // === PEMINJAMAN ===
        Permission::firstOrCreate(['name' => 'view peminjaman']);
        Permission::firstOrCreate(['name' => 'manage peminjaman']);
        Permission::firstOrCreate(['name' => 'delete peminjaman']);

        // === PERBAIKAN ===
        Permission::firstOrCreate(['name' => 'view perbaikan']);
        Permission::firstOrCreate(['name' => 'manage perbaikan']);
        Permission::firstOrCreate(['name' => 'delete perbaikan']);

        // === ROLE ===
        $petugasRole = Role::firstOrCreate(['name' => 'petugas']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Hak akses Petugas
        $petugasRole->syncPermissions([
            'manage barang',
            'view kategori',
            'manage kategori', 
            'view lokasi',
            'view peminjaman',
            'manage peminjaman',
            'view perbaikan',
            'manage perbaikan',
        ]);

        // Hak akses Admin = semua permission
        $adminRole->syncPermissions(Permission::all());
    }
}
