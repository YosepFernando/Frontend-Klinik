<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Absensi;
use App\Models\User;
use App\Models\Pegawai;
use Carbon\Carbon;

class AbsensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all employees (users with roles other than pelanggan)
        $users = User::whereIn('role', ['admin', 'hrd', 'front_office', 'kasir', 'dokter', 'beautician'])->get();

        $absensiData = [];

        // Create attendance data for the last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            
            // Skip weekends (Saturday and Sunday)
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($users as $user) {
                $pegawai = Pegawai::where('id_user', $user->id)->first();
                
                // Skip if no pegawai record found
                if (!$pegawai) continue;
                
                // Random chance of attendance (90% attendance rate)
                if (fake()->numberBetween(1, 100) <= 90) {
                    $jamMasuk = Carbon::createFromTime(8, 0, 0)->addMinutes(fake()->numberBetween(-30, 120));
                    $jamKeluar = Carbon::createFromTime(17, 0, 0)->addMinutes(fake()->numberBetween(-60, 60));
                    
                    $absensiData[] = [
                        'id_pegawai' => $pegawai->id_pegawai,
                        'tanggal' => $date->format('Y-m-d'),
                        'jam_masuk' => $date->format('Y-m-d') . ' ' . $jamMasuk->format('H:i:s'),
                        'jam_keluar' => $date->format('Y-m-d') . ' ' . $jamKeluar->format('H:i:s'),
                        'latitude_masuk' => -8.781952 + fake()->randomFloat(6, -0.001, 0.001),
                        'longitude_masuk' => 115.179793 + fake()->randomFloat(6, -0.001, 0.001),
                        'latitude_keluar' => -8.781952 + fake()->randomFloat(6, -0.001, 0.001),
                        'longitude_keluar' => 115.179793 + fake()->randomFloat(6, -0.001, 0.001),
                        'alamat_masuk' => 'Klinik Kecantikan AES, Denpasar, Bali',
                        'alamat_keluar' => 'Klinik Kecantikan AES, Denpasar, Bali',
                        'catatan' => $jamMasuk->format('H:i') > '08:00' ? 'Terlambat karena macet' : 'Hadir tepat waktu',
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                } else {
                    // 10% chance of absence (no record - just skip)
                    continue;
                }
            }
        }

        // Insert data in chunks to avoid memory issues
        collect($absensiData)->chunk(100)->each(function ($chunk) {
            Absensi::insert($chunk->toArray());
        });
    }
}
