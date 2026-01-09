<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPRAKER - Admin</title>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F8FAFC;
        }
    </style>
</head>

<body class="min-h-screen">

    <!-- ================= SIDEBAR ================= -->
    <aside
        id="sidebar"
        class="fixed inset-y-0 left-0 z-40 w-72 bg-white border-r border-slate-100
               transform -translate-x-full transition-transform duration-300
               md:translate-x-0 md:flex md:flex-col">

        <div class="p-7 flex items-center space-x-3">
            <img src="{{ asset('uploads/img/logo-pln.png') }}" 
                alt="Logo PLN" 
                class="w-[75px] h-auto object-contain">
            
            <span class="text-[#3B82F6] text-2xl font-extrabold tracking-tight">
                SIPRAKER
            </span>
        </div>

        <nav class="flex-grow px-4 space-y-2">
            <a href="{{ route('admin.dashboard') }}"
            class="flex items-center px-6 py-4 rounded-2xl transition-all duration-200
            {{ Request::is('admin/dashboard') ? 'text-blue-500 font-bold bg-blue-50' : 'text-slate-400 hover:text-blue-500 hover:bg-slate-50' }}">
                <i class="bi bi-grid-fill mr-4"></i> Dashboard
            </a>

            <a href="{{ route('admin.peserta.create') }}"
            class="flex items-center px-6 py-4 rounded-2xl transition-all duration-200
            {{ Request::is('admin/peserta/create') ? 'text-blue-500 font-bold bg-blue-50' : 'text-slate-400 hover:text-blue-500 hover:bg-slate-50' }}">
                <i class="bi bi-person-plus mr-4"></i> Tambah Peserta
            </a>

            <a href="{{ route('admin.peserta.index') }}"
            class="flex items-center px-6 py-4 rounded-2xl transition-all duration-200
            {{ Request::is('admin/peserta') ? 'text-blue-500 font-bold bg-blue-50' : 'text-slate-400 hover:text-blue-500 hover:bg-slate-50' }}">
                <i class="bi bi-people mr-4"></i> Manajemen Peserta
            </a>

            <a href="{{ route('admin.absensi.index') }}"
            class="flex items-center px-6 py-4 rounded-2xl transition-all duration-200
            {{ Request::is('admin/absensi*') ? 'text-blue-500 font-bold bg-blue-50' : 'text-slate-400 hover:text-blue-500 hover:bg-slate-50' }}">
                <i class="bi bi-calendar3 mr-4"></i> Absensi Harian
            </a>

            <a href="{{ route('admin.rekap.index') }}"
            class="flex items-center px-6 py-4 rounded-2xl transition-all duration-200
            {{ Request::is('admin/rekap*') ? 'text-blue-500 font-bold bg-blue-50' : 'text-slate-400 hover:text-blue-500 hover:bg-slate-50' }}">
                <i class="bi bi-file-earmark-text mr-4"></i> Rekap Laporan
            </a>
        </nav>
    </aside>

    <!-- ================= OVERLAY ================= -->
    <div
        id="sidebar-overlay"
        class="fixed inset-0 bg-black/40 z-30 hidden md:hidden"
        onclick="toggleSidebar()">
    </div>

    <!-- ================= MAIN WRAPPER ================= -->
    <div class="flex flex-col min-h-screen md:ml-72">

        <!-- ================= HEADER ================= -->
        <header class="h-20 bg-white border-b border-slate-100
                       flex items-center justify-between
                       px-4 md:px-12 sticky top-0 z-20">

            <!-- Hamburger (Mobile) -->
            <button
                class="md:hidden text-slate-600 text-2xl"
                onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>

            <div></div>

            <!-- User Info -->
            <div class="flex items-center space-x-6">
                <i class="bi bi-bell text-slate-400 text-xl cursor-pointer"></i>

                <div class="flex items-center border-l pl-6 border-slate-100 space-x-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-slate-600">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-slate-400">Administrator</p>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button
                            class="w-10 h-10 bg-red-50 text-red-500 rounded-full
                                   flex items-center justify-center
                                   hover:bg-red-500 hover:text-white transition">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- ================= CONTENT ================= -->
        <main class="flex-grow p-4 sm:p-6 md:p-12">
            @yield('content')
        </main>

    </div>

    <!-- ================= SCRIPT ================= -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>

</body>
</html>
