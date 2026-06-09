@extends('layouts.peserta')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<style>
    .ts-control {
        border-radius: 0.75rem !important; 
        border: 1px solid #f1f5f9 !important; 
        background-color: #ffffff !important; 
        padding: 0.75rem 1rem !important; 
        font-family: inherit !important;
        font-size: 0.875rem !important; 
        font-weight: 700 !important; 
        color: #475569 !important; 
        box-shadow: none !important;
        transition: all 0.2s;
    }
    .ts-control.focus {
        background-color: #ffffff !important;
        border-color: #eff6ff !important;
        box-shadow: 0 0 0 4px #eff6ff !important; 
    }
    .ts-dropdown {
        background-color: #ffffff !important; 
        z-index: 50 !important; 
        border-radius: 0.75rem !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        margin-top: 0.25rem !important;
        font-family: inherit !important;
    }
    .ts-dropdown .option {
        background-color: #ffffff !important; 
        padding: 0.75rem 1rem !important;
        font-size: 0.875rem !important;
        font-weight: 600 !important;
        color: #475569 !important;
        border-bottom: 1px solid #f8fafc;
    }
    .ts-dropdown .option.active, .ts-dropdown .option:hover {
        background-color: #eff6ff !important; 
        color: #1d4ed8 !important; 
    }
    /* Mengubah warna panah bawaan agar lebih halus */
    .ts-wrapper.single .ts-control:after {
        border-color: #94a3b8 transparent transparent transparent !important;
        border-width: 5px 4px 0 4px !important;
    }
</style>

