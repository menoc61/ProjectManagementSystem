<?php
/**
 * Personnel Model
 * 
 * Handles personnel data operations
 */
class Personnel {
    // Database connection and table name
    private $conn;
    private $table_name = "PERSONNEL";
    
    // Personnel properties
    public $MatriculePersonnel;
    public $NomPersonnel;
    public $PrenomPersonnel;
    public $EmailPersonnel;
    public $TelPersonnel;
    public $CodeService;
    
    // Related properties
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
     * Create personnel
     * 
     * @return boolean
     */
    public function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                  SET MatriculePersonnel = :MatriculePersonnel,
                      NomPersonnel = :NomPersonnel,
                      PrenomPersonnel = :PrenomPersonnel,
                      EmailPersonnel = :EmailPersonnel,
                      TelPersonnel = :TelPersonnel,
                      CodeService = :CodeService";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->MatriculePersonnel = htmlspecialchars(strip_tags($this->MatriculePersonnel));
        $this->NomPersonnel = htmlspecialchars(strip_tags($this->NomPersonnel));
        $this->PrenomPersonnel = htmlspecialchars(strip_tags($this->PrenomPersonnel));
        $this->EmailPersonnel = htmlspecialchars(strip_tags($this->EmailPersonnel));
        $this->TelPersonnel = htmlspecialchars(strip_tags($this->TelPersonnel));
        $this->CodeService = htmlspecialchars(strip_tags($this->CodeService));
        
