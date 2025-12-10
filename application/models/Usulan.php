<?php
/**
 * Usulan (Proposal) Model
 * SI-PUSBAN: Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Config.php';

class Usulan {
    private $db;
    private $usulanTable = 'usulan';
    private $dokumenTable = 'dokumen_usulan';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Create new usulan (proposal)
     */
    public function create($data) {
        if (empty($data['user_id']) || empty($data['nik']) || empty($data['no_kk']) || empty($data['nama_lengkap'])) {
            return ['success' => false, 'message' => 'Data tidak lengkap'];
        }

        // Check if NIK already has active proposal
        $existing = $this->getUsulanByNik($data['nik']);
        if ($existing) {
            return ['success' => false, 'message' => 'NIK sudah terdaftar dalam sistem'];
        }

        $usulanData = [
            'user_id' => $data['user_id'],
            'nik' => $data['nik'],
            'no_kk' => $data['no_kk'],
            'nama_lengkap' => $data['nama_lengkap'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'alamat_lengkap' => $data['alamat_lengkap'],
            'jenis_bantuan' => $data['jenis_bantuan'],
            'deskripsi_bantuan' => $data['deskripsi_bantuan'] ?? null,
            'status' => 'Antri'
        ];

        try {
            $database = new Database();
            $usulanId = $database->insert($this->usulanTable, $usulanData);

            // Log activity
            $this->logActivity($data['user_id'], 'CREATE', $this->usulanTable, $usulanId, 'Usulan baru dibuat');

            return ['success' => true, 'message' => 'Usulan berhasil dibuat', 'usulan_id' => $usulanId];
        } catch (Exception $e) {
            error_log("Create usulan error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal membuat usulan'];
        }
    }

    /**
     * Get usulan by ID
     */
    public function getUsulanById($id) {
        $database = new Database();
        $usulan = $database->fetchOne("SELECT * FROM {$this->usulanTable} WHERE id = ?", [$id]);
        
        if ($usulan) {
            $usulan['dokumen'] = $this->getDokumenByUsulanId($id);
        }
        
        return $usulan;
    }

    /**
     * Get usulan by NIK
     */
    public function getUsulanByNik($nik) {
        $database = new Database();
        return $database->fetchOne("SELECT * FROM {$this->usulanTable} WHERE nik = ? AND status != 'Ditolak' LIMIT 1", [$nik]);
    }

    /**
     * Get all usulan with optional filtering
     */
    public function getAllUsulan($filters = []) {
        $database = new Database();
        $sql = "SELECT u.*, us.nama_lengkap as verifier_name FROM {$this->usulanTable} u 
                LEFT JOIN users us ON u.verified_by = us.id WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND u.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND u.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (u.nama_lengkap LIKE ? OR u.nik LIKE ? OR u.no_kk LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        if (!empty($filters['sort'])) {
            $sql .= " ORDER BY " . $filters['sort'];
        } else {
            $sql .= " ORDER BY u.tgl_usulan DESC";
        }

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
        }

        return $database->fetchAll($sql, $params);
    }

    /**
     * Update usulan status
     */
    public function updateStatus($usulanId, $status, $adminId, $notes = null, $alasan = null) {
        $validStatus = ['Antri', 'Ditinjau', 'Disetujui', 'Ditolak'];
        
        if (!in_array($status, $validStatus)) {
            return ['success' => false, 'message' => 'Status tidak valid'];
        }

        $updateData = [
            'status' => $status,
            'verified_by' => $adminId,
            'tgl_diproses' => date('Y-m-d H:i:s'),
            'catatan_admin' => $notes
        ];

        if ($status === 'Ditolak' && $alasan) {
            $updateData['alasan_penolakan'] = $alasan;
        }

        try {
            $database = new Database();
            $database->update($this->usulanTable, $updateData, ['id' => $usulanId]);

            // Log activity
            $this->logActivity($adminId, 'UPDATE', $this->usulanTable, $usulanId, "Status diubah menjadi $status");

            return ['success' => true, 'message' => 'Status berhasil diperbarui'];
        } catch (Exception $e) {
            error_log("Update status error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal memperbarui status'];
        }
    }

    /**
     * Delete usulan
     */
    public function deleteUsulan($usulanId, $adminId) {
        try {
            $database = new Database();
            $database->beginTransaction();

            // Delete related documents first
            $database->delete($this->dokumenTable, ['usulan_id' => $usulanId]);

            // Delete usulan
            $database->delete($this->usulanTable, ['id' => $usulanId]);

            $database->commit();

            // Log activity
            $this->logActivity($adminId, 'DELETE', $this->usulanTable, $usulanId, 'Usulan dihapus');

            return ['success' => true, 'message' => 'Usulan berhasil dihapus'];
        } catch (Exception $e) {
            error_log("Delete usulan error: " . $e->getMessage());
            $database->rollBack();
            return ['success' => false, 'message' => 'Gagal menghapus usulan'];
        }
    }

    /**
     * Get documents by usulan ID
     */
    public function getDokumenByUsulanId($usulanId) {
        $database = new Database();
        return $database->fetchAll("SELECT * FROM {$this->dokumenTable} WHERE usulan_id = ? ORDER BY tipe_dokumen", [$usulanId]);
    }

    /**
     * Get statistik usulan
     */
    public function getStatistik() {
        $database = new Database();
        $stats = [];

        $stats['total'] = $database->fetchOne("SELECT COUNT(*) as count FROM {$this->usulanTable}")['count'];
        $stats['antri'] = $database->fetchOne("SELECT COUNT(*) as count FROM {$this->usulanTable} WHERE status = 'Antri'")['count'];
        $stats['ditinjau'] = $database->fetchOne("SELECT COUNT(*) as count FROM {$this->usulanTable} WHERE status = 'Ditinjau'")['count'];
        $stats['disetujui'] = $database->fetchOne("SELECT COUNT(*) as count FROM {$this->usulanTable} WHERE status = 'Disetujui'")['count'];
        $stats['ditolak'] = $database->fetchOne("SELECT COUNT(*) as count FROM {$this->usulanTable} WHERE status = 'Ditolak'")['count'];

        return $stats;
    }

    /**
     * Log activity
     */
    private function logActivity($userId, $action, $table, $recordId, $description) {
        try {
            $database = new Database();
            $logData = [
                'user_id' => $userId,
                'action' => $action,
                'tabel_target' => $table,
                'record_id' => $recordId,
                'deskripsi' => $description,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ];
            $database->insert('activity_log', $logData);
        } catch (Exception $e) {
            error_log("Log activity error: " . $e->getMessage());
        }
    }
}
?>
