@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto pb-20 px-4 lg:px-0">

    {{-- NOTIFIKASI --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-2xl flex items-center shadow-sm">
            <i class="bi bi-check-circle-fill mr-3 text-lg"></i>
            <span class="font-bold text-sm">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-600 rounded-2xl flex items-center shadow-sm">
            <i class="bi bi-exclamation-triangle-fill mr-3 text-lg"></i>
            <span class="font-bold text-sm">{{ session('error') }}</span>
        </div>
    @endif

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-10">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Pendaftaran Peserta PKL</h2>
            <p class="text-slate-400 text-sm mt-1 font-medium italic">Lengkapi data peserta untuk pembuatan akun sistem.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-slate-400 hover:text-blue-500 font-bold text-xs uppercase tracking-widest flex items-center transition">
            <i class="bi bi-arrow-left-circle mr-2 text-lg"></i> Dashboard
        </a>
    </div>

    {{-- FORM CARD --}}
    <div class="bg-white rounded-[3rem] shadow-xl shadow-blue-900/5 border border-slate-100 overflow-hidden">
        {{-- Menambahkan ID pada form agar bisa diakses script reset --}}
        <form id="pendaftaranForm" action="{{ route('admin.peserta.store') }}" method="POST" class="p-8 sm:p-12">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-12 gap-y-8">
                {{-- AKUN --}}
                <div class="col-span-full bg-slate-50/70 rounded-3xl p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-200">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-700">Informasi Akun & Login</h3>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Login ID</label>
                            <input type="text" name="login_id" value="{{ old('login_id') }}" required class="w-full px-6 py-4 bg-white border {{ $errors->has('login_id') ? 'border-red-500' : 'border-slate-200' }} rounded-2xl focus:ring-4 focus:ring-blue-100 outline-none font-bold text-slate-700" placeholder="IP26/S1/001">
                            @error('login_id') <p class="text-red-500 text-[10px] font-bold italic ml-2">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-6 py-4 bg-white border rounded-2xl focus:ring-4 focus:ring-blue-100 outline-none font-bold text-slate-700" placeholder="Nama lengkap peserta">
                        </div>
                    </div>
                </div>

                {{-- AKADEMIK --}}
                <div class="col-span-full bg-slate-50/70 rounded-3xl p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-10 h-10 bg-cyan-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-cyan-200">
                            <i class="bi bi-briefcase-fill"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-700">Detail Akademik</h3>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {{-- INSTANSI --}}
                        <div class="space-y-2">
                            <div class="flex justify-between items-center ml-1">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Instansi</label>
                                <button type="button" onclick="openModal('institution')" class="text-[10px] font-bold text-blue-500 uppercase hover:underline">+ Tambah</button>
                            </div>
                            <select name="institution_id" id="institution_select" required class="w-full px-6 py-4 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-100 outline-none font-bold text-slate-700">
                                <option value="" disabled selected>Pilih Instansi</option>
                                @foreach($institutions as $inst)
                                    <option value="{{ $inst->institution_id }}">{{ $inst->institution_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- JURUSAN --}}
                        <div class="space-y-2">
                            <div class="flex justify-between items-center ml-1">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Jurusan</label>
                                <button type="button" onclick="openModal('major')" class="text-[10px] font-bold text-blue-500 uppercase hover:underline">+ Tambah</button>
                            </div>
                            <select name="major_id" id="major_select" required class="w-full px-6 py-4 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-100 outline-none font-bold text-slate-700">
                                <option value="" disabled selected>Pilih Jurusan</option>
                                @foreach($majors as $major)
                                    <option value="{{ $major->major_id }}">{{ $major->major_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- TANGGAL --}}
                        <div class="space-y-2">
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Mulai</label>
                            <input type="date" name="start_date" required class="w-full px-6 py-4 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-100 outline-none font-bold text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Selesai</label>
                            <input type="date" name="end_date" required class="w-full px-6 py-4 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-100 outline-none font-bold text-slate-700">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                <p class="text-slate-400 text-xs italic"><i class="bi bi-info-circle mr-2"></i>Pastikan data sesuai dokumen resmi</p>
                <div class="flex gap-4">
                    {{-- TOMBOL RESET DENGAN WARNA BARU & KONFIRMASI --}}
                    <button type="button" onclick="confirmReset()" class="px-8 py-3 text-slate-400 font-black uppercase text-[10px] tracking-widest hover:text-red-500 hover:bg-red-50 active:bg-red-100 rounded-2xl transition-all duration-200 ease-in-out">
                        Reset
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-black px-10 py-3 rounded-2xl shadow-lg shadow-blue-200 uppercase text-[10px] tracking-[0.2em]">
                        Daftarkan Peserta
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL TAMBAH CEPAT (Instansi/Jurusan) --}}
<div id="quickModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] w-full max-w-md p-8 shadow-2xl transform transition-all">
        <h4 id="modalTitle" class="text-xl font-black text-slate-800 mb-2">Tambah Data</h4>
        <p class="text-slate-400 text-sm mb-6">Masukkan nama baru untuk didaftarkan ke sistem.</p>
        <input type="hidden" id="modalTarget">
        <input type="text" id="newNameInput" class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-100 outline-none font-bold text-slate-700 mb-6" placeholder="Ketik nama di sini...">
        <div class="flex space-x-3">
            <button type="button" onclick="closeModal()" class="flex-1 py-4 text-slate-400 font-bold uppercase text-[10px] tracking-widest">Batal</button>
            <button type="button" onclick="saveNewData()" class="flex-1 bg-blue-600 text-white py-4 rounded-xl font-bold uppercase text-[10px] tracking-widest shadow-lg shadow-blue-100">Simpan</button>
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI RESET --}}
<div id="resetModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[60] hidden items-center justify-center p-4">
    <div class="bg-white rounded-[2.5rem] w-full max-w-xs p-8 shadow-2xl text-center">
        <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="bi bi-trash3-fill text-2xl"></i>
        </div>
        <h4 class="text-lg font-black text-slate-800 mb-2">Kosongkan Form?</h4>
        <p class="text-slate-400 text-xs mb-6 font-medium">Semua data yang sudah Anda ketik akan hilang.</p>
        <div class="flex flex-col gap-2">
            <button type="button" onclick="executeReset()" class="w-full bg-red-500 text-white py-3 rounded-xl font-bold uppercase text-[10px] tracking-widest shadow-lg shadow-red-100">Ya, Hapus Semua</button>
            <button type="button" onclick="closeResetModal()" class="w-full py-3 text-slate-400 font-bold uppercase text-[10px] tracking-widest">Batal</button>
        </div>
    </div>
