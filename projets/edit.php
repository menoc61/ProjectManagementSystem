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

// Récupérer la liste des clients
$clientsSql = "SELECT * FROM CLIENT ORDER BY NomClient";
$clientsResult = mysqli_query($conn, $clientsSql);

// Récupérer la liste des types de projet
$typesSql = "SELECT * FROM TYPEPROJET ORDER BY LibelleTypeProjet";
$typesResult = mysqli_query($conn, $typesSql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titreProjet = $_POST['titreProjet'];
    $descriptionProjet = $_POST['descriptionProjet'];
    $coutProjet = $_POST['coutProjet'];
    $dateDebutProjet = $_POST['dateDebutProjet'];
    $dateFinProjet = $_POST['dateFinProjet'];
    $etatProjet = $_POST['etatProjet'];
    $idClient = $_POST['idClient'];
    $idTypeProjet = $_POST['idTypeProjet'];
    
    $sql = "UPDATE PROJET SET 
            TitreProjet = '$titreProjet', 
            DescriptionProjet = '$descriptionProjet', 
            CoutProjet = '$coutProjet', 
            DateDebutProjet = '$dateDebutProjet', 
            DateFinProjet = '$dateFinProjet', 
            EtatProjet = '$etatProjet', 
            IdClient = '$idClient', 
            IdTypeProjet = '$idTypeProjet' 
            WHERE IdProjet = $id";
    
    if (mysqli_query($conn, $sql)) {
        $success = "Projet modifié avec succès";
    } else {
        $error = "Erreur lors de la modification du projet: " . mysqli_error($conn);
    }
}

$sql = "SELECT * FROM PROJET WHERE IdProjet = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$projet = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Projet - Gestion de Projets</title>
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
                            <a class="nav-link active" href="../projets/index.php">
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
                    <h1 class="h2">Modifier un Projet</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="index.php" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Retour à la liste
                        </a>
                        <a href="view.php?id=<?php echo $id; ?>" class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i> Voir le projet
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
                    <a href="view.php?id=<?php echo $id; ?>" class="alert-link">Voir le projet</a> ou 
                    <a href="index.php" class="alert-link">retourner à la liste des projets</a>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="post" action="edit.php?id=<?php echo $id; ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="titreProjet" class="form-label">Titre du projet *</label>
                                    <input type="text" class="form-control" id="titreProjet" name="titreProjet" value="<?php echo htmlspecialchars($projet['TitreProjet']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="idClient" class="form-label">Client *</label>
                                    <select class="form-select" id="idClient" name="idClient" required>
                                        <option value="">Sélectionner un client</option>
                                        <?php 
                                        mysqli_data_seek($clientsResult, 0);
                                        while($client = mysqli_fetch_assoc($clientsResult)): 
                                        ?>
                                        <option value="<?php echo $client['IdClient']; ?>" <?php echo $projet['IdClient'] == $client['IdClient'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($client['NomClient']); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="idTypeProjet" class="form-label">Type de projet *</label>
                                    <select class="form-select" id="idTypeProjet" name="idTypeProjet" required>
                                        <option value="">Sélectionner un type</option>
                                        <?php 
                                        mysqli_data_seek($typesResult, 0);
                                        while($type = mysqli_fetch_assoc($typesResult)): 
                                        ?>
                                        <option value="<?php echo $type['IdTypeProjet']; ?>" <?php echo $projet['IdTypeProjet'] == $type['IdTypeProjet'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type['LibelleTypeProjet']); ?> 
                                            (Forfait: <?php echo number_format($type['ForfaitCoutTypeProjet'], 2, ',', ' '); ?> FCFA)
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="coutProjet" class="form-label">Coût du projet (FCFA) *</label>
                                    <input type="number" class="form-control" id="coutProjet" name="coutProjet" min="0" step="100" value="<?php echo $projet['CoutProjet']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="dateDebutProjet" class="form-label">Date de début *</label>
                                    <input type="date" class="form-control" id="dateDebutProjet" name="dateDebutProjet" value="<?php echo $projet['DateDebutProjet']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="dateFinProjet" class="form-label">Date de fin *</label>
                                    <input type="date" class="form-control" id="dateFinProjet" name="dateFinProjet" value="<?php echo $projet['DateFinProjet']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="etatProjet" class="form-label">État du projet *</label>
                                <select class="form-select" id="etatProjet" name="etatProjet" required>
                                    <option value="1" <?php echo $projet['EtatProjet'] == 1 ? 'selected' : ''; ?>>En cours</option>
                                    <option value="2" <?php echo $projet['EtatProjet'] == 2 ? 'selected' : ''; ?>>Terminé</option>
                                    <option value="3" <?php echo $projet['EtatProjet'] == 3 ? 'selected' : ''; ?>>Annulé</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descriptionProjet" class="form-label">Description</label>
                                <textarea class="form-control" id="descriptionProjet" name="descriptionProjet" rows="3"><?php echo htmlspecialchars($projet['DescriptionProjet']); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
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
