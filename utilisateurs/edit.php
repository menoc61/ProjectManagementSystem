<?php
require_once '../config.php';
redirectIfNotLoggedIn();

// Vérifier que l'utilisateur est admin
if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$error = '';
$success = '';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Ne pas permettre de modifier son propre compte
if ($id == $_SESSION['user_id']) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomUtilisateur = $_POST['nomUtilisateur'];
    $roleUtilisateur = $_POST['roleUtilisateur'];
    $motDePasseUtilisateur = $_POST['motDePasseUtilisateur'];

    // Vérifier si le nom d'utilisateur existe déjà (sauf pour l'utilisateur en cours)
    $checkSql = "SELECT * FROM UTILISATEUR WHERE NomUtilisateur = '$nomUtilisateur' AND IdUtilisateur != $id";
    $checkResult = mysqli_query($conn, $checkSql);

    if (mysqli_num_rows($checkResult) > 0) {
        $error = "Ce nom d'utilisateur est déjà utilisé par un autre utilisateur.";
    } else {
        // Si un nouveau mot de passe est fourni, le hasher
        if (!empty($motDePasseUtilisateur)) {
            $hashedPassword = password_hash($motDePasseUtilisateur, PASSWORD_DEFAULT);
            $sql = "UPDATE UTILISATEUR SET 
                    NomUtilisateur = '$nomUtilisateur', 
                    MotDePasse = '$hashedPassword', 
                    Role = '$roleUtilisateur' 
                    WHERE IdUtilisateur = $id";
        } else {
            $sql = "UPDATE UTILISATEUR SET 
                    NomUtilisateur = '$nomUtilisateur', 
                    Role = '$roleUtilisateur' 
                    WHERE IdUtilisateur = $id";
        }

        if (mysqli_query($conn, $sql)) {
            $success = "Utilisateur modifié avec succès";
        } else {
            $error = "Erreur lors de la modification de l'utilisateur: " . mysqli_error($conn);
        }
    }
}

$sql = "SELECT * FROM UTILISATEUR WHERE IdUtilisateur = $id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}

$utilisateur = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Utilisateur - Gestion de Projets</title>
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
                    <h1 class="h2">Modifier un Utilisateur</h1>
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
                    <a href="index.php" class="alert-link">Retourner à la liste des utilisateurs</a>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="post" action="edit.php?id=<?php echo $id; ?>">
                            <div class="mb-3">
                                <label for="nomUtilisateur" class="form-label">Nom complet *</label>
                                <input type="text" class="form-control" id="nomUtilisateur" name="nomUtilisateur" value="<?php echo htmlspecialchars($utilisateur['NomUtilisateur']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="motDePasseUtilisateur" class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control" id="motDePasseUtilisateur" name="motDePasseUtilisateur">
                                <div class="form-text">Laissez vide pour conserver le mot de passe actuel.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="roleUtilisateur" class="form-label">Rôle *</label>
                                <select class="form-select" id="roleUtilisateur" name="roleUtilisateur" required>
                                    <option value="">Sélectionner un rôle</option>
                                    <option value="admin" <?php echo $utilisateur['Role'] == 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                                    <option value="personnel" <?php echo $utilisateur['Role'] == 'personnel' ? 'selected' : ''; ?>>Personnel</option>
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

<script src="../libs/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
