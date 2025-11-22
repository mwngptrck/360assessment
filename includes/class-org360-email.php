<?php
/**
 * Email Management Class
 * Handles all email notifications
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Email {
    
    private $from_email;
    private $from_name;
    
    public function __construct() {
        $this->from_email = Org360_Database::get_setting('from_email', get_option('admin_email'));
        $this->from_name = Org360_Database::get_setting('from_name', get_option('blogname'));
    }
    
    /**
     * Send email
     */
    private function send($to, $subject, $message) {
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->from_name . ' <' . $this->from_email . '>'
        );
        
        $message = $this->wrap_template($message);
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Wrap message in email template
     */
    private function wrap_template($content) {
        $organization_name = Org360_Database::get_setting('organization_name', 'Org360 Assessments');
        
        $template = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4CAF50; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 30px; }
                .button { display: inline-block; padding: 12px 30px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>' . esc_html($organization_name) . '</h1>
                </div>
                <div class="content">
                    ' . $content . '
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' ' . esc_html($organization_name) . '. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $template;
    }
    
    /**
     * Send verification email
     */
    public function send_verification_email($to, $verification_url) {
        $subject = __('Verify Your Email Address', 'org360-assessments');
        
        $message = '
        <h2>' . __('Welcome!', 'org360-assessments') . '</h2>
        <p>' . __('Thank you for registering. Please verify your email address by clicking the button below:', 'org360-assessments') . '</p>
        <p><a href="' . esc_url($verification_url) . '" class="button">' . __('Verify Email', 'org360-assessments') . '</a></p>
        <p>' . __('Or copy and paste this link into your browser:', 'org360-assessments') . '</p>
        <p>' . esc_url($verification_url) . '</p>
        <p>' . __('If you did not create an account, please ignore this email.', 'org360-assessments') . '</p>';
        
        return $this->send($to, $subject, $message);
    }
    
    /**
     * Send password reset email
     */
    public function send_reset_email($to, $reset_url) {
        $subject = __('Reset Your Password', 'org360-assessments');
        
        $message = '
        <h2>' . __('Password Reset Request', 'org360-assessments') . '</h2>
        <p>' . __('You have requested to reset your password. Click the button below to proceed:', 'org360-assessments') . '</p>
        <p><a href="' . esc_url($reset_url) . '" class="button">' . __('Reset Password', 'org360-assessments') . '</a></p>
        <p>' . __('Or copy and paste this link into your browser:', 'org360-assessments') . '</p>
        <p>' . esc_url($reset_url) . '</p>
        <p>' . __('This link will expire in 1 hour.', 'org360-assessments') . '</p>
        <p>' . __('If you did not request a password reset, please ignore this email.', 'org360-assessments') . '</p>';
        
        return $this->send($to, $subject, $message);
    }
    
    /**
     * Send assessment assignment notification
     */
    public function send_assignment_notification($to, $user_name, $assessment_title) {
        $subject = __('New Assessment Assigned', 'org360-assessments');
        
        $dashboard_url = get_permalink(get_option('org360_dashboard_page'));
        
        $message = '
        <h2>' . __('New Assessment Assigned', 'org360-assessments') . '</h2>
        <p>' . sprintf(__('Hello %s,', 'org360-assessments'), esc_html($user_name)) . '</p>
        <p>' . sprintf(__('You have been assigned a new assessment: <strong>%s</strong>', 'org360-assessments'), esc_html($assessment_title)) . '</p>
        <p>' . __('Please log in to your dashboard to complete the assessment.', 'org360-assessments') . '</p>
        <p><a href="' . esc_url($dashboard_url) . '" class="button">' . __('Go to Dashboard', 'org360-assessments') . '</a></p>';
        
        return $this->send($to, $subject, $message);
    }
    
    /**
     * Send assessment completion notification
     */
    public function send_completion_notification($to, $user_name, $assessment_title) {
        $subject = __('Assessment Completed', 'org360-assessments');
        
        $results_url = get_permalink(get_option('org360_results_page'));
        
        $message = '
        <h2>' . __('Assessment Completed', 'org360-assessments') . '</h2>
        <p>' . sprintf(__('Hello %s,', 'org360-assessments'), esc_html($user_name)) . '</p>
        <p>' . sprintf(__('You have successfully completed the assessment: <strong>%s</strong>', 'org360-assessments'), esc_html($assessment_title)) . '</p>
        <p>' . __('Thank you for your participation. You can view your results in your dashboard.', 'org360-assessments') . '</p>
        <p><a href="' . esc_url($results_url) . '" class="button">' . __('View Results', 'org360-assessments') . '</a></p>';
        
        return $this->send($to, $subject, $message);
    }
    
    /**
     * Test email configuration
     */
    public function send_test_email($to) {
        $subject = __('Test Email from Org360 Assessments', 'org360-assessments');
        
        $message = '
        <h2>' . __('Test Email', 'org360-assessments') . '</h2>
        <p>' . __('This is a test email to verify your email configuration is working correctly.', 'org360-assessments') . '</p>
        <p>' . __('If you received this email, your email settings are configured properly.', 'org360-assessments') . '</p>';
        
        return $this->send($to, $subject, $message);
    }
}