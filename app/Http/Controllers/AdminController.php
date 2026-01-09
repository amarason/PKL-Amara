<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Internship;
use App\Models\Attendance;
use App\Models\Institution;
use App\Models\Major;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    // --- 1. DASHBOARD ---
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

    // --- 2. MANAJEMEN PESERTA (CRUD) ---

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
        // Menarik data untuk modal edit (jika ditaruh di halaman index)
        $institutions = Institution::all();
        $majors = Major::all();

        return view('admin.index_peserta', compact('peserta', 'status', 'institutions', 'majors'));
    }

    public function create()
    {
        $institutions = Institution::all();
        $majors = Major::all();
        
        // Memanggil file create_peserta.blade.php Anda
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
                $user = User::create([
                    'login_id' => $request->login_id,
                    'name' => $request->name,
                    'role_id' => 'ROLE_PESERTA',
                    'password' => Hash::make($request->login_id),
                    'is_active' => 1,
                ]);

                Internship::create([
                    'internship_id' => 'INT-' . strtoupper(substr(uniqid(), -7)),
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

    // --- 3. ABSENSI & IZIN ---

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
        $admin = Auth::user();

        try {
            DB::transaction(function () use ($request, $id, $admin) {
                $leave = LeaveRequest::findOrFail($id);
                $leave->update([
                    'status' => $request->action,
                    'approved_by' => $admin->id,
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

            return back()->with('success', 'Permohonan izin berhasil diproses.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses izin.');
        }
    }

    // --- 4. REKAP & PDF ---

    public function indexRekap(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $search = $request->get('search');
        $institution_id = $request->get('institution_id');

        $query = Internship::with(['user', 'institution', 'attendance' => function($query) use ($bulan, $tahun) {
            if ($bulan && $bulan !== 'all') {
                $query->whereMonth('attendance_date', $bulan);
            }
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

        $peserta = $query->where('status', 'aktif')->get();
        $institutions = Institution::all();

        return view('admin.rekap_absensi', compact('peserta', 'bulan', 'tahun', 'institutions'));
    }

    public function exportRekapPdf(Request $request)
    {
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $institution_id = $request->get('institution_id');

        $query = Internship::with(['user', 'institution', 'attendance' => function($query) use ($bulan, $tahun) {
            if ($bulan && $bulan !== 'all') {
                $query->whereMonth('attendance_date', $bulan);
            }
            $query->whereYear('attendance_date', $tahun);
        }]);

        $peserta = $query->where('status', 'aktif')->get();
        $namaBulan = ($bulan === 'all') ? "Seluruh Bulan" : date('F', mktime(0, 0, 0, (int)$bulan, 1));

        $pdf = Pdf::loadView('admin.pdf_rekap', compact('peserta', 'bulan', 'tahun', 'namaBulan'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download("Rekap_Absensi_{$namaBulan}_{$tahun}.pdf");
    }

    // --- 5. TAMBAH CEPAT (AJAX) ---

    public function storeInstitution(Request $request)
    {
        $request->validate(['name' => 'required|unique:institution,institution_name']);
        $institution = Institution::create([
            'institution_id' => 'INST-' . strtoupper(substr(uniqid(), -5)),
            'institution_name' => $request->name,
        ]);
        // Return id & name agar sinkron dengan JavaScript di create_peserta.blade.php
        return response()->json(['id' => $institution->institution_id, 'name' => $institution->institution_name]);
    }

    public function storeMajor(Request $request)
    {
        $request->validate(['name' => 'required|unique:major,major_name']);
        $major = Major::create([
            'major_id' => 'MJR-' . strtoupper(substr(uniqid(), -5)),
            'major_name' => $request->name,
        ]);
        // Return id & name agar sinkron dengan JavaScript di create_peserta.blade.php
        return response()->json(['id' => $major->major_id, 'name' => $major->major_name]);
    }
}