<?php
/**
 * Admin Competencies Class
 * Handles competency management in admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Admin_Competencies {
    
    public function render() {
        // Handle form submissions
        if (isset($_POST['org360_add_competency'])) {
            $this->handle_add_competency();
        }
        
        if (isset($_POST['org360_update_competency'])) {
            $this->handle_update_competency();
        }
        
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['competency_id'])) {
            $this->handle_delete_competency();
        }
        
        $competency_obj = new Org360_Competency();
        $competencies = $competency_obj->get_all();
        
        include ORG360_PLUGIN_DIR . 'admin/views/competencies.php';
    }
    
    /**
     * Handle add competency
     */
    private function handle_add_competency() {
        check_admin_referer('org360_add_competency', 'org360_add_competency_nonce');
        
        $name = sanitize_text_field($_POST['name']);
        $description = wp_kses_post($_POST['description']);
        
        $competency_obj = new Org360_Competency();
        $result = $competency_obj->create(array(
            'name' => $name,
            'description' => $description
        ));
        
        if (is_wp_error($result)) {
            add_settings_error('org360_messages', 'org360_message', $result->get_error_message(), 'error');
        } else {
            add_settings_error('org360_messages', 'org360_message', __('Competency created successfully.', 'org360-assessments'), 'success');
        }
    }
    
    /**
     * Handle update competency
     */
    private function handle_update_competency() {
        check_admin_referer('org360_update_competency', 'org360_update_competency_nonce');
        
        $competency_id = intval($_POST['competency_id']);
        $name = sanitize_text_field($_POST['name']);
        $description = wp_kses_post($_POST['description']);
        
        $competency_obj = new Org360_Competency();
        $result = $competency_obj->update($competency_id, array(
            'name' => $name,
            'description' => $description
        ));
        
        if ($result !== false) {
            add_settings_error('org360_messages', 'org360_message', __('Competency updated successfully.', 'org360-assessments'), 'success');
        } else {
            add_settings_error('org360_messages', 'org360_message', __('Failed to update competency.', 'org360-assessments'), 'error');
        }
    }
    
    /**
     * Handle delete competency
     */
    private function handle_delete_competency() {
        check_admin_referer('org360_delete_competency_' . $_GET['competency_id']);
        
        $competency_id = intval($_GET['competency_id']);
        $competency_obj = new Org360_Competency();
        $competency_obj->delete($competency_id);
        
        wp_redirect(admin_url('admin.php?page=org360-competencies&deleted=1'));
        exit;
    }
}