<?php
require_once '../config.php';
redirectIfNotLoggedIn();

// Vérifier si on exporte une tâche spécifique ou la liste filtrée
$singleTask = isset($_GET['id']) && !empty($_GET['id']);

if ($singleTask) {
    $id = $_GET['id'];
    
    // Récupérer les informations de la tâche
    $sql = "SELECT t.*, p.TitreProjet, p.IdClient, c.NomClient 
            FROM TACHE t 
            JOIN PROJET p ON t.IdProjet = p.IdProjet 
            JOIN CLIENT c ON p.IdClient = c.IdClient 
            WHERE t.IdTache = $id";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 0) {
        header("Location: index.php");
        exit();
    }
    
    $tache = mysqli_fetch_assoc($result);
    
    // Récupérer les affectations pour cette tâche
    $affectationsSql = "SELECT a.*, p.NomPersonnel, p.PrenomPersonnel, s.LibelleService 
                       FROM AFFECTATION a 
                       JOIN PERSONNEL p ON a.IdPersonnel = p.IdPersonnel 
                       JOIN SERVICE s ON p.IdService = s.IdService 
                       WHERE a.IdTache = $id 
                       ORDER BY a.DateDebutAffectation";
    $affectationsResult = mysqli_query($conn, $affectationsSql);
    
    // Générer le nom du fichier PDF
    $filename = 'tache_' . $id . '_' . date('Y-m-d') . '.pdf';
} else {
    // Récupérer les paramètres de filtrage
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
    
    // Générer le nom du fichier PDF
    $filename = 'taches_liste_' . date('Y-m-d') . '.pdf';
}

