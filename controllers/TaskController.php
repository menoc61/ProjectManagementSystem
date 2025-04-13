<?php
/**
 * Task Controller
 * 
 * Handles task operations
 */
class TaskController {
    // Database connection and models
    private $db;
    private $task;
    private $project;
    
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
        $this->task = new Task($this->db);
        $this->project = new Project($this->db);
    }
    
    /**
     * Display list of tasks
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
        $projectId = isset($_GET['project']) ? $_GET['project'] : '';
        
        // Get tasks based on search/filters
        if (!empty($search)) {
            $stmt = $this->task->search($search);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } elseif (!empty($status)) {
            $stmt = $this->task->filterByStatus($status);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } elseif (!empty($projectId)) {
            $stmt = $this->task->filterByProject($projectId);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } else {
            $totalRecords = $this->task->count();
            $totalPages = ceil($totalRecords / $perPage);
            $page = $page > $totalPages ? $totalPages : $page;
            $page = $page < 1 ? 1 : $page;
            $stmt = $this->task->read($page, $perPage);
        }
        
        // Get projects for dropdown
        $projectsStmt = $this->project->getProjectsDropdown();
        
        // Get task counts by status
        $statusCounts = $this->task->countByStatus();
        
        // Display view
        require_once(APP_ROOT . '/views/tasks/index.php');
    }
    
    /**
     * Display task creation form
     * 
     * @return void
     */
    public function create() {
        // Get projects for dropdown
        $projectsStmt = $this->project->getProjectsDropdown();
        
        // Get project ID from query string (if coming from project view)
        $projectId = isset($_GET['project']) ? (int)$_GET['project'] : 0;
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('tasks/create');
            }
            
            // Get form data
            $libelleTache = isset($_POST['LibelleTache']) ? trim($_POST['LibelleTache']) : '';
            $dateDebutTache = isset($_POST['DateDebutTache']) ? trim($_POST['DateDebutTache']) : '';
            $dateFinTache = isset($_POST['DateFinTache']) ? trim($_POST['DateFinTache']) : '';
            $etatTache = isset($_POST['EtatTache']) ? (int)$_POST['EtatTache'] : TASK_STATUS_PENDING;
            $idProjet = isset($_POST['IdProjet']) ? (int)$_POST['IdProjet'] : 0;
            
            // Validate form data
            $validator = new Validator([
                'LibelleTache' => $libelleTache,
                'DateDebutTache' => $dateDebutTache,
                'DateFinTache' => $dateFinTache,
                'EtatTache' => $etatTache,
                'IdProjet' => $idProjet
            ]);
            
            $validator->required('LibelleTache', 'Libellé de la tâche est obligatoire')
                      ->maxLength('LibelleTache', 150, 'Libellé de la tâche ne doit pas dépasser 150 caractères')
                      ->required('DateDebutTache', 'Date de début de la tâche est obligatoire')
                      ->date('DateDebutTache', 'Y-m-d', 'Date de début de la tâche est invalide')
                      ->required('DateFinTache', 'Date de fin de la tâche est obligatoire')
                      ->date('DateFinTache', 'Y-m-d', 'Date de fin de la tâche est invalide')
                      ->required('IdProjet', 'Projet est obligatoire');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'LibelleTache' => $libelleTache,
                    'DateDebutTache' => $dateDebutTache,
                    'DateFinTache' => $dateFinTache,
                    'EtatTache' => $etatTache,
                    'IdProjet' => $idProjet
                ];
                redirect('tasks/create');
            }
            
            // Set task properties
            $this->task->LibelleTache = $libelleTache;
            $this->task->DateDebutTache = $dateDebutTache;
            $this->task->DateFinTache = $dateFinTache;
            $this->task->EtatTache = $etatTache;
            $this->task->IdProjet = $idProjet;
            
            // Create task
            if ($this->task->create()) {
                // Task created successfully
                setFlashMessage('success', 'Tâche créée avec succès');
                
                // Redirect to project view if from project
                if ($idProjet) {
                    redirect('projects/view/' . $idProjet);
                } else {
                    redirect('tasks');
                }
            } else {
                // Task creation failed
                setFlashMessage('danger', 'Erreur lors de la création de la tâche');
                $_SESSION['form_data'] = [
                    'LibelleTache' => $libelleTache,
                    'DateDebutTache' => $dateDebutTache,
                    'DateFinTache' => $dateFinTache,
                    'EtatTache' => $etatTache,
                    'IdProjet' => $idProjet
                ];
                redirect('tasks/create');
            }
        }
        
        // Display task creation form
        require_once(APP_ROOT . '/views/tasks/create.php');
    }
    
    /**
     * Display task edit form
     * 
     * @param int $id Task ID
     * @return void
     */
    public function edit($id) {
        // Get task
        $this->task->IdTache = $id;
        $exists = $this->task->readOne();
        
        if (!$exists) {
            setFlashMessage('danger', 'Tâche non trouvée');
            redirect('tasks');
        }
        
        // Get projects for dropdown
        $projectsStmt = $this->project->getProjectsDropdown();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('tasks/edit/' . $id);
            }
            
            // Get form data
            $libelleTache = isset($_POST['LibelleTache']) ? trim($_POST['LibelleTache']) : '';
            $dateDebutTache = isset($_POST['DateDebutTache']) ? trim($_POST['DateDebutTache']) : '';
            $dateFinTache = isset($_POST['DateFinTache']) ? trim($_POST['DateFinTache']) : '';
            $etatTache = isset($_POST['EtatTache']) ? (int)$_POST['EtatTache'] : TASK_STATUS_PENDING;
            $idProjet = isset($_POST['IdProjet']) ? (int)$_POST['IdProjet'] : 0;
            
            // Validate form data
            $validator = new Validator([
                'LibelleTache' => $libelleTache,
                'DateDebutTache' => $dateDebutTache,
                'DateFinTache' => $dateFinTache,
                'EtatTache' => $etatTache,
                'IdProjet' => $idProjet
            ]);
            
            $validator->required('LibelleTache', 'Libellé de la tâche est obligatoire')
                      ->maxLength('LibelleTache', 150, 'Libellé de la tâche ne doit pas dépasser 150 caractères')
                      ->required('DateDebutTache', 'Date de début de la tâche est obligatoire')
                      ->date('DateDebutTache', 'Y-m-d', 'Date de début de la tâche est invalide')
                      ->required('DateFinTache', 'Date de fin de la tâche est obligatoire')
                      ->date('DateFinTache', 'Y-m-d', 'Date de fin de la tâche est invalide')
                      ->required('IdProjet', 'Projet est obligatoire');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'LibelleTache' => $libelleTache,
                    'DateDebutTache' => $dateDebutTache,
                    'DateFinTache' => $dateFinTache,
                    'EtatTache' => $etatTache,
                    'IdProjet' => $idProjet
                ];
                redirect('tasks/edit/' . $id);
            }
            
            // Set task properties
            $this->task->LibelleTache = $libelleTache;
            $this->task->DateDebutTache = $dateDebutTache;
            $this->task->DateFinTache = $dateFinTache;
            $this->task->EtatTache = $etatTache;
            $this->task->IdProjet = $idProjet;
            
            // Update task
            if ($this->task->update()) {
                // Task updated successfully
                setFlashMessage('success', 'Tâche mise à jour avec succès');
                
                // Check if we need to redirect to project view
                if (isset($_POST['redirect_to_project']) && $_POST['redirect_to_project'] == '1') {
                    redirect('projects/view/' . $idProjet);
                } else {
                    redirect('tasks');
                }
            } else {
                // Task update failed
                setFlashMessage('danger', 'Erreur lors de la mise à jour de la tâche');
                redirect('tasks/edit/' . $id);
            }
        }
        
        // Display task edit form
        require_once(APP_ROOT . '/views/tasks/edit.php');
    }
    
    /**
     * Display task details
     * 
     * @param int $id Task ID
     * @return void
     */
    public function view($id) {
        // Get task
        $this->task->IdTache = $id;
        $exists = $this->task->readOne();
        
        if (!$exists) {
            setFlashMessage('danger', 'Tâche non trouvée');
            redirect('tasks');
        }
        
        // Get task assignments
        $assignmentsStmt = $this->task->getAssignments();
        
        // Display task details
        require_once(APP_ROOT . '/views/tasks/view.php');
    }
    
    /**
     * Delete task
     * 
     * @param int $id Task ID
     * @return void
     */
    public function delete($id) {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('danger', 'Invalid CSRF token');
            redirect('tasks');
        }
        
        // Set task ID
        $this->task->IdTache = $id;
        
        // Check if task exists
        $exists = $this->task->readOne();
        if (!$exists) {
            setFlashMessage('danger', 'Tâche non trouvée');
            redirect('tasks');
        }
        
        // Store project ID for possible redirection
        $projectId = $this->task->IdProjet;
        $redirectToProject = isset($_POST['redirect_to_project']) && $_POST['redirect_to_project'] == '1';
        
        // Delete task
        if ($this->task->delete()) {
            setFlashMessage('success', 'Tâche supprimée avec succès');
            
            // Redirect to project view if requested
            if ($redirectToProject) {
                redirect('projects/view/' . $projectId);
            }
        } else {
            setFlashMessage('danger', 'Erreur lors de la suppression de la tâche');
        }
        
        // Redirect to tasks list or project view
        if ($redirectToProject) {
            redirect('projects/view/' . $projectId);
        } else {
            redirect('tasks');
        }
    }
    
    /**
     * Export tasks to CSV
     * 
     * @return void
     */
    public function exportCSV() {
        // Get all tasks
        $stmt = $this->task->read(1, 1000); // Limit to 1000 records
        
        // Prepare data for export
        $tasks = [];
        $headers = ['ID', 'Libellé', 'Projet', 'Date d\'enregistrement', 'Date début', 'Date fin', 'État'];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tasks[] = [
                $row['IdTache'],
                $row['LibelleTache'],
                $row['TitreProjet'],
                formatDate($row['DateEnregTache']),
                formatDate($row['DateDebutTache']),
                formatDate($row['DateFinTache']),
                getTaskStatusText($row['EtatTache'])
            ];
        }
        
        // Export to CSV
        Export::toCSV($tasks, $headers, 'taches');
    }
    
    /**
     * Export tasks to PDF
     * 
     * @return void
     */
    public function exportPDF() {
        // Get all tasks
        $stmt = $this->task->read(1, 1000); // Limit to 1000 records
        
        // Prepare data for export
        $tasks = [];
        $headers = ['ID', 'Libellé', 'Projet', 'Date début', 'Date fin', 'État'];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tasks[] = [
                $row['IdTache'],
                $row['LibelleTache'],
                $row['TitreProjet'],
                formatDate($row['DateDebutTache']),
                formatDate($row['DateFinTache']),
                getTaskStatusText($row['EtatTache'])
            ];
        }
        
        // Export to PDF
        Export::toPDF($tasks, $headers, 'Liste des Tâches', 'taches');
    }
}
?>
