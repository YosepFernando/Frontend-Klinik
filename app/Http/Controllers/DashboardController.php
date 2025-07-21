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

                        // 3.1 Status Seleksi Berkas
                        $rawStatus = data_get($lamaran, 'status_lamaran', '');
                        $sl        = strtolower($rawStatus);
                        $statusSeleksiBerkas = in_array($sl, ['diterima','lulus'])
                            ? 'lulus'
                            : (in_array($sl, ['ditolak','gagal']) ? 'ditolak' : 'pending');

                        // 3.2 Status Wawancara
                        $statusWawancara = null;
                        $interviewData   = null;

                        try {
                            $resp = $this->wawancaraService->getByLamaran($lamaranId);
                            Log::info('Raw Wawancara', $resp);

                            $records = data_get($resp, 'data.data', data_get($resp, 'data', []));
                            if (! empty($records) && is_array($records)) {
                                $interviewData = reset($records);
                                $hasil = strtolower(data_get($interviewData, 'hasil', ''));

                                if (in_array($hasil, ['lulus','diterima'])) {
                                    $statusWawancara = 'lulus';
                                } elseif (in_array($hasil, ['ditolak','gagal'])) {
                                    $statusWawancara = 'ditolak';
                                } else {
                                    // ada jadwal tapi belum ada hasil
                                    $statusWawancara = 'scheduled';
                                }
                            }
                        } catch (Exception $e) {
                            Log::error('Error fetch wawancara', ['lamaran_id'=>$lamaranId, 'error'=>$e->getMessage()]);
                        }

                        // 3.3 Status Seleksi Akhir
                        $statusSeleksiAkhir = 'pending';
                        $hasilSeleksiData   = null;

                        try {
                            $respSel = $this->hasilSeleksiService->getByLamaran($userId);
                            Log::info('Raw HasilSeleksi', $respSel);

                            $recordsSel = data_get($respSel, 'data.data', data_get($respSel, 'data', []));
                            if (! empty($recordsSel) && is_array($recordsSel)) {
                                // filter berdasarkan lowongan_pekerjaan
                                $lowId = data_get($lamaran, 'lowongan_pekerjaan.id_lowongan_pekerjaan');
                                $filtered = array_filter($recordsSel, function($it) use ($lowId) {
                                    return data_get($it, 'id_lowongan_pekerjaan') == $lowId;
                                });
                                $record = $filtered ? reset($filtered) : reset($recordsSel);

                                $hasilSeleksiData = $record;
                                $st = strtolower(data_get($record, 'status', ''));

                                if (in_array($st, ['diterima','lulus'])) {
                                    $statusSeleksiAkhir = 'lulus';
                                } elseif (in_array($st, ['ditolak','gagal'])) {
                                    $statusSeleksiAkhir = 'ditolak';
                                }
                            }
                        } catch (Exception $e) {
                            Log::error('Error fetch hasil seleksi', ['user_id'=>$userId, 'error'=>$e->getMessage()]);
                        }

                        // 3.4 Logging final status
                        Log::info('Final statuses', [
                            'lamaran_id'           => $lamaranId,
                            'berkas'               => $statusSeleksiBerkas,
                            'wawancara'            => $statusWawancara,
                            'seleksi_akhir'        => $statusSeleksiAkhir,
                        ]);

                        // 3.5 Enrich lamaran
                        $enrichedLamaran['status_seleksi_berkas'] = $statusSeleksiBerkas;
                        $enrichedLamaran['status_wawancara']      = $statusWawancara;
                        $enrichedLamaran['status_seleksi_akhir']  = $statusSeleksiAkhir;

                        if ($interviewData) {
                            $enrichedLamaran['interview_date']     = data_get($interviewData, 'tanggal_wawancara');
                            $enrichedLamaran['interview_time']     = data_get($interviewData, 'waktu_wawancara');
                            $enrichedLamaran['interview_location'] = data_get($interviewData, 'lokasi');
                            $enrichedLamaran['interview_zoom_link']= data_get($interviewData, 'link_zoom');
                            $enrichedLamaran['interview_notes']    = data_get($interviewData, 'catatan');
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
}
