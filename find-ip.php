#!/usr/bin/env php
<?php
/**
 * Script untuk membantu mencari IP address lokal dan setup QR code
 * 
 * Cara pakai:
 * 1. Buka cmd/powershell di folder project
 * 2. Jalankan: php find-ip.php
 */

echo "\n" . str_repeat("=", 70) . "\n";
echo "🔍 PENCARIAN IP ADDRESS LOKAL\n";
echo str_repeat("=", 70) . "\n\n";

// Dapatkan IP dari berbagai sumber
$ipAddresses = [];

// Method 1: Server address
if (!empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1') {
    $ipAddresses['SERVER_ADDR'] = $_SERVER['SERVER_ADDR'];
}

// Method 2: Dari shell command (Windows)
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    echo "OS Terdeteksi: Windows\n\n";
    echo "Mencari IPv4 Address...\n";
    
    $output = shell_exec('ipconfig 2>&1');
    
    // Parse ipconfig output
    if (preg_match_all('/IPv4 Address[\s\.]+: ([0-9.]+)/', $output, $matches)) {
        $allIps = array_unique($matches[1]);
        $count = 0;
        
        foreach ($allIps as $ip) {
            // Skip localhost dan link-local
            if ($ip !== '127.0.0.1' && !strpos($ip, '169.254')) {
                $count++;
                $ipAddresses["IP_" . $count] = $ip;
                echo "  ✓ IP #$count: {$ip}\n";
            }
        }
    }
    
    echo "\n";
} else {
    echo "OS Terdeteksi: Unix/Linux/Mac\n\n";
    echo "Mencari IPv4 Address...\n";
    
    $output = shell_exec('ifconfig 2>&1 || ip addr 2>&1');
    
    if (preg_match_all('/inet\s+([0-9.]+)/', $output, $matches)) {
        $allIps = array_unique($matches[1]);
        $count = 0;
        
        foreach ($allIps as $ip) {
            if ($ip !== '127.0.0.1') {
                $count++;
                $ipAddresses["IP_" . $count] = $ip;
                echo "  ✓ IP #$count: {$ip}\n";
            }
        }
    }
    
    echo "\n";
}

// Tampilkan hasil
if (!empty($ipAddresses)) {
    echo str_repeat("-", 70) . "\n";
    echo "📝 IP ADDRESS YANG DITEMUKAN:\n";
    echo str_repeat("-", 70) . "\n\n";
    
    $count = 1;
    foreach ($ipAddresses as $source => $ip) {
        echo "  [$count] {$ip}\n";
        $count++;
    }
    
    echo "\n💡 PILIH IP YANG SESUAI (Biasanya 192.168.x.x atau 10.0.x.x)\n";
    echo "\n🔧 SETUP LANGKAH:\n";
    echo "  1. Buka file .env\n";
    echo "  2. Cari baris QR_LOCAL_IP\n";
    echo "  3. Uncomment dan set dengan IP dari list di atas\n";
    echo "  4. Contoh: QR_LOCAL_IP=192.168.1.5\n";
    echo "\n";
} else {
    echo "⚠️  Tidak ada IP address yang ditemukan\n";
    echo "Coba manual: ipconfig (Windows) atau ifconfig (Mac/Linux)\n";
}

echo "✅ Setup selesai!\n";
echo "📚 Lihat QR_CODE_SETUP.md untuk dokumentasi lengkap\n";
echo str_repeat("=", 70) . "\n\n";
?>
