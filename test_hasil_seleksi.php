<?php

// Test script untuk menguji data hasil seleksi
echo "=== TEST API HASIL SELEKSI ===\n\n";

// Test 1: API Response
echo "1. Testing API Response:\n";
$apiUrl = "http://127.0.0.1:8002/api/public/hasil-seleksi?id_lowongan_pekerjaan=3";
$response = @file_get_contents($apiUrl);

if ($response === false) {
    echo "❌ GAGAL: Tidak bisa mengakses API {$apiUrl}\n";
    echo "   Pastikan API server berjalan di port 8002\n\n";
} else {
    $data = json_decode($response, true);
    if (isset($data['status']) && $data['status'] === 'success') {
        $count = count($data['data']['data']);
        echo "✅ SUKSES: API mengembalikan {$count} data hasil seleksi\n";
        
        echo "\n2. Detail Data yang Diterima:\n";
        foreach ($data['data']['data'] as $index => $item) {
            echo "   Data #" . ($index + 1) . ":\n";
            echo "   - ID Hasil Seleksi: " . ($item['id_hasil_seleksi'] ?? 'N/A') . "\n";
            echo "   - ID User: " . ($item['id_user'] ?? 'N/A') . "\n";
            echo "   - Status: " . ($item['status'] ?? 'N/A') . "\n";
            echo "   - Nama User: " . ($item['user']['nama_user'] ?? 'N/A') . "\n";
            echo "   - ID Lowongan: " . ($item['lamaran_pekerjaan']['id_lowongan_pekerjaan'] ?? 'N/A') . "\n";
            echo "   - Nama Pelamar: " . ($item['lamaran_pekerjaan']['nama_pelamar'] ?? 'N/A') . "\n";
            echo "\n";
        }
    } else {
        echo "❌ GAGAL: API mengembalikan error: " . ($data['message'] ?? 'Unknown error') . "\n\n";
    }
}

// Test 2: Controller Logic Simulation
echo "3. Testing Controller Logic:\n";
echo "   Simulasi mapping status:\n";

function mapFinalStatus($statusHasilSeleksi) {
    switch($statusHasilSeleksi) {
        case 'diterima':
            return 'accepted';
        case 'ditolak':
            return 'rejected';
        case 'pending':
        default:
            return 'pending';
    }
}

$testStatuses = ['pending', 'diterima', 'ditolak'];
foreach ($testStatuses as $status) {
    $mapped = mapFinalStatus($status);
    echo "   - {$status} -> {$mapped}\n";
}

echo "\n4. Expected Structure untuk Tab Final:\n";
echo "   Setiap aplikasi harus memiliki salah satu dari:\n";
echo "   - data_source: 'hasil_seleksi_api'\n";
echo "   - selection_result: (object with data)\n";
echo "   - hasil_seleksi_id: (valid ID)\n";
echo "   - final_status: (valid status)\n";

echo "\n=== END TEST ===\n";
