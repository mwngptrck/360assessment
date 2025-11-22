<?php
/**
 * Public-facing functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Public {
    
    public function __construct() {
        add_action('init', array($this, 'handle_actions'));
    }
    
    /**
     * Handle public actions
     */
    public function handle_actions() {
        // Handle email verification
        if (isset($_GET['action']) && $_GET['action'] === 'verify_email' && isset($_GET['token'])) {
            $this->handle_email_verification();
        }
        
        // Handle logout
        if (isset($_GET['action']) && $_GET['action'] === 'org360_logout') {
            $this->handle_logout();
        }
    }
    
    /**
     * Handle email verification
     */
    private function handle_email_verification() {
        $token = sanitize_text_field($_GET['token']);
        
        $user_obj = new Org360_User();
        $result = $user_obj->verify_email($token);
        
        if (is_wp_error($result)) {
            wp_die($result->get_error_message());
        }
        
        // Redirect to login with success message
        $login_url = add_query_arg('verified', '1', get_permalink(get_option('org360_login_page')));
        wp_redirect($login_url);
        exit;
    }
    
    /**
     * Handle logout
     */
    private function handle_logout() {
        Org360_Auth::logout();
        
        $login_url = add_query_arg('logged_out', '1', get_permalink(get_option('org360_login_page')));
        wp_redirect($login_url);
        exit;
    }
}