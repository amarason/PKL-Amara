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

class AdminController extends Controller
{
    // =========================================================================
    // DASHBOARD
    // =========================================================================

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
            'totalPeserta',
            'pesertaAktif',
            'hadirHariIni',
            'izinHariIni',
            'kehadiran'
        ));
    }

    // =========================================================================
    // ABSENSI HARIAN & PERIZINAN
    // =========================================================================

    /**
     * Halaman Absensi Harian
     */
    public function indexAbsensi(Request $request)
    {
        $tanggalDipilih = $request->get('tanggal', Carbon::now()->toDateString());

        $attendance = Attendance::with(['internship.user'])
            ->whereDate('attendance_date', $tanggalDipilih)
            ->get();

        $leaveRequests = LeaveRequest::with(['internship.user'])
            ->where('status', 'menunggu')
            ->get();

        return view('admin.absensi', compact(
            'attendance',
            'leaveRequests',
            'tanggalDipilih'
        ));
    }

    /**
     * KOREKSI STATUS ABSENSI
     */
    public function updateAttendanceStatus(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendance,attendance_id',
            'status' => 'required|in:hadir,izin,alpha',
            'update_reason' => 'required|string|min:5',
        ]);

        try {
            /** @var User $admin */
            $admin = Auth::user();

            // Gunakan where untuk memastikan pencarian primary key string (attendance_id)
            $attendance = Attendance::where('attendance_id', $request->attendance_id)->firstOrFail();

            $attendance->update([
                'status' => $request->status,
                'update_reason' => $request->update_reason,
                // PERBAIKAN: Gunakan $admin->id (Integer) bukan login_id (String)
                'updated_by' => $admin->id, 
            ]);

            return back()->with('success', 'Status absensi berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    /**
     * VERIFIKASI IZIN (SETUJUI / TOLAK)
     */
    public function verifyLeave(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:disetujui,ditolak'
        ]);

        /** @var User $admin */
        $admin = Auth::user();

        try {
            DB::transaction(function () use ($request, $id, $admin) {

                $leave = LeaveRequest::findOrFail($id);

                $leave->update([
                    'status' => $request->action,
                    // PERBAIKAN: Gunakan $admin->id agar sesuai dengan tipe data INT di database
                    'approved_by' => $admin->id,
                    'approved_at' => now(),
                ]);

                // Jika disetujui → otomatis buat/update record absensi menjadi 'izin'
                if ($request->action === 'disetujui') {
                    Attendance::updateOrCreate(
                        [
                            'internship_id' => $leave->internship_id,
                            'attendance_date' => $leave->leave_date,
                        ],
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
            return back()->with('error', 'Gagal memproses izin: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // MANAJEMEN PESERTA PKL
    // =========================================================================

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

        return view('admin.index_peserta', compact('peserta', 'status'));
    }

    public function updateInternshipStatus(Request $request, $id)
    {
        Internship::where('internship_id', $id)->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Status peserta berhasil diperbarui.');
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

            return back()->with('success', 'Peserta berhasil didaftarkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mendaftarkan peserta: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // DATA MASTER
    // =========================================================================

    public function storeInstitution(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:institution,institution_name'
        ]);

        $institution = Institution::create([
            'institution_id' => 'INST-' . strtoupper(substr(uniqid(), -5)),
            'institution_name' => $request->name,
        ]);

        return response()->json($institution);
    }

    public function storeMajor(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:major,major_name'
        ]);

        $major = Major::create([
            'major_id' => 'MJR-' . strtoupper(substr(uniqid(), -5)),
            'major_name' => $request->name,
        ]);

        return response()->json($major);
    }
}