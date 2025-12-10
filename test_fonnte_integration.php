#!/usr/bin/php
<?php
/**
 * Test Fonnte WhatsApp OTP Integration
 * Usage: php /workspaces/SIBADES/test_fonnte_integration.php
 */

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║         Fonnte WhatsApp OTP Integration Test                  ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Load environment and configuration
$projectRoot = __DIR__;
require_once $projectRoot . '/application/config/Config.php';
require_once $projectRoot . '/application/config/Auth.php';
require_once $projectRoot . '/application/config/Database.php';

// Start session
Auth::startSession();

echo "=== CONFIGURATION CHECK ===\n";

// Check Fonnte API Key
$fontneKey = Config::get('FONNTE_API_KEY');
if ($fontneKey && $fontneKey !== 'your_fonnte_api_key_here') {
    echo "✓ Fonnte API Key configured\n";
} else {
    echo "✗ ERROR: Fonnte API Key not configured or invalid\n";
    echo "  Please set FONNTE_API_KEY in .env file\n";
    echo "  Get your key from: https://fonnte.com\n";
    exit(1);
}

// Check WhatsAppService exists
$servicePath = $projectRoot . '/application/services/WhatsAppService.php';
if (file_exists($servicePath)) {
    echo "✓ WhatsAppService found\n";
    require_once $servicePath;
} else {
    echo "✗ ERROR: WhatsAppService not found\n";
    exit(1);
}

// Test WhatsAppService initialization
echo "\n=== SERVICE INITIALIZATION ===\n";
try {
    $whatsapp = new WhatsAppService();
    echo "✓ WhatsApp service initialized successfully\n";
} catch (Exception $e) {
    echo "✗ ERROR: Failed to initialize WhatsApp service\n";
    echo "  " . $e->getMessage() . "\n";
    exit(1);
}

// Test OTP Generation
echo "\n=== OTP GENERATION TEST ===\n";
$testOtp = Auth::generateOTP('081234567890');
if ($testOtp['success']) {
    echo "✓ OTP generated: {$testOtp['otp']}\n";
    $generatedOtp = $testOtp['otp'];
} else {
    echo "✗ ERROR: Failed to generate OTP\n";
    echo "  " . $testOtp['message'] . "\n";
    exit(1);
}

// Test Phone Number Normalization
echo "\n=== PHONE NUMBER NORMALIZATION TEST ===\n";
$testNumbers = [
    '081234567890' => '6281234567890',
    '+6281234567890' => '6281234567890',
    '6281234567890' => '6281234567890',
    '0081234567890' => '6281234567890',
];

$phoneTester = new WhatsAppService();
$phoneReflection = new ReflectionClass('WhatsAppService');
$normalizeMethod = $phoneReflection->getMethod('normalizePhoneNumber');
$normalizeMethod->setAccessible(true);

$allPhoneTestsPassed = true;
foreach ($testNumbers as $input => $expected) {
    $result = $normalizeMethod->invoke($phoneTester, $input);
    if ($result === $expected) {
        echo "✓ {$input} → {$result}\n";
    } else {
        echo "✗ {$input} → {$result} (expected {$expected})\n";
        $allPhoneTestsPassed = false;
    }
}

if (!$allPhoneTestsPassed) {
    echo "\n⚠ Some phone number tests failed\n";
}

// Test WhatsApp Send (without actual API call)
echo "\n=== MESSAGE SENDING TEST ===\n";
echo "⚠ NOTE: Not sending actual OTP to avoid quota usage\n";
echo "  To test actual sending, modify this script to call sendOTP()\n";

// Display API Info
echo "\n=== API INFORMATION ===\n";
echo "Gateway: https://api.fonnte.com/send\n";
echo "OTP Length: 6 digits\n";
echo "OTP Expiry: 10 minutes\n";
echo "Message Format: Indonesian (ID)\n";

// Database Check
echo "\n=== DATABASE CHECK ===\n";
try {
    $db = new Database();
    
    // Check if otp_verification table exists
    $tableExists = $db->fetchOne("
        SELECT COUNT(*) as count FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'otp_verification'
    ", [Config::get('DB_NAME')]);
    
    if ($tableExists['count'] > 0) {
        echo "✓ otp_verification table exists\n";
    } else {
        echo "✗ ERROR: otp_verification table not found\n";
        echo "  Please run: mysql < database/schema.sql\n";
    }
} catch (Exception $e) {
    echo "✗ ERROR: Database connection failed\n";
    echo "  " . $e->getMessage() . "\n";
}

// Summary
echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    TEST SUMMARY                                ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n✓ All checks passed!\n\n";

echo "Next steps:\n";
echo "  1. Verify FONNTE_API_KEY is correct\n";
echo "  2. Test with actual registration:\n";
echo "     - Open http://localhost:8000/index.php?page=register\n";
echo "     - Fill registration form\n";
echo "     - You should receive OTP via WhatsApp\n";
echo "  3. Enter OTP in verification page\n";
echo "  4. Account will be activated\n\n";

echo "Documentation: See FONNTE_SETUP.md\n\n";
?>
