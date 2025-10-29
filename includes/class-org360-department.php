<?php
/**
 * Department/Group Management Class
 * Handles user departments and groups
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Department {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'org360_departments';
    }
    
    /**
     * Create departments table
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'org360_departments';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            manager_id bigint(20) UNSIGNED DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY manager_id (manager_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Insert default departments
        self::insert_default_departments();
    }
    
    /**
     * Insert default departments
     */
    private static function insert_default_departments() {
        global $wpdb;
        $table = $wpdb->prefix . 'org360_departments';
        
        $default_departments = array(
            array(
                'name' => 'Human Resources',
                'description' => 'HR department handling employee relations and recruitment'
            ),
            array(
                'name' => 'Information Technology',
                'description' => 'IT department managing technology and systems'
            ),
            array(
                'name' => 'Marketing',
                'description' => 'Marketing department handling promotions and campaigns'
            ),
            array(
                'name' => 'Sales',
                'description' => 'Sales department managing customer relationships'
            ),
            array(
                'name' => 'Finance',
                'description' => 'Finance department managing budgets and accounting'
            )
        );
        
        foreach ($default_departments as $dept) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE name = %s",
                $dept['name']
            ));
            
            if (!$exists) {
                $wpdb->insert($table, $dept);
            }
        }
    }
    
    /**
     * Create a new department
     */
    public function create($data) {
        global $wpdb;
        
        if (empty($data['name'])) {
            return new WP_Error('missing_fields', __('Department name is required.', 'org360-assessments'));
        }
        
        $insert_data = array(
            'name' => sanitize_text_field($data['name']),
            'description' => isset($data['description']) ? wp_kses_post($data['description']) : '',
            'manager_id' => isset($data['manager_id']) ? intval($data['manager_id']) : null
        );
        
        $result = $wpdb->insert($this->table_name, $insert_data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return new WP_Error('creation_failed', __('Failed to create department.', 'org360-assessments'));
    }
    
    /**
     * Get department by ID
     */
    public function get($department_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $department_id
        ));
    }
    
    /**
     * Update department
     */
    public function update($department_id, $data) {
        global $wpdb;
        
        $update_data = array();
        
        if (isset($data['name'])) {
            $update_data['name'] = sanitize_text_field($data['name']);
        }
        
        if (isset($data['description'])) {
            $update_data['description'] = wp_kses_post($data['description']);
        }
        
        if (isset($data['manager_id'])) {
            $update_data['manager_id'] = intval($data['manager_id']);
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        return $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $department_id)
        );
    }
    
    /**
     * Delete department
     */
    public function delete($department_id) {
        global $wpdb;
        
        // Check if department has users
        $user_table = $wpdb->prefix . 'org360_users';
        $user_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $user_table WHERE department_id = %d",
            $department_id
        ));
        
        if ($user_count > 0) {
            return new WP_Error('has_users', __('Cannot delete department with assigned users.', 'org360-assessments'));
        }
        
        return $wpdb->delete(
            $this->table_name,
            array('id' => $department_id)
        );
    }
    
    /**
     * Get all departments
     */
    public function get_all() {
        global $wpdb;
        $user_table = $wpdb->prefix . 'org360_users';
        
        $query = "SELECT d.*, u.full_name as manager_name, u.email as manager_email,
                  (SELECT COUNT(*) FROM $user_table WHERE department_id = d.id) as user_count
                  FROM {$this->table_name} d 
                  LEFT JOIN $user_table u ON d.manager_id = u.id 
                  ORDER BY d.name ASC";
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get departments with user statistics
     */
    public function get_with_stats() {
        global $wpdb;
        $user_table = $wpdb->prefix . 'org360_users';
        $assignment_table = $wpdb->prefix . 'org360_assignments';
        
        $query = "SELECT d.*, 
                  (SELECT COUNT(*) FROM $user_table WHERE department_id = d.id) as user_count,
                  (SELECT COUNT(*) FROM $user_table WHERE department_id = d.id AND status = 'active') as active_users,
                  (SELECT COUNT(*) FROM $assignment_table a 
                   INNER JOIN $user_table u ON a.user_id = u.id 
                   WHERE u.department_id = d.id AND a.status = 'completed') as completed_assessments
                  FROM {$this->table_name} d 
                  ORDER BY d.name ASC";
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Get departments for dropdown
     */
    public function get_for_dropdown() {
        $departments = $this->get_all();
        $dropdown = array();
        
        foreach ($departments as $dept) {
            $dropdown[$dept->id] = $dept->name;
        }
        
        return $dropdown;
    }
    
    /**
     * Get users by department
     */
    public function get_users($department_id) {
        global $wpdb;
        $user_table = $wpdb->prefix . 'org360_users';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $user_table WHERE department_id = %d ORDER BY full_name ASC",
            $department_id
        ));
    }
    
    /**
     * Get department statistics
     */
    public function get_statistics($department_id) {
        global $wpdb;
        $user_table = $wpdb->prefix . 'org360_users';
        $assignment_table = $wpdb->prefix . 'org360_assignments';
        
        $stats = array(
            'total_users' => 0,
            'active_users' => 0,
            'pending_users' => 0,
            'completed_assessments' => 0,
            'in_progress_assessments' => 0,
            'pending_assessments' => 0
        );
        
        // User statistics
        $user_stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
             COUNT(*) as total_users,
             SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
             SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_users
             FROM $user_table WHERE department_id = %d",
            $department_id
        ));
        
        if ($user_stats) {
            $stats['total_users'] = $user_stats->total_users;
            $stats['active_users'] = $user_stats->active_users;
            $stats['pending_users'] = $user_stats->pending_users;
        }
        
        // Assessment statistics
        $assessment_stats = $wpdb->get_row($wpdb->prepare(
            "SELECT 
             SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
             SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
             SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
             FROM $assignment_table a 
             INNER JOIN $user_table u ON a.user_id = u.id 
             WHERE u.department_id = %d",
            $department_id
        ));
        
        if ($assessment_stats) {
            $stats['completed_assessments'] = $assessment_stats->completed;
            $stats['in_progress_assessments'] = $assessment_stats->in_progress;
            $stats['pending_assessments'] = $assessment_stats->pending;
        }
        
        return $stats;
    }
}