<?php
require_once '../config.php';
redirectIfNotLoggedIn();

// Vérifier si on exporte un projet spécifique ou la liste filtrée
$singleProject = isset($_GET['id']) && !empty($_GET['id']);

if ($singleProject) {
    $id = intval($_GET['id']);

    // Récupérer les informations du projet
    $sql = "SELECT p.*, c.NomClient, c.EmailClient, c.TelClient, t.LibelleTypeProjet 
            FROM PROJET p 
            JOIN CLIENT c ON p.IdClient = c.IdClient 
            JOIN TYPEPROJET t ON p.IdTypeProjet = t.IdTypeProjet 
            WHERE p.IdProjet = $id";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 0) {
        header("Location: index.php");
        exit();
    }

    $projet = mysqli_fetch_assoc($result);

    // Récupérer les tâches du projet
    $tachesSql = "SELECT * FROM TACHE WHERE IdProjet = $id ORDER BY DateDebutTache";
    $tachesResult = mysqli_query($conn, $tachesSql);

    // Calculer les statistiques des tâches
    $tachesStatsSql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN EtatTache = 1 THEN 1 ELSE 0 END) as a_faire,
                        SUM(CASE WHEN EtatTache = 2 THEN 1 ELSE 0 END) as en_cours,
                        SUM(CASE WHEN EtatTache = 3 THEN 1 ELSE 0 END) as terminees,
                        SUM(CASE WHEN EtatTache = 4 THEN 1 ELSE 0 END) as annulees
                      FROM TACHE 
                      WHERE IdProjet = $id";
    $tachesStatsResult = mysqli_query($conn, $tachesStatsSql);
    $tachesStats = mysqli_fetch_assoc($tachesStatsResult);

    // Calculer le pourcentage d'avancement
    $totalTaches = $tachesStats['total'];
    $progression = 0;
    if ($totalTaches > 0) {
        $progression = round(($tachesStats['terminees'] / $totalTaches) * 100);
    }

    // Récupérer les règlements du client
    $reglementsSql = "SELECT * FROM REGLEMENT WHERE IdClient = " . intval($projet['IdClient']) . " ORDER BY DateReglement DESC";
    $reglementsResult = mysqli_query($conn, $reglementsSql);

    // Calculer le total des règlements
    $totalReglementsSql = "SELECT SUM(MontantReglement) as total FROM REGLEMENT WHERE IdClient = " . intval($projet['IdClient']);
    $totalReglementsResult = mysqli_query($conn, $totalReglementsSql);
    $totalReglements = mysqli_fetch_assoc($totalReglementsResult)['total'] ?: 0;

    // Calculer le solde
    $solde = $projet['CoutProjet'] - $totalReglements;

    // Générer le nom du fichier PDF
    $filename = 'projet_' . $id . '_' . date('Y-m-d') . '.pdf';
} else {
    // Récupérer les paramètres de filtrage
    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
    $client = isset($_GET['client']) ? intval($_GET['client']) : '';
    $type = isset($_GET['type']) ? intval($_GET['type']) : '';
    $etat = isset($_GET['etat']) ? intval($_GET['etat']) : '';

    $where = [];
    if (!empty($search)) {
        $where[] = "(TitreProjet LIKE '%$search%' OR DescriptionProjet LIKE '%$search%')";
    }
    if (!empty($client)) {
        $where[] = "p.IdClient = $client";
    }
    if (!empty($type)) {
        $where[] = "p.IdTypeProjet = $type";
    }
    if (!empty($etat)) {
        $where[] = "p.EtatProjet = $etat";
    }

    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    $sql = "SELECT p.*, c.NomClient, t.LibelleTypeProjet 
            FROM PROJET p 
            JOIN CLIENT c ON p.IdClient = c.IdClient 
            JOIN TYPEPROJET t ON p.IdTypeProjet = t.IdTypeProjet 
            $whereClause 
            ORDER BY p.IdProjet DESC";
    $result = mysqli_query($conn, $sql);

    // Générer le nom du fichier PDF
    $filename = 'projets_liste_' . date('Y-m-d') . '.pdf';
}

