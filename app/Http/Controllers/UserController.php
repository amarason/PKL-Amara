<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Internship;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UserController extends Controller
{
    // --- Halaman ganti password ---
    public function settings()
    {
        return view('user.settings');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ], [
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'new_password.min' => 'Password baru minimal harus 8 karakter.',
        ]);

        // Mengambil user ID saat ini
        $userId = Auth::id();
        $user = \App\Models\User::find($userId);

        // Cek apakah password lama benar
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Password lama yang Anda masukkan salah.');
        }

        // Update Password menggunakan Eloquent Model
        $user->password = Hash::make($request->new_password);
        $user->save(); 

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    // --- 1. Dashboard ---
    public function index()
    {
        $user = Auth::user();
        $internship = Internship::with('user')->where('user_id', $user->id)->first();
        if (!$internship) return "Data tidak ditemukan.";

        $totalHadir = Attendance::where('internship_id', $internship->internship_id)->where('status', 'hadir')->count();
        $totalIzin = Attendance::where('internship_id', $internship->internship_id)->where('status', 'izin')->count();

        $seharusnyaHadir = $internship->getTotalSeharusnyaHadir();
        $totalAlpha = max(0, $seharusnyaHadir - ($totalHadir + $totalIzin));

        $absensiHariIni = Attendance::where('internship_id', $internship->internship_id)
            ->whereDate('attendance_date', Carbon::today())->first();

        return view('user.dashboard', compact('internship', 'totalHadir', 'totalIzin', 'totalAlpha', 'absensiHariIni'));
    }

    // --- 2. Absensi kamera ---
    public function indexAbsensi()
    {
        $internship = Internship::where('user_id', Auth::id())->first();
        $today = \Carbon\Carbon::today();
        
        // Cek apakah periode PKL masih berlaku
        $endDate = Carbon::parse($internship->end_date)->endOfDay();
        if ($today->gt($endDate)) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Periode PKL Anda telah berakhir. Sistem presensi ditutup.');
        }
        
        $isHoliday = \App\Models\Holiday::whereDate('holiday_date', $today)->exists();

        if ($today->isWeekend() || $isHoliday) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Hari ini adalah hari libur atau akhir pekan. Sistem presensi dinonaktifkan.');
        }
        $attendance = Attendance::where('internship_id', $internship->internship_id)
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        return view('user.absensi_kamera', compact('attendance'));
    }

    // --- 3. Simpan Absen Masuk  ---
    public function storeMasuk(Request $request)
    {
        try {
            $now = Carbon::now();
            $today = Carbon::today();
            $jam = $now->format('H:i');
            
            // Cek apakah periode PKL masih berlaku
            $internship = Internship::where('user_id', Auth::id())->first();
            $endDate = Carbon::parse($internship->end_date)->endOfDay();
            if ($today->gt($endDate)) {
                return response()->json(['error' => 'Periode PKL Anda telah berakhir. Absensi tidak dapat dilakukan.'], 403);
            }

            // Validasi Waktu: 06:00 - 10:00
            if ($jam < '06:00' || $jam > '10:00') {
                return response()->json(['error' => 'Gagal! Absen masuk hanya tersedia pukul 06:00 - 10:00 WIB.'], 403);
            }

            $request->validate(['photo' => 'required']);

            // Cek apakah sudah absen hari ini
            $existing = Attendance::where('internship_id', $internship->internship_id)
                ->whereDate('attendance_date', Carbon::today())
                ->first();

            if ($existing) {
                return response()->json(['error' => 'Anda sudah tercatat hadir hari ini.'], 400);
            }

            $image = $request->photo;
            $image = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $image);
            $safeId = \Illuminate\Support\Str::slug($internship->internship_id);
            $imageName = 'in_' . $safeId . '_' . Carbon::today()->format('dmy') . '.jpg';

            \Illuminate\Support\Facades\Storage::disk('public_uploads')->put('attendance/' . $imageName, base64_decode($image));

            Attendance::create([
                'attendance_id' => 'ATT-' . strtoupper(\Illuminate\Support\Str::random(7)),
                'internship_id' => $internship->internship_id,
                'attendance_date' => Carbon::today(),
                'check_in_time' => $now->toTimeString(),
                'check_in_photo' => 'uploads/attendance/' . $imageName,
                'status' => 'hadir'
            ]);

            return response()->json(['success' => 'Berhasil absen masuk tepat waktu!']);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // --- 4. Simpan Absen Pulang  ---
    public function storePulang(Request $request)
    {
        try {
            $now = Carbon::now();
            $today = Carbon::today();
            $jam = $now->format('H:i');
            
            // Cek apakah periode PKL masih berlaku
            $internship = Internship::where('user_id', Auth::id())->first();
            $endDate = Carbon::parse($internship->end_date)->endOfDay();
            if ($today->gt($endDate)) {
                return response()->json(['error' => 'Periode PKL Anda telah berakhir. Absensi tidak dapat dilakukan.'], 403);
            }

            // Validasi Waktu: 16:00 - 23:59
            if ($jam < '16:00') {
                return response()->json(['error' => 'Belum waktunya pulang! Absen pulang dimulai pukul 16:00 WIB.'], 403);
            }

            $request->validate(['photo' => 'required']);
            
            $attendance = Attendance::where('internship_id', $internship->internship_id)
                ->whereDate('attendance_date', Carbon::today())
                ->first();

            if (!$attendance) {
                return response()->json(['error' => 'Gagal! Anda harus absen masuk terlebih dahulu.'], 400);
            }

            if ($attendance->check_out_time) {
                return response()->json(['error' => 'Anda sudah absen pulang hari ini.'], 400);
            }

            $image = $request->photo;
            $image = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $image);
            $safeId = \Illuminate\Support\Str::slug($internship->internship_id);
            $imageName = 'out_' . $safeId . '_' . Carbon::today()->format('dmy') . '.jpg';
            
            \Illuminate\Support\Facades\Storage::disk('public_uploads')->put('attendance/' . $imageName, base64_decode($image));

            $attendance->update([
                'check_out_time' => $now->toTimeString(),
                'check_out_photo' => 'uploads/attendance/' . $imageName,
            ]);

            return response()->json(['success' => 'Berhasil absen pulang, hati-hati di jalan!']);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 5. --- Halaman dan Proses Izin ---
    public function indexIzin()
    {
        $internship = Internship::where('user_id', Auth::id())->first();
        
        $leaveRequests = LeaveRequest::where('internship_id', $internship->internship_id)
            ->latest()
            ->get();

        return view('user.izin', compact('leaveRequests'));
    }

    public function storeIzin(Request $request)
    {
        // Cek apakah periode PKL masih berlaku
        $internship = Internship::where('user_id', Auth::id())->first();
        $today = Carbon::today();
        $endDate = Carbon::parse($internship->end_date)->endOfDay();
        if ($today->gt($endDate)) {
            return back()->with('error', 'Periode PKL Anda telah berakhir. Pengajuan izin tidak dapat dilakukan.');
        }

        $request->validate([
            'leave_date' => 'required|date', 
            'reason' => 'required|string|max:255',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', 
        ], [
            'document.max' => 'Gagal! Ukuran file lampiran tidak boleh lebih dari 2MB.',
            'document.mimes' => 'Gagal! Format file harus PDF, JPG, atau PNG.',
        ]);

        $exists = LeaveRequest::where('internship_id', $internship->internship_id)
            ->whereDate('leave_date', $request->leave_date)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Anda sudah mengajukan izin untuk tanggal ' . Carbon::parse($request->leave_date)->format('d M Y') . '.');
        }

        $documentPath = null;
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = 'leave_' . Str::slug($internship->internship_id) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/leave'), $filename);
            $documentPath = 'uploads/leave/' . $filename;
        }

        LeaveRequest::create([
            'leave_id' => 'LR-' . strtoupper(Str::random(7)),
            'internship_id' => $internship->internship_id,
            'leave_date' => $request->leave_date,
            'reason' => $request->reason,
            'document_path' => $documentPath,
            'status' => 'menunggu',
        ]);

        return redirect()->route('user.izin.index')->with('success', 'Pengajuan izin berhasil dikirim.');
    }

    public function destroyIzin($id)
    {
        $internship = Internship::where('user_id', Auth::id())->first();
  
        $leave = LeaveRequest::where('leave_id', $id)
            ->where('internship_id', $internship->internship_id)
            ->firstOrFail();

        if ($leave->status !== 'menunggu') {
            return back()->with('error', 'Gagal! Izin yang sudah diproses admin tidak dapat dibatalkan.');
        }

        if ($leave->document_path && file_exists(public_path($leave->document_path))) {
            unlink(public_path($leave->document_path));
        }

        $leave->delete();
        return redirect()->route('user.izin.index')->with('success', 'Pengajuan izin berhasil dibatalkan.');
    }

    // --- 5. Rekap dan laporan pdf --- 
    public function indexRekap(Request $request)
    {
        $user = Auth::user();
        $internship = Internship::with('user')->where('user_id', $user->id)->first();
        $month = $request->get('month');
        $year = $request->get('year', date('Y'));

        $query = Attendance::where('internship_id', $internship->internship_id);
        if ($month) {
            $query->whereMonth('attendance_date', $month)->whereYear('attendance_date', $year);
        }
        $attendances = $query->orderBy('attendance_date', 'desc')->get();

        $stats = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'izin'  => $attendances->where('status', 'izin')->count(),
            'alpha' => 0
        ];

        // Sinkronisasi Alpha Rekap Peserta
        $seharusnya = $internship->getTotalSeharusnyaHadir($month, $year);
        $stats['alpha'] = max(0, $seharusnya - ($stats['hadir'] + $stats['izin']));

        return view('user.rekap', compact('internship', 'attendances', 'stats', 'month', 'year'));
    }


    public function exportRekapPdf(Request $request)
    {
        $user = Auth::user();
        $internship = Internship::with(['user', 'institution'])->where('user_id', $user->id)->first();

        $month = $request->get('month');
        $year = $request->get('year', date('Y'));

        $query = Attendance::where('internship_id', $internship->internship_id);

        if ($month) {
            $query->whereMonth('attendance_date', $month)->whereYear('attendance_date', $year);
            $periodeLabel = Carbon::createFromFormat('m', $month)->translatedFormat('F') . " " . $year;
        } else {
            $periodeLabel = "Seluruh Periode PKL";
        }

        $attendances = $query->orderBy('attendance_date', 'asc')->get();

        $stats = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'izin'  => $attendances->where('status', 'izin')->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
        ];

        // QR Code Logic
        $hashData = $internship->internship_id . '|' . ($month ?? 'all') . '|' . $year;
        $encryptedHash = Crypt::encryptString($hashData);
        $verifyUrl = route('report.verify', ['hash' => $encryptedHash]);
        $qrcode = base64_encode(QrCode::format('svg')->size(90)->errorCorrection('H')->generate($verifyUrl));
        
        // Logo Logic
        $logoPath = public_path('uploads/img/logo-pln.png');
        $logoData = "";
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('user.rekap_pdf', compact(
            'internship', 
            'attendances', 
            'stats', 
            'month', 
            'year', 
            'periodeLabel', 
            'qrcode',
            'logoData' 
        ))->setPaper('a4', 'portrait');

        return $pdf->download("Rekap_Absensi_{$internship->user->name}.pdf");
    }
}