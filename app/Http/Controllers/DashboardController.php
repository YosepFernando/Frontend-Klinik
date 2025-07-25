<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\DashboardService;
use App\Services\AbsensiService;
use App\Services\PelatihanService;
use App\Services\LowonganPekerjaanService;
use App\Services\LamaranPekerjaanService;
use App\Services\WawancaraService;
use App\Services\HasilSeleksiService;
use Exception;

class DashboardController extends Controller
{
    protected $dashboardService;
    protected $absensiService;
    protected $pelatihanService;
    protected $lowonganService;
    protected $lamaranService;
    protected $wawancaraService;
    protected $hasilSeleksiService;

    public function __construct(
        DashboardService $dashboardService,
        AbsensiService $absensiService,
        PelatihanService $pelatihanService,
        LowonganPekerjaanService $lowonganService,
        LamaranPekerjaanService $lamaranService,
        WawancaraService $wawancaraService,
        HasilSeleksiService $hasilSeleksiService
    ) {
        $this->middleware('api.auth');

        $this->dashboardService    = $dashboardService;
        $this->absensiService      = $absensiService;
        $this->pelatihanService    = $pelatihanService;
        $this->lowonganService     = $lowonganService;
        $this->lamaranService      = $lamaranService;
        $this->wawancaraService    = $wawancaraService;
        $this->hasilSeleksiService = $hasilSeleksiService;
    }

