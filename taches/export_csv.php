<?php
require_once '../config.php';
redirectIfNotLoggedIn();

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

// Définir les en-têtes pour le téléchargement CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=taches_export_' . date('Y-m-d') . '.csv');

// Créer un fichier de sortie
$output = fopen('php://output', 'w');

// Ajouter le BOM UTF-8 pour Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// En-têtes des colonnes
fputcsv($output, [
    'ID', 
    'Libellé', 
    'Description', 
    'Projet', 
    'Client', 
    'Date d\'enregistrement', 
    'Date de début', 
    'Date de fin', 
    'État'
]);

// Données
while ($row = mysqli_fetch_assoc($result)) {
    $etat = '';
    switch($row['EtatTache']) {
        case 1: $etat = 'À faire'; break;
        case 2: $etat = 'En cours'; break;
        case 3: $etat = 'Terminée'; break;
        case 4: $etat = 'Annulée'; break;
    }
    
    fputcsv($output, [
        $row['IdTache'],
        $row['LibelleTache'],
        $row['DescriptionTache'],
        $row['TitreProjet'],
        $row['NomClient'],
        date('d/m/Y', strtotime($row['DateEnregTache'])),
        date('d/m/Y', strtotime($row['DateDebutTache'])),
        date('d/m/Y', strtotime($row['DateFinTache'])),
        $etat
    ]);
}

fclose($output);
exit;
?>
