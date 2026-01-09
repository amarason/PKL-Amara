@extends('layouts.admin')

@section('content')
<div class="space-y-10 pb-20">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Absensi Harian</h2>
            <p class="text-slate-400 font-bold text-sm mt-1">
                <i class="bi bi-calendar-event mr-2"></i>Monitoring data absensi peserta
            </p>
        </div>

        <div class="bg-white p-2 rounded-2xl border border-slate-100 shadow-sm flex items-center space-x-3">
            <span class="pl-4 text-[10px] font-black uppercase text-slate-400 tracking-widest">Pilih Tanggal:</span>
            <form action="{{ route('admin.absensi.index') }}" method="GET" id="formTanggal">
                <input type="date" 
                       name="tanggal" 
                       value="{{ $tanggalDipilih }}"
                       onchange="document.getElementById('formTanggal').submit()"
                       class="bg-blue-50 text-blue-600 font-bold px-4 py-2 rounded-xl border-none outline-none focus:ring-2 focus:ring-blue-500 transition-all cursor-pointer">
            </form>
        </div>
    </div>

    {{-- Sub-header info tanggal --}}
    <div class="-mt-4">
        <span class="bg-blue-500 text-white px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-100">
            Menampilkan Data: {{ \Carbon\Carbon::parse($tanggalDipilih)->translatedFormat('d M Y') }}
        </span>
    </div>

    {{-- TABEL 1: PERSETUJUAN IZIN (WAITING LIST) --}}
    @if($leaveRequests->count() > 0)
    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
        <div class="flex items-center space-x-4 mb-8">
            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-2xl">
                <i class="bi bi-envelope-paper-fill"></i>
            </div>
            <div>
                <h3 class="text-xl font-black text-slate-800 leading-none">Persetujuan Izin</h3>
                <p class="text-slate-400 text-xs font-bold mt-1 uppercase tracking-widest">{{ $leaveRequests->count() }} Menunggu Tindakan</p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-[2rem] border border-slate-50">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 text-slate-400 uppercase text-[10px] font-black tracking-widest">
                    <tr>
                        <th class="px-8 py-5">Peserta</th>
                        <th class="px-8 py-5">Alasan</th>
                        <th class="px-8 py-5">Lampiran</th>
                        <th class="px-8 py-5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($leaveRequests as $leave)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-8 py-5">
                            <p class="font-bold text-slate-800 text-sm">{{ $leave->internship->user->name }}</p>
                            <p class="text-[10px] text-blue-500 font-black uppercase">{{ $leave->internship->user->login_id }}</p>
                        </td>
                        <td class="px-8 py-5 italic text-slate-500 text-sm font-medium">"{{ $leave->reason }}"</td>
                        <td class="px-8 py-5">
                            @if($leave->document_path)
                                <a href="{{ asset('storage/' . $leave->document_path) }}" target="_blank" class="text-blue-600 font-bold text-xs hover:underline">
                                    <i class="bi bi-file-earmark-pdf-fill mr-1"></i> Lihat Dokumen
                                </a>
                            @else
                                <span class="text-slate-300 text-[10px] font-bold uppercase italic">Tanpa Lampiran</span>
                            @endif
                        </td>
                        <td class="px-8 py-5 text-center">
                            <div class="flex justify-center space-x-2">
                                <form id="form-terima-{{ $leave->leave_id }}" action="{{ route('admin.izin.verify', $leave->leave_id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="disetujui">
                                    <button type="button" onclick="confirmVerify('{{ $leave->leave_id }}', 'disetujui', '{{ $leave->internship->user->name }}')" class="bg-green-500 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase hover:bg-green-600 transition">Terima</button>
                                </form>
                                <form id="form-tolak-{{ $leave->leave_id }}" action="{{ route('admin.izin.verify', $leave->leave_id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="ditolak">
                                    <button type="button" onclick="confirmVerify('{{ $leave->leave_id }}', 'ditolak', '{{ $leave->internship->user->name }}')" class="bg-white border border-red-200 text-red-500 px-4 py-2 rounded-xl text-[10px] font-black uppercase hover:bg-red-50 transition">Tolak</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- TABEL 2: LOG KEHADIRAN PESERTA --}}
    <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
        <div class="flex items-center space-x-4 mb-8">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-2xl">
                <i class="bi bi-clock-history"></i>
            </div>
            <h3 class="text-xl font-black text-slate-800">Log Kehadiran Peserta</h3>
        </div>

        <div class="overflow-x-auto rounded-[2rem] border border-slate-50">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#AEE2FF] text-slate-700 font-bold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-8 py-5">No</th>
                        <th class="px-8 py-5">Nama Peserta</th>
                        <th class="px-8 py-5">No ID</th>
                        <th class="px-8 py-5 text-center">Jam Masuk</th>
                        <th class="px-8 py-5 text-center">Jam Pulang</th>
                        <th class="px-8 py-5 text-center">Status</th>
                        <th class="px-8 py-5 text-center">Bukti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($attendance as $index => $log)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-8 py-5 text-slate-400 font-bold">{{ $index + 1 }}.</td>
                        <td class="px-8 py-5 font-bold text-slate-800">{{ $log->internship->user->name }}</td>
                        <td class="px-8 py-5 font-medium text-blue-500">{{ $log->internship->user->login_id }}</td>
                        <td class="px-8 py-5 text-center font-bold text-slate-700">{{ \Carbon\Carbon::parse($log->check_in_time)->format('H:i') }}</td>
                        <td class="px-8 py-5 text-center font-bold text-slate-700">{{ $log->check_out_time ? \Carbon\Carbon::parse($log->check_out_time)->format('H:i') : '--:--' }}</td>
                        <td class="px-8 py-5 text-center">
                            <button type="button" 
                                onclick="openEditStatusModal('{{ $log->attendance_id }}', '{{ $log->status }}', '{{ $log->internship->user->name }}')"
                                class="px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all hover:scale-105 shadow-sm
                                {{ $log->status == 'hadir' ? 'bg-green-100 text-green-600' : ($log->status == 'izin' ? 'bg-blue-100 text-blue-600' : 'bg-red-100 text-red-600') }}">
                                {{ $log->status }} <i class="bi bi-pencil-fill ml-1 text-[8px]"></i>
                            </button>
                        </td>
                        <td class="px-8 py-5 text-center">
                            <button onclick="openPhotoModal('{{ $log->check_in_photo }}', '{{ $log->check_out_photo }}', '{{ $log->internship->user->name }}')" class="text-slate-300 hover:text-blue-500 transition">
                                <i class="bi bi-image text-xl"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-8 py-20 text-center text-slate-300 italic font-bold">Belum ada data kehadiran pada tanggal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABEL 3: RIWAYAT LOG PERIZINAN (DISPROVED & APPROVED) --}}
