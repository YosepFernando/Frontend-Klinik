<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header h2 {
            margin: 5px 0;
            color: #666;
            font-size: 16px;
            font-weight: normal;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section strong {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-hadir {
            color: #28a745;
            font-weight: bold;
        }
        .status-izin {
            color: #ffc107;
            font-weight: bold;
        }
        .status-sakit {
            color: #dc3545;
            font-weight: bold;
        }
        .status-alpha {
            color: #6c757d;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .summary h3 {
            margin-top: 0;
            color: #333;
        }
        .summary-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN ABSENSI KARYAWAN</h1>
        <h2>{{ config('app.name', 'Klinik Management System') }}</h2>
        @if(isset($filters['start_date']) && isset($filters['end_date']))
            <p>Periode: {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}</p>
        @endif
    </div>

    <div class="info-section">
        <strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}<br>
        @if(isset($filters['pegawai_name']))
            <strong>Pegawai:</strong> {{ $filters['pegawai_name'] }}<br>
        @endif
        @if(isset($filters['status']))
            <strong>Status Filter:</strong> {{ ucfirst($filters['status']) }}<br>
        @endif
        <strong>Total Data:</strong> {{ isset($absensi) ? count($absensi) : 0 }} record
    </div>

    @if(count($absensi) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 25%;">Nama Pegawai</th>
                    <th style="width: 12%;">Tanggal</th>
                    <th style="width: 10%;">Check In</th>
                    <th style="width: 10%;">Check Out</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 26%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($absensi as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @php
                                $namaKaryawan = 'Data Tidak Tersedia';
                                
                                if (is_array($item)) {
                                    // Struktur API klinik yang benar
                                    if (isset($item['pegawai']['nama_lengkap'])) {
                                        $namaKaryawan = $item['pegawai']['nama_lengkap'];
                                    } elseif (isset($item['nama_pegawai'])) {
                                        $namaKaryawan = $item['nama_pegawai'];
                                    } elseif (isset($item['pegawai']['user']['nama_user'])) {
                                        $namaKaryawan = $item['pegawai']['user']['nama_user'];
                                    } elseif (isset($item['pegawai']['nama'])) {
                                        $namaKaryawan = $item['pegawai']['nama'];
                                    } elseif (isset($item['nama_lengkap'])) {
                                        $namaKaryawan = $item['nama_lengkap'];
                                    } else {
                                        // Fallback untuk kompatibilitas
                                        $namaKaryawan = $item['nama'] ?? 
                                                       $item['user_name'] ?? 
                                                       $item['pegawai_nama'] ?? 
                                                       $item['name'] ?? 
                                                       $item['full_name'] ?? 
                                                       'Data Tidak Tersedia';
                                                       
                                        // Jika masih kosong, coba akses nested
                                        if ($namaKaryawan === 'Data Tidak Tersedia' && isset($item['user'])) {
                                            $namaKaryawan = $item['user']['name'] ?? 
                                                           $item['user']['nama'] ?? 
                                                           'Data Tidak Tersedia';
                                        }
                                    }
                                } elseif (is_object($item)) {
                                    // Untuk objek/model
                                    $namaKaryawan = $item->pegawai->nama_lengkap ?? 
                                                   $item->pegawai->user->nama_user ?? 
                                                   $item->nama_pegawai ?? 
                                                   'Data Tidak Tersedia';
                                }
                            @endphp
                                } elseif (is_object($item)) {
                                    $namaKaryawan = $item->nama_pegawai ?? 
                                                   $item->pegawai->nama ?? 
                                                   $item->nama ?? 
                                                   $item->user_name ?? 
                                                   $item->pegawai_nama ?? 
                                                   $item->name ?? 
                                                   $item->full_name ?? 
                                                   'Data Tidak Tersedia';
                                                   
                                    // Jika masih kosong, coba akses nested
                                    if ($namaKaryawan === 'Data Tidak Tersedia' && isset($item->user)) {
                                        $namaKaryawan = $item->user->name ?? 
                                                       $item->user->nama ?? 
                                                       'Data Tidak Tersedia';
                                    }
                                }
                                
                                // Fallback jika masih kosong
                                if (empty($namaKaryawan) || $namaKaryawan === 'Data Tidak Tersedia') {
                                    $namaKaryawan = 'Nama Tidak Ditemukan';
                                }
                            @endphp
                            <strong>{{ $namaKaryawan }}</strong>
                        </td>
                        <td>
                            @php
                                $tanggal = '';
                                if (is_array($item)) {
                                    $tanggal = $item['tanggal'] ?? '';
                                } elseif (is_object($item)) {
                                    $tanggal = $item->tanggal ?? '';
                                }
                            @endphp
                            {{ $tanggal ? \Carbon\Carbon::parse($tanggal)->format('d/m/Y') : 'N/A' }}
                        </td>
                        <td>
                            @php
                                $waktuMasuk = '';
                                if (is_array($item)) {
                                    $waktuMasuk = $item['waktu_masuk'] ?? $item['jam_masuk'] ?? '';
                                } elseif (is_object($item)) {
                                    $waktuMasuk = $item->waktu_masuk ?? $item->jam_masuk ?? '';
                                }
                            @endphp
                            {{ $waktuMasuk ? \Carbon\Carbon::parse($waktuMasuk)->format('H:i') : '-' }}
                        </td>
                        <td>
                            @php
                                $waktuKeluar = '';
                                if (is_array($item)) {
                                    $waktuKeluar = $item['waktu_keluar'] ?? $item['jam_keluar'] ?? '';
                                } elseif (is_object($item)) {
                                    $waktuKeluar = $item->waktu_keluar ?? $item->jam_keluar ?? '';
                                }
                            @endphp
                            {{ $waktuKeluar ? \Carbon\Carbon::parse($waktuKeluar)->format('H:i') : '-' }}
                        </td>
                        <td>
                            @php
                                $status = 'Hadir';
                                if (is_array($item)) {
                                    $status = $item['status'] ?? 'Hadir';
                                } elseif (is_object($item)) {
                                    $status = $item->status ?? 'Hadir';
                                }
                                $statusClass = 'status-' . strtolower($status);
                            @endphp
                            <span class="{{ $statusClass }}">{{ ucfirst($status) }}</span>
                        </td>
                        <td>
                            @php
                                $keterangan = '';
                                if (is_array($item)) {
                                    $keterangan = $item['keterangan'] ?? $item['catatan'] ?? '';
                                } elseif (is_object($item)) {
                                    $keterangan = $item->keterangan ?? $item->catatan ?? '';
                                }
                            @endphp
                            {{ $keterangan ?: '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $summary = [
                'hadir' => 0,
                'izin' => 0,
                'sakit' => 0,
                'alpha' => 0,
                'terlambat' => 0
            ];
            if (isset($absensi) && is_array($absensi)) {
                foreach($absensi as $item) {
                    $status = '';
                    if (is_array($item)) {
                        $status = strtolower($item['status'] ?? 'alpha');
                    } elseif (is_object($item)) {
                        $status = strtolower($item->status ?? 'alpha');
                    }
                    
                    if($status === 'hadir') {
                        $summary['hadir']++;
                    } elseif($status === 'terlambat') {
                        $summary['terlambat']++;
                    } elseif($status === 'izin') {
                        $summary['izin']++;
                    } elseif($status === 'sakit') {
                        $summary['sakit']++;
                    } else {
                        $summary['alpha']++;
                    }
                }
            }
        @endphp

        <div class="summary">
            <h3>Ringkasan Absensi</h3>
            <div class="summary-item"><strong>Hadir:</strong> {{ $summary['hadir'] }} hari</div>
            <div class="summary-item"><strong>Terlambat:</strong> {{ $summary['terlambat'] }} hari</div>
            <div class="summary-item"><strong>Izin:</strong> {{ $summary['izin'] }} hari</div>
            <div class="summary-item"><strong>Sakit:</strong> {{ $summary['sakit'] }} hari</div>
            <div class="summary-item"><strong>Alpha:</strong> {{ $summary['alpha'] }} hari</div>
        </div>
    @else
        <div style="text-align: center; padding: 50px; color: #666;">
            <h3>Tidak ada data absensi untuk ditampilkan</h3>
            <p>Silakan periksa filter yang digunakan atau periode tanggal.</p>
        </div>
    @endif

    <div class="footer">
        Dicetak pada {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }} | 
        {{ config('app.name', 'Klinik Management System') }}
    </div>
</body>
</html>