    public function index()
    {
        // 1. Autentikasi
        if (! is_authenticated()) {
            return redirect()->route('login');
        }

        $user   = auth_user();
        $userId = $user->id_user ?? $user->id;
        $data   = ['user' => $user];

        try {
            // 2. Data dashboard umum
            $dashboardResponse = $this->dashboardService->getDashboardData();
            if (data_get($dashboardResponse, 'status') === 'success') {
                $data = array_merge($data, data_get($dashboardResponse, 'data', []));
            }

            // 3. Jika pelanggan, ambil lamaran & enrich
            if ($user->role === 'pelanggan') {
                $lamaranResponse = $this->lamaranService->getAll([
                    'limit'   => 10,
                    'id_user' => $userId,
                ]);

                $enrichedApplications = [];

                if (data_get($lamaranResponse, 'status') === 'success') {
                    $lamaranData = data_get($lamaranResponse, 'data.data', data_get($lamaranResponse, 'data', []));

                    foreach ($lamaranData as $lamaran) {
                        $enrichedLamaran = $lamaran;
                        $lamaranId       = data_get($lamaran, 'id_lamaran_pekerjaan', data_get($lamaran, 'id'));

                        // 3.1 Status Seleksi Berkas - ambil dari status_lamaran field
                        $rawStatus = data_get($lamaran, 'status_lamaran', data_get($lamaran, 'status', ''));
                        $statusSeleksiBerkas = $this->mapStatusLamaran($rawStatus);

                        // 3.2 Status Wawancara - ambil dari API wawancara
                        $statusWawancara = null;
                        $interviewData   = null;

                        try {
                            Log::info('Fetching wawancara for lamaran', ['lamaran_id' => $lamaranId]);
                            $resp = $this->wawancaraService->getByLamaran($lamaranId);
                            Log::info('Wawancara API Response', [
                                'lamaran_id' => $lamaranId, 
                                'response' => $resp,
                                'status' => data_get($resp, 'status'),
                                'data_structure' => is_array(data_get($resp, 'data')) ? 'array' : gettype(data_get($resp, 'data'))
                            ]);

                            if (data_get($resp, 'status') === 'success') {
                                $records = data_get($resp, 'data.data', data_get($resp, 'data', []));
                                Log::info('Wawancara records found', [
                                    'lamaran_id' => $lamaranId,
                                    'records_count' => is_array($records) ? count($records) : 'not_array',
                                    'records' => $records
                                ]);
                                
                                if (!empty($records) && is_array($records)) {
                                    $interviewData = reset($records);
                                    $wawancaraStatus = data_get($interviewData, 'status', '');
                                    Log::info('Wawancara status extracted', [
                                        'lamaran_id' => $lamaranId,
                                        'interview_data' => $interviewData,
                                        'wawancara_status_raw' => $wawancaraStatus
                                    ]);
                                    $statusWawancara = $this->mapStatusWawancara($wawancaraStatus);
                                }
                            }
                        } catch (Exception $e) {
                            Log::error('Error fetch wawancara', ['lamaran_id'=>$lamaranId, 'error'=>$e->getMessage(), 'trace' => $e->getTraceAsString()]);
                        }

                        // 3.3 Status Seleksi Akhir - ambil dari API hasil seleksi
                        $statusSeleksiAkhir = null;
                        $hasilSeleksiData   = null;

                        try {
                            Log::info('Fetching hasil seleksi for user', ['user_id' => $userId, 'lamaran_id' => $lamaranId]);
                            
                            // Coba dulu berdasarkan user saja kemudian filter
                            $respSel = $this->hasilSeleksiService->getByUser($userId);
                            Log::info('HasilSeleksi API Response (by user)', [
                                'user_id' => $userId,
                                'response' => $respSel,
                                'status' => data_get($respSel, 'status'),
                                'data_structure' => is_array(data_get($respSel, 'data')) ? 'array' : gettype(data_get($respSel, 'data'))
                            ]);

                            if (data_get($respSel, 'status') === 'success') {
                                $recordsSel = data_get($respSel, 'data.data', data_get($respSel, 'data', []));
                                Log::info('HasilSeleksi records found', [
                                    'user_id' => $userId,
                                    'records_count' => is_array($recordsSel) ? count($recordsSel) : 'not_array',
                                    'records' => $recordsSel
                                ]);
                                
                                if (!empty($recordsSel) && is_array($recordsSel)) {
                                    // Filter berdasarkan lowongan pekerjaan
                                    $lowonganId = data_get($lamaran, 'id_lowongan_pekerjaan');
                                    Log::info('Filtering hasil seleksi', [
                                        'lowongan_id' => $lowonganId,
                                        'lamaran_id' => $lamaranId
                                    ]);
                                    
                                    $filtered = array_filter($recordsSel, function($it) use ($lowonganId, $lamaranId) {
                                        // Prioritas pertama: match dengan lamaran ID
                                        $matchLamaran = data_get($it, 'id_lamaran_pekerjaan') == $lamaranId;
                                        
                                        // Jika tidak match lamaran, coba match lowongan via relasi
                                        $matchLowongan = false;
                                        if (!$matchLamaran) {
                                            $lamaranRelasi = data_get($it, 'lamaran_pekerjaan');
                                            if ($lamaranRelasi) {
                                                $lowonganIdFromRelasi = data_get($lamaranRelasi, 'id_lowongan_pekerjaan');
                                                $matchLowongan = $lowonganIdFromRelasi == $lowonganId;
                                            }
                                        }
                                        
                                        Log::info('Filtering hasil seleksi item', [
                                            'item_id' => data_get($it, 'id_hasil_seleksi'),
                                            'item_lamaran_id' => data_get($it, 'id_lamaran_pekerjaan'),
                                            'target_lamaran_id' => $lamaranId,
                                            'match_lamaran' => $matchLamaran,
                                            'match_lowongan' => $matchLowongan,
                                            'will_include' => $matchLamaran || $matchLowongan
                                        ]);
                                        
                                        return $matchLamaran || $matchLowongan;
                                    });
                                    
                                    Log::info('Filtered results', [
                                        'filtered_count' => count($filtered),
                                        'filtered_data' => $filtered
                                    ]);
                                    
                                    if (!empty($filtered)) {
                                        $record = reset($filtered);
                                        $hasilSeleksiData = $record;
                                        $hasilStatus = data_get($record, 'status', '');
                                        Log::info('HasilSeleksi status extracted', [
                                            'record' => $record,
                                            'hasil_status_raw' => $hasilStatus
                                        ]);
                                        $statusSeleksiAkhir = $this->mapStatusHasilSeleksi($hasilStatus);
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            Log::error('Error fetch hasil seleksi', ['user_id'=>$userId, 'lamaran_id'=>$lamaranId, 'error'=>$e->getMessage(), 'trace' => $e->getTraceAsString()]);
                        }

                        // 3.4 Logging final status
                        Log::info('Final statuses for application', [
                            'lamaran_id'           => $lamaranId,
                            'user_id'              => $userId,
                            'lowongan_id'          => data_get($lamaran, 'id_lowongan_pekerjaan'),
                            'berkas'               => $statusSeleksiBerkas,
                            'wawancara'            => $statusWawancara,
                            'seleksi_akhir'        => $statusSeleksiAkhir,
                            'raw_status_lamaran'   => $rawStatus,
                            'raw_status_wawancara' => data_get($interviewData, 'status'),
                            'raw_status_seleksi'   => data_get($hasilSeleksiData, 'status'),
                        ]);

                        // 3.5 Enrich lamaran
                        $enrichedLamaran['status_seleksi_berkas'] = $statusSeleksiBerkas;
                        $enrichedLamaran['status_wawancara']      = $statusWawancara;
                        $enrichedLamaran['status_seleksi_akhir']  = $statusSeleksiAkhir;

                        if ($interviewData) {
                            $enrichedLamaran['interview_date']     = data_get($interviewData, 'tanggal_wawancara');
                            $enrichedLamaran['interview_time']     = data_get($interviewData, 'tanggal_wawancara'); // Ambil dari tanggal karena waktu sudah termasuk
                            $enrichedLamaran['interview_location'] = data_get($interviewData, 'lokasi');
                            $enrichedLamaran['interview_zoom_link']= data_get($interviewData, 'link_zoom');
                            $enrichedLamaran['interview_notes']    = data_get($interviewData, 'catatan');
                            $enrichedLamaran['interview_result']   = data_get($interviewData, 'hasil');
                        }

                        if ($hasilSeleksiData) {
                            $enrichedLamaran['hasil_seleksi'] = $hasilSeleksiData;
                        }

                        $enrichedApplications[] = $enrichedLamaran;
                    }
                }

                $data['myApplications'] = $enrichedApplications;
            }

        } catch (Exception $e) {
            $data['error'] = 'Gagal memuat data dashboard: '.$e->getMessage();
        }

        return view('dashboard', $data);
    }

    /**
     * Map status lamaran to readable format
     */
    private function mapStatusLamaran($status)
    {
        $status = $status ?? '';
        switch (strtolower(trim($status))) {
            case 'pending':
                return 'Menunggu Review';
            case 'diterima':
                return 'Berkas Diterima';
            case 'ditolak':
                return 'Berkas Ditolak';
            case '':
                return 'Belum Diproses';
            default:
                return 'Status Tidak Diketahui';
        }
    }

    /**
     * Map status wawancara to readable format
     */
    private function mapStatusWawancara($status)
    {
        $status = $status ?? '';
        switch (strtolower(trim($status))) {
            case 'pending':
                return 'Wawancara Dijadwalkan';
            case 'terjadwal':
                return 'Wawancara Dijadwalkan';
            case 'diterima':
                return 'Lolos Wawancara';
            case 'ditolak':
                return 'Tidak Lolos Wawancara';
            case '':
                return 'Belum Ada Wawancara';
            default:
                return 'Status Wawancara Tidak Diketahui';
        }
    }

    /**
     * Map status hasil seleksi to readable format
     */
    private function mapStatusHasilSeleksi($status)
    {
        $status = $status ?? '';
        switch (strtolower(trim($status))) {
            case 'pending':
                return 'Menunggu Keputusan Final';
            case 'diterima':
                return 'Diterima Bekerja';
            case 'ditolak':
                return 'Tidak Diterima';
            case '':
                return 'Belum Ada Keputusan';
            default:
                return 'Status Final Tidak Diketahui';
        }
    }
}
