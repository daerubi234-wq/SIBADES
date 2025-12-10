<?php
/**
 * Database Configuration Class
 * SI-PUSBAN: Sistem Informasi Pendataan Usulan Bantuan Sosial Internal Desa
 */

class Database {
    private $host;
    private $user;
    private $pass;
    private $dbname;
    private $port;
    private $conn;

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->user = getenv('DB_USER') ?: 'root';
        $this->pass = getenv('DB_PASS') ?: '';
        $this->dbname = getenv('DB_NAME') ?: 'si_pusban';
        $this->port = getenv('DB_PORT') ?: 3306;
    }

    /**
     * Establish database connection using PDO
     */
    public function connect() {
        $this->conn = null;

        try {
            // Use explicit TCP connection (not Unix socket) by specifying host with protocol
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->dbname . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
            return $this->conn;
        } catch (PDOException $e) {
            die("Database Connection Error: " . $e->getMessage());
        }
    }

    /**
     * Get database connection
     */
    public function getConnection() {
        if ($this->conn === null) {
            $this->connect();
        }
        return $this->conn;
    }

    /**
     * Execute query with parameters
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database Query Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Fetch single row
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insert record
     */
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $this->query($sql, array_values($data));
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Update record
     */
    public function update($table, $data, $where) {
        $setClause = implode(',', array_map(fn($k) => "$k = ?", array_keys($data)));
        $whereClause = implode(' AND ', array_map(fn($k) => "$k = ?", array_keys($where)));
        
        $sql = "UPDATE $table SET $setClause WHERE $whereClause";
        $params = array_merge(array_values($data), array_values($where));
        
        return $this->query($sql, $params);
    }

    /**
     * Delete record
     */
    public function delete($table, $where) {
        $whereClause = implode(' AND ', array_map(fn($k) => "$k = ?", array_keys($where)));
        $sql = "DELETE FROM $table WHERE $whereClause";
        
        return $this->query($sql, array_values($where));
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->getConnection()->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollBack() {
        return $this->getConnection()->rollBack();
    }
}
?>
