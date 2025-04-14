<?php
require_once '../config.php';
redirectIfNotLoggedIn();

// Vérifier si on exporte un membre spécifique ou la liste filtrée
$singlePersonnel = isset($_GET['id']) && !empty($_GET['id']);

if ($singlePersonnel) {
    $id = $_GET['id'];
    
    // Récupérer les informations du personnel
    $sql = "SELECT p.*, s.LibelleService 
            FROM PERSONNEL p 
            LEFT JOIN SERVICE s ON p.CodeService = s.CodeService 
            WHERE p.MatriculePersonnel = '$id'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) == 0) {
        header("Location: index.php");
        exit();
    }
    
    $personnel = mysqli_fetch_assoc($result);
    
    // Récupérer les affectations de ce membre du personnel
    $affectationsSql = "SELECT a.*, t.LibelleTache, t.DateDebutTache, t.DateFinTache, t.EtatTache, pr.TitreProjet
                        FROM AFFECTATION a 
                        JOIN TACHE t ON a.IdTache = t.IdTache 
                        JOIN PROJET pr ON t.IdProjet = pr.IdProjet
                        WHERE a.MatriculePersonnel = '$id' 
                        ORDER BY t.DateDebutTache DESC";
    $affectationsResult = mysqli_query($conn, $affectationsSql);
    
    // Générer le nom du fichier CSV
    $filename = 'personnel_' . $id . '_' . date('Y-m-d') . '.csv';
} else {
    // Récupérer les paramètres de filtrage
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $service = isset($_GET['service']) ? $_GET['service'] : '';
    
    $where = [];
    if (!empty($search)) {
        $where[] = "(p.NomPersonnel LIKE '%$search%' OR p.PrenomPersonnel LIKE '%$search%' OR p.EmailPersonnel LIKE '%$search%')";
    }
    if (!empty($service)) {
        $where[] = "p.CodeService = '$service'";
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    $sql = "SELECT p.*, s.LibelleService 
            FROM PERSONNEL p 
            LEFT JOIN SERVICE s ON p.CodeService = s.CodeService 
            $whereClause 
            ORDER BY p.NomPersonnel, p.PrenomPersonnel";
    $result = mysqli_query($conn, $sql);
    
    // Générer le nom du fichier CSV
    $filename = 'personnel_liste_' . date('Y-m-d') . '.csv';
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

if ($singlePersonnel) {
    // En-têtes des colonnes pour un membre spécifique
    fputcsv($output, [
        'Matricule', 
        'Nom', 
        'Prénom', 
        'Email', 
        'Téléphone', 
        'Service', 
        'Compétences', 
        'Commentaires'
    ]);
    
    // Données du membre
    fputcsv($output, [
        $personnel['MatriculePersonnel'],
        $personnel['NomPersonnel'],
        $personnel['PrenomPersonnel'],
        $personnel['EmailPersonnel'],
        $personnel['TelPersonnel'],
        $personnel['LibelleService'] ?: 'Non assigné',
        $personnel['CompetencesPersonnel'],
        $personnel['CommentairesPersonnel']
    ]);
    
    // Ligne vide
    fputcsv($output, []);
    
    // En-têtes des colonnes pour les affectations
    fputcsv($output, [
        'ID Affectation', 
        'Projet', 
        'Tâche', 
        'Date début', 
        'Date fin', 
        'État'
    ]);
    
    // Données des affectations
    while ($affectation = mysqli_fetch_assoc($affectationsResult)) {
        fputcsv($output, [
            $affectation['IdAffectation'],
            $affectation['TitreProjet'],
            $affectation['LibelleTache'],
            date('d/m/Y', strtotime($affectation['DateDebutTache'])),
            date('d/m/Y', strtotime($affectation['DateFinTache'])),
            $etatTacheLabels[$affectation['EtatTache']] ?? 'Inconnu'
        ]);
    }
} else {
    // En-têtes des colonnes pour la liste
    fputcsv($output, [
        'Matricule', 
        'Nom', 
        'Prénom', 
        'Email', 
        'Téléphone', 
        'Service', 
        'Compétences', 
        'Commentaires'
    ]);
    
    // Données
    while ($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, [
            $row['MatriculePersonnel'],
            $row['NomPersonnel'],
            $row['PrenomPersonnel'],
            $row['EmailPersonnel'],
            $row['TelPersonnel'],
            $row['LibelleService'] ?: 'Non assigné',
            $row['CompetencesPersonnel'],
            $row['CommentairesPersonnel']
        ]);
    }
}

fclose($output);
exit;
?>
