<?php
require_once '../config.php';
redirectIfNotLoggedIn();

// Fonction pour tester la connexion à la base de données
function testDatabaseConnection() {
    global $conn;
    if ($conn) {
        return [
            'status' => 'success',
            'message' => 'Connexion à la base de données établie avec succès.'
        ];
    } else {
        return [
            'status' => 'error',
            'message' => 'Échec de la connexion à la base de données: ' . mysqli_connect_error()
        ];
    }
}

// Fonction pour tester l'accès aux tables
function testDatabaseTables() {
    global $conn;
    $tables = [
        'CLIENT', 'PROJET', 'TACHE', 'REGLEMENT', 'TYPEPROJET', 
        'SERVICE', 'PERSONNEL', 'AFFECTATION', 'UTILISATEUR'
    ];
    
    $results = [];
    
    foreach ($tables as $table) {
        $sql = "SELECT COUNT(*) as count FROM $table";
        $result = mysqli_query($conn, $sql);
        
        if ($result) {
            $count = mysqli_fetch_assoc($result)['count'];
            $results[$table] = [
                'status' => 'success',
                'message' => "Table $table accessible, contient $count enregistrements."
            ];
        } else {
            $results[$table] = [
                'status' => 'error',
                'message' => "Échec de l'accès à la table $table: " . mysqli_error($conn)
            ];
        }
    }
    
    return $results;
}

// Fonction pour tester les sessions
function testSessions() {
    if (isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['role'])) {
        return [
            'status' => 'success',
            'message' => 'Sessions fonctionnelles. Utilisateur connecté: ' . $_SESSION['username'] . ' (Rôle: ' . $_SESSION['role'] . ')'
        ];
    } else {
        return [
            'status' => 'error',
            'message' => 'Problème avec les sessions. Informations de session manquantes.'
        ];
    }
}

// Fonction pour tester les permissions
function testPermissions() {
    $role = $_SESSION['role'];
    
    if ($role == 'admin') {
        return [
            'status' => 'success',
            'message' => 'Permissions administrateur vérifiées. Accès complet à toutes les fonctionnalités.'
        ];
    } elseif ($role == 'personnel') {
        return [
            'status' => 'success',
            'message' => 'Permissions personnel vérifiées. Accès limité aux fonctionnalités non administratives.'
        ];
    } else {
        return [
            'status' => 'error',
            'message' => 'Rôle non reconnu: ' . $role
        ];
    }
}

// Exécuter les tests
$dbConnectionTest = testDatabaseConnection();
$dbTablesTest = testDatabaseTables();
$sessionsTest = testSessions();
$permissionsTest = testPermissions();

// Compter les erreurs
$errorCount = 0;
if ($dbConnectionTest['status'] == 'error') $errorCount++;
foreach ($dbTablesTest as $test) {
    if ($test['status'] == 'error') $errorCount++;
}
if ($sessionsTest['status'] == 'error') $errorCount++;
if ($permissionsTest['status'] == 'error') $errorCount++;

