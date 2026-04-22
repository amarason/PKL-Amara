@extends('layouts.peserta')

@section('content')
<div class="space-y-10">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        
        {{-- Form Pengajuan --}}
        <div class="lg:col-span-1">
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 sticky top-24">
                <h3 class="text-xl font-black text-slate-800 mb-6">Ajukan Izin</h3>
                
                <form action="{{ route('user.izin.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tanggal Izin</label>
                        <input type="date" 
                            name="leave_date" 
                            id="leave_date"
                            value="{{ date('Y-m-d') }}" 
                            required 
                            class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-50 outline-none font-bold text-slate-700 transition">
                        <p class="text-[9px] text-blue-600 font-bold ml-1 italic">*Berlaku untuk tanggal sebelumnya, hari ini, atau mendatang.</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Alasan</label>
                        <textarea name="reason" required placeholder="Contoh: Sakit demam, Keperluan keluarga..." class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-50 outline-none font-medium h-32 resize-none transition"></textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Lampiran (Opsional)</label>
                        
                        {{-- Atribut accept=".pdf,.jpg,.jpeg,.png" --}}
                        <input type="file" name="document" id="document_input" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 transition">
                        
                        {{-- Teks Format--}}
                        <p id="file_help_text" class="text-[9px] text-slate-400 mt-1 transition-all">
                            *Format: PDF, JPG, PNG (Maks 2MB)
                        </p>
                        
                        {{-- Teks Peringatan Error --}}
                        <p id="file_error_text" class="text-[10px] text-red-500 font-bold mt-1 hidden transition-all">
                        </p>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-blue-100 hover:bg-blue-700 transition active:scale-95">
                        Kirim Pengajuan
                    </button>
                </form>
            </div>
        </div>

        {{-- Riwayat Izin --}}
        <div class="lg:col-span-2">
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 h-full">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-black text-slate-800">Riwayat Pengajuan</h3>
                    <i class="bi bi-clock-history text-slate-200 text-2xl"></i>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-400 uppercase text-[10px] font-black tracking-widest">
                                <th class="px-6 py-4 rounded-l-2xl">Tanggal</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-center">Lampiran</th>
                                <th class="px-6 py-4 rounded-r-2xl text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($leaveRequests as $leave)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-5">
                                    <p class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($leave->leave_date)->translatedFormat('d M Y') }}</p>
                                    <p class="text-[10px] text-slate-400 italic">"{{ Str::limit($leave->reason, 40) }}"</p>
                                </td>
                                <td class="px-6 py-5">
                                    @if($leave->status == 'menunggu')
                                        <span class="px-3 py-1 bg-amber-100 text-amber-600 rounded-lg text-[9px] font-black uppercase tracking-widest">Menunggu</span>
                                    @elseif($leave->status == 'disetujui')
                                        <span class="px-3 py-1 bg-green-100 text-green-600 rounded-lg text-[9px] font-black uppercase tracking-widest">Disetujui</span>
                                    @else
                                        <span class="px-3 py-1 bg-red-100 text-red-600 rounded-lg text-[9px] font-black uppercase tracking-widest">Ditolak</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @if($leave->document_path)
                                        <a href="{{ asset($leave->document_path) }}" target="_blank" class="text-blue-500 hover:text-blue-700 transition inline-block">
                                            <i class="bi bi-file-earmark-text text-xl"></i>
                                        </a>
                                    @else
                                        <span class="text-slate-300 text-[10px] font-bold uppercase italic tracking-tighter">No File</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @if($leave->status == 'menunggu')
                                        <form action="{{ route('user.izin.destroy', $leave->leave_id) }}" method="POST" class="inline-block" onsubmit="return confirmCancel(event, this)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 bg-red-50 text-red-500 rounded-xl flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                                <i class="bi bi-trash3-fill text-sm"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-300" title="Data sudah dikunci"><i class="bi bi-lock-fill"></i></span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center space-y-2">
                                        <i class="bi bi-inbox text-slate-100 text-5xl"></i>
                                        <p class="text-slate-300 italic font-medium">Belum ada pengajuan izin.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    /**
     * 1. Fungsi Konfirmasi Pembatalan
     */
    function confirmCancel(event, form) {
        event.preventDefault(); 
        
        Swal.fire({
            title: 'Batalkan Izin?',
            text: "Pengajuan izin ini akan dihapus permanen dari sistem.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Tutup',
            customClass: {
                popup: 'rounded-[2rem]'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    /**
     * 2. Pengecekan Format dan Ukuran File (Tanpa Alert)
     */
    const docInput = document.getElementById('document_input');
    const fileHelpText = document.getElementById('file_help_text');
    const fileErrorText = document.getElementById('file_error_text');

    if (docInput) {
        docInput.addEventListener('change', function() {
            const file = this.files[0];
            const maxSize = 2 * 1024 * 1024; // 2 MB
            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];

            if (file) {
                // Cek Format File
                if (!allowedTypes.includes(file.type)) {
                    fileErrorText.innerText = '*Peringatan: Format file harus berupa PDF, JPG, atau PNG!';
                    fileErrorText.classList.remove('hidden');
                    fileHelpText.classList.add('hidden');
                    this.value = ''; // Kosongkan input otomatis
                } 
                // Cek Ukuran File
                else if (file.size > maxSize) {
                    fileErrorText.innerText = '*Peringatan: Ukuran file melebihi batas maksimal 2MB!';
                    fileErrorText.classList.remove('hidden');
                    fileHelpText.classList.add('hidden');
                    this.value = ''; // Kosongkan input otomatis
                } 
                // Jika Valid
                else {
                    fileErrorText.classList.add('hidden');
                    fileHelpText.classList.remove('hidden');
                }
            } else {
                // Jika user batal memilih file
                fileErrorText.classList.add('hidden');
                fileHelpText.classList.remove('hidden');
            }
        });
    }

    /**
     * 3. Penanganan Error Validasi Laravel ($errors)
     */
    @if($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Gagal Validasi',
            text: "{{ $errors->first() }}",
            confirmButtonColor: '#ef4444',
            customClass: { popup: 'rounded-[2rem]' }
        });
    @endif

    /**
     * 4. Flash Message: Berhasil
     */
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            timer: 2500,
            showConfirmButton: false,
            customClass: { popup: 'rounded-[2rem]' }
        });
    @endif

    /**
     * 5. Flash Message: Gagal (Manual Error)
     */
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: "{{ session('error') }}",
            confirmButtonColor: '#ef4444',
            customClass: { popup: 'rounded-[2rem]' }
        });
    @endif
</script>

@endsection