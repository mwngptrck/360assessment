<?php
/**
 * Admin Add Assessment View
 */

if (!defined('ABSPATH')) {
    exit;
}

settings_errors('org360_messages');

$competency_obj = new Org360_Competency();
$competencies = $competency_obj->get_all();

$user_obj = new Org360_User();
$users = $user_obj->get_all(array('limit' => 1000));
?>

<div class="wrap org360-assessment-builder">
    <h1><?php _e('Create Assessment', 'org360-assessments'); ?></h1>
    
    <form method="post" action="<?php echo admin_url('admin.php?page=org360-assessments-manage'); ?>" id="org360-assessment-form">
        <?php wp_nonce_field('org360_create_assessment', 'org360_create_assessment_nonce'); ?>
        
        <div class="org360-tabs-container">
            <nav class="nav-tab-wrapper">
                <a href="#tab-basic" class="nav-tab nav-tab-active"><?php _e('Basic Info', 'org360-assessments'); ?></a>
                <a href="#tab-questionnaires" class="nav-tab"><?php _e('Questionnaires', 'org360-assessments'); ?></a>
                <a href="#tab-assign" class="nav-tab"><?php _e('Assign Users', 'org360-assessments'); ?></a>
            </nav>
            
            <div class="tab-content">
                <!-- Basic Info Tab -->
                <div id="tab-basic" class="tab-pane active">
                    <table class="form-table">
                        <tr>
                            <th><label for="title"><?php _e('Title', 'org360-assessments'); ?></label></th>
                            <td><input type="text" name="title" id="title" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="description"><?php _e('Description', 'org360-assessments'); ?></label></th>
                            <td><textarea name="description" id="description" rows="5" class="large-text"></textarea></td>
                        </tr>
                    </table>
                </div>
                
                <!-- Questionnaires Tab -->
                <div id="tab-questionnaires" class="tab-pane">
                    <div class="org360-questionnaires-header">
                        <h2><?php _e('Questionnaires', 'org360-assessments'); ?></h2>
                        <button type="button" class="button button-primary" id="add-questionnaire">
                            <span class="dashicons dashicons-plus"></span> <?php _e('Add Questionnaire', 'org360-assessments'); ?>
                        </button>
                    </div>
                    
                    <div id="questionnaires-container">
                        <!-- Questionnaires will be added here dynamically -->
                    </div>
                    
                    <?php if (empty($competencies)) : ?>
                        <div class="notice notice-warning inline">
                            <p>
                                <?php _e('No competencies found. Please', 'org360-assessments'); ?> 
                                <a href="<?php echo admin_url('admin.php?page=org360-competencies'); ?>"><?php _e('create competencies', 'org360-assessments'); ?></a> 
                                <?php _e('first.', 'org360-assessments'); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Assign Users Tab -->
                <div id="tab-assign" class="tab-pane">
                    <h2><?php _e('Assign to Users', 'org360-assessments'); ?></h2>
                    <p class="description"><?php _e('Select users who should complete this assessment. You can also assign users later.', 'org360-assessments'); ?></p>
                    
                    <div class="org360-user-selection">
                        <div class="org360-select-all">
                            <label>
                                <input type="checkbox" id="select-all-users">
                                <strong><?php _e('Select All', 'org360-assessments'); ?></strong>
                            </label>
                        </div>
                        
                        <div class="org360-users-grid">
                            <?php foreach ($users as $user) : ?>
                                <label class="org360-user-checkbox">
                                    <input type="checkbox" name="assign_users[]" value="<?php echo $user->id; ?>">
                                    <span class="user-info">
                                        <strong><?php echo esc_html($user->full_name); ?></strong>
                                        <small><?php echo esc_html($user->email); ?></small>
                                        <span class="user-role"><?php echo esc_html(ucfirst($user->role)); ?></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
