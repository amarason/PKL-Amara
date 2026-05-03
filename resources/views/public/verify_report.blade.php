<div class="min-h-screen bg-slate-50 flex items-center justify-center p-6 font-sans">
    <div class="max-w-md w-full bg-white rounded-[2.5rem] shadow-2xl border-t-[10px] border-green-500 p-8 text-center">
        <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl">
            <i class="bi bi-patch-check-fill"></i>
        </div>

        <h1 class="text-2xl font-black text-slate-800 mb-2">Dokumen Terverifikasi</h1>
        <p class="text-slate-400 text-sm mb-8 font-medium">Sistem Informasi Praktik Kerja (SIPRAKER) menyatakan bahwa dokumen ini adalah <strong>ASLI</strong>.</p>

        <div class="space-y-4 text-left bg-slate-50 p-6 rounded-3xl border border-slate-100">
            <div>
                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Nama Peserta</p>
                <p class="font-bold text-slate-800">{{ $nama }}</p>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Asal Instansi</p>
                <p class="font-bold text-slate-800">{{ $instansi }}</p>
            </div>
            <div>
                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Periode Magang</p>
                <p class="font-bold text-slate-800">{{ date('d M Y', strtotime($periode_mulai)) }} - {{ date('d M Y', strtotime($periode_selesai)) }}</p>
            </div>
            <div class="pt-4 border-t border-slate-200">
                <p class="text-[10px] font-black uppercase text-green-600 tracking-widest">Status Laporan</p>
                <p class="font-bold text-slate-800">Valid (Bulan {{ $laporan_bulan }} {{ $laporan_tahun }})</p>
            </div>
        </div>

        <p class="mt-8 text-[10px] text-slate-300 font-bold uppercase tracking-tight italic">
            Diverifikasi secara digital pada {{ $verified_at }}
        </p>
    </div>
</div>