<?php
require_once '../config.php';
redirectIfNotLoggedIn();

// Récupérer les statistiques globales
// Nombre total de clients
$clientsSql = "SELECT COUNT(*) as count FROM CLIENT";
$clientsResult = mysqli_query($conn, $clientsSql);
$clientsCount = mysqli_fetch_assoc($clientsResult)['count'];

// Nombre total de projets
$projetsSql = "SELECT COUNT(*) as count FROM PROJET";
$projetsResult = mysqli_query($conn, $projetsSql);
$projetsCount = mysqli_fetch_assoc($projetsResult)['count'];

// Nombre de projets par état
$projetsEtatSql = "SELECT EtatProjet, COUNT(*) as count FROM PROJET GROUP BY EtatProjet";
$projetsEtatResult = mysqli_query($conn, $projetsEtatSql);
$projetsEtat = [];
while ($row = mysqli_fetch_assoc($projetsEtatResult)) {
    $projetsEtat[$row['EtatProjet']] = $row['count'];
}

// Nombre total de tâches
$tachesSql = "SELECT COUNT(*) as count FROM TACHE";
$tachesResult = mysqli_query($conn, $tachesSql);
$tachesCount = mysqli_fetch_assoc($tachesResult)['count'];

// Nombre de tâches par état
$tachesEtatSql = "SELECT EtatTache, COUNT(*) as count FROM TACHE GROUP BY EtatTache";
$tachesEtatResult = mysqli_query($conn, $tachesEtatSql);
$tachesEtat = [];
while ($row = mysqli_fetch_assoc($tachesEtatResult)) {
    $tachesEtat[$row['EtatTache']] = $row['count'];
}

// Montant total des règlements
$reglementsSql = "SELECT SUM(MontantReglement) as total FROM REGLEMENT";
$reglementsResult = mysqli_query($conn, $reglementsSql);
$reglementsTotal = mysqli_fetch_assoc($reglementsResult)['total'] ?: 0;

// Montant total des projets
$projetsMontantSql = "SELECT SUM(CoutProjet) as total FROM PROJET";
$projetsMontantResult = mysqli_query($conn, $projetsMontantSql);
$projetsMontantTotal = mysqli_fetch_assoc($projetsMontantResult)['total'] ?: 0;

// Solde à percevoir
$soldeTotal = $projetsMontantTotal - $reglementsTotal;

// Projets récents
$projetsRecentsSql = "SELECT p.*, c.NomClient, tp.LibelleTypeProjet 
                      FROM PROJET p 
                      JOIN CLIENT c ON p.IdClient = c.IdClient 
                      JOIN TYPEPROJET tp ON p.IdTypeProjet = tp.IdTypeProjet 
                      ORDER BY p.DateDebutProjet DESC 
                      LIMIT 5";
$projetsRecentsResult = mysqli_query($conn, $projetsRecentsSql);

// Tâches récentes
$tachesRecentesSql = "SELECT t.*, p.TitreProjet 
                      FROM TACHE t 
                      JOIN PROJET p ON t.IdProjet = p.IdProjet 
                      ORDER BY t.DateDebutTache DESC 
                      LIMIT 5";
$tachesRecentesResult = mysqli_query($conn, $tachesRecentesSql);

// Règlements récents
$reglementsRecentsSql = "SELECT r.*, c.NomClient 
                         FROM REGLEMENT r 
                         JOIN CLIENT c ON r.IdClient = c.IdClient 
                         ORDER BY r.DateReglement DESC 
                         LIMIT 5";
$reglementsRecentsResult = mysqli_query($conn, $reglementsRecentsSql);

// Statistiques par mois (6 derniers mois)
$mois = [];
$montantsProjetsMois = [];
$montantsReglementsMois = [];

for ($i = 5; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $mois[] = date('M Y', strtotime("-$i months"));
    
    $projetsMoisSql = "SELECT SUM(CoutProjet) as total 
                       FROM PROJET 
                       WHERE DATE_FORMAT(DateDebutProjet, '%Y-%m') = '$date'";
    $projetsMoisResult = mysqli_query($conn, $projetsMoisSql);
    $montantsProjetsMois[] = mysqli_fetch_assoc($projetsMoisResult)['total'] ?: 0;
    
    $reglementsMoisSql = "SELECT SUM(MontantReglement) as total 
                          FROM REGLEMENT 
                          WHERE DATE_FORMAT(DateReglement, '%Y-%m') = '$date'";
    $reglementsMoisResult = mysqli_query($conn, $reglementsMoisSql);
    $montantsReglementsMois[] = mysqli_fetch_assoc($reglementsMoisResult)['total'] ?: 0;
}

