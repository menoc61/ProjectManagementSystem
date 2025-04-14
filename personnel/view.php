<?php
require_once '../config.php';
redirectIfNotLoggedIn();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Récupérer les informations du personnel
$sql = "SELECT p.*, s.LibelleService 
        FROM PERSONNEL p 
        LEFT JOIN SERVICE s ON p.CodeService = s.CodeService 
        WHERE p.MatriculePersonnel = '$id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$personnel = mysqli_fetch_assoc($result);

// Récupérer les affectations de ce membre du personnel
$affectationsSql = "SELECT a.*, t.IdTache, t.LibelleTache, t.DateDebutTache, t.DateFinTache, t.EtatTache, pr.TitreProjet
                    FROM AFFECTATION a 
                    JOIN TACHE t ON a.IdTache = t.IdTache 
                    JOIN PROJET pr ON t.IdProjet = pr.IdProjet
                    WHERE a.MatriculePersonnel = '$id' 
                    ORDER BY t.DateDebutTache DESC";
$affectationsResult = mysqli_query($conn, $affectationsSql);
$affectationsCount = mysqli_num_rows($affectationsResult);

// Calculer les statistiques
$tachesEnCoursSql = "SELECT COUNT(*) as count 
                     FROM AFFECTATION a 
                     JOIN TACHE t ON a.IdTache = t.IdTache 
                     WHERE a.MatriculePersonnel = '$id' AND t.EtatTache = 2"; // 2: En cours
$tachesEnCoursResult = mysqli_query($conn, $tachesEnCoursSql);
$tachesEnCours = mysqli_fetch_assoc($tachesEnCoursResult)['count'];

$tachesTermineesSql = "SELECT COUNT(*) as count 
                       FROM AFFECTATION a 
                       JOIN TACHE t ON a.IdTache = t.IdTache 
                       WHERE a.MatriculePersonnel = '$id' AND t.EtatTache = 3"; // 3: Terminée
$tachesTermineesResult = mysqli_query($conn, $tachesTermineesSql);
$tachesTerminees = mysqli_fetch_assoc($tachesTermineesResult)['count'];

// Map EtatTache to human-readable labels
$etatTacheLabels = [
    1 => 'À faire',
    2 => 'En cours',
    3 => 'Terminée',
    4 => 'Annulée'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Personnel - Gestion de Projets</title>
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
        .personnel-header {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .personnel-info {
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
                            <a class="nav-link active" href="../personnel/index.php">
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
                    <h1 class="h2">Détails du Personnel</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="index.php" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Retour à la liste
                        </a>
                        <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-sm btn-warning me-2">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                        <a href="export_csv.php?id=<?php echo $id; ?>" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-file-earmark-excel"></i> Exporter CSV
                        </a>
                        <button onclick="window.print()" class="btn btn-sm btn-info">
                            <i class="bi bi-printer"></i> Imprimer
                        </button>
                    </div>
                </div>

                <div class="personnel-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h3><?php echo htmlspecialchars($personnel['PrenomPersonnel'] . ' ' . $personnel['NomPersonnel']); ?></h3>
                            <p class="personnel-info"><strong>ID:</strong> <?php echo $personnel['MatriculePersonnel']; ?></p>
                            <p class="personnel-info"><strong>Email:</strong> <?php echo htmlspecialchars($personnel['EmailPersonnel']); ?></p>
                            <?php if (!empty($personnel['TelPersonnel'])): ?>
                            <p class="personnel-info"><strong>Téléphone:</strong> <?php echo htmlspecialchars($personnel['TelPersonnel']); ?></p>
                            <?php endif; ?>
                            <p class="personnel-info"><strong>Service:</strong> <?php echo htmlspecialchars($personnel['LibelleService'] ?: 'Non assigné'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Statistiques</h5>
                                    <p class="card-text"><strong>Tâches totales:</strong> <?php echo $affectationsCount; ?></p>
                                    <p class="card-text"><strong>Tâches en cours:</strong> <?php echo $tachesEnCours; ?></p>
                                    <p class="card-text"><strong>Tâches terminées:</strong> <?php echo $tachesTerminees; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($personnel['CompetencesPersonnel'])): ?>
                    <div class="mt-3">
                        <h5>Compétences</h5>
                        <p><?php echo nl2br(htmlspecialchars($personnel['CompetencesPersonnel'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($personnel['CommentairesPersonnel'])): ?>
                    <div class="mt-3">
                        <h5>Commentaires</h5>
                        <p><?php echo nl2br(htmlspecialchars($personnel['CommentairesPersonnel'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <h4 class="mb-3">Tâches affectées</h4>
                
                <?php if ($affectationsCount > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Projet</th>
                                <th>Tâche</th>
                                <th>Date début</th>
                                <th>Date fin</th>
                                <th>État</th>
                                <th class="no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($affectation = mysqli_fetch_assoc($affectationsResult)): ?>
                            <tr>
                                <td><?php echo $affectation['IdAffectation']; ?></td>
                                <td><?php echo htmlspecialchars($affectation['TitreProjet']); ?></td>
                                <td><?php echo htmlspecialchars($affectation['LibelleTache']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($affectation['DateDebutTache'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($affectation['DateFinTache'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $affectation['EtatTache'] == 'Terminée' ? 'bg-success' : ($affectation['EtatTache'] == 'En cours' ? 'bg-primary' : 'bg-warning'); ?>">
                                        <?php echo htmlspecialchars($affectation['EtatTache']); ?>
                                    </span>
                                </td>
                                <td class="no-print">
                                    <a href="../taches/view.php?id=<?php echo $affectation['IdTache']; ?>" class="btn btn-sm btn-info me-1"><i class="bi bi-eye"></i></a>
                                    <a href="../affectations/edit.php?id=<?php echo $affectation['IdAffectation']; ?>" class="btn btn-sm btn-warning me-1"><i class="bi bi-pencil"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    Aucune tâche affectée à ce membre du personnel.
                </div>
                <?php endif; ?>

                <div class="mt-4 no-print">
                    <a href="../affectations/create.php?personnel=<?php echo $id; ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Affecter une nouvelle tâche
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