        // Bind values
        $stmt->bindParam(":MatriculePersonnel", $this->MatriculePersonnel);
        $stmt->bindParam(":NomPersonnel", $this->NomPersonnel);
        $stmt->bindParam(":PrenomPersonnel", $this->PrenomPersonnel);
        $stmt->bindParam(":EmailPersonnel", $this->EmailPersonnel);
        $stmt->bindParam(":TelPersonnel", $this->TelPersonnel);
        $stmt->bindParam(":CodeService", $this->CodeService);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Read all personnel
     * 
     * @param int $page Page number
     * @param int $perPage Records per page
     * @return PDOStatement
     */
    public function read($page = 1, $perPage = 10) {
        // Calculate the offset
        $offset = ($page - 1) * $perPage;
        
        // Query to read all personnel with pagination
        $query = "SELECT p.MatriculePersonnel, p.NomPersonnel, p.PrenomPersonnel, 
                         p.EmailPersonnel, p.TelPersonnel, p.CodeService, 
                         s.LibelleService
                  FROM " . $this->table_name . " p
                  LEFT JOIN SERVICE s ON p.CodeService = s.CodeService
                  ORDER BY p.NomPersonnel ASC, p.PrenomPersonnel ASC
                  LIMIT :offset, :perPage";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindParam(":perPage", $perPage, PDO::PARAM_INT);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Read one personnel
     * 
     * @return boolean
     */
    public function readOne() {
        // Query to read one personnel
        $query = "SELECT p.MatriculePersonnel, p.NomPersonnel, p.PrenomPersonnel, 
                         p.EmailPersonnel, p.TelPersonnel, p.CodeService, 
                         s.LibelleService
                  FROM " . $this->table_name . " p
                  LEFT JOIN SERVICE s ON p.CodeService = s.CodeService
                  WHERE p.MatriculePersonnel = :MatriculePersonnel
                  LIMIT 1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->MatriculePersonnel = htmlspecialchars(strip_tags($this->MatriculePersonnel));
        
        // Bind values
        $stmt->bindParam(":MatriculePersonnel", $this->MatriculePersonnel);
        
        // Execute query
        $stmt->execute();
        
        // Get record details
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if record exists
        if (!$row) {
            return false;
        }
        
        // Set values to object properties
        $this->MatriculePersonnel = $row['MatriculePersonnel'];
        $this->NomPersonnel = $row['NomPersonnel'];
        $this->PrenomPersonnel = $row['PrenomPersonnel'];
        $this->EmailPersonnel = $row['EmailPersonnel'];
        $this->TelPersonnel = $row['TelPersonnel'];
        $this->CodeService = $row['CodeService'];
        $this->LibelleService = $row['LibelleService'];
        
        return true;
    }
    
    /**
     * Update personnel
     * 
     * @return boolean
     */
    public function update() {
        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                  SET NomPersonnel = :NomPersonnel,
                      PrenomPersonnel = :PrenomPersonnel,
                      EmailPersonnel = :EmailPersonnel,
                      TelPersonnel = :TelPersonnel,
                      CodeService = :CodeService
                  WHERE MatriculePersonnel = :MatriculePersonnel";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->NomPersonnel = htmlspecialchars(strip_tags($this->NomPersonnel));
        $this->PrenomPersonnel = htmlspecialchars(strip_tags($this->PrenomPersonnel));
        $this->EmailPersonnel = htmlspecialchars(strip_tags($this->EmailPersonnel));
        $this->TelPersonnel = htmlspecialchars(strip_tags($this->TelPersonnel));
        $this->CodeService = htmlspecialchars(strip_tags($this->CodeService));
        $this->MatriculePersonnel = htmlspecialchars(strip_tags($this->MatriculePersonnel));
        
        // Bind values
        $stmt->bindParam(":NomPersonnel", $this->NomPersonnel);
        $stmt->bindParam(":PrenomPersonnel", $this->PrenomPersonnel);
        $stmt->bindParam(":EmailPersonnel", $this->EmailPersonnel);
        $stmt->bindParam(":TelPersonnel", $this->TelPersonnel);
        $stmt->bindParam(":CodeService", $this->CodeService);
        $stmt->bindParam(":MatriculePersonnel", $this->MatriculePersonnel);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete personnel
     * 
     * @return boolean
     */
    public function delete() {
        // Check if personnel has assignments
        if ($this->hasAssignments()) {
            return false;
        }
        
        // Query to delete record
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE MatriculePersonnel = :MatriculePersonnel";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->MatriculePersonnel = htmlspecialchars(strip_tags($this->MatriculePersonnel));
        
        // Bind value
        $stmt->bindParam(":MatriculePersonnel", $this->MatriculePersonnel);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if personnel has assignments
     * 
     * @return boolean
     */
    private function hasAssignments() {
        // Query to check if personnel has assignments
        $query = "SELECT COUNT(*) as count
                  FROM AFFECTATION
                  WHERE MatriculePersonnel = :MatriculePersonnel";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(":MatriculePersonnel", $this->MatriculePersonnel);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Return true if personnel has assignments
        return $row['count'] > 0;
    }
    
    /**
     * Search personnel
     * 
     * @param string $keywords Keywords to search
     * @return PDOStatement
     */
    public function search($keywords) {
        // Query to search personnel
        $query = "SELECT p.MatriculePersonnel, p.NomPersonnel, p.PrenomPersonnel, 
                         p.EmailPersonnel, p.TelPersonnel, p.CodeService, 
                         s.LibelleService
                  FROM " . $this->table_name . " p
                  LEFT JOIN SERVICE s ON p.CodeService = s.CodeService
                  WHERE p.NomPersonnel LIKE :keywords
                  OR p.PrenomPersonnel LIKE :keywords
                  OR p.EmailPersonnel LIKE :keywords
                  OR p.TelPersonnel LIKE :keywords
                  OR p.MatriculePersonnel LIKE :keywords
                  ORDER BY p.NomPersonnel ASC, p.PrenomPersonnel ASC";
        
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
     * Filter personnel by service
     * 
     * @param string $serviceCode Service code to filter
     * @return PDOStatement
     */
    public function filterByService($serviceCode) {
        // Query to filter personnel
        $query = "SELECT p.MatriculePersonnel, p.NomPersonnel, p.PrenomPersonnel, 
                         p.EmailPersonnel, p.TelPersonnel, p.CodeService, 
                         s.LibelleService
                  FROM " . $this->table_name . " p
                  LEFT JOIN SERVICE s ON p.CodeService = s.CodeService
                  WHERE p.CodeService = :serviceCode
                  ORDER BY p.NomPersonnel ASC, p.PrenomPersonnel ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $serviceCode = htmlspecialchars(strip_tags($serviceCode));
        
        // Bind values
        $stmt->bindParam(":serviceCode", $serviceCode);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get personnel assignments
     * 
     * @return PDOStatement
     */
    public function getAssignments() {
        // Query to get personnel assignments
        $query = "SELECT a.IdAffectation, a.DateAffectation, a.HeureAffectation, 
                         a.FonctionAffectation, a.IdTache,
                         t.LibelleTache, p.TitreProjet
                  FROM AFFECTATION a
                  LEFT JOIN TACHE t ON a.IdTache = t.IdTache
                  LEFT JOIN PROJET p ON t.IdProjet = p.IdProjet
                  WHERE a.MatriculePersonnel = :MatriculePersonnel
                  ORDER BY a.DateAffectation DESC, a.HeureAffectation DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->MatriculePersonnel = htmlspecialchars(strip_tags($this->MatriculePersonnel));
        
        // Bind values
        $stmt->bindParam(":MatriculePersonnel", $this->MatriculePersonnel);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Count personnel
     * 
     * @return int
     */
    public function count() {
        // Query to count personnel
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
     * Get personnel for dropdown
     * 
     * @return PDOStatement
     */
    public function getPersonnelDropdown() {
        // Query to read personnel for dropdown
        $query = "SELECT MatriculePersonnel, NomPersonnel, PrenomPersonnel
                  FROM " . $this->table_name . "
                  ORDER BY NomPersonnel ASC, PrenomPersonnel ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get most assigned personnel
     * 
     * @param int $limit Number of records to return
     * @return PDOStatement
     */
    public function getMostAssignedPersonnel($limit = 5) {
        // Query to get most assigned personnel
        $query = "SELECT p.MatriculePersonnel, p.NomPersonnel, p.PrenomPersonnel, 
                         COUNT(a.IdAffectation) as assignmentCount
                  FROM " . $this->table_name . " p
                  LEFT JOIN AFFECTATION a ON p.MatriculePersonnel = a.MatriculePersonnel
                  GROUP BY p.MatriculePersonnel
                  ORDER BY assignmentCount DESC
                  LIMIT :limit";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind limit
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
}
?>