// Convertir les tableaux en format JSON pour les graphiques
$moisJson = json_encode($mois);
$montantsProjetsJson = json_encode($montantsProjetsMois);
$montantsReglementsJson = json_encode($montantsReglementsMois);

// Statistiques par type de projet
$typeProjetsSql = "SELECT tp.LibelleTypeProjet, COUNT(p.IdProjet) as count 
                   FROM TYPEPROJET tp 
                   LEFT JOIN PROJET p ON tp.IdTypeProjet = p.IdTypeProjet 
                   GROUP BY tp.IdTypeProjet";
$typeProjetsResult = mysqli_query($conn, $typeProjetsSql);

$typeProjetsLabels = [];
$typeProjetsData = [];

while ($row = mysqli_fetch_assoc($typeProjetsResult)) {
    $typeProjetsLabels[] = $row['LibelleTypeProjet'];
    $typeProjetsData[] = $row['count'];
}

$typeProjetsLabelsJson = json_encode($typeProjetsLabels);
$typeProjetsDataJson = json_encode($typeProjetsData);

// Map EtatProjet to human-readable labels
$etatProjetLabels = [
    1 => 'En cours',
    2 => 'Terminé',
    3 => 'Annulé'
];

// Initialize the project state counts
$projetsEtatCounts = [
    'En cours' => 0,
    'Terminé' => 0,
    'Annulé' => 0
];

// Populate the project state counts
foreach ($projetsEtat as $etat => $count) {
    if (isset($etatProjetLabels[$etat])) {
        $projetsEtatCounts[$etatProjetLabels[$etat]] = $count;
    }
}

// Map EtatTache to human-readable labels
$etatTacheLabels = [
    1 => 'À faire',
    2 => 'En cours',
    3 => 'Terminée',
    4 => 'Annulée'
];

// Initialize the task state counts
$tachesEtatCounts = [
    'À faire' => 0,
    'En cours' => 0,
    'Terminée' => 0,
    'Annulée' => 0
];

