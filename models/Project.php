<?php
/**
 * Project Model
 * 
 * Handles project data operations
 */
class Project {
    // Database connection and table name
    private $conn;
    private $table_name = "PROJET";
    
    // Project properties
    public $IdProjet;
    public $TitreProjet;
    public $DescriptionProjet;
    public $CoutProjet;
    public $DateDebutProjet;
    public $DateFinProjet;
    public $EtatProjet;
    public $IdClient;
    public $IdTypeProjet;
    
    // Related data
    public $NomClient;
    public $LibelleTypeProjet;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create project
     * 
     * @return boolean
     */
    public function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                  SET TitreProjet = :TitreProjet,
                      DescriptionProjet = :DescriptionProjet,
                      CoutProjet = :CoutProjet,
                      DateDebutProjet = :DateDebutProjet,
                      DateFinProjet = :DateFinProjet,
                      EtatProjet = :EtatProjet,
                      IdClient = :IdClient,
                      IdTypeProjet = :IdTypeProjet";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->TitreProjet = htmlspecialchars(strip_tags($this->TitreProjet));
        $this->DescriptionProjet = htmlspecialchars(strip_tags($this->DescriptionProjet));
        $this->CoutProjet = htmlspecialchars(strip_tags($this->CoutProjet));
        $this->DateDebutProjet = htmlspecialchars(strip_tags($this->DateDebutProjet));
        $this->DateFinProjet = htmlspecialchars(strip_tags($this->DateFinProjet));
        $this->EtatProjet = htmlspecialchars(strip_tags($this->EtatProjet));
        $this->IdClient = htmlspecialchars(strip_tags($this->IdClient));
        $this->IdTypeProjet = htmlspecialchars(strip_tags($this->IdTypeProjet));
        
        // Bind values
        $stmt->bindParam(":TitreProjet", $this->TitreProjet);
        $stmt->bindParam(":DescriptionProjet", $this->DescriptionProjet);
        $stmt->bindParam(":CoutProjet", $this->CoutProjet);
        $stmt->bindParam(":DateDebutProjet", $this->DateDebutProjet);
        $stmt->bindParam(":DateFinProjet", $this->DateFinProjet);
        $stmt->bindParam(":EtatProjet", $this->EtatProjet);
        $stmt->bindParam(":IdClient", $this->IdClient);
        $stmt->bindParam(":IdTypeProjet", $this->IdTypeProjet);
        
