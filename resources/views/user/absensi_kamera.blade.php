@php
    // Inisialisasi waktu sekarang dengan zona waktu Jakarta
    $now = \Carbon\Carbon::now('Asia/Jakarta');
    $jamMenit = $now->format('H:i');
    
    // Ambil data internship dari user yang sedang login untuk cek status keaktifan
    $internship = auth()->user()->internship;
    $isAktif = ($internship && $internship->status === 'aktif');

    // Pengaturan Jam Operasional (Masuk: 00:00 - 12:00, Pulang: 12:01 - 19:00)
    $canCheckIn = ($jamMenit >= '00:00' && $jamMenit <= '12:00');
    $canCheckOut = ($jamMenit >= '12:01' && $jamMenit <= '19:00');

    /** * LOGIKA TAMPILAN KAMERA:
     * Kamera hanya akan muncul jika akun AKTIF dan:
     * 1. Berada di jam masuk DAN belum ada data absensi sama sekali.
     * 2. Berada di jam pulang DAN belum melakukan absen pulang (boleh sudah/belum absen masuk).
     */
    $showCamera = false;
    if ($isAktif) {
        if (!$attendance && $canCheckIn) {
            $showCamera = true;
        } elseif ((!$attendance || !$attendance->check_out_time) && $canCheckOut) {
            $showCamera = true;
        }
    }
    
    // Status khusus: Memberi tahu user untuk menunggu jam pulang jika sudah absen masuk pagi hari
    $waitingForCheckOut = ($isAktif && $attendance && !$attendance->check_out_time && $canCheckIn);
@endphp

