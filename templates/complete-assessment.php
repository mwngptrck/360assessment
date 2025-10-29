<?php
/**
 * Complete Assessment Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$assignment_id = isset($_GET['assignment_id']) ? intval($_GET['assignment_id']) : 0;

if (!$assignment_id) {
    echo '<p>' . __('Invalid assessment.', 'org360-assessments') . '</p>';
    return;
}

$assignment_obj = new Org360_Assignment();
$assignment = $assignment_obj->get_with_details($assignment_id);

if (!$assignment || $assignment->user_id != Org360_Auth::get_current_user_id()) {
    echo '<p>' . __('You do not have permission to access this assessment.', 'org360-assessments') . '</p>';
    return;
}

if ($assignment->status === 'completed') {
    echo '<p>' . __('This assessment has already been completed.', 'org360-assessments') . ' ';
    echo '<a href="' . add_query_arg('assignment_id', $assignment_id, get_permalink(get_option('org360_results_page'))) . '">' . __('View Results', 'org360-assessments') . '</a></p>';
    return;
}

// Mark as in progress if pending
if ($assignment->status === 'pending') {
    $assignment_obj->mark_in_progress($assignment_id);
}

$response_obj = new Org360_Response();
$existing_responses = $response_obj->get_by_assignment($assignment_id);
$responses_map = array();
foreach ($existing_responses as $resp) {
    $responses_map[$resp->question_id] = $resp;
}

// Get questionnaires for this assessment
$questionnaire_obj = new Org360_Questionnaire();
$questionnaires = $questionnaire_obj->get_by_assessment($assignment->assessment->id);

// If no questionnaires, fall back to direct questions
$has_questionnaires = !empty($questionnaires);
?>

<div class="org360-assessment-form">
    <h1><?php echo esc_html($assignment->assessment->title); ?></h1>
    <p class="org360-assessment-description"><?php echo wp_kses_post($assignment->assessment->description); ?></p>
    
    <form method="post" class="org360-form">
        <?php wp_nonce_field('org360_submit_assessment', 'org360_assessment_nonce'); ?>
        <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">
        
        <?php 
        $question_num = 1;
        
        if ($has_questionnaires) {
            // Display questions organized by questionnaires
            foreach ($questionnaires as $questionnaire) :
                $question_obj = new Org360_Question();
                $questions = $question_obj->get_by_questionnaire($questionnaire->id);
                
                if (!empty($questions)) :
        ?>
                    <div class="org360-questionnaire-section">
                        <h2 class="org360-questionnaire-title"><?php echo esc_html($questionnaire->title); ?></h2>
                        <?php if ($questionnaire->competency_name) : ?>
                            <p class="org360-competency-badge">
                                <span class="dashicons dashicons-awards"></span>
                                <?php echo esc_html($questionnaire->competency_name); ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($questionnaire->description) : ?>
                            <p class="org360-questionnaire-description"><?php echo wp_kses_post($questionnaire->description); ?></p>
                        <?php endif; ?>
                        
                        <?php foreach ($questions as $question) : 
                            $existing_response = isset($responses_map[$question->id]) ? $responses_map[$question->id] : null;
                        ?>
            <div class="org360-question-block">
                <h3>
                    <?php echo $question_num; ?>. <?php echo esc_html($question->question_text); ?>
                    <?php if ($question->required) : ?>
                        <span class="org360-required">*</span>
                    <?php endif; ?>
                </h3>
                
                <?php if ($question->question_type === 'text') : ?>
                    <input type="text" 
                           name="responses[<?php echo $question->id; ?>][text]" 
                           value="<?php echo $existing_response ? esc_attr($existing_response->response_text) : ''; ?>"
                           <?php echo $question->required ? 'required' : ''; ?>>
                
                <?php elseif ($question->question_type === 'textarea') : ?>
                    <textarea name="responses[<?php echo $question->id; ?>][text]" 
                              rows="5" 
                              <?php echo $question->required ? 'required' : ''; ?>><?php echo $existing_response ? esc_textarea($existing_response->response_text) : ''; ?></textarea>
                
                <?php elseif ($question->question_type === 'multiple_choice') : ?>
                    <?php if (!empty($question->options)) : ?>
                        <?php foreach ($question->options as $option) : ?>
                            <label class="org360-radio-label">
                                <input type="radio" 
                                       name="responses[<?php echo $question->id; ?>][value]" 
                                       value="<?php echo esc_attr($option); ?>"
                                       <?php echo ($existing_response && $existing_response->response_value === $option) ? 'checked' : ''; ?>
                                       <?php echo $question->required ? 'required' : ''; ?>>
                                <?php echo esc_html($option); ?>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                
                <?php elseif ($question->question_type === 'rating') : ?>
                    <div class="org360-rating-scale">
                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                            <label class="org360-rating-label">
                                <input type="radio" 
                                       name="responses[<?php echo $question->id; ?>][value]" 
                                       value="<?php echo $i; ?>"
                                       <?php echo ($existing_response && $existing_response->response_value == $i) ? 'checked' : ''; ?>
                                       <?php echo $question->required ? 'required' : ''; ?>>
                                <span class="org360-rating-number"><?php echo $i; ?></span>
                            </label>
                        <?php endfor; ?>
                    </div>
                    <div class="org360-rating-labels">
                        <span><?php _e('Poor', 'org360-assessments'); ?></span>
                        <span><?php _e('Excellent', 'org360-assessments'); ?></span>
                    </div>
                
                <?php elseif ($question->question_type === 'yes_no') : ?>
                    <label class="org360-radio-label">
                        <input type="radio" 
                               name="responses[<?php echo $question->id; ?>][value]" 
                               value="Yes"
                               <?php echo ($existing_response && $existing_response->response_value === 'Yes') ? 'checked' : ''; ?>
                               <?php echo $question->required ? 'required' : ''; ?>>
                        <?php _e('Yes', 'org360-assessments'); ?>
                    </label>
                    <label class="org360-radio-label">
                        <input type="radio" 
                               name="responses[<?php echo $question->id; ?>][value]" 
                               value="No"
                               <?php echo ($existing_response && $existing_response->response_value === 'No') ? 'checked' : ''; ?>
                               <?php echo $question->required ? 'required' : ''; ?>>
                        <?php _e('No', 'org360-assessments'); ?>
                    </label>
                
                <?php endif; ?>
            </div>
        <?php 
                            $question_num++;
                        endforeach; 
                        ?>
                    </div>
                <?php 
                endif;
            endforeach;
        } else {
            // Display direct questions (backward compatibility)
            foreach ($assignment->assessment->questions as $question) : 
                $existing_response = isset($responses_map[$question->id]) ? $responses_map[$question->id] : null;
            ?>
                <div class="org360-question-block">
                    <h3>
                        <?php echo $question_num; ?>. <?php echo esc_html($question->question_text); ?>
                        <?php if ($question->required) : ?>
                            <span class="org360-required">*</span>
                        <?php endif; ?>
                    </h3>
                    
                    <?php if ($question->question_type === 'text') : ?>
                        <input type="text" 
                               name="responses[<?php echo $question->id; ?>][text]" 
                               value="<?php echo $existing_response ? esc_attr($existing_response->response_text) : ''; ?>"
                               <?php echo $question->required ? 'required' : ''; ?>>
                    
                    <?php elseif ($question->question_type === 'textarea') : ?>
                        <textarea name="responses[<?php echo $question->id; ?>][text]" 
                                  rows="5" 
                                  <?php echo $question->required ? 'required' : ''; ?>><?php echo $existing_response ? esc_textarea($existing_response->response_text) : ''; ?></textarea>
                    
                    <?php elseif ($question->question_type === 'multiple_choice') : ?>
                        <?php if (!empty($question->options)) : ?>
                            <?php foreach ($question->options as $option) : ?>
                                <label class="org360-radio-label">
                                    <input type="radio" 
                                           name="responses[<?php echo $question->id; ?>][value]" 
                                           value="<?php echo esc_attr($option); ?>"
                                           <?php echo ($existing_response && $existing_response->response_value === $option) ? 'checked' : ''; ?>
                                           <?php echo $question->required ? 'required' : ''; ?>>
                                    <?php echo esc_html($option); ?>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    
                    <?php elseif ($question->question_type === 'rating') : ?>
                        <div class="org360-rating-scale">
                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                <label class="org360-rating-label">
                                    <input type="radio" 
                                           name="responses[<?php echo $question->id; ?>][value]" 
                                           value="<?php echo $i; ?>"
                                           <?php echo ($existing_response && $existing_response->response_value == $i) ? 'checked' : ''; ?>
                                           <?php echo $question->required ? 'required' : ''; ?>>
                                    <span class="org360-rating-number"><?php echo $i; ?></span>
                                </label>
                            <?php endfor; ?>
                        </div>
                        <div class="org360-rating-labels">
                            <span><?php _e('Poor', 'org360-assessments'); ?></span>
                            <span><?php _e('Excellent', 'org360-assessments'); ?></span>
                        </div>
                    
                    <?php elseif ($question->question_type === 'yes_no') : ?>
                        <label class="org360-radio-label">
                            <input type="radio" 
                                   name="responses[<?php echo $question->id; ?>][value]" 
                                   value="Yes"
                                   <?php echo ($existing_response && $existing_response->response_value === 'Yes') ? 'checked' : ''; ?>
                                   <?php echo $question->required ? 'required' : ''; ?>>
                            <?php _e('Yes', 'org360-assessments'); ?>
                        </label>
                        <label class="org360-radio-label">
                            <input type="radio" 
                                   name="responses[<?php echo $question->id; ?>][value]" 
                                   value="No"
                                   <?php echo ($existing_response && $existing_response->response_value === 'No') ? 'checked' : ''; ?>
                                   <?php echo $question->required ? 'required' : ''; ?>>
                            <?php _e('No', 'org360-assessments'); ?>
                        </label>
                    
                    <?php endif; ?>
                </div>
            <?php 
                $question_num++;
            endforeach;
        }
        ?>
        
        <div class="org360-form-actions">
            <button type="submit" name="org360_submit_assessment" class="org360-button org360-button-primary org360-button-large">
                <?php _e('Submit Assessment', 'org360-assessments'); ?>
            </button>
            <a href="<?php echo get_permalink(get_option('org360_dashboard_page')); ?>" class="org360-button org360-button-secondary">
                <?php _e('Save & Continue Later', 'org360-assessments'); ?>
            </a>
        </div>
    </form>
</div>