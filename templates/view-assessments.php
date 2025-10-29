<?php
/**
 * View Assessments Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = Org360_Auth::get_current_user_id();
$assignment_obj = new Org360_Assignment();
$assessment_obj = new Org360_Assessment();

$all_assignments = $assignment_obj->get_by_user($user_id);
?>

<div class="org360-assessments-page">
    <h1><?php _e('My Assessments', 'org360-assessments'); ?></h1>
    
    <div class="org360-tabs">
        <button class="org360-tab-button active" data-tab="all"><?php _e('All', 'org360-assessments'); ?></button>
        <button class="org360-tab-button" data-tab="pending"><?php _e('Pending', 'org360-assessments'); ?></button>
        <button class="org360-tab-button" data-tab="in_progress"><?php _e('In Progress', 'org360-assessments'); ?></button>
        <button class="org360-tab-button" data-tab="completed"><?php _e('Completed', 'org360-assessments'); ?></button>
    </div>
    
    <div class="org360-tab-content">
        <?php if (!empty($all_assignments)) : ?>
            <table class="org360-table">
                <thead>
                    <tr>
                        <th><?php _e('Assessment', 'org360-assessments'); ?></th>
                        <th><?php _e('Status', 'org360-assessments'); ?></th>
                        <th><?php _e('Assigned Date', 'org360-assessments'); ?></th>
                        <th><?php _e('Completed Date', 'org360-assessments'); ?></th>
                        <th><?php _e('Actions', 'org360-assessments'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_assignments as $assignment) : 
                        $assessment = $assessment_obj->get($assignment->assessment_id);
                    ?>
                        <tr data-status="<?php echo esc_attr($assignment->status); ?>">
                            <td><?php echo esc_html($assessment->title); ?></td>
                            <td>
                                <span class="org360-status org360-status-<?php echo esc_attr($assignment->status); ?>">
                                    <?php echo esc_html(ucfirst(str_replace('_', ' ', $assignment->status))); ?>
                                </span>
                            </td>
                            <td><?php echo date('F j, Y', strtotime($assignment->assigned_at)); ?></td>
                            <td>
                                <?php echo $assignment->completed_at ? date('F j, Y', strtotime($assignment->completed_at)) : '-'; ?>
                            </td>
                            <td>
                                <?php if ($assignment->status === 'completed') : ?>
                                    <a href="<?php echo add_query_arg('assignment_id', $assignment->id, get_permalink(get_option('org360_results_page'))); ?>" 
                                       class="org360-button org360-button-small">
                                        <?php _e('View Results', 'org360-assessments'); ?>
                                    </a>
                                <?php else : ?>
                                    <a href="<?php echo add_query_arg('assignment_id', $assignment->id, get_permalink(get_option('org360_complete_page'))); ?>" 
                                       class="org360-button org360-button-small org360-button-primary">
                                        <?php echo $assignment->status === 'in_progress' ? __('Continue', 'org360-assessments') : __('Start', 'org360-assessments'); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php _e('No assessments assigned yet.', 'org360-assessments'); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="org360-back-link">
        <a href="<?php echo get_permalink(get_option('org360_dashboard_page')); ?>">
            &larr; <?php _e('Back to Dashboard', 'org360-assessments'); ?>
        </a>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.org360-tab-button').on('click', function() {
        var tab = $(this).data('tab');
        
        $('.org360-tab-button').removeClass('active');
        $(this).addClass('active');
        
        if (tab === 'all') {
            $('.org360-table tbody tr').show();
        } else {
            $('.org360-table tbody tr').hide();
            $('.org360-table tbody tr[data-status="' + tab + '"]').show();
        }
    });
});
</script>