        // Execute query
        if ($stmt->execute()) {
            $this->IdProjet = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Read all projects
     * 
     * @param int $page Page number
     * @param int $perPage Records per page
     * @return PDOStatement
     */
    public function read($page = 1, $perPage = 10) {
        // Calculate the offset
        $offset = ($page - 1) * $perPage;
        
        // Query to read all projects with pagination
        $query = "SELECT p.IdProjet, p.TitreProjet, p.DescriptionProjet, p.CoutProjet, 
                         p.DateDebutProjet, p.DateFinProjet, p.EtatProjet, p.IdClient, p.IdTypeProjet,
                         c.NomClient, tp.LibelleTypeProjet
                  FROM " . $this->table_name . " p
                  LEFT JOIN CLIENT c ON p.IdClient = c.IdClient
                  LEFT JOIN TYPEPROJET tp ON p.IdTypeProjet = tp.IdTypeProjet
                  ORDER BY p.DateDebutProjet DESC
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
     * Read one project
     * 
     * @return boolean
     */
    public function readOne() {
        // Query to read one project
        $query = "SELECT p.IdProjet, p.TitreProjet, p.DescriptionProjet, p.CoutProjet, 
                         p.DateDebutProjet, p.DateFinProjet, p.EtatProjet, p.IdClient, p.IdTypeProjet,
                         c.NomClient, tp.LibelleTypeProjet
                  FROM " . $this->table_name . " p
                  LEFT JOIN CLIENT c ON p.IdClient = c.IdClient
                  LEFT JOIN TYPEPROJET tp ON p.IdTypeProjet = tp.IdTypeProjet
                  WHERE p.IdProjet = :IdProjet
                  LIMIT 1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdProjet = htmlspecialchars(strip_tags($this->IdProjet));
        
        // Bind values
        $stmt->bindParam(":IdProjet", $this->IdProjet);
        
        // Execute query
        $stmt->execute();
        
        // Get record details
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if record exists
        if (!$row) {
            return false;
        }
        
        // Set values to object properties
        $this->IdProjet = $row['IdProjet'];
        $this->TitreProjet = $row['TitreProjet'];
        $this->DescriptionProjet = $row['DescriptionProjet'];
        $this->CoutProjet = $row['CoutProjet'];
        $this->DateDebutProjet = $row['DateDebutProjet'];
        $this->DateFinProjet = $row['DateFinProjet'];
        $this->EtatProjet = $row['EtatProjet'];
        $this->IdClient = $row['IdClient'];
        $this->IdTypeProjet = $row['IdTypeProjet'];
        $this->NomClient = $row['NomClient'];
        $this->LibelleTypeProjet = $row['LibelleTypeProjet'];
        
        return true;
    }
    
    /**
     * Update project
     * 
     * @return boolean
     */
    public function update() {
        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                  SET TitreProjet = :TitreProjet,
                      DescriptionProjet = :DescriptionProjet,
                      CoutProjet = :CoutProjet,
                      DateDebutProjet = :DateDebutProjet,
                      DateFinProjet = :DateFinProjet,
                      EtatProjet = :EtatProjet,
                      IdClient = :IdClient,
                      IdTypeProjet = :IdTypeProjet
                  WHERE IdProjet = :IdProjet";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->TitreProjet = htmlspecialchars(strip_tags($this->TitreProjet));
        $this->DescriptionProjet = htmlspecialchars(strip_tags($this->DescriptionProjet));
        $this->CoutProjet = htmlspecialchars(strip_tags($this->CoutProjet));
        $this->DateDebutProjet = htmlspecialchars(strip_tags($this->DateDebutProjet));
        $this->DateFinProjet = htmlspecialchars(strip_tags($this->DateFinProjet));
        $this->EtatProjet = htmlspecialchars(strip_tags($this->EtatProjet));
        $this->IdClient = htmlspecialchars(strip_tags($this->IdClient));
        $this->IdTypeProjet = htmlspecialchars(strip_tags($this->IdTypeProjet));
        $this->IdProjet = htmlspecialchars(strip_tags($this->IdProjet));
        
        // Bind values
        $stmt->bindParam(":TitreProjet", $this->TitreProjet);
        $stmt->bindParam(":DescriptionProjet", $this->DescriptionProjet);
        $stmt->bindParam(":CoutProjet", $this->CoutProjet);
        $stmt->bindParam(":DateDebutProjet", $this->DateDebutProjet);
        $stmt->bindParam(":DateFinProjet", $this->DateFinProjet);
        $stmt->bindParam(":EtatProjet", $this->EtatProjet);
        $stmt->bindParam(":IdClient", $this->IdClient);
        $stmt->bindParam(":IdTypeProjet", $this->IdTypeProjet);
        $stmt->bindParam(":IdProjet", $this->IdProjet);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete project
     * 
     * @return boolean
     */
    public function delete() {
        // First, delete related tasks
        $this->deleteRelatedTasks();
        
        // Query to delete record
        $query = "DELETE FROM " . $this->table_name . " WHERE IdProjet = :IdProjet";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdProjet = htmlspecialchars(strip_tags($this->IdProjet));
        
        // Bind value
        $stmt->bindParam(":IdProjet", $this->IdProjet);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete related tasks
     * 
     * @return void
     */
    private function deleteRelatedTasks() {
        // First, delete assignments related to tasks
        $query = "DELETE a FROM AFFECTATION a
                  INNER JOIN TACHE t ON a.IdTache = t.IdTache
                  WHERE t.IdProjet = :IdProjet";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":IdProjet", $this->IdProjet);
        $stmt->execute();
        
        // Then, delete tasks
        $query = "DELETE FROM TACHE WHERE IdProjet = :IdProjet";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":IdProjet", $this->IdProjet);
        $stmt->execute();
    }
    
    /**
     * Search projects
     * 
     * @param string $keywords Keywords to search
     * @return PDOStatement
     */
    public function search($keywords) {
        // Query to search projects
        $query = "SELECT p.IdProjet, p.TitreProjet, p.DescriptionProjet, p.CoutProjet, 
                         p.DateDebutProjet, p.DateFinProjet, p.EtatProjet, p.IdClient, p.IdTypeProjet,
                         c.NomClient, tp.LibelleTypeProjet
                  FROM " . $this->table_name . " p
                  LEFT JOIN CLIENT c ON p.IdClient = c.IdClient
                  LEFT JOIN TYPEPROJET tp ON p.IdTypeProjet = tp.IdTypeProjet
                  WHERE p.TitreProjet LIKE :keywords 
                  OR p.DescriptionProjet LIKE :keywords 
                  OR c.NomClient LIKE :keywords
                  ORDER BY p.DateDebutProjet DESC";
        
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
     * Filter projects by status
     * 
     * @param int $status Status to filter
     * @return PDOStatement
     */
    public function filterByStatus($status) {
        // Query to filter projects
        $query = "SELECT p.IdProjet, p.TitreProjet, p.DescriptionProjet, p.CoutProjet, 
                         p.DateDebutProjet, p.DateFinProjet, p.EtatProjet, p.IdClient, p.IdTypeProjet,
                         c.NomClient, tp.LibelleTypeProjet
                  FROM " . $this->table_name . " p
                  LEFT JOIN CLIENT c ON p.IdClient = c.IdClient
                  LEFT JOIN TYPEPROJET tp ON p.IdTypeProjet = tp.IdTypeProjet
                  WHERE p.EtatProjet = :status
                  ORDER BY p.DateDebutProjet DESC";
        
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
     * Filter projects by client
     * 
     * @param int $clientId Client ID to filter
     * @return PDOStatement
     */
    public function filterByClient($clientId) {
        // Query to filter projects
        $query = "SELECT p.IdProjet, p.TitreProjet, p.DescriptionProjet, p.CoutProjet, 
                         p.DateDebutProjet, p.DateFinProjet, p.EtatProjet, p.IdClient, p.IdTypeProjet,
                         c.NomClient, tp.LibelleTypeProjet
                  FROM " . $this->table_name . " p
                  LEFT JOIN CLIENT c ON p.IdClient = c.IdClient
                  LEFT JOIN TYPEPROJET tp ON p.IdTypeProjet = tp.IdTypeProjet
                  WHERE p.IdClient = :clientId
                  ORDER BY p.DateDebutProjet DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $clientId = htmlspecialchars(strip_tags($clientId));
        
        // Bind values
        $stmt->bindParam(":clientId", $clientId);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Filter projects by date range
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return PDOStatement
     */
    public function filterByDateRange($startDate, $endDate) {
        // Query to filter projects
        $query = "SELECT p.IdProjet, p.TitreProjet, p.DescriptionProjet, p.CoutProjet, 
                         p.DateDebutProjet, p.DateFinProjet, p.EtatProjet, p.IdClient, p.IdTypeProjet,
                         c.NomClient, tp.LibelleTypeProjet
                  FROM " . $this->table_name . " p
                  LEFT JOIN CLIENT c ON p.IdClient = c.IdClient
                  LEFT JOIN TYPEPROJET tp ON p.IdTypeProjet = tp.IdTypeProjet
                  WHERE p.DateDebutProjet BETWEEN :startDate AND :endDate
                  ORDER BY p.DateDebutProjet DESC";
        
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
     * Get project tasks
     * 
     * @return PDOStatement
     */
    public function getTasks() {
        // Query to get project tasks
        $query = "SELECT IdTache, LibelleTache, DateEnregTache, DateDebutTache, DateFinTache, EtatTache
                  FROM TACHE
                  WHERE IdProjet = :IdProjet
                  ORDER BY DateDebutTache ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdProjet = htmlspecialchars(strip_tags($this->IdProjet));
        
        // Bind values
        $stmt->bindParam(":IdProjet", $this->IdProjet);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Count projects
     * 
     * @return int
     */
    public function count() {
        // Query to count projects
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
     * Count projects by status
     * 
     * @return array
     */
    public function countByStatus() {
        // Query to count projects by status
        $query = "SELECT EtatProjet, COUNT(*) as count
                  FROM " . $this->table_name . "
                  GROUP BY EtatProjet";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        // Initialize results array
        $results = [
            PROJECT_STATUS_PENDING => 0,
            PROJECT_STATUS_IN_PROGRESS => 0,
            PROJECT_STATUS_COMPLETED => 0,
            PROJECT_STATUS_CANCELLED => 0
        ];
        
        // Populate results
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[$row['EtatProjet']] = $row['count'];
        }
        
        return $results;
    }
    
    /**
     * Get total cost of projects
     * 
     * @return float
     */
    public function getTotalCost() {
        // Query to get total cost
        $query = "SELECT SUM(CoutProjet) as total FROM " . $this->table_name;
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ? $row['total'] : 0;
    }
    
    /**
     * Get total cost by client
     * 
     * @return PDOStatement
     */
    public function getTotalCostByClient() {
        // Query to get total cost by client
        $query = "SELECT c.IdClient, c.NomClient, SUM(p.CoutProjet) as total
                  FROM " . $this->table_name . " p
                  JOIN CLIENT c ON p.IdClient = c.IdClient
                  GROUP BY c.IdClient
                  ORDER BY total DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get total cost by period (month)
     * 
     * @param int $year Year to filter
     * @return array
     */
    public function getTotalCostByMonth($year) {
        // Query to get total cost by month
        $query = "SELECT MONTH(DateDebutProjet) as month, SUM(CoutProjet) as total
                  FROM " . $this->table_name . "
                  WHERE YEAR(DateDebutProjet) = :year
                  GROUP BY MONTH(DateDebutProjet)
                  ORDER BY month ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind value
        $stmt->bindParam(":year", $year);
        
        // Execute query
        $stmt->execute();
        
        // Initialize results array
        $results = array_fill(1, 12, 0);
        
        // Populate results
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[$row['month']] = $row['total'];
        }
        
        return $results;
    }
    
    /**
     * Get completion rate
     * 
     * @return float
     */
    public function getCompletionRate() {
        // Get counts by status
        $counts = $this->countByStatus();
        
        // Calculate total projects (excluding cancelled)
        $total = $counts[PROJECT_STATUS_PENDING] + 
                 $counts[PROJECT_STATUS_IN_PROGRESS] + 
                 $counts[PROJECT_STATUS_COMPLETED];
        
        // Calculate completion rate
        if ($total > 0) {
            return ($counts[PROJECT_STATUS_COMPLETED] / $total) * 100;
        }
        
        return 0;
    }
    
    /**
     * Get projects for dropdown
     * 
     * @return PDOStatement
     */
    public function getProjectsDropdown() {
        // Query to read projects for dropdown
        $query = "SELECT IdProjet, TitreProjet
                  FROM " . $this->table_name . "
                  ORDER BY TitreProjet ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
}
?>
