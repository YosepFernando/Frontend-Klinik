<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DebugController extends Controller
{
    protected $absensiService;
    
    public function __construct(\App\Services\AbsensiService $absensiService)
    {
        $this->absensiService = $absensiService;
    }
    
    public function testPdfExport(Request $request)
    {
        try {
            \Log::info('Debug PDF Export Test Started');
            
            // Check authentication
            $user = auth_user();
            if (!$user) {
                return response()->json(['error' => 'Not authenticated'], 401);
            }
            
            // Test API call
            $response = $this->absensiService->getAll([]);
            
            \Log::info('Debug - API Response:', $response);
            
            // Try to generate PDF with empty data
            $data = [
                'absensi' => [],
                'filters' => []
            ];
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.absensi-report', $data);
            $pdf->setPaper('A4', 'landscape');
            
            return $pdf->download('debug_test.pdf');
            
        } catch (\Exception $e) {
            \Log::error('Debug PDF Export Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
}
