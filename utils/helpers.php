<?php
/**
 * Helper Functions
 * 
 * Contains application-wide helper functions
 */

/**
 * Redirect to a specific page
 * 
 * @param string $page Target page
 * @return void
 */
function redirect($page) {
    header('Location: ' . URL_ROOT . '/' . $page);
    exit;
}

/**
 * Sanitize input data
 * 
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Format date to readable format
 * 
 * @param string $date Date to format
 * @param string $format Format to use (default: d/m/Y)
 * @return string Formatted date
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date) || $date == '0000-00-00') {
        return '';
    }
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

/**
 * Format number as currency
 * 
 * @param float $amount Amount to format
 * @return string Formatted amount
 */
function formatMoney($amount) {
    return number_format($amount, 2, ',', ' ') . ' €';
}

/**
 * Check if user is logged in
 * 
 * @return boolean True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION[SESSION_AUTH]) && $_SESSION[SESSION_AUTH] === true;
}

/**
 * Get current user data
 * 
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    return isset($_SESSION[SESSION_USER]) ? $_SESSION[SESSION_USER] : null;
}

/**
 * Check if user has specific role
 * 
 * @param string $role Role to check
 * @return boolean True if user has role, false otherwise
 */
function hasRole($role) {
    $user = getCurrentUser();
    return isset($user) && isset($user['role']) && $user['role'] === $role;
}

/**
 * Check if user is admin
 * 
 * @return boolean True if user is admin, false otherwise
 */
function isAdmin() {
    return hasRole(ROLE_ADMIN);
}

/**
 * Generate CSRF token
 * 
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return boolean True if token is valid, false otherwise
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Convert project status code to text
 * 
 * @param int $status Status code
 * @return string Status text
 */
function getProjectStatusText($status) {
    switch ($status) {
        case PROJECT_STATUS_PENDING:
            return 'En attente';
        case PROJECT_STATUS_IN_PROGRESS:
            return 'En cours';
        case PROJECT_STATUS_COMPLETED:
            return 'Terminé';
        case PROJECT_STATUS_CANCELLED:
            return 'Annulé';
        default:
            return 'Inconnu';
    }
}

/**
 * Convert task status code to text
 * 
 * @param int $status Status code
 * @return string Status text
 */
function getTaskStatusText($status) {
    switch ($status) {
        case TASK_STATUS_PENDING:
            return 'En attente';
        case TASK_STATUS_IN_PROGRESS:
            return 'En cours';
        case TASK_STATUS_COMPLETED:
            return 'Terminée';
        case TASK_STATUS_CANCELLED:
            return 'Annulée';
        default:
            return 'Inconnu';
    }
}

/**
 * Get project status CSS class
 * 
 * @param int $status Status code
 * @return string CSS class
 */
function getProjectStatusClass($status) {
    switch ($status) {
        case PROJECT_STATUS_PENDING:
            return 'badge bg-warning';
        case PROJECT_STATUS_IN_PROGRESS:
            return 'badge bg-info';
        case PROJECT_STATUS_COMPLETED:
            return 'badge bg-success';
        case PROJECT_STATUS_CANCELLED:
            return 'badge bg-danger';
        default:
            return 'badge bg-secondary';
    }
}

/**
 * Get task status CSS class
 * 
 * @param int $status Status code
 * @return string CSS class
 */
function getTaskStatusClass($status) {
    switch ($status) {
        case TASK_STATUS_PENDING:
            return 'badge bg-warning';
        case TASK_STATUS_IN_PROGRESS:
            return 'badge bg-info';
        case TASK_STATUS_COMPLETED:
            return 'badge bg-success';
        case TASK_STATUS_CANCELLED:
            return 'badge bg-danger';
        default:
            return 'badge bg-secondary';
    }
}

/**
 * Get current page name
 * 
 * @return string Current page name
 */
function getCurrentPage() {
    $uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($uri, PHP_URL_PATH);
    $parts = explode('/', trim($path, '/'));
    return end($parts);
}

/**
 * Format a phone number
 * 
 * @param string $phone Phone number to format
 * @return string Formatted phone number
 */
function formatPhoneNumber($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if(strlen($phone) === 10) {
        return substr($phone, 0, 2) . ' ' . substr($phone, 2, 2) . ' ' . 
               substr($phone, 4, 2) . ' ' . substr($phone, 6, 2) . ' ' . 
               substr($phone, 8, 2);
    }
    return $phone;
}

/**
 * Add flash message
 * 
 * @param string $type Message type (success, danger, warning, info)
 * @param string $message Message text
 * @return void
 */
function setFlashMessage($type, $message) {
    if(!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get flash messages and clear them
 * 
 * @return array Flash messages
 */
function getFlashMessages() {
    $messages = isset($_SESSION['flash_messages']) ? $_SESSION['flash_messages'] : [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Generate pagination links
 * 
 * @param int $currentPage Current page number
 * @param int $totalPages Total number of pages
 * @param string $baseUrl Base URL for links
 * @return string HTML pagination links
 */
function getPaginationLinks($currentPage, $totalPages, $baseUrl) {
    $links = '<ul class="pagination">';
    
    // Previous button
    if ($currentPage > 1) {
        $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . ($currentPage - 1) . '">Précédent</a></li>';
    } else {
        $links .= '<li class="page-item disabled"><a class="page-link" href="#">Précédent</a></li>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '1">1</a></li>';
        if ($start > 2) {
            $links .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
    }
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $links .= '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
        } else {
            $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . $i . '">' . $i . '</a></li>';
        }
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $links .= '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
        }
        $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . $totalPages . '">' . $totalPages . '</a></li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . ($currentPage + 1) . '">Suivant</a></li>';
    } else {
        $links .= '<li class="page-item disabled"><a class="page-link" href="#">Suivant</a></li>';
    }
    
    $links .= '</ul>';
    return $links;
}
?>
