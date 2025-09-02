<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrainingController;
use Illuminate\Http\Request;

// Test route for debugging training deletion
Route::get('/test-training-delete/{id}', function($id) {
    try {
        // Log the attempt
        \Log::info('Testing training deletion', ['id' => $id]);
        
        // Check if user is authenticated
        if (!session('authenticated')) {
            return response()->json([
                'error' => 'User not authenticated',
                'session_data' => session()->all()
            ]);
        }
        
        // Try to instantiate the service
        $pelatihanService = new \App\Services\PelatihanService();
        
        // Test API connection first
        $testResponse = $pelatihanService->getById($id);
        
        if (!$testResponse || !isset($testResponse['status'])) {
            return response()->json([
                'error' => 'API connection failed',
                'response' => $testResponse
            ]);
        }
        
        if ($testResponse['status'] !== 'success') {
            return response()->json([
                'error' => 'Training not found or API error',
                'response' => $testResponse
            ]);
        }
        
        // Now try to delete
        $deleteResponse = $pelatihanService->delete($id);
        
        return response()->json([
            'message' => 'Delete test completed',
            'get_response' => $testResponse,
            'delete_response' => $deleteResponse
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Test delete error: ' . $e->getMessage());
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->middleware(['role:admin,hrd,front_office,kasir,dokter,beautician,pelanggan', 'api.check']);
