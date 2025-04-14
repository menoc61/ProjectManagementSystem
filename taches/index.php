<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$projet = isset($_GET['projet']) ? $_GET['projet'] : '';
$etat = isset($_GET['etat']) ? $_GET['etat'] : '';

$where = [];
if (!empty($search)) {
    $where[] = "(LibelleTache LIKE '%$search%' OR DescriptionTache LIKE '%$search%')";
}
if (!empty($projet)) {
    $where[] = "t.IdProjet = $projet";
}
if (!empty($etat)) {
    $where[] = "t.EtatTache = $etat";
}

$whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT t.*, p.TitreProjet, p.IdClient, c.NomClient 
        FROM TACHE t 
        JOIN PROJET p ON t.IdProjet = p.IdProjet 
        JOIN CLIENT c ON p.IdClient = c.IdClient 
        $whereClause 
        ORDER BY t.IdTache DESC";
$result = mysqli_query($conn, $sql);

// Récupérer la liste des projets pour le filtre
$projetsSql = "SELECT p.IdProjet, p.TitreProjet, c.NomClient 
              FROM PROJET p 
              JOIN CLIENT c ON p.IdClient = c.IdClient 
              ORDER BY p.TitreProjet";
$projetsResult = mysqli_query($conn, $projetsSql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Tâches - Gestion de Projets</title>
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
                            <a class="nav-link active" href="../taches/index.php">
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
                    <h1 class="h2">Gestion des Tâches</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="create.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Nouvelle Tâche
                        </a>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <form action="index.php" method="get" class="row g-3">
                            <div class="col-md-5">
                                <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <select name="projet" class="form-select">
                                    <option value="">Tous les projets</option>
                                    <?php while($projetRow = mysqli_fetch_assoc($projetsResult)): ?>
                                    <option value="<?php echo $projetRow['IdProjet']; ?>" <?php echo $projet == $projetRow['IdProjet'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($projetRow['TitreProjet']); ?> (<?php echo htmlspecialchars($projetRow['NomClient']); ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="etat" class="form-select">
                                    <option value="">Tous les états</option>
                                    <option value="1" <?php echo $etat == '1' ? 'selected' : ''; ?>>À faire</option>
                                    <option value="2" <?php echo $etat == '2' ? 'selected' : ''; ?>>En cours</option>
                                    <option value="3" <?php echo $etat == '3' ? 'selected' : ''; ?>>Terminée</option>
                                    <option value="4" <?php echo $etat == '4' ? 'selected' : ''; ?>>Annulée</option>
                                </select>
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

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Libellé</th>
                                <th>Projet</th>
                                <th>Client</th>
                                <th>Date d'enregistrement</th>
                                <th>Date de début</th>
                                <th>Date de fin</th>
                                <th>État</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    $tacheId = $row['IdTache'];
                                    
                                    $etatLabel = "";
                                    switch($row['EtatTache']) {
                                        case 1: $etatLabel = "<span class='badge bg-secondary'>À faire</span>"; break;
                                        case 2: $etatLabel = "<span class='badge bg-primary'>En cours</span>"; break;
                                        case 3: $etatLabel = "<span class='badge bg-success'>Terminée</span>"; break;
                                        case 4: $etatLabel = "<span class='badge bg-danger'>Annulée</span>"; break;
                                    }
                                    
                                    echo "<tr>";
                                    echo "<td>" . $row['IdTache'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['LibelleTache']) . "</td>";
                                    echo "<td><a href='../projets/view.php?id=" . $row['IdProjet'] . "'>" . htmlspecialchars($row['TitreProjet']) . "</a></td>";
                                    echo "<td><a href='../clients/view.php?id=" . $row['IdClient'] . "'>" . htmlspecialchars($row['NomClient']) . "</a></td>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['DateEnregTache'])) . "</td>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['DateDebutTache'])) . "</td>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['DateFinTache'])) . "</td>";
                                    echo "<td>" . $etatLabel . "</td>";
                                    echo "<td>";
                                    echo "<a href='view.php?id=" . $tacheId . "' class='btn btn-sm btn-info me-1'><i class='bi bi-eye'></i></a>";
                                    echo "<a href='edit.php?id=" . $tacheId . "' class='btn btn-sm btn-warning me-1'><i class='bi bi-pencil'></i></a>";
                                    echo "<a href='delete.php?id=" . $tacheId . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer cette tâche ?\")'><i class='bi bi-trash'></i></a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center'>Aucune tâche trouvée</td></tr>";
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
