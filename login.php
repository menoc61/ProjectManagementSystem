<?php
require_once 'config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM UTILISATEUR WHERE NomUtilisateur = '$username'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['MotDePasse'])) {
            $_SESSION['user_id'] = $row['IdUtilisateur'];
            $_SESSION['username'] = $row['NomUtilisateur'];
            $_SESSION['role'] = $row['Role'];
            
            if ($row['Role'] == 'admin') {
                header("Location: dashboard/index.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Mot de passe incorrect";
        }
    } else {
        $error = "Nom d'utilisateur non trouvé";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion de Projets</title>
    <link href="./libs/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./libs/bootstrap-icons-1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            height: 100vh;
            background-color: #f8f9fa;
        }
        .login-container {
            height: 100%;
        }
        .login-image {
            background: url('./assets/img/login.jpg') center/cover no-repeat;
            height: 100%;
        }
        .login-form {
            padding: 2rem;
        }
        .login-title {
            margin-bottom: 2rem;
            color: #0d6efd;
        }
        @media (max-width: 767.98px) {
            .login-image {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid h-100 p-0">
        <div class="row login-container g-0">
            <div class="col-md-6 login-image d-none d-md-block"></div>
            <div class="col-md-6 d-flex align-items-center">
                <div class="login-form w-100">
                    <h1 class="login-title text-center">Gestion de Projets</h1>
                    
                    <?php if($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="post" action="login.php">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Se connecter</button>
                        </div>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <p class="text-muted">2025 ©️ Application de gestion de projets d'entreprise</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../libs/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordField = document.getElementById('password');
            const passwordFieldType = passwordField.getAttribute('type');
            const icon = this.querySelector('i');

            if (passwordFieldType === 'password') {
                passwordField.setAttribute('type', 'text');
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordField.setAttribute('type', 'password');
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    </script>
</body>
</html>
