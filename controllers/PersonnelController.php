<?php
/**
 * Personnel Controller
 * 
 * Handles personnel operations
 */
class PersonnelController {
    // Database connection and models
    private $db;
    private $personnel;
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
        
        // Initialize models
        $this->personnel = new Personnel($this->db);
        $this->service = new Service($this->db);
    }
    
    /**
     * Display list of personnel
     * 
     * @return void
     */
    public function index() {
        // Get page number
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = $page < 1 ? 1 : $page;
        $perPage = 10;
        
        // Get search keyword and service filter
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $serviceCode = isset($_GET['service']) ? $_GET['service'] : '';
        
        // Get personnel based on search/filter
        if (!empty($search)) {
            $stmt = $this->personnel->search($search);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } elseif (!empty($serviceCode)) {
            $stmt = $this->personnel->filterByService($serviceCode);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } else {
            $totalRecords = $this->personnel->count();
            $totalPages = ceil($totalRecords / $perPage);
            $page = $page > $totalPages ? $totalPages : $page;
            $page = $page < 1 ? 1 : $page;
            $stmt = $this->personnel->read($page, $perPage);
        }
        
        // Get services for dropdown
        $servicesStmt = $this->service->getServicesDropdown();
        
        // Display view
        require_once(APP_ROOT . '/views/personnel/index.php');
    }
    
    /**
     * Display personnel creation form
     * 
     * @return void
     */
    public function create() {
        // Get services for dropdown
        $servicesStmt = $this->service->getServicesDropdown();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('personnel/create');
            }
            
            // Get form data
            $matriculePersonnel = isset($_POST['MatriculePersonnel']) ? trim($_POST['MatriculePersonnel']) : '';
            $nomPersonnel = isset($_POST['NomPersonnel']) ? trim($_POST['NomPersonnel']) : '';
            $prenomPersonnel = isset($_POST['PrenomPersonnel']) ? trim($_POST['PrenomPersonnel']) : '';
            $emailPersonnel = isset($_POST['EmailPersonnel']) ? trim($_POST['EmailPersonnel']) : '';
            $telPersonnel = isset($_POST['TelPersonnel']) ? trim($_POST['TelPersonnel']) : '';
            $codeService = isset($_POST['CodeService']) ? trim($_POST['CodeService']) : '';
            
            // Validate form data
            $validator = new Validator([
                'MatriculePersonnel' => $matriculePersonnel,
                'NomPersonnel' => $nomPersonnel,
                'PrenomPersonnel' => $prenomPersonnel,
                'EmailPersonnel' => $emailPersonnel,
                'TelPersonnel' => $telPersonnel,
                'CodeService' => $codeService
            ]);
            
            $validator->required('MatriculePersonnel', 'Matricule du personnel est obligatoire')
                      ->maxLength('MatriculePersonnel', 20, 'Matricule du personnel ne doit pas dépasser 20 caractères')
                      ->required('NomPersonnel', 'Nom du personnel est obligatoire')
                      ->maxLength('NomPersonnel', 100, 'Nom du personnel ne doit pas dépasser 100 caractères')
                      ->required('PrenomPersonnel', 'Prénom du personnel est obligatoire')
                      ->maxLength('PrenomPersonnel', 100, 'Prénom du personnel ne doit pas dépasser 100 caractères')
                      ->required('EmailPersonnel', 'Email du personnel est obligatoire')
                      ->email('EmailPersonnel', 'Email du personnel est invalide')
                      ->maxLength('EmailPersonnel', 100, 'Email du personnel ne doit pas dépasser 100 caractères')
                      ->required('TelPersonnel', 'Téléphone du personnel est obligatoire')
                      ->phone('TelPersonnel', 'Téléphone du personnel est invalide')
                      ->maxLength('TelPersonnel', 20, 'Téléphone du personnel ne doit pas dépasser 20 caractères')
                      ->required('CodeService', 'Service est obligatoire');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'MatriculePersonnel' => $matriculePersonnel,
                    'NomPersonnel' => $nomPersonnel,
                    'PrenomPersonnel' => $prenomPersonnel,
                    'EmailPersonnel' => $emailPersonnel,
                    'TelPersonnel' => $telPersonnel,
                    'CodeService' => $codeService
                ];
                redirect('personnel/create');
            }
            
            // Check if matricule already exists
            $this->personnel->MatriculePersonnel = $matriculePersonnel;
            if ($this->personnel->readOne()) {
                setFlashMessage('danger', 'Ce matricule existe déjà');
                $_SESSION['form_data'] = [
                    'MatriculePersonnel' => $matriculePersonnel,
                    'NomPersonnel' => $nomPersonnel,
                    'PrenomPersonnel' => $prenomPersonnel,
                    'EmailPersonnel' => $emailPersonnel,
                    'TelPersonnel' => $telPersonnel,
                    'CodeService' => $codeService
                ];
                redirect('personnel/create');
            }
            
            // Set personnel properties
            $this->personnel->MatriculePersonnel = $matriculePersonnel;
            $this->personnel->NomPersonnel = $nomPersonnel;
            $this->personnel->PrenomPersonnel = $prenomPersonnel;
            $this->personnel->EmailPersonnel = $emailPersonnel;
            $this->personnel->TelPersonnel = $telPersonnel;
            $this->personnel->CodeService = $codeService;
            
            // Create personnel
            if ($this->personnel->create()) {
                // Personnel created successfully
                setFlashMessage('success', 'Personnel créé avec succès');
                redirect('personnel');
            } else {
                // Personnel creation failed
                setFlashMessage('danger', 'Erreur lors de la création du personnel');
                $_SESSION['form_data'] = [
                    'MatriculePersonnel' => $matriculePersonnel,
                    'NomPersonnel' => $nomPersonnel,
                    'PrenomPersonnel' => $prenomPersonnel,
                    'EmailPersonnel' => $emailPersonnel,
                    'TelPersonnel' => $telPersonnel,
                    'CodeService' => $codeService
                ];
                redirect('personnel/create');
            }
        }
        
        // Display personnel creation form
        require_once(APP_ROOT . '/views/personnel/create.php');
    }
    
    /**
     * Display personnel edit form
     * 
     * @param string $matricule Personnel Matricule
     * @return void
     */
    public function edit($matricule) {
        // Get personnel
        $this->personnel->MatriculePersonnel = $matricule;
        $exists = $this->personnel->readOne();
        
        if (!$exists) {
            setFlashMessage('danger', 'Personnel non trouvé');
            redirect('personnel');
        }
        
        // Get services for dropdown
        $servicesStmt = $this->service->getServicesDropdown();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('personnel/edit/' . $matricule);
            }
            
            // Get form data
            $nomPersonnel = isset($_POST['NomPersonnel']) ? trim($_POST['NomPersonnel']) : '';
            $prenomPersonnel = isset($_POST['PrenomPersonnel']) ? trim($_POST['PrenomPersonnel']) : '';
            $emailPersonnel = isset($_POST['EmailPersonnel']) ? trim($_POST['EmailPersonnel']) : '';
            $telPersonnel = isset($_POST['TelPersonnel']) ? trim($_POST['TelPersonnel']) : '';
            $codeService = isset($_POST['CodeService']) ? trim($_POST['CodeService']) : '';
            
            // Validate form data
            $validator = new Validator([
                'NomPersonnel' => $nomPersonnel,
                'PrenomPersonnel' => $prenomPersonnel,
                'EmailPersonnel' => $emailPersonnel,
                'TelPersonnel' => $telPersonnel,
                'CodeService' => $codeService
            ]);
            
            $validator->required('NomPersonnel', 'Nom du personnel est obligatoire')
                      ->maxLength('NomPersonnel', 100, 'Nom du personnel ne doit pas dépasser 100 caractères')
                      ->required('PrenomPersonnel', 'Prénom du personnel est obligatoire')
                      ->maxLength('PrenomPersonnel', 100, 'Prénom du personnel ne doit pas dépasser 100 caractères')
                      ->required('EmailPersonnel', 'Email du personnel est obligatoire')
                      ->email('EmailPersonnel', 'Email du personnel est invalide')
                      ->maxLength('EmailPersonnel', 100, 'Email du personnel ne doit pas dépasser 100 caractères')
                      ->required('TelPersonnel', 'Téléphone du personnel est obligatoire')
                      ->phone('TelPersonnel', 'Téléphone du personnel est invalide')
                      ->maxLength('TelPersonnel', 20, 'Téléphone du personnel ne doit pas dépasser 20 caractères')
                      ->required('CodeService', 'Service est obligatoire');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'NomPersonnel' => $nomPersonnel,
                    'PrenomPersonnel' => $prenomPersonnel,
                    'EmailPersonnel' => $emailPersonnel,
                    'TelPersonnel' => $telPersonnel,
                    'CodeService' => $codeService
                ];
                redirect('personnel/edit/' . $matricule);
            }
            
            // Set personnel properties
            $this->personnel->NomPersonnel = $nomPersonnel;
            $this->personnel->PrenomPersonnel = $prenomPersonnel;
            $this->personnel->EmailPersonnel = $emailPersonnel;
            $this->personnel->TelPersonnel = $telPersonnel;
            $this->personnel->CodeService = $codeService;
            
            // Update personnel
            if ($this->personnel->update()) {
                // Personnel updated successfully
                setFlashMessage('success', 'Personnel mis à jour avec succès');
                redirect('personnel');
            } else {
                // Personnel update failed
                setFlashMessage('danger', 'Erreur lors de la mise à jour du personnel');
                redirect('personnel/edit/' . $matricule);
            }
        }
        
        // Display personnel edit form
        require_once(APP_ROOT . '/views/personnel/edit.php');
    }
    
    /**
     * Display personnel details
     * 
     * @param string $matricule Personnel Matricule
     * @return void
     */
    public function view($matricule) {
        // Get personnel
        $this->personnel->MatriculePersonnel = $matricule;
        $exists = $this->personnel->readOne();
        
        if (!$exists) {
            setFlashMessage('danger', 'Personnel non trouvé');
            redirect('personnel');
        }
        
        // Get personnel assignments
        $assignmentsStmt = $this->personnel->getAssignments();
        
        // Display personnel details
        require_once(APP_ROOT . '/views/personnel/view.php');
    }
    
    /**
     * Delete personnel
     * 
     * @param string $matricule Personnel Matricule
     * @return void
     */
    public function delete($matricule) {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('danger', 'Invalid CSRF token');
            redirect('personnel');
        }
        
        // Set personnel matricule
        $this->personnel->MatriculePersonnel = $matricule;
        
        // Check if personnel exists
        if (!$this->personnel->readOne()) {
            setFlashMessage('danger', 'Personnel non trouvé');
            redirect('personnel');
        }
        
        // Delete personnel
        if ($this->personnel->delete()) {
            setFlashMessage('success', 'Personnel supprimé avec succès');
        } else {
            setFlashMessage('danger', 'Impossible de supprimer ce personnel car il est associé à des affectations');
        }
        
        redirect('personnel');
    }
    
    /**
     * Export personnel to CSV
     * 
     * @return void
     */
    public function exportCSV() {
        // Get all personnel
        $stmt = $this->personnel->read(1, 1000); // Limit to 1000 records
        
        // Prepare data for export
        $personnel = [];
        $headers = ['Matricule', 'Nom', 'Prénom', 'Email', 'Téléphone', 'Service'];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $personnel[] = [
                $row['MatriculePersonnel'],
                $row['NomPersonnel'],
                $row['PrenomPersonnel'],
                $row['EmailPersonnel'],
                $row['TelPersonnel'],
                $row['LibelleService']
            ];
        }
        
        // Export to CSV
        Export::toCSV($personnel, $headers, 'personnel');
    }
    
    /**
     * Export personnel to PDF
     * 
     * @return void
     */
    public function exportPDF() {
        // Get all personnel
        $stmt = $this->personnel->read(1, 1000); // Limit to 1000 records
        
        // Prepare data for export
        $personnel = [];
        $headers = ['Matricule', 'Nom', 'Prénom', 'Email', 'Téléphone', 'Service'];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $personnel[] = [
                $row['MatriculePersonnel'],
                $row['NomPersonnel'],
                $row['PrenomPersonnel'],
                $row['EmailPersonnel'],
                $row['TelPersonnel'],
                $row['LibelleService']
            ];
        }
        
        // Export to PDF
        Export::toPDF($personnel, $headers, 'Liste du Personnel', 'personnel');
    }
}
?>
