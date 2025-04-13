<?php
/**
 * Client Controller
 * 
 * Handles client operations
 */
class ClientController {
    // Database connection and model
    private $db;
    private $client;
    
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
        
        // Initialize client model
        $this->client = new Client($this->db);
    }
    
    /**
     * Display list of clients
     * 
     * @return void
     */
    public function index() {
        // Get page number
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = $page < 1 ? 1 : $page;
        $perPage = 10;
        
        // Get search keyword
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Get clients
        if (!empty($search)) {
            $stmt = $this->client->search($search);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } else {
            $totalRecords = $this->client->count();
            $totalPages = ceil($totalRecords / $perPage);
            $page = $page > $totalPages ? $totalPages : $page;
            $page = $page < 1 ? 1 : $page;
            $stmt = $this->client->read($page, $perPage);
        }
        
        // Display view
        require_once(APP_ROOT . '/views/clients/index.php');
    }
    
    /**
     * Display client creation form
     * 
     * @return void
     */
    public function create() {
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('clients/create');
            }
            
            // Get form data
            $nomClient = isset($_POST['NomClient']) ? trim($_POST['NomClient']) : '';
            $adresseClient = isset($_POST['AdresseClient']) ? trim($_POST['AdresseClient']) : '';
            $emailClient = isset($_POST['EmailClient']) ? trim($_POST['EmailClient']) : '';
            $telClient = isset($_POST['TelClient']) ? trim($_POST['TelClient']) : '';
            
            // Validate form data
            $validator = new Validator([
                'NomClient' => $nomClient,
                'AdresseClient' => $adresseClient,
                'EmailClient' => $emailClient,
                'TelClient' => $telClient
            ]);
            
            $validator->required('NomClient', 'Nom du client est obligatoire')
                      ->maxLength('NomClient', 100, 'Nom du client ne doit pas dépasser 100 caractères')
                      ->required('AdresseClient', 'Adresse du client est obligatoire')
                      ->maxLength('AdresseClient', 255, 'Adresse du client ne doit pas dépasser 255 caractères')
                      ->required('EmailClient', 'Email du client est obligatoire')
                      ->email('EmailClient', 'Email du client est invalide')
                      ->maxLength('EmailClient', 100, 'Email du client ne doit pas dépasser 100 caractères')
                      ->required('TelClient', 'Téléphone du client est obligatoire')
                      ->phone('TelClient', 'Téléphone du client est invalide')
                      ->maxLength('TelClient', 20, 'Téléphone du client ne doit pas dépasser 20 caractères');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'NomClient' => $nomClient,
                    'AdresseClient' => $adresseClient,
                    'EmailClient' => $emailClient,
                    'TelClient' => $telClient
                ];
                redirect('clients/create');
            }
            
            // Set client properties
            $this->client->NomClient = $nomClient;
            $this->client->AdresseClient = $adresseClient;
            $this->client->EmailClient = $emailClient;
            $this->client->TelClient = $telClient;
            
            // Create client
            if ($this->client->create()) {
                // Client created successfully
                setFlashMessage('success', 'Client créé avec succès');
                redirect('clients');
            } else {
                // Client creation failed
                setFlashMessage('danger', 'Erreur lors de la création du client');
                $_SESSION['form_data'] = [
                    'NomClient' => $nomClient,
                    'AdresseClient' => $adresseClient,
                    'EmailClient' => $emailClient,
                    'TelClient' => $telClient
                ];
                redirect('clients/create');
            }
        }
        
        // Display client creation form
        require_once(APP_ROOT . '/views/clients/create.php');
    }
    
    /**
     * Display client edit form
     * 
     * @param int $id Client ID
     * @return void
     */
    public function edit($id) {
        // Get client
        $this->client->IdClient = $id;
        $exists = $this->client->readOne();
        
        if (!$exists) {
            setFlashMessage('danger', 'Client non trouvé');
            redirect('clients');
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('clients/edit/' . $id);
            }
            
            // Get form data
            $nomClient = isset($_POST['NomClient']) ? trim($_POST['NomClient']) : '';
            $adresseClient = isset($_POST['AdresseClient']) ? trim($_POST['AdresseClient']) : '';
            $emailClient = isset($_POST['EmailClient']) ? trim($_POST['EmailClient']) : '';
            $telClient = isset($_POST['TelClient']) ? trim($_POST['TelClient']) : '';
            
            // Validate form data
            $validator = new Validator([
                'NomClient' => $nomClient,
                'AdresseClient' => $adresseClient,
                'EmailClient' => $emailClient,
                'TelClient' => $telClient
            ]);
            
            $validator->required('NomClient', 'Nom du client est obligatoire')
                      ->maxLength('NomClient', 100, 'Nom du client ne doit pas dépasser 100 caractères')
                      ->required('AdresseClient', 'Adresse du client est obligatoire')
                      ->maxLength('AdresseClient', 255, 'Adresse du client ne doit pas dépasser 255 caractères')
                      ->required('EmailClient', 'Email du client est obligatoire')
                      ->email('EmailClient', 'Email du client est invalide')
                      ->maxLength('EmailClient', 100, 'Email du client ne doit pas dépasser 100 caractères')
                      ->required('TelClient', 'Téléphone du client est obligatoire')
                      ->phone('TelClient', 'Téléphone du client est invalide')
                      ->maxLength('TelClient', 20, 'Téléphone du client ne doit pas dépasser 20 caractères');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'NomClient' => $nomClient,
                    'AdresseClient' => $adresseClient,
                    'EmailClient' => $emailClient,
                    'TelClient' => $telClient
                ];
                redirect('clients/edit/' . $id);
            }
            
            // Set client properties
            $this->client->NomClient = $nomClient;
            $this->client->AdresseClient = $adresseClient;
            $this->client->EmailClient = $emailClient;
            $this->client->TelClient = $telClient;
            
            // Update client
            if ($this->client->update()) {
                // Client updated successfully
                setFlashMessage('success', 'Client mis à jour avec succès');
                redirect('clients');
            } else {
                // Client update failed
                setFlashMessage('danger', 'Erreur lors de la mise à jour du client');
                redirect('clients/edit/' . $id);
            }
        }
        
        // Display client edit form
        require_once(APP_ROOT . '/views/clients/edit.php');
    }
    
    /**
     * Display client details
     * 
     * @param int $id Client ID
     * @return void
     */
    public function view($id) {
        // Get client
        $this->client->IdClient = $id;
        $exists = $this->client->readOne();
        
        if (!$exists) {
            setFlashMessage('danger', 'Client non trouvé');
            redirect('clients');
        }
        
        // Get client projects
        $projectsStmt = $this->client->getProjects();
        
        // Get client payments
        $paymentsStmt = $this->client->getPayments();
        
        // Get client payment statistics
        $totalPayments = $this->client->getTotalPayments();
        $totalProjectsCost = $this->client->getTotalProjectsCost();
        $balance = $totalProjectsCost - $totalPayments;
        
        // Display client details
        require_once(APP_ROOT . '/views/clients/view.php');
    }
    
    /**
     * Delete client
     * 
     * @param int $id Client ID
     * @return void
     */
    public function delete($id) {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('danger', 'Invalid CSRF token');
            redirect('clients');
        }
        
        // Set client ID
        $this->client->IdClient = $id;
        
        // Check if client exists
        if (!$this->client->readOne()) {
            setFlashMessage('danger', 'Client non trouvé');
            redirect('clients');
        }
        
        // Get client projects
        $projectsStmt = $this->client->getProjects();
        
        // Check if client has projects
        if ($projectsStmt->rowCount() > 0) {
            setFlashMessage('danger', 'Impossible de supprimer ce client car il a des projets associés');
            redirect('clients');
        }
        
        // Delete client
        if ($this->client->delete()) {
            setFlashMessage('success', 'Client supprimé avec succès');
        } else {
            setFlashMessage('danger', 'Erreur lors de la suppression du client');
        }
        
        redirect('clients');
    }
    
    /**
     * Export client list to CSV
     * 
     * @return void
     */
    public function exportCSV() {
        // Get all clients
        $stmt = $this->client->read(1, 1000); // Limit to 1000 records
        
        // Prepare data for export
        $clients = [];
        $headers = ['ID', 'Nom', 'Adresse', 'Email', 'Téléphone'];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clients[] = [
                $row['IdClient'],
                $row['NomClient'],
                $row['AdresseClient'],
                $row['EmailClient'],
                $row['TelClient']
            ];
        }
        
        // Export to CSV
        Export::toCSV($clients, $headers, 'clients');
    }
    
    /**
     * Export client list to PDF
     * 
     * @return void
     */
    public function exportPDF() {
        // Get all clients
        $stmt = $this->client->read(1, 1000); // Limit to 1000 records
        
        // Prepare data for export
        $clients = [];
        $headers = ['ID', 'Nom', 'Email', 'Téléphone'];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clients[] = [
                $row['IdClient'],
                $row['NomClient'],
                $row['EmailClient'],
                $row['TelClient']
            ];
        }
        
        // Export to PDF
        Export::toPDF($clients, $headers, 'Liste des Clients', 'clients');
    }
    
    /**
     * Export client details to PDF
     * 
     * @param int $id Client ID
     * @return void
     */
    public function exportDetailsPDF($id) {
        // Get client
        $this->client->IdClient = $id;
        $exists = $this->client->readOne();
        
        if (!$exists) {
            setFlashMessage('danger', 'Client non trouvé');
            redirect('clients');
        }
        
        // Get client projects
        $projectsStmt = $this->client->getProjects();
        $projects = [];
        
        while ($row = $projectsStmt->fetch(PDO::FETCH_ASSOC)) {
            $projects[] = $row;
        }
        
        // Get client payments
        $paymentsStmt = $this->client->getPayments();
        $payments = [];
        
        while ($row = $paymentsStmt->fetch(PDO::FETCH_ASSOC)) {
            $payments[] = $row;
        }
        
        // Export to PDF
        Export::clientInfoPDF([
            'IdClient' => $this->client->IdClient,
            'NomClient' => $this->client->NomClient,
            'AdresseClient' => $this->client->AdresseClient,
            'EmailClient' => $this->client->EmailClient,
            'TelClient' => $this->client->TelClient
        ], $projects, $payments, 'client_' . $id);
    }
}
?>
