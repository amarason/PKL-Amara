@extends('layouts.admin')

@section('content')
{{-- Library Tom Select --}}
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<style>
    .ts-control {
        border-radius: 1rem !important; 
        border: none !important; 
        background-color: #f8fafc !important; 
        padding: 0.75rem 1rem !important; 
        font-family: inherit !important;
        font-size: 0.75rem !important; 
        font-weight: 700 !important; 
        color: #334155 !important;
        box-shadow: none !important;
        transition: all 0.2s;
    }
    .ts-control.focus {
        background-color: #ffffff !important;
        box-shadow: 0 0 0 2px #3b82f6 !important; 
    }
    .ts-dropdown {
        border-radius: 1rem !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        margin-top: 0.25rem !important;
        font-family: inherit !important;
    }
    .ts-dropdown .option {
        padding: 0.75rem 1rem !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        color: #475569 !important;
        border-bottom: 1px solid #f8fafc;
    }
    .ts-dropdown .option.active, .ts-dropdown .option:hover {
        background-color: #eff6ff !important; 
        color: #1d4ed8 !important; 
    }
    .ts-wrapper.single .ts-control:after {
        border-color: #94a3b8 transparent transparent transparent !important;
        border-width: 5px 4px 0 4px !important;
    }
</style>

<div class="space-y-8 pb-20">
    <div>
        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Rekap Absensi</h2>
        <p class="text-slate-400 font-bold text-sm mt-1">Laporan statistik kehadiran seluruh peserta PKL</p>
    </div>

    {{-- Filter Panel --}}
    <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
        <form action="{{ route('admin.rekap.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            {{-- Cari Peserta --}}
            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Cari Peserta</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / ID..." 
                    class="w-full bg-slate-50 border-none rounded-2xl text-xs font-bold focus:ring-2 focus:ring-blue-500 py-3 px-4 outline-none">
            </div>

            {{-- Instansi --}}
            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Instansi</label>
                <select name="institution_id" id="filter_instansi" class="w-full" placeholder="Semua Instansi">
                    <option value="">Semua Instansi</option>
                    @foreach($institutions as $inst)
                        <option value="{{ $inst->institution_id }}" {{ request('institution_id') == $inst->institution_id ? 'selected' : '' }}>
                            {{ $inst->institution_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status Peserta --}}
            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Status Peserta</label>
                <select name="status" id="filter_status" class="w-full" placeholder="Semua Status">
                    <option value="all" {{ ($status ?? 'all') == 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="aktif" {{ ($status ?? 'all') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="selesai" {{ ($status ?? 'all') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>

            {{-- Periode --}}
            <div class="space-y-1">
                <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Periode</label>
                <div class="flex gap-2">
                    <div class="w-1/2">
                        <select name="bulan" id="filter_bulan" class="w-full" placeholder="Bulan">
                            <option value="all" {{ $bulan == 'all' ? 'selected' : '' }}>Semua Bulan</option>
                            @for($m=1; $m<=12; $m++)
                                @php $val = sprintf('%02d', $m); @endphp
                                <option value="{{ $val }}" {{ $bulan == $val ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="w-1/2">
                        <select name="tahun" id="filter_tahun" class="w-full" placeholder="Tahun">
                            @for($y = date('Y'); $y >= 2026; $y--)
                                <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
                    Filter
                </button>

                @if(request('search') || request('institution_id') || request('status') || request('bulan') != date('m') || request('tahun') != date('Y'))
                    <a href="{{ route('admin.rekap.index') }}" 
                    class="bg-slate-200 text-slate-600 px-4 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-300 transition-all shadow-sm flex items-center justify-center"
                    title="Bersihkan Filter">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
                
                <a href="{{ route('admin.rekap.pdf', request()->all()) }}" 
                target="_blank"
                class="flex-1 bg-red-500 text-white py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-red-600 transition-all shadow-lg shadow-red-100 text-center flex items-center justify-center">
                    <i class="bi bi-file-pdf mr-2"></i> PDF
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel Rekap --}}
    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#AEE2FF] text-slate-700 uppercase text-[10px] font-black tracking-widest">
                        <th class="px-6 py-5 rounded-l-2xl">Peserta</th>
                        <th class="px-6 py-5 text-center">Status</th>
                        <th class="px-6 py-5">Instansi</th>
                        <th class="px-6 py-5 text-center">Hadir</th>
                        <th class="px-6 py-5 text-center">Izin</th>
                        <th class="px-6 py-5 text-center text-red-500">Alpha</th>
                        <th class="px-6 py-5 text-center rounded-r-2xl">Efektivitas (%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($peserta as $p)
                        @php
                            $allAtt = $p->attendance;
                            $h_lengkap = $allAtt->where('status', 'hadir')->where('check_in_time', '!=', '00:00:00')->count();
                            $h_lupa = $allAtt->where('status', 'hadir')->where('check_in_time', '==', '00:00:00')->count();
                            $izin = $allAtt->where('status', 'izin')->count();

                            $totalSeharusnya = $p->getTotalSeharusnyaHadir($bulan == 'all' ? null : $bulan, $tahun);
                            
                            $poinKehadiran = $h_lengkap + ($h_lupa * 0.5);
                            
                            $alpha = max(0, $totalSeharusnya - ($h_lengkap + $h_lupa + $izin));
                            $persentase = $totalSeharusnya > 0 ? min(100, round(($poinKehadiran / $totalSeharusnya) * 100)) : 0;
                        @endphp

                        <tr class="hover:bg-slate-50/50 transition-all">
                            <td class="px-6 py-5">
                                <p class="font-bold text-slate-800 text-sm leading-none">{{ $p->user->name }}</p>
                                <p class="text-[10px] text-blue-500 font-black tracking-tighter mt-1">{{ $p->user->login_id }}</p>
                            </td>

                            <td class="px-6 py-5 text-center">
                                @if($p->status == 'aktif')
                                    <span class="bg-green-100 text-green-600 px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider">Aktif</span>
                                @else
                                    <span class="bg-slate-100 text-slate-500 px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider">Selesai</span>
                                @endif
                            </td>
                            
                            <td class="px-6 py-5">
                                <span class="bg-slate-100 text-slate-500 px-3 py-1 rounded-lg text-[9px] font-black uppercase italic">
                                    {{ $p->institution->institution_name }}
                                </span>
                            </td>

                            <td class="px-6 py-5 text-center font-black text-green-500 text-sm">
                                {{ $h_lengkap + $h_lupa }}
                                @if($h_lupa > 0)
                                    <span class="block text-[8px] text-amber-500 font-bold">({{ $h_lupa }} Lupa)</span>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-center font-black text-blue-500 text-sm">{{ $izin }}</td>
                            <td class="px-6 py-5 text-center font-black text-red-500 text-sm">{{ $alpha }}</td>
                            <td class="px-6 py-5 text-center">
                                <div class="flex flex-col items-center">
                                    <span class="text-[10px] font-black {{ $persentase >= 80 ? 'text-green-500' : 'text-amber-500' }}">
                                        {{ $persentase }}%
                                    </span>
                                    <div class="w-20 bg-slate-100 h-1.5 rounded-full mt-1 overflow-hidden">
                                        <div class="{{ $persentase >= 80 ? 'bg-green-500' : 'bg-amber-500' }} h-full" 
                                             style="width: {{ $persentase }}%">
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-20 text-center text-slate-300 italic font-bold">
                                @if($bulan != 'all')
                                    Tidak ada peserta yang aktif pada bulan {{ \Carbon\Carbon::create()->month((int)$bulan)->translatedFormat('F') }} {{ $tahun }}.
                                @else
                                    Data tidak ditemukan.
                                @endif
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
        // Inisialisasi Tom Select untuk masing-masing dropdown filter
        new TomSelect('#filter_instansi', {
            create: false,
            searchField: ['text'],
            placeholder: 'Semua Instansi'
        });

        new TomSelect('#filter_status', {
            create: false,
            searchField: ['text'],
            placeholder: 'Semua Status'
        });

        new TomSelect('#filter_bulan', {
            create: false,
            searchField: ['text'],
            placeholder: 'Bulan'
        });

        new TomSelect('#filter_tahun', {
            create: false,
            searchField: ['text'],
            placeholder: 'Tahun'
        });
    });
</script>
@endsection