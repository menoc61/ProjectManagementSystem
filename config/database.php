<?php
/**
 * Database Configuration
 * 
 * Handles database connection using PDO
 */
class Database {
    private $host = 'localhost';
    private $db_name = 'GestionProjetsEntreprise';
    private $username = 'root';
    private $password = '';
    private $conn;

    /**
     * Get the database connection
     * 
     * @return PDO Database connection
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>
