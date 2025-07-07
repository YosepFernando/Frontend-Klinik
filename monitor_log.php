<?php

/**
 * Script untuk monitor log Laravel secara real-time
 */

$logFile = __DIR__ . '/storage/logs/laravel.log';

echo "=== MONITORING LARAVEL LOG ===\n";
echo "Log file: $logFile\n";
echo "Monitoring dimulai...\n\n";

if (!file_exists($logFile)) {
    echo "Log file tidak ditemukan. Membuat file baru...\n";
    touch($logFile);
}

$lastSize = filesize($logFile);

while (true) {
    clearstatcache();
    $currentSize = filesize($logFile);
    
    if ($currentSize > $lastSize) {
        $handle = fopen($logFile, 'r');
        fseek($handle, $lastSize);
        
        while (($line = fgets($handle)) !== false) {
            echo $line;
        }
        
        fclose($handle);
        $lastSize = $currentSize;
    }
    
    usleep(500000); // 0.5 second
}
