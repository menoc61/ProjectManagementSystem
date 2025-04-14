<?php
require_once '../config.php';
redirectIfNotLoggedIn();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

$sql = "SELECT * FROM TYPEPROJET WHERE IdTypeProjet = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$typeProjet = mysqli_fetch_assoc($result);

$projetsSql = "SELECT p.*, c.NomClient 
              FROM PROJET p 
              JOIN CLIENT c ON p.IdClient = c.IdClient 
              WHERE p.IdTypeProjet = $id 
              ORDER BY p.DateDebutProjet DESC";
$projetsResult = mysqli_query($conn, $projetsSql);

$totalProjetsSql = "SELECT COUNT(*) as count, SUM(CoutProjet) as total FROM PROJET WHERE IdTypeProjet = $id";
$totalProjetsResult = mysqli_query($conn, $totalProjetsSql);
$totalProjets = mysqli_fetch_assoc($totalProjetsResult);
$projetsCount = $totalProjets['count'];
$totalCout = $totalProjets['total'] ?: 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Type de Projet - Gestion de Projets</title>
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
        .type-header {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .type-info {
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
                            <a class="nav-link active" href="../typesprojet/index.php">
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
                    <h1 class="h2">Détails du Type de Projet</h1>
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

                <div class="type-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h3><?php echo htmlspecialchars($typeProjet['LibelleTypeProjet']); ?></h3>
                            <p class="type-info"><strong>ID:</strong> <?php echo $typeProjet['IdTypeProjet']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="type-info"><strong>Forfait coût:</strong> <?php echo number_format($typeProjet['ForfaitCoutTypeProjet'], 2, ',', ' '); ?> FCFA</p>
                            <p class="type-info"><strong>Nombre de projets:</strong> <?php echo $projetsCount; ?></p>
                            <p class="type-info"><strong>Coût total des projets:</strong> <?php echo number_format($totalCout, 2, ',', ' '); ?> FCFA</p>
                        </div>
                    </div>
                </div>

                <h4 class="mb-3">Projets associés à ce type</h4>
                
                <?php if (mysqli_num_rows($projetsResult) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Titre</th>
                                <th>Client</th>
                                <th>Coût</th>
                                <th>Début</th>
                                <th>Fin</th>
                                <th>État</th>
                                <th class="no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($projet = mysqli_fetch_assoc($projetsResult)): 
                                $etat = "";
                                switch($projet['EtatProjet']) {
                                    case 1: $etat = "<span class='badge bg-primary'>En cours</span>"; break;
                                    case 2: $etat = "<span class='badge bg-success'>Terminé</span>"; break;
                                    case 3: $etat = "<span class='badge bg-danger'>Annulé</span>"; break;
                                }
                            ?>
                            <tr>
                                <td><?php echo $projet['IdProjet']; ?></td>
                                <td><?php echo htmlspecialchars($projet['TitreProjet']); ?></td>
                                <td><?php echo htmlspecialchars($projet['NomClient']); ?></td>
                                <td><?php echo number_format($projet['CoutProjet'], 2, ',', ' '); ?> FCFA</td>
                                <td><?php echo date('d/m/Y', strtotime($projet['DateDebutProjet'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($projet['DateFinProjet'])); ?></td>
                                <td><?php echo $etat; ?></td>
                                <td class="no-print">
                                    <a href="../projets/view.php?id=<?php echo $projet['IdProjet']; ?>" class="btn btn-sm btn-info me-1"><i class="bi bi-eye"></i></a>
                                    <a href="../projets/edit.php?id=<?php echo $projet['IdProjet']; ?>" class="btn btn-sm btn-warning me-1"><i class="bi bi-pencil"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">Total</th>
                                <th><?php echo number_format($totalCout, 2, ',', ' '); ?> FCFA</th>
                                <th colspan="4"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    Aucun projet associé à ce type.
                </div>
                <?php endif; ?>

                <div class="mt-4">
                    <a href="../projets/create.php?type=<?php echo $id; ?>" class="btn btn-primary no-print">
                        <i class="bi bi-plus-circle"></i> Créer un nouveau projet de ce type
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
