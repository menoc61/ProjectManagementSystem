<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$error = '';
$success = '';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Récupérer la liste des projets
$projetsSql = "SELECT p.IdProjet, p.TitreProjet, c.NomClient 
              FROM PROJET p 
              JOIN CLIENT c ON p.IdClient = c.IdClient 
              ORDER BY p.TitreProjet";
$projetsResult = mysqli_query($conn, $projetsSql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $libelleTache = $_POST['libelleTache'];
    $dateDebutTache = $_POST['dateDebutTache'];
    $dateFinTache = $_POST['dateFinTache'];
    $etatTache = $_POST['etatTache'];
    $idProjet = $_POST['idProjet'];

    $sql = "UPDATE TACHE SET 
            LibelleTache = '$libelleTache', 
            DateDebutTache = '$dateDebutTache', 
            DateFinTache = '$dateFinTache', 
            EtatTache = '$etatTache', 
            IdProjet = '$idProjet' 
            WHERE IdTache = $id";

    if (mysqli_query($conn, $sql)) {
        $success = "Tâche modifiée avec succès";
    } else {
        $error = "Erreur lors de la modification de la tâche: " . mysqli_error($conn);
    }
}

$sql = "SELECT * FROM TACHE WHERE IdTache = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$tache = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Tâche - Gestion de Projets</title>
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
                    <h1 class="h2">Modifier une Tâche</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="index.php" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Retour à la liste
                        </a>
                        <a href="view.php?id=<?php echo $id; ?>" class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i> Voir la tâche
                        </a>
                    </div>
                </div>

                <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success; ?>
                    <a href="view.php?id=<?php echo $id; ?>" class="alert-link">Voir la tâche</a> ou 
                    <a href="index.php" class="alert-link">retourner à la liste des tâches</a>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="post" action="edit.php?id=<?php echo $id; ?>">
                            <div class="mb-3">
                                <label for="idProjet" class="form-label">Projet *</label>
                                <select class="form-select" id="idProjet" name="idProjet" required>
                                    <option value="">Sélectionner un projet</option>
                                    <?php 
                                    mysqli_data_seek($projetsResult, 0);
                                    while ($projet = mysqli_fetch_assoc($projetsResult)): 
                                    ?>
                                    <option value="<?php echo $projet['IdProjet']; ?>" <?php echo $tache['IdProjet'] == $projet['IdProjet'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($projet['TitreProjet']); ?> (<?php echo htmlspecialchars($projet['NomClient']); ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="libelleTache" class="form-label">Libellé de la tâche *</label>
                                <input type="text" class="form-control" id="libelleTache" name="libelleTache" value="<?php echo htmlspecialchars($tache['LibelleTache']); ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dateDebutTache" class="form-label">Date de début *</label>
                                    <input type="date" class="form-control" id="dateDebutTache" name="dateDebutTache" value="<?php echo $tache['DateDebutTache']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="dateFinTache" class="form-label">Date de fin *</label>
                                    <input type="date" class="form-control" id="dateFinTache" name="dateFinTache" value="<?php echo $tache['DateFinTache']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="etatTache" class="form-label">État de la tâche *</label>
                                <select class="form-select" id="etatTache" name="etatTache" required>
                                    <option value="1" <?php echo $tache['EtatTache'] == 1 ? 'selected' : ''; ?>>À faire</option>
                                    <option value="2" <?php echo $tache['EtatTache'] == 2 ? 'selected' : ''; ?>>En cours</option>
                                    <option value="3" <?php echo $tache['EtatTache'] == 3 ? 'selected' : ''; ?>>Terminée</option>
                                    <option value="4" <?php echo $tache['EtatTache'] == 4 ? 'selected' : ''; ?>>Annulée</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                            <a href="index.php" class="btn btn-secondary">Annuler</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>