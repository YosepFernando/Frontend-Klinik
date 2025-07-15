<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Route;

echo "=== API Response Test for PDF Export ===\n\n";

// Test if the application is working
try {
    // Create a minimal Laravel app instance for testing
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    echo "✓ Laravel application loaded successfully\n";
    
    // Test the routes
    $routes = [
        'absensi.export-pdf' => '/absensi/export-pdf',
        'payroll.export-pdf' => '/payroll/export-pdf',
        'pegawai.export-pdf' => '/pegawai-export-pdf'
    ];
    
    echo "\n--- Testing Routes ---\n";
    foreach ($routes as $name => $path) {
        try {
            if (Route::has($name)) {
                echo "✓ Route '$name' exists: $path\n";
            } else {
                echo "✗ Route '$name' does not exist\n";
            }
        } catch (Exception $e) {
            echo "✗ Error checking route '$name': " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Failed to load Laravel: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
