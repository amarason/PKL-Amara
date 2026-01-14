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
    </style>
</head>

<body class="min-h-screen">

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 bg-white border-r border-slate-100 transform -translate-x-full sidebar-transition md:translate-x-0 md:flex md:flex-col">
        <div class="p-7 flex items-center space-x-3">
            <img src="{{ asset('uploads/img/logo-plnIP.png') }}" alt="Logo PLN" class="w-[75px] h-auto object-contain">
            <span class="text-[#3B82F6] text-2xl font-extrabold tracking-tight uppercase">SIPRAKER</span>
        </div>

        <nav class="flex-grow px-4 space-y-2">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-6 py-4 rounded-2xl transition-all duration-200 {{ Request::is('admin/dashboard') ? 'text-blue-500 font-bold bg-blue-50 shadow-sm' : 'text-slate-400 hover:text-blue-500 hover:bg-slate-50' }}">
                <i class="bi bi-grid-fill mr-4"></i> Dashboard
            </a>
            <a href="{{ route('admin.peserta.create') }}" class="flex items-center px-6 py-4 rounded-2xl transition-all duration-200 {{ Request::is('admin/peserta/create') ? 'text-blue-500 font-bold bg-blue-50 shadow-sm' : 'text-slate-400 hover:text-blue-500 hover:bg-slate-50' }}">
                <i class="bi bi-person-plus-fill mr-4"></i> Tambah Peserta
            </a>
            <a href="{{ route('admin.peserta.index') }}" class="flex items-center px-6 py-4 rounded-2xl transition-all duration-200 {{ Request::is('admin/peserta') ? 'text-blue-500 font-bold bg-blue-50 shadow-sm' : 'text-slate-400 hover:text-blue-500 hover:bg-slate-50' }}">
                <i class="bi bi-people-fill mr-4"></i> Manajemen Peserta
            </a>
            <a href="{{ route('admin.absensi.index') }}" class="flex items-center px-6 py-4 rounded-2xl transition-all duration-200 {{ Request::is('admin/absensi*') ? 'text-blue-500 font-bold bg-blue-50 shadow-sm' : 'text-slate-400 hover:text-blue-500 hover:bg-slate-50' }}">
                <i class="bi bi-calendar3 mr-4"></i> Absensi Harian
            </a>
            <a href="{{ route('admin.rekap.index') }}" class="flex items-center px-6 py-4 rounded-2xl transition-all duration-200 {{ Request::is('admin/rekap*') ? 'text-blue-500 font-bold bg-blue-50 shadow-sm' : 'text-slate-400 hover:text-blue-500 hover:bg-slate-50' }}">
                <i class="bi bi-file-earmark-text-fill mr-4"></i> Rekap Laporan
            </a>
        </nav>
        
        <div class="p-6 border-t border-slate-50">
            <p class="text-[10px] text-slate-300 font-bold uppercase tracking-widest text-center">PLN IP UBP Semarang</p>
        </div>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-30 hidden md:hidden" onclick="toggleSidebar()"></div>

    <div class="flex flex-col min-h-screen md:ml-72">
        <header class="h-20 bg-white border-b border-slate-100 flex items-center justify-between px-4 md:px-12 sticky top-0 z-20">
            <button class="md:hidden text-slate-600 text-2xl" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>

            <div></div> 

            <div class="flex items-center space-x-6">
                <div class="relative">
                    <a href="{{ route('admin.absensi.index') }}" class="block p-2 rounded-xl hover:bg-slate-50 transition-all">
                        <i class="bi bi-bell text-slate-400 text-xl cursor-pointer hover:text-blue-500 transition-colors"></i>
                        <div id="notif-badge" class="hidden absolute top-1 right-1 w-5 h-5 bg-red-500 border-2 border-white rounded-full flex items-center justify-center pulse-badge">
                            <span class="text-[9px] text-white font-black" id="notif-count">0</span>
                        </div>
                    </a>
                </div>

                <div class="flex items-center border-l pl-6 border-slate-100 space-x-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-slate-600">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Administrator</p>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-10 h-10 bg-red-50 text-red-500 rounded-full flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm group">
                            <i class="bi bi-box-arrow-right group-hover:scale-110 transition-transform"></i>
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

        // --- Global SweetAlert Notifications ---
        @if(session('success'))
            Swal.fire({
                icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}",
                timer: 3000, showConfirmButton: false, customClass: { popup: 'rounded-[2rem]' }
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error', title: 'Gagal!', text: "{{ session('error') }}",
                customClass: { popup: 'rounded-[2rem]' }
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
                                text: `Ada ${data.count} permohonan izin yang menunggu persetujuan.`,
                                icon: 'info',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 5000,
                                timerProgressBar: true
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