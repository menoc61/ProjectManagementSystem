<?php
require_once '../config.php';
redirectIfNotLoggedIn();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Fetch task details
$sql = "SELECT t.*, p.TitreProjet, c.NomClient 
        FROM TACHE t
        JOIN PROJET p ON t.IdProjet = p.IdProjet
        JOIN CLIENT c ON p.IdClient = c.IdClient
        WHERE t.IdTache = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$tache = mysqli_fetch_assoc($result);

// Calculate remaining days or overdue status
$today = new DateTime();
$endDate = new DateTime($tache['DateFinTache']);
$interval = $today->diff($endDate);
$joursRestants = $interval->days;
$retard = $today > $endDate && $tache['EtatTache'] != 3 && $tache['EtatTache'] != 4;

// Fetch personnel assigned to the task
$affectationsSql = "SELECT a.*, p.NomPersonnel, p.PrenomPersonnel, s.LibelleService 
                    FROM AFFECTATION a
                    JOIN PERSONNEL p ON a.MatriculePersonnel = p.MatriculePersonnel
                    JOIN SERVICE s ON p.CodeService = s.CodeService
                    WHERE a.IdTache = $id";
$affectationsResult = mysqli_query($conn, $affectationsSql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Tâche - Gestion de Projets</title>
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
        .tache-header {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .tache-info {
            margin-bottom: 0;
        }
        @media print {
            .sidebar, .btn-toolbar, .no-print {
                display: none !important;
            }
            .container-fluid, .ms-sm-auto {
                margin: 0 !important;
                padding: 0 !important;
            }
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
                            <a class="nav-link active" href="../taches/index.php">
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
                    <h1 class="h2">Détails de la Tâche</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="index.php" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Retour à la liste
                        </a>
                        <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-sm btn-warning me-2">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                        <button onclick="window.print()" class="btn btn-sm btn-info">
                            <i class="bi bi-printer"></i> Imprimer
                        </button>
                    </div>
                </div>

                <div class="tache-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h3><?php echo htmlspecialchars($tache['LibelleTache']); ?></h3>
                            <p class="tache-info"><strong>ID:</strong> <?php echo $tache['IdTache']; ?></p>
                            <p class="tache-info">
                                <strong>État:</strong> 
                                <?php 
                                switch($tache['EtatTache']) {
                                    case 1: echo "<span class='badge bg-secondary'>À faire</span>"; break;
                                    case 2: echo "<span class='badge bg-primary'>En cours</span>"; break;
                                    case 3: echo "<span class='badge bg-success'>Terminée</span>"; break;
                                    case 4: echo "<span class='badge bg-danger'>Annulée</span>"; break;
                                }
                                ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="tache-info"><strong>Projet:</strong> <a href="../projets/view.php?id=<?php echo $tache['IdProjet']; ?>"><?php echo htmlspecialchars($tache['TitreProjet']); ?></a></p>
                            <p class="tache-info"><strong>Client:</strong> <?php echo htmlspecialchars($tache['NomClient']); ?></p>
                            <p class="tache-info"><strong>Date d'enregistrement:</strong> <?php echo date('d/m/Y', strtotime($tache['DateEnregTache'])); ?></p>
                            <p class="tache-info"><strong>Date de début:</strong> <?php echo date('d/m/Y', strtotime($tache['DateDebutTache'])); ?></p>
                            <p class="tache-info"><strong>Date de fin:</strong> <?php echo date('d/m/Y', strtotime($tache['DateFinTache'])); ?></p>
                            
                            <?php if ($retard): ?>
                            <div class="alert alert-danger mt-2">
                                <i class="bi bi-exclamation-triangle"></i> Tâche en retard de <?php echo $joursRestants; ?> jours.
                            </div>
                            <?php elseif ($tache['EtatTache'] != 3 && $tache['EtatTache'] != 4): ?>
                            <p class="tache-info"><strong>Jours restants:</strong> <?php echo $joursRestants; ?> jours</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <h4 class="mb-3">Personnel affecté à cette tâche</h4>
                
                <?php if (mysqli_num_rows($affectationsResult) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Personnel</th>
                                <th>Service</th>
                                <th>Date d'affectation</th>
                                <th class="no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($affectation = mysqli_fetch_assoc($affectationsResult)): ?>
                            <tr>
                                <td><?php echo $affectation['IdAffectation']; ?></td>
                                <td>
                                    <a href="../personnel/view.php?id=<?php echo $affectation['MatriculePersonnel']; ?>">
                                        <?php echo htmlspecialchars($affectation['PrenomPersonnel'] . ' ' . $affectation['NomPersonnel']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($affectation['LibelleService']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($affectation['DateAffectation'])); ?></td>
                                <td class="no-print">
                                    <a href="../affectations/view.php?id=<?php echo $affectation['IdAffectation']; ?>" class="btn btn-sm btn-info me-1"><i class="bi bi-eye"></i></a>
                                    <a href="../affectations/edit.php?id=<?php echo $affectation['IdAffectation']; ?>" class="btn btn-sm btn-warning me-1"><i class="bi bi-pencil"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    Aucun personnel affecté à cette tâche.
                </div>
                <?php endif; ?>

                <div class="mt-4 no-print">
                    <a href="../affectations/create.php?tache=<?php echo $id; ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Affecter du personnel
                    </a>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Chronologie</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <i class="bi bi-calendar-plus"></i> 
                                <strong>Enregistrement:</strong> <?php echo date('d/m/Y', strtotime($tache['DateEnregTache'])); ?>
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-calendar-date"></i> 
                                <strong>Début:</strong> <?php echo date('d/m/Y', strtotime($tache['DateDebutTache'])); ?>
                            </li>
                            <li class="list-group-item">
                                <i class="bi bi-calendar-check"></i> 
                                <strong>Fin prévue:</strong> <?php echo date('d/m/Y', strtotime($tache['DateFinTache'])); ?>
                            </li>
                            <?php if ($tache['EtatTache'] == 3): ?>
                            <li class="list-group-item text-success">
                                <i class="bi bi-check-circle"></i> 
                                <strong>Terminée</strong>
                            </li>
                            <?php elseif ($tache['EtatTache'] == 4): ?>
                            <li class="list-group-item text-danger">
                                <i class="bi bi-x-circle"></i> 
                                <strong>Annulée</strong>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>