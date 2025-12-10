<?php
/**
 * Report Generator Model
 * SI-PUSBAN: Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Config.php';

class Report {
    private $database;

    public function __construct() {
        $db = new Database();
        $this->database = $db->getConnection();
    }

    /**
     * Generate summary report
     */
    public function getSummaryReport() {
        $report = [];

        // Total statistics
        $stmt = $this->database->prepare("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Antri' THEN 1 ELSE 0 END) as antri,
            SUM(CASE WHEN status = 'Ditinjau' THEN 1 ELSE 0 END) as ditinjau,
            SUM(CASE WHEN status = 'Disetujui' THEN 1 ELSE 0 END) as disetujui,
            SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) as ditolak
            FROM usulan");
        $stmt->execute();
        $report['usulan'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // User statistics
        $stmt = $this->database->prepare("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as users,
            SUM(CASE WHEN role = 'operator' THEN 1 ELSE 0 END) as operators,
            SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active
            FROM users");
        $stmt->execute();
        $report['users'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Jenis bantuan breakdown
        $stmt = $this->database->prepare("SELECT jenis_bantuan, COUNT(*) as count
            FROM usulan
            GROUP BY jenis_bantuan
            ORDER BY count DESC");
        $stmt->execute();
        $report['by_jenis'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Status breakdown by month
        $stmt = $this->database->prepare("SELECT 
            DATE_FORMAT(tgl_usulan, '%Y-%m') as month,
            SUM(CASE WHEN status = 'Disetujui' THEN 1 ELSE 0 END) as disetujui,
            COUNT(*) as total
            FROM usulan
            WHERE tgl_usulan >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(tgl_usulan, '%Y-%m')
            ORDER BY month DESC");
        $stmt->execute();
        $report['monthly'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $report;
    }

    /**
     * Export to CSV
     */
    public function exportToCSV($filters = []) {
        $sql = "SELECT 
            u.id,
            u.nik,
            u.no_kk,
            u.nama_lengkap,
            u.jenis_kelamin,
            u.alamat_lengkap,
            u.jenis_bantuan,
            u.status,
            u.tgl_usulan,
            u.tgl_diproses,
            COUNT(d.id) as dokumen_count
            FROM usulan u
            LEFT JOIN dokumen_usulan d ON u.id = d.usulan_id
            WHERE 1=1";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND u.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND u.tgl_usulan >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND u.tgl_usulan <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= " GROUP BY u.id ORDER BY u.tgl_usulan DESC";

        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->arrayToCSV($data);
    }

    /**
     * Convert array to CSV
     */
    private function arrayToCSV($data) {
        if (empty($data)) {
            return '';
        }

        $csv = '';
        
        // Headers
        $csv .= implode(',', array_keys($data[0])) . "\n";

        // Data
        foreach ($data as $row) {
            $csv .= implode(',', array_map(function($value) {
                return '"' . str_replace('"', '""', $value) . '"';
            }, $row)) . "\n";
        }

        return $csv;
    }

    /**
     * Get approval rate
     */
    public function getApprovalRate($months = 12) {
        $stmt = $this->database->prepare("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Disetujui' THEN 1 ELSE 0 END) as approved
            FROM usulan
            WHERE tgl_usulan >= DATE_SUB(NOW(), INTERVAL ? MONTH)");
        $stmt->execute([$months]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['total'] > 0) {
            return ($result['approved'] / $result['total']) * 100;
        }

        return 0;
    }

    /**
     * Get average processing time (in days)
     */
    public function getAverageProcessingTime() {
        $stmt = $this->database->prepare("SELECT 
            AVG(DATEDIFF(tgl_diproses, tgl_usulan)) as avg_days
            FROM usulan
            WHERE tgl_diproses IS NOT NULL
            AND status IN ('Disetujui', 'Ditolak')");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return round($result['avg_days'] ?? 0, 2);
    }
}
?>
