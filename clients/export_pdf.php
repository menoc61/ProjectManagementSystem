<?php
require_once '../config.php';
redirectIfNotLoggedIn();

// Vérifier si une bibliothèque PDF est disponible
if (!extension_loaded('gd')) {
    die("L'extension GD est requise pour générer des PDF.");
}

// Récupérer l'ID du client si spécifié
$clientId = isset($_GET['id']) ? $_GET['id'] : null;

// Créer une classe simple pour générer un PDF
class PDF {
    private $html = '';
    private $title = '';
    
    public function __construct($title) {
        $this->title = $title;
        $this->html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . $title . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #0d6efd; text-align: center; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .footer { text-align: center; font-size: 12px; margin-top: 30px; color: #666; }
                .client-info { margin-bottom: 20px; }
                .client-info p { margin: 5px 0; }
                .summary { margin-top: 20px; }
                .summary h3 { color: #0d6efd; }
            </style>
        </head>
        <body>
            <h1>' . $title . '</h1>';
    }
    
    public function addText($text) {
        $this->html .= '<p>' . $text . '</p>';
    }
    
    public function addClientInfo($client) {
        $this->html .= '
        <div class="client-info">
            <h2>' . htmlspecialchars($client['NomClient']) . '</h2>
            <p><strong>ID:</strong> ' . $client['IdClient'] . '</p>
            <p><strong>Adresse:</strong> ' . htmlspecialchars($client['AdresseClient']) . '</p>
            <p><strong>Email:</strong> ' . htmlspecialchars($client['EmailClient']) . '</p>
            <p><strong>Téléphone:</strong> ' . htmlspecialchars($client['TelClient']) . '</p>
        </div>';
    }
    
    public function startTable($headers) {
        $this->html .= '<table><thead><tr>';
        foreach ($headers as $header) {
            $this->html .= '<th>' . $header . '</th>';
        }
        $this->html .= '</tr></thead><tbody>';
    }
    
    public function addTableRow($data) {
        $this->html .= '<tr>';
        foreach ($data as $cell) {
            $this->html .= '<td>' . $cell . '</td>';
        }
        $this->html .= '</tr>';
    }
    
    public function endTable() {
        $this->html .= '</tbody></table>';
    }
    
    public function addSummary($totalProjets, $totalReglements, $solde) {
        $this->html .= '
        <div class="summary">
            <h3>Résumé financier</h3>
            <p><strong>Total des projets:</strong> ' . number_format($totalProjets, 2, ',', ' ') . ' FCFA</p>
            <p><strong>Total des règlements:</strong> ' . number_format($totalReglements, 2, ',', ' ') . ' FCFA</p>
            <p><strong>Solde:</strong> ' . number_format($solde, 2, ',', ' ') . ' FCFA</p>
        </div>';
    }
    
    public function output() {
        $this->html .= '
            <div class="footer">
                Document généré le ' . date('d/m/Y à H:i') . ' - Gestion de Projets
            </div>
        </body>
        </html>';
        
        // Définir les en-têtes pour le téléchargement PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $this->title . '_' . date('Y-m-d') . '.pdf"');
        
        // Utiliser une bibliothèque de conversion HTML vers PDF
        // Comme nous n'avons pas de bibliothèque spécifique installée, nous allons simuler
        // en renvoyant le HTML avec un message d'information
        echo $this->html;
        echo '<script>alert("Dans un environnement de production, ce HTML serait converti en PDF avec une bibliothèque comme TCPDF, FPDF ou Dompdf.");</script>';
        exit;
    }
}

// Si un ID client est spécifié, générer un PDF pour ce client
if ($clientId) {
    $sql = "SELECT * FROM CLIENT WHERE IdClient = $clientId";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 0) {
        header("Location: index.php");
        exit();
    }
    
    $client = mysqli_fetch_assoc($result);
    
    // Récupérer les projets du client
    $projetsSql = "SELECT p.*, t.LibelleTypeProjet 
                  FROM PROJET p 
                  JOIN TYPEPROJET t ON p.IdTypeProjet = t.IdTypeProjet 
                  WHERE p.IdClient = $clientId 
                  ORDER BY p.DateDebutProjet DESC";
    $projetsResult = mysqli_query($conn, $projetsSql);
    
    // Récupérer les règlements du client
    $reglementsSql = "SELECT * FROM REGLEMENT WHERE IdClient = $clientId ORDER BY DateReglement DESC";
    $reglementsResult = mysqli_query($conn, $reglementsSql);
    
    // Calculer les totaux
    $totalProjetsSql = "SELECT SUM(CoutProjet) as total FROM PROJET WHERE IdClient = $clientId";
    $totalProjetsResult = mysqli_query($conn, $totalProjetsSql);
    $totalProjets = mysqli_fetch_assoc($totalProjetsResult)['total'] ?: 0;
    