// Créer le contenu HTML pour le PDF
ob_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export PDF - Gestion de Projets</title>
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
        .projet-info {
            margin-bottom: 5px;
        }
        .progress-container {
            width: 100%;
            height: 20px;
            background-color: #e9ecef;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .progress-bar {
            height: 20px;
            background-color: #0d6efd;
            border-radius: 5px;
            text-align: center;
            color: white;
            line-height: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Gestion de Projets d'Entreprise</h1>
        <p>Date d'export: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
    
    <?php if ($singleProject): ?>
    <!-- Export d'un projet spécifique -->
    <h2>Détails du Projet #<?php echo $projet['IdProjet']; ?></h2>
    
    <div>
        <h3><?php echo htmlspecialchars($projet['TitreProjet']); ?></h3>
        <p class="projet-info"><strong>Description:</strong> <?php echo htmlspecialchars($projet['DescriptionProjet']); ?></p>
        <p class="projet-info">
            <strong>État:</strong> 
            <?php 
            switch($projet['EtatProjet']) {
                case 1: echo "<span class='badge badge-primary'>En cours</span>"; break;
                case 2: echo "<span class='badge badge-success'>Terminé</span>"; break;
                case 3: echo "<span class='badge badge-danger'>Annulé</span>"; break;
            }
            ?>
        </p>
        <p class="projet-info"><strong>Client:</strong> <?php echo htmlspecialchars($projet['NomClient']); ?></p>
        <p class="projet-info"><strong>Type:</strong> <?php echo htmlspecialchars($projet['LibelleTypeProjet']); ?></p>
        <p class="projet-info"><strong>Coût:</strong> <?php echo number_format($projet['CoutProjet'], 2, ',', ' '); ?> FCFA</p>
        <p class="projet-info"><strong>Début:</strong> <?php echo date('d/m/Y', strtotime($projet['DateDebutProjet'])); ?></p>
        <p class="projet-info"><strong>Fin:</strong> <?php echo date('d/m/Y', strtotime($projet['DateFinProjet'])); ?></p>
    </div>
    
    <h3>Progression du projet: <?php echo $progression; ?>%</h3>
    <div class="progress-container">
        <div class="progress-bar" style="width: <?php echo $progression; ?>%;"><?php echo $progression; ?>%</div>
    </div>
    
    <h3>Tâches du projet</h3>
    <?php if (mysqli_num_rows($tachesResult) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Libellé</th>
                <th>Date d'enregistrement</th>
                <th>Date de début</th>
                <th>Date de fin</th>
                <th>État</th>
            </tr>
        </thead>
        <tbody>
            <?php while($tache = mysqli_fetch_assoc($tachesResult)): 
                $etat = "";
                switch($tache['EtatTache']) {
                    case 1: $etat = "<span class='badge badge-secondary'>À faire</span>"; break;
                    case 2: $etat = "<span class='badge badge-primary'>En cours</span>"; break;
                    case 3: $etat = "<span class='badge badge-success'>Terminée</span>"; break;
                    case 4: $etat = "<span class='badge badge-danger'>Annulée</span>"; break;
                }
            ?>
            <tr>
                <td><?php echo $tache['IdTache']; ?></td>
                <td><?php echo htmlspecialchars($tache['LibelleTache']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($tache['DateEnregTache'])); ?></td>
                <td><?php echo date('d/m/Y', strtotime($tache['DateDebutTache'])); ?></td>
                <td><?php echo date('d/m/Y', strtotime($tache['DateFinTache'])); ?></td>
                <td><?php echo $etat; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Aucune tâche trouvée pour ce projet.</p>
    <?php endif; ?>
    
    <h3>Informations financières</h3>
    <table>
        <tr>
            <th>Coût du projet</th>
            <td><?php echo number_format($projet['CoutProjet'], 2, ',', ' '); ?> FCFA</td>
        </tr>
        <tr>
            <th>Total des règlements</th>
            <td><?php echo number_format($totalReglements, 2, ',', ' '); ?> FCFA</td>
        </tr>
        <tr>
            <th>Solde</th>
            <td><?php echo number_format($solde, 2, ',', ' '); ?> FCFA</td>
        </tr>
    </table>
    
    <h3>Statistiques des tâches</h3>
    <table>
        <tr>
            <th>À faire</th>
            <td><?php echo $tachesStats['a_faire']; ?></td>
            <td><?php echo $totalTaches > 0 ? round(($tachesStats['a_faire'] / $totalTaches) * 100) : 0; ?>%</td>
        </tr>
        <tr>
            <th>En cours</th>
            <td><?php echo $tachesStats['en_cours']; ?></td>
            <td><?php echo $totalTaches > 0 ? round(($tachesStats['en_cours'] / $totalTaches) * 100) : 0; ?>%</td>
        </tr>
        <tr>
            <th>Terminées</th>
            <td><?php echo $tachesStats['terminees']; ?></td>
            <td><?php echo $totalTaches > 0 ? round(($tachesStats['terminees'] / $totalTaches) * 100) : 0; ?>%</td>
        </tr>
        <tr>
            <th>Annulées</th>
            <td><?php echo $tachesStats['annulees']; ?></td>
            <td><?php echo $totalTaches > 0 ? round(($tachesStats['annulees'] / $totalTaches) * 100) : 0; ?>%</td>
        </tr>
        <tr>
            <th>Total</th>
            <td><?php echo $totalTaches; ?></td>
            <td>100%</td>
        </tr>
    </table>
    
    <?php else: ?>
    <!-- Export de la liste des projets -->
    <h2>Liste des Projets</h2>
    
    <?php if (mysqli_num_rows($result) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Client</th>
                <th>Type</th>
                <th>Coût</th>
                <th>Début</th>
                <th>Fin</th>
                <th>État</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalCout = 0;
            while($row = mysqli_fetch_assoc($result)): 
                $totalCout += $row['CoutProjet'];
                
                $etat = "";
                switch($row['EtatProjet']) {
                    case 1: $etat = "<span class='badge badge-primary'>En cours</span>"; break;
                    case 2: $etat = "<span class='badge badge-success'>Terminé</span>"; break;
                    case 3: $etat = "<span class='badge badge-danger'>Annulé</span>"; break;
                }
            ?>
            <tr>
                <td><?php echo $row['IdProjet']; ?></td>
                <td><?php echo htmlspecialchars($row['TitreProjet']); ?></td>
                <td><?php echo htmlspecialchars($row['NomClient']); ?></td>
                <td><?php echo htmlspecialchars($row['LibelleTypeProjet']); ?></td>
                <td><?php echo number_format($row['CoutProjet'], 2, ',', ' '); ?> FCFA</td>
                <td><?php echo date('d/m/Y', strtotime($row['DateDebutProjet'])); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['DateFinProjet'])); ?></td>
                <td><?php echo $etat; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Total</th>
                <th><?php echo number_format($totalCout, 2, ',', ' '); ?> FCFA</th>
                <th colspan="3"></th>
            </tr>
        </tfoot>
    </table>
    <?php else: ?>
    <p>Aucun projet trouvé.</p>
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
