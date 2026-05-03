@extends('layouts.admin')

@section('content')

<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-6 mb-10">
    {{-- JUDUL & TOMBOL SINKRONISASI --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
        <h2 class="text-2xl md:text-3xl font-bold text-slate-800 tracking-tight">
            Selamat Datang Admin
        </h2>

        {{-- Tombol Sinkronisasi Libur --}}
        <form action="{{ route('admin.holidays.sync') }}" method="POST" class="inline-block" onsubmit="return confirmSync(event, this)">
            @csrf
            <button type="submit" class="bg-red-50 text-red-500 px-4 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-red-500 hover:text-white transition-all shadow-sm border border-red-100 flex items-center">
                <i class="bi bi-calendar2-check-fill mr-2 text-sm"></i> Sinkronkan Libur Nasional
            </button>
        </form>
    </div>

    {{-- PENCARIAN --}}
    <form action="{{ route('admin.dashboard') }}" method="GET" class="w-full md:w-auto">
        <div class="relative w-full md:w-96 flex gap-2">
            <div class="relative w-full">
                <i class="bi bi-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input
                    type="text"
                    name="search" 
                    value="{{ request('search') }}"
                    class="w-full pl-12 pr-6 py-4 bg-white border border-slate-100 rounded-2xl
                           outline-none focus:ring-4 focus:ring-blue-50 transition shadow-sm"
                    placeholder="Cari nama atau ID Peserta...">
            </div>
            
            {{-- Tombol Submit --}}
            <button type="submit" class="bg-blue-600 text-white px-6 rounded-2xl hover:bg-blue-700 transition">
                <i class="bi bi-arrow-right text-lg"></i>
            </button>
        </div>
    </form>
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
                    
                    {{-- Akses Nama via Internship -> User --}}
                    <td class="px-6 py-4 font-bold text-slate-700">
                        {{ $row->internship->user->name }}
                    </td>

                    {{-- Akses Login ID --}}
                    <td class="px-8 py-5 font-medium text-blue-500">
                        {{ $row->internship->user->login_id }}
                    </td>

                    {{-- Akses Periode via Internship --}}
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
                        <span class="px-4 py-1.5 rounded-xl text-[10px] font-black uppercase border
                            @if($row->status == 'hadir')
                                bg-[#10B981] text-white border-transparent
                            @elseif($row->status == 'izin')
                                bg-[#FFFED6] text-yellow-600 border-yellow-200/50
                            @else
                                bg-red-50 text-red-600 border-red-200
                            @endif
                        ">
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


<script>
    function confirmSync(event, form) {
        event.preventDefault(); // Mencegah form langsung terkirim
        
        Swal.fire({
            title: 'Sinkronisasi Libur?',
            text: "Sistem akan menarik pembaruan data libur nasional dari API. Proses ini membutuhkan koneksi internet.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3B82F6',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Tarik Data!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-[2rem]'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Munculkan animasi loading karena proses tarik API butuh waktu beberapa detik
                Swal.fire({
                    title: 'Mohon Tunggu...',
                    text: 'Sedang menarik data dari server.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Kirim form setelah user setuju
                form.submit();
            }
        });
    }
</script>

@endsection