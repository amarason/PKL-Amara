<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Internship;
use App\Models\Attendance;
use App\Models\Institution;
use App\Models\Major;
use App\Models\LeaveRequest;
use App\Services\IdGeneratorService; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt; 
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AdminController extends Controller
{
    protected $idService;

    // --- CONSTRUCTOR ---
    // Inject Service melalui Constructor agar bisa digunakan di semua method
    public function __construct(IdGeneratorService $idService)
    {
        $this->idService = $idService;
    }

    // --- 1. Dashboard ---
    public function index()
    {
        $totalPeserta = Internship::count();
        $pesertaAktif = Internship::where('status', 'aktif')->count();
        $hariIni = Carbon::now()->toDateString();

        $hadirHariIni = Attendance::whereDate('attendance_date', $hariIni)
            ->where('status', 'hadir')
            ->count();

        $izinHariIni = Attendance::whereDate('attendance_date', $hariIni)
            ->where('status', 'izin')
            ->count();

        $kehadiran = Attendance::with(['internship.user'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalPeserta', 'pesertaAktif', 'hadirHariIni', 'izinHariIni', 'kehadiran'
        ));
    }

    // --- 2. Manajemen Peserta ---

    public function indexPeserta(Request $request)
    {
        $status = $request->get('status', 'aktif');
        $search = $request->get('search');

        $query = Internship::with(['user', 'major', 'institution'])
            ->where('status', $status);

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('login_id', 'like', "%{$search}%");
            });
        }

        $peserta = $query->latest()->get();
        $institutions = Institution::all();
        $majors = Major::all();

        return view('admin.index_peserta', compact('peserta', 'status', 'institutions', 'majors'));
    }

    public function create()
    {
        $institutions = Institution::all();
        $majors = Major::all();
        
        return view('admin.create_peserta', compact('institutions', 'majors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'login_id' => 'required|unique:users,login_id',
            'name' => 'required',
            'institution_id' => 'required',
            'major_id' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $inst = Institution::where('institution_id', $request->institution_id)->first();
                $strata = str_contains(strtoupper($inst->institution_name), 'SMK') ? 'SMK' : 'S1';

                $user = User::create([
                    'login_id' => $request->login_id,
                    'name' => $request->name,
                    'role_id' => 'ROLE_PESERTA',
                    'password' => Hash::make($request->login_id),
                    'is_active' => 1,
                ]);

                Internship::create([
                    'internship_id' => $this->idService->generateInternshipId($strata),
                    'user_id' => $user->id,
                    'institution_id' => $request->institution_id,
                    'major_id' => $request->major_id,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'status' => 'aktif',
                ]);
            });

            return back()->with('success', "Peserta atas nama {$request->name} berhasil didaftarkan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mendaftarkan peserta: ' . $e->getMessage());
        }
    }

    public function editPeserta($id)
    {
        $peserta = Internship::with('user')->where('internship_id', $id)->firstOrFail();
        if (!$peserta) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        
        return response()->json($peserta);
    }

    public function updatePeserta(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'login_id' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $peserta = Internship::where('internship_id', $id)->firstOrFail();
                
                $peserta->update([
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'institution_id' => $request->institution_id ?? $peserta->institution_id,
                    'major_id' => $request->major_id ?? $peserta->major_id,
                ]);

                $peserta->user->update([
                    'name' => $request->name,
                    'login_id' => $request->login_id,
                ]);
            });

            return back()->with('success', 'Data peserta berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui data.');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        Internship::where('internship_id', $id)->update(['status' => $request->status]);
        return back()->with('success', 'Status peserta berhasil diperbarui.');
    }

    // --- 3. Absensi dan Izin ---

    public function indexAbsensi(Request $request)
    {
        $tanggalDipilih = $request->get('tanggal', Carbon::now()->toDateString());

        $attendance = Attendance::with(['internship.user', 'admin'])
            ->whereDate('attendance_date', $tanggalDipilih)
            ->get();

        $leaveLogs = LeaveRequest::with(['internship.user', 'admin'])
            ->whereIn('status', ['disetujui', 'ditolak'])
            ->latest('approved_at')
            ->take(10)
            ->get();

        $leaveRequests = LeaveRequest::with(['internship.user'])
            ->where('status', 'menunggu')
            ->get();

        return view('admin.absensi', compact('attendance', 'leaveRequests', 'leaveLogs', 'tanggalDipilih'));
    }

    public function verifyLeave(Request $request, $id)
    {
        $request->validate(['action' => 'required|in:disetujui,ditolak']);

        try {
            $leave = LeaveRequest::where('leave_id', $id)->first();
            if (!$leave) return back()->with('error', 'Data tidak ditemukan.');

            $admin = Auth::user();

            DB::transaction(function () use ($request, $leave, $admin) {
                $leave->update([
                    'status' => $request->action,
                    'approved_by' => $admin->login_id, 
                    'approved_at' => now(),
                ]);

                if ($request->action === 'disetujui') {
                    Attendance::updateOrCreate(
                        ['internship_id' => $leave->internship_id, 'attendance_date' => $leave->leave_date],
                        [
                            'attendance_id' => 'ATT-' . strtoupper(substr(uniqid(), -7)),
                            'check_in_time' => '08:00:00',
                            'check_in_photo' => 'leave_approved.png',
                            'status' => 'izin',
                            'update_reason' => 'Izin disetujui: ' . $leave->reason,
                            'updated_by' => $admin->id,
                        ]
                    );
                }
            });

            return back()->with('success', 'Status berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function checkNotification()
    {
        $count = LeaveRequest::where('status', 'menunggu')->count();
        return response()->json(['count' => $count]);
    }

    public function updateAttendanceStatus(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendance,attendance_id',
            'status' => 'required|in:hadir,izin,alpha',
            'update_reason' => 'required|string|max:255',
        ], [
            'update_reason.required' => 'Alasan perubahan status wajib diisi'
        ]);

        try {
            $attendance = Attendance::where('attendance_id', $request->attendance_id)->firstOrFail();
            $admin = Auth::user();

            $attendance->update([
                'status' => $request->status,
                'update_reason' => $request->update_reason . ' (Dikoreksi oleh Admin: ' . $admin->name . ')',
                'updated_by' => $admin->id, 
            ]);

            return back()->with('success', 'Status kehadiran peserta berhasil diperbarui menjadi ' . strtoupper($request->status));

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    // --- 4. Rekap dan laporan pdf ---

    public function indexRekap(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $search = $request->get('search');
        $institution_id = $request->get('institution_id');

        $query = Internship::with(['user', 'institution', 'attendance' => function($query) use ($bulan, $tahun) {
            if ($bulan && $bulan !== 'all') $query->whereMonth('attendance_date', $bulan);
            $query->whereYear('attendance_date', $tahun);
        }]);

        if ($search) {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('login_id', 'like', "%{$search}%");
            });
        }
        if ($institution_id) $query->where('institution_id', $institution_id);

        $peserta = $query->where('status', 'aktif')->get();
        $institutions = Institution::all();

        return view('admin.rekap_absensi', compact('peserta', 'bulan', 'tahun', 'institutions'));
    }

    public function exportRekapPdf(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $institution_id = $request->get('institution_id');
        $search = $request->get('search'); 

        $query = Internship::with(['user', 'institution', 'attendance' => function($q) use ($bulan, $tahun) {
            if ($bulan && $bulan !== 'all') $q->whereMonth('attendance_date', $bulan);
            $q->whereYear('attendance_date', $tahun);
        }]);

        if ($institution_id) $query->where('institution_id', $institution_id);

        if ($search) {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('login_id', 'like', "%{$search}%");
            });
        }

        $peserta = $query->where('status', 'aktif')->get();

        // Hitung Ringkasan Statistik
        $globalStats = ['hadir' => 0, 'izin' => 0, 'alpha' => 0];
        foreach($peserta as $p) {
            $globalStats['hadir'] += $p->attendance->where('status', 'hadir')->count();
            $globalStats['izin'] += $p->attendance->where('status', 'izin')->count();
            $globalStats['alpha'] += ($bulan == 'all') ? 0 : $p->attendance->where('status', 'alpha')->count();
        }

        $namaInstansi = $institution_id ? (Institution::find($institution_id)->institution_name ?? "Semua") : "Semua Instansi";
        $namaBulan = ($bulan === 'all') ? "Seluruh Bulan" : date('F', mktime(0, 0, 0, (int)$bulan, 1));
        
        // Jika sedang mencari peserta tertentu, tambahkan info di judul laporan
        $subJudul = $search ? "Hasil Pencarian: '$search'" : "Semua Peserta";

        $logoPath = public_path('uploads/img/logo-plnIP.png');
        $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
        
        $hash = Crypt::encryptString($institution_id . '|' . $bulan . '|' . $tahun . '|' . $search);
        $verifyUrl = route('report.verify', ['hash' => $hash]);
        $qrcode = base64_encode(QrCode::format('svg')->size(80)->errorCorrection('H')->generate($verifyUrl));

        $pdf = Pdf::loadView('admin.pdf_rekap', compact(
            'peserta', 'bulan', 'tahun', 'namaBulan', 'namaInstansi', 'qrcode', 'logoData', 'globalStats', 'subJudul'
        ))->setPaper('a4', 'portrait');

        // Penamaan File 
        if ($peserta->count() === 1) {
            $namaSubjek = str_replace(' ', '_', $peserta->first()->user->name);
        } else {
            $namaSubjek = "Semua_Peserta";
        }

        $fileName = "Rekap_Absensi_{$namaSubjek}_{$namaBulan}_{$tahun}.pdf";

        return $pdf->download($fileName);
        }

    // --- 5. Verifikasi publik scan QR ---

    public function verifyReport($hash)
    {
        try {
            $decrypted = Crypt::decryptString($hash);
            [$id, $bulan, $tahun] = explode('|', $decrypted);

            $peserta = Internship::with(['user', 'institution'])->where('internship_id', $id)->first();

            if (!$peserta) return "Dokumen Tidak Dikenali oleh Sistem SIPRAKER.";

            return view('public.verify_report', [
                'nama' => $peserta->user->name,
                'instansi' => $peserta->institution->institution_name,
                'periode_mulai' => $peserta->start_date,
                'periode_selesai' => $peserta->end_date,
                'laporan_bulan' => Carbon::create()->month($bulan)->format('F'),
                'laporan_tahun' => $tahun,
                'verified_at' => now()
            ]);
        } catch (\Exception $e) {
            return "Link Verifikasi Kadaluarsa atau Tidak Valid.";
        }
    }

    // --- 6. Tambah cepat pakai ID GENERATOR ---

    public function storeInstitution(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:institution,institution_name'
        ], [
            'name.unique' => 'Nama instansi ini sudah terdaftar di sistem.'
        ]);
        
        $institution = Institution::create([
            'institution_id' => $this->idService->generateInstitutionId(), 
            'institution_name' => $request->name,
        ]);
        
        return response()->json(['id' => $institution->institution_id, 'name' => $institution->institution_name]);
    }

    public function storeMajor(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:major,major_name'
        ], [
            'name.unique' => 'Nama jurusan ini sudah terdaftar di sistem.'
        ]);
        
        $major = Major::create([
            'major_id' => $this->idService->generateMajorId(),
            'major_name' => $request->name,
        ]);
        
        return response()->json(['id' => $major->major_id, 'name' => $major->major_name]);
    }
}