<?php echo '
                           <div class="org360-assign-actions">
                               <button type="button" id="assign-users-ajax" class="button button-primary">
                                   <span class="dashicons dashicons-admin-users"></span> <?php _e("Assign Selected Users", "org360-assessments"); ?>
                               </button>
                               <span id="assign-status" class="org360-assign-status"></span>
                           </div>
'; ?>                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" name="org360_create_assessment" class="button button-primary button-large" value="<?php _e('Create Assessment', 'org360-assessments'); ?>">
            <a href="<?php echo admin_url('admin.php?page=org360-assessments-manage'); ?>" class="button button-large"><?php _e('Cancel', 'org360-assessments'); ?></a>
        </p>
    </form>
</div>

<!-- Questionnaire Template -->
<script type="text/template" id="questionnaire-template">
    <div class="questionnaire-item" data-index="{{index}}">
        <div class="questionnaire-header">
            <h3><?php _e('Questionnaire', 'org360-assessments'); ?> {{number}}</h3>
            <button type="button" class="button remove-questionnaire"><?php _e('Remove', 'org360-assessments'); ?></button>
        </div>
        <table class="form-table">
            <tr>
                <th><label><?php _e('Competency', 'org360-assessments'); ?></label></th>
                <td>
                    <select name="questionnaires[{{index}}][competency_id]" required>
                        <option value=""><?php _e('Select Competency', 'org360-assessments'); ?></option>
                        <?php foreach ($competencies as $comp) : ?>
                            <option value="<?php echo $comp->id; ?>"><?php echo esc_html($comp->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label><?php _e('Title', 'org360-assessments'); ?></label></th>
                <td><input type="text" name="questionnaires[{{index}}][title]" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label><?php _e('Description', 'org360-assessments'); ?></label></th>
                <td><textarea name="questionnaires[{{index}}][description]" rows="3" class="large-text"></textarea></td>
            </tr>
        </table>
        
        <div class="questions-section">
            <h4><?php _e('Questions', 'org360-assessments'); ?></h4>
            <button type="button" class="button add-question" data-questionnaire="{{index}}">
                <span class="dashicons dashicons-plus"></span> <?php _e('Add Question', 'org360-assessments'); ?>
            </button>
            <div class="questions-container" data-questionnaire="{{index}}">
                <!-- Questions will be added here -->
            </div>
        </div>
    </div>
</script>

<!-- Question Template -->
<script type="text/template" id="question-template">
    <div class="question-item" data-question="{{qindex}}">
        <h5><?php _e('Question', 'org360-assessments'); ?> {{qnumber}}</h5>
        <table class="form-table">
            <tr>
                <th><label><?php _e('Question Text', 'org360-assessments'); ?></label></th>
                <td><textarea name="questionnaires[{{index}}][questions][{{qindex}}][text]" rows="3" class="large-text" required></textarea></td>
            </tr>
            <tr>
                <th><label><?php _e('Question Type', 'org360-assessments'); ?></label></th>
                <td>
                    <select name="questionnaires[{{index}}][questions][{{qindex}}][type]" class="question-type-select">
                        <?php foreach (Org360_Question::get_question_types() as $type_key => $type_name) : ?>
                            <option value="<?php echo esc_attr($type_key); ?>"><?php echo esc_html($type_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr class="options-row" style="display:none;">
                <th><label><?php _e('Options (one per line)', 'org360-assessments'); ?></label></th>
                <td><textarea name="questionnaires[{{index}}][questions][{{qindex}}][options]" rows="3" class="regular-text"></textarea></td>
            </tr>
            <tr>
                <th><label><?php _e('Required', 'org360-assessments'); ?></label></th>
                <td><input type="checkbox" name="questionnaires[{{index}}][questions][{{qindex}}][required]" value="1" checked></td>
            </tr>
        </table>
        <button type="button" class="button remove-question"><?php _e('Remove Question', 'org360-assessments'); ?></button>
    </div>
</script>

<script>
jQuery(document).ready(function($) {
    var questionnaireCount = 0;
    var questionCounts = {};
    
    // Tab switching
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        $('.tab-pane').removeClass('active');
        $(target).addClass('active');
    });
    
    // Select all users
    $('#select-all-users').on('change', function() {
        $('input[name="assign_users[]"]').prop('checked', $(this).prop('checked'));
    });
    
    // Add questionnaire
    $('#add-questionnaire').on('click', function() {
        var template = $('#questionnaire-template').html();
        template = template.replace(/\{\{index\}\}/g, questionnaireCount);
        template = template.replace(/\{\{number\}\}/g, questionnaireCount + 1);
        
        $('#questionnaires-container').append(template);
        questionCounts[questionnaireCount] = 0;
        questionnaireCount++;
    });
    
    // Remove questionnaire
    $(document).on('click', '.remove-questionnaire', function() {
        $(this).closest('.questionnaire-item').remove();
    });
    
    // Add question
    $(document).on('click', '.add-question', function() {
        var qIndex = $(this).data('questionnaire');
        var qCount = questionCounts[qIndex] || 0;
        
        var template = $('#question-template').html();
        template = template.replace(/\{\{index\}\}/g, qIndex);
        template = template.replace(/\{\{qindex\}\}/g, qCount);
        template = template.replace(/\{\{qnumber\}\}/g, qCount + 1);
        
        $('.questions-container[data-questionnaire="' + qIndex + '"]').append(template);
        questionCounts[qIndex] = qCount + 1;
    });
    
    // Remove question
    $(document).on('click', '.remove-question', function() {
        $(this).closest('.question-item').remove();
    });
    
    // Question type change
    $(document).on('change', '.question-type-select', function() {
        var optionsRow = $(this).closest('table').find('.options-row');
        if ($(this).val() === 'multiple_choice') {
            optionsRow.show();
        } else {
            optionsRow.hide();
        }
    });
});
</script>

