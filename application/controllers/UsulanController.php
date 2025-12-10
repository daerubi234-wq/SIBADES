<?php
/**
 * Usulan Controller
 * SI-PUSBAN: Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa
 */

require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Response.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Usulan.php';
require_once __DIR__ . '/../models/Dokumen.php';

class UsulanController {
    private $usulanModel;
    private $dokumenModel;

    public function __construct() {
        Auth::startSession();
        Auth::requireLogin();

        $this->usulanModel = new Usulan();
        $this->dokumenModel = new Dokumen();
    }

    /**
     * Show usulan form
     */
    public function formUsulan() {
        $user = Auth::getCurrentUser();

        // Only user and operator can create usulan
        if (!in_array($user['role'], ['user', 'operator'])) {
            Response::forbidden();
        }

        return [
            'page' => 'form_usulan',
            'title' => 'Form Usulan Bantuan - SI-PUSBAN',
            'jenis_bantuan' => Config::JENIS_BANTUAN,
            'csrf_token' => Auth::generateCsrfToken()
        ];
    }

    /**
     * Create new usulan
     */
    public function createUsulan() {
        $user = Auth::getCurrentUser();

        // Only user and operator can create usulan
        if (!in_array($user['role'], ['user', 'operator'])) {
            Response::forbidden();
        }

        // Check CSRF token
        $token = $_POST['csrf_token'] ?? null;
        if (!Auth::verifyCsrfToken($token)) {
            Response::error('Token tidak valid', null, 403);
        }

        // Sanitize and prepare data
        $data = [
            'user_id' => $user['id'],
            'nik' => Auth::sanitize($_POST['nik'] ?? ''),
            'no_kk' => Auth::sanitize($_POST['no_kk'] ?? ''),
            'nama_lengkap' => Auth::sanitize($_POST['nama_lengkap'] ?? ''),
            'jenis_kelamin' => Auth::sanitize($_POST['jenis_kelamin'] ?? ''),
            'alamat_lengkap' => Auth::sanitize($_POST['alamat_lengkap'] ?? ''),
            'jenis_bantuan' => Auth::sanitize($_POST['jenis_bantuan'] ?? ''),
            'deskripsi_bantuan' => Auth::sanitize($_POST['deskripsi_bantuan'] ?? '')
        ];

        // Validate required fields
        if (empty($data['nik']) || empty($data['no_kk']) || empty($data['nama_lengkap']) || 
            empty($data['jenis_kelamin']) || empty($data['alamat_lengkap']) || empty($data['jenis_bantuan'])) {
            Response::error('Data tidak lengkap', null, 400);
        }

        // Validate NIK format (16 digits)
        if (!preg_match('/^\d{16}$/', $data['nik'])) {
            Response::error('Format NIK tidak valid (harus 16 digit)', null, 400);
        }

        // Create usulan
        $result = $this->usulanModel->create($data);

        if (!$result['success']) {
            Response::error($result['message'], null, 400);
        }

        Response::success('Usulan berhasil dibuat', ['usulan_id' => $result['usulan_id']]);
    }

    /**
     * Show usulan details
     */
    public function detailUsulan() {
        $user = Auth::getCurrentUser();
        $usulanId = Auth::sanitize($_GET['id'] ?? '');

        if (empty($usulanId)) {
            Response::notFound();
        }

        $usulan = $this->usulanModel->getUsulanById($usulanId);

        if (!$usulan) {
            Response::notFound();
        }

        // Check permission
        if ($user['role'] === 'user' && $usulan['user_id'] != $user['id']) {
            Response::forbidden();
        }

        // Get required documents status
        $docStatus = $this->dokumenModel->checkAllDocumentsUploaded($usulanId);

        return [
            'page' => 'detail_usulan',
            'title' => 'Detail Usulan - SI-PUSBAN',
            'usulan' => $usulan,
            'dokumen' => $usulan['dokumen'],
            'doc_status' => $docStatus,
            'jenis_bantuan' => Config::JENIS_BANTUAN,
            'csrf_token' => Auth::generateCsrfToken()
        ];
    }

    /**
     * Upload document
     */
    public function uploadDocument() {
        $user = Auth::getCurrentUser();

        // Check CSRF token
        $token = $_POST['csrf_token'] ?? null;
        if (!Auth::verifyCsrfToken($token)) {
            Response::error('Token tidak valid', null, 403);
        }

        $usulanId = Auth::sanitize($_POST['usulan_id'] ?? '');
        $tipeDocumen = Auth::sanitize($_POST['tipe_dokumen'] ?? '');

        if (empty($usulanId) || empty($tipeDocumen)) {
            Response::error('Data tidak lengkap', null, 400);
        }

        // Get usulan
        $usulan = $this->usulanModel->getUsulanById($usulanId);

        if (!$usulan) {
            Response::notFound();
        }

        // Check permission
        if ($user['role'] === 'user' && $usulan['user_id'] != $user['id']) {
            Response::forbidden();
        }

        // Check if file uploaded
        if (!isset($_FILES['document']) || empty($_FILES['document']['tmp_name'])) {
            Response::error('File tidak diunggah', null, 400);
        }

        // Upload document
        $result = $this->dokumenModel->uploadDocument(
            $usulanId,
            $tipeDocumen,
            $_FILES['document'],
            $usulan['nik'],
            $usulan['nama_lengkap']
        );

        if (!$result['success']) {
            Response::error($result['message'], null, 400);
        }

        Response::success('Dokumen berhasil diunggah', $result);
    }

