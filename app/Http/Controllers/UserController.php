<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Internship;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * DASHBOARD PESERTA
     */
    public function index()
    {
        $user = Auth::user();
        // Mengambil data internship berdasarkan user yang login
        $internship = Internship::where('user_id', $user->id)->first();

        if (!$internship) {
            return "Data Internship tidak ditemukan. Pastikan Seeder sudah dijalankan.";
        }

        // Hitung statistik kehadiran
        $totalHadir = Attendance::where('internship_id', $internship->internship_id)
            ->where('status', 'hadir')
            ->count();

        // Cek status absensi hari ini
        $absensiHariIni = Attendance::where('internship_id', $internship->internship_id)
            ->whereDate('attendance_date', \Carbon\Carbon::today())
            ->first();

        // Kirim variabel ke view
        return view('user.dashboard', compact('internship', 'totalHadir', 'absensiHariIni'));
    }

    /**
     * HALAMAN ABSENSI KAMERA
     */
    public function indexAbsensi()
    {
        $internship = Internship::where('user_id', Auth::id())->first();
        $attendance = Attendance::where('internship_id', $internship->internship_id)
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        return view('user.absensi_kamera', compact('attendance'));
    }

    /**
     * PROSES SIMPAN ABSEN MASUK
     */
    public function storeMasuk(Request $request)
    {
        $request->validate(['photo' => 'required']); // Photo dikirim dalam format Base64
        
        $internship = Internship::where('user_id', Auth::id())->first();
        $image = $request->photo; 
        
        // Dekode Base64 Image
        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'in_' . $internship->internship_id . '_' . time() . '.jpg';
        
        // Simpan ke public/uploads/attendance
        Storage::disk('public_uploads')->put('attendance/' . $imageName, base64_decode($image));

        Attendance::create([
            'attendance_id' => 'ATT-' . strtoupper(Str::random(7)),
            'internship_id' => $internship->internship_id,
            'attendance_date' => Carbon::today(),
            'check_in_time' => Carbon::now()->toTimeString(),
            'check_in_photo' => 'uploads/attendance/' . $imageName,
            'status' => 'hadir'
        ]);

        return response()->json(['success' => 'Berhasil absen masuk!']);
    }

    /**
     * PROSES SIMPAN ABSEN PULANG
     */
    public function storePulang(Request $request)
    {
        $request->validate(['photo' => 'required']);
        
        $internship = Internship::where('user_id', Auth::id())->first();
        $attendance = Attendance::where('internship_id', $internship->internship_id)
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        if (!$attendance) return response()->json(['error' => 'Anda belum absen masuk!'], 400);

        $image = $request->photo;
        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'out_' . $internship->internship_id . '_' . time() . '.jpg';
        
        Storage::disk('public_uploads')->put('attendance/' . $imageName, base64_decode($image));

        $attendance->update([
            'check_out_time' => Carbon::now()->toTimeString(),
            'check_out_photo' => 'uploads/attendance/' . $imageName,
        ]);

        return response()->json(['success' => 'Berhasil absen pulang, hati-hati di jalan!']);
    }
}

