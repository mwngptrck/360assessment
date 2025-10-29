<?php
/**
 * Shortcodes Class
 * Handles all frontend shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Shortcodes {
    
    public function __construct() {
        add_shortcode('org360_login', array($this, 'login_form'));
        add_shortcode('org360_register', array($this, 'register_form'));
        add_shortcode('org360_dashboard', array($this, 'dashboard'));
        add_shortcode('org360_view_assessments', array($this, 'view_assessments'));
        add_shortcode('org360_complete_assessment', array($this, 'complete_assessment'));
        add_shortcode('org360_view_results', array($this, 'view_results'));
        add_shortcode('org360_forgot_password', array($this, 'forgot_password_form'));
        add_shortcode('org360_reset_password', array($this, 'reset_password_form'));
        
        // Handle form submissions
        add_action('init', array($this, 'handle_form_submissions'));
    }
    
    /**
     * Login form shortcode
     */
    public function login_form() {
        // Redirect if already logged in
        if (Org360_Auth::is_logged_in()) {
            wp_redirect(get_permalink(get_option('org360_dashboard_page')));
            exit;
        }
        
        ob_start();
        include ORG360_PLUGIN_DIR . 'templates/login-form.php';
        return ob_get_clean();
    }
    
    /**
     * Register form shortcode
     */
    public function register_form() {
        // Redirect if already logged in
        if (Org360_Auth::is_logged_in()) {
            wp_redirect(get_permalink(get_option('org360_dashboard_page')));
            exit;
        }
        
        ob_start();
        include ORG360_PLUGIN_DIR . 'templates/register-form.php';
        return ob_get_clean();
    }
    
    /**
     * Dashboard shortcode
     */
    public function dashboard() {
        if (!Org360_Auth::is_logged_in()) {
            return '<p>' . __('Please log in to view your dashboard.', 'org360-assessments') . '</p>';
        }
        
        ob_start();
        include ORG360_PLUGIN_DIR . 'templates/dashboard.php';
        return ob_get_clean();
    }
    
    /**
     * View assessments shortcode
     */
    public function view_assessments() {
        if (!Org360_Auth::is_logged_in()) {
            return '<p>' . __('Please log in to view assessments.', 'org360-assessments') . '</p>';
        }
        
        ob_start();
        include ORG360_PLUGIN_DIR . 'templates/view-assessments.php';
        return ob_get_clean();
    }
    
    /**
     * Complete assessment shortcode
     */
    public function complete_assessment() {
        if (!Org360_Auth::is_logged_in()) {
            return '<p>' . __('Please log in to complete assessments.', 'org360-assessments') . '</p>';
        }
        
        ob_start();
        include ORG360_PLUGIN_DIR . 'templates/complete-assessment.php';
        return ob_get_clean();
    }
    
    /**
     * View results shortcode
     */
    public function view_results() {
        if (!Org360_Auth::is_logged_in()) {
            return '<p>' . __('Please log in to view results.', 'org360-assessments') . '</p>';
        }
        
        ob_start();
        include ORG360_PLUGIN_DIR . 'templates/view-results.php';
        return ob_get_clean();
    }
    
    /**
     * Forgot password form shortcode
     */
    public function forgot_password_form() {
        ob_start();
        include ORG360_PLUGIN_DIR . 'templates/forgot-password-form.php';
        return ob_get_clean();
    }
    
    /**
     * Reset password form shortcode
     */
    public function reset_password_form() {
        ob_start();
        include ORG360_PLUGIN_DIR . 'templates/reset-password-form.php';
        return ob_get_clean();
    }
    
    /**
     * Handle form submissions
     */
    public function handle_form_submissions() {
        // Handle login
        if (isset($_POST['org360_login_submit'])) {
            $this->handle_login_submission();
        }
        
        // Handle registration
        if (isset($_POST['org360_register_submit'])) {
            $this->handle_register_submission();
        }
        
        // Handle forgot password
        if (isset($_POST['org360_forgot_password_submit'])) {
            $this->handle_forgot_password_submission();
        }
        
        // Handle reset password
        if (isset($_POST['org360_reset_password_submit'])) {
            $this->handle_reset_password_submission();
        }
        
        // Handle assessment submission
        if (isset($_POST['org360_submit_assessment'])) {
            $this->handle_assessment_submission();
        }
    }
    
    /**
     * Handle login submission
     */
    private function handle_login_submission() {
        if (!isset($_POST['org360_login_nonce']) || !wp_verify_nonce($_POST['org360_login_nonce'], 'org360_login')) {
            return;
        }
        
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        
        $result = Org360_Auth::login($email, $password);
        
        if (is_wp_error($result)) {
            $_SESSION['org360_error'] = $result->get_error_message();
        } else {
            wp_redirect(get_permalink(get_option('org360_dashboard_page')));
            exit;
        }
    }
    
    /**
     * Handle registration submission
     */
    private function handle_register_submission() {
        if (!isset($_POST['org360_register_nonce']) || !wp_verify_nonce($_POST['org360_register_nonce'], 'org360_register')) {
            return;
        }
        
        $full_name = sanitize_text_field($_POST['full_name']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate
        if (empty($full_name) || empty($email) || empty($password)) {
            $_SESSION['org360_error'] = __('All fields are required.', 'org360-assessments');
            return;
        }
        
        if ($password !== $confirm_password) {
            $_SESSION['org360_error'] = __('Passwords do not match.', 'org360-assessments');
            return;
        }
        
        $password_validation = Org360_Security::validate_password($password);
        if (is_wp_error($password_validation)) {
            $_SESSION['org360_error'] = $password_validation->get_error_message();
            return;
        }
        
        // Create user
        $user_obj = new Org360_User();
        $result = $user_obj->create(array(
            'full_name' => $full_name,
            'email' => $email,
            'password' => wp_hash_password($password),
            'role' => 'employee',
            'status' => 'pending'
        ));
        
        if (is_wp_error($result)) {
            $_SESSION['org360_error'] = $result->get_error_message();
        } else {
            $_SESSION['org360_success'] = __('Registration successful! Please check your email to verify your account.', 'org360-assessments');
        }
    }
    
    /**
     * Handle forgot password submission
     */
    private function handle_forgot_password_submission() {
        if (!isset($_POST['org360_forgot_password_nonce']) || !wp_verify_nonce($_POST['org360_forgot_password_nonce'], 'org360_forgot_password')) {
            return;
        }
        
        $email = sanitize_email($_POST['email']);
        
        $user_obj = new Org360_User();
        $result = $user_obj->generate_reset_token($email);
        
        if (is_wp_error($result)) {
            $_SESSION['org360_error'] = $result->get_error_message();
        } else {
            $_SESSION['org360_success'] = __('Password reset link has been sent to your email.', 'org360-assessments');
        }
    }
    
    /**
     * Handle reset password submission
     */
    private function handle_reset_password_submission() {
        if (!isset($_POST['org360_reset_password_nonce']) || !wp_verify_nonce($_POST['org360_reset_password_nonce'], 'org360_reset_password')) {
            return;
        }
        
        $token = sanitize_text_field($_POST['token']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($password !== $confirm_password) {
            $_SESSION['org360_error'] = __('Passwords do not match.', 'org360-assessments');
            return;
        }
        
        $password_validation = Org360_Security::validate_password($password);
        if (is_wp_error($password_validation)) {
            $_SESSION['org360_error'] = $password_validation->get_error_message();
            return;
        }
        
        $user_obj = new Org360_User();
        $result = $user_obj->reset_password($token, $password);
        
        if (is_wp_error($result)) {
            $_SESSION['org360_error'] = $result->get_error_message();
        } else {
            $_SESSION['org360_success'] = __('Password reset successful! You can now log in.', 'org360-assessments');
            wp_redirect(get_permalink(get_option('org360_login_page')));
            exit;
        }
    }
    
    /**
     * Handle assessment submission
     */
    private function handle_assessment_submission() {
        if (!isset($_POST['org360_assessment_nonce']) || !wp_verify_nonce($_POST['org360_assessment_nonce'], 'org360_submit_assessment')) {
            return;
        }
        
        $assignment_id = intval($_POST['assignment_id']);
        $responses = isset($_POST['responses']) ? $_POST['responses'] : array();
        
        // Save responses
        $response_obj = new Org360_Response();
        
        foreach ($responses as $question_id => $response_data) {
            $response_obj->save_response($assignment_id, $question_id, $response_data);
        }
        
        // Mark assignment as completed
        $assignment_obj = new Org360_Assignment();
        $assignment_obj->mark_completed($assignment_id);
        
        $_SESSION['org360_success'] = __('Assessment completed successfully!', 'org360-assessments');
        wp_redirect(get_permalink(get_option('org360_results_page')));
        exit;
    }
}