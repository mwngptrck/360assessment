<?php
/**
 * Admin Reports View
 */

if (!defined('ABSPATH')) {
    exit;
}

settings_errors('org360_messages');
?>

<div class="wrap">
    <h1><?php _e('Assessment Reports', 'org360-assessments'); ?></h1>
    
    <form method="post" action="<?php echo admin_url('admin.php?page=org360-reports'); ?>">
        <?php wp_nonce_field('org360_export_batch', 'org360_export_batch_nonce'); ?>
        
        <p>
            <button type="submit" name="org360_export_batch" class="button button-primary">
                <?php _e('Export Selected as ZIP', 'org360-assessments'); ?>
            </button>
        </p>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="check-column"><input type="checkbox" id="select-all"></th>
                    <th><?php _e('User', 'org360-assessments'); ?></th>
                    <th><?php _e('Assessment', 'org360-assessments'); ?></th>
                    <th><?php _e('Completed Date', 'org360-assessments'); ?></th>
                    <th><?php _e('Actions', 'org360-assessments'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($completed_assignments)) : ?>
                    <?php foreach ($completed_assignments as $assignment) : ?>
                        <tr>
                            <th class="check-column">
                                <input type="checkbox" name="assignment_ids[]" value="<?php echo $assignment->id; ?>">
                            </th>
                            <td>
                                <strong><?php echo esc_html($assignment->full_name); ?></strong><br>
                                <small><?php echo esc_html($assignment->email); ?></small>
                            </td>
                            <td><?php echo esc_html($assignment->assessment_title); ?></td>
                            <td><?php echo date('F j, Y', strtotime($assignment->completed_at)); ?></td>
                            <td>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=org360-reports&export_pdf=1&assignment_id=' . $assignment->id), 'org360_export_pdf_' . $assignment->id); ?>" 
                                   class="button button-small">
                                    <?php _e('Download PDF', 'org360-assessments'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5"><?php _e('No completed assessments found.', 'org360-assessments'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#select-all').on('change', function() {
        $('input[name="assignment_ids[]"]').prop('checked', $(this).prop('checked'));
    });
});
</script>