<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PeminjamanPermissionSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk permission peminjaman.
     */
    public function run(): void
    {
        // Daftar permission untuk fitur peminjaman
        $permissions = [
            'view peminjaman',
            'manage peminjaman',
            'delete peminjaman',
        ];

        // Pastikan semua permission sudah ada
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Ambil role yang ada
        $adminRole   = Role::where('name', 'admin')->first();
        $petugasRole = Role::where('name', 'petugas')->first();
        $userRole    = Role::where('name', 'user')->first();

        // Berikan semua akses untuk admin
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        // Petugas boleh lihat & kelola, tapi tidak hapus
        if ($petugasRole) {
            $petugasRole->givePermissionTo([
                'view peminjaman',
                'manage peminjaman',
            ]);
        }

        // User hanya bisa lihat
        if ($userRole) {
            $userRole->givePermissionTo(['view peminjaman']);
        }
    }
}
