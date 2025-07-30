<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Data Pegawai</title>
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
        .text-center {
            text-align: center;
        }
        .status-aktif {
            color: #28a745;
            font-weight: bold;
        }
        .status-nonaktif {
            color: #dc3545;
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
        <table width="100%" style="border: none;">
            <tr>
                <td style="width: 100px; text-align: center; vertical-align: top;">
                    <img src="{{ public_path('images/nesh.jpeg') }}" alt="Foto Nesh Navya" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 1px solid #ccc;">
                </td>
                <td style="vertical-align: top; padding-left: 15px;">
                    <h1 style="margin-bottom: 5px;">LAPORAN DATA PEGAWAI</h1>
                    <h2 style="margin: 2px 0;">Nesh Navya</h2>
                    <div style="font-size: 11px; color: #333; line-height: 1.6;">
                        <strong>Alamat:</strong> Jl. WR Supratman No.248, Kesiman Kertalangu, Kec. Denpasar Tim., Kota Denpasar, Bali 80237<br>
                        <strong>Telepon:</strong> 081703222719<br>
                        @if(isset($filters['start_date']) && isset($filters['end_date']))
                            <strong>Periode:</strong> {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}<br>
        @if(isset($filters['posisi']))
            <strong>Posisi Filter:</strong> {{ $filters['posisi'] }}<br>
        @endif
        @if(isset($filters['jenis_kelamin']))
            <strong>Jenis Kelamin Filter:</strong> {{ ucfirst($filters['jenis_kelamin']) }}<br>
        @endif
        @if(isset($filters['search']))
            <strong>Pencarian:</strong> {{ $filters['search'] }}<br>
        @endif
        <strong>Total Data:</strong> {{ isset($pegawai) ? count($pegawai) : 0 }} pegawai
    </div>

    @if(isset($pegawai) && count($pegawai) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 20%;">Nama Lengkap</th>
                    <th style="width: 15%;">Email</th>
                    <th style="width: 12%;">Telepon</th>
                    <th style="width: 8%;">Jenis Kelamin</th>
                    <th style="width: 15%;">Posisi</th>
                    <th style="width: 10%;">Tanggal Masuk</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 5%;">Gaji Pokok</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai as $index => $item)
                    @php
                        // Ambil nama pegawai sesuai struktur API klinik yang benar
                        $namaPegawai = 'Nama Tidak Tersedia';
                        if (isset($item['nama_lengkap']) && !empty($item['nama_lengkap'])) {
                            $namaPegawai = $item['nama_lengkap'];
                        } elseif (isset($item['user']['nama_user']) && !empty($item['user']['nama_user'])) {
                            $namaPegawai = $item['user']['nama_user'];
                        } elseif (isset($item['nama']) && !empty($item['nama'])) {
                            $namaPegawai = $item['nama'];
                        } elseif (isset($item['name']) && !empty($item['name'])) {
                            $namaPegawai = $item['name'];
                        } elseif (isset($item['full_name']) && !empty($item['full_name'])) {
                            $namaPegawai = $item['full_name'];
                        }
                        
                        // Ambil gaji pokok sesuai struktur API klinik yang benar
                        $gajiPokok = 0;
                        if (isset($item['gaji_pokok']) && is_numeric($item['gaji_pokok'])) {
                            $gajiPokok = floatval($item['gaji_pokok']);
                        } elseif (isset($item['posisi']['gaji_pokok']) && is_numeric($item['posisi']['gaji_pokok'])) {
                            $gajiPokok = floatval($item['posisi']['gaji_pokok']);
                        } elseif (isset($item['basic_salary']) && is_numeric($item['basic_salary'])) {
                            $gajiPokok = floatval($item['basic_salary']);
                        } elseif (isset($item['salary']) && is_numeric($item['salary'])) {
                            $gajiPokok = floatval($item['salary']);
                        }
                        
                        // Ambil posisi sesuai struktur API klinik yang benar
                        $posisi = 'Posisi Tidak Tersedia';
                        if (isset($item['posisi']['nama_posisi'])) {
                            $posisi = $item['posisi']['nama_posisi'];
                        } elseif (isset($item['nama_posisi'])) {
                            $posisi = $item['nama_posisi'];
                        } elseif (isset($item['position'])) {
                            $posisi = $item['position'];
                        }
                        
                        // Data lainnya sesuai struktur API klinik
                        $email = $item['email'] ?? $item['user']['email'] ?? 'Email Tidak Tersedia';
                        $telepon = $item['telepon'] ?? $item['phone'] ?? $item['no_hp'] ?? $item['user']['no_telp'] ?? 'Telepon Tidak Tersedia';
                        $jenisKelamin = $item['jenis_kelamin'] ?? $item['gender'] ?? 'Tidak Diketahui';
                        
                        // Status berdasarkan tanggal_keluar
                        $status = 'aktif';
                        if (isset($item['tanggal_keluar']) && !empty($item['tanggal_keluar'])) {
                            $status = 'nonaktif';
                        } elseif (isset($item['status'])) {
                            $status = $item['status'];
                        }
                        
                        // Format tanggal masuk
                        $tanggalMasuk = 'Tanggal Tidak Tersedia';
                        if (isset($item['tanggal_masuk']) && !empty($item['tanggal_masuk'])) {
                            try {
                                $tanggalMasuk = \Carbon\Carbon::parse($item['tanggal_masuk'])->format('d/m/Y');
                            } catch (\Exception $e) {
                                $tanggalMasuk = $item['tanggal_masuk'];
                            }
                        } elseif (isset($item['hire_date'])) {
                            try {
                                $tanggalMasuk = \Carbon\Carbon::parse($item['hire_date'])->format('d/m/Y');
                            } catch (\Exception $e) {
                                $tanggalMasuk = $item['hire_date'];
                            }
                        }
                        
                        // Status styling
                        $statusClass = strtolower($status) === 'aktif' ? 'status-aktif' : 'status-nonaktif';
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $namaPegawai }}</strong></td>
                        <td>{{ $email }}</td>
                        <td>{{ $telepon }}</td>
                        <td class="text-center">{{ ucfirst($jenisKelamin) }}</td>
                        <td>{{ $posisi }}</td>
                        <td class="text-center">{{ $tanggalMasuk }}</td>
                        <td class="text-center">
                            <span class="{{ $statusClass }}" style="font-weight: bold; color: {{ strtolower($status) === 'aktif' ? '#28a745' : '#dc3545' }};">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                        <td class="text-right">
                            @if($gajiPokok > 0)
                                <strong style="color: #28a745;">Rp {{ number_format($gajiPokok, 0, ',', '.') }}</strong>
                            @else
                                <span style="color: #6c757d;">Data Tidak Tersedia</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php
            $summary = [
                'total' => count($pegawai),
                'aktif' => 0,
                'nonaktif' => 0,
                'laki_laki' => 0,
                'perempuan' => 0,
                'posisi_count' => []
            ];
            
            foreach($pegawai as $item) {
                // Status
                $status = $item['status'] ?? 'aktif';
                if($status === 'aktif') $summary['aktif']++;
                else $summary['nonaktif']++;
                
                // Jenis Kelamin
                $gender = strtolower($item['jenis_kelamin'] ?? '');
                if($gender === 'laki-laki' || $gender === 'l') $summary['laki_laki']++;
                elseif($gender === 'perempuan' || $gender === 'p') $summary['perempuan']++;
                
                // Posisi
                $posisi = $item['posisi']['nama_posisi'] ?? $item['nama_posisi'] ?? 'N/A';
                if(!isset($summary['posisi_count'][$posisi])) {
                    $summary['posisi_count'][$posisi] = 0;
                }
                $summary['posisi_count'][$posisi]++;
            }
        @endphp

        <div class="summary">
            <h3>Ringkasan Data Pegawai</h3>
            <div class="summary-item"><strong>Total Pegawai:</strong> {{ $summary['total'] }} orang</div>
            <div class="summary-item"><strong>Status Aktif:</strong> {{ $summary['aktif'] }} orang</div>
            <div class="summary-item"><strong>Status Non-Aktif:</strong> {{ $summary['nonaktif'] }} orang</div>
            <div class="summary-item"><strong>Laki-laki:</strong> {{ $summary['laki_laki'] }} orang</div>
            <div class="summary-item"><strong>Perempuan:</strong> {{ $summary['perempuan'] }} orang</div>
            
            @if(count($summary['posisi_count']) > 0)
                <div style="margin-top: 15px;">
                    <strong>Distribusi per Posisi:</strong><br>
                    @foreach($summary['posisi_count'] as $posisi => $count)
                        <div class="summary-item">{{ $posisi }}: {{ $count }} orang</div>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <div style="text-align: center; padding: 50px; color: #666;">
            <h3>Tidak ada data pegawai untuk ditampilkan</h3>
            <p>Silakan periksa filter yang digunakan.</p>
        </div>
    @endif

    <div class="footer">
        Dicetak pada {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }} | 
        {{ config('app.name', 'Klinik Management System') }}
    </div>
</body>
</html>
