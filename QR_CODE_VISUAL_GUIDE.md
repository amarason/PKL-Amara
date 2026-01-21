# 🖼️ VISUAL GUIDE - QR CODE LOCALHOST SETUP

## Langkah 1: Cari IP Address

### Windows - Buka Command Prompt / PowerShell
```
┌──────────────────────────────────────────────────────┐
│ C:\Users\YourName>                                   │
│                                                       │
│ > ipconfig                                           │
│                                                       │
│ Ethernet adapter Ethernet:                           │
│    ...                                               │
│    IPv4 Address. . . . . . . . . . : 192.168.1.5 ← │ CATAT IP INI!
│    Subnet Mask . . . . . . . . . . : 255.255.255.0   │
│    ...                                               │
└──────────────────────────────────────────────────────┘
```

### Mac/Linux - Buka Terminal
```
┌──────────────────────────────────────────────────────┐
│ $ ifconfig                                           │
│                                                       │
│ en0: flags=...                                       │
│     inet 192.168.1.5 netmask 0xffffff00 ← CATAT INI!│
│     ...                                              │
│                                                       │
│ lo0: flags=...                                       │
│     inet 127.0.0.1 netmask 0xff000000 ← JANGAN INI! │
│                                                       │
└──────────────────────────────────────────────────────┘
```

---

## Langkah 2: Setup via Command

### Pilih Salah Satu:

#### 🔹 Method 1: Auto Setup (TERMUDAH)
```
> php artisan qr:setup-ip

═══════════════════════════════════════════════════════════════════
🔍 SETUP QR CODE LOCAL IP
═══════════════════════════════════════════════════════════════════

📡 Mencari IP address lokal...

📝 IP ADDRESS YANG DITEMUKAN:

  [1] 192.168.1.5
  [2] 192.168.1.100

 Which IP address would you like to use? [1] : 1

🔧 Mengkonfigurasi .env...
✅ .env berhasil dikonfigurasi!

═══════════════════════════════════════════════════════════════════
📋 LANGKAH SELANJUTNYA:
  1. ✓ IP address lokal sudah dikonfigurasi: 192.168.1.5
  2. Jalankan: php artisan serve
  3. Pastikan HP terhubung ke WiFi yang sama dengan komputer
  4. Download PDF rekap dan scan QR code dari HP
═══════════════════════════════════════════════════════════════════
```

#### 🔹 Method 2: Manual Edit `.env`
```
┌──────────────────────────────────────────────────┐
│ Buka file: .env (di root folder project)         │
├──────────────────────────────────────────────────┤
│                                                  │
│ Cari bagian ini (di bawah):                      │
│                                                  │
│ # QR Code Configuration                          │
│ #QR_LOCAL_IP=192.168.1.1    ← UNCOMMENT & EDIT  │
│ QR_PORT=8000                                     │
│ QR_REWRITE_LOCALHOST=true                        │
│                                                  │
│ Ubah menjadi:                                    │
│                                                  │
│ # QR Code Configuration                          │
│ QR_LOCAL_IP=192.168.1.5     ← PASTE IP KOMPUTER LOKAL   │
│ QR_PORT=8000                                     │
│ QR_REWRITE_LOCALHOST=true                        │
│                                                  │
│ SAVE FILE!                                       │
│                                                  │
└──────────────────────────────────────────────────┘
```

---

## Langkah 3: Jalankan Server

```
┌──────────────────────────────────────────────────┐
│ > php artisan serve                              │
│                                                  │
│ INFO  Server running on [http://127.0.0.1:8000] │
│                                                  │
│ ✓ Server sudah berjalan                          │
│                                                  │
└──────────────────────────────────────────────────┘
```

---

## Langkah 4: Scan dari HP

### Setup Network

