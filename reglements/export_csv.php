<?php
require_once '../config.php';
redirectIfNotLoggedIn();

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

// Définir les en-têtes pour le téléchargement CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=reglements_export_' . date('Y-m-d') . '.csv');

// Créer un fichier de sortie
$output = fopen('php://output', 'w');

// Ajouter le BOM UTF-8 pour Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// En-têtes des colonnes
fputcsv($output, [
    'ID', 
    'Client', 
    'Date', 
    'Heure', 
    'Montant (FCFA)', 
    'Mode de règlement', 
    'Référence', 
    'Commentaire'
]);

// Données
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['IdReglement'],
        $row['NomClient'],
        date('d/m/Y', strtotime($row['DateReglement'])),
        date('H:i', strtotime($row['HeureReglement'])),
        number_format($row['MontantReglement'], 2, ',', ' '),
        $row['ModeReglement'],
        $row['ReferenceReglement'],
        $row['CommentaireReglement']
    ]);
}

fclose($output);
exit;
?>