    $totalReglementsSql = "SELECT SUM(MontantReglement) as total FROM REGLEMENT WHERE IdClient = $clientId";
    $totalReglementsResult = mysqli_query($conn, $totalReglementsSql);
    $totalReglements = mysqli_fetch_assoc($totalReglementsResult)['total'] ?: 0;
    
    $solde = $totalProjets - $totalReglements;
    
    // Créer le PDF
    $pdf = new PDF("Fiche Client - " . $client['NomClient']);
    $pdf->addClientInfo($client);
    
    // Ajouter les projets
    $pdf->addText("<strong>Projets du client:</strong>");
    if (mysqli_num_rows($projetsResult) > 0) {
        $pdf->startTable(['ID', 'Titre', 'Type', 'Coût (FCFA)', 'Début', 'Fin', 'État']);
        
        while($projet = mysqli_fetch_assoc($projetsResult)) {
            $etat = "";
            switch($projet['EtatProjet']) {
                case 1: $etat = "En cours"; break;
                case 2: $etat = "Terminé"; break;
                case 3: $etat = "Annulé"; break;
            }
            
            $pdf->addTableRow([
                $projet['IdProjet'],
                htmlspecialchars($projet['TitreProjet']),
                htmlspecialchars($projet['LibelleTypeProjet']),
                number_format($projet['CoutProjet'], 2, ',', ' '),
                date('d/m/Y', strtotime($projet['DateDebutProjet'])),
                date('d/m/Y', strtotime($projet['DateFinProjet'])),
                $etat
            ]);
        }
        
        $pdf->endTable();
    } else {
        $pdf->addText("Aucun projet trouvé pour ce client.");
    }
    
    // Ajouter les règlements
    $pdf->addText("<strong>Règlements du client:</strong>");
    if (mysqli_num_rows($reglementsResult) > 0) {
        $pdf->startTable(['ID', 'Date', 'Heure', 'Montant (FCFA)']);
        
        while($reglement = mysqli_fetch_assoc($reglementsResult)) {
            $pdf->addTableRow([
                $reglement['IdReglement'],
                date('d/m/Y', strtotime($reglement['DateReglement'])),
                date('H:i', strtotime($reglement['HeureReglement'])),
                number_format($reglement['MontantReglement'], 2, ',', ' ')
            ]);
        }
        
        $pdf->endTable();
    } else {
        $pdf->addText("Aucun règlement trouvé pour ce client.");
    }
    
    // Ajouter le résumé financier
    $pdf->addSummary($totalProjets, $totalReglements, $solde);
    
    // Générer le PDF
    $pdf->output();
} 
// Sinon, générer un PDF avec tous les clients
else {
    $sql = "SELECT * FROM CLIENT ORDER BY IdClient";
    $result = mysqli_query($conn, $sql);
    
    // Créer le PDF
    $pdf = new PDF("Liste des Clients");
    $pdf->addText("Liste complète des clients avec leurs informations financières.");
    
    if (mysqli_num_rows($result) > 0) {
        $pdf->startTable(['ID', 'Nom', 'Email', 'Téléphone', 'Projets', 'Total Projets (FCFA)', 'Total Règlements (FCFA)', 'Solde (FCFA)']);
        
        while($row = mysqli_fetch_assoc($result)) {
            $clientId = $row['IdClient'];
            
            // Récupérer le nombre de projets
            $projetsSql = "SELECT COUNT(*) as count FROM PROJET WHERE IdClient = $clientId";
            $projetsResult = mysqli_query($conn, $projetsSql);
            $projetsCount = mysqli_fetch_assoc($projetsResult)['count'];
            
            // Récupérer le total des projets
            $totalProjetsSql = "SELECT SUM(CoutProjet) as total FROM PROJET WHERE IdClient = $clientId";
            $totalProjetsResult = mysqli_query($conn, $totalProjetsSql);
            $totalProjets = mysqli_fetch_assoc($totalProjetsResult)['total'] ?: 0;
            
            // Récupérer le total des règlements
            $totalReglementsSql = "SELECT SUM(MontantReglement) as total FROM REGLEMENT WHERE IdClient = $clientId";
            $totalReglementsResult = mysqli_query($conn, $totalReglementsSql);
            $totalReglements = mysqli_fetch_assoc($totalReglementsResult)['total'] ?: 0;
            
            // Calculer le solde
            $solde = $totalProjets - $totalReglements;
            
            $pdf->addTableRow([
                $row['IdClient'],
                htmlspecialchars($row['NomClient']),
                htmlspecialchars($row['EmailClient']),
                htmlspecialchars($row['TelClient']),
                $projetsCount,
                number_format($totalProjets, 2, ',', ' '),
                number_format($totalReglements, 2, ',', ' '),
                number_format($solde, 2, ',', ' ')
            ]);
        }
        
        $pdf->endTable();
    } else {
        $pdf->addText("Aucun client trouvé dans la base de données.");
    }
    
    // Générer le PDF
    $pdf->output();
}
?>
