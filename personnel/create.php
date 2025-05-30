<?php
require_once '../config.php';
redirectIfNotLoggedIn();

$error = '';
$success = '';

// Récupérer la liste des services
$servicesSql = "SELECT * FROM SERVICE ORDER BY LibelleService";
$servicesResult = mysqli_query($conn, $servicesSql);

// Vérifier si on vient d'un service spécifique
$preselectedService = isset($_GET['service']) ? $_GET['service'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matriculePersonnel = uniqid('EMP'); // Générer un matricule unique
    $nomPersonnel = $_POST['nomPersonnel'];
    $prenomPersonnel = $_POST['prenomPersonnel'];
    $emailPersonnel = $_POST['emailPersonnel'];
    $telPersonnel = $_POST['telPersonnel'];
    $codeService = $_POST['codeService'] ?: 'NULL';
    $competencesPersonnel = $_POST['competencesPersonnel'];
    $commentairesPersonnel = $_POST['commentairesPersonnel'];
    
    // Si codeService est NULL, on doit le traiter différemment dans la requête SQL
    if ($codeService === 'NULL') {
        $sql = "INSERT INTO PERSONNEL (MatriculePersonnel, NomPersonnel, PrenomPersonnel, EmailPersonnel, TelPersonnel, CodeService, CompetencesPersonnel, CommentairesPersonnel) 
                VALUES ('$matriculePersonnel', '$nomPersonnel', '$prenomPersonnel', '$emailPersonnel', '$telPersonnel', NULL, '$competencesPersonnel', '$commentairesPersonnel')";
    } else {
        $sql = "INSERT INTO PERSONNEL (MatriculePersonnel, NomPersonnel, PrenomPersonnel, EmailPersonnel, TelPersonnel, CodeService, CompetencesPersonnel, CommentairesPersonnel) 
                VALUES ('$matriculePersonnel', '$nomPersonnel', '$prenomPersonnel', '$emailPersonnel', '$telPersonnel', '$codeService', '$competencesPersonnel', '$commentairesPersonnel')";
    }
    
    if (mysqli_query($conn, $sql)) {
        $newPersonnelId = mysqli_insert_id($conn);
        $success = "Membre du personnel ajouté avec succès";
    } else {
        $error = "Erreur lors de l'ajout du membre du personnel: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Membre du Personnel - Gestion de Projets</title>
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
                    <h1 class="h2">Ajouter un Membre du Personnel</h1>
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
                    <a href="view.php?id=<?php echo $newPersonnelId; ?>" class="alert-link">Voir le membre du personnel</a> ou 
                    <a href="index.php" class="alert-link">retourner à la liste du personnel</a>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="post" action="create.php">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nomPersonnel" class="form-label">Nom *</label>
                                    <input type="text" class="form-control" id="nomPersonnel" name="nomPersonnel" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="prenomPersonnel" class="form-label">Prénom *</label>
                                    <input type="text" class="form-control" id="prenomPersonnel" name="prenomPersonnel" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="emailPersonnel" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="emailPersonnel" name="emailPersonnel" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telPersonnel" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="telPersonnel" name="telPersonnel">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="codeService" class="form-label">Service</label>
                                <select class="form-select" id="codeService" name="codeService">
                                    <option value="">Aucun service</option>
                                    <?php 
                                    mysqli_data_seek($servicesResult, 0);
                                    while($service = mysqli_fetch_assoc($servicesResult)): 
                                    ?>
                                    <option value="<?php echo $service['CodeService']; ?>" <?php echo $preselectedService == $service['CodeService'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($service['LibelleService']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="competencesPersonnel" class="form-label">Compétences</label>
                                <textarea class="form-control" id="competencesPersonnel" name="competencesPersonnel" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="commentairesPersonnel" class="form-label">Commentaires</label>
                                <textarea class="form-control" id="commentairesPersonnel" name="commentairesPersonnel" rows="3"></textarea>
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
