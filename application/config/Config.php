<?php
/**
 * Application Configuration
 * SI-PUSBAN: Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa
 */

// Load .env file
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue; // Skip comments
        if (strpos($line, '=') === false) continue;
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        if (!empty($key) && !empty($value)) {
            putenv("$key=$value");
        }
    }
}

class Config {
    // Application Settings
    public const APP_NAME = 'SI-PUSBAN';
    public const APP_VERSION = '1.0.0';
    public const APP_DEBUG = true;
    public const APP_TIMEZONE = 'Asia/Jakarta';

    // Database Settings (from .env)
    public const DB_HOST = 'DB_HOST';
    public const DB_USER = 'DB_USER';
    public const DB_PASS = 'DB_PASS';
    public const DB_NAME = 'DB_NAME';

    // Desa Information
    public const DESA_NAME = 'DESA_NAME';
    public const KECAMATAN = 'KECAMATAN';
    public const KABUPATEN = 'KABUPATEN';
    public const PROVINSI = 'PROVINSI';
    public const TAHUN_ANGGARAN = 'TAHUN_ANGGARAN';

    // Security
    public const SESSION_TIMEOUT = 3600; // 1 hour in seconds
    public const PASSWORD_MIN_LENGTH = 8;
    public const MAX_LOGIN_ATTEMPTS = 5;
    public const CAPTCHA_LENGTH = 6;
    public const OTP_LENGTH = 6;
    public const OTP_EXPIRY = 600; // 10 minutes

    // File Upload Settings
    public const MAX_FILE_SIZE = 5242880; // 5MB
    public const ALLOWED_IMAGE_TYPES = ['jpg', 'jpeg', 'png', 'gif'];
    public const ALLOWED_DOCUMENT_TYPES = ['pdf', 'doc', 'docx'];
    public const IMAGE_QUALITY = 80; // Compression quality (0-100)
    public const UPLOAD_PATH = '/storage/uploads/';

    // Cloud Storage Settings
    public const CLOUD_PROVIDER = 'CLOUD_PROVIDER'; // google_drive, mega, etc.
    public const CLOUD_API_KEY = 'CLOUD_API_KEY';
    public const CLOUD_FOLDER_ID = 'CLOUD_FOLDER_ID';

    // WhatsApp Gateway - Fonnte (Free)
    // Get API key from: https://fonnte.com (free account with 20 messages/day)
    public const FONNTE_ENABLED = true;
    public const FONNTE_API_KEY = 'FONNTE_API_KEY';
    public const FONNTE_GATEWAY_URL = 'https://api.fonnte.com/send';

    // Legacy WhatsApp Settings (deprecated)
    public const WHATSAPP_ENABLED = true;
    public const WHATSAPP_API_KEY = 'WHATSAPP_API_KEY';
    public const WHATSAPP_GATEWAY_URL = 'WHATSAPP_GATEWAY_URL';

    // Jenis Bantuan (Bansos Types)
    public const JENIS_BANTUAN = [
        'KKS' => 'Kartu Keluarga Sejahtera',
        'PKH' => 'Program Keluarga Harapan',
        'PBI' => 'Penerima Bantuan Iuran',
        'BLT' => 'Bantuan Langsung Tunai',
        'UMKM' => 'Dukungan UMKM',
        'PENDIDIKAN' => 'Beasiswa Pendidikan',
        'KESEHATAN' => 'Bantuan Kesehatan',
        'LAINNYA' => 'Lainnya'
    ];

    // Status Enum
    public const USULAN_STATUS = [
        'Antri' => 'Menunggu Verifikasi',
        'Ditinjau' => 'Sedang Ditinjau',
        'Disetujui' => 'Disetujui',
        'Ditolak' => 'Ditolak'
    ];

    // Role-Based Access Control (RBAC)
    public const ROLES = [
        'user' => ['create_usulan', 'read_own_usulan'],
        'operator' => ['create_usulan', 'read_usulan', 'update_usulan', 'delete_usulan', 'verify_usulan'],
        'admin' => ['all']
    ];

    // Get env variable or default
    public static function get($key, $default = null) {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}

// Set timezone
date_default_timezone_set(Config::APP_TIMEZONE);
?>
