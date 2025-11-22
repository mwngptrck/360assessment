<?php
/**
 * Admin Edit User View
 */

if (!defined('ABSPATH')) {
    exit;
}

settings_errors('org360_messages');

$department_obj = new Org360_Department();
$departments = $department_obj->get_all();
?>

<div class="wrap">
    <h1><?php _e('Edit User', 'org360-assessments'); ?></h1>
    
    <form method="post" action="<?php echo admin_url('admin.php?page=org360-users&action=edit&user_id=' . $user->id); ?>">
        <?php wp_nonce_field('org360_update_user', 'org360_update_user_nonce'); ?>
        <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
        
        <table class="form-table">
            <tr>
                <th><label for="full_name"><?php _e('Full Name', 'org360-assessments'); ?></label></th>
                <td><input type="text" name="full_name" id="full_name" class="regular-text" value="<?php echo esc_attr($user->full_name); ?>" required></td>
            </tr>
            <tr>
                <th><label for="email"><?php _e('Email', 'org360-assessments'); ?></label></th>
                <td><input type="email" name="email" id="email" class="regular-text" value="<?php echo esc_attr($user->email); ?>" required></td>
            </tr>
            <tr>
                <th><label for="department_id"><?php _e('Department', 'org360-assessments'); ?></label></th>
                <td>
                    <select name="department_id" id="department_id">
                        <option value=""><?php _e('No Department', 'org360-assessments'); ?></option>
                        <?php foreach ($departments as $department) : ?>
                            <option value="<?php echo $department->id; ?>" <?php selected($user->department_id, $department->id); ?>>
                                <?php echo esc_html($department->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="password"><?php _e('New Password', 'org360-assessments'); ?></label></th>
                <td>
                    <input type="password" name="password" id="password" class="regular-text">
                    <p class="description"><?php _e('Leave blank to keep current password', 'org360-assessments'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="role"><?php _e('Role', 'org360-assessments'); ?></label></th>
                <td>
                    <select name="role" id="role">
                        <?php foreach (Org360_User::get_roles() as $role_key => $role_name) : ?>
                            <option value="<?php echo esc_attr($role_key); ?>" <?php selected($user->role, $role_key); ?>>
                                <?php echo esc_html($role_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="status"><?php _e('Status', 'org360-assessments'); ?></label></th>
                <td>
                    <select name="status" id="status">
                        <option value="active" <?php selected($user->status, 'active'); ?>><?php _e('Active', 'org360-assessments'); ?></option>
                        <option value="pending" <?php selected($user->status, 'pending'); ?>><?php _e('Pending', 'org360-assessments'); ?></option>
                        <option value="inactive" <?php selected($user->status, 'inactive'); ?>><?php _e('Inactive', 'org360-assessments'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="org360_update_user" class="button button-primary" value="<?php _e('Update User', 'org360-assessments'); ?>">
            <a href="<?php echo admin_url('admin.php?page=org360-users'); ?>" class="button"><?php _e('Cancel', 'org360-assessments'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=org360-users&action=view&user_id=' . $user->id); ?>" class="button"><?php _e('View User Details', 'org360-assessments'); ?></a>
        </p>
    </form>
</div>