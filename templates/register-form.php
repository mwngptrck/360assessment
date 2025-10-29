<?php
/**
 * Registration Form Template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Display messages
if (isset($_SESSION['org360_error'])) {
    echo '<div class="org360-message org360-error">' . esc_html($_SESSION['org360_error']) . '</div>';
    unset($_SESSION['org360_error']);
}

if (isset($_SESSION['org360_success'])) {
    echo '<div class="org360-message org360-success">' . esc_html($_SESSION['org360_success']) . '</div>';
    unset($_SESSION['org360_success']);
}
?>

<div class="org360-form-container">
    <h2><?php _e('Register', 'org360-assessments'); ?></h2>
    
    <form method="post" class="org360-form">
        <?php wp_nonce_field('org360_register', 'org360_register_nonce'); ?>
        
        <div class="org360-form-group">
            <label for="full_name"><?php _e('Full Name', 'org360-assessments'); ?></label>
            <input type="text" name="full_name" id="full_name" required>
        </div>
        
        <div class="org360-form-group">
            <label for="email"><?php _e('Email', 'org360-assessments'); ?></label>
            <input type="email" name="email" id="email" required>
        </div>
        
        <div class="org360-form-group">
            <label for="password"><?php _e('Password', 'org360-assessments'); ?></label>
            <input type="password" name="password" id="password" required>
            <small><?php _e('Must be at least 8 characters with uppercase, lowercase, and number.', 'org360-assessments'); ?></small>
        </div>
        
        <div class="org360-form-group">
            <label for="confirm_password"><?php _e('Confirm Password', 'org360-assessments'); ?></label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>
        
        <div class="org360-form-group">
            <button type="submit" name="org360_register_submit" class="org360-button org360-button-primary">
                <?php _e('Register', 'org360-assessments'); ?>
            </button>
        </div>
        
        <div class="org360-form-links">
            <a href="<?php echo get_permalink(get_option('org360_login_page')); ?>">
                <?php _e('Already have an account? Login', 'org360-assessments'); ?>
            </a>
        </div>
    </form>
</div>