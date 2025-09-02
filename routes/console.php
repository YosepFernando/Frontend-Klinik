<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('debug:session', function () {
    $this->info('=== DEBUG SESSION AUTHENTICATION ===');
    $this->info('Date: ' . date('Y-m-d H:i:s'));
    
    $this->info('1. SESSION STATUS:');
    $this->info('   - Session ID: ' . session_id());
    $this->info('   - Session status: ' . (session_status() == PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE'));
    
    $this->info('2. AUTHENTICATION DATA:');
    $this->info('   - authenticated: ' . (session('authenticated') ? 'YES' : 'NO'));
    $this->info('   - user_id: ' . (session('user_id') ?: 'NULL'));
    $this->info('   - user_email: ' . (session('user_email') ?: 'NULL'));
    $this->info('   - user_name: ' . (session('user_name') ?: 'NULL'));
    $this->info('   - user_role: ' . (session('user_role') ?: 'NULL'));
    
    $this->info('3. API TOKEN:');
    $apiToken = session('api_token');
    if ($apiToken) {
        $this->info('   - Token exists: YES');
        $this->info('   - Token length: ' . strlen($apiToken) . ' characters');
        $this->info('   - Token preview: ' . substr($apiToken, 0, 20) . '...');
        
        // Test token format
        if (preg_match('/^[a-zA-Z0-9_\-\.]+$/', $apiToken)) {
            $this->info('   - Token format: VALID (alphanumeric + _-.)');
        } else {
            $this->info('   - Token format: INVALID');
        }
    } else {
        $this->info('   - Token exists: NO');
    }
    
    $this->info('4. FULL SESSION DATA:');
    $sessionData = session()->all();
    foreach ($sessionData as $key => $value) {
        if ($key === 'api_token' && $value) {
            $this->info("   - {$key}: " . substr($value, 0, 20) . "... (length: " . strlen($value) . ")");
        } elseif (is_array($value)) {
            $this->info("   - {$key}: " . json_encode($value));
        } elseif (is_object($value)) {
            $this->info("   - {$key}: [Object]");
        } else {
            $this->info("   - {$key}: " . ($value ?: 'NULL'));
        }
    }
    
    $this->info('5. TEST API CONNECTION:');
    try {
        $apiService = app(\App\Services\ApiService::class);
        
        $this->info('   - API Base URL: ' . env('API_BASE_URL', 'http://127.0.0.1:8002/api'));
        
        // Test dengan token
        if ($apiToken) {
            $this->info('   - Testing with token...');
            $response = $apiService->withToken($apiToken)->get('auth/profile');
            $this->info('   - Profile API response: ' . json_encode($response));
        } else {
            $this->info('   - Cannot test API: No token available');
        }
        
    } catch (Exception $e) {
        $this->info('   - API Test Error: ' . $e->getMessage());
    }
    
    $this->info('=== END DEBUG ===');
})->purpose('Debug session authentication status');
