<?php
/**
 * Test script untuk memverifikasi koneksi API
 */

require_once __DIR__ . '/vendor/autoload.php';

// Initialize Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔗 Testing API Connection\n";
echo "========================\n\n";

try {
    // Test ApiService
    $apiService = app(\App\Services\ApiService::class);
    
    // Test basic properties
    $reflection = new ReflectionClass($apiService);
    $baseUrlProperty = $reflection->getProperty('baseUrl');
    $baseUrlProperty->setAccessible(true);
    $baseUrl = $baseUrlProperty->getValue($apiService);
    
    echo "📍 Base URL: " . $baseUrl . "\n";
    echo "🌐 Expected: http://127.0.0.1:8002/api\n";
    
    if ($baseUrl === 'http://127.0.0.1:8002/api') {
        echo "✅ Base URL is correct!\n\n";
    } else {
        echo "❌ Base URL mismatch!\n\n";
    }
    
    // Test client base_uri
    $clientProperty = $reflection->getProperty('client');
    $clientProperty->setAccessible(true);
    $client = $clientProperty->getValue($apiService);
    
    // Get client config
    $clientReflection = new ReflectionClass($client);
    $configProperty = $clientReflection->getProperty('config');
    $configProperty->setAccessible(true);
    $config = $configProperty->getValue($client);
    
    echo "🔧 Client base_uri: " . $config['base_uri'] . "\n";
    echo "🌐 Expected: http://127.0.0.1:8002/api/\n";
    
    if ($config['base_uri'] === 'http://127.0.0.1:8002/api/') {
        echo "✅ Client base_uri is correct!\n\n";
    } else {
        echo "❌ Client base_uri mismatch!\n\n";
    }
    
    // Test actual connection
    echo "🔍 Testing actual API connection...\n";
    $testResult = $apiService->testConnection();
    
    if ($testResult) {
        echo "✅ API Server is reachable!\n";
    } else {
        echo "❌ API Server is not reachable. Make sure API server is running on http://127.0.0.1:8002\n";
    }
    
    echo "\n🎯 CONFIGURATION SUMMARY:\n";
    echo "- Environment: " . env('APP_ENV') . "\n";
    echo "- API Base URL (from .env): " . env('API_BASE_URL', 'not set') . "\n";
    echo "- Service Base URL: " . $baseUrl . "\n";
    echo "- Client Base URI: " . $config['base_uri'] . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
