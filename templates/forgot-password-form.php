<?php
/**
 * Forgot Password Form Template
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
    <h2><?php _e('Forgot Password', 'org360-assessments'); ?></h2>
    <p><?php _e('Enter your email address and we will send you a link to reset your password.', 'org360-assessments'); ?></p>
    
    <form method="post" class="org360-form">
        <?php wp_nonce_field('org360_forgot_password', 'org360_forgot_password_nonce'); ?>
        
        <div class="org360-form-group">
            <label for="email"><?php _e('Email', 'org360-assessments'); ?></label>
            <input type="email" name="email" id="email" required>
        </div>
        
        <div class="org360-form-group">
            <button type="submit" name="org360_forgot_password_submit" class="org360-button org360-button-primary">
                <?php _e('Send Reset Link', 'org360-assessments'); ?>
            </button>
        </div>
        
        <div class="org360-form-links">
            <a href="<?php echo get_permalink(get_option('org360_login_page')); ?>">
                &larr; <?php _e('Back to Login', 'org360-assessments'); ?>
            </a>
        </div>
    </form>
</div>