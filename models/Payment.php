<?php
/**
 * Payment Model
 * 
 * Handles payment data operations
 */
class Payment {
    // Database connection and table name
    private $conn;
    private $table_name = "REGLEMENT";
    
    // Payment properties
    public $IdReglement;
    public $DateReglement;
    public $HeureReglement;
    public $MontantReglement;
    public $IdClient;
    
    // Related properties
    public $NomClient;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create payment
     * 
     * @return boolean
     */
    public function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                  SET DateReglement = :DateReglement,
                      HeureReglement = :HeureReglement,
                      MontantReglement = :MontantReglement,
                      IdClient = :IdClient";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->DateReglement = htmlspecialchars(strip_tags($this->DateReglement));
        $this->HeureReglement = htmlspecialchars(strip_tags($this->HeureReglement));
        $this->MontantReglement = htmlspecialchars(strip_tags($this->MontantReglement));
        $this->IdClient = htmlspecialchars(strip_tags($this->IdClient));
        
        // Bind values
        $stmt->bindParam(":DateReglement", $this->DateReglement);
        $stmt->bindParam(":HeureReglement", $this->HeureReglement);
        $stmt->bindParam(":MontantReglement", $this->MontantReglement);
        $stmt->bindParam(":IdClient", $this->IdClient);
        
