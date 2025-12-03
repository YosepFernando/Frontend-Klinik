<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judul }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 15px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header h2 {
            color: #666;
            margin: 5px 0;
            font-size: 14px;
            font-weight: normal;
        }
        
        .header .clinic-info {
            font-size: 10px;
            color: #666;
            margin-top: 8px;
            line-height: 1.4;
        }
        
        .info-section {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .info-section table {
            width: 100%;
            font-size: 10px;
        }
        
        .info-section td {
            padding: 3px 0;
        }
        
        .info-section .label {
            font-weight: bold;
            width: 130px;
        }
        
        .grand-total-section {
            margin: 15px 0;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 5px;
        }
        
        .grand-total-section h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        .grand-total-grid {
            display: table;
            width: 100%;
            font-size: 11px;
        }
        
        .grand-total-row {
            display: table-row;
        }
        
        .grand-total-cell {
            display: table-cell;
            padding: 5px 10px;
            text-align: center;
            border-right: 1px solid rgba(255,255,255,0.3);
        }
        
        .grand-total-cell:last-child {
            border-right: none;
        }
        
        .grand-total-cell .value {
            font-size: 18px;
            font-weight: bold;
            display: block;
            margin-bottom: 3px;
        }
        
        .grand-total-cell .label {
            font-size: 9px;
            opacity: 0.9;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9px;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 6px 4px;
            text-align: center;
        }
        
        .data-table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            font-size: 9px;
        }
        
        .data-table td.text-left {
            text-align: left;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .summary-footer {
            margin-top: 15px;
            padding: 10px;
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            font-size: 10px;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        @page {
            margin: 10mm;
            size: A4 landscape;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKAP ABSENSI BULANAN</h1>
        <h2>{{ $periode }}</h2>
        <div class="clinic-info">
            <strong>Nesh Navya Clinic</strong><br>
            Jl. WR Supratman No.248, Kesiman Kertalangu, Denpasar Timur, Bali 80237<br>
            Telepon: 081703222719
        </div>
    </div>

    <div class="info-section">
        <table>
            <tr>
                <td class="label">Periode Laporan</td>
                <td>: {{ $bulan }} {{ $tahun }}</td>
                <td class="label" style="padding-left: 50px;">Tanggal Export</td>
                <td>: {{ $tanggal_export }}</td>
            </tr>
            <tr>
                <td class="label">Pegawai</td>
                <td>: {{ $nama_pegawai }}</td>
                <td class="label" style="padding-left: 50px;">Total Pegawai</td>
                <td>: {{ isset($grand_total['total_pegawai']) ? $grand_total['total_pegawai'] : count($summary ?? []) }} orang</td>
            </tr>
        </table>
    </div>

    @if(isset($grand_total) && !empty($grand_total))
    <div class="grand-total-section">
        <h3>📊 RINGKASAN TOTAL ABSENSI</h3>
        <div class="grand-total-grid">
            <div class="grand-total-row">
                <div class="grand-total-cell">
                    <span class="value">{{ $grand_total['total_absensi'] ?? 0 }}</span>
                    <span class="label">Total Absensi</span>
                </div>
                <div class="grand-total-cell">
                    <span class="value">{{ $grand_total['total_hadir'] ?? 0 }}</span>
                    <span class="label">✅ Hadir</span>
                </div>
                <div class="grand-total-cell">
                    <span class="value">{{ $grand_total['total_terlambat'] ?? 0 }}</span>
                    <span class="label">⏰ Terlambat</span>
                </div>
                <div class="grand-total-cell">
                    <span class="value">{{ $grand_total['total_sakit'] ?? 0 }}</span>
                    <span class="label">🤒 Sakit</span>
                </div>
                <div class="grand-total-cell">
                    <span class="value">{{ $grand_total['total_cuti'] ?? 0 }}</span>
                    <span class="label">🏖️ Cuti</span>
                </div>
                <div class="grand-total-cell">
                    <span class="value">{{ $grand_total['total_izin'] ?? 0 }}</span>
                    <span class="label">📝 Izin</span>
                </div>
                <div class="grand-total-cell">
                    <span class="value">{{ $grand_total['total_alpa'] ?? 0 }}</span>
                    <span class="label">❌ Alpa</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($summary) && is_array($summary) && count($summary) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 20%;">Nama Pegawai</th>
                    <th style="width: 12%;">Posisi</th>
                    <th style="width: 8%;">Total<br>Absensi</th>
                    <th style="width: 8%;">Hadir</th>
                    <th style="width: 8%;">Terlambat</th>
                    <th style="width: 8%;">Sakit</th>
                    <th style="width: 8%;">Cuti</th>
                    <th style="width: 8%;">Izin</th>
                    <th style="width: 8%;">Alpa</th>
                    <th style="width: 9%;">Persentase<br>Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary as $index => $pegawai)
                @php
                    $totalAbsensi = $pegawai['total_absensi'] ?? 0;
                    $hadir = $pegawai['hadir'] ?? 0;
                    $terlambat = $pegawai['terlambat'] ?? 0;
                    $persenKehadiran = $totalAbsensi > 0 ? round((($hadir + $terlambat) / $totalAbsensi) * 100, 1) : 0;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $pegawai['nama_pegawai'] ?? 'N/A' }}</td>
                    <td>{{ $pegawai['posisi'] ?? 'N/A' }}</td>
                    <td><strong>{{ $totalAbsensi }}</strong></td>
                    <td>{{ $hadir }}</td>
                    <td>{{ $terlambat }}</td>
                    <td>{{ $pegawai['sakit'] ?? 0 }}</td>
                    <td>{{ $pegawai['cuti'] ?? 0 }}</td>
                    <td>{{ $pegawai['izin'] ?? 0 }}</td>
                    <td>{{ $pegawai['alpa'] ?? 0 }}</td>
                    <td>
                        <strong>{{ $persenKehadiran }}%</strong>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-footer">
            <strong>Catatan:</strong>
            <ul style="margin: 5px 0; padding-left: 20px;">
                <li>Total Absensi = Jumlah keseluruhan catatan absensi pegawai dalam periode {{ $bulan }} {{ $tahun }}</li>
                <li>Persentase Kehadiran = (Hadir + Terlambat) / Total Absensi × 100%</li>
                <li>Laporan ini dibuat secara otomatis oleh sistem pada {{ $tanggal_export }}</li>
            </ul>
        </div>
    @else
        <div style="text-align: center; padding: 40px; color: #999;">
            <p style="font-size: 14px; margin: 0;">📭 Tidak ada data absensi untuk periode ini</p>
            <p style="font-size: 11px; margin: 5px 0;">Periode: {{ $bulan }} {{ $tahun }}</p>
        </div>
    @endif

    <div class="footer">
        <p>
            <strong> Clinic - Sistem Manajemen Absensi</strong><br>
            Dokumen ini digenerate secara otomatis pada {{ $tanggal_export }}<br>
            © {{ date('Y') }} Nesh Navya. All rights reserved.
        </p>
    </div>
</body>
</html>
