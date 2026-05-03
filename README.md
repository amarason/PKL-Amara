# SIPRAKER - Sistem Informasi Praktik Kerja

<p align="center">
  <strong>Sistem Manajemen Presensi Peserta PKL</strong>
</p>

---

## 📋 RINGKASAN

**SIPRAKER** adalah sistem manajemen presensi dan absensi berbasis web untuk program Praktik Kerja Lapangan (PKL) di PT PLN Indonesia Power UBP Semarang. Sistem ini memiliki fitur absensi lokasi, pengelolaan data peserta, dan pembuatan laporan PDF otomatis dengan verifikasi QR Code terenkripsi.

---

## 🔧 Tech Stack

Tabel ini merangkum spesifikasi teknis dan versi perangkat lunak yang digunakan dalam pengembangan sistem SIPRAKER.

| Komponen | Teknologi | Versi |
| :--- | :--- | :--- |
| **Backend Framework** | Laravel | 12.45.1 |
| **PHP Version** | PHP | 8.3.16 |
| **Dependency Manager**| Composer | 2.8.12 |
| **Database** | MySQL | 5.7+ |
| **Frontend Build** | Vite | 7.0+ |
| **CSS Framework** | Tailwind CSS | 4.0+ |
| **QR Code** | simplesoftwareio/simple-qrcode | 4.2+ |
| **PDF Generation** | barryvdh/laravel-dompdf | 3.1+ |
| **Environment** | Local Development | Laragon |

**Konfigurasi Sistem**:
*   **Timezone**: Asia/Jakarta (WIB).
*   **Locale**: id (Bahasa Indonesia).
*   **Storage**: Linked (`public/storage`).

---

## 📂 Struktur Proyek
```text
AbsensiPKL/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Logika utama (Admin, Peserta, Absensi)
│   │   └── Middleware/        # Filter akses (Role Admin/Peserta)
│   ├── Models/                # Struktur data (User, Attendance, Internship, dll)
│   └── Services/              # Logika khusus (Generate ID, Dokumen PDF)
├── resources/
│   ├── views/                 # Tampilan antarmuka (Blade + Tailwind)
│   └── css/                   # Konfigurasi CSS Tailwind
├── routes/
│   └── web.php                # Rute URL aplikasi
├── database/
│   ├── migrations/            # Skema tabel database
│   └── seeders/               # Data awal (Admin default, Hari Libur Nasional)
├── config/
│   └── qrcode.php             # Konfigurasi khusus QR Code & IP Lokal
└── public/
    └── uploads/               # Aset gambar dan logo

---

```
## Panduan Instalasi (Untuk Tim IT Server Produksi)
Ikuti langkah berikut untuk memasang aplikasi SIPRAKER di server resmi perusahaan.

### Persiapan File
- Ekstrak file zip SIPRAKER ke dalam folder web server (contoh: htdocs atau /var/www/html). Buka terminal dan arahkan ke folder tersebut.
### Instal Dependensi: `composer instal --optimize-autoloader --no-dev`
### Setup Environment
Salin file .env.example menjadi .env.
`cp .env.example .env`
Sesuaikan koneksi database di dalam file .env:
Code snippet
DB_DATABASE=nama_database_anda
DB_USERNAME=user_database_anda
DB_PASSWORD=password_database_anda

(Penting: Biarkan konfigurasi QR_LOCAL_IP di baris paling bawah tetap kosong agar sistem otomatis menggunakan nama domain server).
### Generate Key & Storage Link
`php artisan key:generate`
`php artisan storage:link`
### Migrasi Database & Isi Data Awal
Perintah ini otomatis membuat tabel, akun admin utama, dan menyinkronkan libur nasional.
`php artisan migrate:fresh --seed`
### Perizinan Folder (Permissions)
Pastikan web server memiliki izin tulis (write permission) pada folder /storage dan /bootstrap/cache.

## Pengelolaan Akun Admin (Khusus Tim IT)
Karena Dashboard Admin hanya difokuskan pada pengelolaan Peserta PKL, penambahan akun Admin baru dapat dilakukan melalui terminal server menggunakan Laravel Tinker:
- Buka terminal di folder proyek, jalankan perintah: `php artisan tinker`
Masukkan kode berikut (sesuaikan nama, username, dan password):
```bash
App\Models\User::create([
    'name' => 'Nama Admin Baru',
    'login_id' => 'admin_username',
    'password' => bcrypt('password_anda'),
    'role' => 'ROLE_ADMIN'
]);
```
Tekan Enter. Akun baru kini dapat digunakan untuk login ke sistem.

## 💻 Panduan Pengembangan (Local Development)
Gunakan langkah ini jika ingin mengembangkan fitur baru di laptop lokal.

### Install Dependencies
```bash
composer install
npm install
```
### Setup Database & Environment
Sesuaikan koneksi database di .env (Laragon biasanya menggunakan user root tanpa password).
Jalankan migrasi:
`php artisan migrate:fresh --seed`
### Jalankan Server Lokal
```bash
npm run dev
php artisan serve
```
### Testing QR Code dari HP (Lokal)
Isi IP Wi-Fi laptop Anda di file .env:
Code snippet

QR_LOCAL_IP=192.168.1.xxx
QR_PORT=8000
QR_REWRITE_LOCALHOST=true
Jalankan server dengan akses publik:
`php artisan serve --host=0.0.0.0 --port=8000`

🐛 Troubleshooting
- Issue: "SQLSTATE[HY000]: General error: 1030"
Cek sisa ruang penyimpanan (disk space) database server.
Jalankan: `php artisan cache:clear`
- Issue: Gambar/PDF Tidak Ditemukan (404)
Kemungkinan tautan storage terputus. Jalankan kembali:`php artisan storage:link `
- Issue: Hasil Scan QR Code "localhost menolak terhubung" di HP
Pastikan HP dan laptop terhubung di Wi-Fi yang sama.
Pastikan Anda sudah mengatur QR_LOCAL_IP di file .env dan membersihkan cache konfigurasi dengan  `php artisan config:clear `
Gunakan PDF rekap yang baru diunduh setelah IP diatur.
- Issue: Laporan PDF Muncul Pesan "Dokumen Tidak Dikenali"
Hapus cache aplikasi: `php artisan cache:clear` Sistem seringkali menyimpan riwayat error scan sebelumnya selama 1 jam.

Dikembangkan oleh: Amara Putri Soniaji (Universitas Diponegoro)


