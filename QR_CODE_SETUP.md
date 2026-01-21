# 📱 Setup QR Code Scan dari HP (Localhost)

Fitur QR code di rekap dokumen sudah dikonfigurasi agar bisa di-scan dari HP meskipun masih localhost. Berikut cara setup-nya:

## 🔧 Cara Setup

### 1. Cari IP Address Lokal Komputer 

#### Di Windows (CMD/PowerShell):
```powershell
ipconfig
```

Cari baris **"IPv4 Address"** yang dimulai dengan `192.168.x.x` atau `10.0.x.x`

**Contoh output:**
```
Ethernet adapter Ethernet:
   ...
   IPv4 Address. . . . . . . . . . : 192.168.1.5
   ...
```

#### Di Mac/Linux (Terminal):
```bash
ifconfig
```

Cari baris **"inet"** dengan alamat `192.168.x.x` atau `10.0.x.x`

### 2. Konfigurasi di File `.env`

Buka file `.env` di root project:
```dotenv
# Masukkan IP address komputer lokal (ganti 192.168.1.5 dengan IP lokal)
QR_LOCAL_IP=192.168.1.5
QR_PORT=8000
QR_REWRITE_LOCALHOST=true
```

### 3. Jalankan Laravel Development Server

```bash
php artisan serve
```

Atau jika pakai Laragon, gunakan URL yang sudah di-setup.

### 4. Mulai Scan QR Code dari HP

✅ Pastikan **HP terhubung ke WiFi yang sama** dengan komputer  
✅ Buka aplikasi camera atau QR code scanner di HP  
✅ Arahkan ke QR code di PDF laporan  
✅ Klik link yang muncul  

## 🔍 Troubleshooting

### ❌ QR Code Tidak Bisa Di-scan dari HP

**Masalah:** HP tidak bisa akses URL
- [ ] Pastikan HP terhubung ke WiFi yang sama dengan komputer
- [ ] Pastikan IP address di `.env` QR_LOCAL_IP sudah benar
- [ ] Coba akses URL langsung di HP browser: `http://192.168.1.5:8000/login`
- [ ] Cek Windows Firewall, mungkin port 8000 diblokir

**Solusi:**
1. Matikan Firewall sementara untuk testing
2. Atau buka Port 8000 di Firewall Windows:
   - Settings → Privacy & Security → Windows Defender Firewall → Allow an app through firewall
   - Tambahkan PHP atau Laravel ke list

### ❌ Dapat Pesan "Connection Refused" atau "Timeout"

- Pastikan Laravel server sedang berjalan
- Pastikan `php artisan serve` sedang running
- Cek apakah port 8000 tidak digunakan aplikasi lain

### ❌ IP Address Tidak Otomatis Terdeteksi

Jika auto-detect gagal, manual set IP address di `.env`:
```dotenv
QR_LOCAL_IP=192.168.1.5
```

## 🚀 Alternative: Gunakan NGROK (Untuk Public Internet)

Jika ingin akses dari internet publik (bukan hanya local network):

1. Download & install [NGROK](https://ngrok.com)
2. Jalankan:
   ```bash
   ngrok http 8000
   ```
3. Copy URL publik yang digenerate NGROK
4. Set di `.env`:
   ```dotenv
   APP_URL=https://xxxxx.ngrok.io
   QR_REWRITE_LOCALHOST=false
   ```

## 📝 Technical Details

- QR code di-generate dengan base64 SVG format
- URL dalam QR code otomatis di-replace dari `localhost:8000` ke `192.168.x.x:8000`
- Fitur ini hanya aktif jika `QR_REWRITE_LOCALHOST=true`
- Config disimpan di `config/qrcode.php`

---

**Pertanyaan?** Lihat bagian AdminController `generateAccessibleUrl()` method.
