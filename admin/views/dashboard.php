<?php
/**
 * Admin Dashboard View
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Org360 Assessments Dashboard', 'org360-assessments'); ?></h1>
    
    <div class="org360-admin-stats">
        <div class="org360-stat-box">
            <div class="org360-stat-icon dashicons dashicons-groups"></div>
            <div class="org360-stat-content">
                <h3><?php echo $total_users; ?></h3>
                <p><?php _e('Total Users', 'org360-assessments'); ?></p>
            </div>
        </div>
        
        <div class="org360-stat-box">
            <div class="org360-stat-icon dashicons dashicons-clipboard"></div>
            <div class="org360-stat-content">
                <h3><?php echo $total_assessments; ?></h3>
                <p><?php _e('Total Assessments', 'org360-assessments'); ?></p>
            </div>
        </div>
        
        <div class="org360-stat-box">
            <div class="org360-stat-icon dashicons dashicons-admin-users"></div>
            <div class="org360-stat-content">
                <h3><?php echo $total_assignments; ?></h3>
                <p><?php _e('Total Assignments', 'org360-assessments'); ?></p>
            </div>
        </div>
        
        <div class="org360-stat-box">
            <div class="org360-stat-icon dashicons dashicons-yes"></div>
            <div class="org360-stat-content">
                <h3><?php echo $completed_assignments; ?></h3>
                <p><?php _e('Completed', 'org360-assessments'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="org360-admin-quick-actions">
        <h2><?php _e('Quick Actions', 'org360-assessments'); ?></h2>
        <div class="org360-action-buttons">
            <a href="<?php echo admin_url('admin.php?page=org360-users&action=add'); ?>" class="button button-primary">
                <span class="dashicons dashicons-plus"></span> <?php _e('Add New User', 'org360-assessments'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=org360-assessments-manage&action=add'); ?>" class="button button-primary">
                <span class="dashicons dashicons-plus"></span> <?php _e('Create Assessment', 'org360-assessments'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=org360-reports'); ?>" class="button">
                <span class="dashicons dashicons-chart-bar"></span> <?php _e('View Reports', 'org360-assessments'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=org360-settings'); ?>" class="button">
                <span class="dashicons dashicons-admin-settings"></span> <?php _e('Settings', 'org360-assessments'); ?>
            </a>
        </div>
    </div>
    
    <div class="org360-admin-info">
        <h2><?php _e('Getting Started', 'org360-assessments'); ?></h2>
        <div class="org360-info-box">
            <h3><?php _e('Welcome to Org360 Assessments!', 'org360-assessments'); ?></h3>
            <p><?php _e('Follow these steps to get started:', 'org360-assessments'); ?></p>
            <ol>
                <li><?php _e('Create users or allow them to register via the frontend registration page', 'org360-assessments'); ?></li>
                <li><?php _e('Create assessments with custom questions', 'org360-assessments'); ?></li>
                <li><?php _e('Assign assessments to users', 'org360-assessments'); ?></li>
                <li><?php _e('Users complete assessments via their dashboard', 'org360-assessments'); ?></li>
                <li><?php _e('View and export reports from the Reports page', 'org360-assessments'); ?></li>
            </ol>
            
            <h4><?php _e('Frontend Pages', 'org360-assessments'); ?></h4>
            <p><?php _e('The following pages have been automatically created:', 'org360-assessments'); ?></p>
            <ul>
                <li><a href="<?php echo get_permalink(get_option('org360_login_page')); ?>" target="_blank"><?php _e('Login Page', 'org360-assessments'); ?></a></li>
                <li><a href="<?php echo get_permalink(get_option('org360_register_page')); ?>" target="_blank"><?php _e('Registration Page', 'org360-assessments'); ?></a></li>
                <li><a href="<?php echo get_permalink(get_option('org360_dashboard_page')); ?>" target="_blank"><?php _e('User Dashboard', 'org360-assessments'); ?></a></li>
            </ul>
            
            <h4><?php _e('Default Admin Credentials', 'org360-assessments'); ?></h4>
            <p>
                <strong><?php _e('Email:', 'org360-assessments'); ?></strong> admin@org360.local<br>
                <strong><?php _e('Password:', 'org360-assessments'); ?></strong> Admin@123
            </p>
            <p class="description"><?php _e('Please change these credentials immediately for security.', 'org360-assessments'); ?></p>
        </div>
    </div>
</div>

<style>
.org360-admin-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.org360-stat-box {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.org360-stat-icon {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: #2271b1;
}

.org360-stat-content h3 {
    margin: 0;
    font-size: 32px;
    font-weight: 600;
    color: #1d2327;
}

.org360-stat-content p {
    margin: 5px 0 0;
    color: #646970;
}

.org360-admin-quick-actions {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    margin: 20px 0;
}

.org360-action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 15px;
}

.org360-admin-info {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    margin: 20px 0;
}

.org360-info-box {
    margin-top: 15px;
}

.org360-info-box h3 {
    margin-top: 0;
}

.org360-info-box ol, .org360-info-box ul {
    margin-left: 20px;
}
</style>