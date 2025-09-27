<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PeminjamanPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for peminjaman
        $permissions = [
            'view peminjaman',
            'manage peminjaman',
            'delete peminjaman',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();

        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        if ($userRole) {
            $userRole->givePermissionTo(['view peminjaman']);
        }
    }
}