<?php
/**
 * Admin Competencies View
 */

if (!defined('ABSPATH')) {
    exit;
}

settings_errors('org360_messages');

$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$competency_id = isset($_GET['competency_id']) ? intval($_GET['competency_id']) : 0;

if ($action === 'edit' && $competency_id) {
    $competency_obj = new Org360_Competency();
    $competency = $competency_obj->get($competency_id);
    ?>
    
    <div class="wrap">
        <h1><?php _e('Edit Competency', 'org360-assessments'); ?></h1>
        
        <form method="post" action="<?php echo admin_url('admin.php?page=org360-competencies'); ?>">
            <?php wp_nonce_field('org360_update_competency', 'org360_update_competency_nonce'); ?>
            <input type="hidden" name="competency_id" value="<?php echo $competency->id; ?>">
            
            <table class="form-table">
                <tr>
                    <th><label for="name"><?php _e('Name', 'org360-assessments'); ?></label></th>
                    <td><input type="text" name="name" id="name" class="regular-text" value="<?php echo esc_attr($competency->name); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="description"><?php _e('Description', 'org360-assessments'); ?></label></th>
                    <td><textarea name="description" id="description" rows="5" class="large-text"><?php echo esc_textarea($competency->description); ?></textarea></td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="org360_update_competency" class="button button-primary" value="<?php _e('Update Competency', 'org360-assessments'); ?>">
                <a href="<?php echo admin_url('admin.php?page=org360-competencies'); ?>" class="button"><?php _e('Cancel', 'org360-assessments'); ?></a>
            </p>
        </form>
    </div>
    
    <?php
} else {
    ?>
    
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php _e('Competencies', 'org360-assessments'); ?></h1>
        
        <hr class="wp-header-end">
        
        <div class="org360-admin-grid">
            <div class="org360-admin-main">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Name', 'org360-assessments'); ?></th>
                            <th><?php _e('Description', 'org360-assessments'); ?></th>
                            <th><?php _e('Actions', 'org360-assessments'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($competencies)) : ?>
                            <?php foreach ($competencies as $competency) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html($competency->name); ?></strong></td>
                                    <td><?php echo esc_html($competency->description); ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=org360-competencies&action=edit&competency_id=' . $competency->id); ?>">
                                            <?php _e('Edit', 'org360-assessments'); ?>
                                        </a> |
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=org360-competencies&action=delete&competency_id=' . $competency->id), 'org360_delete_competency_' . $competency->id); ?>" 
                                           onclick="return confirm('<?php _e('Are you sure?', 'org360-assessments'); ?>');">
                                            <?php _e('Delete', 'org360-assessments'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="3"><?php _e('No competencies found. Add your first competency below.', 'org360-assessments'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="org360-admin-sidebar">
                <div class="org360-admin-box">
                    <h3><?php _e('Add New Competency', 'org360-assessments'); ?></h3>
                    
                    <form method="post" action="<?php echo admin_url('admin.php?page=org360-competencies'); ?>">
                        <?php wp_nonce_field('org360_add_competency', 'org360_add_competency_nonce'); ?>
                        
                        <p>
                            <label for="name"><strong><?php _e('Name', 'org360-assessments'); ?></strong></label>
                            <input type="text" name="name" id="name" class="widefat" required>
                        </p>
                        
                        <p>
                            <label for="description"><strong><?php _e('Description', 'org360-assessments'); ?></strong></label>
                            <textarea name="description" id="description" rows="4" class="widefat"></textarea>
                        </p>
                        
                        <p>
                            <input type="submit" name="org360_add_competency" class="button button-primary button-large widefat" value="<?php _e('Add Competency', 'org360-assessments'); ?>">
                        </p>
                    </form>
                </div>
                
                <div class="org360-admin-box">
                    <h3><?php _e('About Competencies', 'org360-assessments'); ?></h3>
                    <p><?php _e('Competencies are used to organize questions into logical groups within questionnaires. Examples include:', 'org360-assessments'); ?></p>
                    <ul>
                        <li><?php _e('Leadership', 'org360-assessments'); ?></li>
                        <li><?php _e('Communication', 'org360-assessments'); ?></li>
                        <li><?php _e('Technical Skills', 'org360-assessments'); ?></li>
                        <li><?php _e('Teamwork', 'org360-assessments'); ?></li>
                        <li><?php _e('Problem Solving', 'org360-assessments'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <?php
}
?>

<style>
.org360-admin-grid {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 20px;
    margin-top: 20px;
}

.org360-admin-main {
    background: #fff;
}

.org360-admin-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.org360-admin-box {
    background: #fff;
    border: 1px solid #ccd0d4;
    padding: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.org360-admin-box h3 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
}

.org360-admin-box ul {
    margin-left: 20px;
}

@media (max-width: 1200px) {
    .org360-admin-grid {
        grid-template-columns: 1fr;
    }
}
</style>