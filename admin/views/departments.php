<?php
/**
 * Admin Departments View
 */

if (!defined('ABSPATH')) {
    exit;
}

settings_errors('org360_messages');

$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

$user_obj = new Org360_User();
$users = $user_obj->get_all(array('limit' => 1000));

if ($action === 'edit' && $department_id) {
    $department_obj = new Org360_Department();
    $department = $department_obj->get($department_id);
    ?>
    
    <div class="wrap">
        <h1><?php _e('Edit Department', 'org360-assessments'); ?></h1>
        
        <form method="post" action="<?php echo admin_url('admin.php?page=org360-departments'); ?>">
            <?php wp_nonce_field('org360_update_department', 'org360_update_department_nonce'); ?>
            <input type="hidden" name="department_id" value="<?php echo $department->id; ?>">
            
            <table class="form-table">
                <tr>
                    <th><label for="name"><?php _e('Department Name', 'org360-assessments'); ?></label></th>
                    <td><input type="text" name="name" id="name" class="regular-text" value="<?php echo esc_attr($department->name); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="description"><?php _e('Description', 'org360-assessments'); ?></label></th>
                    <td><textarea name="description" id="description" rows="5" class="large-text"><?php echo esc_textarea($department->description); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="manager_id"><?php _e('Department Manager', 'org360-assessments'); ?></label></th>
                    <td>
                        <select name="manager_id" id="manager_id">
                            <option value=""><?php _e('No Manager', 'org360-assessments'); ?></option>
                            <?php foreach ($users as $user) : ?>
                                <option value="<?php echo $user->id; ?>" <?php selected($department->manager_id, $user->id); ?>>
                                    <?php echo esc_html($user->full_name); ?> (<?php echo esc_html($user->email); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="org360_update_department" class="button button-primary" value="<?php _e('Update Department', 'org360-assessments'); ?>">
                <a href="<?php echo admin_url('admin.php?page=org360-departments'); ?>" class="button"><?php _e('Cancel', 'org360-assessments'); ?></a>
            </p>
        </form>
    </div>
    
    <?php
} else {
    ?>
    
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php _e('Departments', 'org360-assessments'); ?></h1>
        <a href="<?php echo admin_url('admin.php?page=org360-departments&action=add'); ?>" class="page-title-action">
            <?php _e('Add New', 'org360-assessments'); ?>
        </a>
        
        <hr class="wp-header-end">
        
        <?php if (!empty($departments)) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'org360-assessments'); ?></th>
                        <th><?php _e('Manager', 'org360-assessments'); ?></th>
                        <th><?php _e('Users', 'org360-assessments'); ?></th>
                        <th><?php _e('Active Users', 'org360-assessments'); ?></th>
                        <th><?php _e('Completed Assessments', 'org360-assessments'); ?></th>
                        <th><?php _e('Actions', 'org360-assessments'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $department) : ?>
                        <tr>
                            <td><strong><?php echo esc_html($department->name); ?></strong></td>
                            <td>
                                <?php if ($department->manager_name) : ?>
                                    <?php echo esc_html($department->manager_name); ?>
                                    <small>(<?php echo esc_html($department->manager_email); ?>)</small>
                                <?php else : ?>
                                    <em><?php _e('No Manager', 'org360-assessments'); ?></em>
                                <?php endif; ?>
                            </td>
                            <td><?php echo intval($department->total_users); ?></td>
                            <td><?php echo intval($department->active_users); ?></td>
                            <td><?php echo intval($department->completed_assessments); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=org360-departments&action=edit&department_id=' . $department->id); ?>">
                                    <?php _e('Edit', 'org360-assessments'); ?>
                                </a> |
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=org360-departments&action=delete&department_id=' . $department->id), 'org360_delete_department_' . $department->id); ?>" 
                                   onclick="return confirm('<?php _e('Are you sure?', 'org360-assessments'); ?>');">
                                    <?php _e('Delete', 'org360-assessments'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php _e('No departments found. Add your first department below.', 'org360-assessments'); ?></p>
        <?php endif; ?>
        
        <div class="org360-admin-box" style="margin-top: 20px;">
            <h3><?php _e('Add New Department', 'org360-assessments'); ?></h3>
            
            <form method="post" action="<?php echo admin_url('admin.php?page=org360-departments'); ?>">
                <?php wp_nonce_field('org360_add_department', 'org360_add_department_nonce'); ?>
                
                <p>
                    <label for="name"><strong><?php _e('Department Name', 'org360-assessments'); ?></strong></label>
                    <input type="text" name="name" id="name" class="widefat" required>
                </p>
                
                <p>
                    <label for="description"><strong><?php _e('Description', 'org360-assessments'); ?></strong></label>
                    <textarea name="description" id="description" rows="4" class="widefat"></textarea>
                </p>
                
                <p>
                    <label for="manager_id"><strong><?php _e('Department Manager', 'org360-assessments'); ?></strong></label>
                    <select name="manager_id" id="manager_id" class="widefat">
                        <option value=""><?php _e('No Manager', 'org360-assessments'); ?></option>
                        <?php foreach ($users as $user) : ?>
                            <option value="<?php echo $user->id; ?>">
                                <?php echo esc_html($user->full_name); ?> (<?php echo esc_html($user->email); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>
                
                <p>
                    <input type="submit" name="org360_add_department" class="button button-primary button-large widefat" value="<?php _e('Add Department', 'org360-assessments'); ?>">
                </p>
            </form>
        </div>
    </div>
    
    <?php
}
?>

<style>
.org360-admin-box {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.org360-admin-box h3 {
    margin-top: 0;
    padding-bottom: 12px;
    border-bottom: 2px solid #2271b1;
    color: #1d2327;
}

.org360-admin-box .button-large {
    padding: 12px 24px;
    height: auto;
    line-height: 1.4;
}

.org360-admin-box select {
    height: 40px;
}
</style>