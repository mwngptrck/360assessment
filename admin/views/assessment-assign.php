<?php
/**
 * Admin Assign Assessment View
 */

if (!defined('ABSPATH')) {
    exit;
}

settings_errors('org360_messages');
?>

<div class="wrap">
    <h1><?php printf(__('Assign Assessment: %s', 'org360-assessments'), esc_html($assessment->title)); ?></h1>
    
    <form method="post" action="<?php echo admin_url('admin.php?page=org360-assessments-manage&action=assign&assessment_id=' . $assessment->id); ?>">
        <?php wp_nonce_field('org360_assign_assessment', 'org360_assign_assessment_nonce'); ?>
        <input type="hidden" name="assessment_id" value="<?php echo $assessment->id; ?>">
        
        <h2><?php _e('Select Users', 'org360-assessments'); ?></h2>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="check-column"><input type="checkbox" id="select-all"></th>
                    <th><?php _e('Name', 'org360-assessments'); ?></th>
                    <th><?php _e('Email', 'org360-assessments'); ?></th>
                    <th><?php _e('Role', 'org360-assessments'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                    <tr>
                        <th class="check-column">
                            <input type="checkbox" name="user_ids[]" value="<?php echo $user->id; ?>">
                        </th>
                        <td><?php echo esc_html($user->full_name); ?></td>
                        <td><?php echo esc_html($user->email); ?></td>
                        <td><?php echo esc_html(ucfirst($user->role)); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p class="submit">
            <input type="submit" name="org360_assign_assessment" class="button button-primary" value="<?php _e('Assign to Selected Users', 'org360-assessments'); ?>">
            <a href="<?php echo admin_url('admin.php?page=org360-assessments-manage'); ?>" class="button"><?php _e('Cancel', 'org360-assessments'); ?></a>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#select-all').on('change', function() {
        $('input[name="user_ids[]"]').prop('checked', $(this).prop('checked'));
    });
});
</script>