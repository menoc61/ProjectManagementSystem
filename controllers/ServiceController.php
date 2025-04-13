<?php
/**
 * Service Controller
 * 
 * Handles service operations
 */
class ServiceController {
    // Database connection and model
    private $db;
    private $service;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Check authentication
        if (!isLoggedIn()) {
            redirect('auth/login');
        }
        
        // Get database connection
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Initialize service model
        $this->service = new Service($this->db);
    }
    
    /**
     * Display list of services
     * 
     * @return void
     */
    public function index() {
        // Get search keyword
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Get services
        if (!empty($search)) {
            $stmt = $this->service->search($search);
        } else {
            $stmt = $this->service->read();
        }
        
        // Get personnel counts by service
        $personnelCountsStmt = $this->service->countPersonnelByService();
        $personnelCounts = [];
        
        while ($row = $personnelCountsStmt->fetch(PDO::FETCH_ASSOC)) {
            $personnelCounts[$row['CodeService']] = $row['personnelCount'];
        }
        
        // Display view
        require_once(APP_ROOT . '/views/services/index.php');
    }
    
    /**
     * Display service creation form
     * 
     * @return void
     */
    public function create() {
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('services/create');
            }
            
            // Get form data
            $codeService = isset($_POST['CodeService']) ? trim($_POST['CodeService']) : '';
            $libelleService = isset($_POST['LibelleService']) ? trim($_POST['LibelleService']) : '';
            
            // Validate form data
            $validator = new Validator([
                'CodeService' => $codeService,
                'LibelleService' => $libelleService
            ]);
            
            $validator->required('CodeService', 'Code du service est obligatoire')
                      ->maxLength('CodeService', 20, 'Code du service ne doit pas dépasser 20 caractères')
                      ->required('LibelleService', 'Libellé du service est obligatoire')
                      ->maxLength('LibelleService', 100, 'Libellé du service ne doit pas dépasser 100 caractères');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'CodeService' => $codeService,
                    'LibelleService' => $libelleService
                ];
                redirect('services/create');
            }
            
            // Check if service code already exists
            $this->service->CodeService = $codeService;
            if ($this->service->readOne()) {
                setFlashMessage('danger', 'Ce code de service existe déjà');
                $_SESSION['form_data'] = [
                    'CodeService' => $codeService,
                    'LibelleService' => $libelleService
                ];
                redirect('services/create');
            }
            
            // Set service properties
            $this->service->CodeService = $codeService;
            $this->service->LibelleService = $libelleService;
            
            // Create service
            if ($this->service->create()) {
                // Service created successfully
                setFlashMessage('success', 'Service créé avec succès');
                redirect('services');
            } else {
                // Service creation failed
                setFlashMessage('danger', 'Erreur lors de la création du service');
                $_SESSION['form_data'] = [
                    'CodeService' => $codeService,
                    'LibelleService' => $libelleService
                ];
                redirect('services/create');
            }
        }
        
        // Display service creation form
        require_once(APP_ROOT . '/views/services/create.php');
    }
    
    /**
     * Display service edit form
     * 
     * @param string $code Service Code
     * @return void
     */
    public function edit($code) {
        // Get service
        $this->service->CodeService = $code;
        $exists = $this->service->readOne();
        
        if (!$exists) {
            setFlashMessage('danger', 'Service non trouvé');
            redirect('services');
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('services/edit/' . $code);
            }
            
            // Get form data
            $libelleService = isset($_POST['LibelleService']) ? trim($_POST['LibelleService']) : '';
            
            // Validate form data
            $validator = new Validator([
                'LibelleService' => $libelleService
            ]);
            
            $validator->required('LibelleService', 'Libellé du service est obligatoire')
                      ->maxLength('LibelleService', 100, 'Libellé du service ne doit pas dépasser 100 caractères');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'LibelleService' => $libelleService
                ];
                redirect('services/edit/' . $code);
            }
            
            // Set service properties
            $this->service->LibelleService = $libelleService;
            
            // Update service
            if ($this->service->update()) {
                // Service updated successfully
                setFlashMessage('success', 'Service mis à jour avec succès');
                redirect('services');
            } else {
                // Service update failed
                setFlashMessage('danger', 'Erreur lors de la mise à jour du service');
                redirect('services/edit/' . $code);
            }
        }
        
        // Display service edit form
        require_once(APP_ROOT . '/views/services/edit.php');
    }
    
    /**
     * Delete service
     * 
     * @param string $code Service Code
     * @return void
     */
    public function delete($code) {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('danger', 'Invalid CSRF token');
            redirect('services');
        }
        
        // Set service code
        $this->service->CodeService = $code;
        
        // Check if service exists
        if (!$this->service->readOne()) {
            setFlashMessage('danger', 'Service non trouvé');
            redirect('services');
        }
        
        // Delete service
        if ($this->service->delete()) {
            setFlashMessage('success', 'Service supprimé avec succès');
        } else {
            setFlashMessage('danger', 'Impossible de supprimer ce service car il est associé à du personnel');
        }
        
        redirect('services');
    }
    
    /**
     * Export services to CSV
     * 
     * @return void
     */
    public function exportCSV() {
        // Get all services
        $stmt = $this->service->read();
        
        // Prepare data for export
        $services = [];
        $headers = ['Code', 'Libellé'];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $services[] = [
                $row['CodeService'],
                $row['LibelleService']
            ];
        }
        
        // Export to CSV
        Export::toCSV($services, $headers, 'services');
    }
    
    /**
     * Export services to PDF
     * 
     * @return void
     */
    public function exportPDF() {
        // Get all services
        $stmt = $this->service->read();
        
        // Prepare data for export
        $services = [];
        $headers = ['Code', 'Libellé'];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $services[] = [
                $row['CodeService'],
                $row['LibelleService']
            ];
        }
        
        // Export to PDF
        Export::toPDF($services, $headers, 'Liste des Services', 'services');
    }
}
?>
