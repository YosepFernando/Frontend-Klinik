<?php

// Simulate the controller filtering logic with our fix

// Mock API response (real structure from actual API)
$hasilSeleksiResponse = [
    'status' => 'success',
    'data' => [
        'data' => [
            [
                'id_hasil_seleksi' => 6,
                'id_user' => 11,
                'id_lamaran_pekerjaan' => 7,
                'status' => 'diterima',
                'catatan' => 'Keputusan: Diterima. Mulai kerja: 2025-08-25',
                'user' => [
                    'id_user' => 11,
                    'nama_user' => 'adhim',
                    'email' => 'adhim@gmail.com',
                    'role' => 'kasir'
                ],
                'lamaran_pekerjaan' => [
                    'id_lamaran_pekerjaan' => 7,
                    'id_lowongan_pekerjaan' => 3,
                    'id_user' => 11,
                    'nama_pelamar' => 'adhim@gmail.com',
                    'status' => 'diterima',
                    'lowongan_pekerjaan' => [
                        'id_lowongan_pekerjaan' => 3,
                        'judul_pekerjaan' => 'Kasir - Part Time/Full Time',
                        'id_posisi' => 5,
                        'status' => 'aktif'
                    ]
                ]
            ],
            [
                'id_hasil_seleksi' => 3,
                'id_user' => 10,
                'id_lamaran_pekerjaan' => 5,
                'status' => 'pending',
                'catatan' => 'Lulus tes praktik kasir dan wawancara. Siap untuk onboarding.',
                'user' => [
                    'id_user' => 10,
                    'nama_user' => 'Sinta Pelanggan',
                    'email' => 'pelanggan2@gmail.com',
                    'role' => 'pelanggan'
                ],
                'lamaran_pekerjaan' => [
                    'id_lamaran_pekerjaan' => 5,
                    'id_lowongan_pekerjaan' => 3,
                    'id_user' => 9,
                    'nama_pelamar' => 'Rudi Hermawan',
                    'status' => 'diterima',
                    'lowongan_pekerjaan' => [
                        'id_lowongan_pekerjaan' => 3,
                        'judul_pekerjaan' => 'Kasir - Part Time/Full Time',
                        'id_posisi' => 5,
                        'status' => 'aktif'
                    ]
                ]
            ]
        ]
    ]
];

$id = 3; // Target recruitment ID

echo "=== TESTING CONTROLLER FILTERING LOGIC ===\n\n";

if (isset($hasilSeleksiResponse['status']) && $hasilSeleksiResponse['status'] === 'success') {
    $hasilSeleksiData = $hasilSeleksiResponse['data']['data'] ?? [];
    echo "Found " . count($hasilSeleksiData) . " final applications for recruitment {$id}\n\n";
    
    $finalApplications = collect($hasilSeleksiData)->filter(function($hasilSeleksi) use ($id) {
        // Debug: Log raw data dari API
        echo "Processing hasil seleksi raw data:\n";
        echo "  - id_hasil_seleksi: " . ($hasilSeleksi['id_hasil_seleksi'] ?? 'missing') . "\n";
        echo "  - id_user: " . ($hasilSeleksi['id_user'] ?? 'missing') . "\n";
        echo "  - status: " . ($hasilSeleksi['status'] ?? 'missing') . "\n";
        echo "  - lamaran_data_present: " . (isset($hasilSeleksi['lamaran_pekerjaan']) ? 'yes' : 'no') . "\n";
        echo "  - lowongan_id_in_lamaran: " . ($hasilSeleksi['lamaran_pekerjaan']['id_lowongan_pekerjaan'] ?? 'missing') . "\n";
        
        // PERBAIKAN: Ambil lowongan ID dari path yang benar
        // API struktur: hasil_seleksi.lamaran_pekerjaan.lowongan_pekerjaan.id_lowongan_pekerjaan
        $hasilLowonganId = null;
        
        if (isset($hasilSeleksi['lamaran_pekerjaan']['lowongan_pekerjaan']['id_lowongan_pekerjaan'])) {
            $hasilLowonganId = $hasilSeleksi['lamaran_pekerjaan']['lowongan_pekerjaan']['id_lowongan_pekerjaan'];
        } elseif (isset($hasilSeleksi['lamaran_pekerjaan']['id_lowongan_pekerjaan'])) {
            // Fallback ke direct field dalam lamaran_pekerjaan
            $hasilLowonganId = $hasilSeleksi['lamaran_pekerjaan']['id_lowongan_pekerjaan'];
        }
        
        echo "  - extracted lowongan ID: {$hasilLowonganId}\n";
        
        if (empty($hasilLowonganId) || $hasilLowonganId != $id) {
            echo "  - RESULT: FILTERED OUT (ID {$hasilLowonganId} != target {$id})\n\n";
            return false;
        }
        
        // Pastikan ada ID hasil seleksi yang valid
        if (!isset($hasilSeleksi['id_hasil_seleksi']) || !$hasilSeleksi['id_hasil_seleksi']) {
            echo "  - RESULT: FILTERED OUT (invalid hasil seleksi ID)\n\n";
            return false;
        }
        
        echo "  - RESULT: PASSED FILTER\n\n";
        return true;
    });
    
    echo "=== FILTER RESULTS ===\n";
    echo "Original count: " . count($hasilSeleksiData) . "\n";
    echo "Filtered count: " . $finalApplications->count() . "\n";
    
    if ($finalApplications->count() > 0) {
        echo "\nPassed applications:\n";
        foreach ($finalApplications as $app) {
            echo "- Hasil Seleksi ID: " . $app['id_hasil_seleksi'] . 
                 ", User: " . $app['user']['nama_user'] . 
                 ", Status: " . $app['status'] . "\n";
        }
        echo "\n✅ SUCCESS: Applications would be displayed in the 'Hasil Seleksi' tab!\n";
    } else {
        echo "\n❌ ERROR: No applications would be displayed!\n";
    }
} else {
    echo "❌ API response error\n";
}

echo "\n=== Test completed ===\n";

// Helper function to mock Laravel's collect()
function collect($items = []) {
    return new class($items) {
        private $items;
        
        public function __construct($items) {
            $this->items = is_array($items) ? $items : [$items];
        }
        
        public function filter($callback) {
            $filtered = array_filter($this->items, $callback);
            return new self(array_values($filtered));
        }
        
        public function count() {
            return count($this->items);
        }
        
        public function getIterator() {
            return new ArrayIterator($this->items);
        }
    };
}
