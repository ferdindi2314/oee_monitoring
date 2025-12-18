<?php
/**
 * Database Configuration Class
 * Konfigurasi koneksi database MySQL
 */

class Database {
    private $host = "localhost";
    private $db_name = "oee_monitoring";
    private $username = "root";
    private $password = "";
    private $port = "3306";
    private $conn;

    // Singleton instance
    private static $instance = null;

    private function __construct() {
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            $dsn = "mysql:host=" . $this->host . 
                   ";port=" . $this->port . 
                   ";dbname=" . $this->db_name . 
                   ";charset=utf8";

            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true
            ]);

        } catch(PDOException $exception) {
            error_log("Database Connection Error: " . $exception->getMessage());
            throw new Exception("Database connection failed. Please check configuration.");
        }
    }

    public function getConnection() {
        // Reconnect if connection is lost
        if ($this->conn == null) {
            $this->connect();
        }
        return $this->conn;
    }

    public function closeConnection() {
        $this->conn = null;
    }

    // Helper method for testing connection
    public function testConnection() {
        try {
            $stmt = $this->conn->query("SELECT 1");
            return $stmt->fetchColumn() === '1';
        } catch (PDOException $e) {
            return false;
        }
    }
}

// Response helper functions
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $status >= 200 && $status < 300,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

function errorResponse($message, $status = 400) {
    jsonResponse(['error' => $message], $status);
}
?>