    /**
     * Delete document
     */
    public function deleteDocument() {
        $user = Auth::getCurrentUser();

        // Check CSRF token
        $token = $_POST['csrf_token'] ?? null;
        if (!Auth::verifyCsrfToken($token)) {
            Response::error('Token tidak valid', null, 403);
        }

        $docId = Auth::sanitize($_POST['doc_id'] ?? '');

        if (empty($docId)) {
            Response::error('Document ID tidak ditemukan', null, 400);
        }

        // Get document
        $doc = $this->dokumenModel->getDocumentById($docId);

        if (!$doc) {
            Response::notFound();
        }

        // Get usulan for permission check
        $usulan = $this->usulanModel->getUsulanById($doc['usulan_id']);

        // Check permission
        if ($user['role'] === 'user' && $usulan['user_id'] != $user['id']) {
            Response::forbidden();
        }

        // Delete document
        $result = $this->dokumenModel->deleteDocument($docId);

        if (!$result['success']) {
            Response::error($result['message'], null, 400);
        }

        Response::success('Dokumen berhasil dihapus');
    }

    /**
     * Update usulan status (Admin/Operator only)
     */
    public function updateStatus() {
        $user = Auth::getCurrentUser();

        // Only admin and operator can update status
        if (!in_array($user['role'], ['admin', 'operator'])) {
            Response::forbidden();
        }

        // Check CSRF token
        $token = $_POST['csrf_token'] ?? null;
        if (!Auth::verifyCsrfToken($token)) {
            Response::error('Token tidak valid', null, 403);
        }

        $usulanId = Auth::sanitize($_POST['usulan_id'] ?? '');
        $status = Auth::sanitize($_POST['status'] ?? '');
        $notes = Auth::sanitize($_POST['notes'] ?? '');
        $alasan = Auth::sanitize($_POST['alasan'] ?? '');

        if (empty($usulanId) || empty($status)) {
            Response::error('Data tidak lengkap', null, 400);
        }

        // Update status
        $result = $this->usulanModel->updateStatus($usulanId, $status, $user['id'], $notes, $alasan);

        if (!$result['success']) {
            Response::error($result['message'], null, 400);
        }

        Response::success('Status berhasil diperbarui');
    }

    /**
     * Delete usulan (Admin only)
     */
    public function deleteUsulan() {
        $user = Auth::getCurrentUser();

        // Only admin can delete
        if ($user['role'] !== 'admin') {
            Response::forbidden();
        }

        // Check CSRF token
        $token = $_POST['csrf_token'] ?? null;
        if (!Auth::verifyCsrfToken($token)) {
            Response::error('Token tidak valid', null, 403);
        }

        $usulanId = Auth::sanitize($_POST['usulan_id'] ?? '');

        if (empty($usulanId)) {
            Response::error('Usulan ID tidak ditemukan', null, 400);
        }

        // Delete usulan
        $result = $this->usulanModel->deleteUsulan($usulanId, $user['id']);

        if (!$result['success']) {
            Response::error($result['message'], null, 400);
        }

        Response::success('Usulan berhasil dihapus');
    }

    /**
     * Export usulan to Excel
     */
    public function exportExcel() {
        $user = Auth::getCurrentUser();

        // Only admin and operator can export
        if (!in_array($user['role'], ['admin', 'operator'])) {
            Response::forbidden();
        }

        $filters = [
            'status' => $_GET['status'] ?? null,
            'search' => $_GET['search'] ?? null
        ];

        $usulan = $this->usulanModel->getAllUsulan($filters);

        // TODO: Implement Excel export using library like PhpSpreadsheet
        // For now, just return CSV
        $this->generateCSV($usulan);
    }

    /**
     * Generate CSV (temporary export method)
     */
    private function generateCSV($usulan) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="usulan_' . date('Y-m-d_H-i-s') . '.csv"');

        $output = fopen('php://output', 'w');

        // Headers
        fputcsv($output, ['ID', 'Nama', 'NIK', 'No KK', 'Jenis Bantuan', 'Status', 'Tanggal Usulan']);

        // Data
        foreach ($usulan as $row) {
            fputcsv($output, [
                $row['id'],
                $row['nama_lengkap'],
                $row['nik'],
                $row['no_kk'],
                $row['jenis_bantuan'],
                $row['status'],
                $row['tgl_usulan']
            ]);
        }

        fclose($output);
        exit;
    }
}
?>
