@extends('layouts.peserta')

@section('content')
<div class="max-w-2xl mx-auto space-y-8">
    {{-- Header --}}
    <div>
        <h2 class="text-3xl font-black text-slate-800 tracking-tight">Keamanan Akun</h2>
        <p class="text-slate-400 font-bold text-sm mt-1">Perbarui kata sandi Anda secara berkala untuk menjaga keamanan data</p>
    </div>

    <div class="bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm">
        {{-- Alert Success --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 text-sm rounded-r-2xl flex items-center">
                <i class="bi bi-check-circle-fill mr-3 text-lg"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- Alert Error --}}
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm rounded-r-2xl flex items-center">
                <i class="bi bi-exclamation-triangle-fill mr-3 text-lg"></i>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('user.password.update') }}" method="POST" class="space-y-6">
            @csrf
            
            {{-- Password Lama --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase mb-2 ml-1 tracking-widest">Kata Sandi Saat Ini</label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-300 group-focus-within:text-blue-500 transition-colors">
                        <i class="bi bi-shield-lock-fill text-xl"></i>
                    </span>
                    <input type="password" name="current_password" id="current_password" required
                        class="w-full pl-12 pr-12 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all font-medium text-slate-700"
                        placeholder="Masukkan sandi lama">
                    <button type="button" onclick="togglePassword('current_password', 'eye-1')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-300 hover:text-blue-500 transition-colors">
                        <i id="eye-1" class="bi bi-eye-slash-fill text-xl"></i>
                    </button>
                </div>
            </div>

            <hr class="border-slate-50">

            {{-- Password Baru --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase mb-2 ml-1 tracking-widest">Kata Sandi Baru</label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-300 group-focus-within:text-blue-500 transition-colors">
                        <i class="bi bi-key-fill text-xl"></i>
                    </span>
                    <input type="password" name="new_password" id="new_password" required
                        class="w-full pl-12 pr-12 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all font-medium text-slate-700"
                        placeholder="Minimal 8 karakter">
                    <button type="button" onclick="togglePassword('new_password', 'eye-2')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-300 hover:text-blue-500 transition-colors">
                        <i id="eye-2" class="bi bi-eye-slash-fill text-xl"></i>
                    </button>
                </div>
                @error('new_password') <p class="text-red-500 text-[10px] mt-1 ml-1 font-bold">{{ $message }}</p> @enderror
            </div>

            {{-- Konfirmasi Password Baru --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase mb-2 ml-1 tracking-widest">Konfirmasi Kata Sandi Baru</label>
                <div class="relative group">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-300 group-focus-within:text-blue-500 transition-colors">
                        <i class="bi bi-check-circle-fill text-xl"></i>
                    </span>
                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" required
                        class="w-full pl-12 pr-12 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all font-medium text-slate-700"
                        placeholder="Ulangi sandi baru">
                    <button type="button" onclick="togglePassword('new_password_confirmation', 'eye-3')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-300 hover:text-blue-500 transition-colors">
                        <i id="eye-3" class="bi bi-eye-slash-fill text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" 
                    class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-4 rounded-2xl shadow-lg shadow-slate-200 transition-all active:scale-[0.98] tracking-widest text-xs uppercase">
                    Perbarui Kata Sandi Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Script Toggle Password --}}
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
@endsection