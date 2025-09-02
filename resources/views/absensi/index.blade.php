@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-user-clock me-2"></i>Sistem Absensi Karyawan
                            </h4>
                            <small class="opacity-75">Management absensi dan kehadiran karyawan</small>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            @php
                                $user = auth_user(); // Menggunakan helper function auth_user() sebagai pengganti auth()->user()
                                $today = \Carbon\Carbon::today();
                                $pegawai = null;
                                
                                // Cek data pegawai dari session
                                if (session()->has('api_user')) {
                                    $apiUser = session('api_user');
                                    $pegawai = is_array($apiUser) ? $apiUser : null;
                                }
                                
                                // Debug: Log todayStatus untuk debugging
                                \Log::info('Index Page Button Logic Debug', [
                                    'todayStatus_available' => isset($todayStatus),
                                    'todayStatus_data' => $todayStatus ?? 'not_set',
                                    'pegawai_available' => $pegawai !== null,
                                    'is_admin' => is_admin(),
                                    'is_hrd' => is_hrd()
                                ]);
                                
                                // Inisialisasi variabel status berdasarkan data today status dari API
                                $hasCheckedIn = false;
                                $hasCheckedOut = false;
                                $canCheckIn = true;
                                $canCheckOut = false;
                                
                                // Gunakan data todayStatus yang dikirim dari controller untuk akurasi yang lebih baik
                                if (isset($todayStatus) && is_array($todayStatus)) {
                                    $hasCheckedIn = $todayStatus['has_checked_in'] ?? false;
                                    $hasCheckedOut = $todayStatus['has_checked_out'] ?? false;
                                    $canCheckIn = !$hasCheckedIn;
                                    $canCheckOut = $hasCheckedIn && !$hasCheckedOut;
                                    
                                    \Log::info('Using API todayStatus for button logic', [
                                        'has_checked_in' => $hasCheckedIn,
                                        'has_checked_out' => $hasCheckedOut,
                                        'can_check_in' => $canCheckIn,
                                        'can_check_out' => $canCheckOut
                                    ]);
                                } else {
                                    // Fallback: cek dari data absensi collection jika todayStatus tidak ada
                                    if (isset($absensi) && $absensi->count() > 0) {
                                        foreach ($absensi as $a) {
                                            $tanggal = is_array($a) ? ($a['tanggal_absensi'] ?? '') : ($a->tanggal_absensi ?? '');
                                            if ($tanggal == $today->format('Y-m-d')) {
                                                $hasCheckedIn = true;
                                                $canCheckIn = false;
                                                
                                                // Check if has checked out
                                                $jamKeluar = is_array($a) ? ($a['jam_keluar'] ?? null) : ($a->jam_keluar ?? null);
                                                if ($jamKeluar) {
                                                    $hasCheckedOut = true;
                                                    $canCheckOut = false;
                                                } else {
                                                    $canCheckOut = true;
                                                }
                                                break;
                                            }
                                        }
                                    }
                                    
                                    \Log::info('Using fallback absensi collection for button logic', [
                                        'has_checked_in' => $hasCheckedIn,
                                        'has_checked_out' => $hasCheckedOut,
                                        'can_check_in' => $canCheckIn,
                                        'can_check_out' => $canCheckOut,
                                        'absensi_count' => isset($absensi) ? $absensi->count() : 0
                                    ]);
                                }
                            @endphp
                            
                            @if($pegawai)
                                @if($canCheckIn && !$hasCheckedIn)
                                    <!-- Check-in button and absence dropdown -->
                                    <div class="dropdown">
                                        <!-- <button class="btn btn-warning dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-exclamation-triangle"></i> Lapor Tidak Hadir
                                        </button> -->
                                        <div class="dropdown-menu">
                                            <form action="{{ route('absensi.submit-absence') }}" method="POST" class="px-3 py-2">
                                                @csrf
                                                <div class="mb-2">
                                                    <select name="status" class="form-select form-select-sm" required>
                                                        <option value="">Pilih Status</option>
                                                        <option value="Sakit">Sakit</option>
                                                        <option value="Izin">Izin</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <textarea name="keterangan" class="form-control form-control-sm" placeholder="Alasan..." rows="2" required></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-sm btn-warning w-100">Kirim Laporan</button>
                                            </form>
                                        </div>
                                    </div>
                                    <a href="{{ route('absensi.create') }}" class="btn btn-success">
                                        <i class="fas fa-clock"></i> Check In
                                    </a>
                                @elseif($hasCheckedIn && $canCheckOut && !$hasCheckedOut)
                                    <!-- Check-out button -->
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary fs-6 px-3 py-2">
                                            <i class="fas fa-clock"></i> Sudah Check In
                                        </span>
                                        <button type="button" class="btn btn-danger" onclick="showCheckOutModal()" data-absensi-id="">
                                            <i class="fas fa-sign-out-alt"></i> Check Out
                                        </button>
                                    </div>
                                @elseif($hasCheckedIn && $hasCheckedOut)
                                    <!-- Already completed for today -->
                                    <span class="badge bg-success fs-6 px-3 py-2">
                                        <i class="fas fa-check"></i> Absensi Hari Ini Selesai
                                    </span>
                                @endif
                                
                                <!-- Refresh Status Button (for real-time update) -->
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshAttendanceStatus()" title="Refresh Status">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            @endif
                            
                            @if(is_admin() || is_hrd())
                                <a href="{{ route('pegawai.index') }}" class="btn btn-info">
                                    <i class="fas fa-users"></i> Kelola Pegawai
                                </a>
                                <!-- <a href="{{ route('absensi.report') }}" class="btn btn-success">
                                    <i class="fas fa-chart-line"></i> Laporan
                                </a> -->
                                <!-- Button untuk Download Rekap Bulanan -->
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#monthlyReportModal">
                                    <i class="fas fa-calendar-alt"></i> Rekap Bulanan
                                </button>
                            @endif
                            
                            <!-- PDF Export Button for all users -->
                            @if(!is_admin() && !is_hrd())
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="exportToPdf()">
                                    <i class="fas fa-file-pdf me-1"></i>Download PDF
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- View Toggle Buttons -->
                    <div class="d-flex justify-content-end mb-3">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary active" id="cardViewBtn" onclick="switchView('card')">
                                <i class="fas fa-th-large me-1"></i> Tampilan Kartu
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="tableViewBtn" onclick="switchView('table')">
                                <i class="fas fa-table me-1"></i> Tampilan Tabel
                            </button>
                        </div>
                    </div>

                    <!-- Filters -->
                    @if(is_admin() || is_hrd())
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-filter me-2"></i>Filter & Pencarian Data
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('absensi.index') }}" class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small">Karyawan</label>
                                        <select name="id_user" class="form-select">
                                            <option value="">Semua Karyawan</option>
                                            @foreach($users as $user)
                                                @php
                                                    // Menentukan ID berdasarkan struktur data
                                                    $userId = '';
                                                    if (is_object($user)) {
                                                        // Prioritaskan id_user dari relasi user jika ada
                                                        if (isset($user->user) && is_object($user->user)) {
                                                            $userId = $user->user->id_user ?? '';
                                                        } else {
                                                            $userId = $user->id_user ?? $user->id ?? '';
                                                        }
                                                    } elseif (is_array($user)) {
                                                        // Prioritaskan id_user dari relasi user jika ada
                                                        if (isset($user['user']) && is_array($user['user'])) {
                                                            $userId = $user['user']['id_user'] ?? '';
                                                        } else {
                                                            $userId = $user['id_user'] ?? $user['id'] ?? '';
                                                        }
                                                    }
                                                    
                                                    // Menentukan nama berdasarkan struktur data
                                                    $userName = 'Tidak ada nama';
                                                    if (is_object($user)) {
                                                        // Mencoba mendapatkan nama dari berbagai kemungkinan properti
                                                        if (!empty($user->nama_lengkap)) {
                                                            $userName = $user->nama_lengkap;
                                                        } elseif (isset($user->user) && is_object($user->user) && !empty($user->user->nama_user)) {
                                                            $userName = $user->user->nama_user;
                                                        } elseif (isset($user->user) && is_object($user->user) && !empty($user->user->name)) {
                                                            $userName = $user->user->name;
                                                        } elseif (!empty($user->name)) {
                                                            $userName = $user->name;
                                                        } elseif (!empty($user->nama_user)) {
                                                            $userName = $user->nama_user;
                                                        }
                                                    } elseif (is_array($user)) {
                                                        // Mencoba mendapatkan nama dari berbagai kemungkinan properti
                                                        if (!empty($user['nama_lengkap'])) {
                                                            $userName = $user['nama_lengkap'];
                                                        } elseif (isset($user['user']) && is_array($user['user']) && !empty($user['user']['nama_user'])) {
                                                            $userName = $user['user']['nama_user'];
                                                        } elseif (isset($user['user']) && is_array($user['user']) && !empty($user['user']['name'])) {
                                                            $userName = $user['user']['name'];
                                                        } elseif (!empty($user['name'])) {
                                                            $userName = $user['name'];
                                                        } elseif (!empty($user['nama_user'])) {
                                                            $userName = $user['nama_user'];
                                                        }
                                                    }
                                                    
                                                    // Menentukan role berdasarkan struktur data
                                                    $userRole = '';
                                                    if (is_object($user)) {
                                                        if (isset($user->user) && is_object($user->user)) {
                                                            $userRole = $user->user->role ?? '';
                                                        } else {
                                                            $userRole = $user->role ?? '';
                                                        }
                                                    } elseif (is_array($user)) {
                                                        if (isset($user['user']) && is_array($user['user'])) {
                                                            $userRole = $user['user']['role'] ?? '';
                                                        } else {
                                                            $userRole = $user['role'] ?? '';
                                                        }
                                                    }
                                                    
                                                    // Log untuk debugging
                                                    \Log::debug('Filter User Data', [
                                                        'user' => $user,
                                                        'userId' => $userId,
                                                        'userName' => $userName,
                                                        'userRole' => $userRole
                                                    ]);
                                                @endphp
                                                <option value="{{ $userId }}" {{ request('id_user') == $userId ? 'selected' : '' }}>
                                                    {{ $userName }} {{ !empty($userRole) ? '('.ucfirst($userRole).')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="">Semua Status</option>
                                            <option value="Hadir" {{ request('status') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                            <option value="Terlambat" {{ request('status') == 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                                            <option value="Sakit" {{ request('status') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                            <option value="Izin" {{ request('status') == 'Izin' ? 'selected' : '' }}>Izin</option>
                                            <option value="Tidak Hadir" {{ request('status') == 'Tidak Hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Tanggal</label>
                                        <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Bulan</label>
                                        <select name="bulan" class="form-select">
                                            <option value="">Bulan</option>
                                            @for($i = 1; $i <= 12; $i++)
                                                <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Tahun</label>
                                        <select name="tahun" class="form-select">
                                            <option value="">Tahun</option>
                                            @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                                <option value="{{ $i }}" {{ request('tahun') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <div class="btn-group w-100">
                                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <a href="{{ route('absensi.index') }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-undo"></i>
                                            </a>
                                        </div>
                                    </div>
                                </form>
                                
                                <!-- PDF Export Button -->
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-success btn-sm" onclick="exportToPdf()">
                                        <i class="fas fa-file-pdf me-2"></i>Download PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($absensi->count() > 0)
                        <!-- Summary Stats for Admin/HRD -->
                        @if(is_admin() || is_hrd())
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="card-title">Hadir</h6>
                                                    <h4 class="mb-0">{{ $absensi->where('status', 'Hadir')->count() }}</h4>
                                                </div>
                                                <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="card-title">Terlambat</h6>
                                                    <h4 class="mb-0">{{ $absensi->where('status', 'Terlambat')->count() }}</h4>
                                                </div>
                                                <i class="fas fa-clock fa-2x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="card-title">Izin/Sakit</h6>
                                                    <h4 class="mb-0">{{ $absensi->whereIn('status', ['Sakit', 'Izin'])->count() }}</h4>
                                                </div>
                                                <i class="fas fa-user-md fa-2x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="card-title">Tidak Hadir</h6>
                                                    <h4 class="mb-0">{{ $absensi->where('status', 'Tidak Hadir')->count() }}</h4>
                                                </div>
                                                <i class="fas fa-times-circle fa-2x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Absensi Cards Grid -->
                        <div id="cardView" class="row">
                            @if(isset($absensi) && count($absensi) > 0)
                                @foreach($absensi as $item)
                                    <div class="col-lg-6 col-xl-4 mb-4">
                                        <div class="card h-100 shadow-sm absensi-card">
                                        <!-- Card Header with Date and Status -->
                                        @php
                                            $itemStatus = 'Hadir'; // Default status
                                            if (is_object($item)) {
                                                $itemStatus = $item->status ?? 'Hadir';
                                            } elseif (is_array($item)) {
                                                $itemStatus = $item['status'] ?? 'Hadir';
                                            } elseif (is_int($item) || is_numeric($item)) {
                                                // Handle case when $item is an integer
                                                $itemStatus = 'Hadir'; // Default value when $item is an integer
                                            }
                                            
                                            $headerClass = 'bg-light text-dark';
                                            if ($itemStatus === 'Hadir') {
                                                $headerClass = 'bg-success text-white';
                                            } elseif ($itemStatus === 'Terlambat') {
                                                $headerClass = 'bg-warning text-dark';
                                            } elseif ($itemStatus === 'Sakit') {
                                                $headerClass = 'bg-info text-white';
                                            } elseif ($itemStatus === 'Izin') {
                                                $headerClass = 'bg-secondary text-white';
                                            } elseif ($itemStatus === 'Tidak Hadir') {
                                                $headerClass = 'bg-danger text-white';
                                            }
                                        @endphp
                                        <div class="card-header d-flex justify-content-between align-items-center {{ $headerClass }}">
                                            <div>
                                                @php
                                                    $tanggalFormatted = 'Tidak tersedia';
                                                    $hariFormatted = '';
                                                    
                                                    if (is_object($item) && isset($item->tanggal_absensi)) {
                                                        if (is_object($item->tanggal_absensi) && method_exists($item->tanggal_absensi, 'format')) {
                                                            $tanggalFormatted = $item->tanggal_absensi->format('d M Y');
                                                            $hariFormatted = $item->tanggal_absensi->format('l');
                                                        } elseif (is_string($item->tanggal_absensi)) {
                                                            $tanggalObj = \Carbon\Carbon::parse($item->tanggal_absensi);
                                                            $tanggalFormatted = $tanggalObj->format('d M Y');
                                                            $hariFormatted = $tanggalObj->format('l');
                                                        }
                                                    } elseif (is_array($item) && isset($item['tanggal_absensi'])) {
                                                        $tanggalObj = \Carbon\Carbon::parse($item['tanggal']);
                                                        $tanggalFormatted = $tanggalObj->format('d M Y');
                                                        $hariFormatted = $tanggalObj->format('l');
                                                    }
                                                @endphp
                                                <h6 class="mb-0">{{ $tanggalFormatted }}</h6>
                                                <small class="opacity-75">{{ $hariFormatted }}</small>
                                            </div>
                                            <span class="badge bg-white text-dark">{{ $itemStatus }}</span>
                                        </div>

                                        <div class="card-body">
                                            @if(is_admin() || is_hrd())
                                                <!-- Employee Info -->
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="avatar-circle me-3">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        @php
                                                            $userName = 'Tidak tersedia';
                                                            $userRole = '';
                                                            $posisiName = '';
                                                            
                                                            if (is_object($item)) {
                                                                if (isset($item->pegawai) && is_object($item->pegawai)) {
                                                                    // Menggunakan nama_lengkap dari pegawai jika tersedia
                                                                    $userName = $item->pegawai->nama_lengkap ?? $userName;
                                                                    
                                                                    if (isset($item->pegawai->user) && is_object($item->pegawai->user)) {
                                                                        // Fallback ke nama user jika nama_lengkap tidak tersedia
                                                                        if (empty($userName) || $userName === 'Tidak tersedia') {
                                                                            $userName = $item->pegawai->user->nama_user ?? $item->pegawai->user->name ?? 'Tidak tersedia';
                                                                        }
                                                                        $userRole = $item->pegawai->user->role ?? '';
                                                                    }
                                                                    
                                                                    if (isset($item->pegawai->posisi) && is_object($item->pegawai->posisi)) {
                                                                        $posisiName = $item->pegawai->posisi->nama_posisi ?? '';
                                                                    }
                                                                }
                                                            } elseif (is_array($item)) {
                                                                if (isset($item['pegawai']) && is_array($item['pegawai'])) {
                                                                    // Menggunakan nama_lengkap dari pegawai jika tersedia
                                                                    $userName = $item['pegawai']['nama_lengkap'] ?? $userName;
                                                                    
                                                                    if (isset($item['pegawai']['user']) && is_array($item['pegawai']['user'])) {
                                                                        // Fallback ke nama user jika nama_lengkap tidak tersedia
                                                                        if (empty($userName) || $userName === 'Tidak tersedia') {
                                                                            $userName = $item['pegawai']['user']['nama_user'] ?? $item['pegawai']['user']['name'] ?? 'Tidak tersedia';
                                                                        }
                                                                        $userRole = $item['pegawai']['user']['role'] ?? '';
                                                                    }
                                                                    
                                                                    if (isset($item['pegawai']['posisi']) && is_array($item['pegawai']['posisi'])) {
                                                                        $posisiName = $item['pegawai']['posisi']['nama_posisi'] ?? '';
                                                                    }
                                                                }
                                                            }
                                                        @endphp
                                                        <strong>{{ $userName }}</strong>
                                                        @if(!empty($userRole))
                                                            <small class="text-muted d-block">{{ ucfirst($userRole) }}</small>
                                                        @endif
                                                        @if(!empty($posisiName))
                                                            <small class="text-muted d-block">{{ $posisiName }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Time Info -->
                                            <div class="row text-center mb-3">
                                                <div class="col-6">
                                                    <div class="border-end">
                                                        <div class="text-muted small">Masuk</div>
                                                        @php
                                                            $hasJamMasuk = false;
                                                            $jamMasukFormatted = '-';
                                                                             if (is_object($item)) {
                                                if (isset($item->jam_masuk) && !empty($item->jam_masuk)) {
                                                    $hasJamMasuk = true;
                                                    if (is_object($item->jam_masuk) && method_exists($item->jam_masuk, 'format')) {
                                                        $jamMasukFormatted = $item->jam_masuk->format('H:i');
                                                    } elseif (is_string($item->jam_masuk)) {
                                                        $jamMasukFormatted = \Carbon\Carbon::parse($item->jam_masuk)->format('H:i');
                                                    }
                                                }
                                            } elseif (is_array($item)) {
                                                if (isset($item['jam_masuk']) && !empty($item['jam_masuk'])) {
                                                    $hasJamMasuk = true;
                                                    $jamMasukFormatted = \Carbon\Carbon::parse($item['jam_masuk'])->format('H:i');
                                                }
                                            }
                                                        @endphp
                                                        <div class="fw-bold {{ $itemStatus === 'Terlambat' ? 'text-warning' : 'text-success' }}">
                                                            @if($hasJamMasuk)
                                                                {{ $jamMasukFormatted }}
                                                                @if($itemStatus === 'Terlambat')
                                                                    <small class="d-block">
                                                                        <i class="fas fa-clock"></i> Terlambat
                                                                    </small>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-muted small">Keluar</div>
                                                    @php
                                                        $hasJamKeluar = false;
                                                        $jamKeluarFormatted = '-';
                                                        
                                                        if (is_object($item)) {
                                                            if (isset($item->jam_keluar) && !empty($item->jam_keluar)) {
                                                                $hasJamKeluar = true;
                                                                if (is_object($item->jam_keluar) && method_exists($item->jam_keluar, 'format')) {
                                                                    $jamKeluarFormatted = $item->jam_keluar->format('H:i');
                                                                } elseif (is_string($item->jam_keluar)) {
                                                                    $jamKeluarFormatted = \Carbon\Carbon::parse($item->jam_keluar)->format('H:i');
                                                                }
                                                            }
                                                        } elseif (is_array($item)) {
                                                            if (isset($item['jam_keluar']) && !empty($item['jam_keluar'])) {
                                                                $hasJamKeluar = true;
                                                                $jamKeluarFormatted = \Carbon\Carbon::parse($item['jam_keluar'])->format('H:i');
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="fw-bold">
                                                        @if($hasJamKeluar)
                                                            <span class="text-success">{{ $jamKeluarFormatted }}</span>
                                                        @else
                                                            @if($hasJamMasuk && ($itemStatus === 'Hadir' || $itemStatus === 'Terlambat'))
                                                                <span class="text-warning">
                                                                    <i class="fas fa-clock"></i> Belum Checkout
                                                                </span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Location Info -->
                                            @php
                                                $alamatMasuk = '';
                                                if (is_object($item) && isset($item->alamat_masuk)) {
                                                    $alamatMasuk = $item->alamat_masuk;
                                                } elseif (is_array($item) && isset($item['alamat_masuk'])) {
                                                    $alamatMasuk = $item['alamat_masuk'];
                                                }
                                                
                                                $durasiKerja = '';
                                                if (is_object($item) && isset($item->durasi_kerja)) {
                                                    $durasiKerja = $item->durasi_kerja;
                                                } elseif (is_array($item) && isset($item['durasi_kerja'])) {
                                                    $durasiKerja = $item['durasi_kerja'];
                                                }
                                                
                                                $catatan = '';
                                                if (is_object($item) && isset($item->catatan)) {
                                                    $catatan = $item->catatan;
                                                } elseif (is_array($item) && isset($item['catatan'])) {
                                                    $catatan = $item['catatan'];
                                                }
                                            @endphp
                                            
                                            <!-- @if(!empty($alamatMasuk))
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        {{ Str::limit($alamatMasuk, 50) }}
                                                    </small>
                                                </div>
                                            @endif -->

                                            <!-- Work Duration -->
                                            @if($hasJamMasuk && $hasJamKeluar)
                                                @php
                                                    $durasiKerjaCalculated = '';
                                                    try {
                                                        if (is_object($item)) {
                                                            $jamMasukTime = null;
                                                            $jamKeluarTime = null;
                                                            
                                                            if (is_object($item->jam_masuk) && method_exists($item->jam_masuk, 'format')) {
                                                                $jamMasukTime = $item->jam_masuk;
                                                            } elseif (is_string($item->jam_masuk)) {
                                                                $jamMasukTime = \Carbon\Carbon::parse($item->jam_masuk);
                                                            }
                                                            
                                                            if (is_object($item->jam_keluar) && method_exists($item->jam_keluar, 'format')) {
                                                                $jamKeluarTime = $item->jam_keluar;
                                                            } elseif (is_string($item->jam_keluar)) {
                                                                $jamKeluarTime = \Carbon\Carbon::parse($item->jam_keluar);
                                                            }
                                                        } elseif (is_array($item)) {
                                                            $jamMasukTime = \Carbon\Carbon::parse($item['jam_masuk']);
                                                            $jamKeluarTime = \Carbon\Carbon::parse($item['jam_keluar']);
                                                        }
                                                        
                                                        if ($jamMasukTime && $jamKeluarTime) {
                                                            $diff = $jamKeluarTime->diff($jamMasukTime);
                                                            $hours = $diff->h;
                                                            $minutes = $diff->i;
                                                            $durasiKerjaCalculated = sprintf('%02d:%02d', $hours, $minutes);
                                                        }
                                                    } catch (\Exception $e) {
                                                        $durasiKerjaCalculated = '';
                                                    }
                                                @endphp
                                                @if(!empty($durasiKerjaCalculated))
                                                    <div class="mb-2">
                                                        <small class="text-info">
                                                            <i class="fas fa-hourglass-half"></i>
                                                            Durasi: {{ $durasiKerjaCalculated }}
                                                        </small>
                                                    </div>
                                                @elseif(!empty($durasiKerja))
                                                    <div class="mb-2">
                                                        <small class="text-info">
                                                            <i class="fas fa-hourglass-half"></i>
                                                            Durasi: {{ $durasiKerja }}
                                                        </small>
                                                    </div>
                                                @endif
                                            @endif

                                            <!-- Notes -->
                                            <!-- @if(!empty($catatan))
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-comment"></i>
                                                        {{ $catatan }}
                                                    </small>
                                                </div>
                                            @endif -->

                                            <!-- Actions -->
                                            <div class="d-flex gap-1 mt-3">
                                                @php
                                                    $itemId = null;
                                                    if (is_object($item)) {
                                                        $itemId = $item->id_absensi ?? $item->id ?? null;
                                                    } elseif (is_array($item)) {
                                                        $itemId = $item['id_absensi'] ?? $item['id'] ?? null;
                                                    } elseif (is_numeric($item)) {
                                                        $itemId = $item;
                                                    }
                                                @endphp
                                                
                                                @if($itemId !== null)
                                                    <a href="{{ route('absensi.show', $itemId) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                                        <i class="fas fa-eye"></i> Detail
                                                    </a>
                                                    @if(is_admin() || is_hrd())
                                                        <a href="{{ route('absensi.admin-edit', $itemId) }}" class="btn btn-sm btn-outline-warning" title="Edit Absensi">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        @if(is_admin())
                                                            <form action="{{ route('absensi.destroy', $itemId) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                        title="Hapus Absensi"
                                                                        onclick="return confirm('Yakin ingin menghapus data absensi ini?')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @else
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body text-center py-5">
                                            <div class="mb-4">
                                                <i class="fas fa-calendar-times fa-4x text-muted opacity-50"></i>
                                            </div>
                                            <h5 class="text-muted">Belum Ada Data Absensi</h5>
                                            <p class="text-muted mb-4">
                                                Tidak ada data absensi yang ditemukan untuk periode yang dipilih.
                                                @if(!is_admin() && !is_hrd())
                                                    Silakan lakukan absensi terlebih dahulu.
                                                @endif
                                            </p>
                                            @if(!is_admin() && !is_hrd())
                                                <a href="{{ route('absensi.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-clock me-2"></i>Absen Sekarang
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Absensi Table View (Hidden by default) -->
                        <div id="tableView" class="row" style="display: none;">
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="border-0 rounded-start ps-3">Tanggal</th>
                                                        @if(is_admin() || is_hrd())
                                                            <th class="border-0">Karyawan</th>
                                                        @endif
                                                        <th class="border-0">Status</th>
                                                        <th class="border-0">Jam Masuk / Keluar</th>
                                                        <th class="border-0 rounded-end text-end pe-3">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($absensi as $item)
                                                        <tr class="border-bottom">
                                                            <td class="ps-3">
                                                                @php
                                                                    $tanggalFormatted = 'Tidak tersedia';
                                                                    $hariFormatted = '-';
                                                                    
                                                                    if (is_object($item) && isset($item->tanggal_absensi)) {
                                                                        if (is_object($item->tanggal_absensi) && method_exists($item->tanggal_absensi, 'format')) {
                                                                            $tanggalFormatted = $item->tanggal_absensi->format('d M Y');
                                                                            $hariFormatted = $item->tanggal_absensi->format('l');
                                                                        } elseif (is_string($item->tanggal_absensi)) {
                                                                            $tanggalObj = \Carbon\Carbon::parse($item->tanggal_absensi);
                                                                            $tanggalFormatted = $tanggalObj->format('d M Y');
                                                                            $hariFormatted = $tanggalObj->format('l');
                                                                        }
                                                                    } elseif (is_array($item) && isset($item['tanggal_absensi'])) {
                                                                        $tanggalObj = \Carbon\Carbon::parse($item['tanggal_absensi']);
                                                                        $tanggalFormatted = $tanggalObj->format('d M Y');
                                                                        $hariFormatted = $tanggalObj->format('l');
                                                                    }
                                                                @endphp
                                                                <div class="fw-bold">{{ $tanggalFormatted }}</div>
                                                                <small class="text-muted">{{ $hariFormatted }}</small>
                                                            </td>
                                                            @if(is_admin() || is_hrd())
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="avatar-circle-sm me-2">
                                                                            <i class="fas fa-user"></i>
                                                                        </div>
                                                                        <div>
                                                                            @php
                                                                                $pegawaiName = 'Tidak tersedia';
                                                                                $posisiInfo = '';
                                                                                
                                                                                if (is_object($item)) {
                                                                                    if (isset($item->pegawai) && is_object($item->pegawai)) {
                                                                                        // Menggunakan nama_lengkap dari pegawai jika tersedia
                                                                                        $pegawaiName = $item->pegawai->nama_lengkap ?? $pegawaiName;
                                                                                        
                                                                                        if (isset($item->pegawai->user) && is_object($item->pegawai->user)) {
                                                                                            // Fallback ke nama user jika nama_lengkap tidak tersedia
                                                                                            if (empty($pegawaiName) || $pegawaiName === 'Tidak tersedia') {
                                                                                                $pegawaiName = $item->pegawai->user->nama_user ?? $item->pegawai->user->name ?? 'Tidak tersedia';
                                                                                            }
                                                                                            
                                                                                            if (isset($item->pegawai->posisi) && is_object($item->pegawai->posisi)) {
                                                                                                $posisiInfo = $item->pegawai->posisi->nama_posisi ?? '';
                                                                                            } else {
                                                                                                $posisiInfo = ucfirst($item->pegawai->user->role ?? '');
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                } elseif (is_array($item)) {
                                                                                    if (isset($item['pegawai']) && is_array($item['pegawai'])) {
                                                                                        // Menggunakan nama_lengkap dari pegawai jika tersedia
                                                                                        $pegawaiName = $item['pegawai']['nama_lengkap'] ?? $pegawaiName;
                                                                                        
                                                                                        if (isset($item['pegawai']['user']) && is_array($item['pegawai']['user'])) {
                                                                                            // Fallback ke nama user jika nama_lengkap tidak tersedia
                                                                                            if (empty($pegawaiName) || $pegawaiName === 'Tidak tersedia') {
                                                                                                $pegawaiName = $item['pegawai']['user']['nama_user'] ?? $item['pegawai']['user']['name'] ?? 'Tidak tersedia';
                                                                                            }
                                                                                            
                                                                                            if (isset($item['pegawai']['posisi']) && is_array($item['pegawai']['posisi'])) {
                                                                                                $posisiInfo = $item['pegawai']['posisi']['nama_posisi'] ?? '';
                                                                                            } else {
                                                                                                $posisiInfo = ucfirst($item['pegawai']['user']['role'] ?? '');
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            @endphp
                                                                            <div class="fw-bold">{{ $pegawaiName }}</div>
                                                                            <small class="text-muted">
                                                                                {{ $posisiInfo }}
                                                                            </small>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            @endif
                                                            <td>
                                                                @php
                                                                    $itemStatus = '';
                                                                    $isToday = false;
                                                                    $hasJamMasuk = false;
                                                                    $hasJamKeluar = false;
                                                                    
                                                                    if (is_object($item) && isset($item->status)) {
                                                                        $itemStatus = $item->status;
                                                                    } elseif (is_array($item) && isset($item['status'])) {
                                                                        $itemStatus = $item['status'];
                                                                    }
                                                                    
                                                                    // Check if this is today
                                                                    if (is_object($item) && isset($item->tanggal)) {
                                                                        if (is_object($item->tanggal) && method_exists($item->tanggal, 'format')) {
                                                                            $isToday = $item->tanggal->isToday();
                                                                        } elseif (is_string($item->tanggal)) {
                                                                            $isToday = \Carbon\Carbon::parse($item->tanggal)->isToday();
                                                                        }
                                                                    } elseif (is_array($item) && isset($item['tanggal'])) {
                                                                        $isToday = \Carbon\Carbon::parse($item['tanggal'])->isToday();
                                                                    }
                                                                    
                                                                    // Check jam masuk and keluar
                                                                    if (is_object($item)) {
                                                                        $hasJamMasuk = isset($item->jam_masuk) && !empty($item->jam_masuk);
                                                                        $hasJamKeluar = isset($item->jam_keluar) && !empty($item->jam_keluar);
                                                                    } elseif (is_array($item)) {
                                                                        $hasJamMasuk = isset($item['jam_masuk']) && !empty($item['jam_masuk']);
                                                                        $hasJamKeluar = isset($item['jam_keluar']) && !empty($item['jam_keluar']);
                                                                    }
                                                                    
                                                                    $badgeClass = 'bg-light text-dark';
                                                                    $statusText = $itemStatus ?: 'Tidak tersedia';
                                                                    
                                                                    if ($itemStatus === 'Hadir') {
                                                                        $badgeClass = 'bg-success';
                                                                    } elseif ($itemStatus === 'Terlambat') {
                                                                        $badgeClass = 'bg-warning text-dark';
                                                                    } elseif ($itemStatus === 'Sakit') {
                                                                        $badgeClass = 'bg-info';
                                                                    } elseif ($itemStatus === 'Izin') {
                                                                        $badgeClass = 'bg-secondary';
                                                                    } elseif ($itemStatus === 'Tidak Hadir') {
                                                                        $badgeClass = 'bg-danger';
                                                                    }
                                                                    
                                                                    // Add checkout status for today
                                                                    if ($isToday && $hasJamMasuk && !$hasJamKeluar) {
                                                                        $statusText .= ' (Belum Checkout)';
                                                                    }
                                                                @endphp
                                                                
                                                                <div class="d-flex flex-column">
                                                                    <span class="badge {{ $badgeClass }} mb-1">
                                                                        {{ $statusText }}
                                                                    </span>
                                                                    
                                                                    @if($isToday && $hasJamMasuk && !$hasJamKeluar)
                                                                        <small class="text-warning">
                                                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                                                            Perlu checkout
                                                                        </small>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $hasJamMasuk = false;
                                                                    $hasJamKeluar = false;
                                                                    $jamMasukFormatted = '-';
                                                                    $jamKeluarFormatted = '-';
                                                                    $isTerlambat = false;
                                                                    $isToday = false;
                                                                    
                                                                    // Check if this is today's attendance
                                                                    if (is_object($item) && isset($item->tanggal)) {
                                                                        if (is_object($item->tanggal) && method_exists($item->tanggal, 'format')) {
                                                                            $isToday = $item->tanggal->isToday();
                                                                        } elseif (is_string($item->tanggal)) {
                                                                            $isToday = \Carbon\Carbon::parse($item->tanggal)->isToday();
                                                                        }
                                                                    } elseif (is_array($item) && isset($item['tanggal'])) {
                                                                        $isToday = \Carbon\Carbon::parse($item['tanggal'])->isToday();
                                                                    }
                                                                    
                                                                    // Process jam_masuk
                                                                    if (is_object($item)) {
                                                                        if (isset($item->jam_masuk) && !empty($item->jam_masuk)) {
                                                                            $hasJamMasuk = true;
                                                                            if (is_object($item->jam_masuk) && method_exists($item->jam_masuk, 'format')) {
                                                                                $jamMasukFormatted = $item->jam_masuk->format('H:i');
                                                                            } elseif (is_string($item->jam_masuk)) {
                                                                                $jamMasukFormatted = \Carbon\Carbon::parse($item->jam_masuk)->format('H:i');
                                                                            }
                                                                            $isTerlambat = isset($item->status) && $item->status === 'Terlambat';
                                                                        }
                                                                        
                                                                        // Process jam_keluar
                                                                        if (isset($item->jam_keluar) && !empty($item->jam_keluar)) {
                                                                            $hasJamKeluar = true;
                                                                            if (is_object($item->jam_keluar) && method_exists($item->jam_keluar, 'format')) {
                                                                                $jamKeluarFormatted = $item->jam_keluar->format('H:i');
                                                                            } elseif (is_string($item->jam_keluar)) {
                                                                                $jamKeluarFormatted = \Carbon\Carbon::parse($item->jam_keluar)->format('H:i');
                                                                            }
                                                                        }
                                                                    } elseif (is_array($item)) {
                                                                        if (isset($item['jam_masuk']) && !empty($item['jam_masuk'])) {
                                                                            $hasJamMasuk = true;
                                                                            $jamMasukFormatted = \Carbon\Carbon::parse($item['jam_masuk'])->format('H:i');
                                                                            $isTerlambat = isset($item['status']) && $item['status'] === 'Terlambat';
                                                                        }
                                                                        
                                                                        if (isset($item['jam_keluar']) && !empty($item['jam_keluar'])) {
                                                                            $hasJamKeluar = true;
                                                                            $jamKeluarFormatted = \Carbon\Carbon::parse($item['jam_keluar'])->format('H:i');
                                                                        }
                                                                    }
                                                                @endphp
                                                                
                                                                <div class="d-flex flex-column">
                                                                    <!-- Jam Masuk -->
                                                                    <div class="mb-1">
                                                                        <small class="text-muted d-block">Masuk:</small>
                                                                        @if($hasJamMasuk)
                                                                            <span class="{{ $isTerlambat ? 'text-warning fw-bold' : 'text-success' }}">
                                                                                <i class="fas fa-sign-in-alt me-1"></i>{{ $jamMasukFormatted }}
                                                                            </span>
                                                                        @else
                                                                            <span class="text-muted">-</span>
                                                                        @endif
                                                                    </div>
                                                                    
                                                                    <!-- Jam Keluar -->
                                                                    <div>
                                                                        <small class="text-muted d-block">Keluar:</small>
                                                                        @if($hasJamKeluar)
                                                                            <span class="text-info">
                                                                                <i class="fas fa-sign-out-alt me-1"></i>{{ $jamKeluarFormatted }}
                                                                            </span>
                                                                        @else
                                                                            @if($isToday && $hasJamMasuk)
                                                                                <span class="text-warning">
                                                                                    <i class="fas fa-clock me-1"></i>Belum Checkout
                                                                                </span>
                                                                            @else
                                                                                <span class="text-muted">-</span>
                                                                            @endif
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-end pe-3">
                                                                <div class="btn-group">
                                                                    @php
                                                                        $itemId = null;
                                                                        $isToday = false;
                                                                        $hasJamMasuk = false;
                                                                        $hasJamKeluar = false;
                                                                        
                                                                        if (is_object($item)) {
                                                                            $itemId = $item->id_absensi ?? $item->id ?? null;
                                                                            $hasJamMasuk = isset($item->jam_masuk) && !empty($item->jam_masuk);
                                                                            $hasJamKeluar = isset($item->jam_keluar) && !empty($item->jam_keluar);
                                                                            
                                                                            if (isset($item->tanggal)) {
                                                                                if (is_object($item->tanggal) && method_exists($item->tanggal, 'format')) {
                                                                                    $isToday = $item->tanggal->isToday();
                                                                                } elseif (is_string($item->tanggal)) {
                                                                                    $isToday = \Carbon\Carbon::parse($item->tanggal)->isToday();
                                                                                }
                                                                            }
                                                                        } elseif (is_array($item)) {
                                                                            $itemId = $item['id_absensi'] ?? $item['id'] ?? null;
                                                                            $hasJamMasuk = isset($item['jam_masuk']) && !empty($item['jam_masuk']);
                                                                            $hasJamKeluar = isset($item['jam_keluar']) && !empty($item['jam_keluar']);
                                                                            
                                                                            if (isset($item['tanggal'])) {
                                                                                $isToday = \Carbon\Carbon::parse($item['tanggal'])->isToday();
                                                                            }
                                                                        } elseif (is_numeric($item)) {
                                                                            $itemId = $item;
                                                                        }
                                                                    @endphp
                                                                    
                                                                    @if($itemId !== null)
                                                                        <!-- Checkout button for today's record -->
                                                                        @if($isToday && $hasJamMasuk && !$hasJamKeluar && !is_admin() && !is_hrd())
                                                                            <button type="button" class="btn btn-sm btn-success me-1" 
                                                                                    onclick="openCheckOutModal({{ $itemId }})" 
                                                                                    title="Checkout Sekarang">
                                                                                <i class="fas fa-sign-out-alt"></i>
                                                                            </button>
                                                                        @endif
                                                                        
                                                                        <a href="{{ route('absensi.show', $itemId) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                                                            <i class="fas fa-eye"></i>
                                                                        </a>
                                                                        
                                                                        @if(is_admin() || is_hrd())
                                                                            <a href="{{ route('absensi.admin-edit', $itemId) }}" class="btn btn-sm btn-outline-warning" title="Edit Absensi">
                                                                                <i class="fas fa-edit"></i>
                                                                            </a>
                                                                            @if(is_admin())
                                                                                <form action="{{ route('absensi.destroy', $itemId) }}" method="POST" class="d-inline">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                                            onclick="return confirm('Yakin ingin menghapus data absensi ini?')"
                                                                                            title="Hapus Absensi">
                                                                                        <i class="fas fa-trash"></i>
                                                                                    </button>
                                                                                </form>
                                                                            @endif
                                                                        @endif
                                                                    @else
                                                                        <span class="text-danger">Data ID tidak valid</span>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Enhanced Pagination Section -->
                        @if(isset($paginationInfo) && $paginationInfo['has_pages'])
                            <div class="pagination-container mt-5">
                                <div class="row align-items-center">
                                    <!-- Pagination Info -->
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <div class="pagination-summary">
                                            <div class="d-flex align-items-center">
                                                <div class="pagination-icon me-3">
                                                    <i class="fas fa-list-ul"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 text-primary fw-bold">Data Absensi</h6>
                                                    <p class="mb-0 text-muted small">
                                                        Menampilkan <span class="fw-bold text-dark">{{ (($paginationInfo['current_page'] - 1) * $paginationInfo['per_page']) + 1 }}</span> 
                                                        - <span class="fw-bold text-dark">{{ min($paginationInfo['current_page'] * $paginationInfo['per_page'], $paginationInfo['total']) }}</span> 
                                                        dari <span class="fw-bold text-primary">{{ $paginationInfo['total'] }}</span> total data
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Pagination Navigation -->
                                    <div class="col-md-6">
                                        <nav aria-label="Pagination Navigation" class="d-flex justify-content-md-end justify-content-center">
                                            <ul class="pagination pagination-modern mb-0">
                                                <!-- First Page -->
                                                @if($paginationInfo['current_page'] > 2)
                                                    <li class="page-item">
                                                        <a class="page-link page-link-first" href="{{ request()->fullUrlWithQuery(['page' => 1]) }}" title="Halaman Pertama">
                                                            <i class="fas fa-angle-double-left"></i>
                                                        </a>
                                                    </li>
                                                @endif
                                                
                                                <!-- Previous Page -->
                                                @if($paginationInfo['current_page'] > 1)
                                                    <li class="page-item">
                                                        <a class="page-link page-link-prev" href="{{ request()->fullUrlWithQuery(['page' => $paginationInfo['current_page'] - 1]) }}" title="Halaman Sebelumnya">
                                                            <i class="fas fa-chevron-left"></i>
                                                            <span class="d-none d-sm-inline ms-1">Prev</span>
                                                        </a>
                                                    </li>
                                                @endif
                                                
                                                <!-- Page Numbers (Smart pagination) -->
                                                @php
                                                    $start = max(1, $paginationInfo['current_page'] - 2);
                                                    $end = min($paginationInfo['last_page'], $paginationInfo['current_page'] + 2);
                                                @endphp
                                                
                                                @if($start > 1)
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => 1]) }}">1</a>
                                                    </li>
                                                    @if($start > 2)
                                                        <li class="page-item disabled">
                                                            <span class="page-link page-dots">...</span>
                                                        </li>
                                                    @endif
                                                @endif
                                                
                                                @for($i = $start; $i <= $end; $i++)
                                                    <li class="page-item {{ $i == $paginationInfo['current_page'] ? 'active' : '' }}">
                                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                                    </li>
                                                @endfor
                                                
                                                @if($end < $paginationInfo['last_page'])
                                                    @if($end < $paginationInfo['last_page'] - 1)
                                                        <li class="page-item disabled">
                                                            <span class="page-link page-dots">...</span>
                                                        </li>
                                                    @endif
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $paginationInfo['last_page']]) }}">{{ $paginationInfo['last_page'] }}</a>
                                                    </li>
                                                @endif
                                                
                                                <!-- Next Page -->
                                                @if($paginationInfo['current_page'] < $paginationInfo['last_page'])
                                                    <li class="page-item">
                                                        <a class="page-link page-link-next" href="{{ request()->fullUrlWithQuery(['page' => $paginationInfo['current_page'] + 1]) }}" title="Halaman Selanjutnya">
                                                            <span class="d-none d-sm-inline me-1">Next</span>
                                                            <i class="fas fa-chevron-right"></i>
                                                        </a>
                                                    </li>
                                                @endif
                                                
                                                <!-- Last Page -->
                                                @if($paginationInfo['current_page'] < $paginationInfo['last_page'] - 1)
                                                    <li class="page-item">
                                                        <a class="page-link page-link-last" href="{{ request()->fullUrlWithQuery(['page' => $paginationInfo['last_page']]) }}" title="Halaman Terakhir">
                                                            <i class="fas fa-angle-double-right"></i>
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                                
                                <!-- Additional Pagination Info -->
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="pagination-details text-center">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Halaman <span class="fw-bold text-primary">{{ $paginationInfo['current_page'] }}</span> 
                                                dari <span class="fw-bold text-primary">{{ $paginationInfo['last_page'] }}</span> halaman
                                                @if($paginationInfo['per_page'] > 0)
                                                    | <span class="fw-bold">{{ $paginationInfo['per_page'] }}</span> data per halaman
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada data absensi</h5>
                            <p class="text-muted">Silakan lakukan absensi untuk melihat riwayat.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Check Out Modal -->
<div class="modal fade" id="checkOutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('absensi.checkout') }}" method="POST" id="checkOutForm">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3">Apakah Anda yakin ingin melakukan check-out?</p>
                    <div class="mb-3">
                        <label for="keterangan_keluar" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control" id="keterangan_keluar" name="keterangan_keluar" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                    <input type="hidden" id="absensi_id_checkout" name="absensi_id">
                    <input type="hidden" id="latitude_checkout" name="latitude">
                    <input type="hidden" id="longitude_checkout" name="longitude">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Keluar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Monthly Report Modal -->
<div class="modal fade" id="monthlyReportModal" tabindex="-1" aria-labelledby="monthlyReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="monthlyReportModalLabel">
                    <i class="fas fa-download me-2"></i>Download Rekap Absensi Bulanan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('absensi.export-monthly-pdf') }}" method="POST" id="monthlyReportForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Fitur ini akan mendownload rekap lengkap absensi semua karyawan untuk bulan yang dipilih.</strong>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="report_bulan" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Bulan <span class="text-danger">*</span>
                                </label>
                                <select name="bulan" id="report_bulan" class="form-select" required>
                                    <option value="">Pilih Bulan</option>
                                    @php
                                        $namaBulan = [
                                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                        ];
                                        $currentMonth = date('n');
                                    @endphp
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ $i == $currentMonth ? 'selected' : '' }}>
                                            {{ $namaBulan[$i] }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="report_tahun" class="form-label">
                                    <i class="fas fa-calendar-check me-1"></i>Tahun <span class="text-danger">*</span>
                                </label>
                                <select name="tahun" id="report_tahun" class="form-select" required>
                                    <option value="">Pilih Tahun</option>
                                    @php $currentYear = date('Y'); @endphp
                                    @for($i = $currentYear; $i >= $currentYear - 3; $i--)
                                        <option value="{{ $i }}" {{ $i == $currentYear ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="bg-light p-3 rounded">
                                <h6 class="mb-2"><i class="fas fa-file-pdf text-danger me-2"></i>Isi Laporan:</h6>
                                <ul class="mb-0 small">
                                    <li>Data absensi semua karyawan untuk bulan yang dipilih</li>
                                    <li>Detail jam masuk, jam keluar, dan durasi kerja</li>
                                    <li>Status kehadiran (Hadir, Sakit, Izin, Alpa)</li>
                                    <li>Lokasi check-in karyawan</li>
                                    <li>Ringkasan statistik kehadiran</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-warning" id="downloadReportBtn">
                        <i class="fas fa-download me-1"></i>Download PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.absensi-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 12px;
    overflow: hidden;
}

.absensi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

.btn-outline-primary:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
}

.btn-outline-warning:hover {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    border-color: transparent;
}

.btn-outline-danger:hover {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    border-color: transparent;
}

.form-label.small {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.shadow-lg {
    box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important;
}

/* Table View Styles */
.table {
    font-size: 0.95rem;
}

.table thead {
    height: 50px;
}

.table thead th {
    font-weight: 600;
    color: #555;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    font-size: 0.8rem;
    padding-top: 15px;
    padding-bottom: 15px;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: rgba(102, 126, 234, 0.05);
}

.avatar-circle-sm {
    width: 30px;
    height: 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.8rem;
}

/* View Toggle Buttons */
.btn-group .btn.active {
    background-color: #667eea;
    color: white;
    border-color: #667eea;
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 0.5rem;
    }
    
    .absensi-card {
        margin-bottom: 1rem;
    }
    
    .d-flex.gap-2.flex-wrap > * {
        margin-bottom: 0.5rem;
    }
    
    .table-responsive {
        font-size: 0.85rem;
    }
}

/* Enhanced Pagination Styles */
.pagination-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(102, 126, 234, 0.1);
    margin-top: 2rem;
}

.pagination-summary {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    border-left: 4px solid #667eea;
}

.pagination-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.pagination-modern {
    gap: 0.25rem;
}

.pagination-modern .page-link {
    border: none;
    border-radius: 10px;
    margin: 0 2px;
    padding: 0.75rem 1rem;
    font-weight: 500;
    color: #495057;
    background: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.pagination-modern .page-link:hover {
    color: white;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    z-index: 2;
}

.pagination-modern .page-link:focus {
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
    outline: none;
}

.pagination-modern .page-item.active .page-link {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    transform: translateY(-1px);
    border: none;
}

.pagination-modern .page-item.active .page-link:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: translateY(-2px);
}

.pagination-modern .page-item.disabled .page-link {
    color: #6c757d;
    background: #f8f9fa;
    opacity: 0.6;
    cursor: not-allowed;
    box-shadow: none;
}

.pagination-modern .page-item.disabled .page-link:hover {
    color: #6c757d;
    background: #f8f9fa;
    transform: none;
    box-shadow: none;
}

/* Special styles for navigation buttons */
.page-link-first,
.page-link-last {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white !important;
    font-weight: 600;
}

.page-link-first:hover,
.page-link-last:hover {
    background: linear-gradient(135deg, #138496 0%, #117a8b 100%) !important;
    color: white !important;
}

.page-link-prev,
.page-link-next {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    color: white !important;
    font-weight: 600;
    min-width: 80px;
}

.page-link-prev:hover,
.page-link-next:hover {
    background: linear-gradient(135deg, #218838 0%, #1abc9c 100%) !important;
    color: white !important;
}

.page-dots {
    background: transparent !important;
    color: #6c757d !important;
    border: none !important;
    box-shadow: none !important;
    cursor: default !important;
    font-weight: bold;
    font-size: 1.2rem;
}

.page-dots:hover {
    background: transparent !important;
    color: #6c757d !important;
    transform: none !important;
    box-shadow: none !important;
}

.pagination-details {
    background: rgba(102, 126, 234, 0.05);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    border: 1px solid rgba(102, 126, 234, 0.1);
}

/* Enhanced hover effects */
.pagination-modern .page-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.pagination-modern .page-link:hover::before {
    left: 100%;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .pagination-container {
        padding: 1.5rem 1rem;
        margin-top: 1.5rem;
    }
    
    .pagination-summary {
        padding: 1rem;
        text-align: center;
    }
    
    .pagination-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .pagination-modern .page-link {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        margin: 0 1px;
    }
    
    .page-link-prev,
    .page-link-next {
        min-width: 60px;
    }
    
    .pagination-modern {
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.125rem;
    }
}

@media (max-width: 576px) {
    .pagination-container {
        padding: 1rem 0.75rem;
    }
    
    .pagination-summary {
        padding: 0.75rem;
    }
    
    .pagination-modern .page-link {
        padding: 0.4rem 0.6rem;
        font-size: 0.8rem;
        min-width: 35px;
    }
    
    .page-link-prev,
    .page-link-next {
        min-width: 50px;
        padding: 0.4rem 0.5rem;
    }
    
    /* Hide text on very small screens, keep icons */
    .page-link-prev span,
    .page-link-next span {
        display: none !important;
    }
}

/* Animation for page transitions */
@keyframes pageTransition {
    0% {
        opacity: 0;
        transform: translateY(10px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.pagination-container {
    animation: pageTransition 0.5s ease-out;
}

/* Loading state for pagination */
.pagination-loading .page-link {
    pointer-events: none;
    opacity: 0.6;
    position: relative;
}

.pagination-loading .page-link::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 16px;
    height: 16px;
    margin: -8px 0 0 -8px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
function showCheckOutModal() {
    // Ambil ID absensi hari ini dari API atau data yang tersedia
    const todayStatus = @json($todayStatus ?? null);
    let absensiId = null;
    
    if (todayStatus && todayStatus.absensi_id) {
        absensiId = todayStatus.absensi_id;
    } else {
        // Fallback: cari dari data absensi hari ini
        const today = new Date().toISOString().split('T')[0];
        const absensiData = @json($absensi ?? []);
        
        if (Array.isArray(absensiData)) {
            const todayAbsensi = absensiData.find(item => {
                const itemDate = item.tanggal ? item.tanggal.split('T')[0] : null;
                return itemDate === today;
            });
            
            if (todayAbsensi) {
                absensiId = todayAbsensi.id_absensi || todayAbsensi.id;
            }
        }
    }
    
    if (!absensiId) {
        alert('Tidak dapat menemukan data absensi hari ini. Silakan refresh halaman.');
        return;
    }
    
    // Set absensi ID ke form
    document.getElementById('absensi_id_checkout').value = absensiId;
    
    // Get current location (optional, for logging purposes)
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            
            document.getElementById('latitude_checkout').value = lat;
            document.getElementById('longitude_checkout').value = lon;
            
            const modal = new bootstrap.Modal(document.getElementById('checkOutModal'));
            modal.show();
        }, function(error) {
            console.warn('Geolocation error:', error.message);
            // Show modal even if geolocation fails
            const modal = new bootstrap.Modal(document.getElementById('checkOutModal'));
            modal.show();
        });
    } else {
        // Show modal without geolocation
        const modal = new bootstrap.Modal(document.getElementById('checkOutModal'));
        modal.show();
    }
}

function switchView(view) {
    const cardViewBtn = document.getElementById('cardViewBtn');
    const tableViewBtn = document.getElementById('tableViewBtn');
    const absensiData = document.getElementById('absensiData');
    const cardView = document.getElementById('cardView');
    const tableView = document.getElementById('tableView');

    if (view === 'card') {
        cardViewBtn.classList.add('active');
        tableViewBtn.classList.remove('active');
        cardView.style.display = 'flex';
        tableView.style.display = 'none';
        // Save preference to localStorage
        localStorage.setItem('absensiViewPreference', 'card');
    } else {
        cardViewBtn.classList.remove('active');
        tableViewBtn.classList.add('active');
        cardView.style.display = 'none';
        tableView.style.display = 'flex';
        // Save preference to localStorage
        localStorage.setItem('absensiViewPreference', 'table');
    }
}

// Check if user has a saved preference
document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('absensiViewPreference');
    if (savedView === 'table') {
        switchView('table');
    }
});

// Refresh attendance status function
function refreshAttendanceStatus() {
    const refreshBtn = document.querySelector('button[onclick="refreshAttendanceStatus()"] i');
    refreshBtn.classList.add('fa-spin');
    
    // Show loading state
    const loadingToast = document.createElement('div');
    loadingToast.className = 'toast position-fixed top-0 end-0 m-3';
    loadingToast.style.zIndex = '9999';
    loadingToast.innerHTML = `
        <div class="toast-body bg-info text-white">
            <i class="fas fa-spinner fa-spin me-2"></i>Memperbarui status absensi...
        </div>
    `;
    document.body.appendChild(loadingToast);
    
    const toast = new bootstrap.Toast(loadingToast);
    toast.show();
    
    // Reload page to get fresh data
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

// Enhanced pagination functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth loading animation for pagination links
    const paginationLinks = document.querySelectorAll('.pagination-modern .page-link');
    
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Don't intercept disabled links or current page
            if (this.closest('.page-item').classList.contains('disabled') || 
                this.closest('.page-item').classList.contains('active')) {
                return;
            }
            
            // Add loading state
            const paginationContainer = document.querySelector('.pagination-modern');
            if (paginationContainer) {
                paginationContainer.classList.add('pagination-loading');
            }
            
            // Show loading indicator
            showPaginationLoading();
        });
    });
    
    // Add keyboard navigation for pagination
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            const currentPage = document.querySelector('.pagination-modern .page-item.active .page-link');
            if (!currentPage) return;
            
            let targetLink = null;
            
            if (e.key === 'ArrowLeft') {
                // Go to previous page
                const prevLink = document.querySelector('.page-link-prev');
                if (prevLink && !prevLink.closest('.page-item').classList.contains('disabled')) {
                    targetLink = prevLink;
                }
            } else if (e.key === 'ArrowRight') {
                // Go to next page
                const nextLink = document.querySelector('.page-link-next');
                if (nextLink && !nextLink.closest('.page-item').classList.contains('disabled')) {
                    targetLink = nextLink;
                }
            }
            
            if (targetLink) {
                e.preventDefault();
                targetLink.click();
            }
        }
    });
});

function showPaginationLoading() {
    // Create overlay
    const overlay = document.createElement('div');
    overlay.id = 'pagination-loading-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        z-index: 9998;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(2px);
    `;
    
    overlay.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted">Memuat data absensi...</p>
        </div>
    `;
    
    document.body.appendChild(overlay);
    
    // Auto-remove overlay after 5 seconds (fallback)
    setTimeout(() => {
        const existingOverlay = document.getElementById('pagination-loading-overlay');
        if (existingOverlay) {
            existingOverlay.remove();
        }
    }, 5000);
}

// Add tooltip functionality for pagination buttons
function initializePaginationTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('.pagination-modern .page-link[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            placement: 'top',
            delay: { show: 500, hide: 100 }
        });
    });
}

