@extends('layouts.peserta')

@section('content')
<div class="space-y-8">
    <div>
        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Selamat Datang, {{ Auth::user()->name }}!</h2>
        <p class="text-slate-400 font-bold text-sm mt-1">Pantau kehadiran PKL kamu di sini.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mb-4">
                <i class="bi bi-check2-circle"></i>
            </div>
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest">Total Hadir</p>
            <h3 class="text-3xl font-black text-slate-800">{{ $totalHadir }} Hari</h3>
        </div>
        </div>

    <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm relative overflow-hidden">
        <div class="relative z-10">
            <h3 class="text-xl font-black text-slate-800 mb-2">Status Presensi Hari Ini</h3>
            <p class="text-slate-400 text-sm font-medium mb-6">{{ date('l, d F Y') }}</p>
            
            @if(!$absensiHariIni)
                <span class="px-4 py-2 bg-amber-100 text-amber-600 rounded-xl text-xs font-black uppercase tracking-widest">Belum Presensi</span>
                <div class="mt-6">
                    <a href="{{ route('user.absensi.index') }}" class="inline-block bg-blue-600 text-white px-8 py-4 rounded-2xl font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 transition">Mulai Presensi Masuk</a>
                </div>
            @else
                <span class="px-4 py-2 bg-green-100 text-green-600 rounded-xl text-xs font-black uppercase tracking-widest italic">Sudah Presensi Masuk ({{ $absensiHariIni->check_in_time }})</span>
                @if(!$absensiHariIni->check_out_time)
                     <div class="mt-6">
                        <a href="{{ route('user.absensi.index') }}" class="inline-block bg-slate-800 text-white px-8 py-4 rounded-2xl font-bold shadow-lg hover:bg-slate-700 transition">Presensi Pulang</a>
                    </div>
                @else
                    <div class="mt-4 italic text-slate-400 text-sm">Selesai untuk hari ini!</div>
                @endif
            @endif
        </div>
        <i class="bi bi-clock-history absolute -bottom-10 -right-10 text-[15rem] text-slate-50"></i>
    </div>
</div>
@endsection