// Populate the task state counts
foreach ($tachesEtat as $etat => $count) {
    if (isset($etatTacheLabels[$etat])) {
        $tachesEtatCounts[$etatTacheLabels[$etat]] = $count;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestion de Projets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .stat-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
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
                            <a class="nav-link active" href="../dashboard/index.php">
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
                    <h1 class="h2">Tableau de Bord</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Imprimer
                        </button>
                    </div>
                </div>

                <!-- Statistiques générales -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Clients</h6>
                                        <h2 class="mb-0"><?php echo $clientsCount; ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-people"></i>
                                    </div>
                                </div>
                                <p class="card-text mt-3">
                                    <a href="../clients/index.php" class="text-white">Voir tous les clients <i class="bi bi-arrow-right"></i></a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Projets</h6>
                                        <h2 class="mb-0"><?php echo $projetsCount; ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-folder"></i>
                                    </div>
                                </div>
                                <p class="card-text mt-3">
                                    <a href="../projets/index.php" class="text-white">Voir tous les projets <i class="bi bi-arrow-right"></i></a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card bg-info text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Tâches</h6>
                                        <h2 class="mb-0"><?php echo $tachesCount; ?></h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-list-check"></i>
                                    </div>
                                </div>
                                <p class="card-text mt-3">
                                    <a href="../taches/index.php" class="text-white">Voir toutes les tâches <i class="bi bi-arrow-right"></i></a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card bg-warning text-dark h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Chiffre d'affaires</h6>
                                        <h2 class="mb-0"><?php echo number_format($projetsMontantTotal, 2, ',', ' '); ?> FCFA</h2>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-cash-coin"></i>
                                    </div>
                                </div>
                                <p class="card-text mt-3">
                                    <a href="../reglements/index.php" class="text-dark">Voir les règlements <i class="bi bi-arrow-right"></i></a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Graphiques -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title">Évolution sur 6 mois</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="evolutionChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title">Répartition par type de projet</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="typeProjetChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title">État des projets</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="etatProjetChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title">État des tâches</h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="etatTacheChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activité récente -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title">Projets récents</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <?php while ($projet = mysqli_fetch_assoc($projetsRecentsResult)): ?>
                                    <a href="../projets/view.php?id=<?php echo $projet['IdProjet']; ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($projet['TitreProjet']); ?></h6>
                                            <small><?php echo date('d/m/Y', strtotime($projet['DateDebutProjet'])); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars($projet['NomClient']); ?></p>
                                        <small class="text-muted"><?php echo htmlspecialchars($projet['LibelleTypeProjet']); ?></small>
                                    </a>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title">Tâches récentes</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <?php while ($tache = mysqli_fetch_assoc($tachesRecentesResult)): ?>
                                    <a href="../taches/view.php?id=<?php echo $tache['IdTache']; ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($tache['LibelleTache']); ?></h6>
                                            <small><?php echo date('d/m/Y', strtotime($tache['DateDebutTache'])); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars($tache['TitreProjet']); ?></p>
                                        <small class="text-muted">
                                            <span class="badge <?php echo $tache['EtatTache'] == 'Terminée' ? 'bg-success' : ($tache['EtatTache'] == 'En cours' ? 'bg-primary' : 'bg-warning'); ?>">
                                                <?php echo htmlspecialchars($tache['EtatTache']); ?>
                                            </span>
                                        </small>
                                    </a>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="card-title">Règlements récents</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <?php while ($reglement = mysqli_fetch_assoc($reglementsRecentsResult)): ?>
                                    <a href="../reglements/view.php?id=<?php echo $reglement['IdReglement']; ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($reglement['NomClient']); ?></h6>
                                            <small><?php echo date('d/m/Y', strtotime($reglement['DateReglement'])); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars($reglement['HeureReglement']); ?></p>
                                        <small class="text-muted"><?php echo number_format($reglement['MontantReglement'], 2, ',', ' '); ?> FCFA</small>
                                    </a>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Résumé financier -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Résumé financier</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Montant total des projets</h6>
                                                <h3 class="text-success"><?php echo number_format($projetsMontantTotal, 2, ',', ' '); ?> FCFA</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Montant total des règlements</h6>
                                                <h3 class="text-primary"><?php echo number_format($reglementsTotal, 2, ',', ' '); ?> FCFA</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Solde à percevoir</h6>
                                                <h3 class="text-danger"><?php echo number_format($soldeTotal, 2, ',', ' '); ?> FCFA</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Graphique d'évolution sur 6 mois
        const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
        const evolutionChart = new Chart(evolutionCtx, {
            type: 'line',
            data: {
                labels: <?php echo $moisJson; ?>,
                datasets: [
                    {
                        label: 'Montant des projets',
                        data: <?php echo $montantsProjetsJson; ?>,
                        borderColor: 'rgba(40, 167, 69, 1)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Montant des règlements',
                        data: <?php echo $montantsReglementsJson; ?>,
                        borderColor: 'rgba(0, 123, 255, 1)',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'CFA' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'CFA' }).format(value);
                            }
                        }
                    }
                }
            }
        });

        // Graphique de répartition par type de projet
        const typeProjetCtx = document.getElementById('typeProjetChart').getContext('2d');
        const typeProjetChart = new Chart(typeProjetCtx, {
            type: 'pie',
            data: {
                labels: <?php echo $typeProjetsLabelsJson; ?>,
                datasets: [{
                    data: <?php echo $typeProjetsDataJson; ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });

        // Graphique d'état des projets
        const etatProjetCtx = document.getElementById('etatProjetChart').getContext('2d');
        const etatProjetChart = new Chart(etatProjetCtx, {
            type: 'doughnut',
            data: {
                labels: ['En cours', 'Terminé', 'Annulé'],
                datasets: [{
                    data: [
                        <?php echo $projetsEtatCounts['En cours']; ?>,
                        <?php echo $projetsEtatCounts['Terminé']; ?>,
                        <?php echo $projetsEtatCounts['Annulé']; ?>
                    ],
                    backgroundColor: [
                        'rgba(0, 123, 255, 0.7)',
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(255, 193, 7, 0.7)'
                    ],
                    borderColor: [
                        'rgba(0, 123, 255, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });

        // Graphique d'état des tâches
        const etatTacheCtx = document.getElementById('etatTacheChart').getContext('2d');
        const etatTacheChart = new Chart(etatTacheCtx, {
            type: 'doughnut',
            data: {
                labels: ['À faire', 'En cours', 'Terminée', 'Annulée'],
                datasets: [{
                    data: [
                        <?php echo $tachesEtatCounts['À faire']; ?>,
                        <?php echo $tachesEtatCounts['En cours']; ?>,
                        <?php echo $tachesEtatCounts['Terminée']; ?>,
                        <?php echo $tachesEtatCounts['Annulée']; ?>
                    ],
                    backgroundColor: [
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(0, 123, 255, 0.7)',
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(255, 99, 132, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 193, 7, 1)',
                        'rgba(0, 123, 255, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
    </script>
</body>
</html>
