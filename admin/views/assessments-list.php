<?php
/**
 * Admin Assessments List View
 */

if (!defined('ABSPATH')) {
    exit;
}

settings_errors('org360_messages');
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Assessments', 'org360-assessments'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=org360-assessments-manage&action=add'); ?>" class="page-title-action">
        <?php _e('Add New', 'org360-assessments'); ?>
    </a>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Title', 'org360-assessments'); ?></th>
                <th><?php _e('Status', 'org360-assessments'); ?></th>
                <th><?php _e('Questions', 'org360-assessments'); ?></th>
                <th><?php _e('Assignments', 'org360-assessments'); ?></th>
                <th><?php _e('Created', 'org360-assessments'); ?></th>
                <th><?php _e('Actions', 'org360-assessments'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($assessments)) : ?>
                <?php 
                $question_obj = new Org360_Question();
                $assignment_obj = new Org360_Assignment();
                foreach ($assessments as $assessment) : 
                    $questions = $question_obj->get_by_assessment($assessment->id);
                    $assignments = $assignment_obj->get_by_assessment($assessment->id);
                ?>
                    <tr>
                        <td><strong><?php echo esc_html($assessment->title); ?></strong></td>
                        <td><?php echo esc_html(ucfirst($assessment->status)); ?></td>
                        <td><?php echo count($questions); ?></td>
                        <td><?php echo count($assignments); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($assessment->created_at)); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=org360-assessments-manage&action=edit&assessment_id=' . $assessment->id); ?>">
                                <?php _e('Edit', 'org360-assessments'); ?>
                            </a> |
                            <a href="<?php echo admin_url('admin.php?page=org360-assessments-manage&action=assign&assessment_id=' . $assessment->id); ?>">
                                <?php _e('Assign', 'org360-assessments'); ?>
                            </a> |
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=org360-assessments-manage&action=delete&assessment_id=' . $assessment->id), 'org360_delete_assessment_' . $assessment->id); ?>" 
                               onclick="return confirm('<?php _e('Are you sure?', 'org360-assessments'); ?>');">
                                <?php _e('Delete', 'org360-assessments'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6"><?php _e('No assessments found.', 'org360-assessments'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>