```
┌─────────────────────────┐
│   COMPUTER              │  IP: 192.168.1.5
│   (Running Server)      │  Port: 8000
│                         │
│   WiFi: MyHomeNetwork   │
└─────────────────────────┘
          │ WiFi
          ├──────────┐
          │          │
    ┌─────────┐   ┌─────────┐
    │  HP #1  │   │  HP #2  │
    │ (Same   │   │ (Same   │
    │ Network)│   │ Network)│
    └─────────┘   └─────────┘
    
    ✓ Bisa scan QR code!
```

### Scan Process

```
┌──────────────────────────────────────────────────┐
│  📱 HP - Open Camera                             │
├──────────────────────────────────────────────────┤
│                                                  │
│     ╔═══════════════════════════════╗           │
│     ║                               ║           │
│     ║   Arahkan ke QR Code di PDF   ║           │
│     ║                               ║           │
│     ║   ┌─────────────────────┐     ║           │
│     ║   │ █ █ █ █ █ █ █       │     ║           │
│     ║   │ █         █         │     ║           │
│     ║   │ █   QR    █   Link  │     ║           │
│     ║   │ █  Code   █  Appear │     ║           │
│     ║   │ █         █         │     ║           │
│     ║   │ █ █ █ █ █ █ █       │     ║           │
│     ║   │                     │     ║           │
│     ║   └─────────────────────┘     ║           │
│     ║                               ║           │
│     ╚═══════════════════════════════╝           │
│                                                  │
│     Link otomatis muncul:                        │
│     http://192.168.1.5:8000/verifikasi/...      │
│                                                  │
│     ✓ Klik link → Bisa akses! 🎉                │
│                                                  │
└──────────────────────────────────────────────────┘
```

---

## Diagram Alur Complete

```
START
  │
  ├─ 1. ipconfig → Dapatkan IP (192.168.1.5)
  │
  ├─ 2. php artisan qr:setup-ip → Setup otomatis
  │                    │
  │         ┌──────────┴──────────┐
  │         │                     │
  │    Manual Setup          Auto Setup
  │    Edit .env             ✓ Dipilih IP
  │    QR_LOCAL_IP=...       ✓ .env updated
  │
  ├─ 3. php artisan serve → Jalankan server
  │
  ├─ 4. Admin export PDF → PDF berisi QR
  │
  ├─ 5. Scan QR dari HP → HP di WiFi sama
  │         │
  │         ├─ QR Code berisi:
  │         │  http://192.168.1.5:8000/verifikasi/...
  │         │
  │         └─ ✓ HP bisa akses!
  │
  └─ 6. Verifikasi dokumen → Selesai! 🎉
```

---

## ✅ Checklist Akhir

```
┌─────────────────────────────────────────────────┐
│ BEFORE YOU SCAN:                                │
├─────────────────────────────────────────────────┤
│                                                 │
│ ☑ IP address sudah setup di .env               │
│ ☑ Server running: php artisan serve            │
│ ☑ HP terhubung ke WiFi SAMA dengan komputer    │
│ ☑ PDF sudah di-export dari admin               │
│ ☑ Browser cache sudah di-clear (optional)      │
│                                                 │
│ THEN:                                           │
│ ☑ Buka Camera di HP                            │
│ ☑ Arahkan ke QR code di PDF                    │
│ ☑ Klik link yang muncul                        │
│ ☑ ✓ DONE!                                      │
│                                                 │
└─────────────────────────────────────────────────┘
```

---

## 🚨 Common Mistakes

```
❌ JANGAN:
   1. Copy IP 127.0.0.1 ← Ini localhost!
      Gunakan: 192.168.x.x atau 10.0.x.x
   
   2. HP di WiFi berbeda
      ✓ Harus WiFi YANG SAMA
   
   3. Lupa save file .env
      ✓ Setelah edit, HARUS SAVE
   
   4. Firewall mengblokir port 8000
      ✓ Whitelist port 8000 di firewall

✓ LAKUKAN:
   1. IP: 192.168.1.5 ← Format ini OK
   2. HP & Komputer: 1 WiFi
   3. Save file
   4. Allow firewall
```

---

**Butuh bantuan?** Lihat QR_CODE_SETUP.md untuk troubleshooting detail
