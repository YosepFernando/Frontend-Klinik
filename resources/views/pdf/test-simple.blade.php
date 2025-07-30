<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Test PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12pt;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .content {
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Test PDF Generation</h1>
        <p>Tanggal: {{ date('d M Y H:i:s') }}</p>
    </div>

    <div class="content">
        <h2>Informasi Test</h2>
        <table>
            <tr>
                <th>Field</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Test ID</td>
                <td>{{ $test_id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Generated At</td>
                <td>{{ date('Y-m-d H:i:s') }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>Success</td>
            </tr>
        </table>

        <p>Jika Anda dapat melihat PDF ini, maka DomPDF berfungsi dengan baik.</p>
    </div>
</body>
</html>
