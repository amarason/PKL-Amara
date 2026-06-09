<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen SIPRAKER</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-gray-100 font-sans text-gray-800">

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
        
        <div class="bg-green-600 px-6 py-4 flex items-center gap-3">
            <i class="bi bi-check-circle-fill text-2xl text-white"></i>
            <h1 class="text-lg font-semibold text-white">Dokumen Valid</h1>
        </div>

        <div class="p-6">
            <p class="text-sm text-gray-600 mb-6 leading-relaxed">
                Sistem Informasi Praktik Kerja (SIPRAKER) PT PLN IP UBP Semarang menyatakan bahwa dokumen ini asli dan terdaftar di dalam database.
            </p>

            <div class="border-t border-gray-200 pt-5">
                <dl class="space-y-3 text-sm">
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="text-gray-500 font-medium">Nama Peserta</dt>
                        <dd class="col-span-2 font-semibold text-gray-900">: {{ $nama }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="text-gray-500 font-medium">Instansi Asal</dt>
                        <dd class="col-span-2 font-semibold text-gray-900">: {{ $instansi }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="text-gray-500 font-medium">Periode PKL</dt>
                        <dd class="col-span-2 font-semibold text-gray-900">: {{ $periode }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <dt class="text-gray-500 font-medium">Status Laporan</dt>
                        <dd class="col-span-2 font-semibold text-gray-900">: Rekapitulasi {{ $laporan_bulan }} {{ $laporan_tahun }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 text-xs text-gray-500 flex justify-between items-center">
            <span>Waktu Verifikasi:</span>
            <span class="font-medium">{{ $verified_at }}</span>
        </div>

    </div>
</div>

</body>
</html>