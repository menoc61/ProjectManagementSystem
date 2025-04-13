<?php
/**
 * Task Model
 * 
 * Handles task data operations
 */
class Task {
    // Database connection and table name
    private $conn;
    private $table_name = "TACHE";
    
    // Task properties
    public $IdTache;
    public $LibelleTache;
    public $DateEnregTache;
    public $DateDebutTache;
    public $DateFinTache;
    public $EtatTache;
    public $IdProjet;
    
    // Related properties
    public $TitreProjet;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create task
     * 
     * @return boolean
     */
    public function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                  SET LibelleTache = :LibelleTache,
                      DateEnregTache = CURDATE(),
                      DateDebutTache = :DateDebutTache,
                      DateFinTache = :DateFinTache,
                      EtatTache = :EtatTache,
                      IdProjet = :IdProjet";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->LibelleTache = htmlspecialchars(strip_tags($this->LibelleTache));
        $this->DateDebutTache = htmlspecialchars(strip_tags($this->DateDebutTache));
        $this->DateFinTache = htmlspecialchars(strip_tags($this->DateFinTache));
        $this->EtatTache = htmlspecialchars(strip_tags($this->EtatTache));
        $this->IdProjet = htmlspecialchars(strip_tags($this->IdProjet));
        
        // Bind values
        $stmt->bindParam(":LibelleTache", $this->LibelleTache);
        $stmt->bindParam(":DateDebutTache", $this->DateDebutTache);
        $stmt->bindParam(":DateFinTache", $this->DateFinTache);
        $stmt->bindParam(":EtatTache", $this->EtatTache);
        $stmt->bindParam(":IdProjet", $this->IdProjet);
        