<style>
.org360-assessment-builder .org360-tabs-container {
    background: #fff;
    border: 1px solid #ccd0d4;
    margin: 20px 0;
}

.org360-assessment-builder .nav-tab-wrapper {
    border-bottom: 1px solid #ccd0d4;
    margin: 0;
    padding: 0 20px;
}

.org360-assessment-builder .tab-content {
    padding: 20px;
}

.org360-assessment-builder .tab-pane {
    display: none;
}

.org360-assessment-builder .tab-pane.active {
    display: block;
}

.org360-questionnaires-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.questionnaire-item {
    background: #f9f9f9;
    border: 1px solid #ddd;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.questionnaire-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 2px solid #ddd;
}

.questionnaire-header h3 {
    margin: 0;
}

.questions-section {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.questions-section h4 {
    margin-top: 0;
}

.question-item {
    background: #fff;
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 4px;
}

.question-item h5 {
    margin-top: 0;
}

.org360-user-selection {
    background: #fff;
    border: 1px solid #ddd;
    padding: 20px;
    border-radius: 4px;
}

.org360-select-all {
    padding: 15px;
    background: #f0f0f0;
    border-bottom: 1px solid #ddd;
    margin: -20px -20px 20px -20px;
}

.org360-users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
}

.org360-user-checkbox {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.org360-user-checkbox:hover {
    background: #f0f0f0;
    border-color: #2271b1;
}

.org360-user-checkbox input[type="checkbox"] {
    margin: 3px 10px 0 0;
}

.org360-user-checkbox .user-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.org360-user-checkbox .user-info strong {
    color: #1d2327;
}

.org360-user-checkbox .user-info small {
    color: #646970;
}

.org360-user-checkbox .user-role {
    display: inline-block;
    padding: 2px 8px;
    background: #2271b1;
    color: #fff;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
}
</style>
<?php include ORG360_PLUGIN_DIR . 'admin/views/assessment-add-ajax.php'; ?>
