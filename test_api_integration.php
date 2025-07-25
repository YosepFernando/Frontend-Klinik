<?php

// Test file to verify API integration
// Run with: php test_api_integration.php

require_once 'vendor/autoload.php';

$apiBaseUrl = 'http://127.0.0.1:8002/api';

echo "Testing Klinik API Integration\n";
echo "============================\n\n";

// Test 1: Get Lowongan
echo "1. Testing GET /lowongan\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiBaseUrl . '/lowongan');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "✅ Success: " . count($data['data']['data']) . " lowongan found\n";
} else {
    echo "❌ Failed: HTTP $httpCode\n";
}

// Test 2: Test with authentication (you'll need a valid token)
echo "\n2. Testing authenticated endpoints (need token)\n";
echo "Note: For full testing, you need to:\n";
echo "- Login to get auth token\n";
echo "- Test GET /lamaran-pekerjaan\n";
echo "- Test GET /wawancara\n";
echo "- Test GET /hasil-seleksi\n";

echo "\n3. Status mapping test\n";
echo "Testing status mapping functions:\n";

function mapStatusLamaran($status) {
    switch (strtolower(trim($status))) {
        case 'pending': return 'Menunggu Review';
        case 'diterima': return 'Berkas Diterima';
        case 'ditolak': return 'Berkas Ditolak';
        case '': case null: return 'Belum Diproses';
        default: return 'Status Tidak Diketahui';
    }
}

function mapStatusWawancara($status) {
    switch (strtolower(trim($status))) {
        case 'pending': return 'Wawancara Dijadwalkan';
        case 'diterima': return 'Lolos Wawancara';
        case 'ditolak': return 'Tidak Lolos Wawancara';
        case '': case null: return 'Belum Ada Wawancara';
        default: return 'Status Wawancara Tidak Diketahui';
    }
}

function mapStatusHasilSeleksi($status) {
    switch (strtolower(trim($status))) {
        case 'pending': return 'Menunggu Keputusan Final';
        case 'diterima': return 'Diterima Bekerja';
        case 'ditolak': return 'Tidak Diterima';
        case '': case null: return 'Belum Ada Keputusan';
        default: return 'Status Final Tidak Diketahui';
    }
}

$testStatuses = ['pending', 'diterima', 'ditolak', '', null, 'unknown'];

foreach ($testStatuses as $status) {
    echo "- Lamaran '$status' → " . mapStatusLamaran($status) . "\n";
    echo "- Wawancara '$status' → " . mapStatusWawancara($status) . "\n";
    echo "- Hasil Seleksi '$status' → " . mapStatusHasilSeleksi($status) . "\n";
    echo "\n";
}

echo "Integration test completed!\n";
