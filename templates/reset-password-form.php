<?php
/**
 * Reset Password Form Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';

if (empty($token)) {
    echo '<p>' . __('Invalid reset link.', 'org360-assessments') . '</p>';
    return;
}

// Display messages
if (isset($_SESSION['org360_error'])) {
    echo '<div class="org360-message org360-error">' . esc_html($_SESSION['org360_error']) . '</div>';
    unset($_SESSION['org360_error']);
}
?>

<div class="org360-form-container">
    <h2><?php _e('Reset Password', 'org360-assessments'); ?></h2>
    
    <form method="post" class="org360-form">
        <?php wp_nonce_field('org360_reset_password', 'org360_reset_password_nonce'); ?>
        <input type="hidden" name="token" value="<?php echo esc_attr($token); ?>">
        
        <div class="org360-form-group">
            <label for="password"><?php _e('New Password', 'org360-assessments'); ?></label>
            <input type="password" name="password" id="password" required>
            <small><?php _e('Must be at least 8 characters with uppercase, lowercase, and number.', 'org360-assessments'); ?></small>
        </div>
        
        <div class="org360-form-group">
            <label for="confirm_password"><?php _e('Confirm Password', 'org360-assessments'); ?></label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>
        
        <div class="org360-form-group">
            <button type="submit" name="org360_reset_password_submit" class="org360-button org360-button-primary">
                <?php _e('Reset Password', 'org360-assessments'); ?>
            </button>
        </div>
    </form>
</div>