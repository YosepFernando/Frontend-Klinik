<?php

// Simulasi data yang dibuat oleh controller
$mockFinalApplications = [
    (object) [
        'id' => 7,
        'user_id' => 11,
        'name' => 'adhim',
        'email' => 'adhim@gmail.com',
        'final_status' => 'accepted',
        'hasil_seleksi_id' => 6,
        'data_source' => 'hasil_seleksi_api',
        'selection_result' => [
            'id' => 6,
            'status' => 'diterima'
        ]
    ],
    (object) [
        'id' => 5,
        'user_id' => 10,
        'name' => 'Sinta Pelanggan',
        'email' => 'pelanggan2@gmail.com',
        'final_status' => 'pending',
        'hasil_seleksi_id' => 3,
        'data_source' => 'hasil_seleksi_api',
        'selection_result' => [
            'id' => 3,
            'status' => 'pending'
        ]
    ]
];

echo "=== SIMULASI FILTER TAB FINAL ===\n\n";
echo "Total data yang masuk ke tab final: " . count($mockFinalApplications) . "\n\n";

foreach ($mockFinalApplications as $index => $application) {
    echo "Data #" . ($index + 1) . ": {$application->name}\n";
    
    // Test filter conditions sesuai dengan yang ada di blade
    $isFromSelectionAPI = isset($application->data_source) && $application->data_source === 'hasil_seleksi_api';
    $hasSelectionResult = isset($application->selection_result) && $application->selection_result;
    $hasResultId = isset($application->hasil_seleksi_id) && $application->hasil_seleksi_id;
    $hasFinalStatus = isset($application->final_status) && $application->final_status;
    
    echo "  - isFromSelectionAPI: " . ($isFromSelectionAPI ? 'YES' : 'NO') . "\n";
    echo "  - hasSelectionResult: " . ($hasSelectionResult ? 'YES' : 'NO') . "\n";
    echo "  - hasResultId: " . ($hasResultId ? 'YES (' . $application->hasil_seleksi_id . ')' : 'NO') . "\n";
    echo "  - hasFinalStatus: " . ($hasFinalStatus ? 'YES (' . $application->final_status . ')' : 'NO') . "\n";
    
    // Filter logic dari blade
    $shouldSkip = !$isFromSelectionAPI && !$hasSelectionResult && !$hasResultId && !$hasFinalStatus;
    
    echo "  - HASIL: " . ($shouldSkip ? 'SKIP (TIDAK TAMPIL)' : 'TAMPIL') . "\n\n";
}

echo "=== KESIMPULAN ===\n";
echo "Jika semua data menunjukkan 'TAMPIL', maka masalah bukan di filter.\n";
echo "Jika ada yang 'SKIP', maka filter terlalu ketat.\n";
