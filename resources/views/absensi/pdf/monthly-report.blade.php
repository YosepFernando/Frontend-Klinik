<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judul }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }
        
        .header h2 {
            color: #666;
            margin: 5px 0;
            font-size: 16px;
            font-weight: normal;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .info-table td {
            padding: 3px 0;
            font-size: 11px;
        }
        
        .info-table .label {
            font-weight: bold;
            width: 120px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .data-table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .data-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }
        
        .status-hadir {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-sakit {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-izin {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .status-alpa {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .summary h3 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 14px;
        }
        
        .summary-table {
            width: 100%;
            font-size: 11px;
        }
        
        .summary-table td {
            padding: 3px 8px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .summary-table .label {
            font-weight: bold;
            width: 200px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        @page {
            margin: 15mm;
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
                    <h1 style="margin-bottom: 5px;">{{ $judul }}</h1>
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

    <table class="info-table">
        <tr>
            <td class="label">Tanggal Export</td>
            <td>: {{ $tanggal_export }}</td>
        </tr>
        <tr>
            <td class="label">Total Data</td>
            <td>: {{ $total_records }} record</td>
        </tr>
        @if($nama_pegawai !== 'Semua Pegawai')
        <tr>
            <td class="label">Nama Pegawai</td>
            <td>: {{ $nama_pegawai }}</td>
        </tr>
        @endif
    </table>

    @if(isset($absensi) && is_array($absensi) && count($absensi) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama Pegawai</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($absensi as $index => $item)
                    <tr>
                        <td style="text-align: center">{{ $index + 1 }}</td>
                        <td>{{ $item['tanggal'] }}</td>
                        <td>{{ $item['nama_pegawai'] }}</td>
                        <td>{{ $item['jam_masuk'] }}</td>
                        <td>{{ $item['jam_keluar'] }}</td>
                        <td>
                            <div class="status-badge status-{{ strtolower($item['status']) }}">
                                {{ $item['status'] }}
                            </div>
                        </td>
                        <td>{{ $item['keterangan'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; margin-top: 50px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;">
            <p style="color: #666; font-size: 14px;">Tidak ada data absensi untuk periode {{ $periode }}</p>
        </div>
    @endif

    <div style="margin-top: 30px; font-size: 10px; color: #666;">
        <p>* Dokumen ini digenerate secara otomatis pada {{ $tanggal_export }}</p>
    </div>
</body>
</html>
