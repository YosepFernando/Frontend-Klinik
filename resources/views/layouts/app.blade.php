<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Klinik App') }} - @yield('title', 'Dashboard')</title>
    
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 0.5rem;
            margin: 0.2rem 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .btn {
            border-radius: 0.5rem;
        }
        .navbar-brand {
            font-weight: 700;
            color: #667eea !important;
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                    <div class="position-sticky pt-3">
                        <div class="text-center mb-4">
                            <h4 class="text-white">
                                <i class="bi bi-hospital"></i>
                                Klinik App
                            </h4>
                        </div>
                        
                        @auth
                        <div class="text-center mb-3">
                            <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bi bi-person-fill text-primary fs-2"></i>
                            </div>
                            <div class="text-white mt-2">
                                <strong>{{ Auth::user()->name }}</strong>
                                <br>
                                <small class="opacity-75">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</small>
                            </div>
                        </div>
                        @endauth
                        
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                            
                            @auth
                                {{-- Treatment - Admin, HRD, Front Office, Kasir, Dokter, Beautician --}}
                                @if(!Auth::user()->isPelanggan())
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('treatments.*') ? 'active' : '' }}" href="{{ route('treatments.index') }}">
                                        <i class="bi bi-heart-pulse"></i> Treatment
                                    </a>
                                </li>
                                @endif
                                
                                {{-- Appointments - Admin, HRD, Front Office, Kasir, Dokter, Beautician (Tidak untuk Pelanggan) --}}
                                @if(!Auth::user()->isPelanggan())
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}" href="{{ route('appointments.index') }}">
                                        <i class="bi bi-calendar-check"></i> Jadwal Treatment
                                    </a>
                                </li>
                                @endif
                                
                                {{-- Attendance - Admin, HRD, Front Office, Kasir, Dokter, Beautician --}}
                                @if(!Auth::user()->isPelanggan())
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('absensi.*') ? 'active' : '' }}" href="{{ route('absensi.index') }}">
                                        <i class="bi bi-clock"></i> Sistem Absensi
                                    </a>
                                    @if(Auth::user()->isAdmin())
                                    <ul class="nav flex-column ms-3">
                                        <li class="nav-item">
                                            <a class="nav-link small {{ request()->routeIs('absensi.dashboard') ? 'active' : '' }}" href="{{ route('absensi.dashboard') }}">
                                                <i class="bi bi-speedometer2"></i> Dashboard (Admin)
                                            </a>
                                        </li>
                                    </ul>
                                    @endif
                                    @if(Auth::user()->isAdmin() || Auth::user()->isHRD())
                                    <ul class="nav flex-column ms-3">
                                        <li class="nav-item">
                                            <a class="nav-link small {{ request()->routeIs('absensi.report') ? 'active' : '' }}" href="{{ route('absensi.report') }}">
                                                <i class="bi bi-graph-up"></i> Laporan
                                            </a>
                                        </li>
                                    </ul>
                                    @endif
                                </li>
                                @endif
                                
                                {{-- Recruitment - Admin, HRD, Pelanggan --}}
                                @if(Auth::user()->isAdmin() || Auth::user()->isHRD())
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('recruitments.*') ? 'active' : '' }}" href="{{ route('recruitments.index') }}">
                                        <i class="bi bi-people"></i> Rekrutmen
                                    </a>
                                </li>
                                @elseif(Auth::user()->isPelanggan())
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle {{ request()->routeIs('recruitments.*') ? 'active' : '' }}" href="#" id="recruitmentDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-briefcase"></i> Jobs
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="recruitmentDropdown">
                                        <li><a class="dropdown-item" href="{{ route('recruitments.index') }}">
                                            <i class="bi bi-search"></i> Browse Jobs
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('recruitments.my-applications') }}">
                                            <i class="bi bi-file-person"></i> My Applications
                                        </a></li>
                                    </ul>
                                </li>
                                @endif
                                
                                {{-- Training - Admin, HRD, Front Office, Kasir, Dokter, Beautician --}}
                                @if(!Auth::user()->isPelanggan())
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('trainings.*') ? 'active' : '' }}" href="{{ route('trainings.index') }}">
                                        <i class="bi bi-book"></i> Pelatihan
                                    </a>
                                </li>
                                @endif
                                
                                {{-- Religious Studies - Admin, HRD, Front Office, Kasir, Dokter, Beautician --}}
                                @if(!Auth::user()->isPelanggan())
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('religious-studies.*') ? 'active' : '' }}" href="{{ route('religious-studies.index') }}">
                                        <i class="bi bi-mosque"></i> Pengajian
                                    </a>
                                </li>
                                @endif
                                
                                {{-- Employee Management - Admin, HRD only --}}
                                @if(Auth::user()->isAdmin() || Auth::user()->isHRD())
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('pegawai.*') ? 'active' : '' }}" href="{{ route('pegawai.index') }}">
                                        <i class="bi bi-person-badge"></i> Kelola Pegawai
                                    </a>
                                </li>
                                @endif
                                
                                {{-- User Management - Admin, HRD only --}}
                                @if(Auth::user()->isAdmin())
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('hrd.dashboard') ? 'active' : '' }}" href="{{ route('hrd.dashboard') }}">
                                        <i class="bi bi-kanban"></i> HRD Dashboard (Admin)
                                    </a>
                                </li>
                                @endif
                                @if(Auth::user()->isAdmin() || Auth::user()->isHRD())
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                        <i class="bi bi-person-gear"></i> Manajemen User
                                    </a>
                                </li>
                                @endif
                            @endauth
                        </ul>
                        
                        @auth
                        <hr class="text-white-50">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a>
                            </li>
                        </ul>
                        
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                        @endauth
                    </div>
                </nav>
                
                <!-- Main content -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2">@yield('page-title', 'Dashboard')</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            @yield('page-actions')
                        </div>
                    </div>
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @yield('content')
                </main>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
