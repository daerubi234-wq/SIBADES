<?php
/**
 * Response Helper Class
 * SI-PUSBAN: Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa
 */

class Response {
    /**
     * Send JSON response
     */
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Success response
     */
    public static function success($message = 'Sukses', $data = null, $statusCode = 200) {
        return self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Error response
     */
    public static function error($message = 'Error', $data = null, $statusCode = 400) {
        return self::json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Redirect
     */
    public static function redirect($location) {
        header('Location: ' . $location);
        exit;
    }

    /**
     * Not found
     */
    public static function notFound() {
        http_response_code(404);
        die('Halaman tidak ditemukan');
    }

    /**
     * Unauthorized
     */
    public static function unauthorized() {
        http_response_code(401);
        die('Akses ditolak. Silakan login terlebih dahulu.');
    }

    /**
     * Forbidden
     */
    public static function forbidden() {
        http_response_code(403);
        die('Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}
?>
