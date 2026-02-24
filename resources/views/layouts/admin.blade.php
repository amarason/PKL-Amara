<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>SIPRAKER - Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #F8FAFC; }
        .pulse-badge { animation: pulse-red 2s infinite; }
        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        .sidebar-transition { transition: transform 0.3s ease-in-out; }
        
        /* Custom Scrollbar untuk Sidebar jika konten panjang */
        aside::-webkit-scrollbar { width: 4px; }
        aside::-webkit-scrollbar-thumb { background-color: #e2e8f0; border-radius: 4px; }
    </style>
</head>

<body class="min-h-screen">

    {{-- SIDEBAR --}}
    {{-- Ubah border-r menjadi shadow-xl untuk kesan melayang (modern) --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 bg-white shadow-xl shadow-blue-900/5 transform -translate-x-full sidebar-transition md:translate-x-0 md:flex md:flex-col">
        
        {{-- Header Sidebar: Tambah gradient halus --}}
        <div class="p-7 flex items-center space-x-3 bg-gradient-to-b from-blue-50/50 to-transparent">
            <img src="{{ asset('uploads/img/logo-plnIP.png') }}" alt="Logo PLN" class="w-[75px] h-auto object-contain drop-shadow-sm">
            <div>
                <span class="block text-[#3B82F6] text-2xl font-extrabold tracking-tight uppercase leading-none">SIPRAKER</span>
                {{-- <span class="text-[10px] text-slate-400 font-medium tracking-wider">Admin Panel</span> --}}
            </div>
        </div>

        {{-- Navigasi --}}
        <nav class="flex-grow px-4 space-y-2 mt-2">
            
            {{-- ITEM: DASHBOARD --}}
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center px-6 py-4 rounded-2xl transition-all duration-300 group
               {{ request()->routeIs('admin.dashboard') 
                  ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/30 translate-x-1' 
                  : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1' }}">
                <i class="bi bi-grid-fill mr-4 text-lg {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                <span class="font-medium">Dashboard</span>
            </a>

            {{-- ITEM: TAMBAH PESERTA --}}
            <a href="{{ route('admin.peserta.create') }}" 
               class="flex items-center px-6 py-4 rounded-2xl transition-all duration-300 group
               {{ request()->routeIs('admin.peserta.create') 
                  ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/30 translate-x-1' 
                  : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1' }}">
                <i class="bi bi-person-plus-fill mr-4 text-lg {{ request()->routeIs('admin.peserta.create') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                <span class="font-medium">Tambah Peserta</span>
            </a>

            {{-- ITEM: MANAJEMEN PESERTA --}}
            <a href="{{ route('admin.peserta.index') }}" 
               class="flex items-center px-6 py-4 rounded-2xl transition-all duration-300 group
               {{ request()->routeIs('admin.peserta.index', 'admin.peserta.edit') 
                  ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/30 translate-x-1' 
                  : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1' }}">
                <i class="bi bi-people-fill mr-4 text-lg {{ request()->routeIs('admin.peserta.index', 'admin.peserta.edit') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                <span class="font-medium">Manajemen Peserta</span>
            </a>

            {{-- ITEM: ABSENSI HARIAN --}}
            <a href="{{ route('admin.absensi.index') }}" 
               class="flex items-center px-6 py-4 rounded-2xl transition-all duration-300 group
               {{ request()->routeIs('admin.absensi.*') 
                  ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/30 translate-x-1' 
                  : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1' }}">
                <i class="bi bi-calendar3 mr-4 text-lg {{ request()->routeIs('admin.absensi.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                <span class="font-medium">Absensi Harian</span>
            </a>

            {{-- ITEM: REKAP LAPORAN --}}
            <a href="{{ route('admin.rekap.index') }}" 
               class="flex items-center px-6 py-4 rounded-2xl transition-all duration-300 group
               {{ request()->routeIs('admin.rekap.*') 
                  ? 'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-lg shadow-blue-500/30 translate-x-1' 
                  : 'text-slate-500 hover:bg-blue-50 hover:text-blue-600 hover:translate-x-1' }}">
                <i class="bi bi-file-earmark-text-fill mr-4 text-lg {{ request()->routeIs('admin.rekap.*') ? 'text-white' : 'text-slate-400 group-hover:text-blue-500' }}"></i>
                <span class="font-medium">Rekap Laporan</span>
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
        <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-100 flex items-center justify-between px-4 md:px-12 sticky top-0 z-20 transition-all duration-300">
            <button class="md:hidden text-slate-600 text-2xl hover:text-blue-600 transition" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>

            <div>
                {{-- Breadcrumb simpel atau judul halaman bisa ditaruh sini --}}
                <h1 class="hidden md:block text-lg font-bold text-slate-700">
                    @if(request()->routeIs('admin.dashboard')) Dashboard Overview
                    @elseif(request()->routeIs('admin.peserta.create')) Tambah Peserta Baru
                    @elseif(request()->routeIs('admin.peserta.*')) Data Peserta
                    @elseif(request()->routeIs('admin.absensi.*')) Monitoring Absensi
                    @elseif(request()->routeIs('admin.rekap.*')) Laporan & Rekap
                    @endif
                </h1>
            </div>

            <div class="flex items-center space-x-6">
                <div class="relative">
                    <a href="{{ route('admin.absensi.index') }}" class="block p-2 rounded-xl hover:bg-blue-50 transition-all group">
                        <i class="bi bi-bell-fill text-slate-300 text-xl cursor-pointer group-hover:text-blue-500 transition-colors"></i>
                        <div id="notif-badge" class="hidden absolute top-1.5 right-1.5 w-4 h-4 bg-red-500 border-2 border-white rounded-full flex items-center justify-center pulse-badge">
                            <span class="text-[8px] text-white font-black" id="notif-count">0</span>
                        </div>
                    </a>
                </div>

                <div class="flex items-center border-l pl-6 border-slate-100 space-x-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-slate-700">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-blue-500 font-bold uppercase tracking-widest">Administrator</p>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-10 h-10 bg-red-50 text-red-500 rounded-xl flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm group border border-red-100">
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

        // --- Global SweetAlert Notifications ---
        @if(session('success'))
            Swal.fire({
                icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}",
                showConfirmButton: true, confirmButtonColor: '#3B82F6', confirmButtonText: 'Oke', customClass: { popup: 'rounded-[2rem]' }
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error', title: 'Gagal!', text: "{{ session('error') }}",
                showConfirmButton: true, confirmButtonColor: '#EF4444', confirmButtonText: 'Tutup', customClass: { popup: 'rounded-[2rem]' }
            });
        @endif

        let lastCount = 0;

        function fetchNotifications() {
            fetch("{{ route('admin.notification.check') }}")
                .then(response => {
                    if (!response.ok) throw new Error('Not found');
                    return response.json();
                })
                .then(data => {
                    const badge = document.getElementById('notif-badge');
                    const countSpan = document.getElementById('notif-count');
                    
                    if (data.count > 0) {
                        badge.classList.remove('hidden');
                        countSpan.innerText = data.count > 9 ? '9+' : data.count;

                        if (data.count > lastCount) {
                            Swal.fire({
                                title: 'Izin Masuk!',
                                text: `Ada ${data.count} permohonan izin baru.`,
                                icon: 'info',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 5000,
                                timerProgressBar: true,
                                background: '#EFF6FF',
                                color: '#1E40AF'
                            });
                        }
                    } else {
                        badge.classList.add('hidden');
                    }
                    lastCount = data.count;
                })
                .catch(err => console.log('Info: Fitur notifikasi nonaktif.'));
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetchNotifications();
            setInterval(fetchNotifications, 30000); 
        });
    </script>
</body>
</html>