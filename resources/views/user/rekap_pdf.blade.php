<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap_Absensi_{{ $internship->user->name }}</title>
    <style>
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 11pt; 
            color: #1a202c; 
            line-height: 1.5; 
            margin: 0;
            padding: 0;
        }
        .container { padding: 30px; }
        
        /* Kop Surat Modern dengan Tabel */
        .header-table { 
            width: 100%; 
            border-bottom: 3px double #000; 
            padding-bottom: 10px; 
            margin-bottom: 25px; 
        }
        .header-logo { width: 80px; text-align: left; vertical-align: middle; }
        .header-text { text-align: center; vertical-align: middle; padding-right: 80px; }
        .header-text h1 { margin: 0; font-size: 16pt; text-transform: uppercase; }
        .header-text h2 { margin: 2px 0; font-size: 13pt; font-weight: normal; }
        .header-text p { margin: 0; font-size: 8pt; color: #4b5563; }
        .header-text .sub-p { font-style: italic; font-size: 7pt; color: #94a3b8; }

        /* Identitas Peserta */
        .info-section { margin-bottom: 20px; width: 100%; }
        .info-table { width: 100%; border: none; }
        .info-table td { padding: 3px 0; vertical-align: top; font-size: 10pt; }
        .label { width: 140px; font-weight: bold; }
        
        /* Ringkasan Kehadiran */
        .summary-text { 
            margin: 15px 0; 
            padding: 12px; 
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 10pt;
            border-radius: 5px;
        }

        /* Tabel Utama */
        table.main-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table.main-table th { 
            background-color: #f1f5f9; 
            border: 1px solid #cbd5e1; 
            padding: 10px 5px; 
            font-size: 9pt; 
            text-transform: uppercase;
            text-align: center;
        }
        table.main-table td { 
            border: 1px solid #cbd5e1; 
            padding: 8px 5px; 
            font-size: 9pt; 
            text-align: center; 
        }
        
        /* Pewarnaan Status */
        .status-hadir { color: #059669; font-weight: bold; }
        .status-izin { color: #d97706; font-style: italic; }
        .status-alpha { color: #dc2626; font-weight: bold; }

        /* Footer & Tanda Tangan */
        .footer-container { margin-top: 50px; width: 100%; }
        
        .signature-wrapper { 
            float: right; 
            width: 250px; 
            text-align: center; 
        }
        
        .qrcode-signature { 
            margin: 10px 0; /* Memberi jarak antara tulisan Peserta PKL dan Nama */
        }

        .info-cetak-kiri {
            float: left;
            width: 300px;
            font-size: 8pt;
            color: #64748b;
            margin-top: 30px;
        }

        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header Layout dengan Logo --}}
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
                    <p>Laporan Rekapitulasi Absensi Peserta Praktek Kerja Lapangan (PKL)</p>
                    <p class="sub-p">Sistem Informasi Praktek Kerja (SIPRAKER) - {{ date('Y') }}</p>
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

        {{-- Box Ringkasan --}}
        <div class="summary-text">
            <strong>RINGKASAN KEHADIRAN:</strong> &nbsp;&nbsp; 
            Hadir: <strong>{{ $stats['hadir'] }}</strong> Hari &nbsp; | &nbsp; 
            Izin: <strong>{{ $stats['izin'] }}</strong> Hari &nbsp; | &nbsp; 
            Alpha: <strong>{{ $stats['alpha'] }}</strong> Hari
        </div>

        {{-- Tabel Data --}}
        <table class="main-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="20%">Tanggal</th>
                    <th width="15%">Masuk</th>
                    <th width="15%">Pulang</th>
                    <th width="45%">Status / Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $index => $att)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($att->attendance_date)->translatedFormat('d M Y') }}</td>
                    <td>{{ $att->check_in_time ? \Carbon\Carbon::parse($att->check_in_time)->format('H:i') : '--:--' }}</td>
                    <td>{{ $att->check_out_time ? \Carbon\Carbon::parse($att->check_out_time)->format('H:i') : '--:--' }}</td>
                    <td class="status-{{ $att->status }}">
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
                * Scan QR Code di samping untuk verifikasi data asli.</p>
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