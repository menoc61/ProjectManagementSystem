<?php
require_once '../config.php';
redirectIfNotLoggedIn();

// Vérifier si on exporte un règlement spécifique ou la liste filtrée
$singleReglement = isset($_GET['id']) && !empty($_GET['id']);

if ($singleReglement) {
    $id = $_GET['id'];
    
    // Récupérer les informations du règlement
    $sql = "SELECT r.*, c.NomClient, c.EmailClient, c.TelClient 
            FROM REGLEMENT r 
            JOIN CLIENT c ON r.IdClient = c.IdClient 
            WHERE r.IdReglement = $id";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 0) {
        header("Location: index.php");
        exit();
    }
    
    $reglement = mysqli_fetch_assoc($result);
    
    // Récupérer les projets du client
    $projetsSql = "SELECT * FROM PROJET WHERE IdClient = " . $reglement['IdClient'] . " ORDER BY DateDebutProjet DESC";
    $projetsResult = mysqli_query($conn, $projetsSql);
    
    // Récupérer les autres règlements du client
    $reglementsSql = "SELECT * FROM REGLEMENT WHERE IdClient = " . $reglement['IdClient'] . " AND IdReglement != $id ORDER BY DateReglement DESC, HeureReglement DESC";
    $reglementsResult = mysqli_query($conn, $reglementsSql);
    
    // Calculer le total des règlements du client
    $totalReglementsSql = "SELECT SUM(MontantReglement) as total FROM REGLEMENT WHERE IdClient = " . $reglement['IdClient'];
    $totalReglementsResult = mysqli_query($conn, $totalReglementsSql);
    $totalReglements = mysqli_fetch_assoc($totalReglementsResult)['total'] ?: 0;
    
    // Calculer le total des projets du client
    $totalProjetsSql = "SELECT SUM(CoutProjet) as total FROM PROJET WHERE IdClient = " . $reglement['IdClient'];
    $totalProjetsResult = mysqli_query($conn, $totalProjetsSql);
    $totalProjets = mysqli_fetch_assoc($totalProjetsResult)['total'] ?: 0;
    
    // Calculer le solde
    $solde = $totalProjets - $totalReglements;
    
    // Générer le nom du fichier PDF
    $filename = 'reglement_' . $id . '_' . date('Y-m-d') . '.pdf';
} else {
    // Récupérer les paramètres de filtrage
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $client = isset($_GET['client']) ? $_GET['client'] : '';
    $dateDebut = isset($_GET['dateDebut']) ? $_GET['dateDebut'] : '';
    $dateFin = isset($_GET['dateFin']) ? $_GET['dateFin'] : '';
    
    $where = [];
    if (!empty($search)) {
        $where[] = "MontantReglement LIKE '%$search%'";
    }
    if (!empty($client)) {
        $where[] = "r.IdClient = $client";
    }
    if (!empty($dateDebut)) {
        $where[] = "DateReglement >= '$dateDebut'";
    }
    if (!empty($dateFin)) {
        $where[] = "DateReglement <= '$dateFin'";
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    $sql = "SELECT r.*, c.NomClient 
            FROM REGLEMENT r 
            JOIN CLIENT c ON r.IdClient = c.IdClient 
            $whereClause 
            ORDER BY r.DateReglement DESC, r.HeureReglement DESC";
    $result = mysqli_query($conn, $sql);
    
    // Calculer le total des règlements
    $totalSql = "SELECT SUM(MontantReglement) as total FROM REGLEMENT $whereClause";
    $totalResult = mysqli_query($conn, $totalSql);
    $totalMontant = mysqli_fetch_assoc($totalResult)['total'] ?: 0;
    
    // Générer le nom du fichier PDF
    $filename = 'reglements_liste_' . date('Y-m-d') . '.pdf';
}

// Créer le contenu HTML pour le PDF
ob_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export PDF - Gestion des Règlements</title>
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
        .reglement-info {
            margin-bottom: 5px;
        }
        .montant {
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
        }
        .card {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .card-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Gestion de Projets d'Entreprise</h1>
        <p>Date d'export: <?php echo date('d/m/Y H:i'); ?></p>
    </div>
    
    <?php if ($singleReglement): ?>
    <!-- Export d'un règlement spécifique -->
    <h2>Détails du Règlement #<?php echo $reglement['IdReglement']; ?></h2>
    
    <div class="card">
        <div class="reglement-info"><strong>Client:</strong> <?php echo htmlspecialchars($reglement['NomClient']); ?></div>
        <div class="reglement-info"><strong>Date:</strong> <?php echo date('d/m/Y', strtotime($reglement['DateReglement'])); ?></div>
        <div class="reglement-info"><strong>Heure:</strong> <?php echo date('H:i', strtotime($reglement['HeureReglement'])); ?></div>
        <div class="reglement-info"><strong>Mode de règlement:</strong> <?php echo htmlspecialchars($reglement['ModeReglement']); ?></div>
        <?php if (!empty($reglement['ReferenceReglement'])): ?>
        <div class="reglement-info"><strong>Référence:</strong> <?php echo htmlspecialchars($reglement['ReferenceReglement']); ?></div>
        <?php endif; ?>
        <div class="montant">Montant: <?php echo number_format($reglement['MontantReglement'], 2, ',', ' '); ?> FCFA</div>
        
        <?php if (!empty($reglement['CommentaireReglement'])): ?>
        <h3>Commentaire</h3>
        <p><?php echo nl2br(htmlspecialchars($reglement['CommentaireReglement'])); ?></p>
        <?php endif; ?>
    </div>
    
    <h3>Informations du client</h3>
    <table>
        <tr>
            <th>Nom</th>
            <td><?php echo htmlspecialchars($reglement['NomClient']); ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?php echo htmlspecialchars($reglement['EmailClient']); ?></td>
        </tr>
        <tr>
            <th>Téléphone</th>
            <td><?php echo htmlspecialchars($reglement['TelClient']); ?></td>
        </tr>
    </table>
    
    <h3>Situation financière</h3>
    <table>
        <tr>
            <th>Total projets</th>
            <td><?php echo number_format($totalProjets, 2, ',', ' '); ?> FCFA</td>
        </tr>
        <tr>
            <th>Total règlements</th>
            <td><?php echo number_format($totalReglements, 2, ',', ' '); ?> FCFA</td>
        </tr>
        <tr>
            <th>Solde</th>
            <td><?php echo number_format($solde, 2, ',', ' '); ?> FCFA</td>
        </tr>
    </table>
    
    <h3>Projets du client</h3>
    <?php if (mysqli_num_rows($projetsResult) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Date début</th>
                <th>Date fin</th>
                <th>Coût</th>
            </tr>
        </thead>
        <tbody>
            <?php while($projet = mysqli_fetch_assoc($projetsResult)): ?>
            <tr>
                <td><?php echo $projet['IdProjet']; ?></td>
                <td><?php echo htmlspecialchars($projet['TitreProjet']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($projet['DateDebutProjet'])); ?></td>
                <td><?php echo date('d/m/Y', strtotime($projet['DateFinProjet'])); ?></td>
                <td><?php echo number_format($projet['CoutProjet'], 2, ',', ' '); ?> FCFA</td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Aucun projet trouvé pour ce client.</p>
    <?php endif; ?>
    
    <h3>Autres règlements du client</h3>
    <?php if (mysqli_num_rows($reglementsResult) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Montant</th>
                <th>Mode</th>
            </tr>
        </thead>
        <tbody>
            <?php while($autreReglement = mysqli_fetch_assoc($reglementsResult)): ?>
            <tr>
                <td><?php echo $autreReglement['IdReglement']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($autreReglement['DateReglement'])); ?></td>
                <td><?php echo number_format($autreReglement['MontantReglement'], 2, ',', ' '); ?> FCFA</td>
                <td><?php echo htmlspecialchars($autreReglement['ModeReglement']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Aucun autre règlement trouvé pour ce client.</p>
    <?php endif; ?>
    
    <?php else: ?>
    <!-- Export de la liste des règlements -->
    <h2>Liste des Règlements</h2>
    
    <div class="card">
        <div class="card-title">Statistiques des règlements</div>
        <table>
            <tr>
                <th>Total des règlements</th>
                <td><?php echo number_format($totalMontant, 2, ',', ' '); ?> FCFA</td>
            </tr>
            <tr>
                <th>Nombre de règlements</th>
                <td><?php echo mysqli_num_rows($result); ?></td>
            </tr>
            <tr>
                <th>Montant moyen</th>
                <td>
                    <?php 
                    $count = mysqli_num_rows($result);
                    echo $count > 0 ? number_format($totalMontant / $count, 2, ',', ' ') : '0,00'; 
                    ?> FCFA
                </td>
            </tr>
        </table>
    </div>
    
    <?php if (mysqli_num_rows($result) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Date</th>
                <th>Heure</th>
                <th>Montant</th>
                <th>Mode</th>
                <th>Référence</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $row['IdReglement']; ?></td>
                <td><?php echo htmlspecialchars($row['NomClient']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['DateReglement'])); ?></td>
                <td><?php echo date('H:i', strtotime($row['HeureReglement'])); ?></td>
                <td><?php echo number_format($row['MontantReglement'], 2, ',', ' '); ?> FCFA</td>
                <td><?php echo htmlspecialchars($row['ModeReglement']); ?></td>
                <td><?php echo htmlspecialchars($row['ReferenceReglement']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Total</th>
                <th><?php echo number_format($totalMontant, 2, ',', ' '); ?> FCFA</th>
                <th colspan="2"></th>
            </tr>
        </tfoot>
    </table>
    <?php else: ?>
    <p>Aucun règlement trouvé.</p>
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
