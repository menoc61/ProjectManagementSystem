<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$service = isset($_GET['service']) ? $_GET['service'] : '';

$where = [];
if (!empty($search)) {
    $where[] = "(p.NomPersonnel LIKE '%$search%' OR p.PrenomPersonnel LIKE '%$search%' OR p.EmailPersonnel LIKE '%$search%')";
}
if (!empty($service)) {
    $where[] = "p.CodeService = '$service'";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT p.*, s.LibelleService 
        FROM PERSONNEL p 
        LEFT JOIN SERVICE s ON p.CodeService = s.CodeService 
        $whereClause 
        ORDER BY p.NomPersonnel, p.PrenomPersonnel";
$result = mysqli_query($conn, $sql);

// Récupérer la liste des services pour le filtre
$servicesSql = "SELECT * FROM SERVICE ORDER BY LibelleService";
$servicesResult = mysqli_query($conn, $servicesSql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Personnel - Gestion de Projets</title>
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
                    <h1 class="h2">Gestion du Personnel</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="create.php" class="btn btn-sm btn-primary me-2">
                            <i class="bi bi-plus-circle"></i> Nouveau membre
                        </a>
                        <a href="export_csv.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-file-earmark-excel"></i> Exporter CSV
                        </a>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <form action="index.php" method="get" class="row g-3">
                            <div class="col-md-5">
                                <input type="text" name="search" class="form-control" placeholder="Rechercher par nom, prénom ou email..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <select name="service" class="form-select">
                                    <option value="">Tous les services</option>
                                    <?php 
                                    mysqli_data_seek($servicesResult, 0);
                                    while($serviceRow = mysqli_fetch_assoc($servicesResult)): 
                                    ?>
                                    <option value="<?php echo $serviceRow['CodeService']; ?>" <?php echo $service == $serviceRow['CodeService'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($serviceRow['LibelleService']); ?>
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
                    Le membre du personnel a été supprimé avec succès.
                </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Matricule</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Service</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    $personnelId = $row['MatriculePersonnel'];
                                    
                                    echo "<tr>";
                                    echo "<td>" . $row['MatriculePersonnel'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['NomPersonnel']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['PrenomPersonnel']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['EmailPersonnel']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['TelPersonnel']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['LibelleService'] ?: 'Non assigné') . "</td>";
                                    echo "<td>";
                                    echo "<a href='view.php?id=" . $personnelId . "' class='btn btn-sm btn-info me-1'><i class='bi bi-eye'></i></a>";
                                    echo "<a href='edit.php?id=" . $personnelId . "' class='btn btn-sm btn-warning me-1'><i class='bi bi-pencil'></i></a>";
                                    echo "<a href='delete.php?id=" . $personnelId . "' class='btn btn-sm btn-danger'><i class='bi bi-trash'></i></a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>Aucun membre du personnel trouvé</td></tr>";
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
