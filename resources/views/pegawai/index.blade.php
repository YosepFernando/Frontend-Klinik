@extends('layouts.app')

@section('content')
<!-- Minimal Header Section -->
<div class="minimal-header mb-3">
    <div class="container-fluid">
        <div class="row align-items-center py-3">
            <div class="col-md-8">
                <h3 class="mb-1 fw-bold text-dark">
                    <i class="fas fa-users-cog me-2 text-primary"></i>Kelola Pegawai
                </h3>
                <p class="mb-0 text-muted">Kelola data karyawan dengan mudah dan efisien</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('pegawai.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus me-1"></i>Tambah Pegawai
                    </a>
                    <a href="{{ route('absensi.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-chart-line me-1"></i>Absensi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Compact Statistics Cards -->
    <div class="row mb-3">
        @php
            // Helper function to detect gender
            function detectGender($person) {
                if (is_array($person)) {
                    $person = (object) $person;
                }
                
                // Check multiple possible field names for gender with priority
                $gender = '';
                
                if (isset($person->jenis_kelamin) && $person->jenis_kelamin) {
                    $gender = $person->jenis_kelamin;
                } elseif (isset($person->user) && is_object($person->user) && isset($person->user->biodata) && is_object($person->user->biodata) && isset($person->user->biodata->jenis_kelamin)) {
                    $gender = $person->user->biodata->jenis_kelamin;
                } elseif (isset($person->user) && is_array($person->user) && isset($person->user['biodata']) && is_array($person->user['biodata']) && isset($person->user['biodata']['jenis_kelamin'])) {
                    $gender = $person->user['biodata']['jenis_kelamin'];
                } elseif (isset($person->gender)) {
                    $gender = $person->gender;
                } elseif (isset($person->sex)) {
                    $gender = $person->sex;
                }
                
                $genderNormalized = strtolower(trim($gender));
                
                // Check for male indicators
                if (in_array($genderNormalized, ['l', 'laki-laki', 'male', 'm', 'pria', 'laki', 'cowok', '1'])) {
                    return 'L';
                }
                
                // Check for female indicators
                if (in_array($genderNormalized, ['p', 'perempuan', 'female', 'f', 'wanita', 'cewe', 'cewek', '0'])) {
                    return 'P';
                }
                
                return 'UNKNOWN';
            }
            
            // Collect and filter employee data once for all cards
            $allPegawaiData = collect();
            
            // Collect all employee data first
            if (is_object($pegawai) && method_exists($pegawai, 'getCollection')) {
                $allPegawaiData = $pegawai->getCollection();
            } elseif (is_object($pegawai) && isset($pegawai->items)) {
                $allPegawaiData = collect($pegawai->items);
            } elseif (is_array($pegawai) && isset($pegawai['data'])) {
                $allPegawaiData = collect($pegawai['data']);
            } elseif (is_array($pegawai)) {
                $allPegawaiData = collect($pegawai);
            } else {
                $allPegawaiData = collect($pegawai);
            }
            
            // Filter out admin users from the count
            $pegawaiNonAdmin = $allPegawaiData->filter(function($p) {
                if (is_array($p)) {
                    $p = (object) $p;
                }
                
                // Check if user exists and is not admin
                if (isset($p->user)) {
                    $userRole = is_array($p->user) ? ($p->user['role'] ?? '') : ($p->user->role ?? '');
                    return $userRole !== 'admin';
                }
                
                return true; // Include if no user relationship (non-user employee)
            });
            
            $totalPegawai = $pegawaiNonAdmin->count();
            
            // Count male employees using helper function
            $totalLaki = $pegawaiNonAdmin->filter(function($p) {
                return detectGender($p) === 'L';
            })->count();
            
            // Count female employees using helper function
            $totalPerempuan = $pegawaiNonAdmin->filter(function($p) {
                return detectGender($p) === 'P';
            })->count();
        @endphp
        
        <div class="col-lg-3 col-md-6 mb-2">
            <div class="card compact-stats-card bg-gradient-primary border-0 shadow-sm">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0 text-white">
                                {{ $totalPegawai }}
                            </h5>
                            <small class="text-white-50">Total Pegawai</small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-users fa-lg text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-2">
            <div class="card compact-stats-card bg-gradient-primary border-0 shadow-sm">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0 text-white">
                                {{ $totalLaki }}
                            </h5>
                            <small class="text-white-50">Laki-laki</small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-male fa-lg text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-2">
            <div class="card compact-stats-card bg-gradient-primary border-0 shadow-sm">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0 text-white">
                                {{ $totalPerempuan }}
                            </h5>
                            <small class="text-white-50">Perempuan</small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-female fa-lg text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-2">
            <div class="card compact-stats-card bg-gradient-primary border-0 shadow-sm">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0 text-white">
                                @php
                                    $totalPosisi = 0;
                                    if (is_object($posisi) && method_exists($posisi, 'count')) {
                                        $totalPosisi = $posisi->count();
                                    } elseif (is_array($posisi) && isset($posisi['data'])) {
                                        $totalPosisi = count($posisi['data']);
                                    } elseif (is_array($posisi)) {
                                        $totalPosisi = count($posisi);
                                    }
                                @endphp
                                {{ $totalPosisi }}
                            </h5>
                            <small class="text-white-50">Total Posisi</small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-briefcase fa-lg text-white-50"></i>
                        </div>
            </div>
        </div>
    </div>

    <!-- Debug Information (remove after fixing) -->
            </div>
    </div>

    <!-- Compact Filter Section -->

    <!-- Compact Filter Section -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white border-0 py-2">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold text-dark">
                    <i class="fas fa-filter me-1 text-primary"></i>Filter & Pencarian
                </h6>
                <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('pegawai.index') }}" class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label form-label-sm">Posisi</label>
                        <select name="posisi_id" class="form-select form-select-sm">
                            <option value="">Semua Posisi</option>
                            @foreach($posisi as $p)
                                @php
                                    $id = is_array($p) ? ($p['id_posisi'] ?? '') : ($p->id_posisi ?? '');
                                    $nama = is_array($p) ? ($p['nama_posisi'] ?? 'Tidak ada nama') : ($p->nama_posisi ?? 'Tidak ada nama');
                                @endphp

                                @if($nama !== 'Admin')
                                    <option value="{{ $id }}" {{ request('posisi_id') == $id ? 'selected' : '' }}>
                                        {{ $nama }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm">Gender</label>
                        <select name="jenis_kelamin" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="L" {{ request('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ request('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label form-label-sm">Pencarian</label>
                        <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Cari Nama Pegawai">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label form-label-sm">&nbsp;</label>
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-undo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm modern-alert" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-2x me-3"></i>
                <div>
                    <h6 class="mb-0">Berhasil!</h6>
                    <small>{{ session('success') }}</small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm modern-alert" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fa-2x me-3"></i>
                <div>
                    <h6 class="mb-0">Terjadi Kesalahan!</h6>
                    <small>{{ session('error') }}</small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Main Content Area -->
    @if(is_array($pegawai) ? count($pegawai) > 0 : $pegawai->count() > 0)
        
        <!-- Compact Data Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-semibold text-dark">
                            <i class="fas fa-table me-1 text-primary"></i>Data Pegawai
                        </h6>
                        @php
                            $firstItem = 1;
                            $lastItem = is_array($pegawai) ? count($pegawai) : (method_exists($pegawai, 'count') ? $pegawai->count() : 0);
                            $totalItems = is_array($pegawai) ? (isset($pegawai['total']) ? $pegawai['total'] : count($pegawai)) : 
                                (method_exists($pegawai, 'total') ? $pegawai->total() : (isset($pegawai->total) ? $pegawai->total : 0));
                            
                            if (is_object($pegawai) && method_exists($pegawai, 'firstItem')) {
                                $firstItem = $pegawai->firstItem();
                            }
                            
                            if (is_object($pegawai) && method_exists($pegawai, 'lastItem')) {
                                $lastItem = $pegawai->lastItem();
                            }
                        @endphp
                        <small class="text-muted">{{ $firstItem }}-{{ $lastItem }} dari {{ $totalItems }} pegawai</small>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="btn btn-outline-primary btn-sm" onclick="exportPegawaiToPdf()">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="printData()">
                            <i class="fas fa-print"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 compact-table">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center py-2" width="4%">
                                    <i class="fas fa-hashtag"></i>
                                </th>
                                <th class="py-2" width="18%">
                                    <i class="fas fa-user me-1"></i>Pegawai
                                </th>
                                <th class="py-2" width="12%">
                                    <i class="fas fa-briefcase me-1"></i>Posisi
                                </th>
                                <th class="py-2" width="15%">
                                    <i class="fas fa-envelope me-1"></i>Email
                                </th>
                                <th class="py-2" width="12%">
                                    <i class="fas fa-phone me-1"></i>Telepon
                                </th>
                                <th class="text-center py-2" width="8%">
                                    <i class="fas fa-venus-mars me-1"></i>Jenis Kelamin
                                </th>
                                <th class="text-center py-2" width="10%">
                                    <i class="fas fa-info-circle me-1"></i>Status
                                </th>
                                <th class="text-center py-2" width="10%">
                                    <i class="fas fa-calendar me-1"></i>Bergabung
                                </th>
                                <th class="text-center py-2" width="11%">
                                    <i class="fas fa-cogs me-1"></i>Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pegawai as $index => $p)
                                @php
                                    // Pastikan $p adalah object/array dan bukan integer
                                    if (is_int($p) || is_string($p)) {
                                        continue; // Skip jika bukan object/array
                                    }
                                    
                                    // Convert array to object for consistency
                                    if (is_array($p)) {
                                        $p = (object) $p;
                                    }
                                    
                                    // Skip admin users - don't display them in the table
                                    if (isset($p->user)) {
                                        $userRole = is_array($p->user) ? ($p->user['role'] ?? '') : ($p->user->role ?? '');
                                        if ($userRole === 'admin') {
                                            continue;
                                        }
                                    }
                                @endphp
                                <tr class="employee-row">
                                    <td class="text-center py-2">
                                        @php
                                            $firstItemValue = 0;
                                            if (is_object($pegawai) && method_exists($pegawai, 'firstItem')) {
                                                $firstItemValue = $pegawai->firstItem();
                                            } elseif (isset($pegawai->firstItem)) {
                                                $firstItemValue = $pegawai->firstItem;
                                            }
                                            $firstItemValue = intval($firstItemValue);
                                            $indexValue = intval($index);
                                        @endphp
                                        <span class="badge text-dark rounded-pill px-2 py-1">
                                            {{ $firstItemValue + $indexValue }}
                                        </span>
                                    </td>
                                    <td class="py-2">
                                        <div class="d-flex align-items-center">
                                            @php
                                                $detectedGender = detectGender($p);
                                                $isLaki = $detectedGender === 'L';
                                            @endphp
                                            <div class="avatar-compact me-2 bg-gradient-secondary }}">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                            <div>
                                                @php
                                                    $nama = '';
                                                    // Cari nama dari berbagai field
                                                    if (isset($p->nama_lengkap) && $p->nama_lengkap) {
                                                        $nama = $p->nama_lengkap;
                                                    } elseif (isset($p->nama_user) && $p->nama_user) {
                                                        $nama = $p->nama_user;
                                                    } elseif (isset($p->user) && is_object($p->user) && isset($p->user->nama_user)) {
                                                        $nama = $p->user->nama_user;
                                                    } elseif (isset($p->user) && is_array($p->user) && isset($p->user['nama_user'])) {
                                                        $nama = $p->user['nama_user'];
                                                    } else {
                                                        $nama = 'Nama tidak tersedia';
                                                    }
                                                    
                                                    $nik = '';
                                                    // Cari NIK dari berbagai field
                                                    if (isset($p->NIK) && $p->NIK) {
                                                        $nik = $p->NIK;
                                                    } elseif (isset($p->user) && is_object($p->user) && isset($p->user->biodata) && is_object($p->user->biodata) && isset($p->user->biodata->NIK)) {
                                                        $nik = $p->user->biodata->NIK;
                                                    } elseif (isset($p->user) && is_array($p->user) && isset($p->user['biodata']) && is_array($p->user['biodata']) && isset($p->user['biodata']['NIK'])) {
                                                        $nik = $p->user['biodata']['NIK'];
                                                    } else {
                                                        $nik = 'NIK tidak tersedia';
                                                    }
                                                @endphp
                                                <div class="fw-semibold text-dark">{{ $nama }}</div>
                                                <small class="text-muted">
                                                    <i class="fas fa-id-card me-1"></i>{{ $nik }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-2">
                                        <span class="badge text-dark px-2 py-1">
                                            @if(is_object($p) && isset($p->posisi) && is_object($p->posisi))
                                                {{ $p->posisi->nama_posisi ?? 'Belum ditentukan' }}
                                            @elseif(is_object($p) && isset($p->posisi) && is_array($p->posisi))
                                                {{ $p->posisi['nama_posisi'] ?? 'Belum ditentukan' }}
                                            @else
                                                Belum ditentukan
                                            @endif
                                        </span>
                                    </td>
                                    <td class="py-2">
                                        @php
                                            $email = '';
                                            if (isset($p->email) && $p->email) {
                                                $email = $p->email;
                                            } elseif (isset($p->user) && is_object($p->user) && isset($p->user->email)) {
                                                $email = $p->user->email;
                                            } elseif (isset($p->user) && is_array($p->user) && isset($p->user['email'])) {
                                                $email = $p->user['email'];
                                            }
                                        @endphp
                                        @if($email)
                                            <div class="d-flex align-items-center">
                                                <small class="text-break">{{ $email }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="py-2">
                                        @php
                                            $telepon = '';
                                            if (isset($p->telepon) && $p->telepon) {
                                                $telepon = $p->telepon;
                                            } elseif (isset($p->no_telp) && $p->no_telp) {
                                                $telepon = $p->no_telp;
                                            } elseif (isset($p->user) && is_object($p->user) && isset($p->user->no_telp)) {
                                                $telepon = $p->user->no_telp;
                                            } elseif (isset($p->user) && is_array($p->user) && isset($p->user['no_telp'])) {
                                                $telepon = $p->user['no_telp'];
                                            }
                                        @endphp
                                        @if($telepon)
                                            <div class="d-flex align-items-center">
                                                <small>{{ $telepon }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center py-2">
                                        @php
                                            $detectedGender = detectGender($p);
                                            $isLaki = $detectedGender === 'L';
                                            $isPerempuan = $detectedGender === 'P';
                                            $gender = $detectedGender;
                                        @endphp
                                        <span class="badge px-2 py-1 text-dark">
                                            @if($isLaki)
                                                L
                                            @elseif($isPerempuan)
                                                P
                                            @else
                                                ❓ {{ $gender ?: 'N/A' }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-center py-2">
                                        @php
                                            $isActive = true;
                                            $statusText = 'Aktif';
                                            $statusClass = 'success';
                                            
                                            // Check if there's tanggal_keluar
                                            if (isset($p->tanggal_keluar) && $p->tanggal_keluar) {
                                                try {
                                                    $tanggalKeluar = null;
                                                    if (is_string($p->tanggal_keluar)) {
                                                        $tanggalKeluar = \Carbon\Carbon::parse($p->tanggal_keluar);
                                                    } elseif (is_object($p->tanggal_keluar) && method_exists($p->tanggal_keluar, 'format')) {
                                                        $tanggalKeluar = $p->tanggal_keluar;
                                                    }
                                                    
                                                    if ($tanggalKeluar) {
                                                        $isActive = false;
                                                        $statusText = 'Non-aktif (keluar ' . $tanggalKeluar->format('d/m/Y') . ')';
                                                        $statusClass = 'danger';
                                                    }
                                                } catch (\Exception $e) {
                                                    // If date parsing fails, keep as active
                                                }
                                            }
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }} px-2 py-1" title="{{ $statusText }}">
                                            @if($isActive)
                                                <i class="fas fa-check-circle me-1"></i>Aktif
                                            @else
                                                <i class="fas fa-times-circle me-1"></i>Non-aktif
                                            @endif
                                        </span>
                                        @if(!$isActive)
                                            <br><small class="text-muted mt-1">{{ $tanggalKeluar->format('d/m/Y') }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center py-2">
                                        @if(isset($p->tanggal_masuk) && $p->tanggal_masuk)
                                            @php
                                                $tanggalMasuk = null;
                                                try {
                                                    if (is_string($p->tanggal_masuk)) {
                                                        $tanggalMasuk = \Carbon\Carbon::parse($p->tanggal_masuk);
                                                    } elseif (is_object($p->tanggal_masuk) && method_exists($p->tanggal_masuk, 'format')) {
                                                        $tanggalMasuk = $p->tanggal_masuk;
                                                    }
                                                } catch (\Exception $e) {
                                                    $tanggalMasuk = null;
                                                }
                                            @endphp
                                            @if($tanggalMasuk)
                                                <div class="fw-semibold small">{{ $tanggalMasuk->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $tanggalMasuk->diffForHumans() }}</small>
                                            @else
                                                <span class="text-muted">Invalid</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center py-2">
                                        <div class="btn-group compact-btn-group" role="group">
                                            @if(isset($p->id_pegawai) || isset($p->id))
                                                <a href="{{ route('pegawai.show', $p->id_pegawai ?? $p->id ?? 0) }}" class="btn btn-outline-info btn-sm compact-btn" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endif
                                            @if(isset($p->id_pegawai) || isset($p->id))
                                                <a href="{{ route('pegawai.edit', $p->id_pegawai ?? $p->id ?? 0) }}" class="btn btn-outline-warning btn-sm compact-btn" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if(isset($p->id_pegawai) || isset($p->id))
                                                <button type="button" class="btn btn-outline-danger btn-sm compact-btn" title="Hapus" onclick="confirmDelete('{{ $p->id_pegawai ?? $p->id ?? 0 }}', '{{ $p->nama_lengkap ?? 'Pegawai' }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Compact Pagination - Always Show -->
            <div class="card-footer bg-light border-0 py-2">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small class="text-muted">
                            @php
                                $currentCount = is_array($pegawai) ? count($pegawai) : (method_exists($pegawai, 'count') ? $pegawai->count() : 0);
                                $firstItem = 1;
                                $lastItem = $currentCount;
                                $total = $currentCount;
                                
                                // Try to get pagination info if available
                                if (is_object($pegawai) && method_exists($pegawai, 'total')) {
                                    $total = $pegawai->total();
                                    $firstItem = $pegawai->firstItem() ?? 1;
                                    $lastItem = $pegawai->lastItem() ?? $currentCount;
                                }
                            @endphp
                            {{ $firstItem }} - {{ $lastItem }} dari {{ $total }} pegawai
                        </small>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end">
                            @if(is_object($pegawai) && method_exists($pegawai, 'links'))
                                <!-- Laravel Pagination Links -->
                                {{ $pegawai->appends(request()->query())->links() }}
                            @else
                                <!-- Manual Pagination -->
                                @php
                                    $currentPage = request('page', 1);
                                    $hasNext = $total > ($currentPage * 10); // Check if there's more data
                                    $hasPrev = $currentPage > 1;
                                @endphp
                                
                                <nav aria-label="Pagination">
                                    <ul class="pagination pagination-sm mb-0">
                                        @if($hasPrev)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}">
                                                    <i class="fas fa-chevron-left"></i> Prev
                                                </a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link">
                                                    <i class="fas fa-chevron-left"></i> Prev
                                                </span>
                                            </li>
                                        @endif
                                        
                                        <!-- Show current page and a few around it -->
                                        @for($i = max(1, $currentPage - 1); $i <= $currentPage + 1; $i++)
                                            <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                            </li>
                                        @endfor
                                        
                                        @if($hasNext)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}">
                                                    Next <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link">
                                                    Next <i class="fas fa-chevron-right"></i>
                                                </span>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Simple Empty State -->
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-users fa-3x text-muted opacity-50"></i>
                </div>
                <h5 class="text-muted mb-2">Belum Ada Data Pegawai</h5>
                <p class="text-muted mb-3">Mulai dengan menambahkan pegawai pertama</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('pegawai.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>Tambah Pegawai
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-1"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0 py-2">
                <h6 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-1"></i>Konfirmasi Hapus
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3">
                <div class="text-center">
                    <i class="fas fa-user-times fa-2x text-danger mb-2"></i>
                    <h6>Hapus pegawai:</h6>
                    <strong id="employeeName" class="text-danger"></strong>
                    <p class="text-muted mt-2 small">Data tidak dapat dikembalikan.</p>
                </div>
            </div>
            <div class="modal-footer border-0 py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Batal
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash me-1"></i>Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Minimal Header */
.minimal-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

/* Compact Statistics Cards */
.compact-stats-card {
    border-radius: 8px;
    transition: none;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-cards {
    background: linear-gradient(135deg, #ffffff 0%, #ffffff 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.bg-gradient-pink {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.bg-gradient-light {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.bg-gradient-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
}

/* Compact Cards */
.card {
    border-radius: 8px;
    overflow: hidden;
}

/* Compact Table */
.compact-table {
    font-size: 0.875rem;
}

.compact-table thead th {
    border: none;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    font-weight: 600;
    font-size: 0.8rem;
}

.compact-table tbody td {
    border-color: rgba(0,0,0,0.05);
    vertical-align: middle;
    font-size: 0.875rem;
}

.employee-row {
    transition: none;
}

/* Compact Avatar */
.avatar-compact {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

/* Compact Buttons */
.compact-btn-group .compact-btn {
    border-radius: 4px;
    margin: 0 1px;
    padding: 4px 8px;
    transition: none;
    border-width: 1px;
    min-width: 32px;
}

/* Form Elements */
.form-label-sm {
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.form-select-sm,
.form-control-sm {
    border-radius: 6px;
    border: 1px solid #ced4da;
    font-size: 0.875rem;
}

/* Badge */
.badge {
    font-size: 0.75rem;
    font-weight: 500;
    border-radius: 6px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .minimal-header .col-md-4 {
        text-align: center !important;
        margin-top: 1rem;
    }
    
    .compact-stats-card {
        margin-bottom: 0.5rem;
    }
    
    .compact-btn-group {
        flex-direction: column;
    }
    
    .compact-btn-group .compact-btn {
        margin: 1px 0;
        width: 100%;
    }
    
    .table-responsive {
        font-size: 0.75rem;
    }
}

/* Remove unnecessary animations */
.card {
    animation: none;
}

/* Pagination Styling */
.pagination .page-link {
    border-radius: 6px;
    margin: 0 1px;
    border: none;
    color: #667eea;
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}

.pagination .page-link:hover {
    background-color: #667eea;
    color: white;
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}
</style>
@endpush

@push('scripts')
<script>
function confirmDelete(id, name) {
    document.getElementById('employeeName').textContent = name;
    document.getElementById('deleteForm').action = `/pegawai/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// PDF Export function for Pegawai
function exportPegawaiToPdf() {
    // Get current filters
    const urlParams = new URLSearchParams(window.location.search);
    const filters = {
        posisi_id: urlParams.get('posisi_id') || '',
        jenis_kelamin: urlParams.get('jenis_kelamin') || '',
        search: urlParams.get('search') || ''
    };
    
    // Build export URL with current filters
    const exportUrl = new URL('{{ route("pegawai.export-pdf") }}', window.location.origin);
    Object.keys(filters).forEach(key => {
        if (filters[key]) {
            exportUrl.searchParams.append(key, filters[key]);
        }
    });
    
    // Open in new window to download
    window.open(exportUrl.toString(), '_blank');
}

function printData() {
    window.print();
}

// Add loading animation
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (alert) {
                new bootstrap.Alert(alert).close();
            }
        });
    }, 5000);
});
</script>
@endpush
