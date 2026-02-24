<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Internship;
use App\Models\Attendance;
use App\Models\Institution;
use App\Models\Major;
use App\Models\LeaveRequest;
use App\Services\IdGeneratorService;
use App\Services\AttendanceDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
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

    /**
     * Generate URL yang bisa diakses dari HP dalam network yang sama
     * Jika APP_URL = localhost, gunakan IP address lokal
     */
    protected function generateAccessibleUrl($routeName, $parameters = [])
    {
        $url = route($routeName, $parameters);
        
        // Jika QR rewrite disabled, return URL apa adanya
        if (!config('qrcode.rewrite_localhost')) {
            return $url;
        }
        
        // Jika masih localhost, ganti dengan IP address lokal
        if (strpos($url, 'localhost') !== false || strpos($url, '127.0.0.1') !== false) {
            $ipAddress = config('qrcode.local_ip') ?? $this->getLocalIP();
            $port = config('qrcode.port', 8000);
            
            // Parse URL dan replace host
            $parsed = parse_url($url);
            $newUrl = $parsed['scheme'] . '://' . $ipAddress . ':' . $port;
            if (!empty($parsed['path'])) {
                $newUrl .= $parsed['path'];
            }
            if (!empty($parsed['query'])) {
                $newUrl .= '?' . $parsed['query'];
            }
            if (!empty($parsed['fragment'])) {
                $newUrl .= '#' . $parsed['fragment'];
            }
            
            return $newUrl;
        }
        
        return $url;
    }

    /**
     * Dapatkan IP address lokal (192.168.x.x)
     */
    protected function getLocalIP()
    {
        $host = request()->getHost();
        
        // Jika sudah bukan localhost, gunakan apa adanya
        if ($host !== 'localhost' && $host !== '127.0.0.1') {
            return $host;
        }
        
        // Jika localhost, coba dapatkan IP dari server
        if (!empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1') {
            return $_SERVER['SERVER_ADDR'];
        }
        
        // Fallback: gunakan hostname
        $hostname = gethostname();
        $ip = gethostbyname($hostname);
        
        // Jika masih localhost/127.0.0.1, return warning
        if ($ip === 'localhost' || $ip === '127.0.0.1' || $ip === $hostname) {
            // Try to get from system
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows
                $output = shell_exec('ipconfig /all 2>&1');
                if (preg_match('/IPv4 Address[\s\.]+: ([0-9.]+)/', $output, $matches)) {
                    return $matches[1];
                }
            }
            return '127.0.0.1';
        }
        
        return $ip;
    }

    // --- 1. Dashboard ---
    public function index(Request $request)
    {
        $search = $request->get('search');

        $totalPeserta = Internship::count();
        $pesertaAktif = Internship::where('status', 'aktif')->count();
        $hariIni = Carbon::now()->toDateString();

        $hadirHariIni = Attendance::whereDate('attendance_date', $hariIni)
            ->where('status', 'hadir')
            ->count();

        $izinHariIni = Attendance::whereDate('attendance_date', $hariIni)
            ->where('status', 'izin')
            ->count();

        $query = Attendance::with(['internship.user']);

        if ($search) {
            $query->whereHas('internship.user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('login_id', 'like', "%{$search}%"); // Cari berdasarkan Nama atau ID (IP26/...)
            });
        }

        $kehadiran = $query->latest()->take(5)->get();

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

                $internshipId = $this->idService->generateInternshipId($strata);
                
                // Buat Login ID khusus (Format: IP26/Strata/No)
                $loginId = str_replace('PKL', 'IP' . date('y'), $internshipId);

    
                $user = User::create([
                    'login_id' => $loginId,
                    'name' => $request->name,
                    'role_id' => 'ROLE_PESERTA',
                    'password' => Hash::make($loginId),
                    'is_active' => 1,
                ]);

                // Simpan Data Internship (Tetap pakai PKL)
                Internship::create([
                    'internship_id' => $internshipId,
                    'user_id' => $user->id,
                    'institution_id' => $request->institution_id,
                    'major_id' => $request->major_id,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'status' => 'aktif',
                ]);
            });

            return back()->with('success', "Peserta berhasil didaftarkan.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
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
            'institution_id' => 'required',
            'major_id' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            DB::beginTransaction();
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $peserta = Internship::where('internship_id', $id)->firstOrFail();
            $oldId = $peserta->internship_id;

            $inst = Institution::where('institution_id', $request->institution_id)->first();
            $strata = str_contains(strtoupper($inst->institution_name), 'SMK') ? 'SMK' : 'S1';
            
            $segments = explode('/', $oldId);
            $oldStrata = $segments[1] ?? 'S1';
            $finalInternId = $oldId;

            // Jika strata berubah, buat ID PKL baru
            if ($strata !== $oldStrata) {
                $finalInternId = $this->idService->generateInternshipId($strata);
            }

            // Generate Login ID (IP) berdasarkan ID PKL akhir
            $finalLoginId = str_replace('PKL', 'IP' . date('y'), $finalInternId);

            // Update User
            $peserta->user->update([
                'name' => $request->name,
                'login_id' => $finalLoginId,
            ]);

            // Update Internship secara manual
            DB::table('internship')->where('internship_id', $oldId)->update([
                'internship_id' => $finalInternId,
                'institution_id' => $request->institution_id,
                'major_id' => $request->major_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'updated_at' => now()
            ]);

            // Sinkronisasi Tabel Anak (Gunakan nama tabel tunggal: leave_request)
            if ($finalInternId !== $oldId) {
                DB::table('attendance')->where('internship_id', $oldId)->update(['internship_id' => $finalInternId]);
                DB::table('leave_request')->where('internship_id', $oldId)->update(['internship_id' => $finalInternId]);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::commit();

            return back()->with('success', "Data diperbarui. ID Login baru: $finalLoginId");

        } catch (\Exception $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }
    
    public function updateStatus(Request $request, $id)
    {
        Internship::where('internship_id', $id)->update(['status' => $request->status]);
        return back()->with('success', 'Status peserta berhasil diperbarui.');
    }

    public function resetPassword($id)
    {
        try {
            $peserta = Internship::where('internship_id', $id)->with('user')->firstOrFail();
            $user = $peserta->user;
            
            // Reset password ke login_id (password default)
            $user->update([
                'password' => Hash::make($user->login_id)
            ]);

            return back()->with('success', "Password peserta {$user->name} berhasil direset ke password default (ID PKL).");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mereset password: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $peserta = Internship::where('internship_id', $id)->firstOrFail();
                $userId = $peserta->user_id;
                Attendance::where('internship_id', $id)->delete();
                LeaveRequest::where('internship_id', $id)->delete();
                $peserta->delete();
                User::where('id', $userId)->delete();
            });

            return back()->with('success', 'Peserta berhasil dihapus permanen dari database.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
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
        $status = $request->get('status', 'all'); 

        $query = Internship::with(['user', 'institution', 'attendance' => function($query) use ($bulan, $tahun) {
            if ($bulan && $bulan !== 'all') $query->whereMonth('attendance_date', $bulan);
            $query->whereYear('attendance_date', $tahun);
        }]);

        if ($search) {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('login_id', 'like', "%{$search}%");
            });
        }
        if ($institution_id) {
            $query->where('institution_id', $institution_id);
        }
        
        // Logika filter status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $peserta = $query->get(); 
        $institutions = Institution::all();

        return view('admin.rekap_absensi', compact('peserta', 'bulan', 'tahun', 'institutions', 'status'));
    }

    public function exportRekapPdf(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $institution_id = $request->get('institution_id');
        $search = $request->get('search'); 
        $status = $request->get('status', 'all'); 

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
        
        // Logika filter status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $peserta = $query->get();

        // Hitung Ringkasan Statistik
        $globalStats = ['hadir' => 0, 'izin' => 0, 'alpha' => 0];
        
        foreach($peserta as $p) {
            $hCount = $p->attendance->where('status', 'hadir')->count();
            $iCount = $p->attendance->where('status', 'izin')->count();
        
            $totalSeharusnya = $p->getTotalSeharusnyaHadir($bulan == 'all' ? null : $bulan, $tahun);
            
            $globalStats['hadir'] += $hCount;
            $globalStats['izin'] += $iCount;
            
            // Alpha hanya dihitung jika Total Seharusnya > (Hadir + Izin)
            $globalStats['alpha'] += max(0, $totalSeharusnya - ($hCount + $iCount));
        }

        $namaInstansi = $institution_id ? (Institution::find($institution_id)->institution_name ?? "Semua") : "Semua Instansi";
        $namaBulan = ($bulan === 'all') ? "Seluruh Bulan" : date('F', mktime(0, 0, 0, (int)$bulan, 1));
        
        // Jika sedang mencari peserta tertentu, tambahkan info di judul laporan
        $subJudul = $search ? "Hasil Pencarian: '$search'" : "Semua Peserta";

        $logoPath = public_path('uploads/img/logo-plnIP.png');
        $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
        
        $hash = Crypt::encryptString($institution_id . '|' . $bulan . '|' . $tahun . '|' . $search);
        // Gunakan IP address lokal jika di localhost untuk bisa di-scan dari HP
        $verifyUrl = $this->generateAccessibleUrl('report.verify', ['hash' => $hash]);
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

        // --- Simpan PDF ke storage ---
        $pdfContent = $pdf->output();
        $filePath = "attendance_documents/" . date('Y/m/d') . "/" . $fileName;
        Storage::disk('local')->put($filePath, $pdfContent);

        // --- Simpan metadata ke database ---
        $documentService = new AttendanceDocumentService();
        
        try {
            if ($peserta->count() === 1) {
                // Jika filter hanya 1 peserta, simpan untuk peserta itu
                $documentService->saveDocument(
                    internshipId: $peserta->first()->internship_id,
                    filePath: $filePath,
                    qrHash: $hash,
                    periodStart: $bulan !== 'all' ? Carbon::create($tahun, $bulan, 1)->startOfMonth() : null,
                    periodEnd: $bulan !== 'all' ? Carbon::create($tahun, $bulan, 1)->endOfMonth() : null,
                );
            } else {
                // Untuk multiple peserta, simpan untuk setiap peserta
                foreach ($peserta as $p) {
                    $documentService->saveDocument(
                        internshipId: $p->internship_id,
                        filePath: $filePath,
                        qrHash: $hash,
                        periodStart: $bulan !== 'all' ? Carbon::create($tahun, $bulan, 1)->startOfMonth() : null,
                        periodEnd: $bulan !== 'all' ? Carbon::create($tahun, $bulan, 1)->endOfMonth() : null,
                    );
                }
            }
        } catch (\Exception $e) {
            // Log error tapi tetap download PDF
            \Illuminate\Support\Facades\Log::warning('Attendance document save failed: ' . $e->getMessage());
        }

        return $pdf->download($fileName);
        }

    // --- 5. Verifikasi publik scan QR ---

    public function verifyReport($hash)
    {
        try {
            $cacheKey = 'verify_report_' . substr($hash, 0, 20);
            
            $reportData = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(1), function() use ($hash) {
                $decrypted = Crypt::decryptString($hash);
                [$id, $bulan, $tahun] = explode('|', $decrypted);

                $peserta = Internship::select(['internship_id', 'user_id', 'institution_id', 'start_date', 'end_date'])
                    ->with(['user:id,name', 'institution:institution_id,institution_name'])
                    ->where('internship_id', $id)
                    ->first();

                if (!$peserta) {
                    return ['error' => true, 'message' => 'Dokumen Tidak Dikenali oleh Sistem SIPRAKER.'];
                }

                return [
                    'error' => false,
                    'nama' => $peserta->user->name,
                    'instansi' => $peserta->institution->institution_name,
                    'periode_mulai' => $peserta->start_date,
                    'periode_selesai' => $peserta->end_date,
                    'laporan_bulan' => Carbon::create()->month($bulan)->format('F'),
                    'laporan_tahun' => $tahun,
                    'verified_at' => now()
                ];
            });

            if ($reportData['error'] ?? false) {
                return $reportData['message'];
            }

            return view('public.verify_report', $reportData);
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