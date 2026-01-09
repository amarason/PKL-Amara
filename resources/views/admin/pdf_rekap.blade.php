<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi - {{ $namaBulan }} {{ $tahun }}</title>
    <link rel="stylesheet" href="{{ public_path('css/pdf-style.css') }}">
</head>
<body>
    <div class="header">
        <div class="title">Laporan Rekapitulasi Absensi Peserta PKL</div>
        <div class="subtitle">Periode: {{ $bulan == 'all' ? 'Semua Bulan' : $namaBulan }} {{ $tahun }}</div>
        <div class="subtitle">Instansi: {{ $namaInstansi }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Nama Peserta</th>
                <th width="15%">ID PKL</th>
                <th width="10%">Hadir</th>
                <th width="10%">Izin</th>
                <th width="10%">Alpha</th>
                <th width="15%">Efektivitas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($peserta as $index => $p)
                @php
                    $hadir = $p->attendance->where('status', 'hadir')->count();
                    $izin = $p->attendance->where('status', 'izin')->count();
                    $alpha = $p->attendance->where('status', 'alpha')->count();
                    $total = $hadir + $izin + $alpha;
                    $persen = $total > 0 ? round(($hadir / $total) * 100) : 0;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $p->user->name }}</td>
                    <td class="text-center">{{ $p->user->login_id }}</td>
                    <td class="text-center">{{ $hadir }}</td>
                    <td class="text-center">{{ $izin }}</td>
                    <td class="text-center text-bold" style="color: #e11d48">{{ $alpha }}</td>
                    <td class="text-center text-bold">{{ $persen }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="ttd-container">
            <p>Semarang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p>Admin Pembimbing,</p>
            <div class="space-ttd"></div>
            <p class="text-bold"><u>( {{ auth()->user()->name }} )</u></p>
            <p>ID: {{ auth()->user()->login_id }}</p>
        </div>
        <div style="clear: both;"></div>
        
        <div class="info-cetak">
           @php
                \Carbon\Carbon::setLocale('id');
            @endphp
            * Dokumen ini diterbitkan oleh Sistem Absensi PKL pada {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i:s') }}.<br>
            * Data yang ditampilkan sesuai dengan catatan kehadiran pada periode yang dipilih.
        </div>
    </div>
</body>
</html>