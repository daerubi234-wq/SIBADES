<?php
/**
 * Authentication & Security Helper
 * SI-PUSBAN: Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa
 */

require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../config/Database.php';

class Auth {
    private static $instance = null;

    /**
     * Start session if not already started
     */
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Set user session
     */
    public static function setUserSession($user) {
        self::startSession();
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'nama_lengkap' => $user['nama_lengkap'],
            'role' => $user['role'],
            'no_wa' => $user['no_wa'],
            'logged_in_at' => time()
        ];
    }

    /**
     * Get current user
     */
    public static function getCurrentUser() {
        self::startSession();
        return $_SESSION['user'] ?? null;
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        self::startSession();
        return isset($_SESSION['user']) && !empty($_SESSION['user']);
    }

    /**
     * Check session timeout
     */
    public static function checkSessionTimeout() {
        self::startSession();
        
        if (!isset($_SESSION['user'])) {
            return true; // Not logged in
        }

        $loginTime = $_SESSION['user']['logged_in_at'] ?? time();
        $elapsed = time() - $loginTime;

        if ($elapsed > Config::SESSION_TIMEOUT) {
            self::logout();
            return true; // Session timed out
        }

        return false; // Session still valid
    }

    /**
     * Logout user
     */
    public static function logout() {
        self::startSession();
        session_destroy();
    }

    /**
     * Check if user has permission
     */
    public static function hasPermission($requiredRole = null) {
        self::startSession();

        if (!self::isLoggedIn()) {
            return false;
        }

        $user = $_SESSION['user'];

        if ($requiredRole === null) {
            return true; // Just check if logged in
        }

        if ($user['role'] === 'admin') {
            return true; // Admin has all permissions
        }

        if (is_array($requiredRole)) {
            return in_array($user['role'], $requiredRole);
        }

        return $user['role'] === $requiredRole;
    }

    /**
     * Require authentication
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: /index.php?page=login');
            exit;
        }
    }

    /**
     * Require specific role
     */
    public static function requireRole($role) {
        self::requireLogin();

        if (!self::hasPermission($role)) {
            http_response_code(403);
            die('Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }
    }

    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken() {
        self::startSession();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken($token) {
        self::startSession();

        if (empty($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== $token) {
            return false;
        }

        return true;
    }

    /**
     * Generate Captcha
     */
    public static function generateCaptcha() {
        $captchaCode = str_pad(random_int(0, 999999), Config::CAPTCHA_LENGTH, '0', STR_PAD_LEFT);
        
        self::startSession();
        $_SESSION['captcha_code'] = $captchaCode;
        $_SESSION['captcha_time'] = time();

        return $captchaCode;
    }

    /**
     * Verify Captcha
     */
    public static function verifyCaptcha($inputCode) {
        self::startSession();

        if (empty($_SESSION['captcha_code'])) {
            return false;
        }

        if ($_SESSION['captcha_code'] !== $inputCode) {
            return false;
        }

        // Check expiry (valid for 10 minutes)
        if (time() - $_SESSION['captcha_time'] > 600) {
            unset($_SESSION['captcha_code']);
            return false;
        }

        // Clear used captcha
        unset($_SESSION['captcha_code']);
        return true;
    }

    /**
     * Generate OTP
     */
    public static function generateOTP($no_wa) {
        $otp = str_pad(random_int(0, 999999), Config::OTP_LENGTH, '0', STR_PAD_LEFT);

        try {
            $database = new Database();
            
            // Clear old OTP
            $database->delete('otp_verification', ['no_wa' => $no_wa]);

            // Save new OTP
            $expiryTime = date('Y-m-d H:i:s', time() + Config::OTP_EXPIRY);
            $otpData = [
                'no_wa' => $no_wa,
                'otp_code' => $otp,
                'is_verified' => false,
                'expires_at' => $expiryTime
            ];

            $database->insert('otp_verification', $otpData);

            return ['success' => true, 'otp' => $otp];
        } catch (Exception $e) {
            error_log("Generate OTP error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal generate OTP'];
        }
    }

    /**
     * Verify OTP
     */
    public static function verifyOTP($no_wa, $otpCode) {
        try {
            $database = new Database();
            $otp = $database->fetchOne(
                "SELECT * FROM otp_verification WHERE no_wa = ? AND otp_code = ? AND expires_at > NOW()",
                [$no_wa, $otpCode]
            );

            if (!$otp) {
                return ['success' => false, 'message' => 'OTP tidak valid atau sudah kadaluarsa'];
            }

            if ($otp['is_verified']) {
                return ['success' => false, 'message' => 'OTP sudah digunakan'];
            }

            // Mark as verified
            $database->update('otp_verification', ['is_verified' => true], ['id' => $otp['id']]);

            return ['success' => true, 'message' => 'OTP berhasil diverifikasi'];
        } catch (Exception $e) {
            error_log("Verify OTP error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal verifikasi OTP'];
        }
    }

    /**
     * Send OTP via WhatsApp using Fonnte service
     */
    public static function sendOTPViaWhatsApp($no_wa, $otp) {
        try {
            require_once __DIR__ . '/../services/WhatsAppService.php';
            
            $whatsapp = new WhatsAppService();
            $result = $whatsapp->sendOTP($no_wa, $otp);

            // Log the result
            if ($result['success']) {
                error_log("OTP {$otp} successfully sent to {$no_wa}");
            } else {
                error_log("OTP send failed for {$no_wa}: " . json_encode($result));
            }

            return $result;
        } catch (Exception $e) {
            error_log("Send OTP WhatsApp error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengirim OTP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sanitize input
     */
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }

        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number (Indonesia format)
     */
    public static function isValidPhone($phone) {
        // Accept formats: 081234567890, +6281234567890, etc.
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        return preg_match('/^(\+62|62|0)?[0-9]{9,12}$/', $phone) === 1;
    }

    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Generate secure token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
}

// Start session on inclusion
Auth::startSession();
?>
