<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$personnel = isset($_GET['personnel']) ? $_GET['personnel'] : '';
$tache = isset($_GET['tache']) ? $_GET['tache'] : '';

$where = [];
if (!empty($search)) {
    $where[] = "(p.NomPersonnel LIKE '%$search%' OR p.PrenomPersonnel LIKE '%$search%' OR t.LibelleTache LIKE '%$search%')";
}
if (!empty($personnel)) {
    $where[] = "a.MatriculePersonnel = '$personnel'";
}
if (!empty($tache)) {
    $where[] = "a.IdTache = $tache";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT a.*, 
        p.NomPersonnel, p.PrenomPersonnel, p.EmailPersonnel,
        t.LibelleTache, t.DateDebutTache, t.DateFinTache, t.EtatTache,
        pr.TitreProjet
        FROM AFFECTATION a 
        JOIN PERSONNEL p ON a.MatriculePersonnel = p.MatriculePersonnel
        JOIN TACHE t ON a.IdTache = t.IdTache
        JOIN PROJET pr ON t.IdProjet = pr.IdProjet
        $whereClause 
        ORDER BY a.DateAffectation DESC";
$result = mysqli_query($conn, $sql);

// Récupérer la liste du personnel pour le filtre
$personnelSql = "SELECT * FROM PERSONNEL ORDER BY NomPersonnel, PrenomPersonnel";
$personnelResult = mysqli_query($conn, $personnelSql);

// Récupérer la liste des tâches pour le filtre
$tachesSql = "SELECT t.*, pr.TitreProjet FROM TACHE t JOIN PROJET pr ON t.IdProjet = pr.IdProjet ORDER BY t.DateDebutTache DESC";
$tachesResult = mysqli_query($conn, $tachesSql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Affectations - Gestion de Projets</title>
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
                    <h1 class="h2">Gestion des Affectations</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="create.php" class="btn btn-sm btn-primary me-2">
                            <i class="bi bi-plus-circle"></i> Nouvelle affectation
                        </a>
                        <a href="export_csv.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-file-earmark-excel"></i> Exporter CSV
                        </a>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <form action="index.php" method="get" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select name="personnel" class="form-select">
                                    <option value="">Tout le personnel</option>
                                    <?php 
                                    mysqli_data_seek($personnelResult, 0);
                                    while($personnelRow = mysqli_fetch_assoc($personnelResult)): 
                                    ?>
                                    <option value="<?php echo $personnelRow['MatriculePersonnel']; ?>" <?php echo $personnel == $personnelRow['MatriculePersonnel'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($personnelRow['NomPersonnel'] . ' ' . $personnelRow['PrenomPersonnel']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="tache" class="form-select">
                                    <option value="">Toutes les tâches</option>
                                    <?php 
                                    mysqli_data_seek($tachesResult, 0);
                                    while($tacheRow = mysqli_fetch_assoc($tachesResult)): 
                                    ?>
                                    <option value="<?php echo $tacheRow['IdTache']; ?>" <?php echo $tache == $tacheRow['IdTache'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tacheRow['LibelleTache'] . ' (' . $tacheRow['TitreProjet'] . ')'); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-outline-primary">Rechercher</button>
                                <a href="index.php" class="btn btn-outline-secondary">Réinitialiser</a>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                <div class="alert alert-success" role="alert">
                    L'affectation a été supprimée avec succès.
                </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Personnel</th>
                                <th>Projet</th>
                                <th>Tâche</th>
                                <th>Date d'affectation</th>
                                <th>État de la tâche</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    $affectationId = $row['IdAffectation'];
                                    
                                    echo "<tr>";
                                    echo "<td>" . $row['IdAffectation'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['PrenomPersonnel'] . ' ' . $row['NomPersonnel']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['TitreProjet']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['LibelleTache']) . "</td>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['DateAffectation'])) . "</td>";
                                    echo "<td>";
                                    echo "<span class='badge " . ($row['EtatTache'] == 'Terminée' ? 'bg-success' : ($row['EtatTache'] == 'En cours' ? 'bg-primary' : 'bg-warning')) . "'>";
                                    echo htmlspecialchars($row['EtatTache']);
                                    echo "</span>";
                                    echo "</td>";
                                    echo "<td>";
                                    echo "<a href='view.php?id=" . $affectationId . "' class='btn btn-sm btn-info me-1'><i class='bi bi-eye'></i></a>";
                                    echo "<a href='edit.php?id=" . $affectationId . "' class='btn btn-sm btn-warning me-1'><i class='bi bi-pencil'></i></a>";
                                    echo "<a href='delete.php?id=" . $affectationId . "' class='btn btn-sm btn-danger'><i class='bi bi-trash'></i></a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>Aucune affectation trouvée</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
