<?php
/**
 * Dashboard Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_user = Org360_Auth::get_current_user();
$user_id = Org360_Auth::get_current_user_id();

$assignment_obj = new Org360_Assignment();
$pending_assignments = $assignment_obj->get_by_user($user_id, 'pending');
$in_progress_assignments = $assignment_obj->get_by_user($user_id, 'in_progress');
$completed_assignments = $assignment_obj->get_by_user($user_id, 'completed');

// Display messages
if (isset($_SESSION['org360_success'])) {
    echo '<div class="org360-message org360-success">' . esc_html($_SESSION['org360_success']) . '</div>';
    unset($_SESSION['org360_success']);
}
?>

<div class="org360-dashboard">
    <div class="org360-dashboard-header">
        <h1><?php printf(__('Welcome, %s!', 'org360-assessments'), esc_html($current_user->full_name)); ?></h1>
        <a href="<?php echo add_query_arg('action', 'org360_logout', home_url()); ?>" class="org360-button org360-button-secondary">
            <?php _e('Logout', 'org360-assessments'); ?>
        </a>
    </div>
    
    <div class="org360-stats-grid">
        <div class="org360-stat-card">
            <h3><?php echo count($pending_assignments); ?></h3>
            <p><?php _e('Pending Assessments', 'org360-assessments'); ?></p>
        </div>
        
        <div class="org360-stat-card">
            <h3><?php echo count($in_progress_assignments); ?></h3>
            <p><?php _e('In Progress', 'org360-assessments'); ?></p>
        </div>
        
        <div class="org360-stat-card">
            <h3><?php echo count($completed_assignments); ?></h3>
            <p><?php _e('Completed', 'org360-assessments'); ?></p>
        </div>
    </div>
    
    <div class="org360-dashboard-section">
        <h2><?php _e('Pending Assessments', 'org360-assessments'); ?></h2>
        
        <?php if (!empty($pending_assignments)) : ?>
            <div class="org360-assessment-list">
                <?php 
                $assessment_obj = new Org360_Assessment();
                foreach ($pending_assignments as $assignment) : 
                    $assessment = $assessment_obj->get($assignment->assessment_id);
                ?>
                    <div class="org360-assessment-item">
                        <h3><?php echo esc_html($assessment->title); ?></h3>
                        <p><?php echo esc_html($assessment->description); ?></p>
                        <p class="org360-meta">
                            <?php printf(__('Assigned: %s', 'org360-assessments'), date('F j, Y', strtotime($assignment->assigned_at))); ?>
                        </p>
                        <a href="<?php echo add_query_arg('assignment_id', $assignment->id, get_permalink(get_option('org360_complete_page'))); ?>" 
                           class="org360-button org360-button-primary">
                            <?php _e('Start Assessment', 'org360-assessments'); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p><?php _e('No pending assessments.', 'org360-assessments'); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="org360-dashboard-section">
        <h2><?php _e('Recent Activity', 'org360-assessments'); ?></h2>
        
        <?php if (!empty($completed_assignments)) : ?>
            <div class="org360-activity-list">
                <?php 
                foreach (array_slice($completed_assignments, 0, 5) as $assignment) : 
                    $assessment = $assessment_obj->get($assignment->assessment_id);
                ?>
                    <div class="org360-activity-item">
                        <p>
                            <strong><?php echo esc_html($assessment->title); ?></strong> - 
                            <?php _e('Completed', 'org360-assessments'); ?>
                        </p>
                        <p class="org360-meta">
                            <?php echo date('F j, Y', strtotime($assignment->completed_at)); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p><?php _e('No recent activity.', 'org360-assessments'); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="org360-dashboard-actions">
        <a href="<?php echo get_permalink(get_option('org360_assessments_page')); ?>" class="org360-button">
            <?php _e('View All Assessments', 'org360-assessments'); ?>
        </a>
        <a href="<?php echo get_permalink(get_option('org360_results_page')); ?>" class="org360-button">
            <?php _e('View Results', 'org360-assessments'); ?>
        </a>
    </div>
</div>