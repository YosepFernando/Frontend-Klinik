<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfTestController extends Controller
{
    /**
     * Simple PDF test without authentication
     */
    public function testSimple()
    {
        try {
            // Clear output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }

            $data = ['test_id' => 'SIMPLE_TEST_' . time()];

            $pdf = Pdf::loadView('pdf.test-simple', $data);
            $pdf->setPaper('A4', 'portrait');
            
            $filename = 'test_simple_' . date('Y-m-d_H-i-s') . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Simple PDF Test Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response('Simple PDF Test Error: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Test slip gaji PDF without authentication
     */
    public function testSlip()
    {
        try {
            // Clear output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Mock data for testing
            $data = [
                'payroll' => [
                    'id_gaji' => 999,
                    'periode_bulan' => 7,
                    'periode_tahun' => 2025,
                    'gaji_pokok' => 5000000,
                    'gaji_kehadiran' => 400000,
                    'gaji_bonus' => 500000,
                    'potongan' => 100000,
                    'pajak' => 250000,
                    'gaji_total' => 5550000,
                    'status' => 'Sudah Terbayar',
                    'jumlah_absensi' => 22,
                    'total_hari_kerja' => 22,
                    'persentase_kehadiran' => 100,
                    'keterangan' => 'Test slip gaji - NO AUTH TEST'
                ],
                'nama_pegawai' => 'Test Employee',
                'posisi' => 'Staff Administrasi',
                'nip' => 'EMP001',
                'tanggal_cetak' => now(),
                'user_info' => [
                    'nama' => 'Administrator',
                    'role' => 'admin'
                ]
            ];

            // Generate PDF with minimal configuration
            $pdf = Pdf::loadView('pdf.slip-gaji', $data);
            $pdf->setPaper('A4', 'portrait');
            
            // Set safe options
            $pdf->setOptions([
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'isFontSubsettingEnabled' => false,
                'defaultFont' => 'DejaVu Sans'
            ]);

            $filename = 'test_slip_gaji_' . date('Y-m-d_H-i-s') . '.pdf';
            
            \Log::info('Test Slip PDF Generation', [
                'filename' => $filename
            ]);

            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Test Slip PDF Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response('Test Slip PDF Error: ' . $e->getMessage(), 500);
        }
    }
}
