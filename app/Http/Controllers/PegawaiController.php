<?php

namespace App\Http\Controllers;

use App\Services\PegawaiService;
use App\Services\PosisiService;
use App\Services\UserService;
use App\Services\GajiService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PegawaiController extends Controller
{
    protected $pegawaiService;
    protected $posisiService;
    protected $userService;
    protected $gajiService;
    
    /**
     * Constructor untuk menginisialisasi service
     */
    public function __construct(
        PegawaiService $pegawaiService,
        PosisiService $posisiService,
        UserService $userService,
        GajiService $gajiService
    ) {
        $this->pegawaiService = $pegawaiService;
        $this->posisiService = $posisiService;
        $this->userService = $userService;
        $this->gajiService = $gajiService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Persiapkan parameter untuk API
        $params = [];
        
        // Filter by position
        if ($request->filled('posisi_id')) {
            $params['posisi_id'] = $request->posisi_id;
        }
        
        // Filter by gender
        if ($request->filled('jenis_kelamin')) {
            $params['jenis_kelamin'] = $request->jenis_kelamin;
        }
        
        // Search by name or email
        if ($request->filled('search')) {
            $params['search'] = $request->search;
        }
        
        // Tambahkan parameter untuk pagination
        $params['page'] = $request->input('page', 1);
        $params['per_page'] = 15;
        
        // Ambil data dari API
        $response = $this->pegawaiService->getAll($params);
         // Periksa apakah respons berhasil
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data pegawai: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }

        // Siapkan data untuk view
        $responseData = $response['data'] ?? [];
        
        // Transform data pegawai untuk memastikan compatibility dengan view
        $pegawaiData = [];
        if (isset($responseData['data']) && is_array($responseData['data'])) {
            foreach ($responseData['data'] as $item) {
                if (is_array($item)) {
                    $pegawaiData[] = (object) $item;
                } else {
                    $pegawaiData[] = $item;
                }
            }
        } else {
            // Fallback jika data tidak dalam format pagination
            $pegawaiData = $responseData;
        }

        // Create Laravel paginator from API pagination data
        $pegawai = new \Illuminate\Pagination\LengthAwarePaginator(
            $pegawaiData,
            $responseData['total'] ?? count($pegawaiData),
            $responseData['per_page'] ?? 15,
            $responseData['current_page'] ?? 1,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
        
        // Ambil data posisi dari API
        $posisiResponse = $this->posisiService->getAll();
        $posisi = $posisiResponse['status'] === 'success' ? $posisiResponse['data'] : [];
        
        return view('pegawai.index', compact('pegawai', 'posisi'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil data posisi dari API
        $posisiResponse = $this->posisiService->getAll();
        $posisi = $posisiResponse['status'] === 'success' ? $posisiResponse['data'] : [];
        
        // Ambil data user yang belum memiliki pegawai dari API
        $usersResponse = $this->userService->getUsersWithoutPegawai();
        $users = $usersResponse['status'] === 'success' ? $usersResponse['data'] : [];
        
        return view('pegawai.create', compact('posisi', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'nullable|exists:users,id',
            'nama_lengkap' => 'required|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'NIK' => 'nullable|string|max:16',
            'id_posisi' => 'required',
            'agama' => 'nullable|string|max:20',
            'tanggal_masuk' => 'required|date',
        ]);

        // Kirim data ke API
        $response = $this->pegawaiService->store($request->all());
        
        // Periksa respons dari API
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('pegawai.index')
                ->with('success', 'Data pegawai berhasil ditambahkan.');
        } else {
            return back()->withInput()
                ->with('error', 'Gagal menambahkan data pegawai: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Ambil detail pegawai dari API
        $response = $this->pegawaiService->getById($id);
        
        // Periksa respons dari API
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data pegawai: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        $pegawaiData = $response['data'] ?? null;
        
        if (!$pegawaiData) {
            return back()->with('error', 'Data pegawai tidak ditemukan');
        }
        
        // Transform array to object for compatibility with view
        if (is_array($pegawaiData)) {
            $pegawai = (object) $pegawaiData;
            
            // Transform nested relationships if they exist
            if (isset($pegawai->posisi) && is_array($pegawai->posisi)) {
                $pegawai->posisi = (object) $pegawai->posisi;
            }
            
            if (isset($pegawai->user) && is_array($pegawai->user)) {
                $pegawai->user = (object) $pegawai->user;
            }
            
            // Handle date fields - convert to Carbon instances if they're strings
            if (isset($pegawai->tanggal_lahir) && is_string($pegawai->tanggal_lahir)) {
                try {
                    $pegawai->tanggal_lahir = \Carbon\Carbon::parse($pegawai->tanggal_lahir);
                } catch (\Exception $e) {
                    $pegawai->tanggal_lahir = null;
                }
            }
            
            if (isset($pegawai->tanggal_masuk) && is_string($pegawai->tanggal_masuk)) {
                try {
                    $pegawai->tanggal_masuk = \Carbon\Carbon::parse($pegawai->tanggal_masuk);
                } catch (\Exception $e) {
                    $pegawai->tanggal_masuk = null;
                }
            }
            
            if (isset($pegawai->tanggal_keluar) && is_string($pegawai->tanggal_keluar)) {
                try {
                    $pegawai->tanggal_keluar = \Carbon\Carbon::parse($pegawai->tanggal_keluar);
                } catch (\Exception $e) {
                    $pegawai->tanggal_keluar = null;
                }
            }
            
            // Handle absensi collection if it exists
            if (isset($pegawai->absensi) && is_array($pegawai->absensi)) {
                $absensiCollection = collect();
                foreach ($pegawai->absensi as $absensi) {
                    $absensiCollection->push(is_array($absensi) ? (object) $absensi : $absensi);
                }
                $pegawai->absensi = $absensiCollection;
            }
        } else {
            $pegawai = $pegawaiData;
        }
        
        return view('pegawai.show', compact('pegawai'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Ambil detail pegawai dari API
        $response = $this->pegawaiService->getById($id);
        
        // Periksa respons dari API
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data pegawai: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        $pegawaiData = $response['data'] ?? null;
        
        if (!$pegawaiData) {
            return back()->with('error', 'Data pegawai tidak ditemukan');
        }
        
        // Transform array to object for compatibility with view
        if (is_array($pegawaiData)) {
            $pegawai = (object) $pegawaiData;
            
            // Transform nested relationships if they exist
            if (isset($pegawai->posisi) && is_array($pegawai->posisi)) {
                $pegawai->posisi = (object) $pegawai->posisi;
            }
            
            if (isset($pegawai->user) && is_array($pegawai->user)) {
                $pegawai->user = (object) $pegawai->user;
            }
        } else {
            $pegawai = $pegawaiData;
        }
        
        // Ambil data posisi dari API
        $posisiResponse = $this->posisiService->getAll();
        $posisi = $posisiResponse['status'] === 'success' ? $posisiResponse['data'] : [];
        
        // Ambil data user yang belum memiliki pegawai dari API (termasuk user yang terkait dengan pegawai ini)
        $usersResponse = $this->userService->getUsersWithoutPegawai();
        $users = $usersResponse['status'] === 'success' ? $usersResponse['data'] : [];
        
        // Tambahkan user yang terkait dengan pegawai ini jika belum ada
        if (isset($pegawai->id_user) && $pegawai->id_user) {
            $userFound = false;
            foreach ($users as $user) {
                $userId = is_array($user) ? $user['id'] : $user->id;
                if ($userId == $pegawai->id_user) {
                    $userFound = true;
                    break;
                }
            }
            
            if (!$userFound) {
                $userResponse = $this->userService->getById($pegawai->id_user);
                if (isset($userResponse['status']) && $userResponse['status'] === 'success' && isset($userResponse['data'])) {
                    $users[] = $userResponse['data'];
                }
            }
        }
        
        return view('pegawai.edit', compact('pegawai', 'posisi', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_user' => 'nullable|exists:users,id',
            'nama_lengkap' => 'required|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:laki-laki,perempuan,L,P',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'NIK' => 'nullable|string|max:16',
            'id_posisi' => 'required',
            'agama' => 'nullable|string|max:20',
            'tanggal_masuk' => 'required|date',
            'tanggal_keluar' => 'nullable|date|after_or_equal:tanggal_masuk',
        ]);

        // Kirim data ke API
        $response = $this->pegawaiService->update($id, $request->all());
        
        // Periksa respons dari API
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('pegawai.index')
                ->with('success', 'Data pegawai berhasil diperbarui.');
        } else {
            return back()->withInput()
                ->with('error', 'Gagal memperbarui data pegawai: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Kirim permintaan hapus ke API
        $response = $this->pegawaiService->delete($id);
        
        // Periksa respons dari API
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('pegawai.index')
                ->with('success', 'Data pegawai berhasil dihapus.');
        } else {
            return back()->with('error', 'Gagal menghapus data pegawai: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
    }

    /**
     * Export data pegawai ke PDF dengan data lengkap dari API
     */
    public function exportPdf(Request $request)
    {
        try {
            \Log::info('Mulai proses export PDF Pegawai', [
                'request_method' => $request->method(),
                'request_params' => $request->all()
            ]);
            
            // Get filters from request
            $filters = [
                'posisi_id' => $request->posisi_id,
                'jenis_kelamin' => $request->jenis_kelamin,
                'search' => $request->search
            ];

            // Remove empty filters
            $filters = array_filter($filters, function($value) {
                return !is_null($value) && $value !== '';
            });

            \Log::info('Filter PDF Export Pegawai:', $filters);

            // Get pegawai data from API using the correct method
            $response = $this->pegawaiService->getAll($filters);
            
            \Log::info('Respon API untuk PDF Pegawai:', [
                'has_status' => isset($response['status']),
                'status' => $response['status'] ?? 'tidak_ada_status',
                'has_data' => isset($response['data']),
                'message' => $response['message'] ?? 'tidak_ada_pesan',
                'response_keys' => array_keys($response)
            ]);
            
            // Check for authentication error first
            if (isset($response['message'])) {
                if ($response['message'] === 'Unauthenticated.' || 
                    strpos($response['message'], 'Unauthorized') !== false ||
                    strpos($response['message'], 'Token') !== false) {
                    \Log::error('Error autentikasi API untuk PDF Pegawai');
                    return redirect()->back()->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
                }
            }
            
            if (!isset($response['status']) || !in_array($response['status'], ['success', 'sukses'])) {
                \Log::warning('API tidak mengembalikan status sukses untuk Pegawai:', $response);
                // If there's an error but we still want to generate empty PDF
                if (isset($response['message']) && 
                    (strpos($response['message'], 'tidak ditemukan') !== false || 
                     strpos($response['message'], 'not found') !== false ||
                     strpos($response['message'], 'No data') !== false ||
                     strpos($response['message'], 'Data tidak ada') !== false)) {
                    \Log::info('Data pegawai tidak ditemukan dari API, membuat PDF kosong');
                    $response = ['status' => 'success', 'data' => []];
                } else {
                    $errorMsg = $response['message'] ?? 'Terjadi kesalahan saat mengambil data dari API';
                    return redirect()->back()->with('error', 'Gagal mengambil data pegawai: ' . $errorMsg);
                }
            }

            // Handle nested data structure
            $pegawaiData = [];
            if (isset($response['data']['data'])) {
                $pegawaiData = $response['data']['data'];
            } elseif (isset($response['data'])) {
                $pegawaiData = is_array($response['data']) ? $response['data'] : [];
            }

            \Log::info('Data Pegawai untuk PDF:', [
                'jumlah_data' => count($pegawaiData),
                'sample_data' => count($pegawaiData) > 0 ? array_keys($pegawaiData[0]) : [],
                'first_record' => count($pegawaiData) > 0 ? $pegawaiData[0] : null
            ]);

            // Enrich data pegawai sesuai struktur API klinik yang benar
            if (!empty($pegawaiData)) {
                try {
                    foreach ($pegawaiData as $index => &$item) {
                        \Log::info("Processing pegawai item $index", [
                            'available_keys' => is_array($item) ? array_keys($item) : 'not_array',
                            'has_user' => isset($item['user']),
                            'has_posisi' => isset($item['posisi'])
                        ]);
                        
                        // Pastikan struktur data sesuai API klinik
                        $namaPegawai = null;
                        $gajiPokok = 0;
                        $posisi = null;
                        
                        // Ambil nama dari struktur API klinik yang benar
                        if (isset($item['nama_lengkap'])) {
                            $namaPegawai = $item['nama_lengkap'];
                        } elseif (isset($item['user']['nama_user'])) {
                            $namaPegawai = $item['user']['nama_user'];
                        } elseif (isset($item['nama'])) {
                            $namaPegawai = $item['nama'];
                        }
                        
                        // Ambil posisi dari struktur API klinik yang benar
                        if (isset($item['posisi']['nama_posisi'])) {
                            $posisi = $item['posisi']['nama_posisi'];
                        } elseif (isset($item['nama_posisi'])) {
                            $posisi = $item['nama_posisi'];
                        }
                        
                        // API klinik tidak memiliki gaji_pokok di tabel pegawai
                        // Gaji pokok ada di tabel gaji/posisi
                        if (isset($item['posisi']['gaji_pokok'])) {
                            $gajiPokok = floatval($item['posisi']['gaji_pokok']);
                        } elseif (isset($item['gaji_pokok'])) {
                            $gajiPokok = floatval($item['gaji_pokok']);
                        }
                        
                        // Jika gaji pokok masih 0, coba ambil dari data gaji terbaru
                        if ($gajiPokok == 0 && isset($item['id_pegawai'])) {
                            try {
                                // Gunakan GajiService untuk mengambil gaji terbaru
                                $gajiResponse = $this->gajiService->getByPegawai($item['id_pegawai']);
                                if (isset($gajiResponse['status']) && in_array($gajiResponse['status'], ['success', 'sukses'])) {
                                    $latestGaji = $gajiResponse['data'][0] ?? null;
                                    if ($latestGaji && isset($latestGaji['gaji_pokok'])) {
                                        $gajiPokok = floatval($latestGaji['gaji_pokok']);
                                        \Log::info("Berhasil mengambil gaji pokok dari data gaji untuk id_pegawai {$item['id_pegawai']}: {$gajiPokok}");
                                    }
                                }
                            } catch (\Exception $e) {
                                \Log::warning("Error mengambil gaji pokok untuk id_pegawai {$item['id_pegawai']}: " . $e->getMessage());
                            }
                        }
                        
                        // Set fallback jika masih kosong
                        if (!$namaPegawai) $namaPegawai = 'Nama Tidak Tersedia';
                        if (!$posisi) $posisi = 'Posisi Tidak Diketahui';
                        
                        // Pastikan struktur data konsisten untuk template (sesuai API klinik)
                        if (is_array($item)) {
                            // Field utama sesuai API klinik
                            $item['nama_lengkap'] = $namaPegawai;
                            $item['nama'] = $namaPegawai; // untuk kompatibilitas template
                            $item['gaji_pokok'] = $gajiPokok;
                            
                            // Pastikan email dan telepon ada
                            $item['email'] = $item['email'] ?? $item['user']['email'] ?? 'Email Tidak Tersedia';
                            $item['telepon'] = $item['telepon'] ?? $item['phone'] ?? $item['no_hp'] ?? 'Telepon Tidak Tersedia';
                            
                            // Pastikan jenis kelamin ada
                            $item['jenis_kelamin'] = $item['jenis_kelamin'] ?? 'Tidak Diketahui';
                            
                            // Pastikan status ada
                            $item['status'] = isset($item['tanggal_keluar']) && $item['tanggal_keluar'] ? 'nonaktif' : 'aktif';
                            
                            // Pastikan struktur posisi ada untuk kompatibilitas
                            if (!isset($item['posisi'])) {
                                $item['posisi'] = [];
                            }
                            $item['posisi']['nama_posisi'] = $posisi;
                            
                            // Format tanggal masuk jika ada
                            if (isset($item['tanggal_masuk']) && !empty($item['tanggal_masuk'])) {
                                try {
                                    $item['tanggal_masuk'] = \Carbon\Carbon::parse($item['tanggal_masuk'])->format('Y-m-d');
                                } catch (\Exception $e) {
                                    \Log::warning("Error parsing tanggal_masuk untuk pegawai {$namaPegawai}: " . $e->getMessage());
                                }
                            }
                        }
                        
                        \Log::info("Pegawai item processed", [
                            'nama_lengkap' => $namaPegawai,
                            'posisi' => $posisi,
                            'gaji_pokok' => $gajiPokok,
                            'status' => $item['status'] ?? 'unknown'
                        ]);
                    }
                    unset($item); // Break reference
                    
                    \Log::info('Data enrichment pegawai selesai', [
                        'total_records_processed' => count($pegawaiData),
                        'sample_enriched_data' => count($pegawaiData) > 0 ? [
                            'nama_lengkap' => $pegawaiData[0]['nama_lengkap'] ?? 'not_set',
                            'gaji_pokok' => $pegawaiData[0]['gaji_pokok'] ?? 0,
                            'status' => $pegawaiData[0]['status'] ?? 'unknown'
                        ] : null
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('Error saat enrich data pegawai: ' . $e->getMessage());
                }
            }

            // If no data, still generate PDF with empty message
            if (empty($pegawaiData)) {
                $pegawaiData = [];
                \Log::warning('Tidak ada data pegawai ditemukan dari API', ['filters' => $filters]);
            }
            
            // If filtering by posisi, get posisi name
            $namaPosisi = null;
            if (isset($filters['posisi_id'])) {
                try {
                    // Coba ambil nama posisi dari service jika ada
                    if (method_exists($this, 'posisiService') && $this->posisiService) {
                        $posisiResponse = $this->posisiService->getPosisiById($filters['posisi_id']);
                        if (isset($posisiResponse['status']) && in_array($posisiResponse['status'], ['success', 'sukses'])) {
                            $namaPosisi = $posisiResponse['data']['nama_posisi'] ?? null;
                            if ($namaPosisi) {
                                $filters['posisi'] = $namaPosisi;
                                $filters['nama_posisi'] = $namaPosisi;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Gagal mengambil nama posisi untuk PDF:', ['error' => $e->getMessage()]);
                }
            }

            // Prepare data for PDF
            $user = auth_user();
            $data = [
                'pegawai' => $pegawaiData,
                'filters' => $filters,
                'tanggal_cetak' => now(),
                'total_data' => count($pegawaiData),
                'user_info' => [
                    'nama' => $user->name ?? 'Administrator',
                    'role' => $user->role ?? 'user'
                ]
            ];

            \Log::info('Memulai generate PDF Pegawai dengan data:', [
                'jumlah_pegawai' => count($pegawaiData),
                'ada_filter' => !empty($filters),
                'nama_posisi' => $namaPosisi
            ]);

            // Generate PDF
            $pdf = Pdf::loadView('pdf.pegawai-report', $data);
            $pdf->setPaper('A4', 'landscape');

            // Set filename berdasarkan filter
            $namaFile = 'laporan_pegawai';
            if ($namaPosisi) {
                $namaFile .= '_' . str_replace(' ', '_', strtolower($namaPosisi));
            }
            if (isset($filters['jenis_kelamin'])) {
                $namaFile .= '_' . $filters['jenis_kelamin'];
            }
            $namaFile .= '_' . date('Y-m-d_H-i-s') . '.pdf';

            \Log::info('PDF Pegawai berhasil dibuat:', [
                'nama_file' => $namaFile,
                'total_data' => count($pegawaiData)
            ]);

            return $pdf->download($namaFile);

        } catch (\Exception $e) {
            \Log::error('Error saat membuat PDF Pegawai:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat membuat laporan PDF: ' . $e->getMessage());
        }
    }
}