<div class="space-y-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Rekap Absensi</h2>
            <p class="text-slate-400 font-bold text-sm mt-1">Pantau konsistensi dan riwayat kehadiran Anda.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Filter Bulan --}}
            <form action="{{ route('user.rekap.index') }}" method="GET" class="flex gap-2 w-full md:w-auto">
                <div class="min-w-[220px]">
                    <select name="month" id="filter_bulan" placeholder="Semua Bulan (Periode PKL)">
                        <option value="">Semua Bulan (Periode PKL)</option>
                        
                        @for($m=1; $m<=12; $m++)
                            @php $mVal = sprintf('%02d', $m); @endphp
                            <option value="{{ $mVal }}" {{ $month == $mVal ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-100 h-full">
                    Cek
                </button>
            </form>
            
            <a href="{{ route('user.rekap.pdf', ['month' => $month, 'year' => $year ?? date('Y')]) }}" 
               class="bg-red-500 text-white px-5 py-3 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-red-600 transition flex items-center shadow-lg shadow-red-100">
                <i class="bi bi-file-pdf mr-2"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- Widget: Statistik Kehadiran --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Card Hadir --}}
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="relative z-10">
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl mb-4 transition-transform group-hover:scale-110 duration-300">
                    <i class="bi bi-person-check-fill"></i>
                </div>
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Hadir</h4>
                <p class="text-4xl font-black text-slate-800 mt-1">{{ $stats['hadir'] }} <span class="text-sm text-slate-400 font-bold">Hari</span></p>
            </div>
            <i class="bi bi-person-check absolute bottom-[-15px] right-[-15px] text-8xl text-slate-50/50 transform -rotate-12 transition-transform duration-500 group-hover:rotate-0"></i>
        </div>

        {{-- Card Izin --}}
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="relative z-10">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-xl mb-4 transition-transform group-hover:scale-110 duration-300">
                    <i class="bi bi-envelope-paper-fill"></i>
                </div>
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Izin</h4>
                <p class="text-4xl font-black text-slate-800 mt-1">{{ $stats['izin'] }} <span class="text-sm text-slate-400 font-bold">Hari</span></p>
            </div>
            <i class="bi bi-envelope-paper absolute bottom-[-15px] right-[-15px] text-8xl text-slate-50/50 transform -rotate-12 transition-transform duration-500 group-hover:rotate-0"></i>
        </div>

        {{-- Card Alpha --}}
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="relative z-10">
                <div class="w-12 h-12 bg-red-50 text-red-600 rounded-2xl flex items-center justify-center text-xl mb-4 transition-transform group-hover:scale-110 duration-300">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tanpa Keterangan</h4>
                <p class="text-4xl font-black text-slate-800 mt-1">{{ $stats['alpha'] }} <span class="text-sm text-slate-400 font-bold">Hari</span></p>
            </div>
            <i class="bi bi-patch-exclamation absolute bottom-[-15px] right-[-15px] text-8xl text-slate-50/50 transform -rotate-12 transition-transform duration-500 group-hover:rotate-0"></i>
        </div>
    </div>

    {{-- Tabel Riwayat Kehadiran --}}
    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4 px-2">
            <h3 class="text-lg font-black text-slate-800 tracking-tight">Detail Log Kehadiran</h3>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-50 px-4 py-1.5 rounded-full border border-slate-100 self-start sm:self-auto">
                Periode: {{ $month ? date('F', mktime(0, 0, 0, $month, 1)) : 'Seluruh PKL' }} {{ $year ?? date('Y') }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-slate-400 uppercase text-[10px] font-black tracking-widest border-b border-slate-50">
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Jam Masuk</th>
                        <th class="px-6 py-4">Jam Pulang</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Verifikasi Foto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($attendances as $att)
                    <tr class="hover:bg-slate-50/50 transition duration-200">
                        <td class="px-6 py-5">
                            <span class="font-bold text-slate-700 block text-sm">{{ \Carbon\Carbon::parse($att->attendance_date)->translatedFormat('d M Y') }}</span>
                            <span class="text-[10px] text-slate-400 font-medium uppercase tracking-tighter">{{ \Carbon\Carbon::parse($att->attendance_date)->translatedFormat('l') }}</span>
                        </td>
                        <td class="px-6 py-5">
                            @if($att->check_in_time)
                                <div class="flex items-center text-slate-600 font-bold text-sm">
                                    <i class="bi bi-box-arrow-in-right mr-2 text-blue-500"></i>
                                    {{ \Carbon\Carbon::parse($att->check_in_time)->format('H:i') }}
                                </div>
                            @else
                                <span class="text-slate-300 font-medium italic text-sm">--:--</span>
                            @endif
                        </td>
                        <td class="px-6 py-5">
                            @if($att->check_out_time)
                                <div class="flex items-center text-slate-600 font-bold text-sm">
                                    <i class="bi bi-box-arrow-left mr-2 text-orange-500"></i>
                                    {{ \Carbon\Carbon::parse($att->check_out_time)->format('H:i') }}
                                </div>
                            @else
                                <span class="text-slate-300 font-medium italic text-sm">--:--</span>
                            @endif
                        </td>
                        <td class="px-6 py-5">
                            @if($att->status == 'hadir')
                                <span class="text-[10px] font-black uppercase text-green-600 bg-green-100/50 px-3 py-1 rounded-lg border border-green-100">Hadir</span>
                            @elseif($att->status == 'izin')
                                <span class="text-[10px] font-black uppercase text-amber-600 bg-amber-100/50 px-3 py-1 rounded-lg border border-amber-100">Izin</span>
                            @else
                                <span class="text-[10px] font-black uppercase text-red-600 bg-red-100/50 px-3 py-1 rounded-lg border border-red-100">Alpha</span>
                            @endif
                        </td>
                        <td class="px-6 py-5 text-center">
                            @if($att->check_in_photo)
                                <div class="flex justify-center -space-x-3 group/photos">
                                    <img src="{{ asset($att->check_in_photo) }}" class="w-9 h-9 rounded-full border-2 border-white object-cover shadow-sm transition-transform group-hover/photos:scale-110" title="Foto Masuk">
                                    @if($att->check_out_photo)
                                        <img src="{{ asset($att->check_out_photo) }}" class="w-9 h-9 rounded-full border-2 border-white object-cover shadow-sm transition-transform group-hover/photos:scale-110" title="Foto Pulang">
                                    @endif
                                </div>
                            @else
                                <i class="bi bi-camera-video-off text-slate-200 text-xl" title="Tanpa Foto"></i>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <i class="bi bi-database-exclamation text-slate-100 text-6xl mb-4"></i>
                                <p class="text-slate-400 italic font-bold">Data tidak ditemukan pada periode ini.</p>
                                <p class="text-slate-300 text-xs mt-1">Silahkan pilih bulan atau tahun yang berbeda.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect('#filter_bulan', {
            create: false,
            searchField: ['text'],
            placeholder: 'Semua Bulan (Periode PKL)'
        });
    });
</script>
@endsection