<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>SIPRAKER - Peserta</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #F8FAFC; }
        .sidebar-transition { transition: transform 0.3s ease-in-out; }
        aside::-webkit-scrollbar { width: 4px; }
        aside::-webkit-scrollbar-thumb { background-color: #e2e8f0; border-radius: 4px; }
    </style>
</head>

<body class="min-h-screen text-slate-600">

    {{-- SIDEBAR --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 bg-white shadow-xl shadow-blue-900/5 transform -translate-x-full sidebar-transition md:translate-x-0 md:flex md:flex-col">
        
        {{-- Header Sidebar --}}
        <div class="p-7 flex items-center space-x-3 bg-gradient-to-b from-blue-50/50 to-transparent">
            <img src="{{ asset('uploads/img/logo-plnIP.png') }}" alt="Logo PLN" class="w-[75px] h-auto object-contain drop-shadow-sm">
            <div>
                <span class="block text-[#3B82F6] text-2xl font-extrabold tracking-tight uppercase leading-none">SIPRAKER</span>
                <span class="text-[10px] text-slate-400 font-medium tracking-wider">Peserta Magang</span>
            </div>
        </div>

        {{-- Navigasi Menu --}}
        <nav class="flex-grow px-4 space-y-2 mt-2">
            
            <a href="{{ route('user.dashboard') }}" 
               class="flex items-center px-6 py-4 rounded-2xl transition-all duration-300 group
               {{ Request::is('user/dashboard') 
                  ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/30 translate-x-1' 
                  : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1' }}">
                <i class="bi bi-grid-fill mr-4 text-lg {{ Request::is('user/dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                <span class="font-medium">Dashboard</span>
            </a>

            <a href="{{ route('user.absensi.index') }}" 
               class="flex items-center px-6 py-4 rounded-2xl transition-all duration-300 group
               {{ Request::is('user/absensi*') 
                  ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/30 translate-x-1' 
                  : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1' }}">
                <i class="bi bi-camera-fill mr-4 text-lg {{ Request::is('user/absensi*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                <span class="font-medium">Absensi Harian</span>
            </a>

            <a href="{{ route('user.izin.index') }}" 
               class="flex items-center px-6 py-4 rounded-2xl transition-all duration-300 group
               {{ Request::is('user/izin*') 
                  ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/30 translate-x-1' 
                  : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1' }}">
                <i class="bi bi-envelope-paper-fill mr-4 text-lg {{ Request::is('user/izin*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                <span class="font-medium">Pengajuan Izin</span>
            </a>

            <a href="{{ route('user.rekap.index') }}" 
               class="flex items-center px-6 py-4 rounded-2xl transition-all duration-300 group
               {{ Request::is('user/rekap*') 
                  ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/30 translate-x-1' 
                  : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1' }}">
                <i class="bi bi-file-earmark-text-fill mr-4 text-lg {{ Request::is('user/rekap*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                <span class="font-medium">Rekap Absensi</span>
            </a>

            <a href="{{ route('user.settings') }}" 
               class="flex items-center px-6 py-4 rounded-2xl transition-all duration-300 group
               {{ Request::is('user/settings') 
                  ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/30 translate-x-1' 
                  : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1' }}">
                <i class="bi bi-gear-fill mr-4 text-lg {{ Request::is('user/settings') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                <span class="font-medium">Update Password</span>
            </a>

        </nav>

        {{-- Footer Sidebar --}}
        <div class="p-6 mt-auto">
            <div class="bg-blue-50 rounded-2xl p-4 text-center border border-blue-100">
                <p class="text-[10px] text-blue-400 font-bold uppercase tracking-widest mb-1">PLN IP UBP Semarang</p>
                <p class="text-[9px] text-slate-400">© {{ date('Y') }} SIPRAKER</p>
            </div>
        </div>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 hidden md:hidden" onclick="toggleSidebar()"></div>

    <div class="flex flex-col min-h-screen md:ml-72">
        <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-100 flex items-center justify-between px-4 md:px-12 sticky top-0 z-20 transition-all">
            <button class="md:hidden text-slate-600 text-2xl hover:text-blue-600 transition" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>

            <div>
                <h1 class="hidden md:block text-lg font-bold text-slate-700">
                    @if(Request::is('user/dashboard')) Halaman Utama
                    @elseif(Request::is('user/absensi*')) Form Absensi
                    @elseif(Request::is('user/izin*')) Permohonan Izin
                    @elseif(Request::is('user/rekap*')) Riwayat Kehadiran
                    @elseif(Request::is('user/settings')) Update Password
                    @endif
                </h1>
            </div> 

            <div class="flex items-center space-x-6">
                <div class="flex items-center border-l pl-6 border-slate-100 space-x-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-slate-700">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-blue-500 font-bold uppercase tracking-widest">Peserta Magang</p>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-10 h-10 bg-red-50 text-red-500 rounded-xl flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm group border border-red-100" title="Keluar Aplikasi">
                            <i class="bi bi-box-arrow-right group-hover:translate-x-0.5 transition-transform"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-grow p-4 sm:p-6 md:p-8 lg:p-12">
            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                sidebar.classList.remove('-translate-x-full'); 
                overlay.classList.add('hidden');
            }
        });

        // --- SweetAlert Notifications ---
        @if(session('success'))
            Swal.fire({
                icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}",
                showConfirmButton: false, timer: 2000, customClass: { popup: 'rounded-[2rem]' }
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error', title: 'Gagal!', text: "{{ session('error') }}",
                showConfirmButton: true, confirmButtonColor: '#EF4444', confirmButtonText: 'Tutup', customClass: { popup: 'rounded-[2rem]' }
            });
        @endif

        @if(session('warning'))
            Swal.fire({
                icon: 'warning', title: 'Perhatian!', text: "{{ session('warning') }}",
                showConfirmButton: true, confirmButtonColor: '#F59E0B', confirmButtonText: 'Oke', customClass: { popup: 'rounded-[2rem]' }
            });
        @endif
    </script>
</body>
</html>