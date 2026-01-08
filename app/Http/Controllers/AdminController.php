<?php



namespace App\Http\Controllers;



use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Hash;

use Carbon\Carbon;



class AdminController extends Controller

{

    /**

     * Menampilkan Dashboard Admin

     */

    public function index()

    {

        $totalPeserta = DB::table('internship')->count();

        $pesertaAktif = DB::table('internship')->where('status', 'aktif')->count();

       

        $hariIni = Carbon::now()->toDateString();

        $hadirHariIni = DB::table('attendance')

            ->where('attendance_date', $hariIni)

            ->where('status', 'hadir')

            ->count();

           

        $izinHariIni = DB::table('attendance')

            ->where('attendance_date', $hariIni)

            ->where('status', 'izin')

            ->count();



        $kehadiran = DB::table('attendance')

            ->join('internship', 'attendance.internship_id', '=', 'internship.internship_id')

            ->join('users', 'internship.user_id', '=', 'users.id')

            ->select('users.name', 'internship.start_date', 'internship.end_date', 'attendance.*')

            ->orderBy('attendance.created_at', 'desc')

            ->take(5)

            ->get();



        return view('admin.dashboard', compact(

            'totalPeserta', 'pesertaAktif', 'hadirHariIni', 'izinHariIni', 'kehadiran'

        ));

    }



    /**

     * Manajemen Peserta (Search & Filter)

     */

    public function indexPeserta(Request $request)

    {

        $search = $request->get('search');

        $status = $request->get('status', 'aktif');



        $query = DB::table('internship')

            ->join('users', 'internship.user_id', '=', 'users.id')

            ->join('major', 'internship.major_id', '=', 'major.major_id')

            ->join('institution', 'internship.institution_id', '=', 'institution.institution_id')

            ->select('users.name', 'users.login_id', 'major.major_name', 'institution.institution_name', 'internship.*')

            ->where('internship.status', $status);



        if ($search) {

            $query->where(function($q) use ($search) {

                $q->where('users.name', 'like', "%{$search}%")

                  ->orWhere('users.login_id', 'like', "%{$search}%");

            });

        }



        $peserta = $query->orderBy('internship.created_at', 'desc')->get();

        return view('admin.index_peserta', compact('peserta', 'status'));

    }



    /**

     * Ambil data detail untuk modal edit (AJAX)

     */

    public function editPeserta($id)

    {

        $peserta = DB::table('internship')

            ->join('users', 'internship.user_id', '=', 'users.id')

            ->where('internship.internship_id', $id)

            ->first();



        return response()->json($peserta);

    }



    /**

     * Update data peserta

     */

    public function updatePeserta(Request $request, $id)

    {

        $request->validate([

            'name' => 'required',

            'login_id' => 'required',

            'institution_id' => 'required',

            'major_id' => 'required',

            'start_date' => 'required|date',

            'end_date' => 'required|date|after:start_date',

        ]);



        try {

            DB::transaction(function () use ($request, $id) {

                $internship = DB::table('internship')->where('internship_id', $id)->first();



                // 1. Update Tabel Users

                DB::table('users')->where('id', $internship->user_id)->update([

                    'name' => $request->name,

                    'login_id' => $request->login_id,

                    'updated_at' => now()

                ]);



                // 2. Update Tabel Internship

                DB::table('internship')->where('internship_id', $id)->update([

                    'institution_id' => $request->institution_id,

                    'major_id' => $request->major_id,

                    'start_date' => $request->start_date,

                    'end_date' => $request->end_date,

                    'updated_at' => now()

                ]);

            });



            return back()->with('success', 'Data peserta berhasil diperbarui!');

        } catch (\Exception $e) {

            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());

        }

    }



    /**

     * Update Status Peserta (Aktif/Selesai)

     */

    public function updateStatus(Request $request, $id)

    {

        DB::table('internship')->where('internship_id', $id)->update([

            'status' => $request->status,

            'updated_at' => now()

        ]);



        return back()->with('success', 'Status peserta berhasil diperbarui.');

    }



    /**

     * Tampilkan Form Tambah

     */

    public function create()

    {

        $institutions = DB::table('institution')->get();

        $majors = DB::table('major')->get();

        return view('admin.create_peserta', compact('institutions', 'majors'));

    }



    /**

     * Simpan Peserta Baru

     */

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

                $userId = DB::table('users')->insertGetId([

                    'login_id' => $request->login_id,

                    'name' => $request->name,

                    'role_id' => 'ROLE_PESERTA',

                    'password' => Hash::make($request->login_id),

                    'is_active' => 1,

                    'created_at' => now(),

                    'updated_at' => now(),

                ]);



                DB::table('internship')->insert([

                    'internship_id' => 'INT-' . strtoupper(substr(uniqid(), -7)),

                    'user_id' => $userId,

                    'institution_id' => $request->institution_id,

                    'major_id' => $request->major_id,

                    'start_date' => $request->start_date,

                    'end_date' => $request->end_date,

                    'status' => 'aktif',

                    'created_at' => now(),

                ]);

            });



            return back()->with('success', 'Peserta '.$request->name.' berhasil didaftarkan!');



        } catch (\Exception $e) {

            return back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();

        }

    }



    /**

     * AJAX Store Institution

     */

    public function storeInstitution(Request $request)

    {

        $request->validate(['name' => 'required|unique:institution,institution_name']);

        $id = 'INST-' . strtoupper(substr(uniqid(), -5));

        DB::table('institution')->insert(['institution_id' => $id, 'institution_name' => $request->name]);

        return response()->json(['id' => $id, 'name' => $request->name]);

    }



    /**

     * AJAX Store Major

     */

    public function storeMajor(Request $request)

    {

        $request->validate(['name' => 'required|unique:major,major_name']);

        $id = 'MJR-' . strtoupper(substr(uniqid(), -5));

        DB::table('major')->insert(['major_id' => $id, 'major_name' => $request->name]);

        return response()->json(['id' => $id, 'name' => $request->name]);

    }

}