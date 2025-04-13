<?php
/**
 * Payment Controller
 * 
 * Handles payment operations
 */
class PaymentController {
    // Database connection and models
    private $db;
    private $payment;
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
        
        // Initialize models
        $this->payment = new Payment($this->db);
        $this->client = new Client($this->db);
    }
    
    /**
     * Display list of payments
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
        $clientId = isset($_GET['client']) ? $_GET['client'] : '';
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
        
        // Get payments based on search/filters
        if (!empty($search)) {
            $stmt = $this->payment->search($search);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } elseif (!empty($clientId)) {
            $stmt = $this->payment->filterByClient($clientId);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } elseif (!empty($startDate) && !empty($endDate)) {
            $stmt = $this->payment->filterByDateRange($startDate, $endDate);
            $totalRecords = $stmt->rowCount();
            $totalPages = 1;
        } else {
            $totalRecords = $this->payment->count();
            $totalPages = ceil($totalRecords / $perPage);
            $page = $page > $totalPages ? $totalPages : $page;
            $page = $page < 1 ? 1 : $page;
            $stmt = $this->payment->read($page, $perPage);
        }
        
        // Get clients for dropdown
        $clientsStmt = $this->client->getClientsDropdown();
        
        // Get total payments
        $totalPayments = $this->payment->getTotalPayments();
        
        // Display view
        require_once(APP_ROOT . '/views/payments/index.php');
    }
    
    /**
     * Display payment creation form
     * 
     * @return void
     */
    public function create() {
        // Get clients for dropdown
        $clientsStmt = $this->client->getClientsDropdown();
        
        // Get client ID from query string (if coming from client view)
        $clientId = isset($_GET['client']) ? (int)$_GET['client'] : 0;
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('payments/create');
            }
            
            // Get form data
            $dateReglement = isset($_POST['DateReglement']) ? trim($_POST['DateReglement']) : '';
            $heureReglement = isset($_POST['HeureReglement']) ? trim($_POST['HeureReglement']) : '';
            $montantReglement = isset($_POST['MontantReglement']) ? trim($_POST['MontantReglement']) : '';
            $idClient = isset($_POST['IdClient']) ? (int)$_POST['IdClient'] : 0;
            
            // Validate form data
            $validator = new Validator([
                'DateReglement' => $dateReglement,
                'HeureReglement' => $heureReglement,
                'MontantReglement' => $montantReglement,
                'IdClient' => $idClient
            ]);
            
            $validator->required('DateReglement', 'Date du règlement est obligatoire')
                      ->date('DateReglement', 'Y-m-d', 'Date du règlement est invalide')
                      ->required('HeureReglement', 'Heure du règlement est obligatoire')
                      ->time('HeureReglement', 'H:i', 'Heure du règlement est invalide')
                      ->required('MontantReglement', 'Montant du règlement est obligatoire')
                      ->numeric('MontantReglement', 'Montant du règlement doit être un nombre')
                      ->required('IdClient', 'Client est obligatoire');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'DateReglement' => $dateReglement,
                    'HeureReglement' => $heureReglement,
                    'MontantReglement' => $montantReglement,
                    'IdClient' => $idClient
                ];
                redirect('payments/create');
            }
            
            // Set payment properties
            $this->payment->DateReglement = $dateReglement;
            $this->payment->HeureReglement = $heureReglement;
            $this->payment->MontantReglement = $montantReglement;
            $this->payment->IdClient = $idClient;
            
            // Create payment
            if ($this->payment->create()) {
                // Payment created successfully
                setFlashMessage('success', 'Règlement créé avec succès');
                
                // Redirect based on context
                if (isset($_POST['redirect_to_client']) && $_POST['redirect_to_client'] == '1') {
                    redirect('clients/view/' . $idClient);
                } else {
                    redirect('payments');
                }
            } else {
                // Payment creation failed
                setFlashMessage('danger', 'Erreur lors de la création du règlement');
                $_SESSION['form_data'] = [
                    'DateReglement' => $dateReglement,
                    'HeureReglement' => $heureReglement,
                    'MontantReglement' => $montantReglement,
                    'IdClient' => $idClient
                ];
                redirect('payments/create');
            }
        }
        
        // Display payment creation form
        require_once(APP_ROOT . '/views/payments/create.php');
    }
    
    /**
     * Display payment edit form
     * 
     * @param int $id Payment ID
     * @return void
     */
    public function edit($id) {
        // Get payment
        $this->payment->IdReglement = $id;
        $exists = $this->payment->readOne();
        
        if (!$exists) {
            setFlashMessage('danger', 'Règlement non trouvé');
            redirect('payments');
        }
        
        // Get clients for dropdown
        $clientsStmt = $this->client->getClientsDropdown();
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF token
            if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
                setFlashMessage('danger', 'Invalid CSRF token');
                redirect('payments/edit/' . $id);
            }
            
            // Get form data
            $dateReglement = isset($_POST['DateReglement']) ? trim($_POST['DateReglement']) : '';
            $heureReglement = isset($_POST['HeureReglement']) ? trim($_POST['HeureReglement']) : '';
            $montantReglement = isset($_POST['MontantReglement']) ? trim($_POST['MontantReglement']) : '';
            $idClient = isset($_POST['IdClient']) ? (int)$_POST['IdClient'] : 0;
            
            // Validate form data
            $validator = new Validator([
                'DateReglement' => $dateReglement,
                'HeureReglement' => $heureReglement,
                'MontantReglement' => $montantReglement,
                'IdClient' => $idClient
            ]);
            
            $validator->required('DateReglement', 'Date du règlement est obligatoire')
                      ->date('DateReglement', 'Y-m-d', 'Date du règlement est invalide')
                      ->required('HeureReglement', 'Heure du règlement est obligatoire')
                      ->time('HeureReglement', 'H:i', 'Heure du règlement est invalide')
                      ->required('MontantReglement', 'Montant du règlement est obligatoire')
                      ->numeric('MontantReglement', 'Montant du règlement doit être un nombre')
                      ->required('IdClient', 'Client est obligatoire');
            
            // Check for validation errors
            if ($validator->hasErrors()) {
                $_SESSION['form_errors'] = $validator->getErrors();
                $_SESSION['form_data'] = [
                    'DateReglement' => $dateReglement,
                    'HeureReglement' => $heureReglement,
                    'MontantReglement' => $montantReglement,
                    'IdClient' => $idClient
                ];
                redirect('payments/edit/' . $id);
            }
            
            // Store original client ID for possible redirection
            $originalClientId = $this->payment->IdClient;
            
            // Set payment properties
            $this->payment->DateReglement = $dateReglement;
            $this->payment->HeureReglement = $heureReglement;
            $this->payment->MontantReglement = $montantReglement;
            $this->payment->IdClient = $idClient;
            
            // Update payment
            if ($this->payment->update()) {
                // Payment updated successfully
                setFlashMessage('success', 'Règlement mis à jour avec succès');
                
                // Redirect based on context
                if (isset($_POST['redirect_to_client']) && $_POST['redirect_to_client'] == '1') {
                    redirect('clients/view/' . $originalClientId);
                } else {
                    redirect('payments');
                }
            } else {
                // Payment update failed
                setFlashMessage('danger', 'Erreur lors de la mise à jour du règlement');
                redirect('payments/edit/' . $id);
            }
        }
        
        // Display payment edit form
        require_once(APP_ROOT . '/views/payments/edit.php');
    }
    
    /**
     * Display payment details
     * 
     * @param int $id Payment ID
     * @return void
     */
    public function view($id) {
        // Get payment
        $this->payment->IdReglement = $id;
        $exists = $this->payment->readOne();
        
        if (!$exists) {
            setFlashMessage('danger', 'Règlement non trouvé');
            redirect('payments');
        }
        
        // Display payment details
        require_once(APP_ROOT . '/views/payments/view.php');
    }
    
    /**
     * Delete payment
     * 
     * @param int $id Payment ID
     * @return void
     */
    public function delete($id) {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
            setFlashMessage('danger', 'Invalid CSRF token');
            redirect('payments');
        }
        
        // Set payment ID
        $this->payment->IdReglement = $id;
        
        // Check if payment exists
        $exists = $this->payment->readOne();
        if (!$exists) {
            setFlashMessage('danger', 'Règlement non trouvé');
            redirect('payments');
        }
        
        // Store client ID for possible redirection
        $clientId = $this->payment->IdClient;
        $redirectToClient = isset($_POST['redirect_to_client']) && $_POST['redirect_to_client'] == '1';
        
        // Delete payment
        if ($this->payment->delete()) {
            setFlashMessage('success', 'Règlement supprimé avec succès');
            
            // Redirect to client view if requested
            if ($redirectToClient) {
                redirect('clients/view/' . $clientId);
            }
        } else {
            setFlashMessage('danger', 'Erreur lors de la suppression du règlement');
        }
        
        // Redirect to payments list or client view
        if ($redirectToClient) {
            redirect('clients/view/' . $clientId);
        } else {
            redirect('payments');
        }
    }
    
    /**
     * Export payments to CSV
     * 
     * @return void
     */
    public function exportCSV() {
        // Get all payments
        $stmt = $this->payment->read(1, 1000); // Limit to 1000 records
        
        // Prepare data for export
        $payments = [];
        $headers = ['ID', 'Date', 'Heure', 'Montant', 'Client'];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $payments[] = [
                $row['IdReglement'],
                formatDate($row['DateReglement']),
                $row['HeureReglement'],
                formatMoney($row['MontantReglement']),
                $row['NomClient']
            ];
        }
        
        // Export to CSV
        Export::toCSV($payments, $headers, 'reglements');
    }
    
    /**
     * Export payments to PDF
     * 
     * @return void
     */
    public function exportPDF() {
        // Get all payments
        $stmt = $this->payment->read(1, 1000); // Limit to 1000 records
        
        // Prepare data for export
        $payments = [];
        $headers = ['ID', 'Date', 'Heure', 'Montant', 'Client'];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $payments[] = [
                $row['IdReglement'],
                formatDate($row['DateReglement']),
                $row['HeureReglement'],
                formatMoney($row['MontantReglement']),
                $row['NomClient']
            ];
        }
        
        // Export to PDF
        Export::toPDF($payments, $headers, 'Liste des Règlements', 'reglements');
    }
    
    /**
     * Display payment balance report
     * 
     * @return void
     */
    public function balance() {
        // Get payment balance by client
        $balanceStmt = $this->payment->getPaymentBalanceByClient();
        
        // Display payment balance view
        require_once(APP_ROOT . '/views/payments/balance.php');
    }
    
    /**
     * Export payment balance to CSV
     * 
     * @return void
     */
    public function exportBalanceCSV() {
        // Get payment balance by client
        $balanceStmt = $this->payment->getPaymentBalanceByClient();
        
        // Prepare data for export
        $balance = [];
        $headers = ['ID Client', 'Nom Client', 'Montant Total Projets', 'Montant Total Payé', 'Solde'];
        
        while ($row = $balanceStmt->fetch(PDO::FETCH_ASSOC)) {
            $totalCost = $row['totalCost'] ? $row['totalCost'] : 0;
            $totalPaid = $row['totalPaid'] ? $row['totalPaid'] : 0;
            $balance[] = [
                $row['IdClient'],
                $row['NomClient'],
                $totalCost,
                $totalPaid,
                $totalCost - $totalPaid
            ];
        }
        
        // Export to CSV
        Export::toCSV($balance, $headers, 'balance_clients');
    }
    
    /**
     * Export payment balance to PDF
     * 
     * @return void
     */
    public function exportBalancePDF() {
        // Get payment balance by client
        $balanceStmt = $this->payment->getPaymentBalanceByClient();
        
        // Prepare data for export
        $balance = [];
        $headers = ['ID', 'Client', 'Total Projets', 'Total Payé', 'Solde'];
        
        while ($row = $balanceStmt->fetch(PDO::FETCH_ASSOC)) {
            $totalCost = $row['totalCost'] ? $row['totalCost'] : 0;
            $totalPaid = $row['totalPaid'] ? $row['totalPaid'] : 0;
            $balance[] = [
                $row['IdClient'],
                $row['NomClient'],
                formatMoney($totalCost),
                formatMoney($totalPaid),
                formatMoney($totalCost - $totalPaid)
            ];
        }
        
        // Export to PDF
        Export::toPDF($balance, $headers, 'Balance des Paiements par Client', 'balance_clients');
    }
}
?>
