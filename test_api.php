<?php

use App\Services\ApiService;

// Test koneksi ke API
$apiService = new ApiService();

echo "Testing API Connection...\n";
echo "API Base URL: http://localhost:8002/api\n\n";

// Test health endpoint
echo "1. Testing health endpoint:\n";
try {
    $healthResponse = $apiService->get('health');
    echo "Health Response: " . json_encode($healthResponse, JSON_PRETTY_PRINT) . "\n\n";
} catch (Exception $e) {
    echo "Health check failed: " . $e->getMessage() . "\n\n";
}

// Test auth login endpoint
echo "2. Testing auth/login endpoint:\n";
try {
    $loginResponse = $apiService->post('auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password'
    ]);
    echo "Login Response: " . json_encode($loginResponse, JSON_PRETTY_PRINT) . "\n\n";
} catch (Exception $e) {
    echo "Login test failed: " . $e->getMessage() . "\n\n";
}

echo "Test completed.\n";
