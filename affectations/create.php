<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$error = '';
$success = '';

// Récupérer la liste du personnel
$personnelSql = "SELECT * FROM PERSONNEL ORDER BY NomPersonnel, PrenomPersonnel";
$personnelResult = mysqli_query($conn, $personnelSql);

// Récupérer la liste des tâches
$tachesSql = "SELECT t.*, p.TitreProjet 
              FROM TACHE t 
              JOIN PROJET p ON t.IdProjet = p.IdProjet 
              WHERE t.EtatTache != 3 -- Exclure les tâches terminées
              ORDER BY t.DateDebutTache";
$tachesResult = mysqli_query($conn, $tachesSql);

// Vérifier si on vient d'un personnel spécifique
$preselectedPersonnel = isset($_GET['personnel']) ? $_GET['personnel'] : '';
// Vérifier si on vient d'une tâche spécifique
$preselectedTache = isset($_GET['tache']) ? $_GET['tache'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matriculePersonnel = $_POST['matriculePersonnel'];
    $idTache = $_POST['idTache'];
    $dateAffectation = $_POST['dateAffectation'] ?: date('Y-m-d');
    $fonctionAffectation = $_POST['fonctionAffectation'];

    // Vérifier si cette affectation existe déjà
    $checkSql = "SELECT * FROM AFFECTATION WHERE MatriculePersonnel = '$matriculePersonnel' AND IdTache = $idTache";
    $checkResult = mysqli_query($conn, $checkSql);

    if (mysqli_num_rows($checkResult) > 0) {
        $error = "Cette affectation existe déjà.";
    } else {
        $sql = "INSERT INTO AFFECTATION (MatriculePersonnel, IdTache, DateAffectation, FonctionAffectation) 
                VALUES ('$matriculePersonnel', $idTache, '$dateAffectation', '$fonctionAffectation')";

        if (mysqli_query($conn, $sql)) {
            $newAffectationId = mysqli_insert_id($conn);
            $success = "Affectation ajoutée avec succès";
        } else {
            $error = "Erreur lors de l'ajout de l'affectation: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Affectation - Gestion de Projets</title>
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
                    <h1 class="h2">Ajouter une Affectation</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="index.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>

                <?php if($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <?php if($success): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success; ?>
                    <a href="view.php?id=<?php echo $newAffectationId; ?>" class="alert-link">Voir l'affectation</a> ou 
                    <a href="index.php" class="alert-link">retourner à la liste des affectations</a>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="post" action="create.php">
                            <div class="mb-3">
                                <label for="matriculePersonnel" class="form-label">Personnel *</label>
                                <select class="form-select" id="matriculePersonnel" name="matriculePersonnel" required>
                                    <option value="">Sélectionner un membre du personnel</option>
                                    <?php 
                                    mysqli_data_seek($personnelResult, 0);
                                    while($personnel = mysqli_fetch_assoc($personnelResult)): 
                                    ?>
                                    <option value="<?php echo $personnel['MatriculePersonnel']; ?>" <?php echo $preselectedPersonnel == $personnel['MatriculePersonnel'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($personnel['NomPersonnel'] . ' ' . $personnel['PrenomPersonnel']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="idTache" class="form-label">Tâche *</label>
                                <select class="form-select" id="idTache" name="idTache" required>
                                    <option value="">Sélectionner une tâche</option>
                                    <?php 
                                    mysqli_data_seek($tachesResult, 0);
                                    while($tache = mysqli_fetch_assoc($tachesResult)): 
                                    ?>
                                    <option value="<?php echo $tache['IdTache']; ?>" <?php echo $preselectedTache == $tache['IdTache'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($tache['LibelleTache'] . ' - ' . $tache['TitreProjet']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="dateAffectation" class="form-label">Date d'affectation</label>
                                <input type="date" class="form-control" id="dateAffectation" name="dateAffectation" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="fonctionAffectation" class="form-label">Fonction</label>
                                <input type="text" class="form-control" id="fonctionAffectation" name="fonctionAffectation" placeholder="Ex: Développeur principal">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                            <a href="index.php" class="btn btn-secondary">Annuler</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../libs/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
