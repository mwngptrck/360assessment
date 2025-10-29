<?php
/**
 * Login Form Template
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

if (isset($_GET['verified'])) {
    echo '<div class="org360-message org360-success">' . __('Email verified successfully! You can now log in.', 'org360-assessments') . '</div>';
}

if (isset($_GET['logged_out'])) {
    echo '<div class="org360-message org360-success">' . __('You have been logged out successfully.', 'org360-assessments') . '</div>';
}
?>

<div class="org360-form-container">
    <h2><?php _e('Login', 'org360-assessments'); ?></h2>
    
    <form method="post" class="org360-form">
        <?php wp_nonce_field('org360_login', 'org360_login_nonce'); ?>
        
        <div class="org360-form-group">
            <label for="email"><?php _e('Email', 'org360-assessments'); ?></label>
            <input type="email" name="email" id="email" required>
        </div>
        
        <div class="org360-form-group">
            <label for="password"><?php _e('Password', 'org360-assessments'); ?></label>
            <input type="password" name="password" id="password" required>
        </div>
        
        <div class="org360-form-group">
            <button type="submit" name="org360_login_submit" class="org360-button org360-button-primary">
                <?php _e('Login', 'org360-assessments'); ?>
            </button>
        </div>
        
        <div class="org360-form-links">
            <a href="<?php echo get_permalink(get_option('org360_forgot_password_page')); ?>">
                <?php _e('Forgot Password?', 'org360-assessments'); ?>
            </a>
            <span>|</span>
            <a href="<?php echo get_permalink(get_option('org360_register_page')); ?>">
                <?php _e('Register', 'org360-assessments'); ?>
            </a>
        </div>
    </form>
</div>