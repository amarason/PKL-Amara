# DATABASE SCHEMA DOCUMENTATION - SIPRAKER

**System**: Sistem Presensi Absensi QR Code (Attendance Tracking System)  
**Database Type**: MySQL 5.7+  
**Encoding**: UTF-8 MB4  

---

## 📋 TABLE OF CONTENTS
1. [Core Tables](#core-tables)
2. [Table Relationships](#table-relationships)
3. [Indexes & Performance](#indexes--performance)
4. [Data Types & Constraints](#data-types--constraints)
5. [Backup & Recovery](#backup--recovery)

---

## 🗂️ CORE TABLES


### 1. **users** - Pengguna Aplikasi
Tabel ini berfungsi untuk menyimpan data seluruh pengguna sistem (mahasiswa dan admin).

| Nama Kolom | Tipe Data | Atribut | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT | PRIMARY KEY, UNSIGNED | ID unik pengguna (Auto-increment). |
| `login_id` | CHAR(30) | UNIQUE, NOT NULL | Username atau ID unik untuk masuk ke sistem. |
| `role_id` | CHAR(30) | INDEX, NOT NULL | Relasi ke tabel peran (Foreign Key). |
| `name` | VARCHAR(255) | NOT NULL | Nama lengkap pengguna. |
| `password` | VARCHAR(255) | NOT NULL | Hash kata sandi untuk keamanan login. |
| `is_active` | TINYINT(1) | DEFAULT 1 | Status akun (1: Aktif, 0: Nonaktif). |
| `remember_token` | VARCHAR(100) | NULLABLE | Token untuk fitur "Ingat Saya". |
| `created_at` | TIMESTAMP | NULLABLE | Waktu pembuatan data. |
| `updated_at` | TIMESTAMP | NULLABLE | Waktu perubahan data terakhir. |

**Indeks**:
*   **PRIMARY KEY**: `id`
*   **UNIQUE**: `login_id`
*   **INDEX**: `role_id`

---

### 2. **role** - Peran Pengguna
Tabel ini mendefinisikan peran yang tersedia di dalam sistem untuk mengatur hak akses pengguna.

| Nama Kolom | Tipe Data | Atribut | Keterangan |
| :--- | :--- | :--- | :--- |
| `role_id` | CHAR(30) | PRIMARY KEY | Identitas unik peran (contoh: ROLE_ADMIN). |
| `role_name` | VARCHAR(30) | NOT NULL | Nama peran yang mudah dibaca manusia. |

**Data Peran Default**:
Berdasarkan data pada tabel `role`, terdapat dua peran utama yang telah didefinisikan:
1.  **ROLE_ADMIN**: Memiliki wewenang sebagai Administrator atau Humas.
2.  **ROLE_PESERTA**: Ditujukan bagi mahasiswa atau peserta PKL.

**Indeks**:
*   **PRIMARY KEY**: `role_id`

---

### 3. **institution** - Instansi/Perusahaan
Tabel ini menyimpan daftar instansi atau organisasi mitra tempat mahasiswa melaksanakan kegiatan.

| Nama Kolom | Tipe Data | Atribut | Keterangan |
| :--- | :--- | :--- | :--- |
| `institution_id` | CHAR(30) | PRIMARY KEY | Identitas unik untuk setiap instansi. |
| `institution_name` | VARCHAR(150) | NOT NULL | Nama resmi universitas atau sekolah. |

**Indeks**:
*   **PRIMARY KEY**: `institution_id`

---

### 4. **major** - Jurusan/Program Studi
Tabel ini menyimpan daftar jurusan atau program studi akademik peserta.

| Nama Kolom | Tipe Data | Atribut | Keterangan |
| :--- | :--- | :--- | :--- |
| `major_id` | CHAR(30) | PRIMARY KEY | Identitas unik untuk setiap jurusan. |
| `major_name` | VARCHAR(150) | NOT NULL | Nama resmi jurusan atau program studi. |

**Indeks**:
*   **PRIMARY KEY**: `major_id`

---

### 5. **internship** - Program Magang
Tabel ini menyimpan data penugasan magang peserta pada instansi terkait.

| Nama Kolom | Tipe Data | Atribut | Keterangan |
| :--- | :--- | :--- | :--- |
| `internship_id` | CHAR(30) | PRIMARY KEY | Identitas unik program magang. |
| `start_date` | DATE | NOT NULL | Tanggal mulai pelaksanaan magang. |
| `end_date` | DATE | NOT NULL | Tanggal berakhir pelaksanaan magang. |
| `status` | ENUM('aktif', 'selesai') | DEFAULT 'aktif' | Status terkini program magang. |
| `user_id` | BIGINT | UNSIGNED, INDEX, NOT NULL | Relasi ke ID pengguna (Mahasiswa). |
| `institution_id` | CHAR(30) | INDEX, NOT NULL | Relasi ke ID instansi tempat magang. |
| `major_id` | CHAR(30) | INDEX, NOT NULL | Relasi ke ID jurusan mahasiswa. |
| `created_at` | TIMESTAMP | NULLABLE | Waktu pembuatan data magang. |
| `updated_at` | TIMESTAMP | NULLABLE | Waktu pembaruan data magang terakhir. |

**Kunci Asing (Foreign Keys)**:
*   `user_id` → `users.id`
*   `institution_id` → `institution.institution_id`
*   `major_id` → `major.major_id`

**Indeks**:
*   **PRIMARY KEY**: `internship_id`
*   **INDEX**: `user_id`, `institution_id`, `major_id`

---

### 6. **attendance** - Presensi Harian
Tabel ini menyimpan data kehadiran harian peserta magang, termasuk waktu masuk, waktu pulang, dan verifikasi foto.

| Nama Kolom | Tipe Data | Atribut | Keterangan |
| :--- | :--- | :--- | :--- |
| `attendance_id` | CHAR(30) | PRIMARY KEY | Identitas unik data presensi. |
| `internship_id` | CHAR(30) | INDEX, NOT NULL | Relasi ke ID program magang. |
| `attendance_date` | DATE | INDEX, NOT NULL | Tanggal pelaksanaan presensi. |
| `check_in_time` | TIME | NOT NULL | Jam masuk peserta. |
| `check_out_time` | TIME | NULLABLE | Jam pulang peserta. |
| `check_in_photo` | VARCHAR(255) | NOT NULL | Path/lokasi penyimpanan foto saat masuk. |
| `check_out_photo` | VARCHAR(255) | NULLABLE | Path/lokasi penyimpanan foto saat pulang. |
| `status` | ENUM('hadir', 'alpha', 'izin') | DEFAULT 'hadir' | Status kehadiran peserta. |
| `updated_by` | BIGINT | UNSIGNED, INDEX, NULLABLE | ID Admin yang melakukan pembaruan data. |
| `update_reason` | TEXT | NULLABLE | Alasan admin melakukan perubahan data. |
| `created_at` | TIMESTAMP | NULLABLE | Waktu pembuatan data presensi. |
| `updated_at` | TIMESTAMP | NULLABLE | Waktu pembaruan data presensi terakhir. |

**Kunci Asing (Foreign Keys)**:
*   `internship_id` → `internship.internship_id`
*   `updated_by` → `users.id`

**Indeks**:
*   **PRIMARY KEY**: `attendance_id`
*   **INDEX**: `internship_id`, `attendance_date`, `updated_by`
*   **COMPOSITE INDEX**: (`internship_id`, `attendance_date`)

---

### 7. **leave_request** - Pengajuan Izin
Tabel ini menyimpan data pengajuan izin atau absensi mahasiswa magang yang disertai dengan dokumen pendukung.

| Nama Kolom | Tipe Data | Atribut | Keterangan |
| :--- | :--- | :--- | :--- |
| `leave_id` | CHAR(30) | PRIMARY KEY | Identitas unik untuk setiap pengajuan izin. |
| `internship_id` | CHAR(30) | INDEX, NOT NULL | Relasi ke ID program magang mahasiswa. |
| `approved_by` | CHAR(30) | INDEX, NULLABLE | Relasi ke login_id admin yang menyetujui izin. |
| `approved_at` | TIMESTAMP | NULLABLE | Waktu penyetujui atau penolakan izin. |
| `leave_date` | DATE | NOT NULL | Tanggal pelaksanaan izin. |
| `reason` | TEXT | NOT NULL | Alasan pengajuan izin. |
| `document_path` | VARCHAR(255) | NULLABLE | Path/lokasi file dokumen pendukung (surat dokter, dll). |
| `status` | ENUM('menunggu', 'disetujui', 'ditolak') | DEFAULT 'menunggu' | Status terkini pengajuan izin. |
| `created_at` | TIMESTAMP | NULLABLE | Waktu pembuatan pengajuan izin. |
| `updated_at` | TIMESTAMP | NULLABLE | Waktu pembaruan data terakhir. |

**Kunci Asing (Foreign Keys)**:
*   `internship_id` → `internship.internship_id`
*   `approved_by` → `users.login_id`

**Indeks**:
*   **PRIMARY KEY**: `leave_id`
*   **INDEX**: `internship_id`, `approved_by`

---

### 8. **attendance_document** - Manajemen Dokumen
Tabel ini menyimpan data dokumen laporan kehadiran yang dihasilkan oleh sistem (generated report) untuk periode tertentu.

| Nama Kolom | Tipe Data | Atribut | Keterangan |
| :--- | :--- | :--- | :--- |
| `document_id` | VARCHAR(36) | PRIMARY KEY | Identitas unik dokumen (format UUID). |
| `internship_id` | CHAR(30) | INDEX, NOT NULL | Relasi ke ID program magang terkait. |
| `generated_by` | BIGINT | UNSIGNED, INDEX, NOT NULL | ID pengguna yang melakukan pembuatan dokumen. |
| `period_start` | DATE | NOT NULL | Tanggal awal periode laporan dalam dokumen. |
| `period_end` | DATE | NOT NULL | Tanggal akhir periode laporan dalam dokumen. |
| `file_path` | VARCHAR(255) | NOT NULL | Lokasi penyimpanan berkas dokumen pada server. |
| `qr_hash` | VARCHAR(255) | NOT NULL | Kode hash unik untuk verifikasi keaslian melalui QR code. |
| `generated_at` | TIMESTAMP | NOT NULL | Waktu dokumen berhasil dibuat oleh sistem. |

**Kunci Asing (Foreign Keys)**:
*   `internship_id` → `internship.internship_id`
*   `generated_by` → `users.id`

**Indeks**:
*   **PRIMARY KEY**: `document_id`
*   **INDEX**: `internship_id`, `generated_by`

---

### 9. **holidays** - Kalender Hari Libur
Tabel ini menyimpan daftar hari libur nasional dan non-kerja yang digunakan untuk memvalidasi jadwal presensi.

| Nama Kolom | Tipe Data | Atribut | Keterangan |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT | PRIMARY KEY, UNSIGNED | ID unik hari libur (Auto-increment). |
| `holiday_date` | DATE | INDEX, NOT NULL | Tanggal pelaksanaan hari libur. |
| `holiday_name` | VARCHAR(255) | NOT NULL | Nama hari libur (contoh: Idul Fitri). |
| `is_national_holiday` | TINYINT(1) | DEFAULT 1 | Penanda libur nasional (1: Ya, 0: Tidak). |
| `created_at` | TIMESTAMP | NULLABLE | Waktu pencatatan data ke sistem. |
| `updated_at` | TIMESTAMP | NULLABLE | Waktu pembaruan data terakhir. |

**Indeks**:
*   **PRIMARY KEY**: `id`
*   **INDEX**: `holiday_date`
---

## 🔗 TABLE RELATIONSHIPS

```
users (1) ──→ (many) internship
users (1) ──→ (many) attendance (updated_by)
users (1) ──→ (many) attendance_document (uploaded_by, verified_by)

role (1) ──→ (many) users

institution (1) ──→ (many) internship
major (1) ──→ (many) internship

internship (1) ──→ (many) attendance
internship (1) ──→ (many) leave_request
internship (1) ──→ (many) attendance_document

attendance_document (1) ──→ (many) leave_request (document via FK)
```

### ER Diagram Summary
```
┌──────────────┐
│    users     │
│ (id, name)   │
└──────────────┘
     ↓ (1:M)
┌──────────────┐      ┌─────────────┐    ┌──────────────┐
│ internship   │──────→ institution  │    │    major     │
│ (id, dates)  │      │ (name, addr) │    │   (name)     │
└──────────────┘      └─────────────┘    └──────────────┘
     ↓ (1:M)
┌──────────────────┐  ┌─────────────────┐  ┌──────────────────┐
│  attendance      │  │  leave_request  │  │ attendance_doc   │
│(date, time, qty) │  │ (date, status)  │  │  (file, path)    │
└──────────────────┘  └─────────────────┘  └──────────────────┘
```

---



### Running Migrations
```bash
# Fresh migration (development only)
php artisan migrate:fresh --seed

# Production migration
php artisan migrate --force
```

---


**Last Updated**: May 3, 2026  
**Version**: 1.0  
**Compatibility**: Laravel 12, MySQL 5.7+, PHP 8.2+
