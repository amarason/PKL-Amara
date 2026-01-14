<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SIPRAKER PLN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .pln-blue { color: #00A2E9; }
        .bg-pln-blue { background-color: #00A2E9; }
    </style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md">
        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-blue-900/10 overflow-hidden border border-slate-100">
            
            <div class="pt-12 pb-6 flex flex-col items-center">
                <img src="{{ asset('uploads/img/logo-plnIP.png') }}" alt="Logo PLN" class="h-24 w-auto mb-4 object-contain">
                <h1 class="text-3xl font-extrabold pln-blue tracking-tight">SIPRAKER</h1>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-2">Sistem Presensi Praktik Kerja</p>
            </div>

            <div class="p-10 pt-4">
                @if($errors->has('login_id'))
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-xs flex items-center rounded-r-2xl animate-pulse">
                        <i class="bi bi-exclamation-circle-fill mr-3 text-lg"></i>
                        {{ $errors->first('login_id') }}
                    </div>
                @endif

                <form action="{{ url('/login') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    {{-- Input ID Identitas --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase mb-2 ml-1 tracking-wider">ID Identitas</label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-300 group-focus-within:pln-blue transition-colors">
                                <i class="bi bi-person-vcard-fill text-xl"></i>
                            </span>
                            <input type="text" name="login_id" value="{{ old('login_id') }}" required autofocus
                                class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-[#00A2E9] outline-none transition-all font-medium text-slate-700 placeholder:text-slate-300"
                                placeholder="Masukkan ID Identitas">
                        </div>
                    </div>

                    {{-- Input Kata Sandi dengan Toggle Password --}}
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase mb-2 ml-1 tracking-wider">Kata Sandi</label>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-300 group-focus-within:pln-blue transition-colors">
                                <i class="bi bi-key-fill text-xl"></i>
                            </span>
                            <input type="password" name="password" id="password" required
                                class="w-full pl-12 pr-12 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-[#00A2E9] outline-none transition-all font-medium text-slate-700 placeholder:text-slate-300"
                                placeholder="••••••••">
                            
                            <button type="button" onclick="togglePassword('password', 'eye-icon')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-300 hover:text-slate-500 transition-colors">
                                <i id="eye-icon" class="bi bi-eye-slash-fill text-xl"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" 
                            class="w-full bg-pln-blue hover:bg-[#008bc8] text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-[0.97] flex items-center justify-center tracking-widest text-sm">
                            MASUK SEKARANG <i class="bi bi-arrow-right-short text-2xl ml-1"></i>
                        </button>
                    </div>
                </form>

                <div class="mt-12 text-center">
                    <p class="text-[10px] text-slate-300 font-medium leading-relaxed italic">
                        Gunakan ID PKL sebagai password default pada login pertama kali. Pastikan segera mengganti password Anda. 
                    </p>
                </div>
            </div>
        </div>
        
        <p class="text-center mt-10 text-slate-400 text-[10px] font-semibold tracking-widest uppercase">
            &copy; 2026 PT PLN INDONESIA POWER UBP Semarang.
        </p>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('bi-eye-slash-fill');
                eyeIcon.classList.add('bi-eye-fill');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('bi-eye-fill');
                eyeIcon.classList.add('bi-eye-slash-fill');
            }
        }
    </script>

</body>
</html>