<?php
/**
 * Test script for service calls - simulate dashboard controller calls
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Services\WawancaraService;
use App\Services\HasilSeleksiService;

// Create app instance
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test services
$wawancaraService = new WawancaraService();
$hasilSeleksiService = new HasilSeleksiService();

echo "=== Testing WawancaraService::getByLamaran ===\n";
try {
    $result = $wawancaraService->getByLamaran(4);
    echo "Status: " . data_get($result, 'status', 'unknown') . "\n";
    echo "Data count: " . count(data_get($result, 'data.data', data_get($result, 'data', []))) . "\n";
    
    $records = data_get($result, 'data.data', data_get($result, 'data', []));
    if (!empty($records)) {
        $first = reset($records);
        echo "First record status: " . data_get($first, 'status') . "\n";
        echo "First record tanggal: " . data_get($first, 'tanggal_wawancara') . "\n";
    }
    echo "Full response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== Testing HasilSeleksiService::getByUser ===\n";
try {
    $result = $hasilSeleksiService->getByUser(9);
    echo "Status: " . data_get($result, 'status', 'unknown') . "\n";
    echo "Data count: " . count(data_get($result, 'data.data', data_get($result, 'data', []))) . "\n";
    
    $records = data_get($result, 'data.data', data_get($result, 'data', []));
    if (!empty($records)) {
        $first = reset($records);
        echo "First record status: " . data_get($first, 'status') . "\n";
        echo "First record lamaran ID: " . data_get($first, 'id_lamaran_pekerjaan') . "\n";
    }
    echo "Full response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "Test completed!\n";
?>
