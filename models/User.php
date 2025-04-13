<?php
/**
 * User Model
 * 
 * Handles user data operations
 */
class User {
    // Database connection and table name
    private $conn;
    private $table_name = "users";
    
    // User properties
    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $created_at;
    public $last_login;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Create user
     * 
     * @return boolean
     */
    public function create() {
        // Hash password
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                  SET name = :name,
                      email = :email, 
                      password = :password, 
                      role = :role,
                      created_at = NOW()";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        
        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":role", $this->role);
        
        // Execute query
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if email exists
     * 
     * @return boolean
     */
    public function emailExists() {
        // Query to check email
        $query = "SELECT id, name, email, password, role
                  FROM " . $this->table_name . "
                  WHERE email = :email
                  LIMIT 1";
        
        // Prepare the query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Bind given email value
        $stmt->bindParam(":email", $this->email);
        
        // Execute the query
        $stmt->execute();
        
        // Get number of rows
        $num = $stmt->rowCount();
        
        // If email exists, assign values to object properties for easy access and use for php sessions
        if ($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->role = $row['role'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Update user
     * 
     * @return boolean
     */
    public function update() {
        // Query to update record (password will be updated separately)
        $query = "UPDATE " . $this->table_name . "
                  SET name = :name,
                      email = :email,
                      role = :role
                  WHERE id = :id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Update user password
     * 
     * @return boolean
     */
    public function updatePassword() {
        // Hash password
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Query to update password
        $query = "UPDATE " . $this->table_name . "
                  SET password = :password
                  WHERE id = :id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind values
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Update last login timestamp
     * 
     * @return boolean
     */
    public function updateLastLogin() {
        // Query to update last login
        $query = "UPDATE " . $this->table_name . "
                  SET last_login = NOW()
                  WHERE id = :id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind values
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Read all users
     * 
     * @return PDOStatement
     */
    public function read() {
        // Query to read all users
        $query = "SELECT id, name, email, role, created_at, last_login
                  FROM " . $this->table_name . "
                  ORDER BY name ASC";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Read one user
     * 
     * @return boolean
     */
    public function readOne() {
        // Query to read one user
        $query = "SELECT id, name, email, role, created_at, last_login
                  FROM " . $this->table_name . "
                  WHERE id = :id
                  LIMIT 1";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind values
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        $stmt->execute();
        
        // Get record details
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if record exists
        if (!$row) {
            return false;
        }
        
        // Set values to object properties
        $this->id = $row['id'];
        $this->name = $row['name'];
        $this->email = $row['email'];
        $this->role = $row['role'];
        $this->created_at = $row['created_at'];
        $this->last_login = $row['last_login'];
        
        return true;
    }
    
    /**
     * Delete user
     * 
     * @return boolean
     */
    public function delete() {
        // Query to delete record
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Sanitize data
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind value
        $stmt->bindParam(":id", $this->id);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Search users
     * 
     * @param string $keywords Keywords to search
     * @return PDOStatement
     */
    public function search($keywords) {
        // Query to search users
        $query = "SELECT id, name, email, role, created_at, last_login
                  FROM " . $this->table_name . "
                  WHERE name LIKE :keywords OR email LIKE :keywords
                  ORDER BY name ASC";
        
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
     * Count users
     * 
     * @return int
     */
    public function count() {
        // Query to count users
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
     * Create users table if not exists
     * 
     * @return boolean
     */
    public function createUsersTable() {
        // Query to create users table
        $query = "CREATE TABLE IF NOT EXISTS " . $this->table_name . " (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    role VARCHAR(20) NOT NULL DEFAULT 'user',
                    created_at DATETIME NOT NULL,
                    last_login DATETIME NULL
                  )";
        
        // Prepare query
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>
