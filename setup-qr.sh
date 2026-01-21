#!/usr/bin/env bash
# Quick setup script untuk Linux/Mac users
# Cara pakai: chmod +x setup-qr.sh && ./setup-qr.sh

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "🔍 QR CODE LOCAL IP SETUP SCRIPT"
echo "════════════════════════════════════════════════════════════════"
echo ""

# Get local IP
echo "📡 Detecting local IP addresses..."
echo ""

if [[ "$OSTYPE" == "linux-gnu"* ]]; then
    # Linux
    IP=$(hostname -I | awk '{print $1}')
    echo "OS: Linux"
elif [[ "$OSTYPE" == "darwin"* ]]; then
    # Mac
    IP=$(ifconfig | grep "inet " | grep -v 127.0.0.1 | awk '{print $2}' | head -1)
    echo "OS: macOS"
else
    echo "OS: Unknown (not Linux or Mac)"
    IP=""
fi

echo ""
if [ -z "$IP" ]; then
    echo "❌ Could not auto-detect IP address"
    echo "📝 Please manually run: ipconfig (Windows) or ifconfig (Mac/Linux)"
else
    echo "📝 Found IP address: $IP"
    echo ""
    echo "🔧 Setup steps:"
    echo "  1. Open .env file"
    echo "  2. Uncomment/set: QR_LOCAL_IP=$IP"
    echo "  3. Save file"
    echo "  4. Run: php artisan serve"
    echo ""
    echo "✅ Ready to scan QR code from HP!"
fi

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "📚 For more info: cat QR_CODE_SETUP.md"
echo "════════════════════════════════════════════════════════════════"
echo ""
