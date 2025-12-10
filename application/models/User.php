<?php
/**
 * User Model
 * SI-PUSBAN: Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Config.php';

class User {
    private $db;
    private $table = 'users';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Create new user (Registration)
     */
    public function register($data) {
        // Validate input
        if (empty($data['username']) || empty($data['password']) || empty($data['nama_lengkap']) || empty($data['no_wa'])) {
            return ['success' => false, 'message' => 'Data tidak lengkap'];
        }

        if (strlen($data['password']) < Config::PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'Password minimal ' . Config::PASSWORD_MIN_LENGTH . ' karakter'];
        }

        // Check if username already exists
        $check = $this->getUserByUsername($data['username']);
        if ($check) {
            return ['success' => false, 'message' => 'Username sudah terdaftar'];
        }

        // Check if phone already exists
        $checkPhone = $this->getUserByPhone($data['no_wa']);
        if ($checkPhone) {
            return ['success' => false, 'message' => 'Nomor WhatsApp sudah terdaftar'];
        }

        // Hash password
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        // Prepare data
        $userData = [
            'username' => $data['username'],
            'password_hash' => $passwordHash,
            'nama_lengkap' => $data['nama_lengkap'],
            'no_wa' => $data['no_wa'],
            'role' => $data['role'] ?? 'user',
            'is_active' => false
        ];

        try {
            $database = new Database();
            $userId = $database->insert($this->table, $userData);
            return ['success' => true, 'message' => 'Registrasi berhasil', 'user_id' => $userId];
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mendaftar'];
        }
    }

    /**
     * Authenticate user (Login)
     */
    public function authenticate($username, $password) {
        $user = $this->getUserByUsername($username);

        if (!$user) {
            return ['success' => false, 'message' => 'Username tidak ditemukan'];
        }

        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Akun belum diaktifkan. Silakan verifikasi OTP'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Password salah'];
        }

        // Update last login
        try {
            $database = new Database();
            $database->update($this->table, ['last_login' => date('Y-m-d H:i:s')], ['id' => $user['id']]);
        } catch (Exception $e) {
            error_log("Update last login error: " . $e->getMessage());
        }

        return ['success' => true, 'message' => 'Login berhasil', 'user' => $user];
    }

    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $database = new Database();
        return $database->fetchOne("SELECT * FROM $this->table WHERE id = ?", [$id]);
    }

    /**
     * Get user by username
     */
    public function getUserByUsername($username) {
        $database = new Database();
        return $database->fetchOne("SELECT * FROM $this->table WHERE username = ?", [$username]);
    }

    /**
     * Get user by phone
     */
    public function getUserByPhone($phone) {
        $database = new Database();
        return $database->fetchOne("SELECT * FROM $this->table WHERE no_wa = ?", [$phone]);
    }

    /**
     * Activate user account (after OTP verification)
     */
    public function activateUser($userId) {
        $database = new Database();
        return $database->update($this->table, ['is_active' => true], ['id' => $userId]);
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        $database = new Database();
        return $database->update($this->table, $data, ['id' => $userId]);
    }

    /**
     * Get all users with optional filtering
     */
    public function getAllUsers($role = null) {
        $database = new Database();
        if ($role) {
            return $database->fetchAll("SELECT id, username, nama_lengkap, no_wa, role, is_active, created_at FROM $this->table WHERE role = ? ORDER BY created_at DESC", [$role]);
        }
        return $database->fetchAll("SELECT id, username, nama_lengkap, no_wa, role, is_active, created_at FROM $this->table ORDER BY created_at DESC");
    }

    /**
     * Delete user
     */
    public function deleteUser($userId) {
        $database = new Database();
        return $database->delete($this->table, ['id' => $userId]);
    }

    /**
     * Change password
     */
    public function changePassword($userId, $oldPassword, $newPassword) {
        $user = $this->getUserById($userId);

        if (!password_verify($oldPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Password lama tidak sesuai'];
        }

        if (strlen($newPassword) < Config::PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'Password baru minimal ' . Config::PASSWORD_MIN_LENGTH . ' karakter'];
        }

        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $database = new Database();
        $database->update($this->table, ['password_hash' => $passwordHash], ['id' => $userId]);

        return ['success' => true, 'message' => 'Password berhasil diubah'];
    }
}
?>
