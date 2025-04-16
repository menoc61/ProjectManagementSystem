<?php
require_once '../config.php';
redirectIfNotLoggedIn();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Fetch the payment details
$sql = "SELECT r.*, c.NomClient, c.EmailClient, c.TelClient 
        FROM REGLEMENT r 
        JOIN CLIENT c ON r.IdClient = c.IdClient 
        WHERE r.IdReglement = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$reglement = mysqli_fetch_assoc($result);

// Provide fallback values for missing fields
$reglement['ModeReglement'] = $reglement['ModeReglement'] ?? 'Non spécifié';
$reglement['ReferenceReglement'] = $reglement['ReferenceReglement'] ?? 'Non spécifiée';

// Fetch the projects of the client
$projetsSql = "SELECT * FROM PROJET WHERE IdClient = " . $reglement['IdClient'] . " ORDER BY DateDebutProjet DESC";
$projetsResult = mysqli_query($conn, $projetsSql);

// Fetch other payments of the client
$reglementsSql = "SELECT * FROM REGLEMENT WHERE IdClient = " . $reglement['IdClient'] . " AND IdReglement != $id ORDER BY DateReglement DESC, HeureReglement DESC";
$reglementsResult = mysqli_query($conn, $reglementsSql);

// Calculate the total payments of the client
$totalReglementsSql = "SELECT SUM(MontantReglement) as total FROM REGLEMENT WHERE IdClient = " . $reglement['IdClient'];
$totalReglementsResult = mysqli_query($conn, $totalReglementsSql);
$totalReglements = mysqli_fetch_assoc($totalReglementsResult)['total'] ?: 0;

// Calculate the total cost of the client's projects
$totalProjetsSql = "SELECT SUM(CoutProjet) as total FROM PROJET WHERE IdClient = " . $reglement['IdClient'];
$totalProjetsResult = mysqli_query($conn, $totalProjetsSql);
$totalProjets = mysqli_fetch_assoc($totalProjetsResult)['total'] ?: 0;

// Calculate the balance
$solde = $totalProjets - $totalReglements;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Règlement - Gestion de Projets</title>
    <link href="../libs/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../libs/bootstrap-icons-1.10.0/font/bootstrap-icons.css">
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
        .reglement-header {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .reglement-info {
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
                            <a class="nav-link active" href="../reglements/index.php">
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
                    <h1 class="h2">Détails du Règlement</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="index.php" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Retour à la liste
                        </a>
                        <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-sm btn-warning me-2">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                        <a href="export_pdf.php?id=<?php echo $id; ?>" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-file-earmark-pdf"></i> Exporter PDF
                        </a>
                        <button onclick="window.print()" class="btn btn-sm btn-info">
                            <i class="bi bi-printer"></i> Imprimer
                        </button>
                    </div>
                </div>

                <div class="reglement-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h3>Règlement #<?php echo $reglement['IdReglement']; ?></h3>
                            <p class="reglement-info"><strong>Client:</strong> <a href="../clients/view.php?id=<?php echo $reglement['IdClient']; ?>"><?php echo htmlspecialchars($reglement['NomClient']); ?></a></p>
                            <p class="reglement-info"><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($reglement['DateReglement'])); ?></p>
                            <p class="reglement-info"><strong>Heure:</strong> <?php echo date('H:i', strtotime($reglement['HeureReglement'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Montant</h5>
                                    <h2 class="card-text text-primary"><?php echo number_format($reglement['MontantReglement'], 2, ',', ' '); ?> FCFA</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($reglement['CommentaireReglement'])): ?>
                    <div class="mt-3">
                        <h5>Commentaire</h5>
                        <p><?php echo nl2br(htmlspecialchars($reglement['CommentaireReglement'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Informations du client</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Nom:</strong> <?php echo htmlspecialchars($reglement['NomClient']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($reglement['EmailClient']); ?></p>
                                <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($reglement['TelClient']); ?></p>
                                <a href="../clients/view.php?id=<?php echo $reglement['IdClient']; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye"></i> Voir la fiche client
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Situation financière</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Total projets:</strong> <?php echo number_format($totalProjets, 2, ',', ' '); ?> FCFA</p>
                                        <p><strong>Total règlements:</strong> <?php echo number_format($totalReglements, 2, ',', ' '); ?> FCFA</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Solde:</strong> 
                                            <span class="<?php echo $solde > 0 ? 'text-danger' : 'text-success'; ?>">
                                                <?php echo number_format($solde, 2, ',', ' '); ?> FCFA
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h4 class="mb-3">Projets du client</h4>
                        <?php if (mysqli_num_rows($projetsResult) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Titre</th>
                                        <th>Date début</th>
                                        <th>Coût</th>
                                        <th class="no-print">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($projet = mysqli_fetch_assoc($projetsResult)): ?>
                                    <tr>
                                        <td><?php echo $projet['IdProjet']; ?></td>
                                        <td><?php echo htmlspecialchars($projet['TitreProjet']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($projet['DateDebutProjet'])); ?></td>
                                        <td><?php echo number_format($projet['CoutProjet'], 2, ',', ' '); ?> FCFA</td>
                                        <td class="no-print">
                                            <a href="../projets/view.php?id=<?php echo $projet['IdProjet']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            Aucun projet trouvé pour ce client.
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <h4 class="mb-3">Autres règlements du client</h4>
                        <?php if (mysqli_num_rows($reglementsResult) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th class="no-print">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($autreReglement = mysqli_fetch_assoc($reglementsResult)): ?>
                                    <tr>
                                        <td><?php echo $autreReglement['IdReglement']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($autreReglement['DateReglement'])); ?></td>
                                        <td><?php echo number_format($autreReglement['MontantReglement'], 2, ',', ' '); ?> FCFA</td>

                                        <td class="no-print">
                                            <a href="view.php?id=<?php echo $autreReglement['IdReglement']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            Aucun autre règlement trouvé pour ce client.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="../libs/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
