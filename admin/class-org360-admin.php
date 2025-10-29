<?php
/**
 * Admin Class
 * Main admin functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_admin_actions'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Org360 Assessments', 'org360-assessments'),
            __('Org360 Assessments', 'org360-assessments'),
            'manage_options',
            'org360-assessments',
            array($this, 'dashboard_page'),
            'dashicons-clipboard',
            30
        );
        
        add_submenu_page(
            'org360-assessments',
            __('Dashboard', 'org360-assessments'),
            __('Dashboard', 'org360-assessments'),
            'manage_options',
            'org360-assessments',
            array($this, 'dashboard_page')
        );
        
        add_submenu_page(
            'org360-assessments',
            __('Users', 'org360-assessments'),
            __('Users', 'org360-assessments'),
            'manage_options',
            'org360-users',
            array($this, 'users_page')
        );
        
        add_submenu_page(
            'org360-assessments',
            __('Assessments', 'org360-assessments'),
            __('Assessments', 'org360-assessments'),
            'manage_options',
            'org360-assessments-manage',
            array($this, 'assessments_page')
        );
        
        add_submenu_page(
            'org360-assessments',
            __('Reports', 'org360-assessments'),
            __('Reports', 'org360-assessments'),
            'manage_options',
            'org360-reports',
            array($this, 'reports_page')
        );
        
        add_submenu_page(
            'org360-assessments',
            __('Departments', 'org360-assessments'),
            __('Departments', 'org360-assessments'),
            'manage_options',
            'org360-departments',
            array($this, 'departments_page')
        );
        
        add_submenu_page(
            'org360-assessments',
            __('Competencies', 'org360-assessments'),
            __('Competencies', 'org360-assessments'),
            'manage_options',
            'org360-competencies',
            array($this, 'competencies_page')
        );
        
        add_submenu_page(
            'org360-assessments',
            __('Settings', 'org360-assessments'),
            __('Settings', 'org360-assessments'),
            'manage_options',
            'org360-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Dashboard page
     */
    public function dashboard_page() {
        $user_obj = new Org360_User();
        $assessment_obj = new Org360_Assessment();
        $assignment_obj = new Org360_Assignment();
        
        $total_users = $user_obj->count();
        $total_assessments = $assessment_obj->count();
        
        global $wpdb;
        $assignment_table = $wpdb->prefix . 'org360_assignments';
        $total_assignments = $wpdb->get_var("SELECT COUNT(*) FROM $assignment_table");
        $completed_assignments = $wpdb->get_var("SELECT COUNT(*) FROM $assignment_table WHERE status = 'completed'");
        
        include ORG360_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    /**
     * Users page
     */
    public function users_page() {
        $admin_users = new Org360_Admin_Users();
        $admin_users->render();
    }
    
    /**
     * Assessments page
     */
    public function assessments_page() {
        $admin_assessments = new Org360_Admin_Assessments();
        $admin_assessments->render();
    }
    
    /**
     * Reports page
     */
    public function reports_page() {
        $admin_reports = new Org360_Admin_Reports();
        $admin_reports->render();
    }
    
    /**
     * Departments page
     */
    public function departments_page() {
        $admin_departments = new Org360_Admin_Departments();
        $admin_departments->render();
    }
    
    /**
     * Competencies page
     */
    public function competencies_page() {
        $admin_competencies = new Org360_Admin_Competencies();
        $admin_competencies->render();
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        $admin_settings = new Org360_Admin_Settings();
        $admin_settings->render();
    }
    
    /**
     * Handle admin actions
     */
    public function handle_admin_actions() {
        // Handle AJAX requests
        add_action('wp_ajax_org360_get_users', array($this, 'ajax_get_users'));
        add_action('wp_ajax_org360_assign_users', array($this, 'ajax_assign_users'));
        add_action('wp_ajax_org360_get_competencies', array($this, 'ajax_get_competencies'));
        add_action('wp_ajax_org360_save_questionnaire', array($this, 'ajax_save_questionnaire'));
        add_action('wp_ajax_org360_delete_questionnaire', array($this, 'ajax_delete_questionnaire'));
    }
    
    /**
     * AJAX: Get users for assignment
     */
    public function ajax_get_users() {
        check_ajax_referer('org360_admin_nonce', 'nonce');
        
        $user_obj = new Org360_User();
        $users = $user_obj->get_all(array('limit' => 1000));
        
        wp_send_json_success($users);
    }
    
    /**
     * AJAX: Assign users to assessment
     */
    public function ajax_assign_users() {
        check_ajax_referer('org360_admin_nonce', 'nonce');
        
        $assessment_id = intval($_POST['assessment_id']);
        $user_ids = isset($_POST['user_ids']) ? array_map('intval', $_POST['user_ids']) : array();
        
        // If assessment_id is 0, check for session data
        if ($assessment_id === 0) {
            if (isset($_SESSION['org360_last_created_assessment'])) {
                $assessment_id = intval($_SESSION['org360_last_created_assessment']);
            } else {
                wp_send_json_error(array('message' => __('No assessment found. Please create an assessment first.', 'org360-assessments')));
                return;
            }
        }
        
        if (empty($user_ids)) {
            wp_send_json_error(array('message' => __('Please select at least one user.', 'org360-assessments')));
            return;
        }
        
        // Exclude system admin (user ID 1) from assignment
        $user_ids = array_filter($user_ids, function($id) {
            return $id > 1; // Exclude system admin
        });
        
        if (empty($user_ids)) {
            wp_send_json_error(array('message' => __('Cannot assign to system administrator.', 'org360-assessments')));
            return;
        }
        
        $assignment_obj = new Org360_Assignment();
        $results = $assignment_obj->bulk_assign($assessment_id, $user_ids, get_current_user_id());
        
        wp_send_json_success(array(
            'message' => sprintf(
                __('Assessment assigned to %d users. %d failed.', 'org360-assessments'),
                count($results['success']),
                count($results['failed'])
            ),
            'results' => $results
        ));
    }
    
    /**
     * AJAX: Get competencies
     */
    public function ajax_get_competencies() {
        check_ajax_referer('org360_admin_nonce', 'nonce');
        
        $competency_obj = new Org360_Competency();
        $competencies = $competency_obj->get_all();
        
        wp_send_json_success($competencies);
    }
    
    /**
     * AJAX: Save questionnaire
     */
    public function ajax_save_questionnaire() {
        check_ajax_referer('org360_admin_nonce', 'nonce');
        
        $questionnaire_data = isset($_POST['questionnaire']) ? $_POST['questionnaire'] : array();
        
        if (empty($questionnaire_data)) {
            wp_send_json_error(array('message' => __('Invalid data.', 'org360-assessments')));
        }
        
        $questionnaire_obj = new Org360_Questionnaire();
        
        if (!empty($questionnaire_data['id'])) {
            // Update existing
            $result = $questionnaire_obj->update($questionnaire_data['id'], $questionnaire_data);
            $questionnaire_id = $questionnaire_data['id'];
        } else {
            // Create new
            $result = $questionnaire_obj->create($questionnaire_data);
            $questionnaire_id = $result;
        }
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array(
            'message' => __('Questionnaire saved successfully.', 'org360-assessments'),
            'questionnaire_id' => $questionnaire_id
        ));
    }
    
    /**
     * AJAX: Delete questionnaire
     */
    public function ajax_delete_questionnaire() {
        check_ajax_referer('org360_admin_nonce', 'nonce');
        
        $questionnaire_id = intval($_POST['questionnaire_id']);
        
        if (empty($questionnaire_id)) {
            wp_send_json_error(array('message' => __('Invalid data.', 'org360-assessments')));
        }
        
        $questionnaire_obj = new Org360_Questionnaire();
        $result = $questionnaire_obj->delete($questionnaire_id);
        
        if ($result) {
            wp_send_json_success(array('message' => __('Questionnaire deleted successfully.', 'org360-assessments')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete questionnaire.', 'org360-assessments')));
        }
    }
}