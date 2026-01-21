<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupQrLocalIp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr:setup-ip';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Setup IP address lokal untuk QR code (agar bisa di-scan dari HP)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════════════════');
        $this->info('🔍 SETUP QR CODE LOCAL IP');
        $this->info('═══════════════════════════════════════════════════════════════════');
        $this->newLine();

        // Scan IP address
        $this->info('📡 Mencari IP address lokal...');
        $this->newLine();

        $ipAddresses = $this->findLocalIpAddresses();

        if (empty($ipAddresses)) {
            $this->error('❌ Tidak ada IP address yang ditemukan!');
            $this->warn('Coba manual: ipconfig (Windows) atau ifconfig (Mac/Linux)');
            return;
        }

        $this->info('📝 IP ADDRESS YANG DITEMUKAN:');
        $this->newLine();

        foreach ($ipAddresses as $index => $ip) {
            $this->line("  [" . ($index + 1) . "] {$ip}");
        }

        $this->newLine();
        $selected = $this->choice('Pilih IP address yang ingin digunakan', $ipAddresses);

        $this->info('🔧 Mengkonfigurasi .env...');

        // Update .env file
        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        // Check if QR_LOCAL_IP sudah ada
        if (preg_match('/^QR_LOCAL_IP\s*=\s*/m', $envContent)) {
            // Replace existing
            $envContent = preg_replace(
                '/^QR_LOCAL_IP\s*=\s*.*/m',
                "QR_LOCAL_IP={$selected}",
                $envContent
            );
        } else {
            // Append ke akhir
            if (!preg_match('/^QR_REWRITE_LOCALHOST/m', $envContent)) {
                $envContent .= "\n# QR Code Configuration\n";
                $envContent .= "QR_LOCAL_IP={$selected}\n";
                $envContent .= "QR_PORT=8000\n";
                $envContent .= "QR_REWRITE_LOCALHOST=true\n";
            } else {
                $envContent = preg_replace(
                    '/^(#QR_LOCAL_IP|QR_LOCAL_IP)\s*=\s*.*/m',
                    "QR_LOCAL_IP={$selected}",
                    $envContent
                );
            }
        }

        File::put($envPath, $envContent);

        $this->info('✅ .env berhasil dikonfigurasi!');
        $this->newLine();

        $this->info('═══════════════════════════════════════════════════════════════════');
        $this->line('📋 LANGKAH SELANJUTNYA:');
        $this->newLine();
        $this->line('  1. ✓ IP address lokal sudah dikonfigurasi: ' . $this->components->twoColumnDetail($selected, ''));
        $this->line('  2. Jalankan: php artisan serve');
        $this->line('  3. Pastikan HP terhubung ke WiFi yang sama dengan komputer');
        $this->line('  4. Download PDF rekap dan scan QR code dari HP');
        $this->newLine();

        $this->warn('⚠️  PENTING:');
        $this->line('  - Jika browser cache, clear cache atau buka incognito window');
        $this->line('  - Check firewall jika HP tidak bisa akses URL');
        $this->newLine();

        $this->info('═══════════════════════════════════════════════════════════════════');
        $this->line('📚 Lihat QR_CODE_SETUP.md untuk troubleshooting lengkap');
        $this->newLine();
    }

    /**
     * Find local IP addresses
     */
    private function findLocalIpAddresses()
    {
        $ips = [];

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            $output = shell_exec('ipconfig 2>&1');

            if (preg_match_all('/IPv4 Address[\s\.]+: ([0-9.]+)/', $output, $matches)) {
                foreach ($matches[1] as $ip) {
                    if ($ip !== '127.0.0.1' && !strpos($ip, '169.254')) {
                        $ips[] = $ip;
                    }
                }
            }
        } else {
            // Unix/Linux/Mac
            $output = shell_exec('ifconfig 2>&1 || ip addr 2>&1');

            if (preg_match_all('/inet\s+([0-9.]+)/', $output, $matches)) {
                foreach ($matches[1] as $ip) {
                    if ($ip !== '127.0.0.1' && !preg_match('/^169\.254/', $ip)) {
                        $ips[] = $ip;
                    }
                }
            }
        }

        // Remove duplicates
        return array_values(array_unique($ips));
    }
}
