<?php

// File ini digunakan untuk debugging session dan role di aplikasi
// Untuk menggunakan, tambahkan ?debug_role=1 ke URL aplikasi

if (isset($_GET['debug_role']) && $_GET['debug_role'] == 1) {
    // Nonaktifkan output buffering
    ob_end_clean();
    
    echo "<pre style='background:#f5f5f5; padding:15px; font-family:monospace; font-size:14px;'>";
    echo "<h2>Debug Session & Role Information</h2>";
    
    // Tampilkan session information
    echo "<h3>Session Data:</h3>";
    echo "session('authenticated'): " . (session('authenticated') ? 'true' : 'false') . "\n";
    echo "session('user_id'): " . session('user_id') . "\n";
    echo "session('user_name'): " . session('user_name') . "\n";
    echo "session('user_email'): " . session('user_email') . "\n";
    echo "session('user_role'): " . session('user_role') . "\n";
    echo "session('api_token'): " . (session('api_token') ? 'exists (' . substr(session('api_token'), 0, 10) . '...)' : 'not set') . "\n\n";
    
    // Tampilkan api_user data
    echo "<h3>API User Data:</h3>";
    $apiUser = session('api_user');
    if ($apiUser) {
        foreach ($apiUser as $key => $value) {
            if (is_array($value)) {
                echo "$key: (array)\n";
            } else {
                echo "$key: $value\n";
            }
        }
    } else {
        echo "No API user data in session\n";
    }
    
    echo "\n";
    
    // Test helper functions
    echo "<h3>Helper Function Results:</h3>";
    echo "auth_user(): " . (auth_user() ? json_encode(auth_user()) : 'null') . "\n";
    echo "user_role(): " . user_role() . "\n";
    echo "is_authenticated(): " . (is_authenticated() ? 'true' : 'false') . "\n";
    echo "is_admin(): " . (is_admin() ? 'true' : 'false') . "\n";
    echo "is_hrd(): " . (is_hrd() ? 'true' : 'false') . "\n";
    echo "is_dokter(): " . (is_dokter() ? 'true' : 'false') . "\n";
    echo "is_beautician(): " . (is_beautician() ? 'true' : 'false') . "\n";
    echo "is_kasir(): " . (is_kasir() ? 'true' : 'false') . "\n";
    echo "is_front_office(): " . (is_front_office() ? 'true' : 'false') . "\n";
    echo "is_pelanggan(): " . (is_pelanggan() ? 'true' : 'false') . "\n";
    
    echo "</pre>";
    exit;
}
