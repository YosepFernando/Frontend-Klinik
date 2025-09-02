<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        \App\Models\User::create([
            'name' => 'Admin Klinik',
            'email' => 'admin@klinik.com',
            'no_telp' => '08123456789',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Front Office
        \App\Models\User::create([
            'name' => 'Front Office',
            'email' => 'frontoffice@klinik.com',
            'no_telp' => '08123456790',
            'password' => bcrypt('password'),
            'role' => 'front_office',
        ]);

        // Dokter
        \App\Models\User::create([
            'name' => 'Dr. John Doe',
            'email' => 'dokter@klinik.com',
            'no_telp' => '08123456791',
            'password' => bcrypt('password'),
            'role' => 'dokter',
        ]);

        // Beautician
        \App\Models\User::create([
            'name' => 'Beauty Expert',
            'email' => 'beautician@klinik.com',
            'no_telp' => '08123456792',
            'password' => bcrypt('password'),
            'role' => 'beautician',
        ]);

        // Kasir
        \App\Models\User::create([
            'name' => 'Kasir Klinik',
            'email' => 'kasir@klinik.com',
            'no_telp' => '08123456793',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        // HRD
        \App\Models\User::create([
            'name' => 'HRD Manager',
            'email' => 'hrd@klinik.com',
            'no_telp' => '08123456794',
            'password' => bcrypt('password'),
            'role' => 'hrd',
        ]);

        // Pelanggan
        \App\Models\User::create([
            'name' => 'Customer One',
            'email' => 'customer@klinik.com',
            'no_telp' => '08123456795',
            'password' => bcrypt('password'),
            'role' => 'pelanggan',
        ]);
    }
}
