<?php

// Simple test script to verify session and API integration
// Run this via: php artisan tinker

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;
use App\Services\GajiService;

class TestSessionAndApi extends Command
{
    protected $signature = 'test:session-api';
    protected $description = 'Test session and API integration for payroll';

    public function handle()
    {
        $this->info('=== SESSION & API TEST ===');
        
        // Test 1: Session Configuration
        $this->info('1. Testing Session Configuration...');
        $this->line('Session Driver: ' . config('session.driver'));
        $this->line('Session Lifetime: ' . config('session.lifetime') . ' minutes');
        $this->line('Session Encrypt: ' . (config('session.encrypt') ? 'Yes' : 'No'));
        
        // Test 2: Session Status
        $this->info('2. Testing Current Session...');
        $this->line('Session ID: ' . Session::getId());
        $this->line('Has authenticated: ' . (Session::has('authenticated') ? 'Yes' : 'No'));
        $this->line('Has api_token: ' . (Session::has('api_token') ? 'Yes' : 'No'));
        $this->line('User ID: ' . Session::get('user_id', 'Not set'));
        $this->line('User Role: ' . Session::get('user_role', 'Not set'));
        
        // Test 3: CSRF Token
        $this->info('3. Testing CSRF...');
        $csrfToken = csrf_token();
        $this->line('CSRF Token: ' . substr($csrfToken, 0, 20) . '...');
        
        // Test 4: API Service (if session exists)
        if (Session::has('api_token')) {
            $this->info('4. Testing API Service...');
            try {
                $gajiService = app(GajiService::class);
                $gajiService->withToken(Session::get('api_token'));
                
                // Try to get gaji list (with limit to avoid too much data)
                $response = $gajiService->getAll(['limit' => 1]);
                
                if (isset($response['status']) && $response['status'] === 'success') {
                    $this->info('✅ API Service: Working');
                } else {
                    $this->error('❌ API Service: Failed');
                    $this->line('Response: ' . json_encode($response));
                }
            } catch (\Exception $e) {
                $this->error('❌ API Service Exception: ' . $e->getMessage());
            }
        } else {
            $this->warn('4. API Service: Skipped (no api_token in session)');
        }
        
        $this->info('=== TEST COMPLETED ===');
        
        return 0;
    }
}
