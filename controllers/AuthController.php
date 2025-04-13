<?php
/**
 * Auth Controller
 * 
 * Handles authentication operations
 */
class AuthController {
    // Database connection and Utils
    private $db;
    private $auth;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Get database connection
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Initialize authentication
        $this->auth = new Authentication($this->db);
        
        // Create users table if not exists
        $user = new User($this->db);
        $user->createUsersTable();
        
        // Create initial admin user if no users exist
        $this->auth->createInitialAdmin();
    }
    
    /**
     * Handle login form submission
     * 
     * @return void
     */
    public function login() {
        // Check if user is already logged in
        if ($this->auth->isAuthenticated()) {
            redirect('dashboard');
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('auth/login');
            }
            
            // Get form data
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            // Validate form data
            $validator = new Validator([
                'email' => $email,
                'password' => $password
            ]);
            
            $validator->required('email', 'Email est obligatoire')
                      ->email('email', 'Email invalide')
                      ->required('password', 'Mot de passe est obligatoire');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'email' => $email
                ];
                redirect('auth/login');
            }
            
            // Attempt to login
            $result = $this->auth->login($email, $password);
            
            if ($result) {
                // Login successful
                setFlashMessage('success', 'Connexion réussie');
                redirect('dashboard');
            } else {
                // Login failed
                setFlashMessage('danger', 'Email ou mot de passe incorrect');
                $_SESSION['form_data'] = [
                    'email' => $email
                ];
                redirect('auth/login');
            }
        }
        
        // Display login form
        require_once(APP_ROOT . '/views/auth/login.php');
    }
    
    /**
     * Handle user logout
     * 
     * @return void
     */
    public function logout() {
        // Logout user
        $this->auth->logout();
        
        // Redirect to login page
        setFlashMessage('success', 'Déconnexion réussie');
        redirect('auth/login');
    }
    
    /**
     * Display change password form
     * 
     * @return void
     */
    public function changePassword() {
        // Check if user is logged in
        if (!$this->auth->isAuthenticated()) {
            redirect('auth/login');
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('auth/change-password');
            }
            
            // Get form data
            $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
            $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
            $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
            
            // Validate form data
            $validator = new Validator([
                'current_password' => $currentPassword,
                'new_password' => $newPassword,
                'confirm_password' => $confirmPassword
            ]);
            
            $validator->required('current_password', 'Mot de passe actuel est obligatoire')
                      ->required('new_password', 'Nouveau mot de passe est obligatoire')
                      ->minLength('new_password', 6, 'Nouveau mot de passe doit contenir au moins 6 caractères')
                      ->required('confirm_password', 'Confirmation du mot de passe est obligatoire')
                      ->matches('confirm_password', 'new_password', 'Les mots de passe ne correspondent pas');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                redirect('auth/change-password');
            }
            
            // Get current user
            $user = getCurrentUser();
            
            // Update password
            $result = $this->auth->updatePassword($user['id'], $currentPassword, $newPassword);
            
            if ($result === true) {
                // Password changed successfully
                setFlashMessage('success', 'Mot de passe modifié avec succès');
                redirect('dashboard');
            } else {
                // Password change failed
                setFlashMessage('danger', $result);
                redirect('auth/change-password');
            }
        }
        
        // Display change password form
        require_once(APP_ROOT . '/views/auth/change_password.php');
    }
}
?>
