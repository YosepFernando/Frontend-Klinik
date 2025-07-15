<?php

// Test script to debug PDF export issues
require_once __DIR__ . '/vendor/autoload.php';

echo "=== PDF Export Debug Test ===\n\n";

// Test 1: Check if routes are accessible
echo "1. Testing route accessibility:\n";
$routes = [
    'Absensi PDF' => 'http://localhost:8000/absensi/export-pdf',
    'Payroll PDF' => 'http://localhost:8000/payroll/export-pdf', 
    'Pegawai PDF' => 'http://localhost:8000/pegawai-export-pdf'
];

foreach ($routes as $name => $url) {
    echo "   - $name: $url\n";
}

echo "\n2. Checking for service method names:\n";

// Check if files exist and methods are correct
$controllers = [
    'AbsensiController' => 'app/Http/Controllers/AbsensiController.php',
    'PayrollController' => 'app/Http/Controllers/PayrollController.php',
    'PegawaiController' => 'app/Http/Controllers/PegawaiController.php'
];

foreach ($controllers as $name => $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        echo "   - $name: ";
        if (strpos($content, 'exportPdf') !== false) {
            echo "✓ exportPdf method found\n";
        } else {
            echo "✗ exportPdf method NOT found\n";
        }
    } else {
        echo "   - $name: ✗ File not found\n";
    }
}

// Check service methods
echo "\n3. Checking service methods:\n";
$services = [
    'AbsensiService' => 'app/Services/AbsensiService.php',
    'GajiService' => 'app/Services/GajiService.php',
    'PegawaiService' => 'app/Services/PegawaiService.php'
];

foreach ($services as $name => $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        echo "   - $name: ";
        if (strpos($content, 'public function getAll(') !== false) {
            echo "✓ getAll method found\n";
        } else {
            echo "✗ getAll method NOT found\n";
        }
    } else {
        echo "   - $name: ✗ File not found\n";
    }
}

// Check PDF templates
echo "\n4. Checking PDF templates:\n";
$templates = [
    'Absensi Template' => 'resources/views/pdf/absensi-report.blade.php',
    'Payroll Template' => 'resources/views/pdf/payroll-report.blade.php',
    'Pegawai Template' => 'resources/views/pdf/pegawai-report.blade.php'
];

foreach ($templates as $name => $file) {
    if (file_exists($file)) {
        echo "   - $name: ✓ Template found\n";
    } else {
        echo "   - $name: ✗ Template NOT found\n";
    }
}

echo "\n5. Checking for potential issues:\n";

// Check for duplicate routes
if (file_exists('routes/web.php')) {
    $routeContent = file_get_contents('routes/web.php');
    $exportPdfCount = substr_count($routeContent, 'export-pdf');
    echo "   - Export PDF routes found: $exportPdfCount\n";
    if ($exportPdfCount > 3) {
        echo "   ⚠ Warning: Possible duplicate routes detected\n";
    }
}

// Check for incorrect method calls
echo "\n6. Scanning for incorrect method calls:\n";
$incorrectMethods = ['getGajiList', 'getAllPegawai'];
$phpFiles = glob('app/**/*.php', GLOB_BRACE);

foreach ($incorrectMethods as $method) {
    $found = false;
    foreach ($phpFiles as $file) {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            if (strpos($content, $method) !== false) {
                echo "   ✗ Found $method in $file\n";
                $found = true;
            }
        }
    }
    if (!$found) {
        echo "   ✓ No instances of $method found\n";
    }
}

echo "\n=== Debug Test Complete ===\n";