// Créer le contenu HTML pour le PDF
ob_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export PDF - Gestion de Tâches</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        h1 {
            color: #0d6efd;
            font-size: 24px;
            margin-bottom: 20px;
        }
        h2 {
            color: #212529;
            font-size: 18px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        h3 {
            color: #495057;
            font-size: 16px;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .header {
            margin-bottom: 30px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            color: white;
        }
        .badge-primary {
            background-color: #0d6efd;
        }
        .badge-success {
            background-color: #198754;
        }
        .badge-danger {
            background-color: #dc3545;
        }
        .badge-secondary {
            background-color: #6c757d;
        }
        .tache-info {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Gestion de Projets d'Entreprise</h1>
        <p>Date d'export: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
    
    <?php if ($singleTask): ?>
    <!-- Export d'une tâche spécifique -->
    <h2>Détails de la Tâche #<?php echo $tache['IdTache']; ?></h2>
    
    <div>
        <h3><?php echo htmlspecialchars($tache['LibelleTache']); ?></h3>
        <p class="tache-info"><strong>Description:</strong> <?php echo htmlspecialchars($tache['DescriptionTache']); ?></p>
        <p class="tache-info">
            <strong>État:</strong> 
            <?php 
            switch($tache['EtatTache']) {
                case 1: echo "<span class='badge badge-secondary'>À faire</span>"; break;
                case 2: echo "<span class='badge badge-primary'>En cours</span>"; break;
                case 3: echo "<span class='badge badge-success'>Terminée</span>"; break;
                case 4: echo "<span class='badge badge-danger'>Annulée</span>"; break;
            }
            ?>
        </p>
        <p class="tache-info"><strong>Projet:</strong> <?php echo htmlspecialchars($tache['TitreProjet']); ?></p>
        <p class="tache-info"><strong>Client:</strong> <?php echo htmlspecialchars($tache['NomClient']); ?></p>
        <p class="tache-info"><strong>Date d'enregistrement:</strong> <?php echo date('d/m/Y', strtotime($tache['DateEnregTache'])); ?></p>
        <p class="tache-info"><strong>Date de début:</strong> <?php echo date('d/m/Y', strtotime($tache['DateDebutTache'])); ?></p>
        <p class="tache-info"><strong>Date de fin:</strong> <?php echo date('d/m/Y', strtotime($tache['DateFinTache'])); ?></p>
    </div>
    
    <h3>Personnel affecté à cette tâche</h3>
    <?php if (mysqli_num_rows($affectationsResult) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Personnel</th>
                <th>Service</th>
                <th>Date de début</th>
                <th>Date de fin</th>
            </tr>
        </thead>
        <tbody>
            <?php while($affectation = mysqli_fetch_assoc($affectationsResult)): ?>
            <tr>
                <td><?php echo $affectation['IdAffectation']; ?></td>
                <td><?php echo htmlspecialchars($affectation['PrenomPersonnel'] . ' ' . $affectation['NomPersonnel']); ?></td>
                <td><?php echo htmlspecialchars($affectation['LibelleService']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($affectation['DateDebutAffectation'])); ?></td>
                <td><?php echo date('d/m/Y', strtotime($affectation['DateFinAffectation'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Aucun personnel affecté à cette tâche.</p>
    <?php endif; ?>
    
    <h3>Chronologie</h3>
    <table>
        <tr>
            <th>Événement</th>
            <th>Date</th>
        </tr>
        <tr>
            <td>Enregistrement</td>
            <td><?php echo date('d/m/Y', strtotime($tache['DateEnregTache'])); ?></td>
        </tr>
        <tr>
            <td>Début</td>
            <td><?php echo date('d/m/Y', strtotime($tache['DateDebutTache'])); ?></td>
        </tr>
        <tr>
            <td>Fin prévue</td>
            <td><?php echo date('d/m/Y', strtotime($tache['DateFinTache'])); ?></td>
        </tr>
        <?php if ($tache['EtatTache'] == 3): ?>
        <tr>
            <td>Terminée</td>
            <td>✓</td>
        </tr>
        <?php elseif ($tache['EtatTache'] == 4): ?>
        <tr>
            <td>Annulée</td>
            <td>✓</td>
        </tr>
        <?php endif; ?>
    </table>
    
    <?php else: ?>
    <!-- Export de la liste des tâches -->
    <h2>Liste des Tâches</h2>
    
    <?php if (mysqli_num_rows($result) > 0): ?>
    <table>
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
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): 
                $etat = "";
                switch($row['EtatTache']) {
                    case 1: $etat = "<span class='badge badge-secondary'>À faire</span>"; break;
                    case 2: $etat = "<span class='badge badge-primary'>En cours</span>"; break;
                    case 3: $etat = "<span class='badge badge-success'>Terminée</span>"; break;
                    case 4: $etat = "<span class='badge badge-danger'>Annulée</span>"; break;
                }
            ?>
            <tr>
                <td><?php echo $row['IdTache']; ?></td>
                <td><?php echo htmlspecialchars($row['LibelleTache']); ?></td>
                <td><?php echo htmlspecialchars($row['TitreProjet']); ?></td>
                <td><?php echo htmlspecialchars($row['NomClient']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['DateEnregTache'])); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['DateDebutTache'])); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['DateFinTache'])); ?></td>
                <td><?php echo $etat; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Aucune tâche trouvée.</p>
    <?php endif; ?>
    <?php endif; ?>
    
    <div class="footer">
        <p>Document généré par l'application de Gestion de Projets d'Entreprise - <?php echo date('Y'); ?></p>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

// Inclure la bibliothèque mPDF
require_once '../vendor/autoload.php';

// Vérifier si le répertoire vendor existe, sinon l'installer
if (!file_exists('../vendor')) {
    // Créer un fichier composer.json temporaire
    file_put_contents('../composer.json', json_encode([
        'require' => [
            'mpdf/mpdf' => '^8.0'
        ]
    ]));
    
    // Installer les dépendances
    exec('cd .. && composer install');
}

try {
    // Créer une instance de mPDF
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 16,
        'margin_bottom' => 16,
        'margin_header' => 9,
        'margin_footer' => 9
    ]);
    
    // Définir les en-têtes pour le téléchargement PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Ajouter le contenu HTML
    $mpdf->WriteHTML($html);
    
    // Sortie du PDF
    $mpdf->Output($filename, 'I');
    exit;
} catch (\Mpdf\MpdfException $e) {
    // En cas d'erreur avec mPDF, utiliser une solution de secours
    echo "Erreur lors de la génération du PDF: " . $e->getMessage();
    echo "<p>Voici le contenu HTML:</p>";
    echo $html;
}
?>
