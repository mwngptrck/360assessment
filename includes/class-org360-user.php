<?php
/**
 * User Management Class
 * Handles all user-related operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_User {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'org360_users';
    }
    
    /**
     * Create a new user
     */
    public function create($data) {
        global $wpdb;
        
        // Validate required fields
        if (empty($data['email']) || empty($data['password']) || empty($data['full_name'])) {
            return new WP_Error('missing_fields', __('Required fields are missing.', 'org360-assessments'));
        }
        
        // Check if email already exists
        if ($this->email_exists($data['email'])) {
            return new WP_Error('email_exists', __('Email already exists.', 'org360-assessments'));
        }
        
        // Generate verification token
        $verification_token = wp_generate_password(32, false);
        
        // Prepare data
        $insert_data = array(
            'full_name' => sanitize_text_field($data['full_name']),
            'email' => sanitize_email($data['email']),
            'password' => $data['password'], // Should already be hashed
            'role' => isset($data['role']) ? sanitize_text_field($data['role']) : 'employee',
            'status' => isset($data['status']) ? sanitize_text_field($data['status']) : 'pending',
            'department_id' => isset($data['department_id']) ? intval($data['department_id']) : null,
            'verification_token' => $verification_token
        );
        
        $result = $wpdb->insert($this->table_name, $insert_data);
        
        if ($result) {
            $user_id = $wpdb->insert_id;
            
            // Send verification email if status is pending
            if ($insert_data['status'] === 'pending') {
                $this->send_verification_email($user_id, $data['email'], $verification_token);
            }
            
            return $user_id;
        }
        
        return new WP_Error('creation_failed', __('Failed to create user.', 'org360-assessments'));
    }
    
    /**
     * Get user by ID
     */
    public function get($user_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $user_id
        ));
    }
    
    /**
     * Get user by email
     */
    public function get_by_email($email) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE email = %s",
            sanitize_email($email)
        ));
    }
    
    /**
     * Update user
     */
    public function update($user_id, $data) {
        global $wpdb;
        
        $update_data = array();
        
        if (isset($data['full_name'])) {
            $update_data['full_name'] = sanitize_text_field($data['full_name']);
        }
        
        if (isset($data['email'])) {
            $email = sanitize_email($data['email']);
            // Check if email is already taken by another user
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$this->table_name} WHERE email = %s AND id != %d",
                $email,
                $user_id
            ));
            
            if ($existing) {
                return new WP_Error('email_exists', __('Email already exists.', 'org360-assessments'));
            }
            
            $update_data['email'] = $email;
        }
        
        if (isset($data['password'])) {
            $update_data['password'] = $data['password']; // Should already be hashed
        }
        
        if (isset($data['role'])) {
            $update_data['role'] = sanitize_text_field($data['role']);
        }
        
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
        }
        
        if (isset($data['department_id'])) {
            $update_data['department_id'] = intval($data['department_id']);
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        return $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $user_id)
        );
    }
    
    /**
     * Delete user
     */
    public function delete($user_id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_name,
            array('id' => $user_id)
        );
    }
    
    /**
     * Get all users
     */
    public function get_all($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'role' => '',
            'status' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 100,
            'offset' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        
        if (!empty($args['role'])) {
            $where[] = $wpdb->prepare("role = %s", $args['role']);
        }
        
        if (!empty($args['status'])) {
            $where[] = $wpdb->prepare("status = %s", $args['status']);
        }
        
        $where_clause = implode(' AND ', $where);
        
        $query = "SELECT * FROM {$this->table_name} 
                  WHERE {$where_clause} 
                  ORDER BY {$args['orderby']} {$args['order']} 
                  LIMIT {$args['limit']} OFFSET {$args['offset']}";
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Count users
     */
    public function count($args = array()) {
        global $wpdb;
        
        $where = array('1=1');
        
        if (!empty($args['role'])) {
            $where[] = $wpdb->prepare("role = %s", $args['role']);
        }
        
        if (!empty($args['status'])) {
            $where[] = $wpdb->prepare("status = %s", $args['status']);
        }
        
        $where_clause = implode(' AND ', $where);
        
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}");
    }
    
    /**
     * Check if email exists
     */
    public function email_exists($email) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE email = %s",
            sanitize_email($email)
        ));
        
        return $count > 0;
    }
    
    /**
     * Verify user email
     */
    public function verify_email($token) {
        global $wpdb;
        
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE verification_token = %s",
            $token
        ));
        
        if (!$user) {
            return new WP_Error('invalid_token', __('Invalid verification token.', 'org360-assessments'));
        }
        
        // Update user status
        $result = $wpdb->update(
            $this->table_name,
            array(
                'status' => 'active',
                'verification_token' => null
            ),
            array('id' => $user->id)
        );
        
        if ($result !== false) {
            return $user;
        }
        
        return new WP_Error('verification_failed', __('Email verification failed.', 'org360-assessments'));
    }
    
    /**
     * Generate password reset token
     */
    public function generate_reset_token($email) {
        global $wpdb;
        
        $user = $this->get_by_email($email);
        
        if (!$user) {
            return new WP_Error('user_not_found', __('User not found.', 'org360-assessments'));
        }
        
        $reset_token = wp_generate_password(32, false);
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $result = $wpdb->update(
            $this->table_name,
            array(
                'reset_token' => $reset_token,
                'reset_token_expiry' => $expiry
            ),
            array('id' => $user->id)
        );
        
        if ($result !== false) {
            // Send reset email
            $this->send_reset_email($user->id, $email, $reset_token);
            return true;
        }
        
        return new WP_Error('token_generation_failed', __('Failed to generate reset token.', 'org360-assessments'));
    }
    
    /**
     * Reset password with token
     */
    public function reset_password($token, $new_password) {
        global $wpdb;
        
        $user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE reset_token = %s",
            $token
        ));
        
        if (!$user) {
            return new WP_Error('invalid_token', __('Invalid reset token.', 'org360-assessments'));
        }
        
        // Check if token is expired
        if (strtotime($user->reset_token_expiry) < time()) {
            return new WP_Error('token_expired', __('Reset token has expired.', 'org360-assessments'));
        }
        
        // Update password
        $result = $wpdb->update(
            $this->table_name,
            array(
                'password' => wp_hash_password($new_password),
                'reset_token' => null,
                'reset_token_expiry' => null
            ),
            array('id' => $user->id)
        );
        
        if ($result !== false) {
            return true;
        }
        
        return new WP_Error('reset_failed', __('Password reset failed.', 'org360-assessments'));
    }
    
    /**
     * Send verification email
     */
    private function send_verification_email($user_id, $email, $token) {
        $verification_url = add_query_arg(array(
            'action' => 'verify_email',
            'token' => $token
        ), get_permalink(get_option('org360_login_page')));
        
        $email_obj = new Org360_Email();
        $email_obj->send_verification_email($email, $verification_url);
    }
    
    /**
     * Send password reset email
     */
    private function send_reset_email($user_id, $email, $token) {
        $reset_url = add_query_arg(array(
            'token' => $token
        ), get_permalink(get_option('org360_reset_password_page')));
        
        $email_obj = new Org360_Email();
        $email_obj->send_reset_email($email, $reset_url);
    }
    
    /**
     * Get available roles
     */
    public static function get_roles() {
        return array(
            'admin' => __('Administrator', 'org360-assessments'),
            'manager' => __('Manager', 'org360-assessments'),
            'employee' => __('Employee', 'org360-assessments'),
            'assessor' => __('Assessor', 'org360-assessments')
        );
    }
    
    /**
     * Check if user has permission
     */
    public function has_permission($user_id, $permission) {
        $user = $this->get($user_id);
        
        if (!$user) {
            return false;
        }
        
        $permissions = array(
            'admin' => array('manage_users', 'manage_assessments', 'view_reports', 'manage_settings'),
            'manager' => array('manage_assessments', 'view_reports'),
            'employee' => array('complete_assessments', 'view_own_results'),
            'assessor' => array('complete_assessments', 'view_assigned_assessments')
        );
        
        if (!isset($permissions[$user->role])) {
            return false;
        }
        
        return in_array($permission, $permissions[$user->role]);
    }
}