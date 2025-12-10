<?php
/**
 * Usulan API Endpoints
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
require_once APP_PATH . 'controllers/UsulanController.php';

header('Content-Type: application/json');

Auth::startSession();
Auth::requireLogin();

$action = isset($_GET['action']) ? htmlspecialchars($_GET['action']) : 'index';
$usulanController = new UsulanController();

try {
    switch ($action) {
        case 'create':
            $usulanController->createUsulan();
            break;
        case 'upload-document':
            $usulanController->uploadDocument();
            break;
        case 'delete-document':
            $usulanController->deleteDocument();
            break;
        case 'update-status':
            $usulanController->updateStatus();
            break;
        case 'delete':
            $usulanController->deleteUsulan();
            break;
        case 'export-excel':
            $usulanController->exportExcel();
            break;
        default:
            Response::error('Action tidak ditemukan', null, 404);
    }
} catch (Exception $e) {
    error_log("Usulan API error: " . $e->getMessage());
    Response::error('Terjadi kesalahan pada server', null, 500);
}
?>
