<?php
/**
 * Project Type Controller
 * 
 * Handles project type operations
 */
class ProjectTypeController {
    // Database connection and model
    private $db;
    private $projectType;
    
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
        
        // Initialize project type model
        $this->projectType = new ProjectType($this->db);
    }
    
    /**
     * Display list of project types
     * 
     * @return void
     */
    public function index() {
        // Get search keyword
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Get project types
        if (!empty($search)) {
            $stmt = $this->projectType->search($search);
        } else {
            $stmt = $this->projectType->read();
        }
        
        // Get project counts by type
        $projectCountsStmt = $this->projectType->countProjectsByType();
        $projectCounts = [];
        
        while ($row = $projectCountsStmt->fetch(PDO::FETCH_ASSOC)) {
            $projectCounts[$row['IdTypeProjet']] = $row['projectCount'];
        }
        
        // Display view
        require_once(APP_ROOT . '/views/project_types/index.php');
    }
    
    /**
     * Display project type creation form
     * 
     * @return void
     */
    public function create() {
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('project-types/create');
            }
            
            // Get form data
            $libelleTypeProjet = isset($_POST['LibelleTypeProjet']) ? trim($_POST['LibelleTypeProjet']) : '';
            $forfaitCoutTypeProjet = isset($_POST['ForfaitCoutTypeProjet']) ? trim($_POST['ForfaitCoutTypeProjet']) : '';
            
            // Validate form data
            $validator = new Validator([
                'LibelleTypeProjet' => $libelleTypeProjet,
                'ForfaitCoutTypeProjet' => $forfaitCoutTypeProjet
            ]);
            
            $validator->required('LibelleTypeProjet', 'Libellé du type de projet est obligatoire')
                      ->maxLength('LibelleTypeProjet', 100, 'Libellé du type de projet ne doit pas dépasser 100 caractères')
                      ->required('ForfaitCoutTypeProjet', 'Forfait coût du type de projet est obligatoire')
                      ->numeric('ForfaitCoutTypeProjet', 'Forfait coût du type de projet doit être un nombre');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'LibelleTypeProjet' => $libelleTypeProjet,
                    'ForfaitCoutTypeProjet' => $forfaitCoutTypeProjet
                ];
                redirect('project-types/create');
            }
            
            // Set project type properties
            $this->projectType->LibelleTypeProjet = $libelleTypeProjet;
            $this->projectType->ForfaitCoutTypeProjet = $forfaitCoutTypeProjet;
            
            // Create project type
            if ($this->projectType->create()) {
                // Project type created successfully
                setFlashMessage('success', 'Type de projet créé avec succès');
                redirect('project-types');
            } else {
                // Project type creation failed
                setFlashMessage('danger', 'Erreur lors de la création du type de projet');
                $_SESSION['form_data'] = [
                    'LibelleTypeProjet' => $libelleTypeProjet,
                    'ForfaitCoutTypeProjet' => $forfaitCoutTypeProjet
                ];
                redirect('project-types/create');
            }
        }
        
        // Display project type creation form
        require_once(APP_ROOT . '/views/project_types/create.php');
    }
    
    /**
     * Display project type edit form
     * 
     * @param int $id Project Type ID
     * @return void
     */
    public function edit($id) {
        // Get project type
        $this->projectType->IdTypeProjet = $id;
        $exists = $this->projectType->readOne();
        
        if (!$exists) {
            setFlashMessage('danger', 'Type de projet non trouvé');
            redirect('project-types');
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('project-types/edit/' . $id);
            }
            
            // Get form data
            $libelleTypeProjet = isset($_POST['LibelleTypeProjet']) ? trim($_POST['LibelleTypeProjet']) : '';
            $forfaitCoutTypeProjet = isset($_POST['ForfaitCoutTypeProjet']) ? trim($_POST['ForfaitCoutTypeProjet']) : '';
            
            // Validate form data
            $validator = new Validator([
                'LibelleTypeProjet' => $libelleTypeProjet,
                'ForfaitCoutTypeProjet' => $forfaitCoutTypeProjet
            ]);
            
            $validator->required('LibelleTypeProjet', 'Libellé du type de projet est obligatoire')
                      ->maxLength('LibelleTypeProjet', 100, 'Libellé du type de projet ne doit pas dépasser 100 caractères')
                      ->required('ForfaitCoutTypeProjet', 'Forfait coût du type de projet est obligatoire')
                      ->numeric('ForfaitCoutTypeProjet', 'Forfait coût du type de projet doit être un nombre');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'LibelleTypeProjet' => $libelleTypeProjet,
                    'ForfaitCoutTypeProjet' => $forfaitCoutTypeProjet
                ];
                redirect('project-types/edit/' . $id);
            }
            
            // Set project type properties
            $this->projectType->LibelleTypeProjet = $libelleTypeProjet;
            $this->projectType->ForfaitCoutTypeProjet = $forfaitCoutTypeProjet;
            
            // Update project type
            if ($this->projectType->update()) {
                // Project type updated successfully
                setFlashMessage('success', 'Type de projet mis à jour avec succès');
                redirect('project-types');
            } else {
                // Project type update failed
                setFlashMessage('danger', 'Erreur lors de la mise à jour du type de projet');
                redirect('project-types/edit/' . $id);
            }
        }
        
        // Display project type edit form
        require_once(APP_ROOT . '/views/project_types/edit.php');
    }
    
    /**
     * Display project type details
     * 
     * @param int $id Project Type ID
     * @return void
     */
    public function view($id) {
        // Get project type
        $this->projectType->IdTypeProjet = $id;
        $exists = $this->projectType->readOne();
        
        if (!$exists) {
            setFlashMessage('danger', 'Type de projet non trouvé');
            redirect('project-types');
        }
        
        // Display project type details
        require_once(APP_ROOT . '/views/project_types/view.php');
    }
    
    /**
     * Delete project type
     * 
     * @param int $id Project Type ID
     * @return void
     */
    public function delete($id) {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('danger', 'Invalid CSRF token');
            redirect('project-types');
        }
        
        // Set project type ID
        $this->projectType->IdTypeProjet = $id;
        
        // Check if project type exists
        if (!$this->projectType->readOne()) {
            setFlashMessage('danger', 'Type de projet non trouvé');
            redirect('project-types');
        }
        
        // Delete project type
        if ($this->projectType->delete()) {
            setFlashMessage('success', 'Type de projet supprimé avec succès');
        } else {
            setFlashMessage('danger', 'Impossible de supprimer ce type de projet car il est associé à des projets');
        }
        
        redirect('project-types');
    }
    
    /**
     * Export project types to CSV
     * 
     * @return void
     */
    public function exportCSV() {
        // Get all project types
        $stmt = $this->projectType->read();
        
        // Prepare data for export
        $projectTypes = [];
        $headers = ['ID', 'Libellé', 'Forfait Coût'];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $projectTypes[] = [
                $row['IdTypeProjet'],
                $row['LibelleTypeProjet'],
                $row['ForfaitCoutTypeProjet']
            ];
        }
        
        // Export to CSV
        Export::toCSV($projectTypes, $headers, 'types_projets');
    }
    
    /**
     * Export project types to PDF
     * 
     * @return void
     */
    public function exportPDF() {
        // Get all project types
        $stmt = $this->projectType->read();
        
        // Prepare data for export
        $projectTypes = [];
        $headers = ['ID', 'Libellé', 'Forfait Coût'];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $projectTypes[] = [
                $row['IdTypeProjet'],
                $row['LibelleTypeProjet'],
                formatMoney($row['ForfaitCoutTypeProjet'])
            ];
        }
        
        // Export to PDF
        Export::toPDF($projectTypes, $headers, 'Liste des Types de Projets', 'types_projets');
    }
}
?>
