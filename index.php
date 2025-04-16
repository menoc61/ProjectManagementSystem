<?php
require_once 'config.php';
redirectIfNotLoggedIn();

$role = $_SESSION['role'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Gestion de Projets</title>
<link href="./libs/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="./libs/bootstrap-icons-1.10.0/font/bootstrap-icons.css">
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
        .welcome-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
                            <a class="nav-link active" href="index.php">
                                <i class="bi bi-house-door"></i> Accueil
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard/index.php">
                                <i class="bi bi-speedometer2"></i> Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="clients/index.php">
                                <i class="bi bi-people"></i> Clients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="projets/index.php">
                                <i class="bi bi-folder"></i> Projets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="taches/index.php">
                                <i class="bi bi-list-check"></i> Tâches
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reglements/index.php">
                                <i class="bi bi-cash-coin"></i> Règlements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="typesprojet/index.php">
                                <i class="bi bi-tags"></i> Types de projet
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="services/index.php">
                                <i class="bi bi-gear"></i> Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="personnel/index.php">
                                <i class="bi bi-person-badge"></i> Personnel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="affectations/index.php">
                                <i class="bi bi-calendar-check"></i> Affectations
                            </a>
                        </li>
                        <?php if ($role == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="utilisateurs/index.php">
                                <i class="bi bi-person-lock"></i> Utilisateurs
                            </a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Déconnexion
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Accueil</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <span class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-person"></i> <?php echo htmlspecialchars($username); ?> (<?php echo htmlspecialchars($role); ?>)
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card welcome-card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Bienvenue dans l'application de gestion de projets</h5>
                                <p class="card-text">
                                    Cette application vous permet de gérer efficacement vos projets, clients, tâches et ressources.
                                    Utilisez le menu de navigation à gauche pour accéder aux différentes fonctionnalités.
                                </p>
                                <a href="dashboard/index.php" class="btn btn-primary">
                                    <i class="bi bi-speedometer2"></i> Accéder au tableau de bord
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Projets récents</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>Titre</th>
                                                <th>Client</th>
                                                <th>État</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT p.IdProjet, p.TitreProjet, c.NomClient, p.EtatProjet 
                                                    FROM PROJET p 
                                                    JOIN CLIENT c ON p.IdClient = c.IdClient 
                                                    ORDER BY p.IdProjet DESC LIMIT 5";
                                            $result = mysqli_query($conn, $sql);
                                            
                                            if (mysqli_num_rows($result) > 0) {
                                                while($row = mysqli_fetch_assoc($result)) {
                                                    $etat = "";
                                                    switch($row['EtatProjet']) {
                                                        case 1: $etat = "<span class='badge bg-primary'>En cours</span>"; break;
                                                        case 2: $etat = "<span class='badge bg-success'>Terminé</span>"; break;
                                                        case 3: $etat = "<span class='badge bg-danger'>Annulé</span>"; break;
                                                    }
                                                    echo "<tr>";
                                                    echo "<td><a href='projets/view.php?id=" . $row['IdProjet'] . "'>" . htmlspecialchars($row['TitreProjet']) . "</a></td>";
                                                    echo "<td>" . htmlspecialchars($row['NomClient']) . "</td>";
                                                    echo "<td>" . $etat . "</td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='3' class='text-center'>Aucun projet trouvé</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="projets/index.php" class="btn btn-sm btn-outline-primary">Voir tous les projets</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Tâches à venir</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tâche</th>
                                                <th>Projet</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sql = "SELECT t.IdTache, t.LibelleTache, p.TitreProjet, t.DateDebutTache 
                                                    FROM TACHE t 
                                                    JOIN PROJET p ON t.IdProjet = p.IdProjet 
                                                    WHERE t.EtatTache IN (1, 2) 
                                                    ORDER BY t.DateDebutTache ASC LIMIT 5";
                                            $result = mysqli_query($conn, $sql);
                                            
                                            if (mysqli_num_rows($result) > 0) {
                                                while($row = mysqli_fetch_assoc($result)) {
                                                    echo "<tr>";
                                                    echo "<td><a href='taches/view.php?id=" . $row['IdTache'] . "'>" . htmlspecialchars($row['LibelleTache']) . "</a></td>";
                                                    echo "<td>" . htmlspecialchars($row['TitreProjet']) . "</td>";
                                                    echo "<td>" . date('d/m/Y', strtotime($row['DateDebutTache'])) . "</td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='3' class='text-center'>Aucune tâche trouvée</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="taches/index.php" class="btn btn-sm btn-outline-primary">Voir toutes les tâches</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="./libs/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
