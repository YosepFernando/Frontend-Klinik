<?php
/**
 * Test route for dashboard data - simulate what dashboard shows
 */

use App\Http\Controllers\DashboardController;
use App\Services\LamaranPekerjaanService;
use App\Services\WawancaraService;
use App\Services\HasilSeleksiService;

Route::get('/test-dashboard-data', function () {
    // Simulate user ID 9 (Budi Pelanggan)
    $userId = 9;
    
    // Simulate services
    $lamaranService = new LamaranPekerjaanService();
    $wawancaraService = new WawancaraService();
    $hasilSeleksiService = new HasilSeleksiService();
    
    // Get applications
    $response = $lamaranService->getByUser($userId);
    $applications = [];
    
    if (data_get($response, 'status') === 'success') {
        $applications = data_get($response, 'data.data', data_get($response, 'data', []));
    }
    
    // Process each application like in DashboardController
    $enrichedApplications = [];
    
    foreach ($applications as $lamaran) {
        $lamaranId = data_get($lamaran, 'id_lamaran_pekerjaan', data_get($lamaran, 'id'));
        
        // Status berkas
        $rawStatus = data_get($lamaran, 'status_lamaran', data_get($lamaran, 'status', ''));
        $statusSeleksiBerkas = mapStatusLamaran($rawStatus);
        
        // Status wawancara
        $statusWawancara = null;
        $interviewData = null;
        
        $respWawancara = $wawancaraService->getByLamaran($lamaranId);
        if (data_get($respWawancara, 'status') === 'success') {
            $recordsWawancara = data_get($respWawancara, 'data.data', data_get($respWawancara, 'data', []));
            if (!empty($recordsWawancara) && is_array($recordsWawancara)) {
                $interviewData = reset($recordsWawancara);
                $wawancaraStatus = data_get($interviewData, 'status', '');
                $statusWawancara = mapStatusWawancara($wawancaraStatus);
            }
        }
        
        // Status hasil seleksi
        $statusSeleksiAkhir = null;
        $hasilSeleksiData = null;
        
        $respHasil = $hasilSeleksiService->getByUser($userId);
        if (data_get($respHasil, 'status') === 'success') {
            $recordsHasil = data_get($respHasil, 'data.data', data_get($respHasil, 'data', []));
            if (!empty($recordsHasil) && is_array($recordsHasil)) {
                $filtered = array_filter($recordsHasil, function($it) use ($lamaranId) {
                    return data_get($it, 'id_lamaran_pekerjaan') == $lamaranId;
                });
                
                if (!empty($filtered)) {
                    $record = reset($filtered);
                    $hasilSeleksiData = $record;
                    $hasilStatus = data_get($record, 'status', '');
                    $statusSeleksiAkhir = mapStatusHasilSeleksi($hasilStatus);
                }
            }
        }
        
        // Enrich lamaran
        $enrichedLamaran = $lamaran;
        $enrichedLamaran['status_seleksi_berkas'] = $statusSeleksiBerkas;
        $enrichedLamaran['status_wawancara'] = $statusWawancara;
        $enrichedLamaran['status_seleksi_akhir'] = $statusSeleksiAkhir;
        
        if ($interviewData) {
            $enrichedLamaran['interview_date'] = data_get($interviewData, 'tanggal_wawancara');
            $enrichedLamaran['interview_location'] = data_get($interviewData, 'lokasi');
            $enrichedLamaran['interview_notes'] = data_get($interviewData, 'catatan');
        }
        
        if ($hasilSeleksiData) {
            $enrichedLamaran['hasil_seleksi'] = $hasilSeleksiData;
        }
        
        $enrichedApplications[] = $enrichedLamaran;
    }
    
    return response()->json([
        'userId' => $userId,
        'applications_count' => count($enrichedApplications),
        'applications' => $enrichedApplications
    ], 200, [], JSON_PRETTY_PRINT);
});

function mapStatusLamaran($status) {
    $status = $status ?? '';
    switch (strtolower(trim($status))) {
        case 'pending': return 'Menunggu Review';
        case 'diterima': return 'Berkas Diterima';
        case 'ditolak': return 'Berkas Ditolak';
        case '': return 'Belum Diproses';
        default: return 'Status Tidak Diketahui';
    }
}

function mapStatusWawancara($status) {
    $status = $status ?? '';
    switch (strtolower(trim($status))) {
        case 'pending': return 'Wawancara Dijadwalkan';
        case 'terjadwal': return 'Wawancara Dijadwalkan';
        case 'diterima': return 'Lolos Wawancara';
        case 'ditolak': return 'Tidak Lolos Wawancara';
        case '': return 'Belum Ada Wawancara';
        default: return 'Status Wawancara Tidak Diketahui';
    }
}

function mapStatusHasilSeleksi($status) {
    $status = $status ?? '';
    switch (strtolower(trim($status))) {
        case 'pending': return 'Menunggu Keputusan Final';
        case 'diterima': return 'Diterima Bekerja';
        case 'ditolak': return 'Tidak Diterima';
        case '': return 'Belum Ada Keputusan';
        default: return 'Status Final Tidak Diketahui';
    }
}
?>
