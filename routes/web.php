<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController; 
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
    Route::get('/settings', [UserController::class, 'settings'])->name('user.settings');
    Route::post('/settings/password', [UserController::class, 'updatePassword'])->name('user.password.update');
    
    // --- RUTE VERIFIKASI PUBLIK (Bisa diakses tanpa login agar instansi luar bisa scan QR) ---
    Route::get('/verifikasi/laporan/{hash}', [AdminController::class, 'verifyReport'])->name('report.verify');

    // --- 1. Grup rute khusus admin ---
    Route::middleware(['role:ROLE_ADMIN'])->prefix('admin')->group(function () {
        // Dashboard Admin
        Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
        Route::get('/peserta/manajemen', [AdminController::class, 'indexPeserta'])->name('admin.peserta.index');
        Route::get('/peserta/tambah', [AdminController::class, 'create'])->name('admin.peserta.create');
        Route::post('/peserta/simpan', [AdminController::class, 'store'])->name('admin.peserta.store');

        // Fitur Manajemen Peserta
        Route::get('/peserta/edit/{id}', [AdminController::class, 'editPeserta'])->name('admin.peserta.edit')->where('id', '.*');
        Route::post('/peserta/update/{id}', [AdminController::class, 'updatePeserta'])->name('admin.peserta.update')->where('id', '.*');
        Route::post('/peserta/update-status/{id}', [AdminController::class, 'updateStatus'])->name('admin.peserta.updateStatus')->where('id', '.*');

        // Fitur Manajemen Instansi & Jurusan
        Route::post('/institution/quick-store', [AdminController::class, 'storeInstitution'])->name('admin.institution.store');
        Route::post('/major/quick-store', [AdminController::class, 'storeMajor'])->name('admin.major.store');

        // Fitur Manajemen Izin & Absensi
        Route::get('/absensi', [AdminController::class, 'indexAbsensi'])->name('admin.absensi.index');
        Route::post('/izin/verifikasi/{id}', [AdminController::class, 'verifyLeave'])->name('admin.izin.verify');
        Route::post('/absensi/update-status', [AdminController::class, 'updateAttendanceStatus'])->name('admin.absensi.updateStatus');
        Route::get('/check-notifications', [AdminController::class, 'checkNotification'])->name('admin.notification.check');

        // Fitur Rekap & Download Laporan
        Route::get('/rekap-absensi', [AdminController::class, 'indexRekap'])->name('admin.rekap.index');
        Route::get('/rekap-absensi/export-pdf', [AdminController::class, 'exportRekapPdf'])->name('admin.rekap.pdf');
    });

    // --- 2. Grup rute khusus peserta PKL ---
    Route::middleware(['auth', 'role:ROLE_PESERTA'])->prefix('user')->group(function () {
        // Dashboard Peserta
        Route::get('/dashboard', [UserController::class, 'index'])->name('user.dashboard');

        // Fitur Absensi (Kamera)
        Route::get('/absensi', [UserController::class, 'indexAbsensi'])->name('user.absensi.index');
        Route::post('/absensi/masuk', [UserController::class, 'storeMasuk'])->name('user.absensi.masuk');
        Route::post('/absensi/pulang', [UserController::class, 'storePulang'])->name('user.absensi.pulang');

        // Fitur Pengajuan Izin
        Route::get('/izin', [UserController::class, 'indexIzin'])->name('user.izin.index');
        Route::post('/izin/store', [UserController::class, 'storeIzin'])->name('user.izin.store');
        Route::delete('/izin/{id}', [UserController::class, 'destroyIzin'])->name('user.izin.destroy');

        // Fitur Rekap & Download Laporan 
        Route::get('/rekap', [UserController::class, 'indexRekap'])->name('user.rekap.index');
        Route::get('/rekap/export-pdf', [UserController::class, 'exportRekapPdf'])->name('user.rekap.pdf');
    });
});