<?php
/**
 * Admin Users Class
 * Handles user management in admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Admin_Users {
    
    public function render() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        switch ($action) {
            case 'add':
                $this->add_user_form();
                break;
            case 'edit':
                $this->edit_user_form();
                break;
            case 'view':
                $this->view_user();
                break;
            case 'delete':
                $this->delete_user();
                break;
            default:
                $this->list_users();
                break;
        }
    }
    
    /**
     * List users
     */
    private function list_users() {
        // Handle form submissions
        if (isset($_POST['org360_add_user'])) {
            $this->handle_add_user();
        }
        
        if (isset($_POST['org360_update_user'])) {
            $this->handle_update_user();
        }
        
        $user_obj = new Org360_User();
        $users = $user_obj->get_all(array('limit' => 1000));
        
        include ORG360_PLUGIN_DIR . 'admin/views/users-list.php';
    }
    
    /**
     * Add user form
     */
    private function add_user_form() {
        include ORG360_PLUGIN_DIR . 'admin/views/user-add.php';
    }
    
    /**
     * Edit user form
     */
    private function edit_user_form() {
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $user_obj = new Org360_User();
        $user = $user_obj->get($user_id);
        
        if (!$user) {
            wp_die(__('User not found.', 'org360-assessments'));
        }
        
        include ORG360_PLUGIN_DIR . 'admin/views/user-edit.php';
    }
    
    /**
     * Handle add user
     */
    private function handle_add_user() {
        check_admin_referer('org360_add_user', 'org360_add_user_nonce');
        
        $full_name = sanitize_text_field($_POST['full_name']);
        $email = sanitize_email($_POST['email']);
        $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : null;
        $password = $_POST['password'];
        $role = sanitize_text_field($_POST['role']);
        $status = sanitize_text_field($_POST['status']);
        
        // Validate required fields
        if (empty($full_name) || empty($email) || empty($password)) {
            add_settings_error('org360_messages', 'org360_message', __('Please fill in all required fields.', 'org360-assessments'), 'error');
            return;
        }
        
        // Auto-generate password if not provided
        if (empty($password)) {
            $password = wp_generate_password(12, false);
        }
        
        $user_obj = new Org360_User();
        $result = $user_obj->create(array(
            'full_name' => $full_name,
            'email' => $email,
            'department_id' => $department_id,
            'password' => wp_hash_password($password),
            'role' => $role,
            'status' => $status
        ));
        
        if (is_wp_error($result)) {
            add_settings_error('org360_messages', 'org360_message', $result->get_error_message(), 'error');
        } else {
            // Send welcome email with password
            $this->send_welcome_email($email, $full_name, $password);
            
            add_settings_error('org360_messages', 'org360_message', __('User created successfully. Welcome email sent with login details.', 'org360-assessments'), 'success');
        }
    }
    
    /**
     * Send welcome email with password
     */
    private function send_welcome_email($email, $full_name, $password) {
        $login_url = get_permalink(get_option('org360_login_page'));
        $organization_name = Org360_Database::get_setting('organization_name', 'Org360 Assessments');
        
        $subject = sprintf(__('Welcome to %s', 'org360-assessments'), $organization_name);
        
        $message = '
        <h2>' . sprintf(__('Welcome to %s!', 'org360-assessments'), esc_html($organization_name)) . '</h2>
        <p>' . sprintf(__('Hello %s,', 'org360-assessments'), esc_html($full_name)) . '</p>
        <p>' . __('Your account has been created successfully. Here are your login details:', 'org360-assessments') . '</p>
        
        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>' . __('Email:', 'org360-assessments') . '</strong> ' . esc_html($email) . '</p>
            <p><strong>' . __('Password:', 'org360-assessments') . '</strong> ' . esc_html($password) . '</p>
        </div>
        
        <p><a href="' . esc_url($login_url) . '" class="button">' . __('Login Now', 'org360-assessments') . '</a></p>
        <p>' . __('Please change your password after your first login for security.', 'org360-assessments') . '</p>
        <p>' . __('If you did not request this account, please contact the administrator.', 'org360-assessments') . '</p>';
        
        $email_obj = new Org360_Email();
        return $email_obj->send($email, $subject, $message);
    }
    
    /**
     * Handle update user
     */
    private function handle_update_user() {
        check_admin_referer('org360_update_user', 'org360_update_user_nonce');
        
        $user_id = intval($_POST['user_id']);
        $full_name = sanitize_text_field($_POST['full_name']);
        $email = sanitize_email($_POST['email']);
        $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : null;
        $role = sanitize_text_field($_POST['role']);
        $status = sanitize_text_field($_POST['status']);
        
        $update_data = array(
            'full_name' => $full_name,
            'email' => $email,
            'department_id' => $department_id,
            'role' => $role,
            'status' => $status
        );
        
        if (!empty($_POST['password'])) {
            $update_data['password'] = wp_hash_password($_POST['password']);
            
            // Send password change notification
            $this->send_password_change_notification($user_id, $email, $_POST['password']);
        }
        
        $user_obj = new Org360_User();
        $result = $user_obj->update($user_id, $update_data);
        
        if (is_wp_error($result)) {
            add_settings_error('org360_messages', 'org360_message', $result->get_error_message(), 'error');
        } else {
            add_settings_error('org360_messages', 'org360_message', __('User updated successfully.', 'org360-assessments'), 'success');
        }
    }
    
    /**
     * Send password change notification
     */
    private function send_password_change_notification($user_id, $email, $new_password) {
        $user_obj = new Org360_User();
        $user = $user_obj->get($user_id);
        
        if (!$user) {
            return false;
        }
        
        $organization_name = Org360_Database::get_setting('organization_name', 'Org360 Assessments');
        $login_url = get_permalink(get_option('org360_login_page'));
        
        $subject = sprintf(__('Password Changed - %s', 'org360-assessments'), $organization_name);
        
        $message = '
        <h2>' . __('Password Changed', 'org360-assessments') . '</h2>
        <p>' . sprintf(__('Hello %s,', 'org360-assessments'), esc_html($user->full_name)) . '</p>
        <p>' . __('Your password has been changed by an administrator. Here are your new login details:', 'org360-assessments') . '</p>
        
        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>' . __('Email:', 'org360-assessments') . '</strong> ' . esc_html($email) . '</p>
            <p><strong>' . __('New Password:', 'org360-assessments') . '</strong> ' . esc_html($new_password) . '</p>
        </div>
        
        <p><a href="' . esc_url($login_url) . '" class="button">' . __('Login Now', 'org360-assessments') . '</a></p>
        <p>' . __('Please change your password after your first login for security.', 'org360-assessments') . '</p>
        <p>' . __('If you did not request this password change, please contact the administrator immediately.', 'org360-assessments') . '</p>';
        
        $email_obj = new Org360_Email();
        return $email_obj->send($email, $subject, $message);
    }
    
    /**
     * View user details
     */
    private function view_user() {
        include ORG360_PLUGIN_DIR . 'admin/views/user-view.php';
    }
    
    /**
     * Delete user
     */
    private function delete_user() {
        check_admin_referer('org360_delete_user_' . $_GET['user_id']);
        
        $user_id = intval($_GET['user_id']);
        
        // Check if this is the system admin user
        if ($user_id == 1) {
            add_settings_error('org360_messages', 'org360_message', __('Cannot delete the system administrator.', 'org360-assessments'), 'error');
            wp_redirect(admin_url('admin.php?page=org360-users'));
            exit;
        }
        
        $user_obj = new Org360_User();
        $user_obj->delete($user_id);
        
        wp_redirect(admin_url('admin.php?page=org360-users&deleted=1'));
        exit;
    }
}