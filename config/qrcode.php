<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QR Code Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk generate QR code yang bisa di-scan dari HP
    | 
    | Untuk localhost:
    | 1. Cari IP address komputer (bukan 127.0.0.1)
    |    - Buka CMD/PowerShell
    |    - Ketik: ipconfig
    |    - Cari IPv4 Address (biasanya 192.168.x.x atau 10.0.x.x)
    | 
    | 2. Set LOCAL_IP di bawah dengan IP address Anda
    | 3. Pastikan HP terhubung ke WiFi yang sama dengan komputer
    |
    */

    // Masukkan IP Address lokal komputer Anda di sini
    // Contoh: '192.168.1.5', '10.0.0.25', dll
    // Biarkan null untuk auto-detect
    'local_ip' => env('QR_LOCAL_IP', null),

    // Port yang digunakan Laravel (default 8000 untuk php artisan serve)
    'port' => env('QR_PORT', 8000),

    // Enable/disable QR code URL rewriting untuk localhost
    'rewrite_localhost' => env('QR_REWRITE_LOCALHOST', true),
];
