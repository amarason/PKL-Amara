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
     * Dashboard Peserta: Menampilkan ringkasan kehadiran dan efektivitas berbobot.
     */
    public function index()
    {
        $user = Auth::user();
        $internship = Internship::with('user')->where('user_id', $user->id)->first();
        
        $allAtt = Attendance::where('internship_id', $internship->internship_id)->get();
        
        /** * LOGIKA KEADILAN: 
         * Absen Lengkap (Check-in ada) = 1.0 poin
         * Lupa Absen Masuk (Check-in 00:00:00) = 0.5 poin
         */
        $h_lengkap = $allAtt->where('status', 'hadir')->where('check_in_time', '!=', '00:00:00')->count();
        $h_lupa = $allAtt->where('status', 'hadir')->where('check_in_time', '==', '00:00:00')->count();
        $totalIzin = $allAtt->where('status', 'izin')->count();
        
        $totalHadir = $h_lengkap + $h_lupa;
        $seharusnyaHadir = $internship->getTotalSeharusnyaHadir();
        $totalAlpha = max(0, $seharusnyaHadir - ($totalHadir + $totalIzin));

        // Menghitung persentase efektivitas berdasarkan poin berbobot
        $efektivitas = $seharusnyaHadir > 0 ? min(100, round((($h_lengkap + ($h_lupa * 0.5)) / $seharusnyaHadir) * 100)) : 0;

        $absensiHariIni = Attendance::where('internship_id', $internship->internship_id)
            ->whereDate('attendance_date', Carbon::today('Asia/Jakarta'))->first();

        return view('user.dashboard', compact('internship', 'totalHadir', 'totalIzin', 'totalAlpha', 'absensiHariIni', 'efektivitas'));
    }

    /**
     * Tampilan Halaman Kamera Presensi
     */
    public function indexAbsensi()
    {
        $internship = Internship::where('user_id', Auth::id())->first();
        
        // Proteksi: Cegah akses jika Admin sudah menandai PKL SELESAI
        if ($internship->status !== 'aktif') {
            return redirect()->route('user.dashboard')->with('error', 'Status PKL Anda sudah berakhir. Fitur presensi dinonaktifkan.');
        }

        $today = Carbon::today('Asia/Jakarta');
        $endDate = Carbon::parse($internship->end_date)->endOfDay();
        
        // Cegah akses jika melewati tanggal akhir PKL
        if ($today->gt($endDate)) {
            return redirect()->route('user.dashboard')->with('error', 'Periode PKL Anda telah berakhir.');
        }
        
        // Blokir absensi pada hari Sabtu, Minggu, atau hari libur nasional
        $isHoliday = \App\Models\Holiday::whereDate('holiday_date', $today)->exists();
        if ($today->isWeekend() || $isHoliday) {
            return redirect()->route('user.dashboard')->with('error', 'Presensi dinonaktifkan pada hari libur/akhir pekan.');
        }

        $attendance = Attendance::where('internship_id', $internship->internship_id)
            ->whereDate('attendance_date', $today)->first();

        return view('user.absensi_kamera', compact('attendance'));
    }

    /**
     * Proses Simpan Absen Masuk (00:00 - 12:00)
     */
    public function storeMasuk(Request $request)
    {
        try {
            $internship = Internship::where('user_id', Auth::id())->first();
            
            // Keamanan tambahan di sisi server untuk mengecek status akun
            if ($internship->status !== 'aktif') {
                return response()->json(['error' => 'Gagal! Status PKL Anda tidak aktif.'], 403);
            }

            $now = Carbon::now('Asia/Jakarta');
            if ($now->format('H:i') < '00:00' || $now->format('H:i') > '12:00') {
                return response()->json(['error' => 'Gagal! Sesi absen masuk ditutup (Hanya 00:00 - 12:00).'], 403);
            }

            $request->validate(['photo' => 'required']);
            $existing = Attendance::where('internship_id', $internship->internship_id)->whereDate('attendance_date', Carbon::today())->first();
            if ($existing) return response()->json(['error' => 'Anda sudah absen hari ini.'], 400);

            $image = $request->photo;
            $image = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $image);
            $imageName = 'in_' . Str::slug($internship->internship_id) . '_' . date('dmy_His') . '.jpg';
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
     * Proses Simpan Absen Pulang (12:01 - 19:00)
     * Tetap mengizinkan absen pulang meskipun lupa absen masuk (Poin 0.5)
     */
    public function storePulang(Request $request)
    {
        try {
            $internship = Internship::where('user_id', Auth::id())->first();
            
            if ($internship->status !== 'aktif') {
                return response()->json(['error' => 'Gagal! Status PKL Anda tidak aktif.'], 403);
            }

            $now = Carbon::now('Asia/Jakarta');
            if ($now->format('H:i') < '12:01' || $now->format('H:i') > '19:00') {
                return response()->json(['error' => 'Gagal! Sesi absen pulang hanya pukul 12:01 - 19:00.'], 403);
            }

            $request->validate(['photo' => 'required']);
            $attendance = Attendance::where('internship_id', $internship->internship_id)->whereDate('attendance_date', Carbon::today())->first();

            if ($attendance && $attendance->check_out_time) return response()->json(['error' => 'Sudah absen pulang hari ini.'], 400);

            $image = $request->photo;
            $image = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $image);
            $imageName = 'out_' . Str::slug($internship->internship_id) . '_' . date('dmy_His') . '.jpg';
            Storage::disk('public_uploads')->put('attendance/' . $imageName, base64_decode($image));

            /**
             * Update record jika ada (sudah masuk), 
             * Create record jika tidak ada (lupa absen masuk) dengan tanda 00:00:00
             */
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
     * Menampilkan daftar riwayat izin peserta
     */
    public function indexIzin()
    {
        $internship = Internship::where('user_id', Auth::id())->first();
        
        $leaveRequests = LeaveRequest::where('internship_id', $internship->internship_id)
            ->latest()
            ->get();

        return view('user.izin', compact('leaveRequests'));
    }

    /**
     * Proses Pengajuan Izin Baru (Maks 2MB)
     */
    public function storeIzin(Request $request)
    {
        $internship = Internship::where('user_id', Auth::id())->first();
        
        // Proteksi Status Akun Selesai
        if ($internship->status !== 'aktif') {
            return back()->with('error', 'Akses ditolak! Status PKL Anda sudah berakhir.');
        }

        $request->validate([
            'leave_date' => 'required|date', 
            'reason' => 'required|string|max:255',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', 
        ], [
            'document.max' => 'Ukuran file lampiran terlalu besar (Maks 2MB).',
            'document.mimes' => 'Format file harus PDF, JPG, atau PNG.',
        ]);

        // Cek duplikasi izin di tanggal yang sama
        $exists = LeaveRequest::where('internship_id', $internship->internship_id)
            ->whereDate('leave_date', $request->leave_date)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Izin untuk tanggal tersebut sudah pernah diajukan.');
        }

        $documentPath = null;
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = 'leave_' . Str::slug($internship->internship_id) . '_' . date('dmy_His') . '.' . $file->getClientOriginalExtension();
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

    /**
     * Menghapus/Membatalkan Pengajuan Izin (Hanya jika status 'menunggu')
     */
    public function destroyIzin($id)
    {
        $internship = Internship::where('user_id', Auth::id())->first();
  
        $leave = LeaveRequest::where('leave_id', $id)
            ->where('internship_id', $internship->internship_id)
            ->firstOrFail();

        // Keamanan: Izin yang sudah disetujui/ditolak tidak boleh dihapus peserta
        if ($leave->status !== 'menunggu') {
            return back()->with('error', 'Gagal! Izin sudah diproses oleh Admin.');
        }

        if ($leave->document_path && file_exists(public_path($leave->document_path))) {
            unlink(public_path($leave->document_path));
        }

        $leave->delete();
        return redirect()->route('user.izin.index')->with('success', 'Pengajuan izin berhasil dibatalkan.');
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
     * Export Laporan PDF untuk peserta dengan fitur tanda tangan QR Code.
     */
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

        // Penulisan metadata untuk verifikasi scan QR Code
        $hashData = $internship->internship_id . '|' . ($month ?? 'all') . '|' . $year;
        $encryptedHash = Crypt::encryptString($hashData);
        $verifyUrl = route('report.verify', ['hash' => $encryptedHash]);
        $qrcode = base64_encode(QrCode::format('svg')->size(90)->errorCorrection('H')->generate($verifyUrl));
        
        $logoPath = public_path('uploads/img/logo-pln.png');
        $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : "";

        $pdf = Pdf::loadView('user.rekap_pdf', compact(
            'internship', 'attendances', 'stats', 'month', 'year', 'periodeLabel', 'qrcode', 'logoData' 
        ))->setPaper('a4', 'portrait');

        $fileName = "Rekap_Absensi_{$internship->user->name}.pdf";

        // Simpan salinan PDF ke storage untuk arsip sistem
        $pdfContent = $pdf->output();
        $filePath = "attendance_documents/" . date('Y/m/d') . "/" . $fileName;
        Storage::disk('local')->put($filePath, $pdfContent);

        // Pencatatan metadata laporan ke database
        $documentService = new \App\Services\AttendanceDocumentService();
        try {
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