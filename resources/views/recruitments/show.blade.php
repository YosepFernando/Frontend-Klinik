@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $recruitment->position ?? 'N/A' }}</h4>
                    <div>
                        @if(is_admin() || is_hrd())
                            <a href="{{ route('recruitments.manage-applications', $recruitment->id ?? 0) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-users"></i> Kelola Aplikasi
                            </a>
                            <a href="{{ route('recruitments.edit', $recruitment->id ?? 0) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif
                        <a href="{{ route('recruitments.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-flex flex-wrap gap-2">
                                @if(isset($recruitment->is_active) && $recruitment->is_active)
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-check-circle"></i> Lowongan Terbuka
                                    </span>
                                @else
                                    <span class="badge bg-danger fs-6">
                                        <i class="fas fa-times-circle"></i> Lowongan Ditutup
                                    </span>
                                @endif
                                <span class="badge bg-info fs-6">{{ $recruitment->employment_type_display ?? 'N/A' }}</span>
                                <span class="badge bg-primary fs-6">{{ $recruitment->slots ?? 0 }} Posisi</span>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-1"><strong>Deadline Lamaran:</strong></p>
                            <p class="text-{{ isset($recruitment->application_deadline) && method_exists($recruitment->application_deadline, 'isPast') && $recruitment->application_deadline->isPast() ? 'danger' : 'success' }}">
                                <i class="fas fa-calendar"></i> {{ isset($recruitment->application_deadline) ? $recruitment->application_deadline->format('d F Y') : 'N/A' }}
                                @if(isset($recruitment->application_deadline) && method_exists($recruitment->application_deadline, 'isPast') && $recruitment->application_deadline->isPast())
                                    (Sudah Lewat)
                                @else
                                    @if(isset($recruitment->application_deadline) && method_exists($recruitment->application_deadline, 'diffForHumans'))
                                        ({{ $recruitment->application_deadline->diffForHumans() }})
                                    @endif
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="border-bottom pb-2 mb-3">Deskripsi Pekerjaan</h5>
                            <div class="mb-4">
                                {!! nl2br(e($recruitment->description ?? '')) !!}
                            </div>

                            <h5 class="border-bottom pb-2 mb-3">Persyaratan</h5>
                            <div class="mb-4">
                                {!! nl2br(e($recruitment->requirements ?? '')) !!}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Informasi Lowongan</h6>

                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td class="fw-bold">Posisi:</td>
                                            <td>{{ $recruitment->position ?? 'N/A' }}</td>
                                        </tr>
                                        @if(isset($recruitment->posisi) && $recruitment->posisi)
                                        <tr>
                                            <td class="fw-bold">Posisi Master:</td>
                                            <td>{{ $recruitment->posisi->nama_posisi ?? 'N/A' }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td class="fw-bold">Tipe:</td>
                                            <td>{{ $recruitment->employment_type_display ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Slots:</td>
                                            <td>{{ $recruitment->slots ?? 0 }} orang</td>
                                        </tr>
                                        @if(isset($recruitment->age_min) || isset($recruitment->age_max))
                                        <tr>
                                            <td class="fw-bold">Rentang Usia:</td>
                                            <td>{{ $recruitment->age_range ?? 'N/A' }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td class="fw-bold">Gaji:</td>
                                            <td>{{ $recruitment->salary_range ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Status:</td>
                                            <td>
                                                @if(isset($recruitment->status) && $recruitment->status === 'open')
                                                    <span class="badge bg-success">Buka</span>
                                                @else
                                                    <span class="badge bg-danger">Tutup</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Deadline:</td>
                                            <td>{{ isset($recruitment->application_deadline) ? $recruitment->application_deadline->format('d/m/Y') : 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            @if(method_exists($recruitment, 'isOpen') && $recruitment->isOpen() && is_pelanggan())
                                @if(method_exists($recruitment, 'getUserApplication') && ($userApplication = $recruitment->getUserApplication(auth()->id())))
                                    <div class="card mt-3">
                                        <div class="card-body text-center bg-gradient-info text-white">
                                            <h6>Anda sudah melamar untuk posisi ini</h6>
                                            <p class="mb-3">Pantau status lamaran Anda di halaman berikut</p>
                                            <a href="{{ route('recruitments.application-status', $recruitment->id ?? 0) }}" class="btn btn-light btn-lg">
                                                <i class="fas fa-chart-line text-info"></i> Lihat Status Lamaran
                                            </a>
                                            <small class="d-block mt-2 opacity-75">Tanggal melamar: {{ isset($userApplication->created_at) ? $userApplication->created_at->format('d M Y H:i') : 'N/A' }}</small>
                                        </div>
                                    </div>
                                @else
                                    <div class="card mt-3">
                                        <div class="card-body text-center bg-gradient-success text-white">
                                            <h6>Tertarik dengan posisi ini?</h6>
                                            <p class="text-black mb-3">Kirim lamaran Anda sekarang dan bergabung dengan tim kami!</p>
                                            <a href="{{ route('recruitments.apply.form', $recruitment->id ?? 0) }}" class="btn btn-light btn-lg">
                                                <i class="fas fa-paper-plane text-success"></i> Lamar Sekarang
                                            </a>
                                            <small class="d-block mt-2 opacity-75">Tim HRD akan menghubungi Anda segera</small>
                                        </div>
                                    </div>
                                @endif
                            @elseif(method_exists($recruitment, 'isOpen') && !$recruitment->isOpen() && is_pelanggan())
                                <div class="card mt-3">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Lowongan Tidak Tersedia</h6>
                                        <p class="text-muted small">Lowongan ini sudah ditutup atau melewati deadline.</p>
                                        <button class="btn btn-secondary" disabled>
                                            <i class="fas fa-times"></i> Tidak Dapat Melamar
                                        </button>
                                    </div>
                                </div>
                            @endif

                            @if(is_admin() || is_hrd())
                                <div class="card mt-3">
                                    <div class="card-body">
                                        <h6>Manajemen Aplikasi</h6>
                                        <p class="text-muted small">Kelola dan review aplikasi untuk lowongan ini</p>
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('recruitments.manage-applications', $recruitment->id ?? 0) }}" class="btn btn-primary">
                                                <i class="fas fa-users"></i> Kelola Aplikasi
                                                @if(isset($recruitment->applications) && $recruitment->applications->count() > 0)
                                                    <span class="badge bg-light text-primary">{{ $recruitment->applications->count() }}</span>
                                                @endif
                                            </a>
                                        </div>

                                        @if(isset($recruitment->applications) && $recruitment->applications->count() > 0)
                                            <div class="row mt-3 text-center">
                                                <div class="col-3">
                                                    <div class="border rounded p-2">
                                                        <div class="fw-bold text-primary">{{ isset($recruitment->applications) ? $recruitment->applications->count() : 0 }}</div>
                                                        <small>Total</small>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="border rounded p-2">
                                                        <div class="fw-bold text-warning">{{ isset($recruitment->applications) ? $recruitment->applications->where('document_status', 'pending')->count() : 0 }}</div>
                                                        <small>Review</small>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="border rounded p-2">
                                                        <div class="fw-bold text-info">{{ isset($recruitment->applications) ? $recruitment->applications->where('interview_status', 'scheduled')->count() : 0 }}</div>
                                                        <small>Interview</small>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="border rounded p-2">
                                                        <div class="fw-bold text-success">{{ isset($recruitment->applications) ? $recruitment->applications->where('final_status', 'accepted')->count() : 0 }}</div>
                                                        <small>Diterima</small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Riwayat</h6>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-bold" style="width: 20%;">Dibuat:</td>
                                    <td>{{ isset($recruitment->created_at) ? $recruitment->created_at->format('d F Y H:i') : 'N/A' }} WIB</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Terakhir Diupdate:</td>
                                    <td>{{ isset($recruitment->updated_at) ? $recruitment->updated_at->format('d F Y H:i') : 'N/A' }} WIB</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if(is_admin() || is_hrd())
                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('recruitments.edit', $recruitment->id ?? 0) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Lowongan
                            </a>

                            <!-- Delete buttons -->
                            <button type="button" class="btn btn-success me-2" onclick="deleteLowongan({{ $recruitment->id ?? 0 }}, 'soft')">
                                <i class="fas fa-archive"></i> Hapus Biasa
                            </button>
                            <button type="button" class="btn btn-danger" onclick="deleteLowongan({{ $recruitment->id ?? 0 }}, 'force')">
                                <i class="fas fa-trash"></i> Hapus Permanen
                            </button>
                        </div>

                            <!-- Original dropdown (alternative) -->
                            <!--
                            <div class="btn-group">
                                <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="deleteDropdown">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="deleteDropdown">
                                    <li>
                                        <button class="dropdown-item" type="button" onclick="deleteLowongan({{ $recruitment->id ?? 0 }}, 'soft')">
                                            <i class="fas fa-archive text-warning"></i> Soft Delete
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger" type="button" onclick="deleteLowongan({{ $recruitment->id ?? 0 }}, 'force')">
                                            <i class="fas fa-trash text-danger"></i> Force Delete (Permanen)
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            -->
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Konfigurasi API untuk port 8002 - FORCE OVERRIDE
const API_BASE_URL = 'http://localhost:8002/api/public'; // Hard-coded untuk memastikan port 8002 dengan prefix /public
const API_TOKEN = '{{ session("api_token") }}';

console.log('=== KONFIGURASI API (Port 8002) ===');
console.log('API Base URL:', API_BASE_URL);
console.log('Token tersedia:', API_TOKEN ? 'Ya' : 'Tidak');
console.log('Port yang digunakan:', '8002');
console.log('====================================');

/**
 * Hapus lowongan menggunakan API di port 8002
 * @param {number} id - ID Lowongan
 * @param {string} type - Jenis hapus: 'soft' atau 'force'
 */
function deleteLowongan(id, type = 'soft') {
    // Debug informasi
    console.log('=== DEBUG INFO HAPUS LOWONGAN ===');
    console.log('ID Lowongan:', id);
    console.log('Jenis Hapus:', type);
    console.log('API Base URL:', API_BASE_URL);
    console.log('API Token:', API_TOKEN ? 'Ada' : 'Tidak ada');
    console.log('=================================');

    // Periksa apakah token API ada
    if (!API_TOKEN) {
        Swal.fire({
            title: 'Error!',
            text: 'Token autentikasi tidak ditemukan. Silakan login ulang.',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
        return;
    }

    // Pesan konfirmasi
    const confirmMessages = {
        soft: {
            title: 'Konfirmasi Hapus Biasa',
            text: 'Lowongan akan dihapus (soft delete) dan dapat dipulihkan kembali. Lanjutkan?',
            icon: 'warning',
            confirmButtonText: 'Ya, Hapus'
        },
        force: {
            title: 'Konfirmasi Hapus Permanen',
            text: 'PERINGATAN: Lowongan akan dihapus PERMANEN beserta semua data terkait (lamaran, hasil seleksi, dll). Aksi ini TIDAK DAPAT DIBATALKAN!',
            icon: 'error',
            confirmButtonText: 'Ya, Hapus Permanen'
        }
    };

    const config = confirmMessages[type];

    // Tampilkan dialog konfirmasi
    Swal.fire({
        title: config.title,
        text: config.text,
        icon: config.icon,
        showCancelButton: true,
        confirmButtonColor: type === 'force' ? '#dc3545' : '#f39c12',
        cancelButtonColor: '#6c757d',
        confirmButtonText: config.confirmButtonText,
        cancelButtonText: 'Batal',
        reverseButtons: true,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading
            Swal.fire({
                title: 'Menghapus Lowongan...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Tentukan endpoint API dengan prefix /public sesuai route - FORCE PORT 8002
            const endpoint = type === 'force'
                ? `http://localhost:8002/api/public/lowongan-pekerjaan/${id}/force`
                : `http://localhost:8002/api/public/lowongan-pekerjaan/${id}`;

            console.log('Endpoint yang akan dipanggil (Port 8002):', endpoint);
            console.log('Menggunakan prefix /public sesuai route API backend');

            // Panggil API
            fetch(endpoint, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${API_TOKEN}`,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Status Response:', response.status);
                console.log('Response URL:', response.url);

                // Tangani berbagai status response
                if (!response.ok) {
                    if (response.status === 401) {
                        throw new Error('Token autentikasi tidak valid. Silakan login ulang.');
                    } else if (response.status === 403) {
                        throw new Error('Anda tidak memiliki izin untuk menghapus lowongan ini.');
                    } else if (response.status === 404) {
                        throw new Error(`Endpoint tidak ditemukan: ${endpoint}\n\nKemungkinan penyebab:\n1. API server tidak berjalan di port 8002\n2. Endpoint route belum terdaftar\n3. ID lowongan tidak ditemukan\n\nPastikan API server berjalan: php artisan serve --port=8002`);
                    } else if (response.status === 422) {
                        return response.json().then(errorData => {
                            throw new Error(errorData.message || 'Data tidak valid atau lowongan memiliki data terkait.');
                        });
                    } else {
                        throw new Error(`HTTP Error: ${response.status} - ${response.statusText}`);
                    }
                }
                return response.json();
            })
            .then(data => {
                console.log('Response Data:', data);

                if (data.status === 'success' || data.success === true) {
                    // Berhasil
                    Swal.fire({
                        title: 'Berhasil!',
                        text: data.message || 'Lowongan berhasil dihapus',
                        icon: 'success',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        // Redirect ke halaman index
                        window.location.href = '{{ route("recruitments.index") }}';
                    });
                } else if (data.status === 'error') {
                    // API mengembalikan error dengan detail
                    let errorMsg = data.message || 'Gagal menghapus lowongan';

                    // Jika ada informasi detail error (seperti data terkait)
                    if (data.errors && data.errors.has_lamaran) {
                        errorMsg += `\n\nDetail:\n• Lowongan memiliki ${data.errors.total_lamaran || 0} lamaran\n• Gunakan "Hapus Permanen" jika tetap ingin menghapus`;
                    }

                    throw new Error(errorMsg);
                } else {
                    throw new Error(data.message || 'Gagal menghapus lowongan');
                }
            })
            .catch(error => {
                console.error('Error Detail:', error);

                // Penanganan error
                let errorMessage = 'Terjadi kesalahan saat menghapus lowongan';

                if (error.message) {
                    errorMessage = error.message;
                } else if (typeof error === 'string') {
                    errorMessage = error;
                }

                Swal.fire({
                    title: 'Gagal!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            });
        }
    });
}

/**
 * Test koneksi API pada port 8002
 */
function testApiConnection() {
    const testUrl = `http://localhost:8002/api/public/lowongan-pekerjaan`;

    console.log('Testing API connection to port 8002 with /public prefix:', testUrl);

    Swal.fire({
        title: 'Test Koneksi API',
        text: 'Menguji koneksi ke API server pada port 8002...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(testUrl, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${API_TOKEN}`,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Test Response Status (Port 8002):', response.status);
        console.log('Test Response URL:', response.url);

        if (response.ok) {
            return response.json().then(data => {
                Swal.fire({
                    title: 'Koneksi Berhasil!',
                    html: `
                        <div class="text-start">
                            <p><strong>Status:</strong> ${response.status} OK</p>
                            <p><strong>Port:</strong> 8002</p>
                            <p><strong>Endpoint:</strong> ${testUrl}</p>
                            <p><strong>Token:</strong> ${API_TOKEN ? 'Valid' : 'Tidak ada'}</p>
                            <p><strong>Data ditemukan:</strong> ${data.data?.total || data.data?.data?.length || 0} lowongan</p>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonColor: '#28a745'
                });
            });
        } else {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
    })
    .catch(error => {
        console.error('Test Connection Error (Port 8002):', error);

        let errorDetail = '';
        if (error.message.includes('Failed to fetch')) {
            errorDetail = 'Server API tidak dapat diakses. Pastikan server berjalan di http://localhost:8002';
        } else {
            errorDetail = error.message;
        }

        Swal.fire({
            title: 'Koneksi Gagal!',
            html: `
                <div class="text-start">
                    <p><strong>Error:</strong> ${errorDetail}</p>
                    <p><strong>Port:</strong> 8002</p>
                    <p><strong>Endpoint:</strong> ${testUrl}</p>
                    <p><strong>Token:</strong> ${API_TOKEN ? 'Ada' : 'Tidak ada'}</p>
                    <hr>
                    <small><strong>Troubleshooting:</strong><br>
                    1. Pastikan API server berjalan: <code>php artisan serve --port=8002</code><br>
                    2. Periksa firewall atau proxy<br>
                    3. Coba refresh halaman untuk memuat ulang token</small>
                </div>
            `,
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
    });
}

/**
 * Tampilkan informasi detail penghapusan
 */
function showDeleteInfo() {
    Swal.fire({
        title: 'Jenis Penghapusan',
        html: `
            <div class="text-start">
                <h6 class="text-warning"><i class="fas fa-archive"></i> Hapus Biasa (Soft Delete)</h6>
                <p class="small mb-3">• Lowongan akan disembunyikan dari daftar<br>
                   • Data masih tersimpan di database<br>
                   • Dapat dipulihkan kembali jika diperlukan<br>
                   • Aman untuk lowongan dengan data terkait</p>

                <h6 class="text-danger"><i class="fas fa-trash"></i> Hapus Permanen (Force Delete)</h6>
                <p class="small mb-0">• Lowongan dihapus PERMANEN dari database<br>
                   • Semua data terkait juga akan dihapus<br>
                   • TIDAK DAPAT dipulihkan kembali<br>
                   • Gunakan dengan SANGAT HATI-HATI</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Mengerti',
        confirmButtonColor: '#007bff'
    });
}

/**
 * Fungsi test untuk memverifikasi button berfungsi
 */
function testButton() {
    Swal.fire({
        title: 'Test Button',
        html: `
            <div class="text-start">
                <p><strong>API URL yang digunakan:</strong> ${API_BASE_URL}</p>
                <p><strong>Token tersedia:</strong> ${API_TOKEN ? 'Ya' : 'Tidak'}</p>
                <p><strong>Port API:</strong> 8002</p>
            </div>
        `,
        icon: 'success',
        confirmButtonText: 'OK'
    });
}

/**
 * Inisialisasi halaman saat DOM dimuat
 */
document.addEventListener('DOMContentLoaded', function() {
    // Debug info saat halaman dimuat
    console.log('✅ Halaman dimuat dengan konfigurasi:');
    console.log('   API URL:', API_BASE_URL);
    console.log('   Token status:', API_TOKEN ? 'Available' : 'Not available');
    console.log('   Target port: 8002');

    console.log('✅ Fungsionalitas hapus telah diinisialisasi');
});
</script>

<!-- Tombol bantuan tambahan untuk opsi delete -->
@if(is_admin() || is_hrd())
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
    <div class="d-flex flex-column gap-2">
        <button class="btn btn-info btn-sm rounded-pill" onclick="showDeleteInfo()" title="Info Penghapusan">
            <i class="fas fa-question-circle"></i> Info Hapus
        </button>
    </div>
</div>
@endif
@endpush
