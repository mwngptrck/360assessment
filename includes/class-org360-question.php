<?php
/**
 * Question Management Class
 * Handles all question-related operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Question {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'org360_questions';
    }
    
    /**
     * Create a new question
     */
    public function create($data) {
        global $wpdb;
        
        // Validate required fields
        if (empty($data['question_text']) || empty($data['question_type'])) {
            return new WP_Error('missing_fields', __('Required fields are missing.', 'org360-assessments'));
        }
        
        // Prepare data
        $insert_data = array(
            'question_text' => wp_kses_post($data['question_text']),
            'question_type' => sanitize_text_field($data['question_type']),
            'options' => isset($data['options']) ? wp_json_encode($data['options']) : null,
            'order_num' => isset($data['order_num']) ? intval($data['order_num']) : 0,
            'required' => isset($data['required']) ? intval($data['required']) : 1
        );
        
        // Add assessment_id or questionnaire_id
        if (!empty($data['assessment_id'])) {
            $insert_data['assessment_id'] = intval($data['assessment_id']);
        }
        
        if (!empty($data['questionnaire_id'])) {
            $insert_data['questionnaire_id'] = intval($data['questionnaire_id']);
        }
        
        $result = $wpdb->insert($this->table_name, $insert_data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return new WP_Error('creation_failed', __('Failed to create question.', 'org360-assessments'));
    }
    
    /**
     * Get question by ID
     */
    public function get($question_id) {
        global $wpdb;
        
        $question = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $question_id
        ));
        
        if ($question && $question->options) {
            $question->options = json_decode($question->options, true);
        }
        
        return $question;
    }
    
    /**
     * Update question
     */
    public function update($question_id, $data) {
        global $wpdb;
        
        $update_data = array();
        
        if (isset($data['question_text'])) {
            $update_data['question_text'] = wp_kses_post($data['question_text']);
        }
        
        if (isset($data['question_type'])) {
            $update_data['question_type'] = sanitize_text_field($data['question_type']);
        }
        
        if (isset($data['options'])) {
            $update_data['options'] = wp_json_encode($data['options']);
        }
        
        if (isset($data['order_num'])) {
            $update_data['order_num'] = intval($data['order_num']);
        }
        
        if (isset($data['required'])) {
            $update_data['required'] = intval($data['required']);
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        return $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $question_id)
        );
    }
    
    /**
     * Delete question
     */
    public function delete($question_id) {
        global $wpdb;
        
        // Delete related responses
        $response_table = $wpdb->prefix . 'org360_responses';
        $wpdb->delete($response_table, array('question_id' => $question_id));
        
        // Delete question
        return $wpdb->delete(
            $this->table_name,
            array('id' => $question_id)
        );
    }
    
    /**
     * Get questions by assessment
     */
    public function get_by_assessment($assessment_id) {
        global $wpdb;
        
        $questions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE assessment_id = %d ORDER BY order_num ASC",
            $assessment_id
        ));
        
        // Decode options for each question
        foreach ($questions as $question) {
            if ($question->options) {
                $question->options = json_decode($question->options, true);
            }
        }
        
        return $questions;
    }
    
    /**
     * Get questions by questionnaire
     */
    public function get_by_questionnaire($questionnaire_id) {
        global $wpdb;
        
        $questions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE questionnaire_id = %d ORDER BY order_num ASC",
            $questionnaire_id
        ));
        
        // Decode options for each question
        foreach ($questions as $question) {
            if ($question->options) {
                $question->options = json_decode($question->options, true);
            }
        }
        
        return $questions;
    }
    
    /**
     * Reorder questions
     */
    public function reorder($question_ids) {
        global $wpdb;
        
        $order = 0;
        foreach ($question_ids as $question_id) {
            $wpdb->update(
                $this->table_name,
                array('order_num' => $order),
                array('id' => intval($question_id))
            );
            $order++;
        }
        
        return true;
    }
    
    /**
     * Get question types
     */
    public static function get_question_types() {
        return array(
            'text' => __('Text Answer', 'org360-assessments'),
            'textarea' => __('Long Text Answer', 'org360-assessments'),
            'multiple_choice' => __('Multiple Choice', 'org360-assessments'),
            'rating' => __('Rating Scale', 'org360-assessments'),
            'yes_no' => __('Yes/No', 'org360-assessments')
        );
    }
}