<!DOCTYPE html>
<html lang="en">
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
    </style>
</head>

<body class="min-h-screen">
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 bg-white border-r border-slate-100 transform -translate-x-full sidebar-transition md:translate-x-0 md:flex md:flex-col">
        <div class="p-7 flex items-center space-x-3">
            <img src="{{ asset('uploads/img/logo-plnIP.png') }}" alt="Logo PLN" class="w-[75px] h-auto object-contain">
            <span class="text-[#3B82F6] text-2xl font-extrabold tracking-tight">SIPRAKER</span>
        </div>

        <nav class="flex-grow px-4 space-y-2">
            <a href="{{ route('user.dashboard') }}" class="flex items-center px-6 py-4 rounded-2xl transition-all duration-200 {{ Request::is('user/dashboard') ? 'text-blue-500 font-bold bg-blue-50 shadow-sm' : 'text-slate-400 hover:text-blue-500 hover:bg-slate-50' }}">
                <i class="bi bi-grid-fill mr-4"></i> Dashboard
            </a>
            <a href="{{ route('user.absensi.index') }}" class="flex items-center px-6 py-4 rounded-2xl transition-all duration-200 {{ Request::is('user/absensi*') ? 'text-blue-500 font-bold bg-blue-50 shadow-sm' : 'text-slate-400 hover:text-blue-500 hover:bg-slate-50' }}">
                <i class="bi bi-camera-fill mr-4"></i> Absensi Harian
            </a>
            <a href="{{ route('user.izin.index') }}" class="flex items-center px-6 py-4 rounded-2xl transition-all duration-200 {{ Request::is('user/izin*') ? 'text-blue-500 font-bold bg-blue-50 shadow-sm' : 'text-slate-400 hover:text-blue-500 hover:bg-slate-50' }}">
                <i class="bi bi-envelope-paper-fill mr-4"></i> Pengajuan Izin
            </a>
            <a href="{{ route('user.rekap.index') }}" class="flex items-center px-6 py-4 rounded-2xl transition-all duration-200 {{ Request::is('user/rekap*') ? 'text-blue-500 font-bold bg-blue-50 shadow-sm' : 'text-slate-400 hover:text-blue-500 hover:bg-slate-50' }}">
                <i class="bi bi-file-earmark-text-fill mr-4"></i> Rekap Absensi
            </a>
        </nav>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-30 hidden md:hidden" onclick="toggleSidebar()"></div>

    <div class="flex flex-col min-h-screen md:ml-72">
        <header class="h-20 bg-white border-b border-slate-100 flex items-center justify-between px-4 md:px-12 sticky top-0 z-20">
            <button class="md:hidden text-slate-600 text-2xl" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>

            <div></div> <div class="flex items-center space-x-6">
                <div class="flex items-center border-l pl-6 border-slate-100 space-x-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-slate-600">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-blue-500 font-bold uppercase tracking-widest">Peserta PKL</p>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-10 h-10 bg-red-50 text-red-500 rounded-full flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm" title="Logout">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-grow p-4 sm:p-6 md:p-12">
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
                sidebar.classList.add('md:translate-x-0');
                overlay.classList.add('hidden');
            }
        });
    </script>
</body>
</html>