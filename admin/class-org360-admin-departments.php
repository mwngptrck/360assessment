<?php
/**
 * Admin Departments Class
 * Handles department management in admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Admin_Departments {
    
    public function render() {
        // Handle form submissions
        if (isset($_POST['org360_add_department'])) {
            $this->handle_add_department();
        }
        
        if (isset($_POST['org360_update_department'])) {
            $this->handle_update_department();
        }
        
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['department_id'])) {
            $this->handle_delete_department();
        }
        
        $department_obj = new Org360_Department();
        $departments = $department_obj->get_with_stats();
        
        include ORG360_PLUGIN_DIR . 'admin/views/departments.php';
    }
    
    /**
     * Handle add department
     */
    private function handle_add_department() {
        check_admin_referer('org360_add_department', 'org360_add_department_nonce');
        
        $name = sanitize_text_field($_POST['name']);
        $description = wp_kses_post($_POST['description']);
        $manager_id = isset($_POST['manager_id']) ? intval($_POST['manager_id']) : null;
        
        $department_obj = new Org360_Department();
        $result = $department_obj->create(array(
            'name' => $name,
            'description' => $description,
            'manager_id' => $manager_id
        ));
        
        if (is_wp_error($result)) {
            add_settings_error('org360_messages', 'org360_message', $result->get_error_message(), 'error');
        } else {
            add_settings_error('org360_messages', 'org360_message', __('Department created successfully.', 'org360-assessments'), 'success');
        }
    }
    
    /**
     * Handle update department
     */
    private function handle_update_department() {
        check_admin_referer('org360_update_department', 'org360_update_department_nonce');
        
        $department_id = intval($_POST['department_id']);
        $name = sanitize_text_field($_POST['name']);
        $description = wp_kses_post($_POST['description']);
        $manager_id = isset($_POST['manager_id']) ? intval($_POST['manager_id']) : null;
        
        $department_obj = new Org360_Department();
        $result = $department_obj->update($department_id, array(
            'name' => $name,
            'description' => $description,
            'manager_id' => $manager_id
        ));
        
        if ($result !== false) {
            add_settings_error('org360_messages', 'org360_message', __('Department updated successfully.', 'org360-assessments'), 'success');
        } else {
            add_settings_error('org360_messages', 'org360_message', __('Failed to update department.', 'org360-assessments'), 'error');
        }
    }
    
    /**
     * Handle delete department
     */
    private function handle_delete_department() {
        check_admin_referer('org360_delete_department_' . $_GET['department_id']);
        
        $department_id = intval($_GET['department_id']);
        $department_obj = new Org360_Department();
        $result = $department_obj->delete($department_id);
        
        if (is_wp_error($result)) {
            add_settings_error('org360_messages', 'org360_message', $result->get_error_message(), 'error');
        } else {
            wp_redirect(admin_url('admin.php?page=org360-departments&deleted=1'));
            exit;
        }
    }
}
?>