<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RolePermissionSeeder::class,
            KategoriSeeder::class,
            LokasiSeeder::class,
            BarangSeeder::class,
        ]);
        
        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@email.com',
        ]);

        $petugas = User::factory()->create([
            'name' => 'Petugas Inventaris',
            'email' => 'petugas@email.com',
        ]);

        $admin->assignRole('admin');
        $petugas->assignRole('petugas');
    }
}
