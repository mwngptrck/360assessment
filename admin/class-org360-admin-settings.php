<?php
/**
 * Admin Settings Class
 * Handles plugin settings in admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Admin_Settings {
    
    public function render() {
        // Handle form submission
        if (isset($_POST['org360_save_settings'])) {
            $this->handle_save_settings();
        }
        
        // Get current settings
        $settings = array(
            'organization_name' => Org360_Database::get_setting('organization_name', 'Org360 Assessments'),
            'from_email' => Org360_Database::get_setting('from_email', get_option('admin_email')),
            'from_name' => Org360_Database::get_setting('from_name', get_option('blogname')),
            'enable_email_notifications' => Org360_Database::get_setting('enable_email_notifications', '1'),
            'registration_enabled' => Org360_Database::get_setting('registration_enabled', '1'),
            'require_email_verification' => Org360_Database::get_setting('require_email_verification', '1')
        );
        
        include ORG360_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    /**
     * Handle save settings
     */
    private function handle_save_settings() {
        check_admin_referer('org360_save_settings', 'org360_save_settings_nonce');
        
        $settings = array(
            'organization_name' => sanitize_text_field($_POST['organization_name']),
            'from_email' => sanitize_email($_POST['from_email']),
            'from_name' => sanitize_text_field($_POST['from_name']),
            'enable_email_notifications' => isset($_POST['enable_email_notifications']) ? '1' : '0',
            'registration_enabled' => isset($_POST['registration_enabled']) ? '1' : '0',
            'require_email_verification' => isset($_POST['require_email_verification']) ? '1' : '0'
        );
        
        foreach ($settings as $key => $value) {
            Org360_Database::update_setting($key, $value);
        }
        
        add_settings_error('org360_messages', 'org360_message', __('Settings saved successfully.', 'org360-assessments'), 'success');
    }
}