<?php
/**
 * Assignment Controller
 * 
 * Handles assignment operations
 */
class AssignmentController {
    // Database connection and models
    private $db;
    private $assignment;
    private $personnel;
    private $task;
    
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
        $this->assignment = new Assignment($this->db);
        $this->personnel = new Personnel($this->db);
        $this->task = new Task($this->db);
    }
    
    /**
     * Display list of assignments
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
        $personnelMatricule = isset($_GET['personnel']) ? $_GET['personnel'] : '';
        $taskId = isset($_GET['task']) ? $_GET['task'] : '';
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
        
        // Get assignments based on search/filters
        if (!empty($search)) {
            $stmt = $this->assignment->search($search);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } elseif (!empty($personnelMatricule)) {
            $stmt = $this->assignment->filterByPersonnel($personnelMatricule);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } elseif (!empty($taskId)) {
            $stmt = $this->assignment->filterByTask($taskId);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } elseif (!empty($startDate) && !empty($endDate)) {
            $stmt = $this->assignment->filterByDateRange($startDate, $endDate);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } else {
            $totalRecords = $this->assignment->count();
            $totalPages = ceil($totalRecords / $perPage);
            $page = $page > $totalPages ? $totalPages : $page;
            $page = $page < 1 ? 1 : $page;
            $stmt = $this->assignment->read($page, $perPage);
        }
        
        // Get personnel for dropdown
        $personnelStmt = $this->personnel->getPersonnelDropdown();
        
        // Get tasks for dropdown
        $tasksStmt = $this->task->getTasksDropdown();
        
        // Display view
        require_once(APP_ROOT . '/views/assignments/index.php');
    }
    
    /**
     * Display assignment creation form
     * 
     * @return void
     */
    public function create() {
        // Get personnel for dropdown
        $personnelStmt = $this->personnel->getPersonnelDropdown();
        
        // Get tasks for dropdown
        $tasksStmt = $this->task->getTasksDropdown();
        
        // Get task ID and personnel matricule from query string (if coming from task or personnel view)
        $taskId = isset($_GET['task']) ? (int)$_GET['task'] : 0;
        $personnelMatricule = isset($_GET['personnel']) ? $_GET['personnel'] : '';
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('assignments/create');
            }
            
            // Get form data
            $dateAffectation = isset($_POST['DateAffectation']) ? trim($_POST['DateAffectation']) : '';
            $heureAffectation = isset($_POST['HeureAffectation']) ? trim($_POST['HeureAffectation']) : '';
            $fonctionAffectation = isset($_POST['FonctionAffectation']) ? trim($_POST['FonctionAffectation']) : '';
            $matriculePersonnel = isset($_POST['MatriculePersonnel']) ? trim($_POST['MatriculePersonnel']) : '';
            $idTache = isset($_POST['IdTache']) ? (int)$_POST['IdTache'] : 0;
            
            // Validate form data
            $validator = new Validator([
                'DateAffectation' => $dateAffectation,
                'HeureAffectation' => $heureAffectation,
                'FonctionAffectation' => $fonctionAffectation,
                'MatriculePersonnel' => $matriculePersonnel,
                'IdTache' => $idTache
            ]);
            
            $validator->required('DateAffectation', 'Date d\'affectation est obligatoire')
                      ->date('DateAffectation', 'Y-m-d', 'Date d\'affectation est invalide')
                      ->required('HeureAffectation', 'Heure d\'affectation est obligatoire')
                      ->time('HeureAffectation', 'H:i', 'Heure d\'affectation est invalide')
                      ->required('FonctionAffectation', 'Fonction d\'affectation est obligatoire')
                      ->maxLength('FonctionAffectation', 100, 'Fonction d\'affectation ne doit pas dépasser 100 caractères')
                      ->required('MatriculePersonnel', 'Personnel est obligatoire')
                      ->required('IdTache', 'Tâche est obligatoire');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'DateAffectation' => $dateAffectation,
                    'HeureAffectation' => $heureAffectation,
                    'FonctionAffectation' => $fonctionAffectation,
                    'MatriculePersonnel' => $matriculePersonnel,
                    'IdTache' => $idTache
                ];
                redirect('assignments/create');
            }
            
            // Set assignment properties
            $this->assignment->DateAffectation = $dateAffectation;
            $this->assignment->HeureAffectation = $heureAffectation;
            $this->assignment->FonctionAffectation = $fonctionAffectation;
            $this->assignment->MatriculePersonnel = $matriculePersonnel;
            $this->assignment->IdTache = $idTache;
            
            // Create assignment
            if ($this->assignment->create()) {
                // Assignment created successfully
                setFlashMessage('success', 'Affectation créée avec succès');
                
                // Redirect based on context
                if (isset($_POST['redirect_to_task']) && $_POST['redirect_to_task'] == '1') {
                    redirect('tasks/view/' . $idTache);
                } elseif (isset($_POST['redirect_to_personnel']) && $_POST['redirect_to_personnel'] == '1') {
                    redirect('personnel/view/' . $matriculePersonnel);
                } else {
                    redirect('assignments');
                }
            } else {
                // Assignment creation failed
                setFlashMessage('danger', 'Erreur lors de la création de l\'affectation');
                $_SESSION['form_data'] = [
                    'DateAffectation' => $dateAffectation,
                    'HeureAffectation' => $heureAffectation,
                    'FonctionAffectation' => $fonctionAffectation,
                    'MatriculePersonnel' => $matriculePersonnel,
                    'IdTache' => $idTache
                ];
                redirect('assignments/create');
            }
        }
        
        // Display assignment creation form
        require_once(APP_ROOT . '/views/assignments/create.php');
    }
    
    /**
     * Display assignment edit form
     * 
     * @param int $id Assignment ID
     * @return void
     */
    public function edit($id) {
        // Get assignment
        $this->assignment->IdAffectation = $id;
        $exists = $this->assignment->readOne();
        
        if (!$exists) {
            setFlashMessage('danger', 'Affectation non trouvée');
            redirect('assignments');
        }
        
        // Get personnel for dropdown
        $personnelStmt = $this->personnel->getPersonnelDropdown();
        
        // Get tasks for dropdown
        $tasksStmt = $this->task->getTasksDropdown();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('assignments/edit/' . $id);
            }
            
            // Get form data
            $dateAffectation = isset($_POST['DateAffectation']) ? trim($_POST['DateAffectation']) : '';
            $heureAffectation = isset($_POST['HeureAffectation']) ? trim($_POST['HeureAffectation']) : '';
            $fonctionAffectation = isset($_POST['FonctionAffectation']) ? trim($_POST['FonctionAffectation']) : '';
            $matriculePersonnel = isset($_POST['MatriculePersonnel']) ? trim($_POST['MatriculePersonnel']) : '';
            $idTache = isset($_POST['IdTache']) ? (int)$_POST['IdTache'] : 0;
            
            // Validate form data
            $validator = new Validator([
                'DateAffectation' => $dateAffectation,
                'HeureAffectation' => $heureAffectation,
                'FonctionAffectation' => $fonctionAffectation,
                'MatriculePersonnel' => $matriculePersonnel,
                'IdTache' => $idTache
            ]);
            
            $validator->required('DateAffectation', 'Date d\'affectation est obligatoire')
                      ->date('DateAffectation', 'Y-m-d', 'Date d\'affectation est invalide')
                      ->required('HeureAffectation', 'Heure d\'affectation est obligatoire')
                      ->time('HeureAffectation', 'H:i', 'Heure d\'affectation est invalide')
                      ->required('FonctionAffectation', 'Fonction d\'affectation est obligatoire')
                      ->maxLength('FonctionAffectation', 100, 'Fonction d\'affectation ne doit pas dépasser 100 caractères')
                      ->required('MatriculePersonnel', 'Personnel est obligatoire')
                      ->required('IdTache', 'Tâche est obligatoire');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'DateAffectation' => $dateAffectation,
                    'HeureAffectation' => $heureAffectation,
                    'FonctionAffectation' => $fonctionAffectation,
                    'MatriculePersonnel' => $matriculePersonnel,
                    'IdTache' => $idTache
                ];
                redirect('assignments/edit/' . $id);
            }
            
            // Store original values for possible redirection
            $originalTaskId = $this->assignment->IdTache;
            $originalMatricule = $this->assignment->MatriculePersonnel;
            
            // Set assignment properties
            $this->assignment->DateAffectation = $dateAffectation;
            $this->assignment->HeureAffectation = $heureAffectation;
            $this->assignment->FonctionAffectation = $fonctionAffectation;
            $this->assignment->MatriculePersonnel = $matriculePersonnel;
            $this->assignment->IdTache = $idTache;
            
            // Update assignment
            if ($this->assignment->update()) {
                // Assignment updated successfully
                setFlashMessage('success', 'Affectation mise à jour avec succès');
                
                // Redirect based on context
                if (isset($_POST['redirect_to_task']) && $_POST['redirect_to_task'] == '1') {
                    redirect('tasks/view/' . $originalTaskId);
                } elseif (isset($_POST['redirect_to_personnel']) && $_POST['redirect_to_personnel'] == '1') {
                    redirect('personnel/view/' . $originalMatricule);
                } else {
                    redirect('assignments');
                }
            } else {
                // Assignment update failed
                setFlashMessage('danger', 'Erreur lors de la mise à jour de l\'affectation');
                redirect('assignments/edit/' . $id);
            }
        }
        
        // Display assignment edit form
        require_once(APP_ROOT . '/views/assignments/edit.php');
    }
    
    /**
     * Delete assignment
     * 
     * @param int $id Assignment ID
     * @return void
     */
    public function delete($id) {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('danger', 'Invalid CSRF token');
            redirect('assignments');
        }
        
        // Set assignment ID
        $this->assignment->IdAffectation = $id;
        
        // Check if assignment exists
        $exists = $this->assignment->readOne();
        if (!$exists) {
            setFlashMessage('danger', 'Affectation non trouvée');
            redirect('assignments');
        }
        
        // Store task ID and personnel matricule for possible redirection
        $taskId = $this->assignment->IdTache;
        $matricule = $this->assignment->MatriculePersonnel;
        $redirectToTask = isset($_POST['redirect_to_task']) && $_POST['redirect_to_task'] == '1';
        $redirectToPersonnel = isset($_POST['redirect_to_personnel']) && $_POST['redirect_to_personnel'] == '1';
        
        // Delete assignment
        if ($this->assignment->delete()) {
            setFlashMessage('success', 'Affectation supprimée avec succès');
            
            // Redirect based on context
            if ($redirectToTask) {
                redirect('tasks/view/' . $taskId);
            } elseif ($redirectToPersonnel) {
                redirect('personnel/view/' . $matricule);
            }
        } else {
            setFlashMessage('danger', 'Erreur lors de la suppression de l\'affectation');
        }
        
        // Default redirect
        if ($redirectToTask) {
            redirect('tasks/view/' . $taskId);
        } elseif ($redirectToPersonnel) {
            redirect('personnel/view/' . $matricule);
        } else {
            redirect('assignments');
        }
    }
    
    /**
     * Export assignments to CSV
     * 
     * @return void
     */
    public function exportCSV() {
        // Get all assignments
        $stmt = $this->assignment->read(1, 1000); // Limit to 1000 records
        
        // Prepare data for export
        $assignments = [];
        $headers = ['ID', 'Date', 'Heure', 'Fonction', 'Personnel', 'Tâche', 'Projet'];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $assignments[] = [
                $row['IdAffectation'],
                formatDate($row['DateAffectation']),
                $row['HeureAffectation'],
                $row['FonctionAffectation'],
                $row['NomPersonnel'] . ' ' . $row['PrenomPersonnel'],
                $row['LibelleTache'],
                $row['TitreProjet']
            ];
        }
        
        // Export to CSV
        Export::toCSV($assignments, $headers, 'affectations');
    }
    
    /**
     * Export assignments to PDF
     * 
     * @return void
     */
    public function exportPDF() {
        // Get all assignments
        $stmt = $this->assignment->read(1, 1000); // Limit to 1000 records
        
        // Prepare data for export
        $assignments = [];
        $headers = ['ID', 'Date', 'Heure', 'Personnel', 'Tâche', 'Fonction'];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $assignments[] = [
                $row['IdAffectation'],
                formatDate($row['DateAffectation']),
                $row['HeureAffectation'],
                $row['NomPersonnel'] . ' ' . $row['PrenomPersonnel'],
                $row['LibelleTache'],
                $row['FonctionAffectation']
            ];
        }
        
        // Export to PDF
        Export::toPDF($assignments, $headers, 'Liste des Affectations', 'affectations');
    }
}
?>
