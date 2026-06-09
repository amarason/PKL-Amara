<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="{{ public_path('css/laporan.css') }}">
</head>
<body>
    <div class="container">
        <table class="header-table">
            <tr>
                <td class="header-logo">
                    @if(isset($logoData))
                        <img src="data:image/png;base64,{{ $logoData }}" width="80">
                    @endif
                </td>
                <td class="header-text">
                    <h1>PT PLN INDONESIA POWER</h1>
                    <h2>UBP SEMARANG</h2>
                    <p>Laporan Rekapitulasi Absensi Peserta Praktik Kerja Lapangan (PKL)</p>
                    <p class="sub-p">Sistem Informasi Praktik Kerja (SIPRAKER)</p>
                </td>
            </tr>
        </table>

        {{-- Identitas Peserta --}}
        <div class="info-section">
            <table class="info-table">
                <tr>
                    <td class="label">Nama Peserta</td>
                    <td>: {{ $internship->user->name }}</td>
                </tr>
                <tr>
                    <td class="label">Nomor ID PKL</td>
                    <td>: {{ $internship->internship_id }}</td>
                </tr>
                <tr>
                    <td class="label">Instansi Asal</td>
                    <td>: {{ $internship->institution->institution_name }}</td>
                </tr>
                <tr>
                    <td class="label">Periode Laporan</td>
                    <td>: {{ $periodeLabel }}</td>
                </tr>
            </table>
        </div>

        <div class="summary-text">
            <strong>RINGKASAN KEHADIRAN:</strong> &nbsp;&nbsp; 
            Hadir: <strong>{{ $stats['hadir'] }}</strong> Hari &nbsp; | &nbsp; 
            Izin: <strong>{{ $stats['izin'] }}</strong> Hari &nbsp; | &nbsp; 
            Alpha: <strong>{{ $stats['alpha'] }}</strong> Hari
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="25%">Tanggal</th>
                    <th width="15%">Masuk</th>
                    <th width="15%">Pulang</th>
                    <th width="40%">Status / Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $index => $att)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($att->attendance_date)->translatedFormat('d M Y') }}</td>
                    {{-- Tampilkan jam atau tanda strip jika Alpha/Izin --}}
                    <td>{{ $att->check_in_time ? \Carbon\Carbon::parse($att->check_in_time)->format('H:i') : '--:--' }}</td>
                    <td>{{ $att->check_out_time ? \Carbon\Carbon::parse($att->check_out_time)->format('H:i') : '--:--' }}</td>
                    <td class="status-{{ $att->status }}" style="{{ $att->status == 'alpha' ? 'color: red; font-weight: bold;' : '' }}">
                        {{ strtoupper($att->status) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Footer dengan QR Code Stempel & Tanda Tangan --}}
        <div class="footer-container">
            <div class="info-cetak-kiri">
                <p><strong>Verifikasi Digital:</strong><br>
                * Laporan ini diterbitkan secara otomatis oleh sistem SIPRAKER.<br>
                * Dokumen ini sah dan diakui di lingkungan PT PLN IP UBP Semarang.<br>
                * Scan QR Code di bawah untuk verifikasi data asli.</p>
            </div>

            <div class="signature-wrapper">
                <p>Semarang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p style="margin-bottom: 5px;">Peserta PKL,</p>
        
                <div class="qrcode-signature">
                    @if(isset($qrcode))
                        <img src="data:image/svg+xml;base64,{{ $qrcode }}" width="85">
                    @endif
                </div>
                
                <p><strong><u>{{ $internship->user->name }}</u></strong></p>
                <p style="font-size: 9pt;">ID: {{ $internship->internship_id }}</p>
            </div>
            
            <div class="clear"></div>
        </div>
    </div>
</body>
</html>