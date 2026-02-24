<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Internship;
use App\Models\LeaveRequest;
use App\Services\AttendanceDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; // WAJIB: Tambahkan ini untuk akses tabel libur mentah
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Halaman Pengaturan Password
     */
    public function settings()
    {
        return view('user.settings');
    }

    /**
     * Proses Update Password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ], [
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'new_password.min' => 'Password baru minimal harus 8 karakter.',
        ]);

        $userId = Auth::id();
        $user = \App\Models\User::find($userId);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Password lama yang Anda masukkan salah.');
        }

        $user->password = Hash::make($request->new_password);
        $user->save(); 

        return back()->with('success', 'Password berhasil diperbarui.');
    }

    /**
     * Dashboard peserta dengan validasi data kehadiran individu
     */
    public function index()
    {
        $user = Auth::user();
        $internship = Internship::with('user')->where('user_id', $user->id)->first();
        
        if (!$internship) {
            return "Data tidak ditemukan.";
        }
        
        $allAtt = Attendance::where('internship_id', $internship->internship_id)->get();
        
        // Poin kehadiran berbobot (Lengkap=1.0, Lupa Masuk=0.5)
        $h_lengkap = $allAtt->where('status', 'hadir')->where('check_in_time', '!=', '00:00:00')->count();
        $h_lupa = $allAtt->where('status', 'hadir')->where('check_in_time', '==', '00:00:00')->count();
        $totalIzin = $allAtt->where('status', 'izin')->count();
        
        $totalHadir = $h_lengkap + $h_lupa;
        
        // Memanggil fungsi dari Model yang SUDAH DIPERBAIKI (Sinkron Admin)
        $seharusnyaHadir = $internship->getTotalSeharusnyaHadir();
        $totalAlpha = max(0, $seharusnyaHadir - ($totalHadir + $totalIzin));

        $efektivitas = $seharusnyaHadir > 0 ? min(100, round((($h_lengkap + ($h_lupa * 0.5)) / $seharusnyaHadir) * 100)) : 0;

        $absensiHariIni = Attendance::where('internship_id', $internship->internship_id)
            ->whereDate('attendance_date', Carbon::today('Asia/Jakarta'))->first();

        return view('user.dashboard', compact('internship', 'totalHadir', 'totalIzin', 'totalAlpha', 'absensiHariIni', 'efektivitas'));
    }

    /**
     * Akses halaman kamera dengan pengecekan status keaktifan user yang login
     */
    public function indexAbsensi()
    {
        $internship = Internship::where('user_id', Auth::id())->first();
        
        if (!$internship || $internship->status !== 'aktif') {
            return redirect()->route('user.dashboard')->with('error', 'Status PKL Anda sudah berakhir. Fitur presensi dinonaktifkan.');
        }

        $today = Carbon::today('Asia/Jakarta');
        $endDate = Carbon::parse($internship->end_date)->endOfDay();
        
        if ($today->gt($endDate)) {
            return redirect()->route('user.dashboard')->with('error', 'Periode PKL Anda telah berakhir.');
        }
        
        // Cek Libur menggunakan DB::table agar konsisten
        $isHoliday = DB::table('holidays')->where('holiday_date', $today->toDateString())->exists();
        
        if ($today->isWeekend() || $isHoliday) {
            return redirect()->route('user.dashboard')->with('error', 'Presensi dinonaktifkan pada hari libur atau akhir pekan.');
        }

        $attendance = Attendance::where('internship_id', $internship->internship_id)
            ->whereDate('attendance_date', $today)->first();

        return view('user.absensi_kamera', compact('attendance', 'internship'));
    }

    /**
     * Menyimpan data presensi masuk
     */
    public function storeMasuk(Request $request)
    {
        try {
            $internship = Internship::where('user_id', Auth::id())->first();
            if (!$internship || $internship->status !== 'aktif') {
                return response()->json(['error' => 'Akses ditolak. Status PKL tidak aktif.'], 403);
            }

            $now = Carbon::now('Asia/Jakarta');
            if ($now->format('H:i') < '00:00' || $now->format('H:i') > '12:00') {
                return response()->json(['error' => 'Gagal! Absen masuk hanya tersedia pukul 00:00 - 12:00 WIB.'], 403);
            }

            $request->validate(['photo' => 'required']);
            $existing = Attendance::where('internship_id', $internship->internship_id)->whereDate('attendance_date', Carbon::today())->first();
            if ($existing) return response()->json(['error' => 'Anda sudah tercatat hadir hari ini.'], 400);

            $image = $request->photo;
            $image = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $image);
            $safeId = Str::slug($internship->internship_id);
            $imageName = 'in_' . $safeId . '_' . date('dmy_His') . '.jpg';
            Storage::disk('public_uploads')->put('attendance/' . $imageName, base64_decode($image));

            Attendance::create([
                'attendance_id' => 'ATT-' . strtoupper(Str::random(7)),
                'internship_id' => $internship->internship_id,
                'attendance_date' => Carbon::today(),
                'check_in_time' => $now->toTimeString(),
                'check_in_photo' => 'uploads/attendance/' . $imageName,
                'status' => 'hadir'
            ]);

            return response()->json(['success' => 'Berhasil absen masuk!']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menyimpan data presensi pulang
     */
    public function storePulang(Request $request)
    {
        try {
            $internship = Internship::where('user_id', Auth::id())->first();
            if (!$internship || $internship->status !== 'aktif') {
                return response()->json(['error' => 'Akses ditolak. Status PKL tidak aktif.'], 403);
            }

            $now = Carbon::now('Asia/Jakarta');
            if ($now->format('H:i') < '12:01' || $now->format('H:i') > '19:00') {
                return response()->json(['error' => 'Absen pulang hanya tersedia pukul 12:01 - 19:00 WIB.'], 403);
            }

            $request->validate(['photo' => 'required']);
            $attendance = Attendance::where('internship_id', $internship->internship_id)->whereDate('attendance_date', Carbon::today())->first();
            if ($attendance && $attendance->check_out_time) return response()->json(['error' => 'Anda sudah absen pulang hari ini.'], 400);

            $image = $request->photo;
            $image = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $image);
            $imageName = 'out_' . Str::slug($internship->internship_id) . '_' . date('dmy_His') . '.jpg';
            Storage::disk('public_uploads')->put('attendance/' . $imageName, base64_decode($image));

            Attendance::updateOrCreate(
                ['internship_id' => $internship->internship_id, 'attendance_date' => Carbon::today()],
                [
                    'attendance_id' => $attendance ? $attendance->attendance_id : 'ATT-' . strtoupper(Str::random(7)),
                    'check_in_time' => $attendance ? $attendance->check_in_time : '00:00:00',
                    'check_in_photo' => $attendance ? $attendance->check_in_photo : 'uploads/attendance/lupa_absen.png',
                    'check_out_time' => $now->toTimeString(),
                    'check_out_photo' => 'uploads/attendance/' . $imageName,
                    'status' => 'hadir'
                ]
            );

            return response()->json(['success' => 'Berhasil absen pulang!']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * CRUD Perizinan
     */
    public function indexIzin()
    {
        $internship = Internship::where('user_id', Auth::id())->first();
        $leaveRequests = LeaveRequest::where('internship_id', $internship->internship_id)->latest()->get();
        return view('user.izin', compact('leaveRequests'));
    }

    public function storeIzin(Request $request)
    {
        $internship = Internship::where('user_id', Auth::id())->first();
        if (!$internship || $internship->status !== 'aktif') {
            return back()->with('error', 'Fitur pengajuan izin dinonaktifkan karena status PKL tidak aktif.');
        }

        $request->validate([
            'leave_date' => 'required|date', 
            'reason' => 'required|string|max:255',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', 
        ], ['document.max' => 'Ukuran file lampiran terlalu besar (Maks 2MB).']);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = 'leave_' . Str::slug($internship->internship_id) . '_' . date('His') . '.' . $file->getClientOriginalExtension();
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
        $leave = LeaveRequest::where('leave_id', $id)->where('internship_id', $internship->internship_id)->firstOrFail();
        
        if ($leave->status !== 'menunggu') {
            return back()->with('error', 'Izin yang sudah diproses tidak dapat dibatalkan.');
        }

        if ($leave->document_path && file_exists(public_path($leave->document_path))) {
            unlink(public_path($leave->document_path));
        }
        
        $leave->delete();
        return redirect()->route('user.izin.index')->with('success', 'Pengajuan izin dibatalkan.');
    }

    /**
     * Rekap Absensi Bulanan di layar peserta
     */
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

        // Sinkronisasi angka Alpha dengan logika pusat di Model
        $seharusnya = $internship->getTotalSeharusnyaHadir($month, $year);
        $stats['alpha'] = max(0, $seharusnya - ($stats['hadir'] + $stats['izin']));

        return view('user.rekap', compact('internship', 'attendances', 'stats', 'month', 'year'));
    }

    /**
     * Export Laporan PDF (PERBAIKAN UTAMA)
     * Menggunakan DB::table dan Strict Comparison untuk sinkronisasi libur.
     */
    public function exportRekapPdf(Request $request)
    {
        $user = Auth::user();
        $internship = Internship::with(['user', 'institution'])->where('user_id', $user->id)->first();

        $month = $request->get('month');
        $year = $request->get('year', date('Y'));

        // 1. Ambil data absensi riil dari database
        $query = Attendance::where('internship_id', $internship->internship_id);
        if ($month) {
            $query->whereMonth('attendance_date', $month)->whereYear('attendance_date', $year);
            $periodeLabel = Carbon::createFromFormat('m', $month)->translatedFormat('F') . " " . $year;
        } else {
            $periodeLabel = "Seluruh Periode PKL";
        }
        // Mapping berdasarkan tanggal string
        $dbAttendances = $query->get()->keyBy(function($item) {
            return Carbon::parse($item->attendance_date)->format('Y-m-d');
        });

        // 2. Tentukan Rentang Tanggal
        $internStart = Carbon::parse($internship->start_date)->startOfDay();
        $accountCreated = Carbon::parse($internship->user->created_at)->startOfDay();
        $internEnd = Carbon::parse($internship->end_date)->endOfDay();
        $now = Carbon::now('Asia/Jakarta')->startOfDay();

        $effectiveStart = $internStart->gt($accountCreated) ? $internStart : $accountCreated;
        
        if ($month) {
            $mStart = Carbon::create($year, $month, 1)->startOfMonth();
            $mEnd = Carbon::create($year, $month, 1)->endOfMonth();
            $start = $effectiveStart->gt($mStart) ? $effectiveStart : $mStart;
            $limit = $internEnd->lt($mEnd) ? $internEnd : $mEnd;
        } else {
            $start = $effectiveStart;
            $limit = $internEnd;
        }
        // Batas akhir adalah hari ini (tidak menghitung masa depan)
        $finalLimit = $now->lt($limit) ? $now : $limit;

        // 3. Generate baris data lengkap
        $attendances = collect();

        if ($start <= $finalLimit) {
            // FIX: Gunakan DB::table agar format tanggal sama persis dengan yang di Model Internship
            $holidays = DB::table('holidays')
                ->whereBetween('holiday_date', [
                    $start->format('Y-m-d'), 
                    $finalLimit->format('Y-m-d')
                ])
                ->pluck('holiday_date')
                ->toArray();

            $current = $start->copy();
            
            // Loop harian
            while ($current <= $finalLimit) {
                // Konversi tanggal loop ke format string Y-m-d agar in_array bekerja 100%
                $dateStr = $current->format('Y-m-d');
                
                // Cek: Bukan Weekend & Bukan Libur Nasional
                if (!$current->isWeekend() && !in_array($dateStr, $holidays)) {
                    
                    if ($dbAttendances->has($dateStr)) {
                        // Jika ada data absen di DB, masukkan
                        $attendances->push($dbAttendances->get($dateStr));
                    } else {
                        // Jika tidak ada data absen tapi hari kerja -> ALPHA
                        $attendances->push((object)[
                            'attendance_date' => $dateStr,
                            'check_in_time' => null,
                            'check_out_time' => null,
                            'status' => 'alpha'
                        ]);
                    }
                }
                $current->addDay();
            }
        }

        $attendances = $attendances->sortBy('attendance_date')->values();

        // 4. Statistik akhir
        $stats = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'izin'  => $attendances->where('status', 'izin')->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
        ];

        // 5. Generate PDF & QR
        $hashData = $internship->internship_id . '|' . ($month ?? 'all') . '|' . $year;
        $encryptedHash = Crypt::encryptString($hashData);
        $verifyUrl = route('report.verify', ['hash' => $encryptedHash]);
        $qrcode = base64_encode(QrCode::format('svg')->size(90)->errorCorrection('H')->generate($verifyUrl));
        
        $logoPath = public_path('uploads/img/logo-plnIP.png');
        $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : "";

        $pdf = Pdf::loadView('user.rekap_pdf', compact(
            'internship', 'attendances', 'stats', 'month', 'year', 'periodeLabel', 'qrcode', 'logoData' 
        ))->setPaper('a4', 'portrait');

        $fileName = "Rekap_Absensi_{$internship->user->name}.pdf";

        $pdfContent = $pdf->output();
        $filePath = "attendance_documents/" . date('Y/m/d') . "/" . $fileName;
        Storage::disk('local')->put($filePath, $pdfContent);

        try {
            $documentService = new \App\Services\AttendanceDocumentService();
            $documentService->saveDocument(
                internshipId: $internship->internship_id,
                filePath: $filePath,
                qrHash: $encryptedHash,
                periodStart: $month ? Carbon::create($year, $month, 1)->startOfMonth() : $internship->start_date,
                periodEnd: $month ? Carbon::create($year, $month, 1)->endOfMonth() : $internship->end_date,
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal simpan metadata PDF: ' . $e->getMessage());
        }

        return $pdf->download($fileName);
    }
}