<?php
/**
 * Admin Settings View
 */

if (!defined('ABSPATH')) {
    exit;
}

settings_errors('org360_messages');
?>

<div class="wrap">
    <h1><?php _e('Org360 Assessments Settings', 'org360-assessments'); ?></h1>
    
    <form method="post" action="<?php echo admin_url('admin.php?page=org360-settings'); ?>">
        <?php wp_nonce_field('org360_save_settings', 'org360_save_settings_nonce'); ?>
        
        <h2><?php _e('General Settings', 'org360-assessments'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="organization_name"><?php _e('Organization Name', 'org360-assessments'); ?></label></th>
                <td><input type="text" name="organization_name" id="organization_name" class="regular-text" value="<?php echo esc_attr($settings['organization_name']); ?>"></td>
            </tr>
        </table>
        
        <h2><?php _e('Email Settings', 'org360-assessments'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="from_email"><?php _e('From Email', 'org360-assessments'); ?></label></th>
                <td><input type="email" name="from_email" id="from_email" class="regular-text" value="<?php echo esc_attr($settings['from_email']); ?>"></td>
            </tr>
            <tr>
                <th><label for="from_name"><?php _e('From Name', 'org360-assessments'); ?></label></th>
                <td><input type="text" name="from_name" id="from_name" class="regular-text" value="<?php echo esc_attr($settings['from_name']); ?>"></td>
            </tr>
            <tr>
                <th><label for="enable_email_notifications"><?php _e('Enable Email Notifications', 'org360-assessments'); ?></label></th>
                <td>
                    <input type="checkbox" name="enable_email_notifications" id="enable_email_notifications" value="1" <?php checked($settings['enable_email_notifications'], '1'); ?>>
                    <p class="description"><?php _e('Send email notifications for registrations, assignments, etc.', 'org360-assessments'); ?></p>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('Registration Settings', 'org360-assessments'); ?></h2>
        <table class="form-table">
            <tr>
                <th><label for="registration_enabled"><?php _e('Enable Registration', 'org360-assessments'); ?></label></th>
                <td>
                    <input type="checkbox" name="registration_enabled" id="registration_enabled" value="1" <?php checked($settings['registration_enabled'], '1'); ?>>
                    <p class="description"><?php _e('Allow users to register via the frontend registration page.', 'org360-assessments'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="require_email_verification"><?php _e('Require Email Verification', 'org360-assessments'); ?></label></th>
                <td>
                    <input type="checkbox" name="require_email_verification" id="require_email_verification" value="1" <?php checked($settings['require_email_verification'], '1'); ?>>
                    <p class="description"><?php _e('Users must verify their email before logging in.', 'org360-assessments'); ?></p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="org360_save_settings" class="button button-primary" value="<?php _e('Save Settings', 'org360-assessments'); ?>">
        </p>
    </form>
    
    <hr>
    
    <h2><?php _e('Frontend Pages', 'org360-assessments'); ?></h2>
    <table class="widefat">
        <thead>
            <tr>
                <th><?php _e('Page', 'org360-assessments'); ?></th>
                <th><?php _e('URL', 'org360-assessments'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php _e('Login', 'org360-assessments'); ?></td>
                <td><a href="<?php echo get_permalink(get_option('org360_login_page')); ?>" target="_blank"><?php echo get_permalink(get_option('org360_login_page')); ?></a></td>
            </tr>
            <tr>
                <td><?php _e('Register', 'org360-assessments'); ?></td>
                <td><a href="<?php echo get_permalink(get_option('org360_register_page')); ?>" target="_blank"><?php echo get_permalink(get_option('org360_register_page')); ?></a></td>
            </tr>
            <tr>
                <td><?php _e('Dashboard', 'org360-assessments'); ?></td>
                <td><a href="<?php echo get_permalink(get_option('org360_dashboard_page')); ?>" target="_blank"><?php echo get_permalink(get_option('org360_dashboard_page')); ?></a></td>
            </tr>
            <tr>
                <td><?php _e('View Assessments', 'org360-assessments'); ?></td>
                <td><a href="<?php echo get_permalink(get_option('org360_assessments_page')); ?>" target="_blank"><?php echo get_permalink(get_option('org360_assessments_page')); ?></a></td>
            </tr>
            <tr>
                <td><?php _e('View Results', 'org360-assessments'); ?></td>
                <td><a href="<?php echo get_permalink(get_option('org360_results_page')); ?>" target="_blank"><?php echo get_permalink(get_option('org360_results_page')); ?></a></td>
            </tr>
        </tbody>
    </table>
</div>