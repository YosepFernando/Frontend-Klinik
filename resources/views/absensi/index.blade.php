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
                                
                                // Inisialisasi variabel todayAbsensi
                                $todayAbsensi = false;
                                // Cek absensi hari ini jika data pegawai tersedia
                                if ($pegawai && isset($absensi)) {
                                    if (is_object($absensi) && method_exists($absensi, 'firstWhere')) {
                                        $todayAbsensi = $absensi->firstWhere('tanggal', $today->format('Y-m-d'));
                                    } elseif (is_array($absensi)) {
                                        foreach ($absensi as $a) {
                                            $tanggal = is_array($a) ? ($a['tanggal'] ?? '') : ($a->tanggal ?? '');
                                            if ($tanggal == $today->format('Y-m-d')) {
                                                $todayAbsensi = $a;
                                                break;
                                            }
                                        }
                                    }
                                }
                            @endphp
                            
                            @if(!$todayAbsensi && $pegawai)
                                <div class="dropdown">
                                    <button class="btn btn-warning dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-exclamation-triangle"></i> Lapor Ketidakhadiran
                                    </button>
                                    <div class="dropdown-menu">
                                        <form action="{{ route('absensi.submit-absence') }}" method="POST" class="px-3 py-2">
                                            @csrf
                                            <div class="mb-2">
                                                <select name="status" class="form-select form-select-sm" required>
                                                    <option value="">Pilih Status</option>
                                                    <option value="sakit">Sakit</option>
                                                    <option value="izin">Izin</option>
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
                            @elseif($todayAbsensi && !$todayAbsensi->jam_keluar && $todayAbsensi->jam_masuk)
                                <button type="button" class="btn btn-danger" onclick="showCheckOutModal()">
                                    <i class="fas fa-sign-out-alt"></i> Check Out
                                </button>
                            @endif
                            
                            @if(is_admin() || is_hrd())
                                <a href="{{ route('pegawai.index') }}" class="btn btn-info">
                                    <i class="fas fa-users"></i> Kelola Pegawai
                                </a>
                                @if(is_admin())
                                @endif
                                <a href="{{ route('absensi.admin-create') }}" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Tambah Absensi
                                </a>
                                <a href="{{ route('absensi.report') }}" class="btn btn-primary">
                                    <i class="fas fa-chart-bar"></i> Laporan
                                </a>
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
                                <i class="fas fa-th-large me-1"></i> Card View
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="tableViewBtn" onclick="switchView('table')">
                                <i class="fas fa-table me-1"></i> Table View
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
                                                    
                                                    if (is_object($item) && isset($item->tanggal)) {
                                                        if (is_object($item->tanggal) && method_exists($item->tanggal, 'format')) {
                                                            $tanggalFormatted = $item->tanggal->format('d M Y');
                                                            $hariFormatted = $item->tanggal->format('l');
                                                        } elseif (is_string($item->tanggal)) {
                                                            $tanggalObj = \Carbon\Carbon::parse($item->tanggal);
                                                            $tanggalFormatted = $tanggalObj->format('d M Y');
                                                            $hariFormatted = $tanggalObj->format('l');
                                                        }
                                                    } elseif (is_array($item) && isset($item['tanggal'])) {
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
                                                        <div class="text-muted small">Check In</div>
                                                        @php
                                                            $hasJamMasuk = false;
                                                            $jamMasukFormatted = '-';
                                                            
                                                            if (is_object($item)) {
                                                                if (isset($item->jam_masuk)) {
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
                                                            if (isset($item->jam_keluar)) {
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
                                                    <div class="fw-bold text-danger">
                                                        @if($hasJamKeluar)
                                                            {{ $jamKeluarFormatted }}
                                                        @else
                                                            <span class="text-muted">-</span>
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
                                            
                                            @if(!empty($alamatMasuk))
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        {{ Str::limit($alamatMasuk, 50) }}
                                                    </small>
                                                </div>
                                            @endif

                                            <!-- Work Duration -->
                                            @if($hasJamMasuk && $hasJamKeluar && !empty($durasiKerja))
                                                <div class="mb-2">
                                                    <small class="text-info">
                                                        <i class="fas fa-hourglass-half"></i>
                                                        Durasi: {{ $durasiKerja }}
                                                    </small>
                                                </div>
                                            @endif

                                            <!-- Notes -->
                                            @if(!empty($catatan))
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-comment"></i>
                                                        {{ $catatan }}
                                                    </small>
                                                </div>
                                            @endif

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
                                                @else
                                                    <span class="text-danger">Data ID tidak valid</span>
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
                                                        <th class="border-0">Check In</th>
                                                        <!-- <th class="border-0">Check Out</th> -->
                                                        <!-- <th class="border-0">Durasi</th> -->
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
                                                                    
                                                                    if (is_object($item) && isset($item->tanggal)) {
                                                                        if (is_object($item->tanggal) && method_exists($item->tanggal, 'format')) {
                                                                            $tanggalFormatted = $item->tanggal->format('d M Y');
                                                                            $hariFormatted = $item->tanggal->format('l');
                                                                        } elseif (is_string($item->tanggal)) {
                                                                            $tanggalObj = \Carbon\Carbon::parse($item->tanggal);
                                                                            $tanggalFormatted = $tanggalObj->format('d M Y');
                                                                            $hariFormatted = $tanggalObj->format('l');
                                                                        }
                                                                    } elseif (is_array($item) && isset($item['tanggal'])) {
                                                                        $tanggalObj = \Carbon\Carbon::parse($item['tanggal']);
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
                                                                    if (is_object($item) && isset($item->status)) {
                                                                        $itemStatus = $item->status;
                                                                    } elseif (is_array($item) && isset($item['status'])) {
                                                                        $itemStatus = $item['status'];
                                                                    }
                                                                    
                                                                    $badgeClass = 'bg-light text-dark';
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
                                                                @endphp
                                                                <span class="badge {{ $badgeClass }}">
                                                                    {{ $itemStatus ?: 'Tidak tersedia' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $hasJamMasuk = false;
                                                                    $jamMasukFormatted = '-';
                                                                    $isTerlambat = false;
                                                                    
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
                                                                    } elseif (is_array($item)) {
                                                                        if (isset($item['jam_masuk']) && !empty($item['jam_masuk'])) {
                                                                            $hasJamMasuk = true;
                                                                            $jamMasukFormatted = \Carbon\Carbon::parse($item['jam_masuk'])->format('H:i');
                                                                            $isTerlambat = isset($item['status']) && $item['status'] === 'Terlambat';
                                                                        }
                                                                    }
                                                                @endphp
                                                                @if($hasJamMasuk)
                                                                    <span class="{{ $isTerlambat ? 'text-warning fw-bold' : '' }}">
                                                                        {{ $jamMasukFormatted }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-end pe-3">
                                                                <div class="btn-group">
                                                                    @php
                                                                        $itemId = null;
                                                                        if (is_object($item) && isset($item->id)) {
                                                                            $itemId = $item->id;
                                                                        } elseif (is_array($item) && isset($item['id'])) {
                                                                            $itemId = $item['id'];
                                                                        } elseif (is_numeric($item)) {
                                                                            $itemId = $item;
                                                                        }
                                                                    @endphp
                                                                    
                                                                    @if($itemId !== null)
                                                                        <a href="{{ route('absensi.show', $itemId) }}" class="btn btn-sm btn-outline-primary">
                                                                            <i class="fas fa-eye"></i>
                                                                        </a>
                                                                        @if(is_admin() || is_hrd())
                                                                            <a href="{{ route('absensi.admin-edit', $itemId) }}" class="btn btn-sm btn-outline-warning">
                                                                                <i class="fas fa-edit"></i>
                                                                            </a>
                                                                            @if(is_admin())
                                                                                <form action="{{ route('absensi.destroy', $itemId) }}" method="POST" class="d-inline">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                                            onclick="return confirm('Yakin ingin menghapus data absensi ini?')">
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
                <h5 class="modal-title">Check Out</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('absensi.checkout') }}" method="POST" id="checkOutForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="alamat_checkout" class="form-label">Alamat Check Out</label>
                        <input type="text" class="form-control" id="alamat_checkout" name="alamat_checkout" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan_keluar" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control" id="keterangan_keluar" name="keterangan_keluar" rows="3"></textarea>
                    </div>
                    <input type="hidden" id="latitude_checkout" name="latitude">
                    <input type="hidden" id="longitude_checkout" name="longitude">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Check Out
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
</style>

<script>
function showCheckOutModal() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            
            document.getElementById('latitude_checkout').value = lat;
            document.getElementById('longitude_checkout').value = lon;
            
            // Get address from coordinates using reverse geocoding
            fetch(`https://api.opencagedata.com/geocode/v1/json?q=${lat}+${lon}&key=YOUR_API_KEY`)
                .then(response => response.json())
                .then(data => {
                    if (data.results && data.results.length > 0) {
                        document.getElementById('alamat_checkout').value = data.results[0].formatted;
                    } else {
                        document.getElementById('alamat_checkout').value = `Lat: ${lat}, Lon: ${lon}`;
                    }
                })
                .catch(error => {
                    console.error('Error getting address:', error);
                    document.getElementById('alamat_checkout').value = `Lat: ${lat}, Lon: ${lon}`;
                });
            
            const modal = new bootstrap.Modal(document.getElementById('checkOutModal'));
            modal.show();
        }, function(error) {
            alert('Error getting location: ' + error.message);
        });
    } else {
        alert('Geolocation is not supported by this browser.');
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
</script>
@endsection
