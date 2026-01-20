<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="{{ public_path('css/laporan.css') }}">
</head>
<body>
    <div class="container">
        <table class="header-table">
            <tr>
                <td class="header-logo">
                    @if($logoData)
                        <img src="data:image/png;base64,{{ $logoData }}" width="75">
                    @endif
                </td>
                <td class="header-text">
                    <h1>PT PLN INDONESIA POWER</h1>
                    <h2>UBP SEMARANG</h2>
                    <p>Laporan Rekapitulasi Absensi Peserta Praktek Kerja Lapangan (PKL)</p>
                    <p class="sub-p">Sistem Informasi Praktek Kerja (SIPRAKER)</p>
                </td>
            </tr>
        </table>

        {{-- Info Laporan --}}
        <div class="info-section">
            <table class="info-table">
                <tr><td class="label">Periode</td><td>: {{ $namaBulan }} {{ $tahun }}</td></tr>
                <tr><td class="label">Instansi</td><td>: {{ $namaInstansi }}</td></tr>
            </table>
        </div>

        {{-- Kotak Ringkasan Keaktifan Kolektif --}}
        <div class="summary-box">
            <strong>RINGKASAN KEAKTIFAN (TOTAL):</strong> &nbsp;&nbsp; 
            Hadir: <strong>{{ $globalStats['hadir'] }}</strong> &nbsp; | &nbsp; 
            Izin: <strong>{{ $globalStats['izin'] }}</strong> &nbsp; | &nbsp; 
            Alpha: <strong>{{ $globalStats['alpha'] }}</strong>
        </div>

        {{-- Tabel Utama Admin --}}
        <table class="main-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="30%">Nama Peserta</th>
                    <th width="15%">ID PKL</th>
                    <th width="10%">Hadir</th>
                    <th width="10%">Izin</th>
                    <th width="10%">Alpha</th>
                    <th width="20%">Efektivitas (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($peserta as $index => $p)
                    @php
                        $h = $p->attendance->where('status', 'hadir')->count();
                        $i = $p->attendance->where('status', 'izin')->count();

                        $totalSeharusnya = $p->getTotalSeharusnyaHadir($bulan == 'all' ? null : $bulan, $tahun);
                        
                        $a = max(0, $totalSeharusnya - ($h + $i));

                        $persen = $totalSeharusnya > 0 ? min(100, round(($h / $totalSeharusnya) * 100)) : 0;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td style="text-align: left; padding-left: 8px;">{{ $p->user->name }}</td>
                        <td>{{ $p->user->login_id }}</td>
                        <td class="text-hadir">{{ $h }}</td>
                        <td class="text-izin">{{ $i }}</td>
                        <td class="text-alpha">{{ $a }}</td>
                        <td style="font-weight: bold;">{{ $persen }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Footer dengan QR Code --}}
        <div class="footer-container">
            <div class="info-footer">
                <p><strong>Verifikasi Digital:</strong><br>
                * Dokumen ini diterbitkan oleh Sistem SIPRAKER PLN IP UBP Semarang.<br>
                * Scan QR Code di samping untuk verifikasi data asli.</p>
            </div>

            <div class="signature-box">
                <p>Semarang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p>Admin Pembimbing PKL,</p>
                <div class="qrcode-sig">
                    <img src="data:image/svg+xml;base64,{{ $qrcode }}" width="80">
                </div>
                <p><strong><u>{{ auth()->user()->name }}</u></strong></p>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</body>
</html>