<?php
/**
 * Plugin Name: Org360 Assessments
 * Plugin URI: https://org360assessments.com
 * Description: A comprehensive 360-degree assessment system for organizations with independent user management and assessment workflows.
 * Version: 1.0.0
 * Author: Org360 Team
 * Author URI: https://org360assessments.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: org360-assessments
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ORG360_VERSION', '1.0.0');
define('ORG360_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ORG360_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ORG360_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Org360 Assessments Class
 */
class Org360_Assessments {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->define_hooks();
    }
    
    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Core classes
        require_once ORG360_PLUGIN_DIR . 'includes/class-org360-database.php';
        require_once ORG360_PLUGIN_DIR . 'includes/class-org360-department.php';
        require_once ORG360_PLUGIN_DIR . 'includes/class-org360-user.php';
        require_once ORG360_PLUGIN_DIR . 'includes/class-org360-auth.php';
        require_once ORG360_PLUGIN_DIR . 'includes/class-org360-competency.php';
        require_once ORG360_PLUGIN_DIR . 'includes/class-org360-questionnaire.php';
        require_once ORG360_PLUGIN_DIR . 'includes/class-org360-assessment.php';
        require_once ORG360_PLUGIN_DIR . 'includes/class-org360-question.php';
        require_once ORG360_PLUGIN_DIR . 'includes/class-org360-response.php';
        require_once ORG360_PLUGIN_DIR . 'includes/class-org360-assignment.php';
        require_once ORG360_PLUGIN_DIR . 'includes/class-org360-email.php';
        require_once ORG360_PLUGIN_DIR . 'includes/class-org360-pdf.php';
        require_once ORG360_PLUGIN_DIR . 'includes/class-org360-security.php';
        
        // Admin classes
        if (is_admin()) {
            require_once ORG360_PLUGIN_DIR . 'admin/class-org360-admin.php';
            require_once ORG360_PLUGIN_DIR . 'admin/class-org360-admin-users.php';
            require_once ORG360_PLUGIN_DIR . 'admin/class-org360-admin-departments.php';
            require_once ORG360_PLUGIN_DIR . 'admin/class-org360-admin-assessments.php';
            require_once ORG360_PLUGIN_DIR . 'admin/class-org360-admin-competencies.php';
            require_once ORG360_PLUGIN_DIR . 'admin/class-org360-admin-reports.php';
            require_once ORG360_PLUGIN_DIR . 'admin/class-org360-admin-settings.php';
        }
        
        // Public classes
        require_once ORG360_PLUGIN_DIR . 'public/class-org360-public.php';
        require_once ORG360_PLUGIN_DIR . 'public/class-org360-shortcodes.php';
    }
    
    /**
     * Define WordPress hooks
     */
    private function define_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize components
        add_action('plugins_loaded', array($this, 'init'));
        add_action('init', array($this, 'load_textdomain'));
        
        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'public_enqueue_scripts'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        try {
            // Create database tables
            Org360_Database::create_tables();
            
            // Create default pages
            $this->create_default_pages();
            
            // Create default admin user if none exists
            $this->create_default_admin();
            
            // Flush rewrite rules
            flush_rewrite_rules();
            
            // Set activation flag
            update_option('org360_activated', true);
            update_option('org360_version', ORG360_VERSION);
            
        } catch (Exception $e) {
            // Log the error
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Org360 Plugin Activation Error: ' . $e->getMessage());
            }
            
            // Deactivate the plugin if there was an error
            deactivate_plugins(plugin_basename(__FILE__));
            
            // Show error message
            wp_die(
                __('Plugin activation failed. Please check error logs for details.', 'org360-assessments'),
                __('Activation Error', 'org360-assessments'),
                array('back_link' => true)
            );
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Initialize plugin components
     */
    public function init() {
        // Don't initialize during plugin activation to avoid conflicts
        if (defined('WP_INSTALLING') && WP_INSTALLING) {
            return;
        }
        
        // Initialize admin
        if (is_admin()) {
            new Org360_Admin();
        }
        
        // Initialize public
        new Org360_Public();
        new Org360_Shortcodes();
        
        // Start session only for actual user interactions, not during activation
        if (!is_admin() && !session_id() && !headers_sent()) {
            session_start();
        }
    }
    
    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('org360-assessments', false, dirname(ORG360_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'org360') === false) {
            return;
        }
        
        wp_enqueue_style('org360-admin-css', ORG360_PLUGIN_URL . 'assets/css/admin.css', array(), ORG360_VERSION);
        
        // Enqueue jQuery UI for sortable if needed
        wp_enqueue_script('jquery-ui-sortable');
        
        wp_enqueue_script('org360-admin-js', ORG360_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-sortable'), ORG360_VERSION, true);
        
        // Localize script
        wp_localize_script('org360-admin-js', 'org360_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('org360_admin_nonce'),
            'strings' => array(
                'no_users_selected' => __('Please select at least one user.', 'org360-assessments'),
                'assigning' => __('Assigning...', 'org360-assessments'),
                'assign' => __('Assign to Selected Users', 'org360-assessments'),
                'saving' => __('Saving...', 'org360-assessments'),
                'save' => __('Save', 'org360-assessments'),
                'error' => __('An error occurred. Please try again.', 'org360-assessments'),
                'confirm_delete' => __('Are you sure you want to delete this item?', 'org360-assessments'),
                'required_fields' => __('Please fill in all required fields.', 'org360-assessments'),
                'draft_saved' => __('Draft saved', 'org360-assessments')
            )
        ));
    }
    
    /**
     * Enqueue public scripts and styles
     */
    public function public_enqueue_scripts() {
        wp_enqueue_style('org360-public-css', ORG360_PLUGIN_URL . 'assets/css/public.css', array(), ORG360_VERSION);
        wp_enqueue_script('org360-public-js', ORG360_PLUGIN_URL . 'assets/js/public.js', array('jquery'), ORG360_VERSION, true);
        
        // Localize script
        wp_localize_script('org360-public-js', 'org360_public', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('org360_public_nonce')
        ));
    }
    
    /**
     * Create default pages on activation
     */
    private function create_default_pages() {
        $pages = array(
            'org360_login_page' => array(
                'title' => 'Login',
                'content' => '[org360_login]',
                'slug' => 'org360-login'
            ),
            'org360_register_page' => array(
                'title' => 'Register',
                'content' => '[org360_register]',
                'slug' => 'org360-register'
            ),
            'org360_dashboard_page' => array(
                'title' => 'My Dashboard',
                'content' => '[org360_dashboard]',
                'slug' => 'org360-dashboard'
            ),
            'org360_assessments_page' => array(
                'title' => 'View Assessments',
                'content' => '[org360_view_assessments]',
                'slug' => 'org360-assessments'
            ),
            'org360_complete_page' => array(
                'title' => 'Complete Assessment',
                'content' => '[org360_complete_assessment]',
                'slug' => 'org360-complete-assessment'
            ),
            'org360_results_page' => array(
                'title' => 'View Results',
                'content' => '[org360_view_results]',
                'slug' => 'org360-results'
            ),
            'org360_forgot_password_page' => array(
                'title' => 'Forgot Password',
                'content' => '[org360_forgot_password]',
                'slug' => 'org360-forgot-password'
            ),
            'org360_reset_password_page' => array(
                'title' => 'Reset Password',
                'content' => '[org360_reset_password]',
                'slug' => 'org360-reset-password'
            )
        );
        
        foreach ($pages as $option_name => $page_data) {
            // Check if page already exists
            $page_id = get_option($option_name);
            
            if (!$page_id || !get_post($page_id)) {
                // Create the page
                $page_id = wp_insert_post(array(
                    'post_title' => $page_data['title'],
                    'post_content' => $page_data['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_name' => $page_data['slug'],
                    'comment_status' => 'closed',
                    'ping_status' => 'closed'
                ));
                
                // Save page ID
                update_option($option_name, $page_id);
            }
        }
    }
    
    /**
     * Create default admin user
     */
    private function create_default_admin() {
        global $wpdb;
        $table = $wpdb->prefix . 'org360_users';
        
        // Check if any admin exists
        $admin_exists = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE role = 'admin'");
        
        if (!$admin_exists) {
            $user = new Org360_User();
            $user->create(array(
                'full_name' => 'System Administrator',
                'email' => 'admin@org360.local',
                'password' => wp_hash_password('Admin@123'),
                'role' => 'admin',
                'status' => 'active'
            ));
        }
    }
}

/**
 * Initialize the plugin
 */
function org360_assessments() {
    return Org360_Assessments::get_instance();
}

// Start the plugin
org360_assessments();