<?php
/**
 * Assignment Model
 * 
 * Handles assignment data operations
 */
class Assignment {
    // Database connection and table name
    private $conn;
    private $table_name = "AFFECTATION";
    
    // Assignment properties
    public $IdAffectation;
    public $DateAffectation;
    public $HeureAffectation;
    public $FonctionAffectation;
    public $MatriculePersonnel;
    public $IdTache;
    
    // Related properties
    public $NomPersonnel;
    public $PrenomPersonnel;
    public $LibelleTache;
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
     * Create assignment
     * 
     * @return boolean
     */
    public function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                  SET DateAffectation = :DateAffectation,
                      HeureAffectation = :HeureAffectation,
                      FonctionAffectation = :FonctionAffectation,
                      MatriculePersonnel = :MatriculePersonnel,
                      IdTache = :IdTache";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->DateAffectation = htmlspecialchars(strip_tags($this->DateAffectation));
        $this->HeureAffectation = htmlspecialchars(strip_tags($this->HeureAffectation));
        $this->FonctionAffectation = htmlspecialchars(strip_tags($this->FonctionAffectation));
        $this->MatriculePersonnel = htmlspecialchars(strip_tags($this->MatriculePersonnel));
        $this->IdTache = htmlspecialchars(strip_tags($this->IdTache));
        
        // Bind values
        $stmt->bindParam(":DateAffectation", $this->DateAffectation);
        $stmt->bindParam(":HeureAffectation", $this->HeureAffectation);
        $stmt->bindParam(":FonctionAffectation", $this->FonctionAffectation);
        $stmt->bindParam(":MatriculePersonnel", $this->MatriculePersonnel);
        $stmt->bindParam(":IdTache", $this->IdTache);
        
