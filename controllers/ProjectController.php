<?php
/**
 * Project Controller
 * 
 * Handles project operations
 */
class ProjectController {
    // Database connection and models
    private $db;
    private $project;
    private $client;
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
        
        // Initialize models
        $this->project = new Project($this->db);
        $this->client = new Client($this->db);
        $this->projectType = new ProjectType($this->db);
    }
    
    /**
     * Display list of projects
     * 
     * @return void
     */
    public function index() {
        // Get page number
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = $page < 1 ? 1 : $page;
        $perPage = 10;
        
        // Get search keyword and filters
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $clientId = isset($_GET['client']) ? $_GET['client'] : '';
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
        
        // Get projects based on search/filters
        if (!empty($search)) {
            $stmt = $this->project->search($search);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } elseif (!empty($status)) {
            $stmt = $this->project->filterByStatus($status);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } elseif (!empty($clientId)) {
            $stmt = $this->project->filterByClient($clientId);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } elseif (!empty($startDate) && !empty($endDate)) {
            $stmt = $this->project->filterByDateRange($startDate, $endDate);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } else {
            $totalRecords = $this->project->count();
            $totalPages = ceil($totalRecords / $perPage);
            $page = $page > $totalPages ? $totalPages : $page;
            $page = $page < 1 ? 1 : $page;
            $stmt = $this->project->read($page, $perPage);
        }
        
        // Get clients for dropdown
        $clientsStmt = $this->client->getClientsDropdown();
        
        // Get project counts by status
        $statusCounts = $this->project->countByStatus();
        
        // Display view
        require_once(APP_ROOT . '/views/projects/index.php');
    }
    
    /**
     * Display project creation form
     * 
     * @return void
     */
    public function create() {
        // Get clients for dropdown
        $clientsStmt = $this->client->getClientsDropdown();
        
        // Get project types for dropdown
        $typesStmt = $this->projectType->getProjectTypesDropdown();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('projects/create');
            }
            
            // Get form data
            $titreProjet = isset($_POST['TitreProjet']) ? trim($_POST['TitreProjet']) : '';
            $descriptionProjet = isset($_POST['DescriptionProjet']) ? trim($_POST['DescriptionProjet']) : '';
            $coutProjet = isset($_POST['CoutProjet']) ? trim($_POST['CoutProjet']) : '';
            $dateDebutProjet = isset($_POST['DateDebutProjet']) ? trim($_POST['DateDebutProjet']) : '';
            $dateFinProjet = isset($_POST['DateFinProjet']) ? trim($_POST['DateFinProjet']) : '';
            $etatProjet = isset($_POST['EtatProjet']) ? (int)$_POST['EtatProjet'] : PROJECT_STATUS_PENDING;
            $idClient = isset($_POST['IdClient']) ? (int)$_POST['IdClient'] : 0;
            $idTypeProjet = isset($_POST['IdTypeProjet']) ? (int)$_POST['IdTypeProjet'] : 0;
            
            // Validate form data
            $validator = new Validator([
                'TitreProjet' => $titreProjet,
                'DescriptionProjet' => $descriptionProjet,
                'CoutProjet' => $coutProjet,
                'DateDebutProjet' => $dateDebutProjet,
                'DateFinProjet' => $dateFinProjet,
                'EtatProjet' => $etatProjet,
                'IdClient' => $idClient,
                'IdTypeProjet' => $idTypeProjet
            ]);
            
            $validator->required('TitreProjet', 'Titre du projet est obligatoire')
                      ->maxLength('TitreProjet', 150, 'Titre du projet ne doit pas dépasser 150 caractères')
                      ->required('DescriptionProjet', 'Description du projet est obligatoire')
                      ->required('CoutProjet', 'Coût du projet est obligatoire')
                      ->numeric('CoutProjet', 'Coût du projet doit être un nombre')
                      ->required('DateDebutProjet', 'Date de début du projet est obligatoire')
                      ->date('DateDebutProjet', 'Y-m-d', 'Date de début du projet est invalide')
                      ->required('DateFinProjet', 'Date de fin du projet est obligatoire')
                      ->date('DateFinProjet', 'Y-m-d', 'Date de fin du projet est invalide')
                      ->required('IdClient', 'Client est obligatoire')
                      ->required('IdTypeProjet', 'Type de projet est obligatoire');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'TitreProjet' => $titreProjet,
                    'DescriptionProjet' => $descriptionProjet,
                    'CoutProjet' => $coutProjet,
                    'DateDebutProjet' => $dateDebutProjet,
                    'DateFinProjet' => $dateFinProjet,
                    'EtatProjet' => $etatProjet,
                    'IdClient' => $idClient,
                    'IdTypeProjet' => $idTypeProjet
                ];
                redirect('projects/create');
            }
            
            // Set project properties
            $this->project->TitreProjet = $titreProjet;
            $this->project->DescriptionProjet = $descriptionProjet;
            $this->project->CoutProjet = $coutProjet;
            $this->project->DateDebutProjet = $dateDebutProjet;
            $this->project->DateFinProjet = $dateFinProjet;
            $this->project->EtatProjet = $etatProjet;
            $this->project->IdClient = $idClient;
            $this->project->IdTypeProjet = $idTypeProjet;
            
            // Create project
            if ($this->project->create()) {
                // Project created successfully
                setFlashMessage('success', 'Projet créé avec succès');
                redirect('projects');
            } else {
                // Project creation failed
                setFlashMessage('danger', 'Erreur lors de la création du projet');
                $_SESSION['form_data'] = [
                    'TitreProjet' => $titreProjet,
                    'DescriptionProjet' => $descriptionProjet,
                    'CoutProjet' => $coutProjet,
                    'DateDebutProjet' => $dateDebutProjet,
                    'DateFinProjet' => $dateFinProjet,
                    'EtatProjet' => $etatProjet,
                    'IdClient' => $idClient,
                    'IdTypeProjet' => $idTypeProjet
                ];
                redirect('projects/create');
            }
        }
        
        // Display project creation form
        require_once(APP_ROOT . '/views/projects/create.php');
    }
    
    /**
     * Display project edit form
     * 
     * @param int $id Project ID
     * @return void
     */
    public function edit($id) {
        // Get project
        $this->project->IdProjet = $id;
        $exists = $this->project->readOne();
        
        if (!$exists) {
            setFlashMessage('danger', 'Projet non trouvé');
            redirect('projects');
        }
        
        // Get clients for dropdown
        $clientsStmt = $this->client->getClientsDropdown();
        
        // Get project types for dropdown
        $typesStmt = $this->projectType->getProjectTypesDropdown();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('projects/edit/' . $id);
            }
            
            // Get form data
            $titreProjet = isset($_POST['TitreProjet']) ? trim($_POST['TitreProjet']) : '';
            $descriptionProjet = isset($_POST['DescriptionProjet']) ? trim($_POST['DescriptionProjet']) : '';
            $coutProjet = isset($_POST['CoutProjet']) ? trim($_POST['CoutProjet']) : '';
            $dateDebutProjet = isset($_POST['DateDebutProjet']) ? trim($_POST['DateDebutProjet']) : '';
            $dateFinProjet = isset($_POST['DateFinProjet']) ? trim($_POST['DateFinProjet']) : '';
            $etatProjet = isset($_POST['EtatProjet']) ? (int)$_POST['EtatProjet'] : PROJECT_STATUS_PENDING;
            $idClient = isset($_POST['IdClient']) ? (int)$_POST['IdClient'] : 0;
            $idTypeProjet = isset($_POST['IdTypeProjet']) ? (int)$_POST['IdTypeProjet'] : 0;
            
            // Validate form data
            $validator = new Validator([
                'TitreProjet' => $titreProjet,
                'DescriptionProjet' => $descriptionProjet,
                'CoutProjet' => $coutProjet,
                'DateDebutProjet' => $dateDebutProjet,
                'DateFinProjet' => $dateFinProjet,
                'EtatProjet' => $etatProjet,
                'IdClient' => $idClient,
                'IdTypeProjet' => $idTypeProjet
            ]);
            
            $validator->required('TitreProjet', 'Titre du projet est obligatoire')
                      ->maxLength('TitreProjet', 150, 'Titre du projet ne doit pas dépasser 150 caractères')
                      ->required('DescriptionProjet', 'Description du projet est obligatoire')
                      ->required('CoutProjet', 'Coût du projet est obligatoire')
                      ->numeric('CoutProjet', 'Coût du projet doit être un nombre')
                      ->required('DateDebutProjet', 'Date de début du projet est obligatoire')
                      ->date('DateDebutProjet', 'Y-m-d', 'Date de début du projet est invalide')
                      ->required('DateFinProjet', 'Date de fin du projet est obligatoire')
                      ->date('DateFinProjet', 'Y-m-d', 'Date de fin du projet est invalide')
                      ->required('IdClient', 'Client est obligatoire')
                      ->required('IdTypeProjet', 'Type de projet est obligatoire');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'TitreProjet' => $titreProjet,
                    'DescriptionProjet' => $descriptionProjet,
                    'CoutProjet' => $coutProjet,
                    'DateDebutProjet' => $dateDebutProjet,
                    'DateFinProjet' => $dateFinProjet,
                    'EtatProjet' => $etatProjet,
                    'IdClient' => $idClient,
                    'IdTypeProjet' => $idTypeProjet
                ];
                redirect('projects/edit/' . $id);
            }
            
            // Set project properties
            $this->project->TitreProjet = $titreProjet;
            $this->project->DescriptionProjet = $descriptionProjet;
            $this->project->CoutProjet = $coutProjet;
            $this->project->DateDebutProjet = $dateDebutProjet;
            $this->project->DateFinProjet = $dateFinProjet;
            $this->project->EtatProjet = $etatProjet;
            $this->project->IdClient = $idClient;
            $this->project->IdTypeProjet = $idTypeProjet;
            
            // Update project
            if ($this->project->update()) {
                // Project updated successfully
                setFlashMessage('success', 'Projet mis à jour avec succès');
                redirect('projects');
            } else {
                // Project update failed
                setFlashMessage('danger', 'Erreur lors de la mise à jour du projet');
                redirect('projects/edit/' . $id);
            }
        }
        
        // Display project edit form
        require_once(APP_ROOT . '/views/projects/edit.php');
    }
    
    /**
     * Display project details
     * 
     * @param int $id Project ID
     * @return void
     */
    public function view($id) {
        // Get project
        $this->project->IdProjet = $id;
        $exists = $this->project->readOne();
        
        if (!$exists) {
            setFlashMessage('danger', 'Projet non trouvé');
            redirect('projects');
        }
        
        // Get project tasks
        $tasksStmt = $this->project->getTasks();
        
        // Display project details
        require_once(APP_ROOT . '/views/projects/view.php');
    }
    
    /**
     * Delete project
     * 
     * @param int $id Project ID
     * @return void
     */
    public function delete($id) {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('danger', 'Invalid CSRF token');
            redirect('projects');
        }
        
        // Set project ID
        $this->project->IdProjet = $id;
        
        // Check if project exists
        if (!$this->project->readOne()) {
            setFlashMessage('danger', 'Projet non trouvé');
            redirect('projects');
        }
        
        // Delete project
        if ($this->project->delete()) {
            setFlashMessage('success', 'Projet supprimé avec succès');
        } else {
            setFlashMessage('danger', 'Erreur lors de la suppression du projet');
        }
        
        redirect('projects');
    }
    
    /**
     * Export projects to CSV
     * 
     * @return void
     */
    public function exportCSV() {
        // Get all projects
        $stmt = $this->project->read(1, 1000); // Limit to 1000 records
        
        // Prepare data for export
        $projects = [];
        $headers = ['ID', 'Titre', 'Client', 'Type', 'Coût', 'Date début', 'Date fin', 'État'];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $projects[] = [
                $row['IdProjet'],
                $row['TitreProjet'],
                $row['NomClient'],
                $row['LibelleTypeProjet'],
                $row['CoutProjet'],
                formatDate($row['DateDebutProjet']),
                formatDate($row['DateFinProjet']),
                getProjectStatusText($row['EtatProjet'])
            ];
        }
        
        // Export to CSV
        Export::toCSV($projects, $headers, 'projets');
    }
    
    /**
     * Export projects to PDF
     * 
     * @return void
     */
    public function exportPDF() {
        // Get all projects
        $stmt = $this->project->read(1, 1000); // Limit to 1000 records
        
        // Prepare data for export
        $projects = [];
        $headers = ['ID', 'Titre', 'Client', 'Coût', 'Date début', 'Date fin', 'État'];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $projects[] = [
                $row['IdProjet'],
                $row['TitreProjet'],
                $row['NomClient'],
                formatMoney($row['CoutProjet']),
                formatDate($row['DateDebutProjet']),
                formatDate($row['DateFinProjet']),
                getProjectStatusText($row['EtatProjet'])
            ];
        }
        
        // Export to PDF
        Export::toPDF($projects, $headers, 'Liste des Projets', 'projets');
    }
    
    /**
     * Export project details to PDF
     * 
     * @param int $id Project ID
     * @return void
     */
    public function exportDetailsPDF($id) {
        // Get project
        $this->project->IdProjet = $id;
        $exists = $this->project->readOne();
        
        if (!$exists) {
            setFlashMessage('danger', 'Projet non trouvé');
            redirect('projects');
        }
        
        // Get project tasks
        $tasksStmt = $this->project->getTasks();
        $tasks = [];
        
        while ($row = $tasksStmt->fetch(PDO::FETCH_ASSOC)) {
            $tasks[] = $row;
        }
        
        // Get client
        $client = [
            'NomClient' => $this->project->NomClient
        ];
        
        // Export to PDF
        Export::projectInfoPDF([
            'IdProjet' => $this->project->IdProjet,
            'TitreProjet' => $this->project->TitreProjet,
            'DescriptionProjet' => $this->project->DescriptionProjet,
            'CoutProjet' => $this->project->CoutProjet,
            'DateDebutProjet' => $this->project->DateDebutProjet,
            'DateFinProjet' => $this->project->DateFinProjet,
            'EtatProjet' => $this->project->EtatProjet,
            'LibelleTypeProjet' => $this->project->LibelleTypeProjet
        ], $client, $tasks, 'projet_' . $id);
    }
}
?>
