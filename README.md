# SIPRAKER - Sistem Informasi Praktik Kerja

<p align="center">
  <strong>Sistem Manajemen Presensi Peserta PKL</strong>
</p>

---

## 📋 Ikhtisar

**SIPRAKER** adalah sistem manajemen presensi dan absensi berbasis web untuk program Praktik Kerja Lapangan (PKL).

---

## 🔧 Tech Stack

Tabel ini merangkum spesifikasi teknis dan versi perangkat lunak yang digunakan dalam pengembangan sistem **SIPRAKER**.

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
*   **Debug Mode**: Enabled (Lingkungan Pengembangan).
*   **Storage**: Linked (`public/storage` sudah terhubung).

---

## 📂 Struktur Proyek

```
AbsensiPKL/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php         # Authentication logic
│   │   │   ├── AdminController.php        # Admin dashboard & management
│   │   │   └── UserController.php         # User settings
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php                       # User model
│   │   ├── Attendance.php                 # Attendance records
│   │   ├── LeaveRequest.php               # Leave requests
│   │   ├── Internship.php                 # Internship data
│   │   └── ...
│   └── Services/
│       ├── AttendanceDocumentService.php  # Document handling
│       └── IdGeneratorService.php         # ID generation
├── resources/
│   ├── views/                             # Blade templates
│   ├── css/                               # Tailwind CSS
│   └── js/                                # JavaScript assets
├── routes/
│   ├── web.php                            # Web routes
│   └── console.php                        # Console commands
├── database/
│   ├── migrations/                        # Database migrations
│   └── seeders/                           # Database seeders
├── config/
│   ├── app.php                            # App configuration
│   ├── auth.php                           # Auth configuration
│   └── database.php                       # Database configuration
├── tests/                                 # Test cases
├── composer.json                          # PHP dependencies
├── package.json                           # Node.js dependencies
├── DEPLOYMENT.md                          # Deployment guide
└── DATABASE.md                            # Database schema documentation
```

---

## 🚀 Quick Start (Development)

### Prasyarat
- PHP 8.2+
- Composer
- Node.js 16+
- MySQL 5.7+

### Instalasi

1. **Unduh proyek**
```bash
# Unduh SIPRAKER.zip dari Google Drive
# Ekstrak ke lokasi yang Anda inginkan
unzip SIPRAKER.zip
cd sipraker
````

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Setup environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database** (edit `.env`)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipraker_db
DB_USERNAME=root
DB_PASSWORD=
```

5. **Run migrations**
```bash
php artisan migrate
```

6. **Build assets**
```bash
npm run build  # or npm run dev for development
```

7. **Start development server**
```bash
php artisan serve
```

Access the application at `http://localhost:8000`

---


## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AuthTest.php

# Run with coverage
php artisan test --coverage
```

---

## 📋 Common Tasks

### Create Admin User
```bash
php artisan tinker
>>> App\Models\User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'login_id' => 'admin', 'password' => bcrypt('password'), 'role' => 'ROLE_ADMIN'])
```

### Reset Database (Development)
```bash
php artisan migrate:fresh --seed
```

### Generate PDF Report
```bash
# PDFs are generated automatically via barryvdh/laravel-dompdf
# See controllers for usage examples
```

---

## 🐛 Troubleshooting

### Issue: "SQLSTATE[HY000]: General error: 1030"
- Check database disk space
- Run: `php artisan cache:clear`

### Issue: "Class not found" errors
- Run: `composer dump-autoload`
- Run: `php artisan clear-compiled`

### Issue: QR Code not displaying
- Check `config/qrcode.php` is configured
- Verify QR library installed: `composer show simplesoftwareio/simple-qrcode`

### Issue: File upload fails
- Check `storage/app` folder permissions
- Check disk space available
- Verify `FILESYSTEM_DISK=local` in `.env`

---

