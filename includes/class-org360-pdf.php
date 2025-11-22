<?php
/**
 * PDF Generation Class
 * Handles PDF report generation using TCPDF
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include TCPDF library
require_once(ORG360_PLUGIN_DIR . 'includes/tcpdf/tcpdf.php');

class Org360_PDF {
    
    /**
     * Generate assessment report PDF
     */
    public function generate_report($assignment_id) {
        $assignment_obj = new Org360_Assignment();
        $assignment = $assignment_obj->get_with_details($assignment_id);
        
        if (!$assignment) {
            return new WP_Error('invalid_assignment', __('Invalid assignment.', 'org360-assessments'));
        }
        
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Org360 Assessments');
        $pdf->SetAuthor(Org360_Database::get_setting('organization_name', 'Org360 Assessments'));
        $pdf->SetTitle('Assessment Report - ' . $assignment->assessment->title);
        $pdf->SetSubject('360 Assessment Report');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 12);
        
        // Generate HTML content
        $html = $this->generate_report_html($assignment);
        
        // Output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output PDF document
        $filename = 'assessment-report-' . $assignment_id . '-' . date('Y-m-d') . '.pdf';
        
        return $pdf->Output($filename, 'D'); // D = download
    }
    
    /**
     * Generate report HTML
     */
    private function generate_report_html($assignment) {
        $organization_name = Org360_Database::get_setting('organization_name', 'Org360 Assessments');
        
        $response_obj = new Org360_Response();
        $responses = $response_obj->get_responses_with_questions($assignment->id);
        $score = $response_obj->calculate_score($assignment->id);
        
        $html = '
        <style>
            h1 { color: #4CAF50; font-size: 24px; margin-bottom: 10px; }
            h2 { color: #333; font-size: 18px; margin-top: 20px; margin-bottom: 10px; }
            h3 { color: #666; font-size: 14px; margin-top: 15px; margin-bottom: 5px; }
            .header { border-bottom: 3px solid #4CAF50; padding-bottom: 10px; margin-bottom: 20px; }
            .info-box { background-color: #f5f5f5; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
            .info-row { margin-bottom: 8px; }
            .label { font-weight: bold; color: #666; }
            .question { background-color: #f9f9f9; padding: 12px; margin-bottom: 15px; border-left: 4px solid #4CAF50; }
            .response { padding: 10px; margin-top: 5px; background-color: white; }
            .score-box { background-color: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px; margin: 20px 0; }
            .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 10px; }
        </style>
        
        <div class="header">
            <h1>' . esc_html($organization_name) . '</h1>
            <p style="font-size: 16px; color: #666;">360-Degree Assessment Report</p>
        </div>
        
        <div class="info-box">
            <div class="info-row">
                <span class="label">Assessment:</span> ' . esc_html($assignment->assessment->title) . '
            </div>
            <div class="info-row">
                <span class="label">Participant:</span> ' . esc_html($assignment->user->full_name) . '
            </div>
            <div class="info-row">
                <span class="label">Email:</span> ' . esc_html($assignment->user->email) . '
            </div>
            <div class="info-row">
                <span class="label">Completed:</span> ' . date('F j, Y', strtotime($assignment->completed_at)) . '
            </div>
        </div>';
        
        if ($score > 0) {
            $html .= '
            <div class="score-box">
                <h2 style="color: white; margin: 0;">Overall Score</h2>
                <p style="font-size: 36px; font-weight: bold; margin: 10px 0;">' . $score . '/5</p>
            </div>';
        }
        
        $html .= '<h2>Assessment Responses</h2>';
        
        if (!empty($responses)) {
            $question_num = 1;
            foreach ($responses as $response) {
                $html .= '
                <div class="question">
                    <h3>Question ' . $question_num . '</h3>
                    <p><strong>' . esc_html($response->question_text) . '</strong></p>
                    <div class="response">';
                
                if ($response->question_type === 'rating') {
                    $html .= '<p>Rating: <strong>' . esc_html($response->response_value) . '/5</strong></p>';
                } elseif ($response->question_type === 'yes_no') {
                    $html .= '<p><strong>' . esc_html($response->response_value) . '</strong></p>';
                } elseif ($response->question_type === 'multiple_choice') {
                    $html .= '<p><strong>' . esc_html($response->response_value) . '</strong></p>';
                } else {
                    $html .= '<p>' . nl2br(esc_html($response->response_text)) . '</p>';
                }
                
                $html .= '
                    </div>
                </div>';
                
                $question_num++;
            }
        } else {
            $html .= '<p>No responses recorded.</p>';
        }
        
        $html .= '
        <div class="footer">
            <p>Generated on ' . date('F j, Y \a\t g:i A') . '</p>
            <p>&copy; ' . date('Y') . ' ' . esc_html($organization_name) . '. All rights reserved.</p>
        </div>';
        
        return $html;
    }
    
    /**
     * Generate batch reports
     */
    public function generate_batch_reports($assignment_ids) {
        if (empty($assignment_ids)) {
            return new WP_Error('no_assignments', __('No assignments provided.', 'org360-assessments'));
        }
        
        // Create ZIP file
        $zip = new ZipArchive();
        $zip_filename = 'assessment-reports-' . date('Y-m-d-His') . '.zip';
        $zip_path = sys_get_temp_dir() . '/' . $zip_filename;
        
        if ($zip->open($zip_path, ZipArchive::CREATE) !== TRUE) {
            return new WP_Error('zip_creation_failed', __('Failed to create ZIP file.', 'org360-assessments'));
        }
        
        foreach ($assignment_ids as $assignment_id) {
            $assignment_obj = new Org360_Assignment();
            $assignment = $assignment_obj->get_with_details($assignment_id);
            
            if (!$assignment) {
                continue;
            }
            
            // Create PDF
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(TRUE, 15);
            $pdf->AddPage();
            $pdf->SetFont('helvetica', '', 12);
            
            $html = $this->generate_report_html($assignment);
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // Add to ZIP
            $pdf_filename = 'report-' . $assignment_id . '-' . sanitize_file_name($assignment->user->full_name) . '.pdf';
            $zip->addFromString($pdf_filename, $pdf->Output('', 'S'));
        }
        
        $zip->close();
        
        // Return ZIP file path
        return $zip_path;
    }
}