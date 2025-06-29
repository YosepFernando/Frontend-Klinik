<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ApiService;

class TestApiConnection extends Command
{
    protected $signature = 'test:api';
    protected $description = 'Test API connection and responses';

    public function handle()
    {
        $apiService = new ApiService();

        $this->info('Testing API Connection...');
        $this->info('API Base URL: http://localhost:8002/api');
        $this->newLine();

        // Test health endpoint
        $this->info('1. Testing /api/health endpoint:');
        $this->info('Full URL would be: http://localhost:8002/api/health');
        try {
            $healthResponse = $apiService->get('health');
            $this->line('Health Response: ' . json_encode($healthResponse, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            $this->error('Health check failed: ' . $e->getMessage());
        }
        $this->newLine();

        // Test auth login endpoint
        $this->info('2. Testing /api/auth/login endpoint:');
        $this->info('Full URL would be: http://localhost:8002/api/auth/login');
        try {
            $loginResponse = $apiService->post('auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password'
            ]);
            $this->line('Login Response: ' . json_encode($loginResponse, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            $this->error('Login test failed: ' . $e->getMessage());
        }
        $this->newLine();

        $this->info('Test completed.');
    }
}
