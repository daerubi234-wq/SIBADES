<?php
/**
 * Authentication API Endpoints
 * SI-PUSBAN: Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa
 */

define('APP_PATH', __DIR__ . '/../../application/');
define('ROOT_PATH', __DIR__ . '/../../');

// Load environment variables from .env
$envFile = ROOT_PATH . '.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

require_once APP_PATH . 'config/Config.php';
require_once APP_PATH . 'config/Auth.php';
require_once APP_PATH . 'config/Response.php';
require_once APP_PATH . 'config/Database.php';
require_once APP_PATH . 'controllers/AuthController.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? htmlspecialchars($_GET['action']) : 'login';
$authController = new AuthController();

try {
    switch ($action) {
        case 'register':
            $authController->register();
            break;
        case 'login':
            $authController->login();
            break;
        case 'verify-otp':
            $authController->verifyOtp();
            break;
        case 'logout':
            $authController->logout();
            break;
        case 'captcha-image':
            $authController->getCaptchaImage();
            break;
        default:
            Response::error('Action tidak ditemukan', null, 404);
    }
} catch (Exception $e) {
    error_log("API error: " . $e->getMessage());
    Response::error('Terjadi kesalahan pada server', null, 500);
}
?>
