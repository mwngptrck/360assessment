<?php
/**
 * Authentication Class
 * Handles user authentication and session management
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Auth {
    
    /**
     * Login user
     */
    public static function login($email, $password) {
        $user_obj = new Org360_User();
        $user = $user_obj->get_by_email($email);
        
        if (!$user) {
            return new WP_Error('invalid_credentials', __('Invalid email or password.', 'org360-assessments'));
        }
        
        // Verify password
        if (!wp_check_password($password, $user->password)) {
            return new WP_Error('invalid_credentials', __('Invalid email or password.', 'org360-assessments'));
        }
        
        // Check if user is active
        if ($user->status !== 'active') {
            return new WP_Error('inactive_account', __('Your account is not active. Please verify your email.', 'org360-assessments'));
        }
        
        // Set session
        self::set_session($user);
        
        return $user;
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        // Don't logout during plugin activation
        if (defined('WP_INSTALLING') && WP_INSTALLING) {
            return;
        }
        
        if (session_id()) {
            unset($_SESSION['org360_user_id']);
            unset($_SESSION['org360_user_email']);
            unset($_SESSION['org360_user_role']);
            session_destroy();
        }
    }
    
    /**
     * Set user session
     */
    private static function set_session($user) {
        // Don't set sessions during plugin activation
        if (defined('WP_INSTALLING') && WP_INSTALLING) {
            return;
        }
        
        if (!session_id() && !headers_sent()) {
            session_start();
        }
        
        $_SESSION['org360_user_id'] = $user->id;
        $_SESSION['org360_user_email'] = $user->email;
        $_SESSION['org360_user_role'] = $user->role;
        $_SESSION['org360_user_name'] = $user->full_name;
    }
    
    /**
     * Check if user is logged in
     */
    public static function is_logged_in() {
        // Don't check sessions during plugin activation
        if (defined('WP_INSTALLING') && WP_INSTALLING) {
            return false;
        }
        
        if (!session_id() && !headers_sent()) {
            session_start();
        }
        
        return isset($_SESSION['org360_user_id']) && !empty($_SESSION['org360_user_id']);
    }
    
    /**
     * Get current user ID
     */
    public static function get_current_user_id() {
        // Don't check sessions during plugin activation
        if (defined('WP_INSTALLING') && WP_INSTALLING) {
            return 0;
        }
        
        if (!session_id() && !headers_sent()) {
            session_start();
        }
        
        return isset($_SESSION['org360_user_id']) ? intval($_SESSION['org360_user_id']) : 0;
    }
    
    /**
     * Get current user
     */
    public static function get_current_user() {
        // Don't check sessions during plugin activation
        if (defined('WP_INSTALLING') && WP_INSTALLING) {
            return null;
        }
        
        $user_id = self::get_current_user_id();
        
        if (!$user_id) {
            return null;
        }
        
        $user_obj = new Org360_User();
        return $user_obj->get($user_id);
    }
    
    /**
     * Check if current user has role
     */
    public static function current_user_has_role($role) {
        if (!session_id()) {
            session_start();
        }
        
        if (!isset($_SESSION['org360_user_role'])) {
            return false;
        }
        
        if (is_array($role)) {
            return in_array($_SESSION['org360_user_role'], $role);
        }
        
        return $_SESSION['org360_user_role'] === $role;
    }
    
    /**
     * Check if current user has permission
     */
    public static function current_user_can($permission) {
        $user_id = self::get_current_user_id();
        
        if (!$user_id) {
            return false;
        }
        
        $user_obj = new Org360_User();
        return $user_obj->has_permission($user_id, $permission);
    }
    
    /**
     * Require login
     */
    public static function require_login() {
        if (!self::is_logged_in()) {
            $login_url = get_permalink(get_option('org360_login_page'));
            wp_redirect($login_url);
            exit;
        }
    }
    
    /**
     * Require role
     */
    public static function require_role($role) {
        self::require_login();
        
        if (!self::current_user_has_role($role)) {
            wp_die(__('You do not have permission to access this page.', 'org360-assessments'));
        }
    }
    
    /**
     * Require permission
     */
    public static function require_permission($permission) {
        self::require_login();
        
        if (!self::current_user_can($permission)) {
            wp_die(__('You do not have permission to perform this action.', 'org360-assessments'));
        }
    }
}