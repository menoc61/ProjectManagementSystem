<?php
require_once '../config.php';
redirectIfNotLoggedIn();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Récupérer les informations de l'affectation
$sql = "SELECT a.*, 
        p.MatriculePersonnel, p.NomPersonnel, p.PrenomPersonnel, p.EmailPersonnel, p.TelPersonnel,
        t.LibelleTache, t.DateDebutTache, t.DateFinTache, t.EtatTache,
        pr.IdProjet, pr.TitreProjet, pr.DescriptionProjet,
        c.IdClient, c.NomClient
        FROM AFFECTATION a 
        JOIN PERSONNEL p ON a.MatriculePersonnel = p.MatriculePersonnel
        JOIN TACHE t ON a.IdTache = t.IdTache
        JOIN PROJET pr ON t.IdProjet = pr.IdProjet
        JOIN CLIENT c ON pr.IdClient = c.IdClient
        WHERE a.IdAffectation = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$affectation = mysqli_fetch_assoc($result);

// Map EtatTache to human-readable labels
$etatTacheLabels = [
    1 => 'À faire',
    2 => 'En cours',
    3 => 'Terminée',
    4 => 'Annulée'
];
$etatTacheLabel = $etatTacheLabels[$affectation['EtatTache']] ?? 'Inconnu';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'Affectation - Gestion de Projets</title>
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
        .affectation-header {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .affectation-info {
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
                            <a class="nav-link" href="../personnel/index.php">
                                <i class="bi bi-person-badge"></i> Personnel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="../affectations/index.php">
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
                    <h1 class="h2">Détails de l'Affectation</h1>
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

                <div class="affectation-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h3>Affectation #<?php echo $affectation['IdAffectation']; ?></h3>
                            <p class="affectation-info"><strong>Date d'affectation:</strong> <?php echo date('d/m/Y', strtotime($affectation['DateAffectation'])); ?></p>
                            <p class="affectation-info"><strong>Personnel:</strong> <?php echo htmlspecialchars($affectation['PrenomPersonnel'] . ' ' . $affectation['NomPersonnel']); ?></p>
                            <p class="affectation-info"><strong>Email:</strong> <?php echo htmlspecialchars($affectation['EmailPersonnel']); ?></p>
                            <?php if (!empty($affectation['TelPersonnel'])): ?>
                            <p class="affectation-info"><strong>Téléphone:</strong> <?php echo htmlspecialchars($affectation['TelPersonnel']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Informations sur la tâche</h5>
                                    <p class="card-text"><strong>Tâche:</strong> <?php echo htmlspecialchars($affectation['LibelleTache']); ?></p>
                                    <p class="card-text"><strong>Projet:</strong> <?php echo htmlspecialchars($affectation['TitreProjet']); ?></p>
                                    <p class="card-text"><strong>Client:</strong> <?php echo htmlspecialchars($affectation['NomClient']); ?></p>
                                    <p class="card-text"><strong>État:</strong> 
                                        <span class="badge <?php echo $affectation['EtatTache'] == 3 ? 'bg-success' : ($affectation['EtatTache'] == 2 ? 'bg-primary' : 'bg-warning'); ?>">
                                            <?php echo htmlspecialchars($etatTacheLabel); ?>
                                        </span>
                                    </p>
                                    <p class="card-text"><strong>Période:</strong> Du <?php echo date('d/m/Y', strtotime($affectation['DateDebutTache'])); ?> au <?php echo date('d/m/Y', strtotime($affectation['DateFinTache'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($affectation['DescriptionTache'])): ?>
                    <div class="mt-3">
                        <h5>Description de la tâche</h5>
                        <p><?php echo nl2br(htmlspecialchars($affectation['DescriptionTache'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($affectation['CommentairesAffectation'])): ?>
                    <div class="mt-3">
                        <h5>Commentaires sur l'affectation</h5>
                        <p><?php echo nl2br(htmlspecialchars($affectation['CommentairesAffectation'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Liens rapides</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <a href="../personnel/view.php?id=<?php echo $affectation['MatriculePersonnel']; ?>" class="list-group-item list-group-item-action">
                                        <i class="bi bi-person-badge"></i> Voir la fiche du personnel
                                    </a>
                                    <a href="../taches/view.php?id=<?php echo $affectation['IdTache']; ?>" class="list-group-item list-group-item-action">
                                        <i class="bi bi-list-check"></i> Voir la fiche de la tâche
                                    </a>
                                    <a href="../projets/view.php?id=<?php echo $affectation['IdProjet']; ?>" class="list-group-item list-group-item-action">
                                        <i class="bi bi-folder"></i> Voir la fiche du projet
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Actions</h5>
                            </div>
                            <div class="card-body">
                                <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-warning mb-2 w-100">
                                    <i class="bi bi-pencil"></i> Modifier cette affectation
                                </a>
                                <a href="delete.php?id=<?php echo $id; ?>" class="btn btn-danger mb-2 w-100">
                                    <i class="bi bi-trash"></i> Supprimer cette affectation
                                </a>
                                <button onclick="window.print()" class="btn btn-info w-100">
                                    <i class="bi bi-printer"></i> Imprimer cette fiche
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
