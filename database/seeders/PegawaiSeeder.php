<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pegawai;
use App\Models\User;
use App\Models\Posisi;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all positions
        $posisiAdmin = Posisi::where('nama_posisi', 'Administrator')->first();
        $posisiHRD = Posisi::where('nama_posisi', 'HRD Manager')->first();
        $posisiFrontOffice = Posisi::where('nama_posisi', 'Front Office')->first();
        $posisiKasir = Posisi::where('nama_posisi', 'Kasir')->first();
        $posisiDokter = Posisi::where('nama_posisi', 'Dokter')->first();
        $posisiBeautician = Posisi::where('nama_posisi', 'Beautician')->first();

        // Get all users except pelanggan
        $users = User::whereIn('role', ['admin', 'hrd', 'front_office', 'kasir', 'dokter', 'beautician'])->get();

        $pegawaiData = [];

        foreach ($users as $user) {
            $posisiId = null;
            $gaji = 0;

            // Assign position based on role
            switch ($user->role) {
                case 'admin':
                    $posisiId = $posisiAdmin?->id;
                    $gaji = 8000000;
                    break;
                case 'hrd':
                    $posisiId = $posisiHRD?->id;
                    $gaji = 7000000;
                    break;
                case 'front_office':
                    $posisiId = $posisiFrontOffice?->id;
                    $gaji = 4500000;
                    break;
                case 'kasir':
                    $posisiId = $posisiKasir?->id;
                    $gaji = 4000000;
                    break;
                case 'dokter':
                    $posisiId = $posisiDokter?->id;
                    $gaji = 12000000;
                    break;
                case 'beautician':
                    $posisiId = $posisiBeautician?->id;
                    $gaji = 5000000;
                    break;
            }

            $pegawaiData[] = [
                'id_user' => $user->id,
                'id_posisi' => $posisiId,
                'NIK' => fake()->unique()->numerify('##########'),
                'nama_lengkap' => $user->name,
                'tanggal_lahir' => fake()->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'),
                'jenis_kelamin' => fake()->randomElement(['L', 'P']),
                'agama' => fake()->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']),
                'alamat' => $user->address ?? fake()->address(),
                'telepon' => $user->no_telp ?? fake()->phoneNumber(),
                'email' => $user->email,
                'tanggal_masuk' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Pegawai::insert($pegawaiData);
    }
}
