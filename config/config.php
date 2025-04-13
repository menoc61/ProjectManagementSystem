<?php
/**
 * Application Configuration
 * 
 * Contains application-wide settings and constants
 */

// App Root
define('APP_ROOT', dirname(dirname(__FILE__)));

// URL Root (update for production)
define('URL_ROOT', 'http://localhost/gestion-projets');

// Site Name
define('SITE_NAME', 'Gestion de Projets Entreprise');

// App Version
define('APP_VERSION', '1.0.0');

// Session constants
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds
define('SESSION_USER', 'user_data');
define('SESSION_AUTH', 'authenticated');

// Database table names
define('TABLE_USERS', 'users');
define('TABLE_CLIENTS', 'CLIENT');
define('TABLE_PROJECT_TYPES', 'TYPEPROJET');
define('TABLE_PROJECTS', 'PROJET');
define('TABLE_TASKS', 'TACHE');
define('TABLE_SERVICES', 'SERVICE');
define('TABLE_PERSONNEL', 'PERSONNEL');
define('TABLE_ASSIGNMENTS', 'AFFECTATION');
define('TABLE_PAYMENTS', 'REGLEMENT');

// Project status constants
define('PROJECT_STATUS_PENDING', 0);
define('PROJECT_STATUS_IN_PROGRESS', 1);
define('PROJECT_STATUS_COMPLETED', 2);
define('PROJECT_STATUS_CANCELLED', 3);

// Task status constants
define('TASK_STATUS_PENDING', 0);
define('TASK_STATUS_IN_PROGRESS', 1);
define('TASK_STATUS_COMPLETED', 2);
define('TASK_STATUS_CANCELLED', 3);

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_MANAGER', 'manager');
define('ROLE_USER', 'user');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Load helpers
require_once APP_ROOT . '/utils/helpers.php';
?>
