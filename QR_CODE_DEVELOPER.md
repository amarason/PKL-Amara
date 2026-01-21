# 👨‍💻 DEVELOPER DOCUMENTATION - QR CODE LOCAL IP

## Overview

Fitur ini memungkinkan QR code untuk di-scan dari HP meskipun aplikasi berjalan di localhost. URL dalam QR code otomatis di-replace dari `localhost:8000` ke `192.168.x.x:8000`.

## Architecture

### Flow Diagram

```
PDF Export Request
    ↓
AdminController::exportRekapPdf()
    ↓
generateAccessibleUrl('report.verify', [...])
    ├─ Check: localhost?
    ├─ YES → Ganti ke IP lokal + port
    └─ NO → Gunakan URL apa adanya
    ↓
QrCode::generate($verifyUrl)
    ↓
Embedded dalam PDF
```

## Implementation Details

### 1. Configuration File: `config/qrcode.php`

```php
return [
    'local_ip' => env('QR_LOCAL_IP', null),           // IP address lokal
    'port' => env('QR_PORT', 8000),                   // Port server
    'rewrite_localhost' => env('QR_REWRITE_LOCALHOST', true), // Enable/disable feature
];
```

### 2. Environment Variables: `.env`

```dotenv
# QR Code Configuration - Untuk Scan dari HP
QR_LOCAL_IP=192.168.1.5          # Set dengan IP lokal komputer
QR_PORT=8000                      # Port Laravel development server
QR_REWRITE_LOCALHOST=true         # Enable auto URL rewriting
```

### 3. Controller Method: `AdminController`

#### generateAccessibleUrl()

```php
protected function generateAccessibleUrl($routeName, $parameters = [])
{
    $url = route($routeName, $parameters);
    
    // Jika QR rewrite disabled, return URL apa adanya
    if (!config('qrcode.rewrite_localhost')) {
        return $url;
    }
    
    // Jika masih localhost, ganti dengan IP address lokal
    if (strpos($url, 'localhost') !== false || strpos($url, '127.0.0.1') !== false) {
        $ipAddress = config('qrcode.local_ip') ?? $this->getLocalIP();
        $port = config('qrcode.port', 8000);
        $baseUrl = "http://{$ipAddress}:{$port}";
        $url = str_replace(['http://localhost', 'http://127.0.0.1'], $baseUrl, $url);
    }
    
    return $url;
}
```

**Parameters:**
- `$routeName` (string): Named route (e.g., 'report.verify')
- `$parameters` (array): Route parameters

**Returns:**
- `string`: Generated URL accessible from local network

**Example Usage:**
```php
$verifyUrl = $this->generateAccessibleUrl('report.verify', ['hash' => $encryptedHash]);
$qrCode = QrCode::generate($verifyUrl);
```

#### getLocalIP()

```php
protected function getLocalIP()
{
    $host = request()->getHost();
    
    // Jika sudah bukan localhost, gunakan apa adanya
    if ($host !== 'localhost' && $host !== '127.0.0.1') {
        return $host;
    }
    
    // Jika localhost, coba dapatkan IP dari server
    if (!empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1') {
        return $_SERVER['SERVER_ADDR'];
    }
    
    // Fallback: gunakan hostname
    $hostname = gethostname();
    $ip = gethostbyname($hostname);
    
    // Jika masih localhost/127.0.0.1, coba shell command (Windows)
    if ($ip === 'localhost' || $ip === '127.0.0.1' || $ip === $hostname) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = shell_exec('ipconfig /all 2>&1');
            if (preg_match('/IPv4 Address[\s\.]+: ([0-9.]+)/', $output, $matches)) {
                return $matches[1];
            }
        }
        return '127.0.0.1';
    }
    
    return $ip;
}
```

**Returns:**
- `string`: Local IP address (192.168.x.x, 10.0.x.x, etc.)

**Note:** Function ini auto-detect IP, tapi untuk akurasi lebih baik gunakan `.env`

### 4. Usage in exportRekapPdf()

```php
public function exportRekapPdf(Request $request)
{
    // ... data gathering ...
    
    $hash = Crypt::encryptString($institution_id . '|' . $bulan . '|' . $tahun . '|' . $search);
    
    // BEFORE: $verifyUrl = route('report.verify', ['hash' => $hash]);
    // AFTER:
    $verifyUrl = $this->generateAccessibleUrl('report.verify', ['hash' => $hash]);
    
    $qrcode = base64_encode(QrCode::format('svg')->size(80)->errorCorrection('H')->generate($verifyUrl));
    
    // ... rest of PDF generation ...
}
```

