<?php

use Illuminate\Support\Facades\Route;

// Add a debug route for PDF export testing
Route::get('/debug-pdf-export', function () {
    try {
        // Test authentication
        $user = auth_user();
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        // Test AbsensiService
        $absensiService = app('App\Services\AbsensiService');
        $response = $absensiService->getAll([]);
        
        return response()->json([
            'user' => $user,
            'api_response' => $response,
            'session_data' => [
                'api_token' => session('api_token') ? 'present' : 'missing',
                'user_id' => session('user_id'),
                'api_user' => session('api_user')
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