<div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
    <div class="flex items-center space-x-4 mb-8">
        <div class="w-12 h-12 bg-slate-100 text-slate-600 rounded-2xl flex items-center justify-center text-2xl">
            <i class="bi bi-journal-check"></i>
        </div>
        <div>
            <h3 class="text-xl font-black text-slate-800">Riwayat Log Perizinan</h3>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mt-1">Daftar izin yang telah diproses admin</p>
        </div>
    </div>

    <div class="overflow-x-auto rounded-[2rem] border border-slate-50">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-400 uppercase text-[10px] font-black tracking-widest">
                    <th class="px-6 py-4 rounded-l-2xl">Peserta</th>
                    <th class="px-6 py-4">Alasan Izin</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Diproses Oleh</th>
                    <th class="px-6 py-4 rounded-r-2xl text-right">Waktu Proses</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($leaveLogs as $log)
                <tr class="hover:bg-slate-50/50 transition-all">
                    <td class="px-6 py-5">
                        <p class="font-bold text-slate-800 text-sm">{{ $log->internship->user->name }}</p>
                        <p class="text-[9px] text-slate-400 font-bold tracking-tighter">{{ $log->leave_date }}</p>
                    </td>
                    <td class="px-6 py-5 text-xs text-slate-500 italic max-w-xs truncate">
                        "{{ $log->reason }}"
                    </td>
                    <td class="px-6 py-5">
                        @if($log->status == 'disetujui')
                            <span class="px-3 py-1 bg-green-100 text-green-600 rounded-lg text-[10px] font-black uppercase">
                                <i class="bi bi-check-circle-fill mr-1"></i> Disetujui
                            </span>
                        @else
                            <span class="px-3 py-1 bg-red-100 text-red-600 rounded-lg text-[10px] font-black uppercase">
                                <i class="bi bi-x-circle-fill mr-1"></i> Ditolak
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-5">
                        <p class="font-bold text-slate-700 text-xs">{{ $log->approved_by ?? 'System' }}</p>
                    </td>
                    <td class="px-6 py-5 text-right text-[10px] font-bold text-slate-400">
                        {{ \Carbon\Carbon::parse($log->approved_at)->translatedFormat('d M Y, H:i') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-slate-300 italic font-medium text-sm">Tidak ada riwayat perizinan yang diproses.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL PHOTO BUKTI --}}
<div id="modalPhoto" class="fixed inset-0 z-[999] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="this.parentElement.classList.add('hidden')"></div>
    <div class="relative w-full max-w-2xl bg-white rounded-[3rem] overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-slate-50 flex justify-between items-center">
            <div>
                <h4 class="text-xl font-black text-slate-800 leading-none">Bukti Foto Absensi</h4>
                <p id="photo_user_name" class="text-blue-500 font-bold text-[10px] uppercase tracking-widest mt-2"></p>
            </div>
            <button onclick="document.getElementById('modalPhoto').classList.add('hidden')" class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-slate-400 hover:text-red-500 transition-all">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-4 text-center">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Foto Masuk</p>
                <div class="aspect-square bg-slate-50 rounded-[2rem] overflow-hidden border-2 border-slate-100 flex flex-col items-center justify-center text-slate-300">
                    <img id="img_check_in" src="" class="w-full h-full object-cover hidden">
                    <div id="no_check_in" class="flex flex-col items-center"><i class="bi bi-camera-off text-4xl mb-2"></i><span class="text-[10px] font-bold uppercase tracking-tight">Belum Ada Foto</span></div>
                </div>
            </div>
            <div class="space-y-4 text-center">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Foto Pulang</p>
                <div class="aspect-square bg-slate-50 rounded-[2rem] overflow-hidden border-2 border-slate-100 flex flex-col items-center justify-center text-slate-300">
                    <img id="img_check_out" src="" class="w-full h-full object-cover hidden">
                    <div id="no_check_out" class="flex flex-col items-center"><i class="bi bi-camera-off text-4xl mb-2"></i><span class="text-[10px] font-bold uppercase tracking-tight">Belum Ada Foto</span></div>
                </div>
            </div>
        </div>
        <div class="px-8 pb-8">
            <button onclick="document.getElementById('modalPhoto').classList.add('hidden')" class="w-full py-4 bg-slate-800 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-700 transition-all">Tutup Detail</button>
        </div>
    </div>
</div>

{{-- MODAL KOREKSI STATUS --}}
<div id="modalEditStatus" class="fixed inset-0 z-[999] hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeEditStatusModal()"></div>
    <div class="relative w-full max-w-md bg-white rounded-[3rem] overflow-hidden shadow-2xl">
        <div class="p-8 border-b border-slate-50 flex justify-between items-center">
            <h4 class="text-xl font-black text-slate-800">Koreksi Absensi</h4>
            <button onclick="closeEditStatusModal()" class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center text-slate-400 hover:text-red-500 transition-all">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <form action="{{ route('admin.absensi.updateStatus') }}" method="POST" class="p-8 space-y-6">
            @csrf
            <input type="hidden" name="attendance_id" id="status_attendance_id">
            
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nama Peserta</label>
                <p id="status_user_name" class="font-bold text-slate-800 text-lg"></p>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pilih Status Baru</label>
                <select name="status" id="status_select" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-bold text-sm outline-none focus:ring-4 focus:ring-blue-100 transition-all appearance-none">
                    <option value="hadir">Hadir</option>
                    <option value="izin">Izin</option>
                    <option value="alpha">Alpha</option>
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Alasan Perubahan</label>
                <textarea name="update_reason" required placeholder="Contoh: Peserta lupa absen tapi ada di lokasi..." 
                    class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl font-medium text-sm outline-none focus:ring-4 focus:ring-blue-100 h-28 transition-all resize-none"></textarea>
            </div>

            <button type="submit" class="w-full py-4 bg-blue-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 shadow-xl shadow-blue-100 transition-all active:scale-[0.98]">
                Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<script>
    function openEditStatusModal(id, currentStatus, name) {
        document.getElementById('status_attendance_id').value = id;
        document.getElementById('status_user_name').innerText = name;
        document.getElementById('status_select').value = currentStatus;
        document.getElementById('modalEditStatus').classList.remove('hidden');
    }

    function closeEditStatusModal() {
        document.getElementById('modalEditStatus').classList.add('hidden');
    }

    function openPhotoModal(checkIn, checkOut, name) {
        const modal = document.getElementById('modalPhoto');
        const imgIn = document.getElementById('img_check_in');
        const imgOut = document.getElementById('img_check_out');
        const noIn = document.getElementById('no_check_in');
        const noOut = document.getElementById('no_check_out');
        document.getElementById('photo_user_name').innerText = name;
        const baseUrl = window.location.origin + "/";

        if (checkIn && checkIn !== 'leave_approved.png') {
            imgIn.src = baseUrl + checkIn; 
            imgIn.classList.remove('hidden'); noIn.classList.add('hidden');
        } else if (checkIn === 'leave_approved.png') {
            noIn.innerHTML = '<i class="bi bi-file-earmark-check text-4xl mb-2 text-blue-400"></i><span class="text-[10px] font-bold uppercase">Izin Disetujui</span>';
            imgIn.classList.add('hidden'); noIn.classList.remove('hidden');
        } else {
            imgIn.classList.add('hidden'); noIn.classList.remove('hidden');
        }

        if (checkOut) {
            imgOut.src = baseUrl + checkOut;
            imgOut.classList.remove('hidden'); noOut.classList.add('hidden');
        } else {
            imgOut.classList.add('hidden'); noOut.classList.remove('hidden');
        }
        modal.classList.remove('hidden');
    }

    function confirmVerify(id, action, nama) {
        const isApprove = action === 'disetujui';
        Swal.fire({
            title: isApprove ? 'Setujui Izin?' : 'Tolak Izin?',
            text: `${isApprove ? 'Menerima' : 'Menolak'} permohonan dari ${nama}.`,
            icon: isApprove ? 'success' : 'warning',
            showCancelButton: true,
            confirmButtonColor: isApprove ? '#10B981' : '#EF4444',
            confirmButtonText: isApprove ? 'Ya, Terima!' : 'Ya, Tolak!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[2rem]' }
        }).then((result) => { 
            if (result.isConfirmed) { 
                document.getElementById(isApprove ? `form-terima-${id}` : `form-tolak-${id}`).submit(); 
            } 
        });
    }

    const successMsg = "{{ session('success') }}";
    if (successMsg) {
        Swal.fire({ 
            icon: 'success', 
            title: 'Berhasil!', 
            text: successMsg, 
            timer: 3000, 
            showConfirmButton: false, 
            customClass: { popup: 'rounded-[2rem]' } 
        });
    }
</script>
@endsection