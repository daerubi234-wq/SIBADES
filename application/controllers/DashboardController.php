<?php
/**
 * Dashboard Controller
 * SI-PUSBAN: Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa
 */

require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Response.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Usulan.php';

class DashboardController {
    private $usulanModel;
    private $database;

    public function __construct() {
        Auth::startSession();
        Auth::requireLogin();

        $this->usulanModel = new Usulan();
        $database = new Database();
        $this->database = $database->getConnection();
    }

    /**
     * Show main dashboard (role-based)
     */
    public function index() {
        $user = Auth::getCurrentUser();

        if ($user['role'] === 'admin') {
            return $this->adminDashboard();
        } else if ($user['role'] === 'operator') {
            return $this->operatorDashboard();
        } else {
            return $this->userDashboard();
        }
    }

    /**
     * Admin Dashboard
     */
    private function adminDashboard() {
        // Get statistics
        $stats = $this->usulanModel->getStatistik();

        // Get recent usulan
        $recentUsulan = $this->usulanModel->getAllUsulan(['limit' => 10]);

        // Get all users count
        $stmt = $this->database->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
        $stmt->execute();
        $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        return [
            'page' => 'dashboard_admin',
            'title' => 'Dashboard Admin - SI-PUSBAN',
            'stats' => $stats,
            'recent_usulan' => $recentUsulan,
            'user_count' => $userCount,
            'user' => Auth::getCurrentUser()
        ];
    }

    /**
     * Operator Dashboard
     */
    private function operatorDashboard() {
        // Get statistics
        $stats = $this->usulanModel->getStatistik();

        // Get antri usulan
        $antriUsulan = $this->usulanModel->getAllUsulan(['status' => 'Antri', 'limit' => 20]);

        return [
            'page' => 'dashboard_operator',
            'title' => 'Dashboard Operator - SI-PUSBAN',
            'stats' => $stats,
            'antri_usulan' => $antriUsulan,
            'user' => Auth::getCurrentUser()
        ];
    }

    /**
     * User Dashboard
     */
    private function userDashboard() {
        $user = Auth::getCurrentUser();

        // Get user's usulan
        $userUsulan = $this->usulanModel->getAllUsulan(['user_id' => $user['id']]);

        return [
            'page' => 'dashboard_user',
            'title' => 'Dashboard Pengguna - SI-PUSBAN',
            'usulan' => $userUsulan,
            'user' => $user
        ];
    }

    /**
     * Get dashboard data via AJAX
     */
    public function getData() {
        $user = Auth::getCurrentUser();

        $filters = [
            'status' => $_GET['status'] ?? null,
            'search' => $_GET['search'] ?? null,
            'user_id' => $user['role'] === 'user' ? $user['id'] : ($_GET['user_id'] ?? null),
            'sort' => $_GET['sort'] ?? 'tgl_usulan DESC'
        ];

        $usulan = $this->usulanModel->getAllUsulan($filters);

        Response::success('Data berhasil diambil', ['usulan' => $usulan]);
    }

    /**
     * Get statistics
     */
    public function getStats() {
        $stats = $this->usulanModel->getStatistik();
        Response::success('Statistik berhasil diambil', $stats);
    }
}
?>
