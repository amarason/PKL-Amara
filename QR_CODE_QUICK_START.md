# 🎯 SOLUSI QR CODE LOCALHOST - QUICK REFERENCE

## 🚀 Setup Cepat (5 Menit)

### ✅ Option 1: Auto Setup (Recommended)
```bash
php artisan qr:setup-ip
```
Command ini akan:
- 🔍 Auto-detect IP address lokal
- 📝 Update file `.env` otomatis
- ✓ Done!

### ✅ Option 2: Manual Setup
```bash
1. Buka CMD/PowerShell
2. Ketik: ipconfig
3. Copy IPv4 Address (192.168.x.x atau 10.0.x.x)
4. Buka .env, uncomment dan set:
   QR_LOCAL_IP=192.168.1.5
   (ganti dengan IP Anda)
```

### ✅ Option 3: Script PHP
```bash
php find-ip.php
```

---

## ✨ Cara Scan dari HP

| Step | Action | Notes |
|------|--------|-------|
| 1️⃣ | Setup IP address (lihat di atas) | Hanya perlu 1 kali |
| 2️⃣ | `php artisan serve` | Jalankan server Laravel |
| 3️⃣ | Hubungkan HP ke WiFi yang sama | ⚠️ PENTING! |
| 4️⃣ | Download PDF rekap | Di admin dashboard |
| 5️⃣ | Scan QR code dari HP | URL auto-generated dengan IP Anda |

---

## 📊 Cara Kerja Teknis

```
┌─────────────────────────────────────────────┐
│   QR CODE GENERATION FLOW                   │
├─────────────────────────────────────────────┤
│                                             │
│  1. Admin export PDF                        │
│     ↓                                       │
│  2. generateAccessibleUrl() dipanggil       │
│     ↓                                       │
│  3. Cek: APP_URL localhost?                 │
│     ├─ YA → Ganti ke IP lokal + port       │
│     └─ TIDAK → Gunakan APP_URL yang ada     │
│     ↓                                       │
│  4. Generate QR Code dengan URL baru        │
│     ↓                                       │
│  5. Embed di PDF                            │
│                                             │
│  Contoh URL di QR:                          │
│  - Before: http://localhost:8000/verifikasi │
│  - After:  http://192.168.1.5:8000/verifikasi │
│                                             │
└─────────────────────────────────────────────┘
```

---

## 🔧 Konfigurasi File

**Location:** `config/qrcode.php`

```php
return [
    'local_ip' => env('QR_LOCAL_IP', null),      // From .env
    'port' => env('QR_PORT', 8000),               // Default port
    'rewrite_localhost' => env('QR_REWRITE_LOCALHOST', true),
];
```

**Location:** `.env`

```dotenv
# QR Code Configuration
QR_LOCAL_IP=192.168.1.5          # SET DENGAN IP KOMPUTER ANDA
QR_PORT=8000                      # Port Laravel
QR_REWRITE_LOCALHOST=true         # Enable auto-replacement
```

---

## ❌ Troubleshooting

### Problem: "ERR_REFUSED_STREAM"
**Solution:**
- Cek firewall Windows: Settings → Firewall → Allow through firewall
- Buka port 8000 untuk PHP atau Laravel

### Problem: "Network Unreachable"
**Solution:**
- Pastikan HP di WiFi yang SAMA dengan komputer
- Cek IP address sudah benar (bukan 127.0.0.1)

### Problem: QR Code masih localhost
**Solution:**
- Clear browser cache Ctrl+Shift+Del
- Verify `.env` sudah disave
- Restart: `php artisan serve`

### Problem: Auto-detect gagal
**Solution:**
- Set manual: `QR_LOCAL_IP=192.168.1.5` di `.env`

---

## 📚 Files Referensi

| File | Purpose |
|------|---------|
| `config/qrcode.php` | Config QR code |
| `.env` | Environment variables |
| `app/Console/Commands/SetupQrLocalIp.php` | Auto setup command |
| `app/Http/Controllers/AdminController.php` | URL generation logic |
| `QR_CODE_SETUP.md` | Detailed documentation |

---

## 🎓 Contoh Praktis

**Skenario:**
Anda ingin scan QR code rekap dari HP di ruangan lain

**Langkah:**
```
1. IP komputer (via ipconfig): 192.168.1.5
2. Edit .env:
   QR_LOCAL_IP=192.168.1.5
3. Jalankan: php artisan serve
4. HP connect ke WiFi rumah (192.168.1.x)
5. Export PDF dari admin
6. Scan QR dengan HP
7. ✓ Bisa buka di browser HP!
```

---

**Questions?** Check documentation atau lihat code di AdminController

