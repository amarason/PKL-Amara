@extends('layouts.peserta')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <div class="text-center">
        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Presensi Harian</h2>
        <p class="text-slate-400 font-bold text-sm mt-1">
            @if(!$attendance)
                Silakan ambil foto selfie untuk Absen Masuk
            @elseif($attendance && !$attendance->check_out_time)
                Silakan ambil foto selfie untuk Absen Pulang
            @else
                Anda sudah menyelesaikan presensi hari ini
            @endif
        </p>
    </div>

    <div class="bg-white p-8 rounded-[3rem] shadow-sm border border-slate-100 flex flex-col items-center">
        @if($attendance && $attendance->check_out_time)
            <div class="text-center py-10">
                <div class="w-24 h-24 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-5xl mx-auto mb-6">
                    <i class="bi bi-check-all"></i>
                </div>
                <h4 class="text-xl font-black text-slate-800">Presensi Selesai!</h4>
                <p class="text-slate-400 font-medium">Hati-hati di jalan saat pulang.</p>
                <a href="{{ route('user.dashboard') }}" class="mt-8 inline-block bg-slate-100 text-slate-600 px-8 py-3 rounded-2xl font-bold uppercase text-xs tracking-widest hover:bg-slate-200 transition">Kembali ke Dashboard</a>
            </div>
        @else
            <div id="my_camera" class="rounded-[2rem] overflow-hidden border-4 border-slate-50 shadow-inner bg-slate-100 mx-auto"></div>
            <div id="results" class="hidden rounded-[2rem] overflow-hidden border-4 border-blue-500 shadow-lg mx-auto"></div>

            <div class="mt-10 flex flex-col sm:flex-row gap-4 w-full justify-center">
                <button id="btn-capture" onclick="take_snapshot()" class="bg-blue-600 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl shadow-blue-100">
                    <i class="bi bi-camera-fill mr-2"></i> 
                    {{ !$attendance ? 'Ambil Foto & Absen Masuk' : 'Ambil Foto & Absen Pulang' }}
                </button>
                
                <button id="btn-reset" onclick="reset_camera()" class="hidden bg-slate-100 text-slate-600 px-10 py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-slate-200 transition-all">
                    <i class="bi bi-arrow-clockwise mr-2"></i> Ulangi Foto
                </button>
            </div>
        @endif
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>

<script>
    const hasAttendance = {{ $attendance ? 'true' : 'false' }};
    const hasCheckOut = {{ ($attendance && $attendance->check_out_time) ? 'true' : 'false' }};
    const routeMasuk = "{{ route('user.absensi.masuk') }}";
    const routePulang = "{{ route('user.absensi.pulang') }}";
    const csrfToken = "{{ csrf_token() }}";

    // Inisialisasi Webcam
    if (!hasCheckOut) {
        Webcam.set({
            width: 400,
            height: 400,
            dest_width: 640,
            dest_height: 640,
            image_format: 'jpeg',
            jpeg_quality: 90,
            constraints: { 
                facingMode: 'user' 
            }
        });
        Webcam.attach('#my_camera');
    }

    // Fungsi Ambil Foto
    function take_snapshot() {
        Webcam.snap(function(data_uri) {
            document.getElementById('my_camera').classList.add('hidden');
            document.getElementById('results').innerHTML = '<img src="'+data_uri+'" class="w-[400px] h-[400px] object-cover"/>';
            document.getElementById('results').classList.remove('hidden');
            
            const btnCapture = document.getElementById('btn-capture');
            btnCapture.innerHTML = '<i class="bi bi-cloud-upload-fill mr-2"></i> Mengirim...';
            btnCapture.disabled = true;
            document.getElementById('btn-reset').classList.remove('hidden');

            const targetRoute = !hasAttendance ? routeMasuk : routePulang;

            // Kirim data ke Server
            fetch(targetRoute, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({ photo: data_uri })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.success,
                        showConfirmButton: false,
                        timer: 2000,
                        customClass: { popup: 'rounded-[2rem]' }
                    }).then(() => {
                        window.location.href = "{{ route('user.dashboard') }}";
                    });
                } else {
                    throw new Error(data.error || 'Terjadi kesalahan sistem');
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: err.message,
                    customClass: { popup: 'rounded-[2rem]' }
                });
                reset_camera();
            });
        });
    }

    // Fungsi Reset Kamera
    function reset_camera() {
        document.getElementById('my_camera').classList.remove('hidden');
        document.getElementById('results').classList.add('hidden');
        document.getElementById('btn-reset').classList.add('hidden');
        
        const btnCapture = document.getElementById('btn-capture');
        const btnText = !hasAttendance ? 'Ambil Foto & Absen Masuk' : 'Ambil Foto & Absen Pulang';
        btnCapture.innerHTML = '<i class="bi bi-camera-fill mr-2"></i> ' + btnText;
        btnCapture.disabled = false;
    }
</script>
@endsection