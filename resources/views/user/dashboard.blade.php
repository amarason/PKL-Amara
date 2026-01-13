@extends('layouts.peserta')

@section('content')
<div class="space-y-8">
    <div>
        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Selamat Datang, {{ Auth::user()->name }}!</h2>
        <p class="text-slate-400 font-bold text-sm mt-1">Pantau kehadiran PKL kamu di sini.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Total Hadir --}}
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-4">
                <i class="bi bi-check2-circle"></i>
            </div>
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Total Hadir</p>
            <h3 class="text-3xl font-black text-slate-800">{{ $totalHadir ?? 0 }} Hari</h3>
        </div>
        
        {{-- Total Izin --}}
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-2xl mb-4">
                <i class="bi bi-calendar-event"></i>
            </div>
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Total Izin</p>
            <h3 class="text-3xl font-black text-slate-800">{{ $totalIzin ?? 0 }} Hari</h3>
        </div>

        {{-- Total Alpha --}}
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center text-2xl mb-4">
                <i class="bi bi-x-circle"></i>
            </div>
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Tanpa Keterangan</p>
            <h3 class="text-3xl font-black text-slate-800">{{ $totalAlpha ?? 0 }} Hari</h3>
        </div>
    </div>

    {{-- Box Status Hari Ini --}}
    <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm relative overflow-hidden">
        <div class="relative z-10">
            <h3 class="text-xl font-black text-slate-800 mb-2">Status Presensi Hari Ini</h3>
            <p class="text-slate-400 text-sm font-medium mb-6">{{ date('l, d F Y') }}</p>
            
            @if(!$absensiHariIni)
                {{-- KONDISI 1: BELUM ABSEN --}}
                <span class="px-4 py-2 bg-amber-100 text-amber-600 rounded-xl text-xs font-black uppercase tracking-widest">Belum Presensi</span>
                <div class="mt-6">
                    <a href="{{ route('user.absensi.index') }}" class="inline-block bg-blue-600 text-white px-8 py-4 rounded-2xl font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 transition">Mulai Presensi Masuk</a>
                </div>

            @elseif($absensiHariIni->status == 'alpha')
                {{-- KONDISI BARU: TERDETEKSI ALPHA OLEH SISTEM --}}
                <div class="flex items-center space-x-3 mb-4">
                    <span class="px-4 py-2 bg-red-100 text-red-600 rounded-xl text-xs font-black uppercase tracking-widest">Alpha / Tanpa Keterangan</span>
                </div>
                <p class="text-red-500 font-bold text-sm italic">Sistem mencatat Anda tidak melakukan presensi hingga batas waktu yang ditentukan.</p>

            @elseif($absensiHariIni && !$absensiHariIni->check_out_time)
                {{-- KONDISI 2: SUDAH MASUK, BELUM PULANG --}}
                <span class="px-4 py-2 bg-blue-100 text-blue-600 rounded-xl text-xs font-black uppercase tracking-widest italic">Sudah Presensi Masuk ({{ \Carbon\Carbon::parse($absensiHariIni->check_in_time)->format('H:i') }})</span>
                <p class="text-slate-400 text-xs mt-3 font-medium">Jangan lupa untuk melakukan presensi pulang sebelum meninggalkan lokasi.</p>
                <div class="mt-6">
                    <a href="{{ route('user.absensi.index') }}" class="inline-block bg-slate-800 text-white px-8 py-4 rounded-2xl font-bold shadow-lg hover:bg-slate-700 transition">Presensi Pulang</a>
                </div>

            @else
                {{-- KONDISI 3: SELESAI --}}
                <div class="flex items-center space-x-3 mb-4">
                    <span class="px-4 py-2 bg-green-100 text-green-600 rounded-xl text-xs font-black uppercase tracking-widest">Presensi Selesai</span>
                </div>
                <div class="space-y-1">
                    <p class="text-slate-500 text-sm font-bold">Masuk: <span class="text-slate-400 font-medium">{{ \Carbon\Carbon::parse($absensiHariIni->check_in_time)->format('H:i') }}</span></p>
                    <p class="text-slate-500 text-sm font-bold">Pulang: <span class="text-slate-400 font-medium">{{ \Carbon\Carbon::parse($absensiHariIni->check_out_time)->format('H:i') }}</span></p>
                </div>
                <p class="mt-6 text-green-500 font-bold text-sm italic"><i class="bi bi-check-all mr-1"></i> Tugas hari ini selesai, selamat beristirahat!</p>
            @endif
        </div>

        <i class="bi bi-clock-history absolute -bottom-10 -right-10 text-[15rem] text-slate-50 opacity-50"></i>
    </div>
</div>
@endsection