        // Execute query
        if ($stmt->execute()) {
            $this->IdTache = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Read all tasks
     * 
     * @param int $page Page number
     * @param int $perPage Records per page
     * @return PDOStatement
     */
    public function read($page = 1, $perPage = 10) {
        // Calculate the offset
        $offset = ($page - 1) * $perPage;
        
        // Query to read all tasks with pagination
        $query = "SELECT t.IdTache, t.LibelleTache, t.DateEnregTache, t.DateDebutTache, 
                         t.DateFinTache, t.EtatTache, t.IdProjet, p.TitreProjet
                  FROM " . $this->table_name . " t
                  LEFT JOIN PROJET p ON t.IdProjet = p.IdProjet
                  ORDER BY t.DateDebutTache DESC
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
     * Read one task
     * 
     * @return boolean
     */
    public function readOne() {
        // Query to read one task
        $query = "SELECT t.IdTache, t.LibelleTache, t.DateEnregTache, t.DateDebutTache, 
                         t.DateFinTache, t.EtatTache, t.IdProjet, p.TitreProjet
                  FROM " . $this->table_name . " t
                  LEFT JOIN PROJET p ON t.IdProjet = p.IdProjet
                  WHERE t.IdTache = :IdTache
                  LIMIT 1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdTache = htmlspecialchars(strip_tags($this->IdTache));
        
        // Bind values
        $stmt->bindParam(":IdTache", $this->IdTache);
        
        // Execute query
        $stmt->execute();
        
        // Get record details
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if record exists
        if (!$row) {
            return false;
        }
        
        // Set values to object properties
        $this->IdTache = $row['IdTache'];
        $this->LibelleTache = $row['LibelleTache'];
        $this->DateEnregTache = $row['DateEnregTache'];
        $this->DateDebutTache = $row['DateDebutTache'];
        $this->DateFinTache = $row['DateFinTache'];
        $this->EtatTache = $row['EtatTache'];
        $this->IdProjet = $row['IdProjet'];
        $this->TitreProjet = $row['TitreProjet'];
        
        return true;
    }
    
    /**
     * Update task
     * 
     * @return boolean
     */
    public function update() {
        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                  SET LibelleTache = :LibelleTache,
                      DateDebutTache = :DateDebutTache,
                      DateFinTache = :DateFinTache,
                      EtatTache = :EtatTache,
                      IdProjet = :IdProjet
                  WHERE IdTache = :IdTache";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->LibelleTache = htmlspecialchars(strip_tags($this->LibelleTache));
        $this->DateDebutTache = htmlspecialchars(strip_tags($this->DateDebutTache));
        $this->DateFinTache = htmlspecialchars(strip_tags($this->DateFinTache));
        $this->EtatTache = htmlspecialchars(strip_tags($this->EtatTache));
        $this->IdProjet = htmlspecialchars(strip_tags($this->IdProjet));
        $this->IdTache = htmlspecialchars(strip_tags($this->IdTache));
        
        // Bind values
        $stmt->bindParam(":LibelleTache", $this->LibelleTache);
        $stmt->bindParam(":DateDebutTache", $this->DateDebutTache);
        $stmt->bindParam(":DateFinTache", $this->DateFinTache);
        $stmt->bindParam(":EtatTache", $this->EtatTache);
        $stmt->bindParam(":IdProjet", $this->IdProjet);
        $stmt->bindParam(":IdTache", $this->IdTache);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete task
     * 
     * @return boolean
     */
    public function delete() {
        // First, delete related assignments
        $this->deleteRelatedAssignments();
        
        // Query to delete record
        $query = "DELETE FROM " . $this->table_name . " WHERE IdTache = :IdTache";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdTache = htmlspecialchars(strip_tags($this->IdTache));
        
        // Bind value
        $stmt->bindParam(":IdTache", $this->IdTache);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete related assignments
     * 
     * @return void
     */
    private function deleteRelatedAssignments() {
        $query = "DELETE FROM AFFECTATION WHERE IdTache = :IdTache";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":IdTache", $this->IdTache);
        $stmt->execute();
    }
    
    /**
     * Search tasks
     * 
     * @param string $keywords Keywords to search
     * @return PDOStatement
     */
    public function search($keywords) {
        // Query to search tasks
        $query = "SELECT t.IdTache, t.LibelleTache, t.DateEnregTache, t.DateDebutTache, 
                         t.DateFinTache, t.EtatTache, t.IdProjet, p.TitreProjet
                  FROM " . $this->table_name . " t
                  LEFT JOIN PROJET p ON t.IdProjet = p.IdProjet
                  WHERE t.LibelleTache LIKE :keywords 
                  OR p.TitreProjet LIKE :keywords
                  ORDER BY t.DateDebutTache DESC";
        
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
     * Filter tasks by status
     * 
     * @param int $status Status to filter
     * @return PDOStatement
     */
    public function filterByStatus($status) {
        // Query to filter tasks
        $query = "SELECT t.IdTache, t.LibelleTache, t.DateEnregTache, t.DateDebutTache, 
                         t.DateFinTache, t.EtatTache, t.IdProjet, p.TitreProjet
                  FROM " . $this->table_name . " t
                  LEFT JOIN PROJET p ON t.IdProjet = p.IdProjet
                  WHERE t.EtatTache = :status
                  ORDER BY t.DateDebutTache DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $status = htmlspecialchars(strip_tags($status));
        
        // Bind values
        $stmt->bindParam(":status", $status);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Filter tasks by project
     * 
     * @param int $projectId Project ID to filter
     * @return PDOStatement
     */
    public function filterByProject($projectId) {
        // Query to filter tasks
        $query = "SELECT t.IdTache, t.LibelleTache, t.DateEnregTache, t.DateDebutTache, 
                         t.DateFinTache, t.EtatTache, t.IdProjet, p.TitreProjet
                  FROM " . $this->table_name . " t
                  LEFT JOIN PROJET p ON t.IdProjet = p.IdProjet
                  WHERE t.IdProjet = :projectId
                  ORDER BY t.DateDebutTache DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $projectId = htmlspecialchars(strip_tags($projectId));
        
        // Bind values
        $stmt->bindParam(":projectId", $projectId);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get task assignments
     * 
     * @return PDOStatement
     */
    public function getAssignments() {
        // Query to get task assignments
        $query = "SELECT a.IdAffectation, a.DateAffectation, a.HeureAffectation, 
                         a.FonctionAffectation, a.MatriculePersonnel,
                         p.NomPersonnel, p.PrenomPersonnel
                  FROM AFFECTATION a
                  LEFT JOIN PERSONNEL p ON a.MatriculePersonnel = p.MatriculePersonnel
                  WHERE a.IdTache = :IdTache
                  ORDER BY a.DateAffectation DESC, a.HeureAffectation DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdTache = htmlspecialchars(strip_tags($this->IdTache));
        
        // Bind values
        $stmt->bindParam(":IdTache", $this->IdTache);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Count tasks
     * 
     * @return int
     */
    public function count() {
        // Query to count tasks
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
     * Count tasks by status
     * 
     * @return array
     */
    public function countByStatus() {
        // Query to count tasks by status
        $query = "SELECT EtatTache, COUNT(*) as count
                  FROM " . $this->table_name . "
                  GROUP BY EtatTache";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        // Initialize results array
        $results = [
            TASK_STATUS_PENDING => 0,
            TASK_STATUS_IN_PROGRESS => 0,
            TASK_STATUS_COMPLETED => 0,
            TASK_STATUS_CANCELLED => 0
        ];
        
        // Populate results
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[$row['EtatTache']] = $row['count'];
        }
        
        return $results;
    }
    
    /**
     * Get tasks for dropdown
     * 
     * @return PDOStatement
     */
    public function getTasksDropdown() {
        // Query to read tasks for dropdown
        $query = "SELECT t.IdTache, t.LibelleTache, p.TitreProjet
                  FROM " . $this->table_name . " t
                  LEFT JOIN PROJET p ON t.IdProjet = p.IdProjet
                  ORDER BY p.TitreProjet ASC, t.LibelleTache ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
}
?>
