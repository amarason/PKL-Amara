@extends('layouts.peserta')

@section('content')
<div class="space-y-10">
    {{-- Bagian Atas: Header, Filter & Tombol --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Rekap Absensi</h2>
            <p class="text-slate-400 font-bold text-sm mt-1">Pantau konsistensi dan riwayat kehadiran Anda.</p>
        </div>

        <div class="flex items-center gap-3">
            {{-- Filter Bulan --}}
            <form action="{{ route('user.rekap.index') }}" method="GET" class="flex gap-2">
                <select name="month" class="px-4 py-2 bg-white border border-slate-100 rounded-xl font-bold text-slate-600 text-sm outline-none focus:ring-4 focus:ring-blue-50 transition">
                    {{-- Opsi Default --}}
                    <option value="">Semua Bulan (Periode PKL)</option>
                    
                    @for($m=1; $m<=12; $m++)
                        @php $mVal = sprintf('%02d', $m); @endphp
                        <option value="{{ $mVal }}" {{ $month == $mVal ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
                <button type="submit" class="bg-slate-800 text-white px-5 py-2 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-slate-700 transition shadow-lg shadow-slate-200">
                    Cek
                </button>
            </form>
            
            {{-- Tombol Export PDF --}}
            <a href="{{ route('user.rekap.pdf', ['month' => $month, 'year' => $year ?? date('Y')]) }}" 
               class="bg-red-500 text-white px-5 py-2 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-red-600 transition flex items-center shadow-lg shadow-red-100">
                <i class="bi bi-file-pdf mr-2"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- Bagian Widget: Statistik Kehadiran --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Card Hadir --}}
        <div class="bg-blue-600 p-8 rounded-[2.5rem] text-white shadow-xl shadow-blue-100 relative overflow-hidden group">
            <div class="relative z-10">
                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] opacity-80">Total Hadir</h4>
                <p class="text-5xl font-black mt-2">{{ $stats['hadir'] }}</p>
            </div>
            <i class="bi bi-person-check absolute bottom-[-10px] right-[-10px] text-7xl opacity-20 transform -rotate-12 transition-transform duration-500 group-hover:rotate-0"></i>
        </div>

        {{-- Card Izin --}}
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="relative z-10">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Total Izin</h4>
                <p class="text-5xl font-black text-slate-800 mt-2">{{ $stats['izin'] }}</p>
            </div>
            <i class="bi bi-envelope-paper absolute bottom-[-10px] right-[-10px] text-7xl text-slate-50 transform -rotate-12 transition-transform duration-500 group-hover:rotate-0 group-hover:text-blue-50"></i>
        </div>

        {{-- Card Alpha --}}
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="relative z-10">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Tanpa Keterangan</h4>
                <p class="text-5xl font-black text-slate-800 mt-2">{{ $stats['alpha'] }}</p>
            </div>
            <i class="bi bi-patch-exclamation absolute bottom-[-10px] right-[-10px] text-7xl text-slate-50 transform -rotate-12 transition-transform duration-500 group-hover:rotate-0 group-hover:text-red-50"></i>
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
@endsection