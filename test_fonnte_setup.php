#!/usr/bin/php
<?php
/**
 * Test Fonnte WhatsApp OTP Integration
 * Usage: php /workspaces/SIBADES/test_fonnte_setup.php
 */

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║         Fonnte WhatsApp OTP Integration Status                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Load environment configuration (without session for CLI)
$projectRoot = __DIR__;
$envFile = $projectRoot . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        if (!empty($key) && !empty($value)) {
            putenv("$key=$value");
        }
    }
}

require_once $projectRoot . '/application/config/Config.php';
require_once $projectRoot . '/application/config/Database.php';

echo "=== CONFIGURATION CHECK ===\n";

// Check Fonnte API Key
$fontneKey = Config::get('FONNTE_API_KEY');
if ($fontneKey && $fontneKey !== 'your_fonnte_api_key_here') {
    echo "✓ Fonnte API Key configured\n";
    $apiConfigured = true;
} else {
    echo "⚠ Fonnte API Key not configured\n";
    echo "  Status: Ready to configure\n";
    $apiConfigured = false;
}

// Check WhatsAppService exists
$servicePath = $projectRoot . '/application/services/WhatsAppService.php';
if (file_exists($servicePath)) {
    echo "✓ WhatsAppService found\n";
} else {
    echo "✗ ERROR: WhatsAppService not found\n";
    exit(1);
}

// Database Check
echo "\n=== DATABASE CHECK ===\n";
try {
    $db = new Database();
    
    // Check if otp_verification table exists
    $tableExists = $db->fetchOne("
        SELECT COUNT(*) as count FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'otp_verification'
    ", [Config::get('DB_NAME')]);
    
    if ($tableExists && $tableExists['count'] > 0) {
        echo "✓ otp_verification table exists in database\n";
    } else {
        echo "⚠ otp_verification table not found\n";
        echo "  Fix: Import schema: mysql < database/schema.sql\n";
    }
} catch (Exception $e) {
    echo "⚠ Database check skipped: " . $e->getMessage() . "\n";
}

// Show Implementation Files
echo "\n=== IMPLEMENTATION FILES ===\n";
$files = [
    '/application/services/WhatsAppService.php' => 'Fonnte API integration service',
    '/application/config/Auth.php' => 'Updated with WhatsApp sender',
    '/application/config/Config.php' => 'Fonnte configuration constants',
    '/.env' => 'FONNTE_API_KEY configuration',
    '/FONNTE_SETUP.md' => 'Complete setup guide'
];

foreach ($files as $file => $description) {
    $fullPath = $projectRoot . $file;
    if (file_exists($fullPath)) {
        echo "✓ $file\n";
        echo "  └─ $description\n";
    }
}

// Flow Diagram
echo "\n=== REGISTRATION FLOW WITH OTP ===\n";
echo "┌─ User fills registration form\n";
echo "│  ├─ Username, Password, Full Name, WhatsApp Number\n";
echo "│  └─ CSRF Token + Captcha\n";
echo "├─ Form submitted to /api/auth/register\n";
echo "├─ CSRF & Captcha validated\n";
echo "├─ User saved to database\n";
echo "├─ 6-digit OTP generated\n";
echo "├─ OTP saved to otp_verification table\n";
echo "├─ OTP sent via Fonnte WhatsApp API\n";
echo "│  └─ Delivery: ~2-3 seconds\n";
echo "├─ User receives OTP on WhatsApp\n";
echo "├─ User enters OTP in verification page\n";
echo "├─ OTP validated against database\n";
echo "├─ User account activated\n";
echo "└─ Redirect to login page\n";

// Summary
echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    SETUP STATUS                                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

if ($apiConfigured) {
    echo "✓ Fonnte integration is READY\n\n";
    echo "Next: Test by registering new account\n";
    echo "  - Open http://localhost:8000/index.php?page=register\n";
    echo "  - Fill registration form\n";
    echo "  - OTP will arrive on WhatsApp\n";
} else {
    echo "⚠ Fonnte integration needs configuration\n\n";
    echo "SETUP STEPS:\n";
    echo "1. Visit https://fonnte.com and create free account\n";
    echo "2. Copy your API Key from dashboard\n";
    echo "3. Edit .env file:\n";
    echo "   FONNTE_API_KEY=your_actual_api_key_here\n";
    echo "4. Save file and restart PHP server\n";
    echo "5. Test by registering new account\n\n";
    echo "Free Account: 20 WhatsApp messages/day\n";
}

echo "\nDocumentation: See FONNTE_SETUP.md\n";
echo "Code: application/services/WhatsAppService.php\n\n";
?>