## Artisan Command: `SetupQrLocalIp`

### Location
`app/Console/Commands/SetupQrLocalIp.php`

### Usage
```bash
php artisan qr:setup-ip
```

### What it does
1. Auto-scan IP address lokal
2. Presents options untuk user pilih
3. Update file `.env` otomatis
4. Display confirmation message

### Example Output
```
═══════════════════════════════════════════════════════════════════
🔍 SETUP QR CODE LOCAL IP
═══════════════════════════════════════════════════════════════════

📡 Mencari IP address lokal...

📝 IP ADDRESS YANG DITEMUKAN:

  [1] 192.168.1.5
  [2] 10.0.0.25

 Pilih IP address yang ingin digunakan [1] : 1

🔧 Mengkonfigurasi .env...
✅ .env berhasil dikonfigurasi!
```

## Extending the Feature

### 1. Add Custom Port Detection

```php
// In config/qrcode.php
'port' => env('QR_PORT', request()->getPort() ?? 8000),
```

### 2. Support untuk Production

```php
// In config/qrcode.php
'rewrite_localhost' => env('QR_REWRITE_LOCALHOST', env('APP_ENV') === 'local'),
```

### 3. Logging untuk Debug

```php
// In AdminController::generateAccessibleUrl()
Log::debug('QR URL Generated', [
    'original' => $url,
    'modified' => $finalUrl,
    'config' => config('qrcode'),
]);
```

## Security Considerations

1. **Only for Local Network**: Feature ini hanya bekerja untuk lokal network
2. **IP Address di .env**: Simpan `.env` di .gitignore (already done)
3. **URL Encryption**: Verifikasi URL sudah dienkripsi dengan `Crypt::encryptString()`

## Testing

### Manual Test

```php
// In browser
dd(app(AdminController::class)->generateAccessibleUrl('report.verify', ['hash' => 'test']));
```

### Unit Test Example

```php
public function test_generate_accessible_url_replaces_localhost()
{
    $controller = app(AdminController::class);
    
    // Mock config
    Config::set('qrcode.local_ip', '192.168.1.5');
    Config::set('qrcode.port', 8000);
    
    $url = $controller->generateAccessibleUrl('report.verify', ['hash' => 'test']);
    
    $this->assertStringContains('192.168.1.5:8000', $url);
    $this->assertStringNotContains('localhost', $url);
}
```

## Troubleshooting untuk Developer

### Issue: URL still has localhost

**Check:**
```php
// 1. Config file loaded?
config('qrcode.rewrite_localhost') // should be true

// 2. QR_LOCAL_IP set?
config('qrcode.local_ip') // should not be null

// 3. Check actual URL generation
dd($this->generateAccessibleUrl('report.verify', ['hash' => 'test']));
```

### Issue: Auto-detect IP tidak bekerja

**Solution:**
Gunakan explicit IP di `.env`:
```dotenv
QR_LOCAL_IP=192.168.1.5
```

### Issue: Command line cache issue

**Solution:**
```bash
php artisan config:cache
php artisan config:clear
```

## Performance Notes

- String replacement hanya terjadi 1x per PDF export
- Tidak ada performance impact untuk non-localhost environments
- IP auto-detection hanya dipanggil jika `config('qrcode.local_ip')` null

## Files Modified/Created

| File | Type | Purpose |
|------|------|---------|
| `config/qrcode.php` | Created | Config storage |
| `.env` | Modified | Environment variables |
| `app/Http/Controllers/AdminController.php` | Modified | URL generation logic |
| `app/Console/Commands/SetupQrLocalIp.php` | Created | Setup command |
| `find-ip.php` | Created | Helper script |
| `QR_CODE_SETUP.md` | Created | User documentation |
| `QR_CODE_QUICK_START.md` | Created | Quick reference |
| `QR_CODE_VISUAL_GUIDE.md` | Created | Visual instructions |
| `QR_CODE_DEVELOPER.md` | This file | Developer docs |

---

**Last Updated:** January 21, 2026  
**Version:** 1.0  
**Status:** Production Ready
