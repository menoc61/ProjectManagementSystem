<?php
require_once '../config.php';
redirectIfNotLoggedIn();

// Récupérer les paramètres de filtrage
$search = isset($_GET['search']) ? $_GET['search'] : '';
$client = isset($_GET['client']) ? $_GET['client'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$etat = isset($_GET['etat']) ? $_GET['etat'] : '';

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

// Définir les en-têtes pour le téléchargement CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=projets_export_' . date('Y-m-d') . '.csv');

// Créer un fichier de sortie
$output = fopen('php://output', 'w');

// Ajouter le BOM UTF-8 pour Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// En-têtes des colonnes
fputcsv($output, [
    'ID', 
    'Titre', 
    'Description', 
    'Client', 
    'Type de projet', 
    'Coût (FCFA)', 
    'Date de début', 
    'Date de fin', 
    'État'
]);

// Données
while ($row = mysqli_fetch_assoc($result)) {
    $etat = '';
    switch($row['EtatProjet']) {
        case 1: $etat = 'En cours'; break;
        case 2: $etat = 'Terminé'; break;
        case 3: $etat = 'Annulé'; break;
    }
    
    fputcsv($output, [
        $row['IdProjet'],
        $row['TitreProjet'],
        $row['DescriptionProjet'],
        $row['NomClient'],
        $row['LibelleTypeProjet'],
        number_format($row['CoutProjet'], 2, ',', ' '),
        date('d/m/Y', strtotime($row['DateDebutProjet'])),
        date('d/m/Y', strtotime($row['DateFinProjet'])),
        $etat
    ]);
}

fclose($output);
exit;
?>
