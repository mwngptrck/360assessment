<?php
/**
 * Admin Assessments Class
 * Handles assessment management in admin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Admin_Assessments {
    
    public function render() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        switch ($action) {
            case 'add':
                $this->add_assessment_form();
                break;
            case 'edit':
                $this->edit_assessment_form();
                break;
            case 'delete':
                $this->delete_assessment();
                break;
            case 'assign':
                $this->assign_assessment_form();
                break;
            default:
                $this->list_assessments();
                break;
        }
    }
    
    /**
     * List assessments
     */
    private function list_assessments() {
        // Handle form submissions
        if (isset($_POST['org360_create_assessment'])) {
            $this->handle_create_assessment();
        }
        
        if (isset($_POST['org360_update_assessment'])) {
            $this->handle_update_assessment();
        }
        
        if (isset($_POST['org360_assign_assessment'])) {
            $this->handle_assign_assessment();
        }
        
        $assessment_obj = new Org360_Assessment();
        $assessments = $assessment_obj->get_all(array('limit' => 1000));
        
        include ORG360_PLUGIN_DIR . 'admin/views/assessments-list.php';
    }
    
    /**
     * Add assessment form
     */
    private function add_assessment_form() {
        include ORG360_PLUGIN_DIR . 'admin/views/assessment-add.php';
    }
    
    /**
     * Edit assessment form
     */
    private function edit_assessment_form() {
        $assessment_id = isset($_GET['assessment_id']) ? intval($_GET['assessment_id']) : 0;
        $assessment_obj = new Org360_Assessment();
        $assessment = $assessment_obj->get_with_questions($assessment_id);
        
        if (!$assessment) {
            wp_die(__('Assessment not found.', 'org360-assessments'));
        }
        
        include ORG360_PLUGIN_DIR . 'admin/views/assessment-edit.php';
    }
    
    /**
     * Assign assessment form
     */
    private function assign_assessment_form() {
        $assessment_id = isset($_GET['assessment_id']) ? intval($_GET['assessment_id']) : 0;
        $assessment_obj = new Org360_Assessment();
        $assessment = $assessment_obj->get($assessment_id);
        
        if (!$assessment) {
            wp_die(__('Assessment not found.', 'org360-assessments'));
        }
        
        $user_obj = new Org360_User();
        $users = $user_obj->get_all(array('limit' => 1000));
        
        include ORG360_PLUGIN_DIR . 'admin/views/assessment-assign.php';
    }
    
    /**
     * Handle create assessment
     */
    private function handle_create_assessment() {
        check_admin_referer('org360_create_assessment', 'org360_create_assessment_nonce');
        
        $title = sanitize_text_field($_POST['title']);
        $description = wp_kses_post($_POST['description']);
        $questionnaires = isset($_POST['questionnaires']) ? $_POST['questionnaires'] : array();
        $assign_users = isset($_POST['assign_users']) ? array_map('intval', $_POST['assign_users']) : array();
        
        $assessment_obj = new Org360_Assessment();
        $assessment_id = $assessment_obj->create(array(
            'title' => $title,
            'description' => $description,
            'created_by' => get_current_user_id(),
            'status' => 'published'
        ));
        
        if (!is_wp_error($assessment_id)) {
            // Add questionnaires and questions
            $questionnaire_obj = new Org360_Questionnaire();
            $question_obj = new Org360_Question();
            $q_order = 0;
            
            foreach ($questionnaires as $q_data) {
                if (empty($q_data['title']) || empty($q_data['competency_id'])) continue;
                
                // Create questionnaire
                $questionnaire_id = $questionnaire_obj->create(array(
                    'assessment_id' => $assessment_id,
                    'competency_id' => intval($q_data['competency_id']),
                    'title' => sanitize_text_field($q_data['title']),
                    'description' => isset($q_data['description']) ? wp_kses_post($q_data['description']) : '',
                    'order_num' => $q_order
                ));
                
                if (!is_wp_error($questionnaire_id)) {
                    // Add questions to questionnaire
                    $questions = isset($q_data['questions']) ? $q_data['questions'] : array();
                    $question_order = 0;
                    
                    foreach ($questions as $question_data) {
                        if (empty($question_data['text'])) continue;
                        
                        $options = null;
                        if ($question_data['type'] === 'multiple_choice' && !empty($question_data['options'])) {
                            $options = array_filter(array_map('trim', explode("\n", $question_data['options'])));
                        }
                        
                        $question_obj->create(array(
                            'questionnaire_id' => $questionnaire_id,
                            'question_text' => $question_data['text'],
                            'question_type' => $question_data['type'],
                            'options' => $options,
                            'order_num' => $question_order,
                            'required' => isset($question_data['required']) ? 1 : 0
                        ));
                        
                        $question_order++;
                    }
                }
                
                $q_order++;
            }
            
            // Assign to users if selected
            if (!empty($assign_users)) {
                $assignment_obj = new Org360_Assignment();
                $assignment_obj->bulk_assign($assessment_id, $assign_users, get_current_user_id());
            }
            
            add_settings_error('org360_messages', 'org360_message', __('Assessment created successfully.', 'org360-assessments'), 'success');
            
            // Store assessment ID for potential AJAX assignment
            $_SESSION['org360_last_created_assessment'] = $assessment_id;
            $_SESSION['org360_pending_user_assignments'] = $assign_users;
        } else {
            add_settings_error('org360_messages', 'org360_message', $assessment_id->get_error_message(), 'error');
        }
    }
    
    /**
     * Handle update assessment
     */
    private function handle_update_assessment() {
        check_admin_referer('org360_update_assessment', 'org360_update_assessment_nonce');
        
        $assessment_id = intval($_POST['assessment_id']);
        $title = sanitize_text_field($_POST['title']);
        $description = wp_kses_post($_POST['description']);
        $questionnaires = isset($_POST['questionnaires']) ? $_POST['questionnaires'] : array();
        $assign_users = isset($_POST['assign_users']) ? array_map('intval', $_POST['assign_users']) : array();
        
        $assessment_obj = new Org360_Assessment();
        $assessment_obj->update($assessment_id, array(
            'title' => $title,
            'description' => $description
        ));
        
        // Delete existing questionnaires and questions
        $questionnaire_obj = new Org360_Questionnaire();
        $existing_questionnaires = $questionnaire_obj->get_by_assessment($assessment_id);
        foreach ($existing_questionnaires as $q) {
            $questionnaire_obj->delete($q->id);
        }
        
        // Add new questionnaires and questions
        $question_obj = new Org360_Question();
        $q_order = 0;
        
        foreach ($questionnaires as $q_data) {
            if (empty($q_data['title']) || empty($q_data['competency_id'])) continue;
            
            // Create questionnaire
            $questionnaire_id = $questionnaire_obj->create(array(
                'assessment_id' => $assessment_id,
                'competency_id' => intval($q_data['competency_id']),
                'title' => sanitize_text_field($q_data['title']),
                'description' => isset($q_data['description']) ? wp_kses_post($q_data['description']) : '',
                'order_num' => $q_order
            ));
            
            if (!is_wp_error($questionnaire_id)) {
                // Add questions to questionnaire
                $questions = isset($q_data['questions']) ? $q_data['questions'] : array();
                $question_order = 0;
                
                foreach ($questions as $question_data) {
                    if (empty($question_data['text'])) continue;
                    
                    $options = null;
                    if ($question_data['type'] === 'multiple_choice' && !empty($question_data['options'])) {
                        $options = array_filter(array_map('trim', explode("\n", $question_data['options'])));
                    }
                    
                    $question_obj->create(array(
                        'questionnaire_id' => $questionnaire_id,
                        'question_text' => $question_data['text'],
                        'question_type' => $question_data['type'],
                        'options' => $options,
                        'order_num' => $question_order,
                        'required' => isset($question_data['required']) ? 1 : 0
                    ));
                    
                    $question_order++;
                }
            }
            
            $q_order++;
        }
        
        // Update user assignments
        $assignment_obj = new Org360_Assignment();
        
        // Get existing assignments
        $existing_assignments = $assignment_obj->get_by_assessment($assessment_id);
        $existing_user_ids = array_map(function($a) { return $a->user_id; }, $existing_assignments);
        
        // Remove users that are no longer selected
        foreach ($existing_assignments as $assignment) {
            if (!in_array($assignment->user_id, $assign_users)) {
                $assignment_obj->delete($assignment->id);
            }
        }
        
        // Add new users
        $new_users = array_diff($assign_users, $existing_user_ids);
        if (!empty($new_users)) {
            $assignment_obj->bulk_assign($assessment_id, $new_users, get_current_user_id());
        }
        
        add_settings_error('org360_messages', 'org360_message', __('Assessment updated successfully.', 'org360-assessments'), 'success');
    }
    
    /**
     * Handle assign assessment
     */
    private function handle_assign_assessment() {
        check_admin_referer('org360_assign_assessment', 'org360_assign_assessment_nonce');
        
        $assessment_id = intval($_POST['assessment_id']);
        $user_ids = isset($_POST['user_ids']) ? array_map('intval', $_POST['user_ids']) : array();
        
        $assignment_obj = new Org360_Assignment();
        $results = $assignment_obj->bulk_assign($assessment_id, $user_ids, get_current_user_id());
        
        $message = sprintf(
            __('Assessment assigned to %d users. %d failed.', 'org360-assessments'),
            count($results['success']),
            count($results['failed'])
        );
        
        add_settings_error('org360_messages', 'org360_message', $message, 'success');
    }
    
    /**
     * Delete assessment
     */
    private function delete_assessment() {
        check_admin_referer('org360_delete_assessment_' . $_GET['assessment_id']);
        
        $assessment_id = intval($_GET['assessment_id']);
        $assessment_obj = new Org360_Assessment();
        $assessment_obj->delete($assessment_id);
        
        wp_redirect(admin_url('admin.php?page=org360-assessments-manage&deleted=1'));
        exit;
    }
}