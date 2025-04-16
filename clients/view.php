<?php
require_once '../config.php';
redirectIfNotLoggedIn();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

$sql = "SELECT * FROM CLIENT WHERE IdClient = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$client = mysqli_fetch_assoc($result);

$projetsSql = "SELECT * FROM PROJET WHERE IdClient = $id ORDER BY DateDebutProjet DESC";
$projetsResult = mysqli_query($conn, $projetsSql);

$reglementsSql = "SELECT * FROM REGLEMENT WHERE IdClient = $id ORDER BY DateReglement DESC";
$reglementsResult = mysqli_query($conn, $reglementsSql);

$totalReglements = 0;
$reglements = [];
if (mysqli_num_rows($reglementsResult) > 0) {
    while($row = mysqli_fetch_assoc($reglementsResult)) {
        $reglements[] = $row;
        $totalReglements += $row['MontantReglement'];
    }
}

$totalProjets = 0;
$projets = [];
if (mysqli_num_rows($projetsResult) > 0) {
    while($row = mysqli_fetch_assoc($projetsResult)) {
        $projets[] = $row;
        $totalProjets += $row['CoutProjet'];
    }
}

$solde = $totalProjets - $totalReglements;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Client - Gestion de Projets</title>
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
        .client-header {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .client-info {
            margin-bottom: 0;
        }
        @media print {
            .sidebar, .btn-toolbar, .nav-tabs, .no-print {
                display: none !important;
            }
            .container-fluid, .ms-sm-auto {
                margin: 0 !important;
                padding: 0 !important;
            }
            .tab-content {
                display: block !important;
            }
            .tab-pane {
                display: block !important;
                opacity: 1 !important;
                visibility: visible !important;
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
                            <a class="nav-link active" href="../clients/index.php">
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
                    <h1 class="h2">Détails du Client</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="index.php" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Retour à la liste
                        </a>
                        <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-sm btn-warning me-2">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                        <button onclick="window.print()" class="btn btn-sm btn-info me-2">
                            <i class="bi bi-printer"></i> Imprimer
                        </button>
                        <a href="export_pdf.php?id=<?php echo $id; ?>" class="btn btn-sm btn-danger">
                            <i class="bi bi-file-earmark-pdf"></i> Exporter PDF
                        </a>
                    </div>
                </div>

                <div class="client-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h3><?php echo htmlspecialchars($client['NomClient']); ?></h3>
                            <p class="client-info"><strong>ID:</strong> <?php echo $client['IdClient']; ?></p>
                            <p class="client-info"><strong>Adresse:</strong> <?php echo htmlspecialchars($client['AdresseClient']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="client-info"><strong>Email:</strong> <?php echo htmlspecialchars($client['EmailClient']); ?></p>
                            <p class="client-info"><strong>Téléphone:</strong> <?php echo htmlspecialchars($client['TelClient']); ?></p>
                            <p class="client-info"><strong>Solde:</strong> <span class="<?php echo $solde > 0 ? 'text-danger' : 'text-success'; ?>"><?php echo number_format($solde, 2, ',', ' '); ?> FCFA</span></p>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs mb-4" id="clientTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="projets-tab" data-bs-toggle="tab" data-bs-target="#projets" type="button" role="tab" aria-controls="projets" aria-selected="true">Projets (<?php echo count($projets); ?>)</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reglements-tab" data-bs-toggle="tab" data-bs-target="#reglements" type="button" role="tab" aria-controls="reglements" aria-selected="false">Règlements (<?php echo count($reglements); ?>)</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="resume-tab" data-bs-toggle="tab" data-bs-target="#resume" type="button" role="tab" aria-controls="resume" aria-selected="false">Résumé financier</button>
                    </li>
                </ul>

                <div class="tab-content" id="clientTabContent">
                    <div class="tab-pane fade show active" id="projets" role="tabpanel" aria-labelledby="projets-tab">
                        <div class="d-flex justify-content-between mb-3">
                            <h4>Projets du client</h4>
                            <a href="../projets/create.php?client=<?php echo $id; ?>" class="btn btn-sm btn-primary no-print">
                                <i class="bi bi-plus-circle"></i> Nouveau Projet
                            </a>
                        </div>
                        
                        <?php if (count($projets) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Titre</th>
                                        <th>Type</th>
                                        <th>Coût</th>
                                        <th>Début</th>
                                        <th>Fin</th>
                                        <th>État</th>
                                        <th class="no-print">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projets as $projet): 
                                        $typeSql = "SELECT LibelleTypeProjet FROM TYPEPROJET WHERE IdTypeProjet = " . $projet['IdTypeProjet'];
                                        $typeResult = mysqli_query($conn, $typeSql);
                                        $typeProjet = mysqli_fetch_assoc($typeResult)['LibelleTypeProjet'];
                                        
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
                                        <td><?php echo htmlspecialchars($typeProjet); ?></td>
                                        <td><?php echo number_format($projet['CoutProjet'], 2, ',', ' '); ?> FCFA</td>
                                        <td><?php echo date('d/m/Y', strtotime($projet['DateDebutProjet'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($projet['DateFinProjet'])); ?></td>
                                        <td><?php echo $etat; ?></td>
                                        <td class="no-print">
                                            <a href="../projets/view.php?id=<?php echo $projet['IdProjet']; ?>" class="btn btn-sm btn-info me-1"><i class="bi bi-eye"></i></a>
                                            <a href="../projets/edit.php?id=<?php echo $projet['IdProjet']; ?>" class="btn btn-sm btn-warning me-1"><i class="bi bi-pencil"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3">Total</th>
                                        <th><?php echo number_format($totalProjets, 2, ',', ' '); ?> FCFA</th>
                                        <th colspan="4"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            Aucun projet trouvé pour ce client.
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="reglements" role="tabpanel" aria-labelledby="reglements-tab">
                        <div class="d-flex justify-content-between mb-3">
                            <h4>Règlements du client</h4>
                            <a href="../reglements/create.php?client=<?php echo $id; ?>" class="btn btn-sm btn-primary no-print">
                                <i class="bi bi-plus-circle"></i> Nouveau Règlement
                            </a>
                        </div>
                        
                        <?php if (count($reglements) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Heure</th>
                                        <th>Montant</th>
                                        <th class="no-print">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reglements as $reglement): ?>
                                    <tr>
                                        <td><?php echo $reglement['IdReglement']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($reglement['DateReglement'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($reglement['HeureReglement'])); ?></td>
                                        <td><?php echo number_format($reglement['MontantReglement'], 2, ',', ' '); ?> FCFA</td>
                                        <td class="no-print">
                                            <a href="../reglements/view.php?id=<?php echo $reglement['IdReglement']; ?>" class="btn btn-sm btn-info me-1"><i class="bi bi-eye"></i></a>
                                            <a href="../reglements/edit.php?id=<?php echo $reglement['IdReglement']; ?>" class="btn btn-sm btn-warning me-1"><i class="bi bi-pencil"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3">Total</th>
                                        <th><?php echo number_format($totalReglements, 2, ',', ' '); ?> FCFA</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            Aucun règlement trouvé pour ce client.
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="resume" role="tabpanel" aria-labelledby="resume-tab">
                        <h4 class="mb-4">Résumé financier</h4>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Montant total des projets</h5>
                                        <h2 class="card-text text-primary"><?php echo number_format($totalProjets, 2, ',', ' '); ?> FCFA</h2>
                                        <p class="card-text text-muted">Somme des coûts de tous les projets</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Montant total des règlements</h5>
                                        <h2 class="card-text text-success"><?php echo number_format($totalReglements, 2, ',', ' '); ?> FCFA</h2>
                                        <p class="card-text text-muted">Somme des paiements effectués</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Solde actuel</h5>
                                <h2 class="card-text <?php echo $solde > 0 ? 'text-danger' : 'text-success'; ?>">
                                    <?php echo number_format($solde, 2, ',', ' '); ?> FCFA
                                </h2>
                                <p class="card-text text-muted">
                                    <?php if ($solde > 0): ?>
                                        Montant restant à payer
                                    <?php elseif ($solde < 0): ?>
                                        Crédit client (trop-perçu)
                                    <?php else: ?>
                                        Compte à l'équilibre
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="../libs/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
