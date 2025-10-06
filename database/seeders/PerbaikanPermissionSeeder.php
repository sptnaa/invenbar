<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PerbaikanPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat permission baru khusus modul perbaikan
        $permissions = [
            'view perbaikan',
            'manage perbaikan',
            'delete perbaikan',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Ambil role
        $adminRole = Role::where('name', 'admin')->first();
        $petugasRole = Role::where('name', 'petugas')->first();
        $userRole = Role::where('name', 'user')->first();

        // Admin dan Petugas bisa manage perbaikan
        if ($adminRole) {
            $adminRole->givePermissionTo(['view perbaikan', 'manage perbaikan', 'delete perbaikan']);
        }

        if ($petugasRole) {
            $petugasRole->givePermissionTo(['view perbaikan', 'manage perbaikan']);
        }

        // User hanya bisa view
        if ($userRole) {
            $userRole->givePermissionTo(['view perbaikan']);
        }
    }
}
