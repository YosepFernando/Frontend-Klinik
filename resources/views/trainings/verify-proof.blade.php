@extends('layouts.app')

@section('title', 'Verifikasi Bukti Pelatihan')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-check-circle me-2"></i>Verifikasi Bukti Pelatihan
        </h1>
        <a href="{{ route('trainings.show', $training['id_pelatihan']) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <!-- Training Info Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Informasi Pelatihan</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="text-primary mb-3">{{ $training['judul'] }}</h5>
                    <p class="mb-2">
                        <i class="fas fa-calendar text-primary me-2"></i>
                        <strong>Jadwal:</strong> 
                        {{ \Carbon\Carbon::parse($training['jadwal_pelatihan'])->format('d F Y, H:i') }} WIB
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-tag text-primary me-2"></i>
                        <strong>Jenis:</strong> 
                        <span class="badge bg-info">{{ ucfirst($training['jenis_pelatihan']) }}</span>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="alert alert-info mb-0">
                        <h6 class="mb-0">Total Bukti:</h6>
                        <h3 class="mb-0">{{ count($buktiList) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bukti List Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Bukti Pelatihan</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(count($buktiList) === 0)
                <div class="alert alert-warning text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h5>Belum Ada Bukti</h5>
                    <p class="mb-0">Belum ada peserta yang mengupload bukti untuk pelatihan ini.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="buktiTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Pegawai</th>
                                <th width="15%">Posisi</th>
                                <th width="10%">File Bukti</th>
                                <th width="20%">Keterangan</th>
                                <th width="10%">Status</th>
                                <th width="12%">Tanggal Upload</th>
                                <th width="13%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($buktiList as $index => $bukti)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $bukti['peserta_pelatihan']['pegawai']['nama_lengkap'] ?? 'N/A' }}</strong>
                                </td>
                                @php
                                    $posisiMap = [
                                        1 => 'Dokter',
                                        2 => 'Beautician',
                                        3 => 'HRD',
                                        4 => 'Front Office',
                                        5 => 'Kasir',
                                    ];
                                    $idPosisi = $bukti['peserta_pelatihan']['pegawai']['id_posisi'] ?? null;
                                    $namaPosisi = $posisiMap[$idPosisi] ?? 'N/A';
                                @endphp
                                <td>
                                    {{ $namaPosisi }}
                                </td>
                                <td class="text-center">
                                    @php
                                        $fileExtension = pathinfo($bukti['file_bukti'], PATHINFO_EXTENSION);
                                        $isPdf = strtolower($fileExtension) === 'pdf';
                                    @endphp
                                    
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary" 
                                            onclick="viewFile('{{ asset('storage/' . $bukti['file_bukti']) }}', {{ $isPdf ? 'true' : 'false' }}, '{{ $bukti['peserta_pelatihan']['pegawai']['nama_lengkap'] ?? 'N/A' }}')">
                                        <i class="fas fa-eye me-1"></i>Lihat
                                    </button>
                                    <a href="{{ asset('storage/' . $bukti['file_bukti']) }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-success"
                                       download>
                                        <i class="fas fa-download me-1"></i>
                                    </a>
                                </td>
                                <td>
                                    <small>{{ $bukti['keterangan'] ?? '-' }}</small>
                                </td>
                                <td class="text-center">
                                    @if($bukti['status_verifikasi'] === 'menunggu')
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-clock me-1"></i>Menunggu
                                        </span>
                                    @elseif($bukti['status_verifikasi'] === 'disetujui')
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Disetujui
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times me-1"></i>Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ \Carbon\Carbon::parse($bukti['created_at'])->format('d M Y, H:i') }}</small>
                                </td>
                                <td class="text-center">
                                    @if($bukti['status_verifikasi'] === 'menunggu')
                                        <div class="btn-group" role="group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-success" 
                                                    onclick="verifyBukti({{ $bukti['id_bukti_pelatihan'] }}, 'disetujui', '{{ $bukti['peserta_pelatihan']['pegawai']['nama_lengkap'] ?? 'N/A' }}')"
                                                    title="Setujui">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="verifyBukti({{ $bukti['id_bukti_pelatihan'] }}, 'ditolak', '{{ $bukti['peserta_pelatihan']['pegawai']['nama_lengkap'] ?? 'N/A' }}')"
                                                    title="Tolak">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @else
                                        <small class="text-muted">
                                            <i class="fas fa-check-double me-1"></i>Terverifikasi
                                        </small>
                                        @if($bukti['catatan_verifikasi'])
                                            <br>
                                            <button type="button" 
                                                    class="btn btn-sm btn-link p-0" 
                                                    onclick="showNote('{{ addslashes($bukti['catatan_verifikasi']) }}')">
                                                <small>Lihat Catatan</small>
                                            </button>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal for File Preview -->
<div class="modal fade" id="filePreviewModal" tabindex="-1" aria-labelledby="filePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filePreviewModalLabel">Preview Bukti Pelatihan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="imagePreviewContainer" style="display: none;">
                    <img id="modalImagePreview" src="" alt="Bukti Pelatihan" class="img-fluid" style="max-height: 70vh;">
                </div>
                <div id="pdfPreviewContainer" style="display: none;">
                    <iframe id="modalPdfPreview" src="" style="width: 100%; height: 70vh;" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .table td {
        vertical-align: middle;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function viewFile(fileUrl, isPdf, pegawaiName) {
    const modal = new bootstrap.Modal(document.getElementById('filePreviewModal'));
    const imageContainer = document.getElementById('imagePreviewContainer');
    const pdfContainer = document.getElementById('pdfPreviewContainer');
    const imagePreview = document.getElementById('modalImagePreview');
    const pdfPreview = document.getElementById('modalPdfPreview');
    
    document.getElementById('filePreviewModalLabel').textContent = 'Bukti - ' + pegawaiName;
    
    if (isPdf) {
        imageContainer.style.display = 'none';
        pdfContainer.style.display = 'block';
        pdfPreview.src = fileUrl;
    } else {
        pdfContainer.style.display = 'none';
        imageContainer.style.display = 'block';
        imagePreview.src = fileUrl;
    }
    
    modal.show();
}

function showNote(note) {
    Swal.fire({
        title: 'Catatan Verifikasi',
        text: note,
        icon: 'info',
        confirmButtonText: 'Tutup'
    });
}

function verifyBukti(buktiId, status, pegawaiName) {
    const statusText = status === 'disetujui' ? 'menyetujui' : 'menolak';
    const statusColor = status === 'disetujui' ? '#28a745' : '#dc3545';
    
    Swal.fire({
        title: 'Konfirmasi Verifikasi',
        html: `Apakah Anda yakin ingin <strong>${statusText}</strong> bukti dari <strong>${pegawaiName}</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: statusColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            if (status === 'ditolak') {
                // Jika ditolak, minta catatan
                Swal.fire({
                    title: 'Alasan Penolakan',
                    input: 'textarea',
                    inputLabel: 'Masukkan alasan penolakan',
                    inputPlaceholder: 'Tulis alasan penolakan...',
                    inputAttributes: {
                        'aria-label': 'Tulis alasan penolakan'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc3545',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Anda harus memberikan alasan penolakan!';
                        }
                    }
                }).then((inputResult) => {
                    if (inputResult.isConfirmed) {
                        submitVerification(buktiId, status, inputResult.value);
                    }
                });
            } else {
                submitVerification(buktiId, status, null);
            }
        }
    });
}

function submitVerification(buktiId, status, catatan) {
    Swal.fire({
        title: 'Memproses...',
        html: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    const formData = {
        status: status,
        catatan: catatan,
        _token: '{{ csrf_token() }}'
    };
    
    fetch(`{{ route('trainings.process-verification', ['trainingId' => $training['id_pelatihan'], 'buktiId' => '__BUKTI_ID__']) }}`.replace('__BUKTI_ID__', buktiId), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                confirmButtonText: 'OK',
                confirmButtonColor: '#28a745'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: data.message || 'Terjadi kesalahan saat memverifikasi bukti',
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan saat memverifikasi bukti',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
    });
}

// DataTable initialization (if you have DataTables library)
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $.fn.DataTable !== 'undefined' && document.getElementById('buktiTable')) {
        $('#buktiTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
            },
            "pageLength": 25,
            "order": [[6, "desc"]] // Sort by date
        });
    }
});
</script>
@endpush
@endsection
