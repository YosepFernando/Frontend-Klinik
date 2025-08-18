<?php

// Debug script untuk analisis detail API response
echo "=== ANALISIS DETAIL API HASIL SELEKSI ===\n\n";

$apiUrl = "http://127.0.0.1:8002/api/public/hasil-seleksi?id_lowongan_pekerjaan=3";
$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

if (isset($data['data']['data'])) {
    $items = $data['data']['data'];
    
    foreach ($items as $index => $item) {
        echo "=== ITEM #" . ($index + 1) . " ===\n";
        echo "ID Hasil Seleksi: " . ($item['id_hasil_seleksi'] ?? 'NULL') . "\n";
        echo "ID User: " . ($item['id_user'] ?? 'NULL') . "\n";
        echo "Status: " . ($item['status'] ?? 'NULL') . "\n";
        
        // Analisis struktur lowongan_pekerjaan
        echo "\n--- lowongan_pekerjaan relation ---\n";
        if (isset($item['lowongan_pekerjaan'])) {
            if (is_array($item['lowongan_pekerjaan']) && !empty($item['lowongan_pekerjaan'])) {
                echo "lowongan_pekerjaan: PRESENT (array)\n";
                echo "id_lowongan_pekerjaan: " . ($item['lowongan_pekerjaan']['id_lowongan_pekerjaan'] ?? 'NULL') . "\n";
            } else {
                echo "lowongan_pekerjaan: EMPTY or NULL\n";
                var_dump($item['lowongan_pekerjaan']);
            }
        } else {
            echo "lowongan_pekerjaan: NOT SET\n";
        }
        
        // Analisis struktur lamaran_pekerjaan  
        echo "\n--- lamaran_pekerjaan relation ---\n";
        if (isset($item['lamaran_pekerjaan'])) {
            if (is_array($item['lamaran_pekerjaan']) && !empty($item['lamaran_pekerjaan'])) {
                echo "lamaran_pekerjaan: PRESENT (array)\n";
                echo "id_lowongan_pekerjaan: " . ($item['lamaran_pekerjaan']['id_lowongan_pekerjaan'] ?? 'NULL') . "\n";
                echo "id_lamaran_pekerjaan: " . ($item['lamaran_pekerjaan']['id_lamaran_pekerjaan'] ?? 'NULL') . "\n";
            } else {
                echo "lamaran_pekerjaan: EMPTY or NULL\n";
                var_dump($item['lamaran_pekerjaan']);
            }
        } else {
            echo "lamaran_pekerjaan: NOT SET\n";
        }
        
        echo "\n=========================\n\n";
    }
} else {
    echo "ERROR: No data found in API response\n";
    var_dump($data);
}

echo "=== END ANALISIS ===\n";
