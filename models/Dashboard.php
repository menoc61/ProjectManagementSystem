<?php
/**
 * Dashboard Model
 * 
 * Handles dashboard data operations
 */
class Dashboard {
    // Database connection
    private $conn;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get projects count by type
     * 
     * @return PDOStatement
     */
    public function getProjectsByType() {
        // Query to get projects count by type
        $query = "SELECT tp.IdTypeProjet, tp.LibelleTypeProjet, COUNT(p.IdProjet) as count
                  FROM TYPEPROJET tp
                  LEFT JOIN PROJET p ON tp.IdTypeProjet = p.IdTypeProjet
                  GROUP BY tp.IdTypeProjet
                  ORDER BY count DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get most active clients
     * 
     * @param int $limit Number of clients to return
     * @return PDOStatement
     */
    public function getMostActiveClients($limit = 5) {
        // Query to get most active clients
        $query = "SELECT c.IdClient, c.NomClient, COUNT(p.IdProjet) as projectCount
                  FROM CLIENT c
                  LEFT JOIN PROJET p ON c.IdClient = p.IdClient
                  GROUP BY c.IdClient
                  ORDER BY projectCount DESC
                  LIMIT :limit";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind limit
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get project completion rate
     * 
     * @return float
     */
    public function getProjectCompletionRate() {
        // Query to get projects by status
        $query = "SELECT EtatProjet, COUNT(*) as count
                  FROM PROJET
                  GROUP BY EtatProjet";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        // Initialize counters
        $completed = 0;
        $total = 0;
        
        // Calculate rates
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['EtatProjet'] == PROJECT_STATUS_COMPLETED) {
                $completed = $row['count'];
            }
            if ($row['EtatProjet'] != PROJECT_STATUS_CANCELLED) {
                $total += $row['count'];
            }
        }
        
        // Calculate completion rate
        if ($total > 0) {
            return ($completed / $total) * 100;
        }
        
        return 0;
    }
    
    /**
     * Get most assigned personnel
     * 
     * @param int $limit Number of personnel to return
     * @return PDOStatement
     */
    public function getMostAssignedPersonnel($limit = 5) {
        // Query to get most assigned personnel
        $query = "SELECT p.MatriculePersonnel, p.NomPersonnel, p.PrenomPersonnel, 
                         COUNT(a.IdAffectation) as assignmentCount
                  FROM PERSONNEL p
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
    
    /**
     * Get payment balance by client
     * 
     * @param int $limit Number of clients to return
     * @return PDOStatement
     */
    public function getPaymentBalanceByClient($limit = 5) {
        // Query to get payment balance by client
        $query = "SELECT c.IdClient, c.NomClient, 
                         SUM(r.MontantReglement) as totalPaid,
                         (SELECT SUM(p.CoutProjet) 
                          FROM PROJET p 
                          WHERE p.IdClient = c.IdClient) as totalCost
                  FROM CLIENT c
                  LEFT JOIN REGLEMENT r ON c.IdClient = r.IdClient
                  GROUP BY c.IdClient
                  ORDER BY (totalCost - totalPaid) DESC
                  LIMIT :limit";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind limit
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get projects by status
     * 
     * @return array
     */
    public function getProjectsByStatus() {
        // Query to get projects by status
        $query = "SELECT EtatProjet, COUNT(*) as count
                  FROM PROJET
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
     * Get tasks by status
     * 
     * @return array
     */
    public function getTasksByStatus() {
        // Query to get tasks by status
        $query = "SELECT EtatTache, COUNT(*) as count
                  FROM TACHE
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
     * Get projects and payments by month
     * 
     * @param int $year Year to filter
     * @return array
     */
    public function getProjectsAndPaymentsByMonth($year) {
        // Initialize results array
        $results = [
            'projects' => array_fill(1, 12, 0),
            'payments' => array_fill(1, 12, 0)
        ];
        
        // Query to get projects by month
        $query = "SELECT MONTH(DateDebutProjet) as month, COUNT(*) as count
                  FROM PROJET
                  WHERE YEAR(DateDebutProjet) = :year
                  GROUP BY MONTH(DateDebutProjet)
                  ORDER BY month ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind year
        $stmt->bindParam(":year", $year);
        
        // Execute query
        $stmt->execute();
        
        // Populate projects results
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results['projects'][$row['month']] = $row['count'];
        }
        
        // Query to get payments by month
        $query = "SELECT MONTH(DateReglement) as month, SUM(MontantReglement) as total
                  FROM REGLEMENT
                  WHERE YEAR(DateReglement) = :year
                  GROUP BY MONTH(DateReglement)
                  ORDER BY month ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind year
        $stmt->bindParam(":year", $year);
        
        // Execute query
        $stmt->execute();
        
        // Populate payments results
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results['payments'][$row['month']] = $row['total'];
        }
        
        return $results;
    }
    
    /**
     * Get counts for dashboard
     * 
     * @return array
     */
    public function getCounts() {
        $counts = [];
        
        // Query to count clients
        $query = "SELECT COUNT(*) as count FROM CLIENT";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $counts['clients'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Query to count projects
        $query = "SELECT COUNT(*) as count FROM PROJET";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $counts['projects'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Query to count tasks
        $query = "SELECT COUNT(*) as count FROM TACHE";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $counts['tasks'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Query to count personnel
        $query = "SELECT COUNT(*) as count FROM PERSONNEL";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $counts['personnel'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Query to count payments
        $query = "SELECT COUNT(*) as count FROM REGLEMENT";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $counts['payments'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Query to sum payments
        $query = "SELECT SUM(MontantReglement) as total FROM REGLEMENT";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $counts['paymentsTotal'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Query to sum project costs
        $query = "SELECT SUM(CoutProjet) as total FROM PROJET";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $counts['projectsTotal'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        return $counts;
    }
    
    /**
     * Get recent projects
     * 
     * @param int $limit Number of projects to return
     * @return PDOStatement
     */
    public function getRecentProjects($limit = 5) {
        // Query to get recent projects
        $query = "SELECT p.IdProjet, p.TitreProjet, p.DateDebutProjet, p.EtatProjet, 
                         c.NomClient, tp.LibelleTypeProjet
                  FROM PROJET p
                  LEFT JOIN CLIENT c ON p.IdClient = c.IdClient
                  LEFT JOIN TYPEPROJET tp ON p.IdTypeProjet = tp.IdTypeProjet
                  ORDER BY p.DateDebutProjet DESC
                  LIMIT :limit";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind limit
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get recent payments
     * 
     * @param int $limit Number of payments to return
     * @return PDOStatement
     */
    public function getRecentPayments($limit = 5) {
        // Query to get recent payments
        $query = "SELECT r.IdReglement, r.DateReglement, r.MontantReglement, 
                         c.NomClient
                  FROM REGLEMENT r
                  LEFT JOIN CLIENT c ON r.IdClient = c.IdClient
                  ORDER BY r.DateReglement DESC, r.HeureReglement DESC
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
