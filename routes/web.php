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

        // --- PERBAIKAN RUTE EDIT & UPDATE (Support ID dengan /) ---
        // Penambahan ->where('id', '.*') memungkinkan Laravel menerima ID seperti PKL/S1/001
        Route::get('/peserta/edit/{id}', [AdminController::class, 'editPeserta'])
            ->name('admin.peserta.edit')
            ->where('id', '.*');
            
        Route::post('/peserta/update/{id}', [AdminController::class, 'updatePeserta'])
            ->name('admin.peserta.update')
            ->where('id', '.*');

        // --- LOGIC UPDATE STATUS (Support ID dengan /) ---
        Route::post('/peserta/update-status/{id}', [AdminController::class, 'updateStatus'])
            ->name('admin.peserta.updateStatus')
            ->where('id', '.*');

        // Logic Tambah Cepat (AJAX untuk Instansi & Jurusan) 
        Route::post('/institution/quick-store', [AdminController::class, 'storeInstitution'])->name('admin.institution.store');
        Route::post('/major/quick-store', [AdminController::class, 'storeMajor'])->name('admin.major.store');

        // Absensi & Perizinan
        Route::get('/absensi', [AdminController::class, 'indexAbsensi'])->name('admin.absensi.index');
        Route::post('/izin/verifikasi/{id}', [AdminController::class, 'verifyLeave'])->name('admin.izin.verify');
        Route::post('/absensi/update-status', [AdminController::class, 'updateAttendanceStatus'])->name('admin.absensi.updateStatus');
        
        // Notifikasi (Disesuaikan jalurnya agar konsisten)
        Route::get('/check-notifications', [AdminController::class, 'checkNotification'])->name('admin.notification.check');

        // Rekap Absensi Bulanan
        Route::get('/rekap-absensi', [AdminController::class, 'indexRekap'])->name('admin.rekap.index');
        Route::get('/rekap-absensi/export-pdf', [AdminController::class, 'exportRekapPdf'])->name('admin.rekap.pdf');
    });

    // --- RUTE VERIFIKASI PUBLIK (Bisa diakses tanpa login agar bisa scan QR) ---
    // Dipindah ke luar middleware auth atau biarkan di sini jika hanya admin yang boleh scan
    // Jika ingin dosen/sekolah bisa scan tanpa login, pindahkan ke paling bawah (luar grup auth)
    Route::get('/verifikasi/laporan/{hash}', [AdminController::class, 'verifyReport'])->name('report.verify');

    // --- RUTE KHUSUS PESERTA PKL ---
    Route::middleware(['role:ROLE_PESERTA'])->group(function () {
        Route::get('/peserta/dashboard', function () { 
            return "Dashboard Peserta PKL (Halaman Absensi)"; 
        })->name('user.dashboard');
    });
});