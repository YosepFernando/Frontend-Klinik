<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Training;

class TrainingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trainings = [
            [
                'judul' => 'Pelatihan Customer Service Excellence',
                'deskripsi' => 'Pelatihan untuk meningkatkan kualitas pelayanan kepada pelanggan, meliputi komunikasi efektif, penanganan keluhan, dan teknik penjualan.',
                'jenis_pelatihan' => 'video',
                'link_url' => 'https://www.youtube.com/watch?v=example1',
                'durasi' => 4,
                'is_active' => true,
            ],
            [
                'judul' => 'Panduan SOP Klinik Estetika',
                'deskripsi' => 'Dokumen lengkap berisi standar operasional prosedur untuk semua layanan di klinik estetika, termasuk protokol keamanan dan kebersihan.',
                'jenis_pelatihan' => 'document',
                'link_url' => 'https://drive.google.com/file/d/example-sop-document',
                'durasi' => 2,
                'is_active' => true,
            ],
            [
                'judul' => 'Workshop Teknik Injeksi Filler Terbaru',
                'deskripsi' => 'Pelatihan praktik langsung mengenai teknik injeksi filler terbaru untuk dokter dan beautician. Dilakukan di ruang praktik klinik.',
                'jenis_pelatihan' => 'offline',
                'konten' => 'Ruang Praktik Klinik Estetika, Lantai 2\nJl. Kesehatan No. 123, Jakarta Selatan',
                'durasi' => 6,
                'is_active' => true,
            ],
            [
                'judul' => 'Pelatihan Keselamatan Kerja dan K3',
                'deskripsi' => 'Video tutorial mengenai keselamatan dan kesehatan kerja di lingkungan klinik, termasuk penanganan bahan kimia dan alat medis.',
                'jenis_pelatihan' => 'video',
                'link_url' => 'https://www.youtube.com/watch?v=k3-safety-training',
                'durasi' => 3,
                'is_active' => true,
            ],
            [
                'judul' => 'Manual Penggunaan Alat Laser CO2',
                'deskripsi' => 'Panduan lengkap penggunaan alat laser CO2 untuk treatment kulit, termasuk setting parameter dan troubleshooting.',
                'jenis_pelatihan' => 'document',
                'link_url' => 'https://drive.google.com/file/d/laser-co2-manual',
                'durasi' => 1,
                'is_active' => true,
            ],
            [
                'judul' => 'Pelatihan Manajemen Stok dan Inventory',
                'deskripsi' => 'Pelatihan tatap muka untuk staf administrasi mengenai manajemen stok produk dan alat-alat klinik.',
                'jenis_pelatihan' => 'offline',
                'konten' => 'Ruang Meeting Utama\nKlinik Estetika, Lantai 1',
                'durasi' => 4,
                'is_active' => true,
            ],
            [
                'judul' => 'Video Tutorial: Konsultasi Pasien Efektif',
                'deskripsi' => 'Video pelatihan mengenai teknik konsultasi yang efektif dengan pasien, termasuk cara menggali kebutuhan dan memberikan rekomendasi.',
                'jenis_pelatihan' => 'video',
                'link_url' => 'https://vimeo.com/consultation-training',
                'durasi' => 2,
                'is_active' => false,
            ],
            [
                'judul' => 'Protokol Sterilisasi Alat Medis',
                'deskripsi' => 'Dokumen protokol lengkap untuk sterilisasi semua alat medis yang digunakan di klinik estetika.',
                'jenis_pelatihan' => 'document',
                'link_url' => 'https://docs.google.com/document/d/sterilization-protocol',
                'durasi' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($trainings as $training) {
            Training::create($training);
        }
    }
}
