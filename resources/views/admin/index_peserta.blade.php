@extends('layouts.admin')

@section('content')
<div class="flex flex-col space-y-8 px-4 sm:px-0">

    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-6">
        <div>
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">
                Manajemen Peserta
            </h2>
            <p class="text-slate-400 text-sm font-medium mt-1">
                Kelola data dan status masa PKL mahasiswa/siswa
            </p>
        </div>

        <form action="{{ route('admin.peserta.index') }}" method="GET"
              class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 w-full lg:w-auto">
            <input type="hidden" name="status" value="{{ $status }}">

            <div class="relative w-full sm:w-72">
                <i class="bi bi-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full pl-12 pr-6 py-4 bg-white border border-slate-100 rounded-[1.5rem] outline-none focus:ring-4 focus:ring-blue-50 transition shadow-sm"
                       placeholder="Cari Nama atau ID...">
            </div>

            <button type="submit"
                    class="bg-blue-600 text-white px-8 py-4 rounded-[1.5rem] font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 transition uppercase text-[10px] tracking-widest">
                Cari
            </button>
        </form>
    </div>

    {{-- Filter Tab --}}
    <div class="overflow-x-auto">
        <div class="flex space-x-2 p-1 bg-slate-100 w-fit rounded-2xl">
            <a href="{{ route('admin.peserta.index', ['status' => 'aktif']) }}"
               class="px-8 py-3 rounded-xl text-xs font-bold transition whitespace-nowrap
               {{ $status == 'aktif' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                Peserta Aktif
            </a>
            <a href="{{ route('admin.peserta.index', ['status' => 'selesai']) }}"
               class="px-8 py-3 rounded-xl text-xs font-bold transition whitespace-nowrap
               {{ $status == 'selesai' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-400 hover:text-slate-600' }}">
                Peserta Selesai
            </a>
        </div>
    </div>

    {{-- Table Data --}}
    <div class="bg-white rounded-[2rem] sm:rounded-[3rem] border border-slate-50 shadow-sm shadow-blue-900/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left">
                <thead class="bg-[#AEE2FF] text-slate-700 font-bold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 sm:px-8 py-5">No</th>
                        <th class="px-6 sm:px-8 py-5">Nama</th>
                        <th class="px-6 sm:px-8 py-5">No ID PKL</th>
                        <th class="px-6 sm:px-8 py-5">Jurusan</th>
                        <th class="px-6 sm:px-8 py-5">Instansi</th>
                        <th class="px-6 sm:px-8 py-5">Periode</th>
                        <th class="px-6 sm:px-8 py-5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm text-slate-600">
                    @forelse($peserta as $index => $row)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 sm:px-8 py-5">{{ $index + 1 }}.</td>
                        <td class="px-6 sm:px-8 py-5 font-bold text-slate-800 whitespace-nowrap">
                            {{ $row->user->name }}
                        </td>
                        <td class="px-6 sm:px-8 py-5 font-medium text-blue-500 whitespace-nowrap">
                            {{ $row->user->login_id }}
                        </td>
                        <td class="px-6 sm:px-8 py-5">{{ $row->major->major_name }}</td>
                        <td class="px-6 sm:px-8 py-5">{{ $row->institution->institution_name }}</td>
                        <td class="px-6 sm:px-8 py-5 text-slate-400 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($row->start_date)->format('d M y') }}
                            -
                            {{ \Carbon\Carbon::parse($row->end_date)->format('d M y') }}
                        </td>
                        <td class="px-6 sm:px-8 py-5 text-center whitespace-nowrap">
                            @if($status == 'aktif')
                            <form id="form-selesai-{{ $row->internship_id }}"
                                  action="{{ route('admin.peserta.updateStatus', $row->internship_id) }}"
                                  method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="status" value="selesai">
                                <button type="button"
                                        onclick="confirmSelesai('{{ $row->internship_id }}', '{{ $row->user->name }}')"
                                        class="bg-green-50 text-green-600 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-tighter hover:bg-green-600 hover:text-white transition">
                                    Selesai
                                </button>
                            </form>
                            @endif

                            <button type="button"
                                    onclick="openEditModal('{{ $row->internship_id }}')"
                                    class="ml-2 text-slate-300 hover:text-blue-500 transition">
                                <i class="bi bi-pencil-square text-lg"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7"
                            class="px-8 py-20 text-center text-slate-400 italic font-medium">
                            Data tidak ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL EDIT PESERTA --}}
<div id="modalEdit"
 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[150] hidden
        flex items-start sm:items-center justify-center
        overflow-y-auto p-4">
    <div class="bg-white rounded-[2rem] sm:rounded-[2.5rem] w-full max-w-2xl p-6 sm:p-10 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-8">
            <h3 class="text-xl sm:text-2xl font-black text-slate-800">
                Edit Data Peserta
            </h3>
            <button onclick="closeEditModal()"
                    class="text-slate-400 hover:text-red-500 text-2xl transition">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="formEditPeserta" method="POST">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Nama Lengkap</label>
                    <input type="text" name="name" id="edit_name" required class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none font-bold text-sm focus:ring-4 focus:ring-blue-100">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">ID PKL</label>
                    <input type="text" name="login_id" id="edit_login_id" required class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-xl outline-none font-bold text-sm focus:ring-4 focus:ring-blue-100">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Instansi</label>
                    <select name="institution_id" id="edit_institution_id" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-sm outline-none focus:ring-4 focus:ring-blue-100">
                        @foreach(DB::table('institution')->get() as $inst)
                            <option value="{{ $inst->institution_id }}">{{ $inst->institution_name }}</option>
                        @endforeach
                    </select>
            </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Jurusan</label>
                    <select name="major_id" id="edit_major_id" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-sm outline-none focus:ring-4 focus:ring-blue-100">
                        @foreach(DB::table('major')->get() as $mjr)
                            <option value="{{ $mjr->major_id }}">{{ $mjr->major_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tgl Mulai</label>
                    <input type="date" name="start_date" id="edit_start_date" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-sm outline-none focus:ring-4 focus:ring-blue-100">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1">Tgl Selesai</label>
                    <input type="date" name="end_date" id="edit_end_date" class="w-full px-5 py-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-sm outline-none focus:ring-4 focus:ring-blue-100">
                </div>
            </div>

            <button type="submit"
                    class="w-full mt-8 bg-blue-600 text-white py-4 rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-blue-100 transition active:scale-95 hover:bg-blue-700">
                Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmSelesai(id, nama) {
        Swal.fire({
            title: 'Konfirmasi Selesai',
            text: `Apakah Anda yakin ingin menyelesaikan masa PKL ${nama}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3B82F6', 
            cancelButtonColor: '#94A3B8',
            confirmButtonText: 'Ya, Selesaikan!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-[2rem]',
                confirmButton: 'rounded-xl px-6 py-3 font-bold uppercase text-xs tracking-widest',
                cancelButton: 'rounded-xl px-6 py-3 font-bold uppercase text-xs tracking-widest'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-selesai-' + id).submit();
            }
        })
    }

    function openEditModal(id) {
        // encodeURIComponent akan mengubah 'PKL/S1/001' menjadi 'PKL%2FS1%2F001'
        const encodedId = encodeURIComponent(id);
        
        fetch(`/admin/peserta/edit/${encodedId}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                document.getElementById('edit_name').value = data.user.name;
                document.getElementById('edit_login_id').value = data.user.login_id;
                document.getElementById('edit_institution_id').value = data.institution_id;
                document.getElementById('edit_major_id').value = data.major_id;
                document.getElementById('edit_start_date').value = data.start_date;
                document.getElementById('edit_end_date').value = data.end_date;
                
                document.getElementById('formEditPeserta').action = `/admin/peserta/update/${encodedId}`;
                
                const modal = document.getElementById('modalEdit');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Tidak dapat mengambil data peserta. Pastikan koneksi stabil.',
                    customClass: { popup: 'rounded-[2rem]' }
                });
            });
    }

    function closeEditModal() {
        const modal = document.getElementById('modalEdit');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    const successMsg = "{{ session('success') }}";
    if (successMsg) {
        Swal.fire({
            title: 'Berhasil!',
            text: successMsg,
            icon: 'success',
            timer: 3000,
            showConfirmButton: false,
            customClass: { popup: 'rounded-[2rem]' }
        });
    }

    window.onclick = function(event) {
        const modal = document.getElementById('modalEdit');
        if (event.target == modal) { closeEditModal(); }
    }
</script>
@endsection