@extends('layouts.peserta')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 pb-20">
    <div class="text-center">
        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Presensi Harian</h2>
        {{-- Indikator Visual Jam Operasional --}}
        <div class="mt-3 flex justify-center gap-2">
            <span class="px-4 py-1.5 {{ $canCheckIn ? 'bg-blue-100 text-blue-600 border-blue-200' : 'bg-slate-100 text-slate-400 border-slate-200' }} rounded-xl text-[10px] font-black uppercase tracking-wider border">
                Masuk: 00:00 - 12:00
            </span>
            <span class="px-4 py-1.5 {{ $canCheckOut ? 'bg-emerald-100 text-emerald-600 border-emerald-200' : 'bg-slate-100 text-slate-400 border-slate-200' }} rounded-xl text-[10px] font-black uppercase tracking-wider border">
                Pulang: 12:01 - 19:00
            </span>
        </div>
    </div>

    <div class="bg-white p-8 sm:p-12 rounded-[3rem] shadow-xl shadow-slate-200/50 border border-slate-100 flex flex-col items-center">
        
        {{-- KONDISI 1: AKUN SUDAH TIDAK AKTIF / SELESAI PKL --}}
        @if(!$isAktif)
            <div class="text-center py-12">
                <div class="w-28 h-28 bg-red-50 text-red-500 rounded-full flex items-center justify-center text-5xl mx-auto mb-8 shadow-inner">
                    <i class="bi bi-person-x-fill"></i>
                </div>
                <h4 class="text-xl font-black text-slate-800 uppercase tracking-tight">Akses Dinonaktifkan</h4>
                <p class="text-slate-400 text-xs font-bold mt-2 leading-relaxed uppercase tracking-widest">
                    Status PKL Anda sudah berakhir (SELESAI).<br>
                    Fitur presensi sudah ditutup oleh Admin.
                </p>
                <a href="{{ route('user.dashboard') }}" class="mt-8 inline-block bg-slate-800 text-white px-8 py-3 rounded-xl font-black uppercase text-[10px] tracking-widest hover:bg-slate-700 transition-all shadow-lg">Kembali ke Dashboard</a>
            </div>

        {{-- KONDISI 2: SUDAH SELESAI PRESENSI MASUK & PULANG HARI INI --}}
        @elseif($attendance && $attendance->check_out_time)
            <div class="text-center py-12">
                <div class="w-28 h-28 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-6xl mx-auto mb-8 shadow-inner">
                    <i class="bi bi-check-all"></i>
                </div>
                <h4 class="text-2xl font-black text-slate-800">Presensi Hari Ini Selesai!</h4>
                <p class="text-slate-400 font-bold mt-2 italic">Data kehadiran Anda telah tercatat dengan aman dalam sistem.</p>
                <a href="{{ route('user.dashboard') }}" class="mt-10 inline-block bg-slate-800 text-white px-10 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-slate-700 transition-all shadow-lg">Kembali ke Dashboard</a>
            </div>

        {{-- KONDISI 3: SUDAH ABSEN MASUK, TAPI MASIH DI JAM PAGI (MENUNGGU JAM PULANG) --}}
        @elseif($waitingForCheckOut)
            <div class="text-center py-12">
                <div class="w-28 h-28 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center text-5xl mx-auto mb-8">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h4 class="text-xl font-black text-slate-800 uppercase tracking-tight">Presensi Masuk Berhasil</h4>
                <p class="text-slate-400 text-xs font-bold mt-2 leading-relaxed uppercase tracking-widest">
                    Anda sudah melakukan presensi masuk.<br>
                    Sesi presensi pulang akan dibuka otomatis mulai pukul 12:01 WIB.
                </p>
                <a href="{{ route('user.dashboard') }}" class="mt-8 inline-block bg-slate-100 text-slate-600 px-8 py-3 rounded-xl font-black uppercase text-[10px] tracking-widest hover:bg-slate-200 transition-all">Kembali ke Dashboard</a>
            </div>

        @else
            {{-- KONDISI 4: AREA ABSENSI AKTIF (SESI MASUK ATAU SESI PULANG) --}}
            <div class="flex gap-4 mb-10 w-full max-w-md">
                {{-- Pilihan Sesi Masuk --}}
                <label class="flex-1 cursor-pointer group">
                    <input type="radio" name="absensi_type" value="masuk" id="type_masuk" class="hidden peer" 
                        {{ (!$attendance && $canCheckIn) ? 'checked' : '' }} 
                        {{ ($attendance || !$canCheckIn) ? 'disabled' : '' }}>
                    <div class="p-5 border-2 border-slate-100 rounded-[2rem] text-center transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-disabled:opacity-40 peer-disabled:bg-slate-50 group-hover:border-blue-200">
                        <i class="bi bi-box-arrow-in-right text-3xl mb-2 block {{ (!$attendance && $canCheckIn) ? 'text-blue-500' : 'text-slate-400' }}"></i>
                        <span class="text-[11px] font-black uppercase tracking-widest block">Masuk</span>
                    </div>
                </label>

                {{-- Pilihan Sesi Pulang --}}
                <label class="flex-1 cursor-pointer group">
                    <input type="radio" name="absensi_type" value="pulang" id="type_pulang" class="hidden peer" 
                        {{ ($canCheckOut && (!$attendance || !$attendance->check_out_time)) ? 'checked' : '' }}
                        {{ (!$canCheckOut || ($attendance && $attendance->check_out_time)) ? 'disabled' : '' }}>
                    <div class="p-5 border-2 border-slate-100 rounded-[2rem] text-center transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-disabled:opacity-40 peer-disabled:bg-slate-50 group-hover:border-emerald-200">
                        <i class="bi bi-box-arrow-right text-3xl mb-2 block {{ ($canCheckOut && (!$attendance || !$attendance->check_out_time)) ? 'text-emerald-500' : 'text-slate-400' }}"></i>
                        <span class="text-[11px] font-black uppercase tracking-widest block">Pulang</span>
                    </div>
                </label>
            </div>

            {{-- Area Kamera muncul secara dinamis berdasarkan variabel $showCamera --}}
            @if($showCamera)
                <div class="relative w-full max-w-md mx-auto mb-10 group">
                    <div class="absolute -inset-4 bg-gradient-to-tr from-blue-50 to-cyan-50 rounded-[3.5rem] -z-10 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative aspect-square bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 overflow-hidden flex items-center justify-center">
                        <div class="relative w-full h-full rounded-[2.5rem] overflow-hidden bg-slate-100">
                            <div id="camera-wrapper" class="relative w-full h-full flex items-center justify-center">
                                <div id="my_camera" class="w-full h-full"></div>
                                <div id="results" class="absolute inset-0 hidden w-full h-full"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kontrol Kamera --}}
                <div class="flex flex-col sm:flex-row gap-4 w-full justify-center px-4">
                    <button id="btn-snapshot" onclick="take_snapshot()" class="bg-blue-600 text-white px-12 py-5 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] hover:bg-blue-700 transition-all shadow-xl shadow-blue-200">
                        <i class="bi bi-camera-fill mr-2"></i> Ambil Foto
                    </button>
                    <button id="btn-confirm" onclick="send_data()" class="hidden bg-emerald-500 text-white px-12 py-5 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] hover:bg-emerald-600 transition-all shadow-xl shadow-emerald-200">
                        <i class="bi bi-cloud-arrow-up-fill mr-2"></i> Konfirmasi & Kirim
                    </button>
                    <button id="btn-reset" onclick="reset_camera()" class="hidden bg-slate-100 text-slate-600 px-12 py-5 rounded-2xl font-black uppercase tracking-[0.2em] text-[10px] hover:bg-slate-200 transition-all border border-slate-200">
                        <i class="bi bi-arrow-counterclockwise mr-2"></i> Ulangi
                    </button>
                </div>
            @else
                {{-- Pesan jika mencoba akses di luar jam operasional atau hari libur --}}
                <div class="p-10 bg-red-50 rounded-[2.5rem] border border-red-100 text-red-600 text-center max-w-sm mx-auto">
                    <i class="bi bi-clock-history text-5xl block mb-4 opacity-50"></i>
                    <p class="text-lg font-black leading-tight uppercase tracking-tight">Akses Terkunci</p>
                    <p class="text-[10px] mt-2 font-bold opacity-80 uppercase tracking-widest leading-relaxed">
                        Sistem hanya dibuka pada jam operasional yang tertera di atas.
                    </p>
                </div>
            @endif
        @endif 
    </div>
