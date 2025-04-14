## Guide d'installation rapide

Ce guide vous aidera à installer et configurer rapidement l'application de gestion de projets d'entreprise.

### Étape 1 : Préparer la base de données

1. Créez une base de données MySQL nommée `GestionProjetsEntreprise`
2. Importez le fichier `database.sql` pour créer les tables et insérer les données d'exemple

### Étape 2 : Configurer l'application

1. Modifiez le fichier `config.php` avec vos paramètres de connexion à la base de données :
   ```php
   $servername = "localhost"; // Adresse du serveur MySQL
   $username = "votre_utilisateur"; // Nom d'utilisateur MySQL
   $password = "votre_mot_de_passe"; // Mot de passe MySQL
   $dbname = "GestionProjetsEntreprise"; // Nom de la base de données
   ```

### Étape 3 : Déployer l'application

1. Placez tous les fichiers sur votre serveur web
2. Assurez-vous que le serveur web a les permissions d'écriture sur les dossiers nécessaires

### Étape 4 : Accéder à l'application

1. Ouvrez votre navigateur et accédez à l'URL de l'application
2. Connectez-vous avec l'un des comptes par défaut :
   - **Administrateur** : admin@example.com / admin123
   - **Personnel** : user@example.com / user123

### Étape 5 : Vérifier l'installation

1. Accédez à la page de tests (`/tests/index.php`) pour vérifier que tout fonctionne correctement
2. Explorez le tableau de bord pour vous assurer que les données sont correctement affichées

### Besoin d'aide ?

Consultez la documentation complète (`documentation.md`) pour plus de détails sur l'utilisation et la maintenance de l'application.
