<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Posisi;

class PosisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posisi = [
            [
                'nama_posisi' => 'Admin',
                'gaji_pokok' => 5000000.00,
                'persen_bonus' => 10.00
            ],
            [
                'nama_posisi' => 'HRD',
                'gaji_pokok' => 6000000.00,
                'persen_bonus' => 12.00
            ],
            [
                'nama_posisi' => 'Front Office',
                'gaji_pokok' => 3500000.00,
                'persen_bonus' => 8.00
            ],
            [
                'nama_posisi' => 'Kasir',
                'gaji_pokok' => 3000000.00,
                'persen_bonus' => 7.00
            ],
            [
                'nama_posisi' => 'Dokter',
                'gaji_pokok' => 8000000.00,
                'persen_bonus' => 15.00
            ],
            [
                'nama_posisi' => 'Beautician',
                'gaji_pokok' => 4000000.00,
                'persen_bonus' => 10.00
            ]
        ];

        foreach ($posisi as $pos) {
            Posisi::create($pos);
        }
    }
}
