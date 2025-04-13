<?php
/**
 * Service Model
 * 
 * Handles service data operations
 */
class Service {
    // Database connection and table name
    private $conn;
    private $table_name = "SERVICE";
    
    // Service properties
    public $CodeService;
    public $LibelleService;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create service
     * 
     * @return boolean
     */
    public function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                  SET CodeService = :CodeService,
                      LibelleService = :LibelleService";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->CodeService = htmlspecialchars(strip_tags($this->CodeService));
        $this->LibelleService = htmlspecialchars(strip_tags($this->LibelleService));
        
        // Bind values
        $stmt->bindParam(":CodeService", $this->CodeService);
        $stmt->bindParam(":LibelleService", $this->LibelleService);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Read all services
     * 
     * @return PDOStatement
     */
    public function read() {
        // Query to read all services
        $query = "SELECT CodeService, LibelleService
                  FROM " . $this->table_name . "
                  ORDER BY LibelleService ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Read one service
     * 
     * @return boolean
     */
    public function readOne() {
        // Query to read one service
        $query = "SELECT CodeService, LibelleService
                  FROM " . $this->table_name . "
                  WHERE CodeService = :CodeService
                  LIMIT 1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->CodeService = htmlspecialchars(strip_tags($this->CodeService));
        
        // Bind values
        $stmt->bindParam(":CodeService", $this->CodeService);
        
        // Execute query
        $stmt->execute();
        
        // Get record details
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if record exists
        if (!$row) {
            return false;
        }
        
        // Set values to object properties
        $this->CodeService = $row['CodeService'];
        $this->LibelleService = $row['LibelleService'];
        
        return true;
    }
    
    /**
     * Update service
     * 
     * @return boolean
     */
    public function update() {
        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                  SET LibelleService = :LibelleService
                  WHERE CodeService = :CodeService";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->LibelleService = htmlspecialchars(strip_tags($this->LibelleService));
        $this->CodeService = htmlspecialchars(strip_tags($this->CodeService));
        
        // Bind values
        $stmt->bindParam(":LibelleService", $this->LibelleService);
        $stmt->bindParam(":CodeService", $this->CodeService);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete service
     * 
     * @return boolean
     */
    public function delete() {
        // Check if service is used in personnel
        if ($this->isUsedInPersonnel()) {
            return false;
        }
        
        // Query to delete record
        $query = "DELETE FROM " . $this->table_name . " WHERE CodeService = :CodeService";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->CodeService = htmlspecialchars(strip_tags($this->CodeService));
        
        // Bind value
        $stmt->bindParam(":CodeService", $this->CodeService);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if service is used in personnel
     * 
     * @return boolean
     */
    private function isUsedInPersonnel() {
        // Query to check if service is used
        $query = "SELECT COUNT(*) as count
                  FROM PERSONNEL
                  WHERE CodeService = :CodeService";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(":CodeService", $this->CodeService);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Return true if service is used
        return $row['count'] > 0;
    }
    
    /**
     * Search services
     * 
     * @param string $keywords Keywords to search
     * @return PDOStatement
     */
    public function search($keywords) {
        // Query to search services
        $query = "SELECT CodeService, LibelleService
                  FROM " . $this->table_name . "
                  WHERE LibelleService LIKE :keywords
                  OR CodeService LIKE :keywords
                  ORDER BY LibelleService ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";
        
        // Bind values
        $stmt->bindParam(":keywords", $keywords);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get personnel by service
     * 
     * @return PDOStatement
     */
    public function getPersonnel() {
        // Query to get personnel by service
        $query = "SELECT MatriculePersonnel, NomPersonnel, PrenomPersonnel, 
                         EmailPersonnel, TelPersonnel
                  FROM PERSONNEL
                  WHERE CodeService = :CodeService
                  ORDER BY NomPersonnel ASC, PrenomPersonnel ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->CodeService = htmlspecialchars(strip_tags($this->CodeService));
        
        // Bind values
        $stmt->bindParam(":CodeService", $this->CodeService);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Count services
     * 
     * @return int
     */
    public function count() {
        // Query to count services
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        // Get row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
    
    /**
     * Get services for dropdown
     * 
     * @return PDOStatement
     */
    public function getServicesDropdown() {
        // Query to read services for dropdown
        $query = "SELECT CodeService, LibelleService
                  FROM " . $this->table_name . "
                  ORDER BY LibelleService ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Count personnel by service
     * 
     * @return PDOStatement
     */
    public function countPersonnelByService() {
        // Query to count personnel by service
        $query = "SELECT s.CodeService, s.LibelleService, COUNT(p.MatriculePersonnel) as personnelCount
                  FROM " . $this->table_name . " s
                  LEFT JOIN PERSONNEL p ON s.CodeService = p.CodeService
                  GROUP BY s.CodeService
                  ORDER BY personnelCount DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
}
?>