// Initialize tooltips when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        initializePaginationTooltips();
    }
});

// PDF Export function for Absensi dengan API Integration
function exportToPdf() {
    // Tampilkan loading
    const loadingHtml = `
        <div id="pdf-loading" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; justify-content: center; align-items: center;">
            <div style="background: white; padding: 20px; border-radius: 8px; text-align: center;">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p style="margin-top: 10px;">Sedang memproses laporan PDF...</p>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', loadingHtml);
    
    try {
        // Get current filters dari URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const filters = {
            id_user: urlParams.get('id_user') || '',
            status: urlParams.get('status') || '',
            tanggal: urlParams.get('tanggal') || '',
            bulan: urlParams.get('bulan') || '',
            tahun: urlParams.get('tahun') || ''
        };
        
        console.log('Filter untuk export PDF:', filters);
        
        // Build export URL dengan filter saat ini
        const exportUrl = new URL('{{ route("absensi.export-pdf") }}', window.location.origin);
        
        // Tambahkan semua filter ke URL
        Object.keys(filters).forEach(key => {
            if (filters[key]) {
                exportUrl.searchParams.append(key, filters[key]);
            }
        });
        
        // Tambahkan date range jika filter tanggal tertentu diatur
        if (filters.tanggal) {
            exportUrl.searchParams.append('start_date', filters.tanggal);
            exportUrl.searchParams.append('end_date', filters.tanggal);
        } else if (filters.bulan && filters.tahun) {
            // Convert bulan/tahun ke date range
            const startDate = new Date(filters.tahun, filters.bulan - 1, 1);
            const endDate = new Date(filters.tahun, filters.bulan, 0);
            exportUrl.searchParams.append('start_date', startDate.toISOString().split('T')[0]);
            exportUrl.searchParams.append('end_date', endDate.toISOString().split('T')[0]);
        }
        
        console.log('URL Export PDF:', exportUrl.toString());
        
        // Buat form untuk POST request (lebih aman untuk data besar)
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("absensi.export-pdf") }}';
        form.target = '_blank';
        form.style.display = 'none';
        
        // Tambahkan CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        // Tambahkan filter sebagai hidden input
        Object.keys(filters).forEach(key => {
            if (filters[key]) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = filters[key];
                form.appendChild(input);
            }
        });
        
        // Tambahkan date range
        if (filters.tanggal) {
            const startInput = document.createElement('input');
            startInput.type = 'hidden';
            startInput.name = 'start_date';
            startInput.value = filters.tanggal;
            form.appendChild(startInput);
            
            const endInput = document.createElement('input');
            endInput.type = 'hidden';
            endInput.name = 'end_date';
            endInput.value = filters.tanggal;
            form.appendChild(endInput);
        } else if (filters.bulan && filters.tahun) {
            const startDate = new Date(filters.tahun, filters.bulan - 1, 1);
            const endDate = new Date(filters.tahun, filters.bulan, 0);
            
            const startInput = document.createElement('input');
            startInput.type = 'hidden';
            startInput.name = 'start_date';
            startInput.value = startDate.toISOString().split('T')[0];
            form.appendChild(startInput);
            
            const endInput = document.createElement('input');
            endInput.type = 'hidden';
            endInput.name = 'end_date';
            endInput.value = endDate.toISOString().split('T')[0];
            form.appendChild(endInput);
        }
        
        // Submit form
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        
        // Tampilkan pesan sukses
        showNotification('Laporan PDF sedang diproses, file akan segera diunduh...', 'success');
        
        // Hapus loading setelah delay yang lebih pendek
        setTimeout(() => {
            const loadingElement = document.getElementById('pdf-loading');
            if (loadingElement) {
                loadingElement.remove();
            }
        }, 2000);
        
    } catch (error) {
        console.error('Error saat export PDF:', error);
        
        // Hapus loading
        const loadingElement = document.getElementById('pdf-loading');
        if (loadingElement) {
            loadingElement.remove();
        }
        
        // Tampilkan pesan error
        showNotification('Terjadi kesalahan saat mengekspor PDF. Silakan coba lagi.', 'error');
    }
}

// Fungsi untuk menampilkan notifikasi
function showNotification(message, type = 'info') {
    // Hapus notifikasi yang sudah ada
    const existingNotification = document.getElementById('custom-notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Buat elemen notifikasi
    const notification = document.createElement('div');
    notification.id = 'custom-notification';
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        max-width: 400px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        transition: all 0.3s ease;
        transform: translateX(100%);
    `;
    
    // Set warna berdasarkan tipe
    if (type === 'success') {
        notification.style.backgroundColor = '#28a745';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#dc3545';
    } else if (type === 'warning') {
        notification.style.backgroundColor = '#ffc107';
        notification.style.color = '#333';
    } else {
        notification.style.backgroundColor = '#17a2b8';
    }
    
    notification.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: space-between;">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" style="
                background: none;
                border: none;
                color: inherit;
                font-size: 18px;
                margin-left: 15px;
                cursor: pointer;
                opacity: 0.7;
            ">&times;</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animasi masuk
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto-hide setelah 5 detik
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    }, 5000);
}
</script>
@endsection
