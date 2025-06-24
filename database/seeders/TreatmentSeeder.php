<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TreatmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $treatments = [
            [
                'name' => 'Facial Basic',
                'description' => 'Basic facial treatment for all skin types',
                'price' => 150000,
                'duration_minutes' => 60,
                'category' => 'beauty',
                'is_active' => true,
            ],
            [
                'name' => 'Facial Premium',
                'description' => 'Premium facial with advanced treatment',
                'price' => 300000,
                'duration_minutes' => 90,
                'category' => 'beauty',
                'is_active' => true,
            ],
            [
                'name' => 'Medical Checkup',
                'description' => 'General medical consultation',
                'price' => 200000,
                'duration_minutes' => 30,
                'category' => 'medical',
                'is_active' => true,
            ],
            [
                'name' => 'Body Massage',
                'description' => 'Relaxing full body massage',
                'price' => 250000,
                'duration_minutes' => 60,
                'category' => 'wellness',
                'is_active' => true,
            ],
            [
                'name' => 'Acne Treatment',
                'description' => 'Specialized acne treatment',
                'price' => 180000,
                'duration_minutes' => 45,
                'category' => 'medical',
                'is_active' => true,
            ],
        ];

        foreach ($treatments as $treatment) {
            \App\Models\Treatment::create($treatment);
        }
    }
}