        // Execute query
        if ($stmt->execute()) {
            $this->IdAffectation = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Read all assignments
     * 
     * @param int $page Page number
     * @param int $perPage Records per page
     * @return PDOStatement
     */
    public function read($page = 1, $perPage = 10) {
        // Calculate the offset
        $offset = ($page - 1) * $perPage;
        
        // Query to read all assignments with pagination
        $query = "SELECT a.IdAffectation, a.DateAffectation, a.HeureAffectation, 
                         a.FonctionAffectation, a.MatriculePersonnel, a.IdTache,
                         p.NomPersonnel, p.PrenomPersonnel, t.LibelleTache, 
                         pr.TitreProjet
                  FROM " . $this->table_name . " a
                  LEFT JOIN PERSONNEL p ON a.MatriculePersonnel = p.MatriculePersonnel
                  LEFT JOIN TACHE t ON a.IdTache = t.IdTache
                  LEFT JOIN PROJET pr ON t.IdProjet = pr.IdProjet
                  ORDER BY a.DateAffectation DESC, a.HeureAffectation DESC
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
     * Read one assignment
     * 
     * @return boolean
     */
    public function readOne() {
        // Query to read one assignment
        $query = "SELECT a.IdAffectation, a.DateAffectation, a.HeureAffectation, 
                         a.FonctionAffectation, a.MatriculePersonnel, a.IdTache,
                         p.NomPersonnel, p.PrenomPersonnel, t.LibelleTache, 
                         pr.TitreProjet
                  FROM " . $this->table_name . " a
                  LEFT JOIN PERSONNEL p ON a.MatriculePersonnel = p.MatriculePersonnel
                  LEFT JOIN TACHE t ON a.IdTache = t.IdTache
                  LEFT JOIN PROJET pr ON t.IdProjet = pr.IdProjet
                  WHERE a.IdAffectation = :IdAffectation
                  LIMIT 1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdAffectation = htmlspecialchars(strip_tags($this->IdAffectation));
        
        // Bind values
        $stmt->bindParam(":IdAffectation", $this->IdAffectation);
        
        // Execute query
        $stmt->execute();
        
        // Get record details
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if record exists
        if (!$row) {
            return false;
        }
        
        // Set values to object properties
        $this->IdAffectation = $row['IdAffectation'];
        $this->DateAffectation = $row['DateAffectation'];
        $this->HeureAffectation = $row['HeureAffectation'];
        $this->FonctionAffectation = $row['FonctionAffectation'];
        $this->MatriculePersonnel = $row['MatriculePersonnel'];
        $this->IdTache = $row['IdTache'];
        $this->NomPersonnel = $row['NomPersonnel'];
        $this->PrenomPersonnel = $row['PrenomPersonnel'];
        $this->LibelleTache = $row['LibelleTache'];
        $this->TitreProjet = $row['TitreProjet'];
        
        return true;
    }
    
    /**
     * Update assignment
     * 
     * @return boolean
     */
    public function update() {
        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                  SET DateAffectation = :DateAffectation,
                      HeureAffectation = :HeureAffectation,
                      FonctionAffectation = :FonctionAffectation,
                      MatriculePersonnel = :MatriculePersonnel,
                      IdTache = :IdTache
                  WHERE IdAffectation = :IdAffectation";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->DateAffectation = htmlspecialchars(strip_tags($this->DateAffectation));
        $this->HeureAffectation = htmlspecialchars(strip_tags($this->HeureAffectation));
        $this->FonctionAffectation = htmlspecialchars(strip_tags($this->FonctionAffectation));
        $this->MatriculePersonnel = htmlspecialchars(strip_tags($this->MatriculePersonnel));
        $this->IdTache = htmlspecialchars(strip_tags($this->IdTache));
        $this->IdAffectation = htmlspecialchars(strip_tags($this->IdAffectation));
        
        // Bind values
        $stmt->bindParam(":DateAffectation", $this->DateAffectation);
        $stmt->bindParam(":HeureAffectation", $this->HeureAffectation);
        $stmt->bindParam(":FonctionAffectation", $this->FonctionAffectation);
        $stmt->bindParam(":MatriculePersonnel", $this->MatriculePersonnel);
        $stmt->bindParam(":IdTache", $this->IdTache);
        $stmt->bindParam(":IdAffectation", $this->IdAffectation);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete assignment
     * 
     * @return boolean
     */
    public function delete() {
        // Query to delete record
        $query = "DELETE FROM " . $this->table_name . " WHERE IdAffectation = :IdAffectation";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdAffectation = htmlspecialchars(strip_tags($this->IdAffectation));
        
        // Bind value
        $stmt->bindParam(":IdAffectation", $this->IdAffectation);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Search assignments
     * 
     * @param string $keywords Keywords to search
     * @return PDOStatement
     */
    public function search($keywords) {
        // Query to search assignments
        $query = "SELECT a.IdAffectation, a.DateAffectation, a.HeureAffectation, 
                         a.FonctionAffectation, a.MatriculePersonnel, a.IdTache,
                         p.NomPersonnel, p.PrenomPersonnel, t.LibelleTache, 
                         pr.TitreProjet
                  FROM " . $this->table_name . " a
                  LEFT JOIN PERSONNEL p ON a.MatriculePersonnel = p.MatriculePersonnel
                  LEFT JOIN TACHE t ON a.IdTache = t.IdTache
                  LEFT JOIN PROJET pr ON t.IdProjet = pr.IdProjet
                  WHERE p.NomPersonnel LIKE :keywords
                  OR p.PrenomPersonnel LIKE :keywords
                  OR t.LibelleTache LIKE :keywords
                  OR pr.TitreProjet LIKE :keywords
                  OR a.FonctionAffectation LIKE :keywords
                  ORDER BY a.DateAffectation DESC, a.HeureAffectation DESC";
        
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
     * Filter assignments by personnel
     * 
     * @param string $matricule Personnel matricule to filter
     * @return PDOStatement
     */
    public function filterByPersonnel($matricule) {
        // Query to filter assignments
        $query = "SELECT a.IdAffectation, a.DateAffectation, a.HeureAffectation, 
                         a.FonctionAffectation, a.MatriculePersonnel, a.IdTache,
                         p.NomPersonnel, p.PrenomPersonnel, t.LibelleTache, 
                         pr.TitreProjet
                  FROM " . $this->table_name . " a
                  LEFT JOIN PERSONNEL p ON a.MatriculePersonnel = p.MatriculePersonnel
                  LEFT JOIN TACHE t ON a.IdTache = t.IdTache
                  LEFT JOIN PROJET pr ON t.IdProjet = pr.IdProjet
                  WHERE a.MatriculePersonnel = :matricule
                  ORDER BY a.DateAffectation DESC, a.HeureAffectation DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $matricule = htmlspecialchars(strip_tags($matricule));
        
        // Bind values
        $stmt->bindParam(":matricule", $matricule);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Filter assignments by task
     * 
     * @param int $taskId Task ID to filter
     * @return PDOStatement
     */
    public function filterByTask($taskId) {
        // Query to filter assignments
        $query = "SELECT a.IdAffectation, a.DateAffectation, a.HeureAffectation, 
                         a.FonctionAffectation, a.MatriculePersonnel, a.IdTache,
                         p.NomPersonnel, p.PrenomPersonnel, t.LibelleTache, 
                         pr.TitreProjet
                  FROM " . $this->table_name . " a
                  LEFT JOIN PERSONNEL p ON a.MatriculePersonnel = p.MatriculePersonnel
                  LEFT JOIN TACHE t ON a.IdTache = t.IdTache
                  LEFT JOIN PROJET pr ON t.IdProjet = pr.IdProjet
                  WHERE a.IdTache = :taskId
                  ORDER BY a.DateAffectation DESC, a.HeureAffectation DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $taskId = htmlspecialchars(strip_tags($taskId));
        
        // Bind values
        $stmt->bindParam(":taskId", $taskId);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Filter assignments by date range
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return PDOStatement
     */
    public function filterByDateRange($startDate, $endDate) {
        // Query to filter assignments
        $query = "SELECT a.IdAffectation, a.DateAffectation, a.HeureAffectation, 
                         a.FonctionAffectation, a.MatriculePersonnel, a.IdTache,
                         p.NomPersonnel, p.PrenomPersonnel, t.LibelleTache, 
                         pr.TitreProjet
                  FROM " . $this->table_name . " a
                  LEFT JOIN PERSONNEL p ON a.MatriculePersonnel = p.MatriculePersonnel
                  LEFT JOIN TACHE t ON a.IdTache = t.IdTache
                  LEFT JOIN PROJET pr ON t.IdProjet = pr.IdProjet
                  WHERE a.DateAffectation BETWEEN :startDate AND :endDate
                  ORDER BY a.DateAffectation DESC, a.HeureAffectation DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $startDate = htmlspecialchars(strip_tags($startDate));
        $endDate = htmlspecialchars(strip_tags($endDate));
        
        // Bind values
        $stmt->bindParam(":startDate", $startDate);
        $stmt->bindParam(":endDate", $endDate);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Count assignments
     * 
     * @return int
     */
    public function count() {
        // Query to count assignments
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
     * Count assignments by personnel
     * 
     * @return PDOStatement
     */
    public function countByPersonnel() {
        // Query to count assignments by personnel
        $query = "SELECT p.MatriculePersonnel, p.NomPersonnel, p.PrenomPersonnel, 
                         COUNT(a.IdAffectation) as assignmentCount
                  FROM PERSONNEL p
                  LEFT JOIN " . $this->table_name . " a ON p.MatriculePersonnel = a.MatriculePersonnel
                  GROUP BY p.MatriculePersonnel
                  ORDER BY assignmentCount DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Count assignments by task
     * 
     * @return PDOStatement
     */
    public function countByTask() {
        // Query to count assignments by task
        $query = "SELECT t.IdTache, t.LibelleTache, pr.TitreProjet, 
                         COUNT(a.IdAffectation) as assignmentCount
                  FROM TACHE t
                  LEFT JOIN " . $this->table_name . " a ON t.IdTache = a.IdTache
                  LEFT JOIN PROJET pr ON t.IdProjet = pr.IdProjet
                  GROUP BY t.IdTache
                  ORDER BY assignmentCount DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
}
?>
