<?php
// File: test_admin_edit.php
// Test untuk mengecek adminEdit method

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing adminEdit method...\n";

try {
    // Bootstrap Laravel app
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();
    
    // Test if class exists
    if (class_exists('App\Http\Controllers\AbsensiController')) {
        echo "✓ AbsensiController class exists\n";
        
        // Test if method exists
        if (method_exists('App\Http\Controllers\AbsensiController', 'adminEdit')) {
            echo "✓ adminEdit method exists\n";
        } else {
            echo "✗ adminEdit method NOT found\n";
        }
        
        if (method_exists('App\Http\Controllers\AbsensiController', 'adminUpdate')) {
            echo "✓ adminUpdate method exists\n";
        } else {
            echo "✗ adminUpdate method NOT found\n";
        }
        
    } else {
        echo "✗ AbsensiController class NOT found\n";
    }
    
    // Test helper functions
    if (function_exists('is_admin')) {
        echo "✓ is_admin helper function exists\n";
    } else {
        echo "✗ is_admin helper function NOT found\n";
    }
    
    if (function_exists('is_authenticated')) {
        echo "✓ is_authenticated helper function exists\n";
    } else {
        echo "✗ is_authenticated helper function NOT found\n";
    }
    
    echo "\nTest completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
