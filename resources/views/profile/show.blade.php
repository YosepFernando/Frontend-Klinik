@extends('layouts.app')

@section('title', 'Profile Saya')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Profile Saya</h3>
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row">
                        <!-- Profile Photo -->
                        <div class="col-md-3 text-center">
                            <div class="profile-photo-section">
                                @if(!empty($profile['foto_profil']))
                                    <img src="{{ asset('storage/' . $profile['foto_profil']) }}" 
                                         alt="Profile Photo" 
                                         class="img-fluid rounded-circle mb-3" 
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center mb-3" 
                                         style="width: 150px; height: 150px;">
                                        <i class="fas fa-user text-white" style="font-size: 4rem;"></i>
                                    </div>
                                @endif
                                
                                <!-- Upload Photo Form -->
                                <form action="{{ route('profile.upload-photo') }}" method="POST" enctype="multipart/form-data" class="mt-2">
                                    @csrf
                                    <div class="form-group">
                                        <input type="file" name="foto_profil" id="foto_profil" class="form-control-file d-none" accept="image/*">
                                        <label for="foto_profil" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-camera"></i> Ganti Foto
                                        </label>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-sm d-none" id="upload-btn">
                                        <i class="fas fa-upload"></i> Upload
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Profile Information -->
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Informasi Personal</h5>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Nama Lengkap:</strong></td>
                                            <td>{{ $profile['nama_user'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $profile['email'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>No. Telepon:</strong></td>
                                            <td>{{ $profile['no_telp'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal Lahir:</strong></td>
                                            <td>{{ $profile['tanggal_lahir'] ? \Carbon\Carbon::parse($profile['tanggal_lahir'])->format('d-m-Y') : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Role:</strong></td>
                                            <td>
                                                <span class="badge badge-info">{{ ucfirst($profile['role'] ?? '-') }}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <h5>Informasi Biodata</h5>
                                    @if(isset($profile['biodata']) && $profile['biodata'])
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>NIK:</strong></td>
                                                <td>{{ $profile['biodata']['NIK'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Jenis Kelamin:</strong></td>
                                                <td>
                                                    @if(isset($profile['biodata']['jenis_kelamin']))
                                                        {{ $profile['biodata']['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Agama:</strong></td>
                                                <td>{{ $profile['biodata']['agama'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Alamat:</strong></td>
                                                <td>{{ $profile['biodata']['alamat'] ?? '-' }}</td>
                                            </tr>
                                        </table>
                                    @else
                                        <p class="text-muted">Belum ada data biodata. <a href="{{ route('profile.edit') }}">Lengkapi biodata Anda</a></p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('foto_profil').addEventListener('change', function() {
    if (this.files && this.files.length > 0) {
        document.getElementById('upload-btn').classList.remove('d-none');
    } else {
        document.getElementById('upload-btn').classList.add('d-none');
    }
});
</script>
@endsection
