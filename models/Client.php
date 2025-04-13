<?php
/**
 * Client Model
 * 
 * Handles client data operations
 */
class Client {
    // Database connection and table name
    private $conn;
    private $table_name = "CLIENT";
    
    // Client properties
    public $IdClient;
    public $NomClient;
    public $AdresseClient;
    public $EmailClient;
    public $TelClient;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create client
     * 
     * @return boolean
     */
    public function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                  SET NomClient = :NomClient,
                      AdresseClient = :AdresseClient,
                      EmailClient = :EmailClient,
                      TelClient = :TelClient";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->NomClient = htmlspecialchars(strip_tags($this->NomClient));
        $this->AdresseClient = htmlspecialchars(strip_tags($this->AdresseClient));
        $this->EmailClient = htmlspecialchars(strip_tags($this->EmailClient));
        $this->TelClient = htmlspecialchars(strip_tags($this->TelClient));
        
        // Bind values
        $stmt->bindParam(":NomClient", $this->NomClient);
        $stmt->bindParam(":AdresseClient", $this->AdresseClient);
        $stmt->bindParam(":EmailClient", $this->EmailClient);
        $stmt->bindParam(":TelClient", $this->TelClient);
        
        // Execute query
        if ($stmt->execute()) {
            $this->IdClient = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Read all clients
     * 
     * @param int $page Page number
     * @param int $perPage Records per page
     * @return PDOStatement
     */
    public function read($page = 1, $perPage = 10) {
        // Calculate the offset
        $offset = ($page - 1) * $perPage;
        
        // Query to read all clients with pagination
        $query = "SELECT IdClient, NomClient, AdresseClient, EmailClient, TelClient
                  FROM " . $this->table_name . "
                  ORDER BY NomClient ASC
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
     * Read one client
     * 
     * @return boolean
     */
    public function readOne() {
        // Query to read one client
        $query = "SELECT IdClient, NomClient, AdresseClient, EmailClient, TelClient
                  FROM " . $this->table_name . "
                  WHERE IdClient = :IdClient
                  LIMIT 1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdClient = htmlspecialchars(strip_tags($this->IdClient));
        
        // Bind values
        $stmt->bindParam(":IdClient", $this->IdClient);
        
        // Execute query
        $stmt->execute();
        
        // Get record details
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if record exists
        if (!$row) {
            return false;
        }
        
        // Set values to object properties
        $this->IdClient = $row['IdClient'];
        $this->NomClient = $row['NomClient'];
        $this->AdresseClient = $row['AdresseClient'];
        $this->EmailClient = $row['EmailClient'];
        $this->TelClient = $row['TelClient'];
        
        return true;
    }
    
    /**
     * Update client
     * 
     * @return boolean
     */
    public function update() {
        // Query to update record
        $query = "UPDATE " . $this->table_name . "
                  SET NomClient = :NomClient,
                      AdresseClient = :AdresseClient,
                      EmailClient = :EmailClient,
                      TelClient = :TelClient
                  WHERE IdClient = :IdClient";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->NomClient = htmlspecialchars(strip_tags($this->NomClient));
        $this->AdresseClient = htmlspecialchars(strip_tags($this->AdresseClient));
        $this->EmailClient = htmlspecialchars(strip_tags($this->EmailClient));
        $this->TelClient = htmlspecialchars(strip_tags($this->TelClient));
        $this->IdClient = htmlspecialchars(strip_tags($this->IdClient));
        
        // Bind values
        $stmt->bindParam(":NomClient", $this->NomClient);
        $stmt->bindParam(":AdresseClient", $this->AdresseClient);
        $stmt->bindParam(":EmailClient", $this->EmailClient);
        $stmt->bindParam(":TelClient", $this->TelClient);
        $stmt->bindParam(":IdClient", $this->IdClient);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete client
     * 
     * @return boolean
     */
    public function delete() {
        // Query to delete record
        $query = "DELETE FROM " . $this->table_name . " WHERE IdClient = :IdClient";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdClient = htmlspecialchars(strip_tags($this->IdClient));
        
        // Bind value
        $stmt->bindParam(":IdClient", $this->IdClient);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Search clients
     * 
     * @param string $keywords Keywords to search
     * @return PDOStatement
     */
    public function search($keywords) {
        // Query to search clients
        $query = "SELECT IdClient, NomClient, AdresseClient, EmailClient, TelClient
                  FROM " . $this->table_name . "
                  WHERE NomClient LIKE :keywords 
                  OR AdresseClient LIKE :keywords 
                  OR EmailClient LIKE :keywords
                  OR TelClient LIKE :keywords
                  ORDER BY NomClient ASC";
        
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
     * Get client projects
     * 
     * @return PDOStatement
     */
    public function getProjects() {
        // Query to get client projects
        $query = "SELECT p.IdProjet, p.TitreProjet, p.DescriptionProjet, p.CoutProjet, 
                         p.DateDebutProjet, p.DateFinProjet, p.EtatProjet, 
                         tp.LibelleTypeProjet
                  FROM PROJET p
                  LEFT JOIN TYPEPROJET tp ON p.IdTypeProjet = tp.IdTypeProjet
                  WHERE p.IdClient = :IdClient
                  ORDER BY p.DateDebutProjet DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdClient = htmlspecialchars(strip_tags($this->IdClient));
        
        // Bind values
        $stmt->bindParam(":IdClient", $this->IdClient);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get client payments
     * 
     * @return PDOStatement
     */
    public function getPayments() {
        // Query to get client payments
        $query = "SELECT IdReglement, DateReglement, HeureReglement, MontantReglement
                  FROM REGLEMENT
                  WHERE IdClient = :IdClient
                  ORDER BY DateReglement DESC, HeureReglement DESC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdClient = htmlspecialchars(strip_tags($this->IdClient));
        
        // Bind values
        $stmt->bindParam(":IdClient", $this->IdClient);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get total amount paid by client
     * 
     * @return float
     */
    public function getTotalPayments() {
        // Query to get total payments
        $query = "SELECT SUM(MontantReglement) as total
                  FROM REGLEMENT
                  WHERE IdClient = :IdClient";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdClient = htmlspecialchars(strip_tags($this->IdClient));
        
        // Bind values
        $stmt->bindParam(":IdClient", $this->IdClient);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ? $row['total'] : 0;
    }
    
    /**
     * Get total cost of client projects
     * 
     * @return float
     */
    public function getTotalProjectsCost() {
        // Query to get total project costs
        $query = "SELECT SUM(CoutProjet) as total
                  FROM PROJET
                  WHERE IdClient = :IdClient";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->IdClient = htmlspecialchars(strip_tags($this->IdClient));
        
        // Bind values
        $stmt->bindParam(":IdClient", $this->IdClient);
        
        // Execute query
        $stmt->execute();
        
        // Get result
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ? $row['total'] : 0;
    }
    
    /**
     * Count clients
     * 
     * @return int
     */
    public function count() {
        // Query to count clients
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
     * Get all clients for dropdown
     * 
     * @return PDOStatement
     */
    public function getClientsDropdown() {
        // Query to read all clients
        $query = "SELECT IdClient, NomClient
                  FROM " . $this->table_name . "
                  ORDER BY NomClient ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Get clients with most projects
     * 
     * @param int $limit Number of clients to return
     * @return PDOStatement
     */
    public function getTopClients($limit = 5) {
        // Query to get top clients
        $query = "SELECT c.IdClient, c.NomClient, COUNT(p.IdProjet) as projectCount,
                         SUM(p.CoutProjet) as totalCost
                  FROM " . $this->table_name . " c
                  LEFT JOIN PROJET p ON c.IdClient = p.IdClient
                  GROUP BY c.IdClient
                  ORDER BY projectCount DESC, totalCost DESC
                  LIMIT :limit";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Bind values
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
}
?>