</div>

{{-- SCRIPTS --}}
<script>
    // --- Logika Modal Tambah Cepat ---
    function openModal(type) {
        const modal = document.getElementById('quickModal');
        const title = document.getElementById('modalTitle');
        const target = document.getElementById('modalTarget');
        target.value = type;
        title.innerText = type === 'institution' ? 'Tambah Instansi Baru' : 'Tambah Jurusan Baru';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        const modal = document.getElementById('quickModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('newNameInput').value = '';
    }

    function saveNewData() {
        const type = document.getElementById('modalTarget').value;
        const name = document.getElementById('newNameInput').value.trim();
        if (!name) { alert('Nama tidak boleh kosong'); return; }

        const url = type === 'institution' ? '{{ route("admin.institution.store") }}' : '{{ route("admin.major.store") }}';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ name })
        })
        .then(async res => {
            if (!res.ok) {
                const errorData = await res.json();
                throw new Error(errorData.message || 'Gagal menyimpan data');
            }
            return res.json();
        })
        .then(data => {
            const select = document.getElementById(type + '_select');
            const option = new Option(data.name, data.id, true, true);
            select.add(option);
            closeModal();
        })
        .catch(err => alert(err.message));
    }

    // --- Logika Konfirmasi Reset ---
    function confirmReset() {
        const modal = document.getElementById('resetModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeResetModal() {
        const modal = document.getElementById('resetModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function executeReset() {
        document.getElementById('pendaftaranForm').reset();
        closeResetModal();
    }
</script>
@endsection