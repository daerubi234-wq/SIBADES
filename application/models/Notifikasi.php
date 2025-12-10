<?php
/**
 * Notifikasi Model
 * SI-PUSBAN: Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Config.php';

class Notifikasi {
    private $table = 'notifikasi';

    public function __construct() {
    }

    /**
     * Create notification
     */
    public function create($userId, $judul, $isi, $tipe = 'Info') {
        $database = new Database();
        
        $data = [
            'user_id' => $userId,
            'judul' => $judul,
            'isi' => $isi,
            'tipe_notifikasi' => $tipe,
            'is_read' => false
        ];

        try {
            return $database->insert($this->table, $data);
        } catch (Exception $e) {
            error_log("Create notification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications($userId, $unreadOnly = false) {
        $database = new Database();
        
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ?";
        $params = [$userId];

        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }

        $sql .= " ORDER BY created_at DESC";

        return $database->fetchAll($sql, $params);
    }

    /**
     * Mark as read
     */
    public function markAsRead($notificationId) {
        $database = new Database();
        return $database->update($this->table, ['is_read' => true], ['id' => $notificationId]);
    }

    /**
     * Mark all as read for user
     */
    public function markAllAsRead($userId) {
        $database = new Database();
        return $database->update($this->table, ['is_read' => true], ['user_id' => $userId, 'is_read' => false]);
    }

    /**
     * Delete notification
     */
    public function delete($notificationId) {
        $database = new Database();
        return $database->delete($this->table, ['id' => $notificationId]);
    }

    /**
     * Send notification when usulan status changes
     */
    public function notifyStatusChange($usulanId, $newStatus, $adminNotes = null) {
        try {
            $database = new Database();
            
            // Get usulan
            $usulan = $database->fetchOne("SELECT * FROM usulan WHERE id = ?", [$usulanId]);
            
            if (!$usulan) {
                return false;
            }

            // Create notification for user
            $messages = [
                'Antri' => 'Usulan Anda sedang menunggu verifikasi',
                'Ditinjau' => 'Usulan Anda sedang ditinjau oleh tim admin',
                'Disetujui' => 'Selamat! Usulan Anda telah disetujui',
                'Ditolak' => 'Mohon maaf, usulan Anda tidak dapat disetujui'
            ];

            $title = "Update Status Usulan";
            $message = $messages[$newStatus] ?? "Status usulan berubah menjadi $newStatus";

            if ($adminNotes) {
                $message .= "\n\nCatatan: " . $adminNotes;
            }

            $this->create($usulan['user_id'], $title, $message, 'Info');

            // Send WhatsApp notification
            $this->sendWhatsAppNotification($usulan['user_id'], $message);

            return true;
        } catch (Exception $e) {
            error_log("Notify status change error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send WhatsApp notification (placeholder)
     */
    private function sendWhatsAppNotification($userId, $message) {
        try {
            $database = new Database();
            $user = $database->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);

            if (!$user) {
                return false;
            }

            // TODO: Implement actual WhatsApp API call
            error_log("WhatsApp notification to {$user['no_wa']}: $message");

            return true;
        } catch (Exception $e) {
            error_log("Send WhatsApp error: " . $e->getMessage());
            return false;
        }
    }
}
?>
