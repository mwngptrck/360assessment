<?php
/**
 * Admin Reports Class
 * Handles reports and exports in admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Admin_Reports {
    
    public function render() {
        // Handle PDF export
        if (isset($_GET['export_pdf']) && isset($_GET['assignment_id'])) {
            $this->export_single_pdf();
        }
        
        if (isset($_POST['org360_export_batch'])) {
            $this->export_batch_pdf();
        }
        
        $this->list_reports();
    }
    
    /**
     * List reports
     */
    private function list_reports() {
        global $wpdb;
        $assignment_table = $wpdb->prefix . 'org360_assignments';
        $assessment_table = $wpdb->prefix . 'org360_assessments';
        $user_table = $wpdb->prefix . 'org360_users';
        
        $query = "SELECT a.*, ast.title as assessment_title, u.full_name, u.email 
                  FROM {$assignment_table} a 
                  INNER JOIN {$assessment_table} ast ON a.assessment_id = ast.id 
                  INNER JOIN {$user_table} u ON a.user_id = u.id 
                  WHERE a.status = 'completed' 
                  ORDER BY a.completed_at DESC 
                  LIMIT 100";
        
        $completed_assignments = $wpdb->get_results($query);
        
        include ORG360_PLUGIN_DIR . 'admin/views/reports.php';
    }
    
    /**
     * Export single PDF
     */
    private function export_single_pdf() {
        check_admin_referer('org360_export_pdf_' . $_GET['assignment_id']);
        
        $assignment_id = intval($_GET['assignment_id']);
        $pdf_obj = new Org360_PDF();
        $pdf_obj->generate_report($assignment_id);
        exit;
    }
    
    /**
     * Export batch PDF
     */
    private function export_batch_pdf() {
        check_admin_referer('org360_export_batch', 'org360_export_batch_nonce');
        
        $assignment_ids = isset($_POST['assignment_ids']) ? array_map('intval', $_POST['assignment_ids']) : array();
        
        if (empty($assignment_ids)) {
            add_settings_error('org360_messages', 'org360_message', __('Please select at least one report to export.', 'org360-assessments'), 'error');
            return;
        }
        
        $pdf_obj = new Org360_PDF();
        $zip_path = $pdf_obj->generate_batch_reports($assignment_ids);
        
        if (is_wp_error($zip_path)) {
            add_settings_error('org360_messages', 'org360_message', $zip_path->get_error_message(), 'error');
            return;
        }
        
        // Download the ZIP file
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zip_path) . '"');
        header('Content-Length: ' . filesize($zip_path));
        readfile($zip_path);
        unlink($zip_path);
        exit;
    }
}