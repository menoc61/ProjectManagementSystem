<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$client = isset($_GET['client']) ? $_GET['client'] : '';
$dateDebut = isset($_GET['dateDebut']) ? $_GET['dateDebut'] : '';
$dateFin = isset($_GET['dateFin']) ? $_GET['dateFin'] : '';

$where = [];
if (!empty($search)) {
    $where[] = "MontantReglement LIKE '%$search%'";
}
if (!empty($client)) {
    $where[] = "r.IdClient = $client";
}
if (!empty($dateDebut)) {
    $where[] = "DateReglement >= '$dateDebut'";
}
if (!empty($dateFin)) {
    $where[] = "DateReglement <= '$dateFin'";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT r.*, c.NomClient 
        FROM REGLEMENT r 
        JOIN CLIENT c ON r.IdClient = c.IdClient 
        $whereClause 
        ORDER BY r.DateReglement DESC, r.HeureReglement DESC";
$result = mysqli_query($conn, $sql);

// Récupérer la liste des clients pour le filtre
$clientsSql = "SELECT * FROM CLIENT ORDER BY NomClient";
$clientsResult = mysqli_query($conn, $clientsSql);

// Calculer le total des règlements
$totalSql = "SELECT SUM(MontantReglement) as total FROM REGLEMENT $whereClause";
$totalResult = mysqli_query($conn, $totalSql);
$totalMontant = mysqli_fetch_assoc($totalResult)['total'] ?: 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Règlements - Gestion de Projets</title>
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
                    <h1 class="h2">Gestion des Règlements</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="create.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Nouveau Règlement
                        </a>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <form action="index.php" method="get" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="client" class="form-select">
                                    <option value="">Tous les clients</option>
                                    <?php while($clientRow = mysqli_fetch_assoc($clientsResult)): ?>
                                    <option value="<?php echo $clientRow['IdClient']; ?>" <?php echo $client == $clientRow['IdClient'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($clientRow['NomClient']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="dateDebut" class="form-control" placeholder="Date début" value="<?php echo $dateDebut; ?>">
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="dateFin" class="form-control" placeholder="Date fin" value="<?php echo $dateFin; ?>">
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-outline-primary">Filtrer</button>
                                <a href="index.php" class="btn btn-outline-secondary">Réinitialiser</a>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="export_csv.php<?php echo !empty($whereClause) ? '?' . http_build_query($_GET) : ''; ?>" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-file-earmark-excel"></i> Exporter CSV
                        </a>
                        <a href="export_pdf.php<?php echo !empty($whereClause) ? '?' . http_build_query($_GET) : ''; ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-file-earmark-pdf"></i> Exporter PDF
                        </a>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Statistiques des règlements</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-subtitle mb-2 text-muted">Total des règlements</h6>
                                        <h3 class="card-text"><?php echo number_format($totalMontant, 2, ',', ' '); ?> FCFA</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-subtitle mb-2 text-muted">Nombre de règlements</h6>
                                        <h3 class="card-text"><?php echo mysqli_num_rows($result); ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-subtitle mb-2 text-muted">Montant moyen</h6>
                                        <h3 class="card-text">
                                            <?php 
                                            $count = mysqli_num_rows($result);
                                            echo $count > 0 ? number_format($totalMontant / $count, 2, ',', ' ') : '0,00'; 
                                            ?> FCFA
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>Montant</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    $reglementId = $row['IdReglement'];
                                    
                                    echo "<tr>";
                                    echo "<td>" . $row['IdReglement'] . "</td>";
                                    echo "<td><a href='../clients/view.php?id=" . $row['IdClient'] . "'>" . htmlspecialchars($row['NomClient']) . "</a></td>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['DateReglement'])) . "</td>";
                                    echo "<td>" . date('H:i', strtotime($row['HeureReglement'])) . "</td>";
                                    echo "<td>" . number_format($row['MontantReglement'], 2, ',', ' ') . " FCFA</td>";
                                    echo "<td>";
                                    echo "<a href='view.php?id=" . $reglementId . "' class='btn btn-sm btn-info me-1'><i class='bi bi-eye'></i></a>";
                                    echo "<a href='edit.php?id=" . $reglementId . "' class='btn btn-sm btn-warning me-1'><i class='bi bi-pencil'></i></a>";
                                    echo "<a href='delete.php?id=" . $reglementId . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce règlement ?\")'><i class='bi bi-trash'></i></a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>Aucun règlement trouvé</td></tr>";
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Total:</th>
                                <th><?php echo number_format($totalMontant, 2, ',', ' '); ?> FCFA</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
