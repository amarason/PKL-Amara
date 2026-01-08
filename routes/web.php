<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// Redirect halaman utama ke login
Route::get('/', function () { 
    return redirect('/login'); 
});

// Grup Autentikasi (Guest)
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout');
});

// Grup Rute Terproteksi Login (Auth)
Route::middleware(['auth'])->group(function () {
    
    // --- RUTE KHUSUS ADMIN ---
    Route::middleware(['role:ROLE_ADMIN'])->prefix('admin')->group(function () {
        
        // Dashboard Utama (Overview)
        Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    
        // Manajemen Peserta (Daftar Aktif & Selesai)
        Route::get('/peserta/manajemen', [AdminController::class, 'indexPeserta'])->name('admin.peserta.index');
        
        // Form Tambah Peserta Baru
        Route::get('/peserta/tambah', [AdminController::class, 'create'])->name('admin.peserta.create');
        
        // Logic Simpan Peserta (Users & Internship)
        Route::post('/peserta/simpan', [AdminController::class, 'store'])->name('admin.peserta.store');

        // --- RUTE EDIT & UPDATE PESERTA (BARU) ---
        // Digunakan untuk mengambil data via AJAX (Edit) dan menyimpan perubahan (Update)
        Route::get('/peserta/edit/{id}', [AdminController::class, 'editPeserta'])->name('admin.peserta.edit');
        Route::post('/peserta/update/{id}', [AdminController::class, 'updatePeserta'])->name('admin.peserta.update');

        // Logic Tambah Cepat (AJAX untuk Instansi & Jurusan) 
        Route::post('/institution/quick-store', [AdminController::class, 'storeInstitution'])->name('admin.institution.store');
        Route::post('/major/quick-store', [AdminController::class, 'storeMajor'])->name('admin.major.store');

        // Fitur Update Status (Aktif ke Selesai)
        Route::post('/peserta/update-status/{id}', [AdminController::class, 'updateStatus'])->name('admin.peserta.updateStatus');

        // Absensi & Perizinan
        Route::get('/absensi', [AdminController::class, 'indexAbsensi'])->name('admin.absensi.index');
        Route::post('/izin/verifikasi/{id}', [AdminController::class, 'verifyLeave'])->name('admin.izin.verify');
    });

    // --- RUTE KHUSUS PESERTA PKL ---
    Route::middleware(['role:ROLE_PESERTA'])->group(function () {
        Route::get('/dashboard', function () { 
            return "Dashboard Peserta PKL (Halaman Absensi)"; 
        })->name('user.dashboard');
    });
});