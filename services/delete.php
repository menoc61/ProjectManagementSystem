<?php
require_once '../config.php';
redirectIfNotLoggedIn();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Vérifier si le service existe
$checkSql = "SELECT * FROM SERVICE WHERE IdService = $id";
$checkResult = mysqli_query($conn, $checkSql);

if (mysqli_num_rows($checkResult) == 0) {
    header("Location: index.php");
    exit();
}

// Vérifier si le service a du personnel associé
$personnelSql = "SELECT COUNT(*) as count FROM PERSONNEL WHERE IdService = $id";
$personnelResult = mysqli_query($conn, $personnelSql);
$personnelCount = mysqli_fetch_assoc($personnelResult)['count'];

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm'])) {
    if ($personnelCount > 0) {
        $error = "Impossible de supprimer ce service car il a du personnel associé.";
    } else {
        $sql = "DELETE FROM SERVICE WHERE IdService = $id";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: index.php?deleted=1");
            exit();
        } else {
            $error = "Erreur lors de la suppression du service: " . mysqli_error($conn);
        }
    }
}

$service = mysqli_fetch_assoc($checkResult);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer un Service - Gestion de Projets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
                            <a class="nav-link active" href="../services/index.php">
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
                    <h1 class="h2">Supprimer un Service</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="index.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>

                <?php if($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Confirmation de suppression</h5>
                        <p class="card-text">
                            Êtes-vous sûr de vouloir supprimer le service suivant ?
                        </p>
                        
                        <div class="mb-4">
                            <p><strong>ID:</strong> <?php echo $service['IdService']; ?></p>
                            <p><strong>Libellé:</strong> <?php echo htmlspecialchars($service['LibelleService']); ?></p>
                            <?php if (!empty($service['DescriptionService'])): ?>
                            <p><strong>Description:</strong> <?php echo htmlspecialchars($service['DescriptionService']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($personnelCount > 0): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Ce service a <strong><?php echo $personnelCount; ?> membre(s)</strong> de personnel associé(s). La suppression n'est pas possible.
                        </div>
                        <a href="index.php" class="btn btn-secondary">Retour à la liste</a>
                        <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Attention: Cette action est irréversible. Le service sera définitivement supprimé.
                        </div>
                        
                        <form method="post" action="delete.php?id=<?php echo $id; ?>">
                            <input type="hidden" name="confirm" value="1">
                            <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
                            <a href="index.php" class="btn btn-secondary">Annuler</a>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
