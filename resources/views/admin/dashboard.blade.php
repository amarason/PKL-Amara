@extends('layouts.admin')

@section('content')

<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-6 mb-10">
    <h2 class="text-2xl md:text-3xl font-bold text-slate-800 tracking-tight">
        Selamat Datang Admin
    </h2>

    <div class="relative w-full md:w-96">
        <i class="bi bi-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input
            type="text"
            class="w-full pl-12 pr-6 py-4 bg-white border border-slate-100 rounded-2xl
                   outline-none focus:ring-4 focus:ring-blue-50 transition shadow-sm"
            placeholder="Cari nama peserta...">
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">

    <div class="bg-[#D1F3FF] p-6 md:p-8 rounded-[2.5rem] relative border border-blue-200/50">
        <i class="bi bi-people-fill text-4xl text-blue-600/40 absolute right-6 top-6"></i>
        <p class="text-4xl md:text-5xl font-extrabold text-slate-800">{{ $totalPeserta }}</p>
        <p class="text-slate-600 text-sm font-bold mt-2">Total Peserta PKL</p>
    </div>

    <div class="bg-[#E0F7FF] p-6 md:p-8 rounded-[2.5rem] relative border border-cyan-200/50">
        <i class="bi bi-person-fill text-4xl text-cyan-600/40 absolute right-6 top-6"></i>
        <p class="text-4xl md:text-5xl font-extrabold text-slate-800">{{ $pesertaAktif }}</p>
        <p class="text-slate-600 text-sm font-bold mt-2">Peserta Aktif</p>
    </div>

    <div class="bg-[#DFFFD6] p-6 md:p-8 rounded-[2.5rem] relative border border-green-200/50">
        <i class="bi bi-person-check-fill text-4xl text-green-600/40 absolute right-6 top-6"></i>
        <p class="text-4xl md:text-5xl font-extrabold text-slate-800">{{ $hadirHariIni }}</p>
        <p class="text-slate-600 text-sm font-bold mt-2">Hadir Hari Ini</p>
    </div>

    <div class="bg-[#FFFED6] p-6 md:p-8 rounded-[2.5rem] relative border border-yellow-200/50">
        <i class="bi bi-person-exclamation text-4xl text-yellow-600/40 absolute right-6 top-6"></i>
        <p class="text-4xl md:text-5xl font-extrabold text-slate-800">{{ $izinHariIni }}</p>
        <p class="text-slate-600 text-sm font-bold mt-2">Izin Hari Ini</p>
    </div>

</div>

<div class="bg-white rounded-[3rem] border border-slate-100 overflow-hidden shadow-sm shadow-blue-900/5">

    <div class="py-6 md:py-8 text-center border-b border-slate-50">
        <p class="text-[10px] font-bold text-slate-300 uppercase tracking-[0.3em] mb-1">
            Monitoring
        </p>
        <h3 class="text-lg md:text-xl font-extrabold text-slate-800">
            Daftar Hadir Terkini
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-[900px] w-full text-left border-collapse">
            <thead class="bg-[#AEE2FF] text-slate-700 font-bold text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4">No</th>
                    <th class="px-6 py-4">Nama</th>
                    <th class="px-8 py-5">No ID PKL</th> {{-- Tambahan agar lebih jelas --}}
                    <th class="px-6 py-4">Periode</th>
                    <th class="px-6 py-4">Tanggal</th>
                    <th class="px-6 py-4">Masuk</th>
                    <th class="px-6 py-4">Pulang</th>
                    <th class="px-6 py-4">Status</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-50">
                @forelse($kehadiran as $index => $row)
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-6 py-4 text-slate-400">{{ $index + 1 }}</td>
                    
                    {{-- PERBAIKAN: Akses Nama via Internship -> User --}}
                    <td class="px-6 py-4 font-bold text-slate-700">
                        {{ $row->internship->user->name }}
                    </td>

                    {{-- PERBAIKAN: Akses Login ID --}}
                    <td class="px-8 py-5 font-medium text-blue-500">
                        {{ $row->internship->user->login_id }}
                    </td>

                    {{-- PERBAIKAN: Akses Periode via Internship --}}
                    <td class="px-6 py-4 text-slate-400 text-sm">
                        {{ \Carbon\Carbon::parse($row->internship->start_date)->format('M') }} -
                        {{ \Carbon\Carbon::parse($row->internship->end_date)->format('M y') }}
                    </td>

                    <td class="px-6 py-4 text-slate-500 text-sm">
                        {{ \Carbon\Carbon::parse($row->attendance_date)->format('d M Y') }}
                    </td>
                    
                    <td class="px-6 py-4 font-bold">{{ substr($row->check_in_time, 0, 5) }}</td>
                    
                    <td class="px-6 py-4 font-bold">
                        {{ $row->check_out_time ? substr($row->check_out_time, 0, 5) : '-' }}
                    </td>
                    
                    <td class="px-6 py-4">
                        <span class="{{ $row->status == 'hadir' ? 'bg-[#10B981]' : ($row->status == 'izin' ? 'bg-blue-500' : 'bg-[#FBBF24]') }}
                                     text-white px-4 py-1.5 rounded-xl text-[10px] font-black uppercase">
                            {{ $row->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-16 text-slate-400 italic">
                        Belum ada aktivitas kehadiran hari ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-6 md:p-8 flex justify-end bg-slate-50/20">
        <a href="{{ route('admin.absensi.index') }}" class="bg-[#E6F7FF] hover:bg-blue-100 text-blue-600 font-black
                               px-10 py-3 rounded-2xl text-[10px] uppercase tracking-widest transition">
            Lihat Semua Absensi
        </a>
    </div>

</div>

@endsection