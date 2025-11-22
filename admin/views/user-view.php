<?php
/**
 * Admin User View Page
 * Shows detailed user information and statistics
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get user details
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$user_obj = new Org360_User();
$user = $user_obj->get($user_id);

if (!$user) {
    wp_die(__('User not found.', 'org360-assessments'));
}

// Get department information
$department_name = '';
$department_obj = new Org360_Department();
if ($user->department_id) {
    $department = $department_obj->get($user->department_id);
    $department_name = $department ? $department->name : '';
}

// Get user statistics
$assignment_obj = new Org360_Assignment();
$assessment_obj = new Org360_Assessment();

// Get all assignments for this user
$all_assignments = $assignment_obj->get_by_user($user_id);
$pending_assignments = array_filter($all_assignments, function($a) { return $a->status === 'pending'; });
$in_progress_assignments = array_filter($all_assignments, function($a) { return $a->status === 'in_progress'; });
$completed_assignments = array_filter($all_assignments, function($a) { return $a->status === 'completed'; });

// Get assessment details for completed assignments
$completed_details = array();
foreach ($completed_assignments as $assignment) {
    $assessment = $assessment_obj->get($assignment->assessment_id);
    if ($assessment) {
        $completed_details[] = array(
            'assignment' => $assignment,
            'assessment' => $assessment
        );
    }
}
?>

<div class="wrap">
    <h1><?php printf(__('User Details: %s', 'org360-assessments'), esc_html($user->full_name)); ?></h1>
    
    <div class="org360-user-profile">
        <!-- Basic Information -->
        <div class="org360-profile-section">
            <h2><?php _e('Basic Information', 'org360-assessments'); ?></h2>
            <table class="form-table">
                <tr>
                    <th><?php _e('Full Name', 'org360-assessments'); ?></th>
                    <td><?php echo esc_html($user->full_name); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Email', 'org360-assessments'); ?></th>
                    <td><?php echo esc_html($user->email); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Role', 'org360-assessments'); ?></th>
                    <td><span class="org360-role-badge org360-role-<?php echo esc_attr($user->role); ?>"><?php echo esc_html(ucfirst($user->role)); ?></span></td>
                </tr>
                <tr>
                    <th><?php _e('Status', 'org360-assessments'); ?></th>
                    <td><span class="org360-status-badge org360-status-<?php echo esc_attr($user->status); ?>"><?php echo esc_html(ucfirst($user->status)); ?></span></td>
                </tr>
                <tr>
                    <th><?php _e('Department', 'org360-assessments'); ?></th>
                    <td><?php echo $department_name ? esc_html($department_name) : '<em>' . __('No Department', 'org360-assessments') . '</em>'; ?></td>
                </tr>
                <tr>
                    <th><?php _e('Member Since', 'org360-assessments'); ?></th>
                    <td><?php echo date('F j, Y', strtotime($user->created_at)); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Last Updated', 'org360-assessments'); ?></th>
                    <td><?php echo date('F j, Y g:i A', strtotime($user->updated_at)); ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Assessment Statistics -->
        <div class="org360-profile-section">
            <h2><?php _e('Assessment Statistics', 'org360-assessments'); ?></h2>
            <div class="org360-stats-grid">
                <div class="org360-stat-box">
                    <div class="org360-stat-icon dashicons dashicons-clipboard"></div>
                    <div class="org360-stat-content">
                        <h3><?php echo count($all_assignments); ?></h3>
                        <p><?php _e('Total Assigned', 'org360-assessments'); ?></p>
                    </div>
                </div>
                <div class="org360-stat-box">
                    <div class="org360-stat-icon dashicons dashicons-clock"></div>
                    <div class="org360-stat-content">
                        <h3><?php echo count($pending_assignments); ?></h3>
                        <p><?php _e('Pending', 'org360-assessments'); ?></p>
                    </div>
                </div>
                <div class="org360-stat-box">
                    <div class="org360-stat-icon dashicons dashicons-update"></div>
                    <div class="org360-stat-content">
                        <h3><?php echo count($in_progress_assignments); ?></h3>
                        <p><?php _e('In Progress', 'org360-assessments'); ?></p>
                    </div>
                </div>
                <div class="org360-stat-box">
                    <div class="org360-stat-icon dashicons dashicons-yes"></div>
                    <div class="org360-stat-content">
                        <h3><?php echo count($completed_assignments); ?></h3>
                        <p><?php _e('Completed', 'org360-assessments'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="org360-profile-section">
            <h2><?php _e('Recent Activity', 'org360-assessments'); ?></h2>
            <?php if (!empty($completed_details)) : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Assessment', 'org360-assessments'); ?></th>
                            <th><?php _e('Completed Date', 'org360-assessments'); ?></th>
                            <th><?php _e('Actions', 'org360-assessments'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($completed_details, 0, 10) as $detail) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($detail['assessment']->title); ?></strong></td>
                                <td><?php echo date('F j, Y', strtotime($detail['assignment']->completed_at)); ?></td>
                                <td>
                                    <a href="<?php echo add_query_arg('assignment_id', $detail['assignment']->id, get_permalink(get_option('org360_results_page'))); ?>" target="_blank">
                                        <?php _e('View Results', 'org360-assessments'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No completed assessments yet.', 'org360-assessments'); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Current Assignments -->
        <div class="org360-profile-section">
            <h2><?php _e('Current Assignments', 'org360-assessments'); ?></h2>
            <?php if (!empty($pending_assignments) || !empty($in_progress_assignments)) : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Assessment', 'org360-assessments'); ?></th>
                            <th><?php _e('Status', 'org360-assessments'); ?></th>
                            <th><?php _e('Assigned Date', 'org360-assessments'); ?></th>
                            <th><?php _e('Actions', 'org360-assessments'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $current_assignments = array_merge($pending_assignments, $in_progress_assignments);
                        foreach ($current_assignments as $assignment) : 
                            $assessment = $assessment_obj->get($assignment->assessment_id);
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($assessment->title); ?></strong></td>
                                <td>
                                    <span class="org360-status-badge org360-status-<?php echo esc_attr($assignment->status); ?>">
                                        <?php echo esc_html(ucfirst(str_replace('_', ' ', $assignment->status))); ?>
                                    </span>
                                </td>
                                <td><?php echo date('F j, Y', strtotime($assignment->assigned_at)); ?></td>
                                <td>
                                    <?php if ($assignment->status === 'pending') : ?>
                                        <a href="<?php echo add_query_arg('assignment_id', $assignment->id, get_permalink(get_option('org360_complete_page'))); ?>" target="_blank">
                                            <?php _e('Start Assessment', 'org360-assessments'); ?>
                                        </a>
                                    <?php else : ?>
                                        <a href="<?php echo add_query_arg('assignment_id', $assignment->id, get_permalink(get_option('org360_complete_page'))); ?>" target="_blank">
                                            <?php _e('Continue', 'org360-assessments'); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php _e('No pending or in-progress assessments.', 'org360-assessments'); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <p class="submit">
        <a href="<?php echo admin_url('admin.php?page=org360-users'); ?>" class="button button-primary">
            <span class="dashicons dashicons-arrow-left-alt"></span> <?php _e('Back to Users', 'org360-assessments'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=org360-users&action=edit&user_id=' . $user->id); ?>" class="button">
            <span class="dashicons dashicons-edit"></span> <?php _e('Edit User', 'org360-assessments'); ?>
        </a>
        <a href="<?php echo admin_url('admin.php?page=org360-users&action=view&user_id=' . $user->id); ?>" class="button button-primary" onclick="window.print();">
            <span class="dashicons dashicons-printer"></span> <?php _e('Print', 'org360-assessments'); ?>
        </a>
    </p>
</div>

<style>
.org360-user-profile {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.org360-profile-section {
    padding: 25px;
    border-bottom: 1px solid #f0f0f0;
}

.org360-profile-section:last-child {
    border-bottom: none;
}

.org360-profile-section h2 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #1d2327;
    font-size: 18px;
    padding-bottom: 10px;
    border-bottom: 2px solid #2271b1;
}

.org360-profile-section .form-table th {
    width: 200px;
    font-weight: 600;
    color: #555;
}

.org360-profile-section .form-table td {
    color: #1d2327;
}

.org360-role-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
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
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.org360-status-active { background: #d4edda; color: #155724; }
.org360-status-pending { background: #fff3cd; color: #856404; }
.org360-status-inactive { background: #f8d7da; color: #721c24; }

@media print {
    .submit {
        display: none;
    }
    
    .org360-user-profile {
        border: none;
        box-shadow: none;
    }
    
    .org360-profile-section {
        page-break-inside: avoid;
    }
}
</style>