<?php
/**
 * Admin Users List View
 */

if (!defined('ABSPATH')) {
    exit;
}

settings_errors('org360_messages');

$department_obj = new Org360_Department();
$assignment_obj = new Org360_Assignment();

// Get users with their assignment statistics
$user_obj = new Org360_User();
$users = $user_obj->get_all(array('limit' => 1000));

// Calculate statistics for each user
foreach ($users as $user) {
    $user_assignments = $assignment_obj->get_by_user($user->id);
    $user->total_assigned = count($user_assignments);
    $user->completed_count = count(array_filter($user_assignments, function($a) { return $a->status === 'completed'; }));
    $user->pending_count = count(array_filter($user_assignments, function($a) { return $a->status === 'pending'; }));
    $user->in_progress_count = count(array_filter($user_assignments, function($a) { return $a->status === 'in_progress'; }));
    
    // Get department name
    $user->department_name = '';
    if ($user->department_id) {
        $dept = $department_obj->get($user->department_id);
        $user->department_name = $dept ? $dept->name : '';
    }
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Users', 'org360-assessments'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=org360-users&action=add'); ?>" class="page-title-action">
        <?php _e('Add New', 'org360-assessments'); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <?php if (!empty($users)) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Name', 'org360-assessments'); ?></th>
                    <th><?php _e('Email', 'org360-assessments'); ?></th>
                    <th><?php _e('Department', 'org360-assessments'); ?></th>
                    <th><?php _e('Role', 'org360-assessments'); ?></th>
                    <th><?php _e('Status', 'org360-assessments'); ?></th>
                    <th><?php _e('Assigned', 'org360-assessments'); ?></th>
                    <th><?php _e('Completed', 'org360-assessments'); ?></th>
                    <th><?php _e('Completion Rate', 'org360-assessments'); ?></th>
                    <th><?php _e('Actions', 'org360-assessments'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : 
                    $completion_rate = $user->total_assigned > 0 ? round(($user->completed_count / $user->total_assigned) * 100) : 0;
                ?>
                    <tr>
                        <td><strong><?php echo esc_html($user->full_name); ?></strong></td>
                        <td><?php echo esc_html($user->email); ?></td>
                        <td><?php echo $user->department_name ? esc_html($user->department_name) : '<em>' . __('None', 'org360-assessments') . '</em>'; ?></td>
                        <td><span class="org360-role-badge org360-role-<?php echo esc_attr($user->role); ?>"><?php echo esc_html(ucfirst($user->role)); ?></span></td>
                        <td>
                            <span class="org360-status-badge org360-status-<?php echo esc_attr($user->status); ?>">
                                <?php echo esc_html(ucfirst($user->status)); ?>
                            </span>
                        </td>
                        <td>
                            <span class="org360-count-badge"><?php echo $user->total_assigned; ?></span>
                        </td>
                        <td>
                            <span class="org360-count-badge org360-completed"><?php echo $user->completed_count; ?></span>
                        </td>
                        <td>
                            <div class="org360-completion-rate">
                                <div class="org360-progress-bar">
                                    <div class="org360-progress-fill" style="width: <?php echo $completion_rate; ?>%;"></div>
                                </div>
                                <span class="org360-progress-text"><?php echo $completion_rate; ?>%</span>
                            </div>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=org360-users&action=view&user_id=' . $user->id); ?>">
                                <?php _e('View', 'org360-assessments'); ?>
                            </a> |
                            <a href="<?php echo admin_url('admin.php?page=org360-users&action=edit&user_id=' . $user->id); ?>">
                                <?php _e('Edit', 'org360-assessments'); ?>
                            </a> |
                            <?php if ($user->id > 1) : // Prevent deleting system admin ?>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=org360-users&action=delete&user_id=' . $user->id), 'org360_delete_user_' . $user->id); ?>" 
                                   onclick="return confirm('<?php _e('Are you sure you want to delete this user?', 'org360-assessments'); ?>');">
                                    <?php _e('Delete', 'org360-assessments'); ?>
                                </a>
                            <?php else : ?>
                                <span style="color: #999;"><?php _e('Delete', 'org360-assessments'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p><?php _e('No users found.', 'org360-assessments'); ?></p>
    <?php endif; ?>
</div>

<style>
.org360-count-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    background: #f0f0f0;
    color: #555;
}

.org360-count-badge.org360-completed {
    background: #d4edda;
    color: #155724;
}

.org360-completion-rate {
    display: flex;
    align-items: center;
    gap: 8px;
}

.org360-progress-bar {
    width: 60px;
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
}

.org360-progress-fill {
    height: 100%;
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    transition: width 0.3s ease;
}

.org360-progress-text {
    font-size: 11px;
    font-weight: 600;
    color: #555;
}

.org360-role-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.org360-role-admin { background: #d4edda; color: #155724; }
.org360-role-manager { background: #cce5ff; color: #004085; }
.org360-role-employee { background: #fff3cd; color: #856404; }
.org360-role-assessor { background: #e2e3e5; color: #383d41; }

.org360-status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.org360-status-active { background: #d4edda; color: #155724; }
.org360-status-pending { background: #fff3cd; color: #856404; }
.org360-status-inactive { background: #f8d7da; color: #721c24; }
</style>