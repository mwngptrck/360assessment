<?php
/**
 * Security Class
 * Handles security, validation, and sanitization
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Security {
    
    /**
     * Verify nonce
     */
    public static function verify_nonce($nonce, $action) {
        if (!wp_verify_nonce($nonce, $action)) {
            wp_die(__('Security check failed. Please try again.', 'org360-assessments'));
        }
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitize_input($data) {
        if (is_array($data)) {
            return array_map(array('self', 'sanitize_input'), $data);
        }
        
        return sanitize_text_field($data);
    }
    
    /**
     * Validate email
     */
    public static function validate_email($email) {
        $email = sanitize_email($email);
        
        if (!is_email($email)) {
            return new WP_Error('invalid_email', __('Invalid email address.', 'org360-assessments'));
        }
        
        return $email;
    }
    
    /**
     * Validate password strength
     */
    public static function validate_password($password) {
        if (strlen($password) < 8) {
            return new WP_Error('weak_password', __('Password must be at least 8 characters long.', 'org360-assessments'));
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return new WP_Error('weak_password', __('Password must contain at least one uppercase letter.', 'org360-assessments'));
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            return new WP_Error('weak_password', __('Password must contain at least one lowercase letter.', 'org360-assessments'));
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return new WP_Error('weak_password', __('Password must contain at least one number.', 'org360-assessments'));
        }
        
        return true;
    }
    
    /**
     * Sanitize HTML content
     */
    public static function sanitize_html($content) {
        $allowed_tags = array(
            'p' => array(),
            'br' => array(),
            'strong' => array(),
            'em' => array(),
            'u' => array(),
            'h1' => array(),
            'h2' => array(),
            'h3' => array(),
            'h4' => array(),
            'ul' => array(),
            'ol' => array(),
            'li' => array(),
            'a' => array('href' => array(), 'title' => array()),
        );
        
        return wp_kses($content, $allowed_tags);
    }
    
    /**
     * Prevent SQL injection
     */
    public static function escape_sql($value) {
        global $wpdb;
        return $wpdb->_real_escape($value);
    }
    
    /**
     * Generate secure token
     */
    public static function generate_token($length = 32) {
        return wp_generate_password($length, false);
    }
    
    /**
     * Check CSRF token
     */
    public static function check_csrf_token() {
        if (!isset($_POST['org360_nonce']) || !wp_verify_nonce($_POST['org360_nonce'], 'org360_action')) {
            return false;
        }
        return true;
    }
    
    /**
     * Rate limiting check
     */
    public static function check_rate_limit($action, $limit = 5, $period = 3600) {
        $ip = self::get_client_ip();
        $key = 'org360_rate_limit_' . $action . '_' . md5($ip);
        
        $attempts = get_transient($key);
        
        if ($attempts === false) {
            set_transient($key, 1, $period);
            return true;
        }
        
        if ($attempts >= $limit) {
            return false;
        }
        
        set_transient($key, $attempts + 1, $period);
        return true;
    }
    
    /**
     * Get client IP address
     */
    public static function get_client_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }
    
    /**
     * Sanitize file name
     */
    public static function sanitize_filename($filename) {
        return sanitize_file_name($filename);
    }
    
    /**
     * Validate file upload
     */
    public static function validate_file_upload($file, $allowed_types = array()) {
        if (!isset($file['error']) || is_array($file['error'])) {
            return new WP_Error('invalid_file', __('Invalid file upload.', 'org360-assessments'));
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_error', __('File upload error.', 'org360-assessments'));
        }
        
        if ($file['size'] > 5242880) { // 5MB
            return new WP_Error('file_too_large', __('File size exceeds 5MB limit.', 'org360-assessments'));
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!empty($allowed_types) && !in_array($mime_type, $allowed_types)) {
            return new WP_Error('invalid_file_type', __('Invalid file type.', 'org360-assessments'));
        }
        
        return true;
    }
}