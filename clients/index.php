<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if (!empty($search)) {
    $where = "WHERE NomClient LIKE '%$search%' OR EmailClient LIKE '%$search%' OR TelClient LIKE '%$search%'";
}

$sql = "SELECT * FROM CLIENT $where ORDER BY IdClient DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Clients - Gestion de Projets</title>
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
                    <h1 class="h2">Gestion des Clients</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="create.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Nouveau Client
                        </a>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <form action="index.php" method="get" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" placeholder="Rechercher un client..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-outline-primary">Rechercher</button>
                        </form>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="export_csv.php" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-file-earmark-excel"></i> Exporter CSV
                        </a>
                        <a href="export_pdf.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-file-earmark-pdf"></i> Exporter PDF
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Projets</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    $clientId = $row['IdClient'];
                                    
                                    $projetsSql = "SELECT COUNT(*) as count FROM PROJET WHERE IdClient = $clientId";
                                    $projetsResult = mysqli_query($conn, $projetsSql);
                                    $projetsCount = mysqli_fetch_assoc($projetsResult)['count'];
                                    
                                    echo "<tr>";
                                    echo "<td>" . $row['IdClient'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['NomClient']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['EmailClient']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['TelClient']) . "</td>";
                                    echo "<td><a href='../projets/index.php?client=" . $clientId . "'>" . $projetsCount . " projet(s)</a></td>";
                                    echo "<td>";
                                    echo "<a href='view.php?id=" . $clientId . "' class='btn btn-sm btn-info me-1'><i class='bi bi-eye'></i></a>";
                                    echo "<a href='edit.php?id=" . $clientId . "' class='btn btn-sm btn-warning me-1'><i class='bi bi-pencil'></i></a>";
                                    echo "<a href='delete.php?id=" . $clientId . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce client ?\")'><i class='bi bi-trash'></i></a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>Aucun client trouvé</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<script src="../libs/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