        // Execute query
        if ($stmt->execute()) {
            $this->IdReglement = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Read all payments
     * 
     * @param int $page Page number
     * @param int $perPage Records per page
     * @return PDOStatement
     */
    public function read($page = 1, $perPage = 10) {
        // Calculate the offset
        $offset = ($page - 1) * $perPage;
        
        // Query to read all payments with pagination
        $query = "SELECT r.IdReglement, r.DateReglement, r.HeureReglement, 
                         r.MontantReglement, r.IdClient, c.NomClient
                  FROM " . $this->table_name . " r
                  LEFT JOIN CLIENT c ON r.IdClient = c.IdClient
                  ORDER BY r.DateReglement DESC, r.HeureReglement DESC
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
     * Read one payment
     * 
     * @return boolean
     */
    public function readOne() {
        // Query to read one payment
        $query = "SELECT r.IdReglement, r.DateReglement, r.HeureReglement, 
                         r.MontantReglement, r.IdClient, c.NomClient
                  FROM " . $this->table_name . " r
                  LEFT JOIN CLIENT c ON r.IdClient = c.IdClient
                  WHERE r.IdReglement = :IdReglement
                  LIMIT 1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdReglement = htmlspecialchars(strip_tags($this->IdReglement));
        
        // Bind values
        $stmt->bindParam(":IdReglement", $this->IdReglement);
        
        // Execute query
        $stmt->execute();
        
        // Get record details
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if record exists
        if (!$row) {
            return false;
        }
        
        // Set values to object properties
        $this->IdReglement = $row['IdReglement'];
        $this->DateReglement = $row['DateReglement'];
        $this->HeureReglement = $row['HeureReglement'];
        $this->MontantReglement = $row['MontantReglement'];
        $this->IdClient = $row['IdClient'];
        $this->NomClient = $row['NomClient'];
        
        return true;
    }
    
    /**
     * Update payment
     * 
     * @return boolean
     */
    public function update() {
        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                  SET DateReglement = :DateReglement,
                      HeureReglement = :HeureReglement,
                      MontantReglement = :MontantReglement,
                      IdClient = :IdClient
                  WHERE IdReglement = :IdReglement";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->DateReglement = htmlspecialchars(strip_tags($this->DateReglement));
        $this->HeureReglement = htmlspecialchars(strip_tags($this->HeureReglement));
        $this->MontantReglement = htmlspecialchars(strip_tags($this->MontantReglement));
        $this->IdClient = htmlspecialchars(strip_tags($this->IdClient));
        $this->IdReglement = htmlspecialchars(strip_tags($this->IdReglement));
        
        // Bind values
        $stmt->bindParam(":DateReglement", $this->DateReglement);
        $stmt->bindParam(":HeureReglement", $this->HeureReglement);
        $stmt->bindParam(":MontantReglement", $this->MontantReglement);
        $stmt->bindParam(":IdClient", $this->IdClient);
        $stmt->bindParam(":IdReglement", $this->IdReglement);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete payment
     * 
     * @return boolean
     */
    public function delete() {
        // Query to delete record
        $query = "DELETE FROM " . $this->table_name . " WHERE IdReglement = :IdReglement";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdReglement = htmlspecialchars(strip_tags($this->IdReglement));
        
        // Bind value
        $stmt->bindParam(":IdReglement", $this->IdReglement);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Search payments
     * 
     * @param string $keywords Keywords to search
     * @return PDOStatement
     */
    public function search($keywords) {
        // Query to search payments
        $query = "SELECT r.IdReglement, r.DateReglement, r.HeureReglement, 
                         r.MontantReglement, r.IdClient, c.NomClient
                  FROM " . $this->table_name . " r
                  LEFT JOIN CLIENT c ON r.IdClient = c.IdClient
                  WHERE c.NomClient LIKE :keywords
                  OR r.MontantReglement LIKE :keywords
                  ORDER BY r.DateReglement DESC, r.HeureReglement DESC";
        
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
     * Filter payments by client
     * 
     * @param int $clientId Client ID to filter
     * @return PDOStatement
     */
    public function filterByClient($clientId) {
        // Query to filter payments
        $query = "SELECT r.IdReglement, r.DateReglement, r.HeureReglement, 
                         r.MontantReglement, r.IdClient, c.NomClient
                  FROM " . $this->table_name . " r
                  LEFT JOIN CLIENT c ON r.IdClient = c.IdClient
                  WHERE r.IdClient = :clientId
                  ORDER BY r.DateReglement DESC, r.HeureReglement DESC";
        
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
     * Filter payments by date range
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return PDOStatement
     */
    public function filterByDateRange($startDate, $endDate) {
        // Query to filter payments
        $query = "SELECT r.IdReglement, r.DateReglement, r.HeureReglement, 
                         r.MontantReglement, r.IdClient, c.NomClient
                  FROM " . $this->table_name . " r
                  LEFT JOIN CLIENT c ON r.IdClient = c.IdClient
                  WHERE r.DateReglement BETWEEN :startDate AND :endDate
                  ORDER BY r.DateReglement DESC, r.HeureReglement DESC";
        
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
     * Get total payments
     * 
     * @return float
     */
    public function getTotalPayments() {
        // Query to get total payments
        $query = "SELECT SUM(MontantReglement) as total FROM " . $this->table_name;
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ? $row['total'] : 0;
    }
    
    /**
     * Get total payments by client
     * 
     * @return PDOStatement
     */
    public function getTotalPaymentsByClient() {
        // Query to get total payments by client
        $query = "SELECT c.IdClient, c.NomClient, SUM(r.MontantReglement) as total
                  FROM " . $this->table_name . " r
                  JOIN CLIENT c ON r.IdClient = c.IdClient
                  GROUP BY c.IdClient
                  ORDER BY total DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get total payments by month
     * 
     * @param int $year Year to filter
     * @return array
     */
    public function getTotalPaymentsByMonth($year) {
        // Query to get total payments by month
        $query = "SELECT MONTH(DateReglement) as month, SUM(MontantReglement) as total
                  FROM " . $this->table_name . "
                  WHERE YEAR(DateReglement) = :year
                  GROUP BY MONTH(DateReglement)
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
     * Count payments
     * 
     * @return int
     */
    public function count() {
        // Query to count payments
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
     * Get payment balance by client
     * 
     * @return PDOStatement
     */
    public function getPaymentBalanceByClient() {
        // Query to get payment balance by client
        $query = "SELECT c.IdClient, c.NomClient, 
                         SUM(r.MontantReglement) as totalPaid,
                         (SELECT SUM(p.CoutProjet) 
                          FROM PROJET p 
                          WHERE p.IdClient = c.IdClient) as totalCost
                  FROM CLIENT c
                  LEFT JOIN " . $this->table_name . " r ON c.IdClient = r.IdClient
                  GROUP BY c.IdClient
                  ORDER BY c.NomClient ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
}
?>
