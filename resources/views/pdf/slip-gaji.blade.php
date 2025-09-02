<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $nama_pegawai }}</title>
    <style>
        @page {
            margin: 20mm;
            size: A4;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header img {
            width: 80px;
            height: 80px;
        }
        .company-info {
            margin: 15px 0;
            font-weight: bold;
        }
        .employee-info,
        .attendance-info,
        .salary-section,
        .total-section,
        .notes-section {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 15px;
        }
        .employee-info table,
        .total-section table,
        .salary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .employee-info td {
            padding: 4px;
        }
        .employee-info .label {
            width: 30%;
            font-weight: bold;
        }
        .employee-info .separator {
            width: 5%;
        }
        .attendance-grid {
            display: flex;
            justify-content: space-between;
            text-align: center;
        }
        .attendance-item {
            flex: 1;
            padding: 10px;
        }
        .attendance-item .number {
            font-size: 16px;
            font-weight: bold;
            color: #007bff;
        }
        .salary-section h3 {
            margin-bottom: 10px;
        }
        .salary-table th, .salary-table td {
            border: 1px solid #ccc;
            padding: 6px;
        }
        .salary-table th {
            background-color: #007bff;
            color: white;
        }
        .salary-table .amount {
            text-align: right;
        }
        .total-section td {
            padding: 10px;
            font-weight: bold;
            font-size: 14px;
        }
        .total-section .label {
            width: 70%;
        }
        .total-section .amount {
            width: 30%;
            text-align: right;
            color: #007bff;
        }
        .notes-section p {
            margin: 0;
            font-style: italic;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-top: 30px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>

<div class="header">
    <table width="100%">
        <tr>
            <td width="100" align="center">
                <img src="{{ public_path('images/nesh.jpeg') }}" alt="Foto">
            </td>
            <td align="left">
                <h2>SLIP GAJI PEGAWAI</h2>
                <h4>Nesh Navya</h4>
                <div style="font-size: 11px;">
                    <strong>Alamat:</strong> Jl. WR Supratman No.248, Kesiman Kertalangu, Denpasar, Bali<br>
                    <strong>Telepon:</strong> 081703222719<br>
                    @if(isset($filters['start_date']) && isset($filters['end_date']))
                        <strong>Periode:</strong> {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="company-info">
    Periode Gaji: {{ \Carbon\Carbon::create(null, $payroll['periode_bulan'] ?? date('n'))->translatedFormat('F') }}
    {{ $payroll['periode_tahun'] ?? date('Y') }}
</div>

<!-- Informasi Karyawan -->
<div class="employee-info">
    <table>
        <tr>
            <td class="label">Nama Lengkap</td>
            <td class="separator">:</td>
            <td>{{ $nama_pegawai }}</td>
        </tr>
        <tr>
            <td class="label">NIP</td>
            <td class="separator">:</td>
            <td>{{ $nip }}</td>
        </tr>
        <tr>
            <td class="label">Posisi/Jabatan</td>
            <td class="separator">:</td>
            <td>{{ $posisi }}</td>
        </tr>
        <tr>
            <td class="label">Status Pembayaran</td>
            <td class="separator">:</td>
            <td><strong>{{ $payroll['status'] ?? 'Belum Terbayar' }}</strong></td>
        </tr>
    </table>
</div>

<!-- Kehadiran -->
@if(isset($payroll['jumlah_absensi']) || isset($payroll['persentase_kehadiran']))
<div class="attendance-info">
    <div class="attendance-grid">
        <div class="attendance-item">
            <div class="number">{{ $payroll['jumlah_absensi'] ?? 0 }}</div>
            <div>Hari Hadir</div>
        </div>
        <div class="attendance-item">
            <div class="number">{{ $payroll['total_hari_kerja'] ?? 0 }}</div>
            <div>Total Hari Kerja</div>
        </div>
        <div class="attendance-item">
            <div class="number">{{ $payroll['persentase_kehadiran'] ?? 0 }}%</div>
            <div>Persentase Kehadiran</div>
        </div>
        <div class="attendance-item">
            <div class="number">{{ ($payroll['total_hari_kerja'] ?? 0) - ($payroll['jumlah_absensi'] ?? 0) }}</div>
            <div>Hari Tidak Hadir</div>
        </div>
    </div>
</div>
@endif

<!-- Rincian Gaji -->
<div class="salary-section">
    <h3>RINCIAN GAJI</h3>
    <table class="salary-table">
        <thead>
            <tr>
                <th>Komponen</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="2"><strong>PENDAPATAN</strong></td></tr>
            <tr>
                <td>Gaji Pokok</td>
                <td class="amount">Rp {{ number_format($payroll['gaji_pokok'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            @if(!empty($payroll['gaji_kehadiran']))
            <tr>
                <td>Gaji Kehadiran</td>
                <td class="amount">Rp {{ number_format($payroll['gaji_kehadiran'], 0, ',', '.') }}</td>
            </tr>
            @endif
            @if(!empty($payroll['gaji_bonus']))
            <tr>
                <td>Bonus</td>
                <td class="amount">Rp {{ number_format($payroll['gaji_bonus'], 0, ',', '.') }}</td>
            </tr>
            @endif
            @if(!empty($payroll['potongan']) || !empty($payroll['pajak']))
            <tr><td colspan="2"><strong>POTONGAN</strong></td></tr>
                @if(!empty($payroll['potongan']))
                <tr>
                    <td>Potongan Lain-lain</td>
                    <td class="amount">- Rp {{ number_format($payroll['potongan'], 0, ',', '.') }}</td>
                </tr>
                @endif
                @if(!empty($payroll['pajak']))
                <tr>
                    <td>Pajak</td>
                    <td class="amount">- Rp {{ number_format($payroll['pajak'], 0, ',', '.') }}</td>
                </tr>
                @endif
            @endif
        </tbody>
    </table>
</div>

<!-- Total -->
<div class="total-section">
    <table>
        <tr>
            <td class="label">TOTAL GAJI BERSIH</td>
            <td class="amount">Rp {{ number_format($payroll['gaji_total'] ?? 0, 0, ',', '.') }}</td>
        </tr>
    </table>
</div>

<!-- Catatan -->
@if(!empty($payroll['keterangan']))
<div class="notes-section">
    <p>{{ $payroll['keterangan'] }}</p>
</div>
@endif

<!-- Footer -->
<div class="footer">
    <p><strong>Slip Gaji ini adalah dokumen resmi.</strong> Harap disimpan dengan baik.</p>
    <p>Dicetak pada: {{ $tanggal_cetak->format('d M Y H:i:s') }} | {{ config('app.name', 'Klinik Management System') }}</p>
</div>

</body>
</html>
