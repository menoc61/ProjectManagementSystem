<?php
/**
 * Export Class
 * 
 * Handles exporting data to CSV and PDF formats
 */
class Export {
    /**
     * Export data to CSV
     * 
     * @param array $data Data to export
     * @param array $headers Column headers
     * @param string $filename Filename for the CSV file
     * @return void
     */
    public static function toCSV($data, $headers, $filename) {
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Output headers
        fputcsv($output, $headers, ';');
        
        // Output data rows
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }
        
        // Close output stream
        fclose($output);
        exit;
    }
    
    /**
     * Export data to PDF using FPDF
     * 
     * @param array $data Data to export
     * @param array $headers Column headers
     * @param string $title Document title
     * @param string $filename Filename for the PDF file
     * @return void
     */
    public static function toPDF($data, $headers, $title, $filename) {
        // Require FPDF library
        require_once(APP_ROOT . '/lib/fpdf/fpdf.php');
        
        // Create new PDF instance
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Set document properties
        $pdf->SetTitle($title);
        $pdf->SetAuthor(SITE_NAME);
        
        // Add title
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, utf8_decode($title), 0, 1, 'C');
        $pdf->Ln(5);
        
        // Add date
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 10, 'Généré le ' . date('d/m/Y H:i'), 0, 1, 'R');
        $pdf->Ln(5);
        
        // Calculate column widths based on number of columns
        $width = 190 / count($headers);
        
        // Add table headers
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetFillColor(200, 200, 200);
        foreach ($headers as $header) {
            $pdf->Cell($width, 10, utf8_decode($header), 1, 0, 'C', true);
        }
        $pdf->Ln();
        
        // Add data rows
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetFillColor(255, 255, 255);
        
        foreach ($data as $row) {
            foreach ($row as $cell) {
                $pdf->Cell($width, 8, utf8_decode($cell), 1, 0, 'L');
            }
            $pdf->Ln();
        }
        
        // Output PDF
        $pdf->Output('D', $filename . '.pdf');
        exit;
    }
    
    /**
     * Generate a client information PDF
     * 
     * @param array $client Client data
     * @param array $projects Client's projects
     * @param array $payments Client's payments
     * @param string $filename Filename for the PDF file
     * @return void
     */
    public static function clientInfoPDF($client, $projects, $payments, $filename) {
        // Require FPDF library
        require_once(APP_ROOT . '/lib/fpdf/fpdf.php');
        
        // Create new PDF instance
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Set document properties
        $pdf->SetTitle('Fiche Client - ' . $client['NomClient']);
        $pdf->SetAuthor(SITE_NAME);
        
        // Add company header
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell(0, 10, utf8_decode(SITE_NAME), 0, 1, 'C');
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 5, utf8_decode('Gestion de Projets'), 0, 1, 'C');
        $pdf->Ln(10);
        
        // Add client information
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, utf8_decode('Fiche Client'), 0, 1, 'L');
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 8, 'ID:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, $client['IdClient'], 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 8, 'Nom:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, utf8_decode($client['NomClient']), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 8, 'Adresse:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, utf8_decode($client['AdresseClient']), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 8, 'Email:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, $client['EmailClient'], 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 8, 'Téléphone:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, $client['TelClient'], 0, 1);
        
        $pdf->Ln(10);
        
        // Add projects information
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, utf8_decode('Projets du client'), 0, 1, 'L');
        
        if (count($projects) > 0) {
            // Table headers
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(200, 200, 200);
            $pdf->Cell(15, 8, 'ID', 1, 0, 'C', true);
            $pdf->Cell(75, 8, 'Titre', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'Date de début', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'Date de fin', 1, 0, 'C', true);
            $pdf->Cell(40, 8, 'Coût', 1, 1, 'C', true);
            
            // Table data
            $pdf->SetFont('Arial', '', 9);
            foreach ($projects as $project) {
                $pdf->Cell(15, 8, $project['IdProjet'], 1, 0, 'C');
                $pdf->Cell(75, 8, utf8_decode($project['TitreProjet']), 1, 0, 'L');
                $pdf->Cell(30, 8, formatDate($project['DateDebutProjet']), 1, 0, 'C');
                $pdf->Cell(30, 8, formatDate($project['DateFinProjet']), 1, 0, 'C');
                $pdf->Cell(40, 8, formatMoney($project['CoutProjet']), 1, 1, 'R');
            }
        } else {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 8, 'Aucun projet pour ce client.', 0, 1, 'L');
        }
        
        $pdf->Ln(10);
        
        // Add payment information
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, utf8_decode('Règlements du client'), 0, 1, 'L');
        
        if (count($payments) > 0) {
            // Summary of payments
            $totalPaid = 0;
            foreach ($payments as $payment) {
                $totalPaid += $payment['MontantReglement'];
            }
            
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(80, 8, 'Total des règlements:', 0, 0);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 8, formatMoney($totalPaid), 0, 1);
            
            $pdf->Ln(5);
            
            // Table headers
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(200, 200, 200);
            $pdf->Cell(20, 8, 'ID', 1, 0, 'C', true);
            $pdf->Cell(40, 8, 'Date', 1, 0, 'C', true);
            $pdf->Cell(40, 8, 'Heure', 1, 0, 'C', true);
            $pdf->Cell(90, 8, 'Montant', 1, 1, 'C', true);
            
            // Table data
            $pdf->SetFont('Arial', '', 10);
            foreach ($payments as $payment) {
                $pdf->Cell(20, 8, $payment['IdReglement'], 1, 0, 'C');
                $pdf->Cell(40, 8, formatDate($payment['DateReglement']), 1, 0, 'C');
                $pdf->Cell(40, 8, $payment['HeureReglement'], 1, 0, 'C');
                $pdf->Cell(90, 8, formatMoney($payment['MontantReglement']), 1, 1, 'R');
            }
        } else {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 8, 'Aucun règlement pour ce client.', 0, 1, 'L');
        }
        
        // Add footer
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Page ' . $pdf->PageNo(), 0, 0, 'C');
        
        // Output PDF
        $pdf->Output('D', $filename . '.pdf');
        exit;
    }
    
    /**
     * Generate a project information PDF
     * 
     * @param array $project Project data
     * @param array $client Client data
     * @param array $tasks Project's tasks
     * @param string $filename Filename for the PDF file
     * @return void
     */
    public static function projectInfoPDF($project, $client, $tasks, $filename) {
        // Require FPDF library
        require_once(APP_ROOT . '/lib/fpdf/fpdf.php');
        
        // Create new PDF instance
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Set document properties
        $pdf->SetTitle('Fiche Projet - ' . $project['TitreProjet']);
        $pdf->SetAuthor(SITE_NAME);
        
        // Add company header
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell(0, 10, utf8_decode(SITE_NAME), 0, 1, 'C');
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 5, utf8_decode('Gestion de Projets'), 0, 1, 'C');
        $pdf->Ln(10);
        
        // Add project information
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, utf8_decode('Fiche Projet'), 0, 1, 'L');
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 8, 'ID:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, $project['IdProjet'], 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 8, 'Titre:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, utf8_decode($project['TitreProjet']), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 8, 'Client:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, utf8_decode($client['NomClient']), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 8, 'Type de projet:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, utf8_decode($project['LibelleTypeProjet']), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 8, 'Description:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        
        // Handle multiline description
        $description = utf8_decode($project['DescriptionProjet']);
        $pdf->MultiCell(0, 8, $description, 0, 'L');
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 8, 'Coût:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, formatMoney($project['CoutProjet']), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 8, 'Date de début:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, formatDate($project['DateDebutProjet']), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 8, 'Date de fin:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, formatDate($project['DateFinProjet']), 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 8, 'État:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, getProjectStatusText($project['EtatProjet']), 0, 1);
        
        $pdf->Ln(10);
        
        // Add tasks information
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, utf8_decode('Tâches du projet'), 0, 1, 'L');
        
        if (count($tasks) > 0) {
            // Table headers
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(200, 200, 200);
            $pdf->Cell(15, 8, 'ID', 1, 0, 'C', true);
            $pdf->Cell(85, 8, 'Libellé', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'Date début', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'Date fin', 1, 0, 'C', true);
            $pdf->Cell(30, 8, 'État', 1, 1, 'C', true);
            
            // Table data
            $pdf->SetFont('Arial', '', 9);
            foreach ($tasks as $task) {
                $pdf->Cell(15, 8, $task['IdTache'], 1, 0, 'C');
                $pdf->Cell(85, 8, utf8_decode($task['LibelleTache']), 1, 0, 'L');
                $pdf->Cell(30, 8, formatDate($task['DateDebutTache']), 1, 0, 'C');
                $pdf->Cell(30, 8, formatDate($task['DateFinTache']), 1, 0, 'C');
                $pdf->Cell(30, 8, getTaskStatusText($task['EtatTache']), 1, 1, 'C');
            }
        } else {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 8, 'Aucune tâche pour ce projet.', 0, 1, 'L');
        }
        
        // Add footer
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Page ' . $pdf->PageNo(), 0, 0, 'C');
        
        // Output PDF
        $pdf->Output('D', $filename . '.pdf');
        exit;
    }
}
?>
