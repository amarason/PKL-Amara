<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Internship;
use App\Models\LeaveRequest;
use App\Services\AttendanceDocumentService;
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
        
        $allAtt = Attendance::where('internship_id', $internship->internship_id)->get();
        
        // Hitung Poin Berbobot untuk Efektivitas
        $h_lengkap = $allAtt->where('status', 'hadir')->where('check_in_time', '!=', '00:00:00')->count();
        $h_lupa = $allAtt->where('status', 'hadir')->where('check_in_time', '==', '00:00:00')->count();
        $totalIzin = $allAtt->where('status', 'izin')->count();
        
        $totalHadir = $h_lengkap + $h_lupa;

        $seharusnyaHadir = $internship->getTotalSeharusnyaHadir();
        $totalAlpha = max(0, $seharusnyaHadir - ($totalHadir + $totalIzin));

        $efektivitas = $seharusnyaHadir > 0 ? min(100, round((($h_lengkap + ($h_lupa * 0.5)) / $seharusnyaHadir) * 100)) : 0;

        $absensiHariIni = Attendance::where('internship_id', $internship->internship_id)
            ->whereDate('attendance_date', \Carbon\Carbon::today())->first();

        return view('user.dashboard', compact('internship', 'totalHadir', 'totalIzin', 'totalAlpha', 'absensiHariIni', 'efektivitas'));
    }

    // --- 2. Absensi kamera ---
    public function indexAbsensi()
    {
        $internship = Internship::where('user_id', Auth::id())->first();
        $today = \Carbon\Carbon::today('Asia/Jakarta');
        
        // Validasi Akhir Periode PKL (Wajib)
        $endDate = Carbon::parse($internship->end_date)->endOfDay();
        if ($today->gt($endDate)) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Periode PKL Anda telah berakhir. Sistem presensi ditutup.');
        }
        
        $isHoliday = \App\Models\Holiday::whereDate('holiday_date', $today)->exists();

        // Validasi Akhir Pekan & Hari Libur (Wajib)
        if ($today->isWeekend() || $isHoliday) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Hari ini adalah hari libur atau akhir pekan. Sistem presensi dinonaktifkan.');
        }

        $attendance = Attendance::where('internship_id', $internship->internship_id)
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        return view('user.absensi_kamera', compact('attendance'));
    }

    // --- 3. Simpan Absen Masuk ---
    public function storeMasuk(Request $request)
    {
        try {
            $now = Carbon::now('Asia/Jakarta');
            $jam = $now->format('H:i');
            
            // Validasi Waktu: 00:00 - 12:00 
            if ($jam < '00:00' || $jam > '12:00') {
                return response()->json(['error' => 'Gagal! Absen masuk hanya tersedia pukul 00:00 - 12:00 WIB.'], 403);
            }

            $request->validate(['photo' => 'required']);
            $internship = Internship::where('user_id', Auth::id())->first();

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

    // --- 4. Simpan Absen Pulang ---
    public function storePulang(Request $request)
    {
        try {
            $now = Carbon::now('Asia/Jakarta');
            $jam = $now->format('H:i');
            
            // Validasi Waktu: 12:01 - 19:00
            if ($jam < '12:01' || $jam > '19:00') {
                return response()->json(['error' => 'Gagal! Absen pulang hanya tersedia pukul 12:01 - 19:00 WIB.'], 403);
            }

            $request->validate(['photo' => 'required']);
            $internship = Internship::where('user_id', Auth::id())->first();
            
            $attendance = Attendance::where('internship_id', $internship->internship_id)
                ->whereDate('attendance_date', Carbon::today())
                ->first();

            if ($attendance && $attendance->check_out_time) {
                return response()->json(['error' => 'Anda sudah absen pulang hari ini.'], 400);
            }

            $image = $request->photo;
            $image = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $image);
            $imageName = 'out_' . \Illuminate\Support\Str::slug($internship->internship_id) . '_' . date('dmy_His') . '.jpg';
            
            \Illuminate\Support\Facades\Storage::disk('public_uploads')->put('attendance/' . $imageName, base64_decode($image));

            //  Menggunakan 00:00:00 sebagai flag tanpa ubah DB
            Attendance::updateOrCreate(
                ['internship_id' => $internship->internship_id, 'attendance_date' => Carbon::today()],
                [
                    'attendance_id' => $attendance ? $attendance->attendance_id : 'ATT-' . strtoupper(\Illuminate\Support\Str::random(7)),
                    'check_in_time' => $attendance ? $attendance->check_in_time : '00:00:00',
                    'check_in_photo' => $attendance ? $attendance->check_in_photo : 'uploads/attendance/lupa_absen.png',
                    'check_out_time' => $now->toTimeString(),
                    'check_out_photo' => 'uploads/attendance/' . $imageName,
                    'status' => 'hadir'
                ]
            );

            return response()->json(['success' => 'Berhasil absen pulang!']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal: ' . $e->getMessage()], 500);
        }
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

        $fileName = "Rekap_Absensi_{$internship->user->name}.pdf";

        // --- Simpan pdf ke storage ---
        $pdfContent = $pdf->output();
        $filePath = "attendance_documents/" . date('Y/m/d') . "/" . $fileName;
        Storage::disk('local')->put($filePath, $pdfContent);

        // --- Simpa metadata ke database ---
        $documentService = new AttendanceDocumentService();
        try {
            $documentService->saveDocument(
                internshipId: $internship->internship_id,
                filePath: $filePath,
                qrHash: $encryptedHash,
                periodStart: $month ? Carbon::create($year, $month, 1)->startOfMonth() : $internship->start_date,
                periodEnd: $month ? Carbon::create($year, $month, 1)->endOfMonth() : $internship->end_date,
            );
        } catch (\Exception $e) {
            // Log error tapi tetap download PDF
            \Illuminate\Support\Facades\Log::warning('Attendance document save failed: ' . $e->getMessage());
        }

        return $pdf->download($fileName);
    }
}