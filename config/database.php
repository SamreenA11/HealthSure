<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'healthsure_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", 
                                $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            die("Connection error: " . $exception->getMessage());
        }
        return $this->conn;
    }
    
    public static function connect() {
        $database = new Database();
        return $database->getConnection();
    }
}

// Global database connection
$database = new Database();
$conn = $database->getConnection();
?>