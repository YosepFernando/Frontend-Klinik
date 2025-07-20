<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $nama_pegawai }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
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
            font-weight: bold;
        }
        .header h2 {
            margin: 5px 0;
            color: #666;
            font-size: 16px;
            font-weight: normal;
        }
        .company-info {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }
        .employee-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        .employee-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .employee-info td {
            padding: 8px 0;
            border: none;
        }
        .employee-info .label {
            width: 30%;
            font-weight: bold;
            color: #333;
        }
        .employee-info .separator {
            width: 5%;
            text-align: center;
        }
        .employee-info .value {
            width: 65%;
            color: #555;
        }
        .salary-section {
            margin-bottom: 30px;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .salary-table th,
        .salary-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .salary-table th {
            background-color: #4a90e2;
            color: white;
            font-weight: bold;
        }
        .salary-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .salary-table .amount {
            text-align: right;
            font-weight: bold;
        }
        .salary-table .positive {
            color: #28a745;
        }
        .salary-table .negative {
            color: #dc3545;
        }
        .total-section {
            background: linear-gradient(135deg, #4a90e2, #50c878);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .total-section table {
            width: 100%;
            border-collapse: collapse;
        }
        .total-section td {
            padding: 8px 0;
            border: none;
            color: white;
        }
        .total-section .label {
            font-size: 16px;
            font-weight: bold;
        }
        .total-section .amount {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
        }
        .attendance-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }
        .attendance-info h4 {
            margin-top: 0;
            color: #1976d2;
        }
        .attendance-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 10px;
        }
        .attendance-item {
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 6px;
            border: 1px solid #bbdefb;
        }
        .attendance-item .number {
            font-size: 18px;
            font-weight: bold;
            color: #1976d2;
        }
        .attendance-item .label {
            font-size: 12px;
            color: #666;
        }
        .notes-section {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ffeaa7;
            margin-bottom: 20px;
        }
        .notes-section h4 {
            margin-top: 0;
            color: #856404;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-box .title {
            font-weight: bold;
            margin-bottom: 60px;
        }
        .signature-box .name {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-weight: bold;
        }
        .print-date {
            text-align: right;
            font-size: 10px;
            color: #666;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="print-date">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }}
    </div>

    <div class="header">
        <h1>SLIP GAJI KARYAWAN</h1>
        <h2>{{ config('app.name', 'Klinik Management System') }}</h2>
    </div>

    <div class="company-info">
        <strong>Periode Gaji:</strong> 
        {{ DateTime::createFromFormat('!m', $payroll['periode_bulan'] ?? date('n'))->format('F') }} 
        {{ $payroll['periode_tahun'] ?? date('Y') }}
    </div>

    <!-- Informasi Karyawan -->
    <div class="employee-info">
        <h3 style="margin-top: 0; color: #333;">INFORMASI KARYAWAN</h3>
        <table>
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="separator">:</td>
                <td class="value">{{ $nama_pegawai }}</td>
            </tr>
            <tr>
                <td class="label">NIP</td>
                <td class="separator">:</td>
                <td class="value">{{ $nip }}</td>
            </tr>
            <tr>
                <td class="label">Posisi/Jabatan</td>
                <td class="separator">:</td>
                <td class="value">{{ $posisi }}</td>
            </tr>
            <tr>
                <td class="label">Status Pembayaran</td>
                <td class="separator">:</td>
                <td class="value">
                    <strong style="color: {{ ($payroll['status'] ?? '') == 'Terbayar' ? '#28a745' : '#ffc107' }};">
                        {{ $payroll['status'] ?? 'Belum Terbayar' }}
                    </strong>
                </td>
            </tr>
        </table>
    </div>

    <!-- Informasi Kehadiran -->
    @if(isset($absensi_summary) || isset($payroll['jumlah_absensi']) || isset($payroll['persentase_kehadiran']))
    <div class="attendance-info">
        <h4>INFORMASI KEHADIRAN</h4>
        <div class="attendance-grid">
            @if(isset($absensi_summary))
                <!-- Data dari API absensi_summary -->
                <div class="attendance-item">
                    <div class="number">{{ $absensi_summary['total_hari'] ?? 0 }}</div>
                    <div class="label">Total Hari</div>
                </div>
                <div class="attendance-item">
                    <div class="number">{{ $absensi_summary['tepat_waktu'] ?? 0 }}</div>
                    <div class="label">Tepat Waktu</div>
                </div>
                <div class="attendance-item">
                    <div class="number">{{ $absensi_summary['terlambat'] ?? 0 }}</div>
                    <div class="label">Terlambat</div>
                </div>
                <div class="attendance-item">
                    <div class="number">{{ ($absensi_summary['izin'] ?? 0) + ($absensi_summary['sakit'] ?? 0) + ($absensi_summary['alpha'] ?? 0) }}</div>
                    <div class="label">Tidak Hadir</div>
                </div>
            @else
                <!-- Data dari payroll langsung -->
                <div class="attendance-item">
                    <div class="number">{{ $payroll['jumlah_absensi'] ?? 0 }}</div>
                    <div class="label">Hari Hadir</div>
                </div>
                <div class="attendance-item">
                    <div class="number">{{ $payroll['total_hari_kerja'] ?? 0 }}</div>
                    <div class="label">Total Hari Kerja</div>
                </div>
                <div class="attendance-item">
                    <div class="number">{{ $payroll['persentase_kehadiran'] ?? 0 }}%</div>
                    <div class="label">Persentase Kehadiran</div>
                </div>
                <div class="attendance-item">
                    <div class="number">{{ ($payroll['total_hari_kerja'] ?? 0) - ($payroll['jumlah_absensi'] ?? 0) }}</div>
                    <div class="label">Hari Tidak Hadir</div>
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Detail Gaji -->
    <div class="salary-section">
        <h3 style="color: #333; margin-bottom: 15px;">RINCIAN GAJI</h3>
        <table class="salary-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Komponen</th>
                    <th style="width: 50%;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <!-- Pendapatan -->
                <tr style="background-color: #e8f5e8;">
                    <td colspan="2"><strong>PENDAPATAN</strong></td>
                </tr>
                <tr>
                    <td>Gaji Pokok</td>
                    <td class="amount positive">Rp {{ number_format($payroll['gaji_pokok'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @if(($payroll['gaji_kehadiran'] ?? 0) > 0)
                <tr>
                    <td>Gaji Kehadiran</td>
                    <td class="amount positive">Rp {{ number_format($payroll['gaji_kehadiran'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if(($payroll['gaji_bonus'] ?? 0) > 0)
                <tr>
                    <td>Bonus</td>
                    <td class="amount positive">Rp {{ number_format($payroll['gaji_bonus'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if(($payroll['tunjangan'] ?? 0) > 0)
                <tr>
                    <td>Tunjangan</td>
                    <td class="amount positive">Rp {{ number_format($payroll['tunjangan'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endif
                
                <!-- Potongan -->
                @if(($payroll['potongan'] ?? 0) > 0 || ($payroll['pajak'] ?? 0) > 0)
                <tr style="background-color: #ffeee8;">
                    <td colspan="2"><strong>POTONGAN</strong></td>
                </tr>
                @if(($payroll['potongan'] ?? 0) > 0)
                <tr>
                    <td>Potongan Lain-lain</td>
                    <td class="amount negative">- Rp {{ number_format($payroll['potongan'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if(($payroll['pajak'] ?? 0) > 0)
                <tr>
                    <td>Pajak</td>
                    <td class="amount negative">- Rp {{ number_format($payroll['pajak'] ?? 0, 0, ',', '.') }}</td>
                </tr>
                @endif
                @endif
            </tbody>
        </table>
    </div>

    <!-- Total Gaji -->
    <div class="total-section">
        <table>
            <tr>
                <td class="label">TOTAL GAJI BERSIH</td>
                <td class="amount">Rp {{ number_format($payroll['gaji_total'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Catatan -->
    @if(isset($payroll['keterangan']) && !empty($payroll['keterangan']))
    <div class="notes-section">
        <h4>CATATAN</h4>
        <p>{{ $payroll['keterangan'] }}</p>
    </div>
    @endif

    <!-- Tanda Tangan -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="title">Mengetahui,<br>HRD</div>
            <div class="name">( _________________ )</div>
        </div>
        <div class="signature-box">
            <div class="title">Penerima,<br>{{ $nama_pegawai }}</div>
            <div class="name">( _________________ )</div>
        </div>
    </div>

    <div class="footer">
        <p><strong>PENTING:</strong> Slip gaji ini adalah dokumen resmi. Harap disimpan dengan baik.</p>
        <p>Dokumen ini dicetak secara otomatis pada {{ \Carbon\Carbon::now()->format('d M Y H:i:s') }} | 
        {{ config('app.name', 'Klinik Management System') }}</p>
    </div>
</body>
</html>
