<?php
require_once '../config.php';
redirectIfNotLoggedIn();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

$sql = "SELECT p.*, c.NomClient, c.EmailClient, c.TelClient, t.LibelleTypeProjet, t.ForfaitCoutTypeProjet 
        FROM PROJET p 
        JOIN CLIENT c ON p.IdClient = c.IdClient 
        JOIN TYPEPROJET t ON p.IdTypeProjet = t.IdTypeProjet 
        WHERE p.IdProjet = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$projet = mysqli_fetch_assoc($result);

// Récupérer les tâches du projet
$tachesSql = "SELECT * FROM TACHE WHERE IdProjet = $id ORDER BY DateDebutTache";
$tachesResult = mysqli_query($conn, $tachesSql);

// Calculer les statistiques des tâches
$tachesStatsSql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN EtatTache = 1 THEN 1 ELSE 0 END) as a_faire,
                    SUM(CASE WHEN EtatTache = 2 THEN 1 ELSE 0 END) as en_cours,
                    SUM(CASE WHEN EtatTache = 3 THEN 1 ELSE 0 END) as terminees,
                    SUM(CASE WHEN EtatTache = 4 THEN 1 ELSE 0 END) as annulees
                  FROM TACHE 
                  WHERE IdProjet = $id";
$tachesStatsResult = mysqli_query($conn, $tachesStatsSql);
$tachesStats = mysqli_fetch_assoc($tachesStatsResult);

// Calculer le pourcentage d'avancement
$totalTaches = $tachesStats['total'];
$progression = 0;
if ($totalTaches > 0) {
    $progression = round(($tachesStats['terminees'] / $totalTaches) * 100);
}

// Récupérer les règlements du client
$reglementsSql = "SELECT * FROM REGLEMENT WHERE IdClient = " . $projet['IdClient'] . " ORDER BY DateReglement DESC";
$reglementsResult = mysqli_query($conn, $reglementsSql);

// Calculer le total des règlements
$totalReglementsSql = "SELECT SUM(MontantReglement) as total FROM REGLEMENT WHERE IdClient = " . $projet['IdClient'];
$totalReglementsResult = mysqli_query($conn, $totalReglementsSql);
$totalReglements = mysqli_fetch_assoc($totalReglementsResult)['total'] ?: 0;

