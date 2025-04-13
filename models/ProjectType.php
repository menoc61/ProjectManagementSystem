<?php
/**
 * Project Type Model
 * 
 * Handles project type data operations
 */
class ProjectType {
    // Database connection and table name
    private $conn;
    private $table_name = "TYPEPROJET";
    
    // Project type properties
    public $IdTypeProjet;
    public $LibelleTypeProjet;
    public $ForfaitCoutTypeProjet;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create project type
     * 
     * @return boolean
     */
    public function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                  SET LibelleTypeProjet = :LibelleTypeProjet,
                      ForfaitCoutTypeProjet = :ForfaitCoutTypeProjet";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->LibelleTypeProjet = htmlspecialchars(strip_tags($this->LibelleTypeProjet));
        $this->ForfaitCoutTypeProjet = htmlspecialchars(strip_tags($this->ForfaitCoutTypeProjet));
        
        // Bind values
        $stmt->bindParam(":LibelleTypeProjet", $this->LibelleTypeProjet);
        $stmt->bindParam(":ForfaitCoutTypeProjet", $this->ForfaitCoutTypeProjet);
        
        // Execute query
        if ($stmt->execute()) {
            $this->IdTypeProjet = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Read all project types
     * 
     * @return PDOStatement
     */
    public function read() {
        // Query to read all project types
        $query = "SELECT IdTypeProjet, LibelleTypeProjet, ForfaitCoutTypeProjet
                  FROM " . $this->table_name . "
                  ORDER BY LibelleTypeProjet ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Read one project type
     * 
     * @return boolean
     */
    public function readOne() {
        // Query to read one project type
        $query = "SELECT IdTypeProjet, LibelleTypeProjet, ForfaitCoutTypeProjet
                  FROM " . $this->table_name . "
                  WHERE IdTypeProjet = :IdTypeProjet
                  LIMIT 1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdTypeProjet = htmlspecialchars(strip_tags($this->IdTypeProjet));
        
        // Bind values
        $stmt->bindParam(":IdTypeProjet", $this->IdTypeProjet);
        
        // Execute query
        $stmt->execute();
        
        // Get record details
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if record exists
        if (!$row) {
            return false;
        }
        
        // Set values to object properties
        $this->IdTypeProjet = $row['IdTypeProjet'];
        $this->LibelleTypeProjet = $row['LibelleTypeProjet'];
        $this->ForfaitCoutTypeProjet = $row['ForfaitCoutTypeProjet'];
        
        return true;
    }
    
    /**
     * Update project type
     * 
     * @return boolean
     */
    public function update() {
        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                  SET LibelleTypeProjet = :LibelleTypeProjet,
                      ForfaitCoutTypeProjet = :ForfaitCoutTypeProjet
                  WHERE IdTypeProjet = :IdTypeProjet";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->LibelleTypeProjet = htmlspecialchars(strip_tags($this->LibelleTypeProjet));
        $this->ForfaitCoutTypeProjet = htmlspecialchars(strip_tags($this->ForfaitCoutTypeProjet));
        $this->IdTypeProjet = htmlspecialchars(strip_tags($this->IdTypeProjet));
        
        // Bind values
        $stmt->bindParam(":LibelleTypeProjet", $this->LibelleTypeProjet);
        $stmt->bindParam(":ForfaitCoutTypeProjet", $this->ForfaitCoutTypeProjet);
        $stmt->bindParam(":IdTypeProjet", $this->IdTypeProjet);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete project type
     * 
     * @return boolean
     */
    public function delete() {
        // Check if type is used in projects
        if ($this->isUsedInProjects()) {
            return false;
        }
        
        // Query to delete record
        $query = "DELETE FROM " . $this->table_name . " WHERE IdTypeProjet = :IdTypeProjet";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdTypeProjet = htmlspecialchars(strip_tags($this->IdTypeProjet));
        
        // Bind value
        $stmt->bindParam(":IdTypeProjet", $this->IdTypeProjet);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if project type is used in projects
     * 
     * @return boolean
     */
    private function isUsedInProjects() {
        // Query to check if type is used
        $query = "SELECT COUNT(*) as count
                  FROM PROJET
                  WHERE IdTypeProjet = :IdTypeProjet";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(":IdTypeProjet", $this->IdTypeProjet);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Return true if type is used
        return $row['count'] > 0;
    }
    
    /**
     * Get project types dropdown
     * 
     * @return PDOStatement
     */
    public function getProjectTypesDropdown() {
        // Query to read all project types
        $query = "SELECT IdTypeProjet, LibelleTypeProjet, ForfaitCoutTypeProjet
                  FROM " . $this->table_name . "
                  ORDER BY LibelleTypeProjet ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Count projects by type
     * 
     * @return PDOStatement
     */
    public function countProjectsByType() {
        // Query to count projects by type
        $query = "SELECT tp.IdTypeProjet, tp.LibelleTypeProjet, COUNT(p.IdProjet) as projectCount
                  FROM " . $this->table_name . " tp
                  LEFT JOIN PROJET p ON tp.IdTypeProjet = p.IdTypeProjet
                  GROUP BY tp.IdTypeProjet
                  ORDER BY projectCount DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Search project types
     * 
     * @param string $keywords Keywords to search
     * @return PDOStatement
     */
    public function search($keywords) {
        // Query to search project types
        $query = "SELECT IdTypeProjet, LibelleTypeProjet, ForfaitCoutTypeProjet
                  FROM " . $this->table_name . "
                  WHERE LibelleTypeProjet LIKE :keywords
                  ORDER BY LibelleTypeProjet ASC";
        
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
     * Count project types
     * 
     * @return int
     */
    public function count() {
        // Query to count project types
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        // Get row
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
}
?>
