<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Payroll</title>
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
        .text-right {
            text-align: right;
        }
        .currency {
            font-weight: bold;
            color: #28a745;
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
            margin-bottom: 10px;
        }
        .total-row {
            background-color: #e9ecef !important;
            font-weight: bold;
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
                    <h1 style="margin-bottom: 5px;">LAPORAN PAYROLL KARYAWAN</h1>
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
        @if(isset($filters['bulan']) && isset($filters['tahun']))
            <p>Periode: {{ $filters['bulan'] }}/{{ $filters['tahun'] }}</p>
        @endif
        <strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}<br>
        @if(isset($filters['pegawai_name']))
            <strong>Pegawai:</strong> {{ $filters['pegawai_name'] }}<br>
        @endif
        <strong>Total Data:</strong> {{ isset($payroll) ? count($payroll) : 0 }} record
    </div>

    @if(isset($payroll) && count($payroll) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 18%;">Nama Pegawai</th>
                    <th style="width: 12%;">Posisi</th>
                    <th style="width: 10%;">Periode</th>
                    <th style="width: 11%;">Gaji Pokok</th>
                    <th style="width: 11%;">Gaji Bonus</th>
                    <th style="width: 11%;">Gaji Absensi</th>
                    <th style="width: 11%;">Total Gaji</th>
                    <th style="width: 11%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @php $totalGaji = 0; @endphp
                @foreach($payroll as $index => $item)
                    @php 
                        // Ambil nama pegawai sesuai struktur API klinik yang benar
                        $namaPegawai = 'Nama Tidak Tersedia';
                        if (isset($item['pegawai']['nama_lengkap'])) {
                            $namaPegawai = $item['pegawai']['nama_lengkap'];
                        } elseif (isset($item['nama_pegawai'])) {
                            $namaPegawai = $item['nama_pegawai'];
                        } elseif (isset($item['pegawai']['user']['nama_user'])) {
                            $namaPegawai = $item['pegawai']['user']['nama_user'];
                        } elseif (isset($item['pegawai']['nama'])) {
                            $namaPegawai = $item['pegawai']['nama'];
                        } elseif (isset($item['nama_lengkap'])) {
                            $namaPegawai = $item['nama_lengkap'];
                        } elseif (isset($item['nama'])) {
                            $namaPegawai = $item['nama'];
                        } elseif (isset($item['user']['name'])) {
                            $namaPegawai = $item['user']['name'];
                        }
                        
                        // Ambil posisi sesuai struktur API klinik yang benar
                        $posisi = 'Posisi Tidak Tersedia';
                        if (isset($item['pegawai']['posisi']['nama_posisi'])) {
                            $posisi = $item['pegawai']['posisi']['nama_posisi'];
                        } elseif (isset($item['posisi'])) {
                            $posisi = is_array($item['posisi']) ? ($item['posisi']['nama_posisi'] ?? 'Posisi Tidak Tersedia') : $item['posisi'];
                        } elseif (isset($item['nama_posisi'])) {
                            $posisi = $item['nama_posisi'];
                        } elseif (isset($item['position'])) {
                            $posisi = $item['position'];
                        }
                        
                        // Ambil periode sesuai struktur API klinik (periode_bulan dan periode_tahun)
                        $periode = 'N/A';
                        if (isset($item['periode_bulan']) && isset($item['periode_tahun'])) {
                            $periode = $item['periode_bulan'] . '/' . $item['periode_tahun'];
                        } elseif (isset($item['bulan']) && isset($item['tahun'])) {
                            $periode = $item['bulan'] . '/' . $item['tahun'];
                        } elseif (isset($item['periode'])) {
                            $periode = $item['periode'];
                        }
                        
                        // Ambil data gaji sesuai struktur API klinik yang benar
                        $gajiPokok = floatval($item['gaji_pokok'] ?? 0);
                        $gajiBonus = floatval($item['gaji_bonus'] ?? 0);
                        
                        // API klinik menggunakan gaji_kehadiran, bukan gaji_absensi
                        $gajiKehadiran = floatval($item['gaji_kehadiran'] ?? 0);
                        $gajiAbsensi = $gajiKehadiran; // untuk display
                        
                        $totalItem = floatval($item['gaji_total'] ?? ($gajiPokok + $gajiBonus + $gajiKehadiran));
                        
                        $totalGaji += $totalItem;
                        
                        // Ambil status
                        $status = $item['status'] ?? 'pending';
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $namaPegawai }}</strong></td>
                        <td>{{ $posisi }}</td>
                        <td class="text-center">{{ $periode }}</td>
                        <td class="text-right currency">Rp {{ number_format($gajiPokok, 0, ',', '.') }}</td>
                        <td class="text-right currency">Rp {{ number_format($gajiBonus, 0, ',', '.') }}</td>
                        <td class="text-right currency">Rp {{ number_format($gajiAbsensi, 0, ',', '.') }}</td>
                        <td class="text-right currency"><strong>Rp {{ number_format($totalItem, 0, ',', '.') }}</strong></td>
                        <td class="text-center">
                            @php
                                $statusClass = '';
                                $statusText = ucfirst($status);
                                switch(strtolower($status)) {
                                    case 'dibayar':
                                    case 'paid':
                                    case 'lunas':
                                        $statusClass = '#28a745';
                                        $statusText = 'Dibayar';
                                        break;
                                    case 'pending':
                                    case 'menunggu':
                                        $statusClass = '#ffc107';
                                        $statusText = 'Pending';
                                        break;
                                    case 'ditolak':
                                    case 'rejected':
                                        $statusClass = '#dc3545';
                                        $statusText = 'Ditolak';
                                        break;
                                    default:
                                        $statusClass = '#6c757d';
                                        break;
                                }
                            @endphp
                            <span style="color: {{ $statusClass }}; font-weight: bold;">
                                {{ $statusText }}
                            </span>
                        </td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="7" class="text-right"><strong>TOTAL KESELURUHAN:</strong></td>
                    <td class="text-right currency"><strong>Rp {{ number_format($totalGaji, 0, ',', '.') }}</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="summary">
            <h3>Ringkasan Payroll</h3>
            @php
                $statusCounts = ['dibayar' => 0, 'pending' => 0, 'ditolak' => 0];
                foreach($payroll as $item) {
                    $status = $item['status_pembayaran'] ?? 'pending';
                    if(isset($statusCounts[$status])) {
                        $statusCounts[$status]++;
                    }
                }
            @endphp
            <div class="summary-item"><strong>Total Karyawan:</strong> {{ count($payroll) }} orang</div>
            <div class="summary-item"><strong>Sudah Dibayar:</strong> {{ $statusCounts['dibayar'] }} orang</div>
            <div class="summary-item"><strong>Pending:</strong> {{ $statusCounts['pending'] }} orang</div>
            <div class="summary-item"><strong>Total Pengeluaran:</strong> Rp {{ number_format($totalGaji, 0, ',', '.') }}</div>
        </div>
    @else
        <div style="text-align: center; padding: 50px; color: #666;">
            <h3>Tidak ada data payroll untuk ditampilkan</h3>
            <p>Silakan periksa filter yang digunakan atau periode yang dipilih.</p>
        </div>
    @endif

    <div class="footer">
        Dicetak pada {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }} | 
        {{ config('app.name', 'Klinik Management System') }}
    </div>
</body>
</html>
