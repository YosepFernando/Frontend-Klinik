@extends('layouts.app')

@section('title', 'Edit Pengguna')
@section('page-title', 'Edit Pengguna: ' . ($user->name ?? $user->nama_user ?? 'Unknown'))

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </a>
    <a href="{{ route('users.show', $user->id ?? $user->id_user) }}" class="btn btn-outline-info rounded-pill px-4 shadow-sm">
        <i class="fas fa-eye me-2"></i> Lihat Detail
    </a>
</div>
@endsection

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <form action="{{ route('users.update', $user->id ?? $user->id_user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        {{-- Nama User --}}
                        <div class="mb-4">
                            <label for="nama_user" class="form-label">
                                <i class="fas fa-user me-2"></i>Nama Lengkap
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('nama_user') is-invalid @enderror" 
                                   id="nama_user" 
                                   name="nama_user" 
                                   value="{{ old('nama_user', $user->nama_user ?? $user->name) }}" 
                                   placeholder="Masukkan nama lengkap"
                                   required>
                            @error('nama_user')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- No Telp --}}
                        <div class="mb-4">
                            <label for="no_telp" class="form-label">
                                <i class="fas fa-phone me-2"></i>Nomor Telepon
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">+62</span>
                                <input type="tel" 
                                       class="form-control @error('no_telp') is-invalid @enderror" 
                                       id="no_telp" 
                                       name="no_telp" 
                                       value="{{ old('no_telp', $user->no_telp ?? $user->phone) }}"
                                       placeholder="81234567890"
                                       pattern="[0-9]{10,13}"
                                       title="Nomor telepon harus 10-13 digit angka">
                            </div>
                            @error('no_telp')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Contoh: 81234567890 (tanpa angka 0 di depan)</small>
                        </div>

                        {{-- Tanggal Lahir --}}
                        <div class="mb-4">
                            <label for="tanggal_lahir" class="form-label">
                                <i class="fas fa-calendar me-2"></i>Tanggal Lahir
                                <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   class="form-control @error('tanggal_lahir') is-invalid @enderror" 
                                   id="tanggal_lahir" 
                                   name="tanggal_lahir" 
                                   value="{{ old('tanggal_lahir', $user->tanggal_lahir ?? ($user->birth_date ? $user->birth_date->format('Y-m-d') : null)) }}"
                                   max="{{ date('Y-m-d') }}">
                            @error('tanggal_lahir')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: YYYY-MM-DD</small>
                        </div>

                        <div class="alert alert-info mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle me-2"></i>
                                <div>
                                    <strong>Informasi:</strong>
                                    <ul class="mb-0 mt-1">
                                        <li>Pastikan nama lengkap sesuai dengan identitas resmi</li>
                                        <li>Format nomor telepon: 81234567890 (tanpa 0 di depan)</li>
                                        <li>Tanggal lahir harus diisi sesuai format YYYY-MM-DD</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
{{-- The CSS styles can remain unchanged --}}
@endpush
