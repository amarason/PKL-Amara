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
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UserController extends Controller
{
    // --- 1. Dashboard ---
    public function index()
    {
        $user = Auth::user();
        $internship = Internship::where('user_id', $user->id)->first();

        if (!$internship) {
            return "Data Internship tidak ditemukan. Pastikan data peserta sudah terdaftar.";
        }

        // Statistik untuk widget dashboard
        $totalHadir = Attendance::where('internship_id', $internship->internship_id)
            ->where('status', 'hadir')
            ->count();

        $totalIzin = Attendance::where('internship_id', $internship->internship_id)
            ->where('status', 'izin')
            ->count();

        // Hitung Total Alpha
        $totalAlpha = Attendance::where('internship_id', $internship->internship_id)
            ->where('status', 'alpha')
            ->count();

        // Status absensi hari ini 
        $absensiHariIni = Attendance::where('internship_id', $internship->internship_id)
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        return view('user.dashboard', compact('internship', 'totalHadir', 'totalIzin', 'totalAlpha', 'absensiHariIni'));
    }

    // --- 2. Absensi kamera ---
    public function indexAbsensi()
    {
        $today = \Carbon\Carbon::today();
        $isHoliday = \App\Models\Holiday::whereDate('holiday_date', $today)->exists();

        if ($today->isWeekend() || $isHoliday) {
            return redirect()->route('user.dashboard')
                ->with('error', 'Hari ini adalah hari libur atau akhir pekan. Sistem presensi dinonaktifkan.');
        }
        $internship = Internship::where('user_id', Auth::id())->first();
        $attendance = Attendance::where('internship_id', $internship->internship_id)
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        return view('user.absensi_kamera', compact('attendance'));
    }

    // --- 3. Simpan Absen Masuk  ---
    public function storeMasuk(Request $request)
    {
        try {
            $request->validate(['photo' => 'required']); 
            $internship = Internship::where('user_id', Auth::id())->first();

            // Cek Keberadaan Data
            $existing = Attendance::where('internship_id', $internship->internship_id)
                ->whereDate('attendance_date', Carbon::today())
                ->first();

            if ($existing) {
                return response()->json(['error' => 'Anda sudah tercatat hadir hari ini.'], 400);
            }

            $image = $request->photo; 
            if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
                $image = substr($image, strpos($image, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, etc

                if (!in_array($type, ['jpg', 'jpeg', 'png'])) {
                    throw new \Exception('Format gambar tidak valid.');
                }
                $image = str_replace(' ', '+', $image);
            } else {
                throw new \Exception('Data gambar tidak valid.');
            }

            $safeId = Str::slug($internship->internship_id);
            $imageName = 'in_' . $safeId . '_' . time() . '.jpg';

            if (!file_exists(public_path('uploads/attendance'))) {
                mkdir(public_path('uploads/attendance'), 0777, true);
            }

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

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // --- 4. Simpan Absen Pulang  ---
    public function storePulang(Request $request)
    {
        $request->validate(['photo' => 'required']);
        
        $internship = Internship::where('user_id', Auth::id())->first();
        $attendance = Attendance::where('internship_id', $internship->internship_id)
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        if (!$attendance) {
            return response()->json(['error' => 'Anda belum absen masuk!'], 400);
        }

        if ($attendance->check_out_time) {
            return response()->json(['error' => 'Anda sudah absen pulang hari ini!'], 400);
        }

        $image = $request->photo;
        $image = str_replace(['data:image/jpeg;base64,', 'data:image/png;base64,', ' '], ['', '', '+'], $image);
        
        $safeId = Str::slug($internship->internship_id);
        $imageName = 'out_' . $safeId . '_' . time() . '.jpg';
        
        Storage::disk('public_uploads')->put('attendance/' . $imageName, base64_decode($image));

        $attendance->update([
            'check_out_time' => Carbon::now()->toTimeString(),
            'check_out_photo' => 'uploads/attendance/' . $imageName,
        ]);

        return response()->json(['success' => 'Berhasil absen pulang, hati-hati di jalan!']);
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
        $request->validate([
            'leave_date' => 'required|date|after_or_equal:today',
            'reason' => 'required|string|max:255',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'leave_date.after_or_equal' => 'Gagal! Anda tidak dapat mengajukan izin untuk tanggal yang sudah terlewat.'
        ]);

        $internship = Internship::where('user_id', Auth::id())->first();

        $exists = LeaveRequest::where('internship_id', $internship->internship_id)
            ->whereDate('leave_date', $request->leave_date)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Anda sudah mengajukan izin untuk tanggal tersebut.');
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
        $internship = Internship::where('user_id', $user->id)->first();

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
            'alpha' => $attendances->where('status', 'alpha')->count(),
        ];

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