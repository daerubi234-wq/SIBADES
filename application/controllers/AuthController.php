<?php
/**
 * Authentication Controller
 * SI-PUSBAN: Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa
 */

require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Response.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Show registration form
     */
    public function registrationForm() {
        // Check if already logged in
        if (Auth::isLoggedIn()) {
            header('Location: /index.php?page=dashboard');
            exit;
        }

        return [
            'page' => 'register',
            'title' => 'Registrasi - SI-PUSBAN',
            'captcha' => Auth::generateCaptcha()
        ];
    }

    /**
     * Process registration
     */
    public function register() {
        // Check CSRF token
        $token = $_POST['csrf_token'] ?? null;
        if (!Auth::verifyCsrfToken($token)) {
            Response::error('Token tidak valid', null, 403);
        }

        // Verify captcha
        $captcha = $_POST['captcha'] ?? null;
        if (!Auth::verifyCaptcha($captcha)) {
            Response::error('Captcha tidak sesuai', null, 400);
        }

        // Sanitize input
        $data = [
            'username' => Auth::sanitize($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'nama_lengkap' => Auth::sanitize($_POST['nama_lengkap'] ?? ''),
            'no_wa' => Auth::sanitize($_POST['no_wa'] ?? ''),
        ];

        // Validate input
        if (empty($data['username']) || empty($data['password']) || empty($data['nama_lengkap']) || empty($data['no_wa'])) {
            Response::error('Data tidak lengkap', null, 400);
        }

        if ($data['password'] !== $data['password_confirm']) {
            Response::error('Password tidak sesuai', null, 400);
        }

        if (!Auth::isValidPhone($data['no_wa'])) {
            Response::error('Nomor WhatsApp tidak valid', null, 400);
        }

        // Register user
        $result = $this->userModel->register($data);

        if (!$result['success']) {
            Response::error($result['message'], null, 400);
        }

        // Generate and send OTP
        $otpResult = Auth::generateOTP($data['no_wa']);
        if (!$otpResult['success']) {
            Response::error('Gagal generate OTP', null, 500);
        }

        // Send OTP via WhatsApp
        Auth::sendOTPViaWhatsApp($data['no_wa'], $otpResult['otp']);

        Response::success('Registrasi berhasil. Silakan verifikasi OTP yang telah dikirim ke WhatsApp', [
            'user_id' => $result['user_id'],
            'no_wa' => $data['no_wa']
        ]);
    }

    /**
     * Show OTP verification form
     */
    public function otpForm() {
        if (Auth::isLoggedIn()) {
            header('Location: /index.php?page=dashboard');
            exit;
        }

        $no_wa = $_GET['no_wa'] ?? null;
        if (!$no_wa) {
            Response::error('No WhatsApp tidak ditemukan', null, 400);
        }

        return [
            'page' => 'verify_otp',
            'title' => 'Verifikasi OTP - SI-PUSBAN',
            'no_wa' => $no_wa,
            'csrf_token' => Auth::generateCsrfToken()
        ];
    }

    /**
     * Verify OTP
     */
    public function verifyOtp() {
        // Check CSRF token
        $token = $_POST['csrf_token'] ?? null;
        if (!Auth::verifyCsrfToken($token)) {
            Response::error('Token tidak valid', null, 403);
        }

        $no_wa = Auth::sanitize($_POST['no_wa'] ?? '');
        $otp = Auth::sanitize($_POST['otp'] ?? '');

        if (empty($no_wa) || empty($otp)) {
            Response::error('Data tidak lengkap', null, 400);
        }

        // Verify OTP
        $result = Auth::verifyOTP($no_wa, $otp);

        if (!$result['success']) {
            Response::error($result['message'], null, 400);
        }

        // Find and activate user
        $user = $this->userModel->getUserByPhone($no_wa);
        if (!$user) {
            Response::error('Pengguna tidak ditemukan', null, 404);
        }

        $this->userModel->activateUser($user['id']);

        Response::success('OTP berhasil diverifikasi. Akun Anda telah diaktifkan.', [
            'user_id' => $user['id']
        ]);
    }

    /**
     * Show login form
     */
    public function loginForm() {
        // Check if already logged in
        if (Auth::isLoggedIn()) {
            header('Location: /index.php?page=dashboard');
            exit;
        }

        return [
            'page' => 'login',
            'title' => 'Login - SI-PUSBAN',
            'captcha' => Auth::generateCaptcha(),
            'csrf_token' => Auth::generateCsrfToken()
        ];
    }

    /**
     * Process login
     */
    public function login() {
        // Check CSRF token
        $token = $_POST['csrf_token'] ?? null;
        if (!Auth::verifyCsrfToken($token)) {
            Response::error('Token tidak valid', null, 403);
        }

        // Verify captcha
        $captcha = $_POST['captcha'] ?? null;
        if (!Auth::verifyCaptcha($captcha)) {
            Response::error('Captcha tidak sesuai', null, 400);
        }

        $username = Auth::sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            Response::error('Username dan password harus diisi', null, 400);
        }

        // Authenticate
        $result = $this->userModel->authenticate($username, $password);

        if (!$result['success']) {
            Response::error($result['message'], null, 401);
        }

        // Set session
        Auth::setUserSession($result['user']);

        Response::success('Login berhasil', [
            'user_id' => $result['user']['id'],
            'role' => $result['user']['role']
        ]);
    }

    /**
     * Logout
     */
    public function logout() {
        Auth::logout();
        header('Location: /index.php?page=login');
        exit;
    }

    /**
     * Get captcha image (for display)
     */
    public function getCaptchaImage() {
        Auth::startSession();

        $captchaCode = $_SESSION['captcha_code'] ?? '';

        // Create image
        $image = imagecreatetruecolor(200, 100);
        $backgroundColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        
        imagefill($image, 0, 0, $backgroundColor);
        imagestring($image, 5, 50, 40, $captchaCode, $textColor);

        // Add noise
        for ($i = 0; $i < 100; $i++) {
            $x = mt_rand(0, 200);
            $y = mt_rand(0, 100);
            $noiseColor = imagecolorallocate($image, mt_rand(100, 255), mt_rand(100, 255), mt_rand(100, 255));
            imagesetpixel($image, $x, $y, $noiseColor);
        }

        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
    }
}
?>
