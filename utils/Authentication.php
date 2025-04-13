<?php
/**
 * Authentication Class
 * 
 * Handles user authentication and security
 */
class Authentication {
    private $db;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Login a user
     * 
     * @param string $email User email
     * @param string $password User password
     * @return bool|array User data on success, false on failure
     */
    public function login($email, $password) {
        // Prepare query
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':email', $email);
        
        // Execute query
        $stmt->execute();
        
        // Check if user exists
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Create session
                $_SESSION[SESSION_AUTH] = true;
                $_SESSION[SESSION_USER] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
                
                // Update last login time
                $this->updateLastLogin($user['id']);
                
                return $user;
            }
        }
        
        return false;
    }
    
    /**
     * Logout the current user
     * 
     * @return void
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // If it's desired to kill the session, also delete the session cookie.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Finally, destroy the session.
        session_destroy();
    }
    
    /**
     * Register a new user
     * 
     * @param string $name User name
     * @param string $email User email
     * @param string $password User password
     * @param string $role User role
     * @return bool|string True on success, error message on failure
     */
    public function register($name, $email, $password, $role = ROLE_USER) {
        // Check if email already exists
        if ($this->emailExists($email)) {
            return "Cet email est déjà utilisé.";
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare query
        $query = "INSERT INTO users (name, email, password, role, created_at) 
                  VALUES (:name, :email, :password, :role, NOW())";
        $stmt = $this->db->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        
        // Execute query
        if ($stmt->execute()) {
            return true;
        }
        
        return "Une erreur est survenue lors de l'inscription.";
    }
    
    /**
     * Check if email exists
     * 
     * @param string $email Email to check
     * @return bool True if email exists, false otherwise
     */
    private function emailExists($email) {
        $query = "SELECT id FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Update last login time for a user
     * 
     * @param int $userId User ID
     * @return void
     */
    private function updateLastLogin($userId) {
        $query = "UPDATE users SET last_login = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
    }
    
    /**
     * Check if current user is authenticated
     * 
     * @return bool True if authenticated, false otherwise
     */
    public function isAuthenticated() {
        return isset($_SESSION[SESSION_AUTH]) && $_SESSION[SESSION_AUTH] === true;
    }
    
    /**
     * Check if current user has specific role
     * 
     * @param string $role Role to check
     * @return bool True if user has role, false otherwise
     */
    public function hasRole($role) {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        return isset($_SESSION[SESSION_USER]['role']) && $_SESSION[SESSION_USER]['role'] === $role;
    }
    
    /**
     * Get current user data
     * 
     * @return array|null User data or null if not authenticated
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return $_SESSION[SESSION_USER];
    }
    
    /**
     * Update user password
     * 
     * @param int $userId User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return bool|string True on success, error message on failure
     */
    public function updatePassword($userId, $currentPassword, $newPassword) {
        // Get user
        $query = "SELECT * FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify current password
            if (password_verify($currentPassword, $user['password'])) {
                // Hash new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Update password
                $query = "UPDATE users SET password = :password WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':id', $userId);
                
                if ($stmt->execute()) {
                    return true;
                }
                
                return "Une erreur est survenue lors de la mise à jour du mot de passe.";
            }
            
            return "Le mot de passe actuel est incorrect.";
        }
        
        return "Utilisateur non trouvé.";
    }
    
    /**
     * Create initial admin user if no users exist
     * 
     * @return bool True on success, false on failure
     */
    public function createInitialAdmin() {
        // Check if any users exist
        $query = "SELECT COUNT(*) as count FROM users";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] == 0) {
            // Create admin user
            return $this->register('Administrateur', 'admin@example.com', 'admin123', ROLE_ADMIN);
        }
        
        return true;
    }
}
?>
