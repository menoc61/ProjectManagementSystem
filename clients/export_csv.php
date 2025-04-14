<?php
require_once '../config.php';
redirectIfNotLoggedIn();

// Récupérer les données des clients
$sql = "SELECT * FROM CLIENT ORDER BY IdClient";
$result = mysqli_query($conn, $sql);

// Définir les en-têtes pour le téléchargement CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=clients_export_' . date('Y-m-d') . '.csv');

// Créer un fichier de sortie
$output = fopen('php://output', 'w');

// Ajouter le BOM UTF-8 pour Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Ajouter les en-têtes de colonnes
fputcsv($output, array('ID', 'Nom', 'Adresse', 'Email', 'Téléphone', 'Nombre de Projets', 'Total Projets (FCFA)', 'Total Règlements (FCFA)', 'Solde (FCFA)'), ';');

// Ajouter les données des clients
if (mysqli_num_rows($result) > 0) {
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
        
        // Préparer les données pour l'export
        $line = array(
            $row['IdClient'],
            $row['NomClient'],
            $row['AdresseClient'],
            $row['EmailClient'],
            $row['TelClient'],
            $projetsCount,
            number_format($totalProjets, 2, ',', ''),
            number_format($totalReglements, 2, ',', ''),
            number_format($solde, 2, ',', '')
        );
        
        fputcsv($output, $line, ';');
    }
}

fclose($output);
exit;
?>