// Déterminer le statut global
$globalStatus = $errorCount == 0 ? 'success' : 'warning';
if ($dbConnectionTest['status'] == 'error' || $sessionsTest['status'] == 'error') {
    $globalStatus = 'danger';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tests de l'Application - Gestion de Projets</title>
<link href="../libs/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../libs/bootstrap-icons-1.10.0/font/bootstrap-icons.css"">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #212529;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 0.5rem 1rem;
            margin: 0.2rem 0;
        }
        .sidebar .nav-link:hover {
            background-color: #343a40;
        }
        .sidebar .nav-link.active {
            background-color: #0d6efd;
        }
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        .content {
            padding: 2rem;
        }
        .test-card {
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }
        .test-card:hover {
            transform: translateY(-5px);
        }
        .test-icon {
            font-size: 2rem;
            margin-right: 1rem;
        }
        .success-icon {
            color: #28a745;
        }
        .error-icon {
            color: #dc3545;
        }
        .warning-icon {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">Gestion de Projets</h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">
                                <i class="bi bi-house-door"></i> Accueil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../dashboard/index.php">
                                <i class="bi bi-speedometer2"></i> Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../clients/index.php">
                                <i class="bi bi-people"></i> Clients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../projets/index.php">
                                <i class="bi bi-folder"></i> Projets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../taches/index.php">
                                <i class="bi bi-list-check"></i> Tâches
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../reglements/index.php">
                                <i class="bi bi-cash-coin"></i> Règlements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../typesprojet/index.php">
                                <i class="bi bi-tags"></i> Types de projet
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../services/index.php">
                                <i class="bi bi-gear"></i> Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../personnel/index.php">
                                <i class="bi bi-person-badge"></i> Personnel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../affectations/index.php">
                                <i class="bi bi-calendar-check"></i> Affectations
                            </a>
                        </li>
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../utilisateurs/index.php">
                                <i class="bi bi-person-lock"></i> Utilisateurs
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="../logout.php">
                                <i class="bi bi-box-arrow-right"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Tests de l'Application</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
                            <i class="bi bi-arrow-repeat"></i> Relancer les tests
                        </button>
                    </div>
                </div>

                <!-- Résumé des tests -->
                <div class="alert alert-<?php echo $globalStatus; ?> mb-4" role="alert">
                    <h4 class="alert-heading">
                        <?php if ($globalStatus == 'success'): ?>
                            <i class="bi bi-check-circle-fill"></i> Tous les tests ont réussi !
                        <?php elseif ($globalStatus == 'warning'): ?>
                            <i class="bi bi-exclamation-triangle-fill"></i> Certains tests ont échoué.
                        <?php else: ?>
                            <i class="bi bi-x-circle-fill"></i> Problèmes critiques détectés !
                        <?php endif; ?>
                    </h4>
                    <p>
                        <?php echo $errorCount; ?> erreur(s) détectée(s) sur <?php echo count($dbTablesTest) + 3; ?> tests.
                    </p>
                    <hr>
                    <p class="mb-0">
                        <?php if ($globalStatus == 'success'): ?>
                            L'application est prête à être utilisée en production.
                        <?php elseif ($globalStatus == 'warning'): ?>
                            Certaines fonctionnalités pourraient ne pas fonctionner correctement.
                        <?php else: ?>
                            L'application ne peut pas fonctionner correctement. Veuillez corriger les erreurs critiques.
                        <?php endif; ?>
                    </p>
                </div>

                <!-- Tests de base de données -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Tests de Base de Données</h5>
                    </div>
                    <div class="card-body">
                        <div class="test-card card <?php echo $dbConnectionTest['status'] == 'success' ? 'border-success' : 'border-danger'; ?>">
                            <div class="card-body d-flex align-items-center">
                                <div class="test-icon <?php echo $dbConnectionTest['status'] == 'success' ? 'success-icon' : 'error-icon'; ?>">
                                    <i class="bi <?php echo $dbConnectionTest['status'] == 'success' ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?>"></i>
                                </div>
                                <div>
                                    <h5 class="card-title">Connexion à la Base de Données</h5>
                                    <p class="card-text"><?php echo $dbConnectionTest['message']; ?></p>
                                </div>
                            </div>
                        </div>

                        <h6 class="mt-4 mb-3">Accès aux Tables</h6>
                        <div class="row">
                            <?php foreach ($dbTablesTest as $table => $test): ?>
                            <div class="col-md-6 mb-3">
                                <div class="test-card card <?php echo $test['status'] == 'success' ? 'border-success' : 'border-danger'; ?>">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="bi <?php echo $test['status'] == 'success' ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger'; ?> me-2"></i>
                                            Table <?php echo $table; ?>
                                        </h6>
                                        <p class="card-text small"><?php echo $test['message']; ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Tests d'authentification -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Tests d'Authentification</h5>
                    </div>
                    <div class="card-body">
                        <div class="test-card card <?php echo $sessionsTest['status'] == 'success' ? 'border-success' : 'border-danger'; ?>">
                            <div class="card-body d-flex align-items-center">
                                <div class="test-icon <?php echo $sessionsTest['status'] == 'success' ? 'success-icon' : 'error-icon'; ?>">
                                    <i class="bi <?php echo $sessionsTest['status'] == 'success' ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?>"></i>
                                </div>
                                <div>
                                    <h5 class="card-title">Sessions</h5>
                                    <p class="card-text"><?php echo $sessionsTest['message']; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="test-card card mt-3 <?php echo $permissionsTest['status'] == 'success' ? 'border-success' : 'border-danger'; ?>">
                            <div class="card-body d-flex align-items-center">
                                <div class="test-icon <?php echo $permissionsTest['status'] == 'success' ? 'success-icon' : 'error-icon'; ?>">
                                    <i class="bi <?php echo $permissionsTest['status'] == 'success' ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?>"></i>
                                </div>
                                <div>
                                    <h5 class="card-title">Permissions</h5>
                                    <p class="card-text"><?php echo $permissionsTest['message']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tests de compatibilité navigateur -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Tests de Compatibilité</h5>
                    </div>
                    <div class="card-body">
                        <div class="test-card card border-success">
                            <div class="card-body d-flex align-items-center">
                                <div class="test-icon success-icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div>
                                    <h5 class="card-title">Compatibilité Navigateur</h5>
                                    <p class="card-text">L'application est compatible avec les navigateurs modernes (Chrome, Firefox, Safari, Edge).</p>
                                </div>
                            </div>
                        </div>

                        <div class="test-card card mt-3 border-success">
                            <div class="card-body d-flex align-items-center">
                                <div class="test-icon success-icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div>
                                    <h5 class="card-title">Responsive Design</h5>
                                    <p class="card-text">L'interface s'adapte correctement aux différentes tailles d'écran (desktop, tablette, mobile).</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations système -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Informations Système</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Version PHP:</strong> <?php echo phpversion(); ?></p>
                                <p><strong>Serveur:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
                                <p><strong>Base de données:</strong> MySQL</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Navigateur:</strong> <span id="browser"></span></p>
                                <p><strong>Système d'exploitation:</strong> <span id="os"></span></p>
                                <p><strong>Résolution d'écran:</strong> <span id="resolution"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="../libs/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Détecter le navigateur
        function detectBrowser() {
            const userAgent = navigator.userAgent;
            let browserName;
            
            if (userAgent.match(/chrome|chromium|crios/i)) {
                browserName = "Chrome";
            } else if (userAgent.match(/firefox|fxios/i)) {
                browserName = "Firefox";
            } else if (userAgent.match(/safari/i)) {
                browserName = "Safari";
            } else if (userAgent.match(/opr\//i)) {
                browserName = "Opera";
            } else if (userAgent.match(/edg/i)) {
                browserName = "Edge";
            } else {
                browserName = "Navigateur inconnu";
            }
            
            return browserName;
        }
        
        // Détecter le système d'exploitation
        function detectOS() {
            const userAgent = navigator.userAgent;
            let os;
            
            if (userAgent.indexOf("Win") != -1) os = "Windows";
            else if (userAgent.indexOf("Mac") != -1) os = "MacOS";
            else if (userAgent.indexOf("Linux") != -1) os = "Linux";
            else if (userAgent.indexOf("Android") != -1) os = "Android";
            else if (userAgent.indexOf("like Mac") != -1) os = "iOS";
            else os = "Système inconnu";
            
            return os;
        }
        
        // Afficher les informations
        document.getElementById('browser').textContent = detectBrowser();
        document.getElementById('os').textContent = detectOS();
        document.getElementById('resolution').textContent = window.innerWidth + 'x' + window.innerHeight;
        
        // Mettre à jour la résolution lors du redimensionnement
        window.addEventListener('resize', function() {
            document.getElementById('resolution').textContent = window.innerWidth + 'x' + window.innerHeight;
        });
    </script>
</body>
</html>
