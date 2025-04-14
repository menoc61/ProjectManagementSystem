<?php
require_once '../config.php';
redirectIfNotLoggedIn();

// Vérifier si on exporte une affectation spécifique ou la liste filtrée
$singleAffectation = isset($_GET['id']) && !empty($_GET['id']);

if ($singleAffectation) {
    $id = $_GET['id'];
    
    // Récupérer les informations de l'affectation
    $sql = "SELECT a.*, 
            p.NomPersonnel, p.PrenomPersonnel, p.EmailPersonnel, p.TelPersonnel,
            t.LibelleTache, t.DescriptionTache, t.DateDebutTache, t.DateFinTache, t.EtatTache,
            pr.TitreProjet
            FROM AFFECTATION a 
            JOIN PERSONNEL p ON a.MatriculePersonnel = p.MatriculePersonnel
            JOIN TACHE t ON a.IdTache = t.IdTache
            JOIN PROJET pr ON t.IdProjet = pr.IdProjet
            WHERE a.IdAffectation = $id";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 0) {
        header("Location: index.php");
        exit();
    }
    
    $affectation = mysqli_fetch_assoc($result);
    
    // Générer le nom du fichier CSV
    $filename = 'affectation_' . $id . '_' . date('Y-m-d') . '.csv';
} else {
    // Récupérer les paramètres de filtrage
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $personnel = isset($_GET['personnel']) ? $_GET['personnel'] : '';
    $tache = isset($_GET['tache']) ? $_GET['tache'] : '';
    
    $where = [];
    if (!empty($search)) {
        $where[] = "(p.NomPersonnel LIKE '%$search%' OR p.PrenomPersonnel LIKE '%$search%' OR t.LibelleTache LIKE '%$search%')";
    }
    if (!empty($personnel)) {
        $where[] = "a.MatriculePersonnel = '$personnel'";
    }
    if (!empty($tache)) {
        $where[] = "a.IdTache = $tache";
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    $sql = "SELECT a.*, 
            p.NomPersonnel, p.PrenomPersonnel, p.EmailPersonnel,
            t.LibelleTache, t.DateDebutTache, t.DateFinTache, t.EtatTache,
            pr.TitreProjet
            FROM AFFECTATION a 
            JOIN PERSONNEL p ON a.MatriculePersonnel = p.MatriculePersonnel
            JOIN TACHE t ON a.IdTache = t.IdTache
            JOIN PROJET pr ON t.IdProjet = pr.IdProjet
            $whereClause 
            ORDER BY a.DateAffectation DESC";
    $result = mysqli_query($conn, $sql);
    
    // Générer le nom du fichier CSV
    $filename = 'affectations_liste_' . date('Y-m-d') . '.csv';
}

// Définir les en-têtes pour le téléchargement CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Créer un fichier de sortie
$output = fopen('php://output', 'w');

// Ajouter le BOM UTF-8 pour Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Map EtatTache to human-readable labels
$etatTacheLabels = [
    1 => 'À faire',
    2 => 'En cours',
    3 => 'Terminée',
    4 => 'Annulée'
];

if ($singleAffectation) {
    // En-têtes des colonnes pour une affectation spécifique
    fputcsv($output, [
        'ID', 
        'Personnel', 
        'Email', 
        'Téléphone',
        'Projet',
        'Tâche',
        'État de la tâche',
        'Date début tâche',
        'Date fin tâche',
        'Date affectation',
        'Fonction'
    ]);
    
    // Données de l'affectation
    fputcsv($output, [
        $affectation['IdAffectation'],
        $affectation['PrenomPersonnel'] . ' ' . $affectation['NomPersonnel'],
        $affectation['EmailPersonnel'],
        $affectation['TelPersonnel'],
        $affectation['TitreProjet'],
        $affectation['LibelleTache'],
        $etatTacheLabels[$affectation['EtatTache']] ?? 'Inconnu',
        date('d/m/Y', strtotime($affectation['DateDebutTache'])),
        date('d/m/Y', strtotime($affectation['DateFinTache'])),
        date('d/m/Y', strtotime($affectation['DateAffectation'])),
        $affectation['FonctionAffectation']
    ]);
} else {
    // En-têtes des colonnes pour la liste
    fputcsv($output, [
        'ID', 
        'Personnel', 
        'Email',
        'Projet',
        'Tâche',
        'État de la tâche',
        'Date début tâche',
        'Date fin tâche',
        'Date affectation',
        'Fonction'
    ]);
    
    // Données
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['IdAffectation'],
            $row['PrenomPersonnel'] . ' ' . $row['NomPersonnel'],
            $row['EmailPersonnel'],
            $row['TitreProjet'],
            $row['LibelleTache'],
            $etatTacheLabels[$row['EtatTache']] ?? 'Inconnu',
            date('d/m/Y', strtotime($row['DateDebutTache'])),
            date('d/m/Y', strtotime($row['DateFinTache'])),
            date('d/m/Y', strtotime($row['DateAffectation'])),
            $row['FonctionAffectation']
        ]);
    }
}

fclose($output);
exit;
?>