// Calculer le solde
$solde = $projet['CoutProjet'] - $totalReglements;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Projet - Gestion de Projets</title>
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
        .projet-header {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .projet-info {
            margin-bottom: 0;
        }
        .progress {
            height: 20px;
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
                            <a class="nav-link" href="../clients/index.php">
                                <i class="bi bi-people"></i> Clients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="../projets/index.php">
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
                    <h1 class="h2">Détails du Projet</h1>
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

                <div class="projet-header">
                    <div class="row">
                        <div class="col-md-8">
                            <h3><?php echo htmlspecialchars($projet['TitreProjet']); ?></h3>
                            <p class="projet-info"><strong>ID:</strong> <?php echo $projet['IdProjet']; ?></p>
                            <p class="projet-info"><strong>Description:</strong> <?php echo htmlspecialchars($projet['DescriptionProjet']); ?></p>
                            <p class="projet-info">
                                <strong>État:</strong> 
                                <?php 
                                switch($projet['EtatProjet']) {
                                    case 1: echo "<span class='badge bg-primary'>En cours</span>"; break;
                                    case 2: echo "<span class='badge bg-success'>Terminé</span>"; break;
                                    case 3: echo "<span class='badge bg-danger'>Annulé</span>"; break;
                                }
                                ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="projet-info"><strong>Client:</strong> <a href="../clients/view.php?id=<?php echo $projet['IdClient']; ?>"><?php echo htmlspecialchars($projet['NomClient']); ?></a></p>
                            <p class="projet-info"><strong>Type:</strong> <a href="../typesprojet/view.php?id=<?php echo $projet['IdTypeProjet']; ?>"><?php echo htmlspecialchars($projet['LibelleTypeProjet']); ?></a></p>
                            <p class="projet-info"><strong>Coût:</strong> <?php echo number_format($projet['CoutProjet'], 2, ',', ' '); ?> FCFA</p>
                            <p class="projet-info"><strong>Début:</strong> <?php echo date('d/m/Y', strtotime($projet['DateDebutProjet'])); ?></p>
                            <p class="projet-info"><strong>Fin:</strong> <?php echo date('d/m/Y', strtotime($projet['DateFinProjet'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Progression du projet</h5>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo $progression; ?>%;" aria-valuenow="<?php echo $progression; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $progression; ?>%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs mb-4" id="projetTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="taches-tab" data-bs-toggle="tab" data-bs-target="#taches" type="button" role="tab" aria-controls="taches" aria-selected="true">Tâches (<?php echo $totalTaches; ?>)</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="finances-tab" data-bs-toggle="tab" data-bs-target="#finances" type="button" role="tab" aria-controls="finances" aria-selected="false">Finances</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="statistiques-tab" data-bs-toggle="tab" data-bs-target="#statistiques" type="button" role="tab" aria-controls="statistiques" aria-selected="false">Statistiques</button>
                    </li>
                </ul>

                <div class="tab-content" id="projetTabContent">
                    <div class="tab-pane fade show active" id="taches" role="tabpanel" aria-labelledby="taches-tab">
                        <div class="d-flex justify-content-between mb-3">
                            <h4>Tâches du projet</h4>
                            <a href="../taches/create.php?projet=<?php echo $id; ?>" class="btn btn-sm btn-primary no-print">
                                <i class="bi bi-plus-circle"></i> Nouvelle Tâche
                            </a>
                        </div>
                        
                        <?php if (mysqli_num_rows($tachesResult) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Libellé</th>
                                        <th>Date d'enregistrement</th>
                                        <th>Date de début</th>
                                        <th>Date de fin</th>
                                        <th>État</th>
                                        <th class="no-print">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($tache = mysqli_fetch_assoc($tachesResult)): 
                                        $etat = "";
                                        switch($tache['EtatTache']) {
                                            case 1: $etat = "<span class='badge bg-secondary'>À faire</span>"; break;
                                            case 2: $etat = "<span class='badge bg-primary'>En cours</span>"; break;
                                            case 3: $etat = "<span class='badge bg-success'>Terminée</span>"; break;
                                            case 4: $etat = "<span class='badge bg-danger'>Annulée</span>"; break;
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $tache['IdTache']; ?></td>
                                        <td><?php echo htmlspecialchars($tache['LibelleTache']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($tache['DateEnregTache'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($tache['DateDebutTache'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($tache['DateFinTache'])); ?></td>
                                        <td><?php echo $etat; ?></td>
                                        <td class="no-print">
                                            <a href="../taches/view.php?id=<?php echo $tache['IdTache']; ?>" class="btn btn-sm btn-info me-1"><i class="bi bi-eye"></i></a>
                                            <a href="../taches/edit.php?id=<?php echo $tache['IdTache']; ?>" class="btn btn-sm btn-warning me-1"><i class="bi bi-pencil"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            Aucune tâche trouvée pour ce projet.
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="finances" role="tabpanel" aria-labelledby="finances-tab">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Informations financières</h5>
                                        <p><strong>Coût du projet:</strong> <?php echo number_format($projet['CoutProjet'], 2, ',', ' '); ?> FCFA</p>
                                        <p><strong>Forfait type de projet:</strong> <?php echo number_format($projet['ForfaitCoutTypeProjet'], 2, ',', ' '); ?> FCFA</p>
                                        <p><strong>Total des règlements:</strong> <?php echo number_format($totalReglements, 2, ',', ' '); ?> FCFA</p>
                                        <p><strong>Solde:</strong> <span class="<?php echo $solde > 0 ? 'text-danger' : 'text-success'; ?>"><?php echo number_format($solde, 2, ',', ' '); ?> FCFA</span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Informations client</h5>
                                        <p><strong>Nom:</strong> <a href="../clients/view.php?id=<?php echo $projet['IdClient']; ?>"><?php echo htmlspecialchars($projet['NomClient']); ?></a></p>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($projet['EmailClient']); ?></p>
                                        <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($projet['TelClient']); ?></p>
                                        <a href="../reglements/create.php?client=<?php echo $projet['IdClient']; ?>" class="btn btn-sm btn-primary no-print">
                                            <i class="bi bi-plus-circle"></i> Nouveau Règlement
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h5 class="mb-3">Historique des règlements</h5>
                        <?php if (mysqli_num_rows($reglementsResult) > 0): ?>
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
                                    <?php while($reglement = mysqli_fetch_assoc($reglementsResult)): ?>
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
                                    <?php endwhile; ?>
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

                    <div class="tab-pane fade" id="statistiques" role="tabpanel" aria-labelledby="statistiques-tab">
                        <h4 class="mb-4">Statistiques du projet</h4>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Répartition des tâches</h5>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>État</th>
                                                        <th>Nombre</th>
                                                        <th>Pourcentage</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><span class="badge bg-secondary">À faire</span></td>
                                                        <td><?php echo $tachesStats['a_faire']; ?></td>
                                                        <td><?php echo $totalTaches > 0 ? round(($tachesStats['a_faire'] / $totalTaches) * 100) : 0; ?>%</td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-primary">En cours</span></td>
                                                        <td><?php echo $tachesStats['en_cours']; ?></td>
                                                        <td><?php echo $totalTaches > 0 ? round(($tachesStats['en_cours'] / $totalTaches) * 100) : 0; ?>%</td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-success">Terminées</span></td>
                                                        <td><?php echo $tachesStats['terminees']; ?></td>
                                                        <td><?php echo $totalTaches > 0 ? round(($tachesStats['terminees'] / $totalTaches) * 100) : 0; ?>%</td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-danger">Annulées</span></td>
                                                        <td><?php echo $tachesStats['annulees']; ?></td>
                                                        <td><?php echo $totalTaches > 0 ? round(($tachesStats['annulees'] / $totalTaches) * 100) : 0; ?>%</td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th>Total</th>
                                                        <th><?php echo $totalTaches; ?></th>
                                                        <th>100%</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Progression du projet</h5>
                                        <div class="progress mb-3">
                                            <div class="progress-bar" role="progressbar" style="width: <?php echo $progression; ?>%;" aria-valuenow="<?php echo $progression; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $progression; ?>%</div>
                                        </div>
                                        <p>Basé sur le nombre de tâches terminées par rapport au nombre total de tâches.</p>
                                        
                                        <h6 class="mt-4">Durée du projet</h6>
                                        <?php
                                        $dateDebut = new DateTime($projet['DateDebutProjet']);
                                        $dateFin = new DateTime($projet['DateFinProjet']);
                                        $duree = $dateDebut->diff($dateFin);
                                        $dureeJours = $duree->days;
                                        
                                        $aujourdhui = new DateTime();
                                        $joursEcoules = $dateDebut->diff($aujourdhui)->days;
                                        $joursRestants = $dateFin->diff($aujourdhui)->days;
                                        
                                        $progressionTemps = 0;
                                        if ($dureeJours > 0) {
                                            $progressionTemps = min(100, round(($joursEcoules / $dureeJours) * 100));
                                        }
                                        
                                        $retard = ($aujourdhui > $dateFin && $projet['EtatProjet'] == 1);
                                        ?>
                                        
                                        <p><strong>Durée totale:</strong> <?php echo $dureeJours; ?> jours</p>
                                        <p><strong>Jours écoulés:</strong> <?php echo $joursEcoules; ?> jours</p>
                                        <p><strong>Jours restants:</strong> <?php echo $joursRestants; ?> jours</p>
                                        
                                        <?php if ($retard): ?>
                                        <div class="alert alert-danger">
                                            <i class="bi bi-exclamation-triangle"></i> Le projet est en retard de <?php echo $joursRestants; ?> jours.
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="progress">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $progressionTemps; ?>%;" aria-valuenow="<?php echo $progressionTemps; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo $progressionTemps; ?>%</div>
                                        </div>
                                        <p class="mt-2">Progression temporelle du projet</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Comparaison progression / temps</h5>
                                <p>
                                    <?php if ($progression > $progressionTemps): ?>
                                    <span class="text-success"><i class="bi bi-arrow-up-circle"></i> Le projet avance plus vite que prévu.</span>
                                    <?php elseif ($progression < $progressionTemps): ?>
                                    <span class="text-warning"><i class="bi bi-arrow-down-circle"></i> Le projet avance moins vite que prévu.</span>
                                    <?php else: ?>
                                    <span class="text-info"><i class="bi bi-arrow-right-circle"></i> Le projet avance comme prévu.</span>
                                    <?php endif; ?>
                                </p>
                                <div class="progress mb-2">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $progression; ?>%;" aria-valuenow="<?php echo $progression; ?>" aria-valuemin="0" aria-valuemax="100">Tâches: <?php echo $progression; ?>%</div>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $progressionTemps; ?>%;" aria-valuenow="<?php echo $progressionTemps; ?>" aria-valuemin="0" aria-valuemax="100">Temps: <?php echo $progressionTemps; ?>%</div>
                                </div>
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
