<?php
/**
 * Main Application Entry Point
 * SI-PUSBAN: Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa
 */

// Start output buffering
ob_start();

// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Define base paths
// `public/index.php` lives inside `public/`, so application files are one level up
define('APP_PATH', __DIR__ . '/../application/');
define('PUBLIC_PATH', __DIR__ . '/');
define('ROOT_PATH', __DIR__ . '/../');

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

// Load configuration
require_once APP_PATH . 'config/Config.php';
require_once APP_PATH . 'config/Auth.php';
require_once APP_PATH . 'config/Response.php';
require_once APP_PATH . 'config/Database.php';

// Load controllers and models
require_once APP_PATH . 'controllers/AuthController.php';
require_once APP_PATH . 'controllers/DashboardController.php';
require_once APP_PATH . 'controllers/UsulanController.php';
require_once APP_PATH . 'models/User.php';
require_once APP_PATH . 'models/Usulan.php';
require_once APP_PATH . 'models/Dokumen.php';

// Request routing
$page = isset($_GET['page']) ? htmlspecialchars($_GET['page']) : 'home';
$action = isset($_GET['action']) ? htmlspecialchars($_GET['action']) : 'index';

// Start session
Auth::startSession();

// Check session timeout
if (Auth::checkSessionTimeout()) {
    Auth::logout();
    if ($page !== 'login' && $page !== 'register') {
        header('Location: /index.php?page=login');
        exit;
    }
}

// Route handler
try {
    if ($page === 'home' || $page === 'index') {
        // Home page
        $title = 'SI-PUSBAN - Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa';
        ob_start();
        require APP_PATH . 'views/landing.php';
        $content = ob_get_clean();

    } else if ($page === 'login') {
        $authController = new AuthController();
        $viewData = $authController->loginForm();
        $title = $viewData['title'];
        
        ob_start();
        require APP_PATH . 'views/login.php';
        $content = ob_get_clean();

    } else if ($page === 'register') {
        $authController = new AuthController();
        $viewData = $authController->registrationForm();
        $title = $viewData['title'];
        
        ob_start();
        require APP_PATH . 'views/register.php';
        $content = ob_get_clean();

    } else if ($page === 'verify_otp') {
        $authController = new AuthController();
        $no_wa = $_GET['no_wa'] ?? '';
        $title = 'Verifikasi OTP - SI-PUSBAN';
        
        ob_start();
        require APP_PATH . 'views/verify_otp.php';
        $content = ob_get_clean();

    } else if ($page === 'dashboard') {
        Auth::requireLogin();
        $dashboardController = new DashboardController();
        $viewData = $dashboardController->index();
        $title = $viewData['title'];
        $user = $viewData['user'];
        
        $stats = $viewData['stats'] ?? [];
        $recent_usulan = $viewData['recent_usulan'] ?? [];
        $user_count = $viewData['user_count'] ?? 0;
        $usulan = $viewData['usulan'] ?? [];
        $antri_usulan = $viewData['antri_usulan'] ?? [];
        
        if ($viewData['page'] === 'dashboard_admin') {
            ob_start();
            require APP_PATH . 'views/dashboard_admin.php';
            $content = ob_get_clean();
        } else if ($viewData['page'] === 'dashboard_user') {
            ob_start();
            require APP_PATH . 'views/dashboard_user.php';
            $content = ob_get_clean();
        }

    } else if ($page === 'form_usulan') {
        Auth::requireLogin();
        $usulanController = new UsulanController();
        $viewData = $usulanController->formUsulan();
        $title = $viewData['title'];
        $jenis_bantuan = $viewData['jenis_bantuan'];
        
        ob_start();
        require APP_PATH . 'views/form_usulan.php';
        $content = ob_get_clean();

    } else if ($page === 'detail_usulan') {
        Auth::requireLogin();
        $usulanController = new UsulanController();
        $viewData = $usulanController->detailUsulan();
        $title = $viewData['title'];
        $usulan = $viewData['usulan'];
        $dokumen = $viewData['dokumen'];
        $doc_status = $viewData['doc_status'];
        
        ob_start();
        require APP_PATH . 'views/detail_usulan.php';
        $content = ob_get_clean();

    } else {
        Response::notFound();
    }

    // Load and render layout
    $title = $title ?? 'SI-PUSBAN';
    ob_start();
    require APP_PATH . 'views/layout.php';
    echo ob_get_clean();

} catch (Exception $e) {
    error_log("Application error: " . $e->getMessage());
    http_response_code(500);
    echo "Terjadi kesalahan. Silakan coba lagi nanti.";
    exit;
}

ob_end_flush();
?>
