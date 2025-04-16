<?php
require_once '../config.php';
redirectIfNotLoggedIn();

// Vérifier que l'utilisateur est admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : '';

$where = [];
if (!empty($search)) {
    $where[] = "(NomUtilisateur LIKE '%$search%')";
}
if (!empty($role)) {
    $where[] = "Role = '$role'";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT * FROM UTILISATEUR $whereClause ORDER BY NomUtilisateur";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Gestion de Projets</title>
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
                        <li class="nav-item">
                            <a class="nav-link active" href="../utilisateurs/index.php">
                                <i class="bi bi-person-lock"></i> Utilisateurs
                            </a>
                        </li>
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
                    <h1 class="h2">Gestion des Utilisateurs</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="create.php" class="btn btn-sm btn-primary me-2">
                            <i class="bi bi-plus-circle"></i> Nouvel utilisateur
                        </a>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <form action="index.php" method="get" class="row g-3">
                            <div class="col-md-5">
                                <input type="text" name="search" class="form-control" placeholder="Rechercher par nom..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <select name="role" class="form-select">
                                    <option value="">Tous les rôles</option>
                                    <option value="admin" <?php echo $role == 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                                    <option value="personnel" <?php echo $role == 'personnel' ? 'selected' : ''; ?>>Personnel</option>
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
                    L'utilisateur a été supprimé avec succès.
                </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Rôle</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    $userId = $row['IdUtilisateur'];
                                    
                                    echo "<tr>";
                                    echo "<td>" . $row['IdUtilisateur'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['NomUtilisateur']) . "</td>";
                                    echo "<td>";
                                    if ($row['Role'] == 'admin') {
                                        echo "<span class='badge bg-danger'>Administrateur</span>";
                                    } else {
                                        echo "<span class='badge bg-primary'>Personnel</span>";
                                    }
                                    echo "</td>";
                                    echo "<td>";
                                    echo "<a href='edit.php?id=" . $userId . "' class='btn btn-sm btn-warning me-1'><i class='bi bi-pencil'></i></a>";
                                    // Ne pas permettre de supprimer son propre compte
                                    if ($userId != $_SESSION['user_id']) {
                                        echo "<a href='delete.php?id=" . $userId . "' class='btn btn-sm btn-danger'><i class='bi bi-trash'></i></a>";
                                    }
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>Aucun utilisateur trouvé</td></tr>";
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
