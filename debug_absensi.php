<?php

// Debug script untuk mencari sumber error
echo "=== DEBUGGING ABSENSI ERROR ===" . PHP_EOL;

require_once __DIR__ . '/bootstrap/app.php';

// Boot Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';

try {
    // Simulate the exact conditions that cause the error
    echo "1. Checking if tb_absensi table exists..." . PHP_EOL;
    
    $pdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='tb_absensi'");
    $result = $stmt->fetchAll();
    
    if (count($result) > 0) {
        echo "⚠ tb_absensi table EXISTS!" . PHP_EOL;
        
        // Check table structure
        $stmt = $pdo->query("PRAGMA table_info(tb_absensi)");
        $columns = $stmt->fetchAll();
        
        echo "Columns:" . PHP_EOL;
        foreach ($columns as $col) {
            echo "  - {$col['name']} ({$col['type']}, notnull: {$col['notnull']})" . PHP_EOL;
        }
        
        // Check if there are any triggers or constraints
        $stmt = $pdo->query("SELECT sql FROM sqlite_master WHERE tbl_name='tb_absensi'");
        $triggers = $stmt->fetchAll();
        
        echo "SQL Definitions:" . PHP_EOL;
        foreach ($triggers as $trigger) {
            echo "  " . $trigger['sql'] . PHP_EOL;
        }
        
        // Try to see what would cause an insert
        echo "2. Testing what happens with INSERT..." . PHP_EOL;
        try {
            $stmt = $pdo->prepare("INSERT INTO tb_absensi (tanggal, created_at, updated_at) VALUES (?, ?, ?)");
            $stmt->execute(['2025-07-03', '2025-07-03 17:07:37', '2025-07-03 17:07:37']);
            echo "✗ INSERT succeeded - this should NOT happen!" . PHP_EOL;
        } catch (Exception $e) {
            echo "✓ INSERT failed as expected: " . $e->getMessage() . PHP_EOL;
        }
        
    } else {
        echo "✓ tb_absensi table does NOT exist" . PHP_EOL;
        
        // Check if any migration would auto-create it
        echo "2. Checking if migration would create table..." . PHP_EOL;
        
        // Look for auto-migration or seeding
        echo "3. Checking for auto-seeding..." . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

echo "=== DEBUG COMPLETE ===" . PHP_EOL;