</div>

<style>
#my_camera video,
#results img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important; 
    border-radius: 2rem;
}
/* Flip horizontal video kamera (mirror) */
#my_camera video {
    transform: scaleX(-1) !important;
}
</style>

{{-- Integrasi WebcamJS dan SweetAlert2 --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Inisialisasi Kamera jika elemen tersedia
    if (document.getElementById('my_camera')) {
        Webcam.set({
            width: 640, 
            height: 640, 
            image_format: 'jpeg',
            jpeg_quality: 90,
            constraints: {
                facingMode: 'user' // Menggunakan kamera depan
            }
        });
        Webcam.attach('#my_camera');
    }

    /**
     * Mengambil snapshot dari webcam
     */
    function take_snapshot() {
        Webcam.snap(function(data_uri) {
            document.getElementById('results').innerHTML = '<img src="'+data_uri+'" class="w-full h-full object-cover"/>';
            
            // Sembunyikan live camera, tampilkan hasil foto
            document.getElementById('my_camera').classList.add('hidden');
            document.getElementById('results').classList.remove('hidden');
            
            // Manajemen Tombol
            document.getElementById('btn-snapshot').classList.add('hidden');
            document.getElementById('btn-confirm').classList.remove('hidden');
            document.getElementById('btn-reset').classList.remove('hidden');
        });
    }

    /**
     * Mengulang proses pengambilan foto
     */
    function reset_camera() {
        document.getElementById('my_camera').classList.remove('hidden');
        document.getElementById('results').classList.add('hidden');
        
        document.getElementById('btn-snapshot').classList.remove('hidden');
        document.getElementById('btn-confirm').classList.add('hidden');
        document.getElementById('btn-reset').classList.add('hidden');
    }

    /**
     * Mengirim data foto dan tipe absensi ke server
     */
    function send_data() {
        const image = document.querySelector('#results img').src;
        const radioChecked = document.querySelector('input[name=\"absensi_type\"]:checked');
        
        if (!radioChecked) {
            Swal.fire('Error', 'Silahkan pilih tipe absensi (Masuk/Pulang) terlebih dahulu', 'error');
            return;
        }

        const type = radioChecked.value;
        const targetUrl = type === 'masuk' 
            ? '{{ route("user.absensi.masuk") }}' 
            : '{{ route("user.absensi.pulang") }}';

        Swal.fire({
            title: 'Mengirim Data...',
            text: 'Sedang memproses presensi Anda',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        // Pengiriman data menggunakan Fetch API
        fetch(targetUrl, { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                photo: image 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.success,
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#3b82f6'
                }).then(() => {
                    // Reload halaman agar logika PHP mengevaluasi ulang status (menutup kamera)
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: data.error, 
                    confirmButtonColor: '#ef4444'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Gagal terhubung ke server. Periksa koneksi internet Anda.', 'error');
        });
    }
</script>
@endsection