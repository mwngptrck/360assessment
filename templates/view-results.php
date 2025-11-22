<?php
/**
 * View Results Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = Org360_Auth::get_current_user_id();
$assignment_id = isset($_GET['assignment_id']) ? intval($_GET['assignment_id']) : 0;

if ($assignment_id) {
    // View specific assessment result
    $assignment_obj = new Org360_Assignment();
    $assignment = $assignment_obj->get_with_details($assignment_id);
    
    if (!$assignment || $assignment->user_id != $user_id) {
        echo '<p>' . __('You do not have permission to view this result.', 'org360-assessments') . '</p>';
        return;
    }
    
    if ($assignment->status !== 'completed') {
        echo '<p>' . __('This assessment has not been completed yet.', 'org360-assessments') . '</p>';
        return;
    }
    
    $response_obj = new Org360_Response();
    $responses = $response_obj->get_responses_with_questions($assignment_id);
    $score = $response_obj->calculate_score($assignment_id);
    ?>
    
    <div class="org360-results-detail">
        <div class="org360-results-header">
            <h1><?php echo esc_html($assignment->assessment->title); ?></h1>
            <a href="<?php echo add_query_arg('download_pdf', $assignment_id); ?>" class="org360-button org360-button-primary">
                <?php _e('Download PDF Report', 'org360-assessments'); ?>
            </a>
        </div>
        
        <div class="org360-results-info">
            <p><strong><?php _e('Completed:', 'org360-assessments'); ?></strong> <?php echo date('F j, Y', strtotime($assignment->completed_at)); ?></p>
        </div>
        
        <?php if ($score > 0) : ?>
            <div class="org360-score-display">
                <h2><?php _e('Overall Score', 'org360-assessments'); ?></h2>
                <div class="org360-score-circle">
                    <span class="org360-score-value"><?php echo $score; ?></span>
                    <span class="org360-score-max">/5</span>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="org360-responses-section">
            <h2><?php _e('Your Responses', 'org360-assessments'); ?></h2>
            
            <?php 
            $question_num = 1;
            foreach ($responses as $response) : 
            ?>
                <div class="org360-response-item">
                    <h3><?php echo $question_num; ?>. <?php echo esc_html($response->question_text); ?></h3>
                    <div class="org360-response-content">
                        <?php if ($response->question_type === 'rating') : ?>
                            <p class="org360-rating-result">
                                <?php _e('Rating:', 'org360-assessments'); ?> 
                                <strong><?php echo esc_html($response->response_value); ?>/5</strong>
                            </p>
                        <?php elseif ($response->question_type === 'yes_no' || $response->question_type === 'multiple_choice') : ?>
                            <p><strong><?php echo esc_html($response->response_value); ?></strong></p>
                        <?php else : ?>
                            <p><?php echo nl2br(esc_html($response->response_text)); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
                $question_num++;
            endforeach; 
            ?>
        </div>
        
        <div class="org360-back-link">
            <a href="<?php echo get_permalink(get_option('org360_results_page')); ?>">
                &larr; <?php _e('Back to All Results', 'org360-assessments'); ?>
            </a>
        </div>
    </div>
    
    <?php
} else {
    // List all completed assessments
    $assignment_obj = new Org360_Assignment();
    $completed_assignments = $assignment_obj->get_by_user($user_id, 'completed');
    $assessment_obj = new Org360_Assessment();
    ?>
    
    <div class="org360-results-list">
        <h1><?php _e('My Results', 'org360-assessments'); ?></h1>
        
        <?php if (!empty($completed_assignments)) : ?>
            <div class="org360-results-grid">
                <?php foreach ($completed_assignments as $assignment) : 
                    $assessment = $assessment_obj->get($assignment->assessment_id);
                    $response_obj = new Org360_Response();
                    $score = $response_obj->calculate_score($assignment->id);
                ?>
                    <div class="org360-result-card">
                        <h3><?php echo esc_html($assessment->title); ?></h3>
                        <p class="org360-result-date">
                            <?php printf(__('Completed: %s', 'org360-assessments'), date('F j, Y', strtotime($assignment->completed_at))); ?>
                        </p>
                        <?php if ($score > 0) : ?>
                            <p class="org360-result-score">
                                <?php _e('Score:', 'org360-assessments'); ?> <strong><?php echo $score; ?>/5</strong>
                            </p>
                        <?php endif; ?>
                        <div class="org360-result-actions">
                            <a href="<?php echo add_query_arg('assignment_id', $assignment->id, get_permalink(get_option('org360_results_page'))); ?>" 
                               class="org360-button org360-button-primary">
                                <?php _e('View Details', 'org360-assessments'); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p><?php _e('You have not completed any assessments yet.', 'org360-assessments'); ?></p>
        <?php endif; ?>
        
        <div class="org360-back-link">
            <a href="<?php echo get_permalink(get_option('org360_dashboard_page')); ?>">
                &larr; <?php _e('Back to Dashboard', 'org360-assessments'); ?>
            </a>
        </div>
    </div>
    
    <?php
}

// Handle PDF download
if (isset($_GET['download_pdf']) && $assignment_id) {
    $pdf_obj = new Org360_PDF();
    $pdf_obj->generate_report($assignment_id);
    exit;
}
?>