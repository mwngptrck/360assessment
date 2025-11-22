<?php
/**
 * Response Management Class
 * Handles assessment responses
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Response {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'org360_responses';
    }
    
    /**
     * Create a new response
     */
    public function create($data) {
        global $wpdb;
        
        // Validate required fields
        if (empty($data['assignment_id']) || empty($data['question_id'])) {
            return new WP_Error('missing_fields', __('Required fields are missing.', 'org360-assessments'));
        }
        
        // Prepare data
        $insert_data = array(
            'assignment_id' => intval($data['assignment_id']),
            'question_id' => intval($data['question_id']),
            'response_text' => isset($data['response_text']) ? wp_kses_post($data['response_text']) : null,
            'response_value' => isset($data['response_value']) ? sanitize_text_field($data['response_value']) : null
        );
        
        $result = $wpdb->insert($this->table_name, $insert_data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return new WP_Error('creation_failed', __('Failed to save response.', 'org360-assessments'));
    }
    
    /**
     * Get response by ID
     */
    public function get($response_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $response_id
        ));
    }
    
    /**
     * Update response
     */
    public function update($response_id, $data) {
        global $wpdb;
        
        $update_data = array();
        
        if (isset($data['response_text'])) {
            $update_data['response_text'] = wp_kses_post($data['response_text']);
        }
        
        if (isset($data['response_value'])) {
            $update_data['response_value'] = sanitize_text_field($data['response_value']);
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        return $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $response_id)
        );
    }
    
    /**
     * Delete response
     */
    public function delete($response_id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_name,
            array('id' => $response_id)
        );
    }
    
    /**
     * Get responses by assignment
     */
    public function get_by_assignment($assignment_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE assignment_id = %d",
            $assignment_id
        ));
    }
    
    /**
     * Get response by assignment and question
     */
    public function get_by_assignment_and_question($assignment_id, $question_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE assignment_id = %d AND question_id = %d",
            $assignment_id,
            $question_id
        ));
    }
    
    /**
     * Save or update response
     */
    public function save_response($assignment_id, $question_id, $response_data) {
        // Check if response already exists
        $existing = $this->get_by_assignment_and_question($assignment_id, $question_id);
        
        $data = array(
            'assignment_id' => $assignment_id,
            'question_id' => $question_id,
            'response_text' => isset($response_data['text']) ? $response_data['text'] : null,
            'response_value' => isset($response_data['value']) ? $response_data['value'] : null
        );
        
        if ($existing) {
            // Update existing response
            return $this->update($existing->id, $data);
        } else {
            // Create new response
            return $this->create($data);
        }
    }
    
    /**
     * Save multiple responses
     */
    public function save_multiple($assignment_id, $responses) {
        $results = array(
            'success' => 0,
            'failed' => 0
        );
        
        foreach ($responses as $question_id => $response_data) {
            $result = $this->save_response($assignment_id, $question_id, $response_data);
            
            if (is_wp_error($result) || $result === false) {
                $results['failed']++;
            } else {
                $results['success']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Get responses with questions
     */
    public function get_responses_with_questions($assignment_id) {
        global $wpdb;
        
        $question_table = $wpdb->prefix . 'org360_questions';
        
        $query = "SELECT r.*, q.question_text, q.question_type, q.options 
                  FROM {$this->table_name} r 
                  INNER JOIN {$question_table} q ON r.question_id = q.id 
                  WHERE r.assignment_id = %d 
                  ORDER BY q.order_num ASC";
        
        $results = $wpdb->get_results($wpdb->prepare($query, $assignment_id));
        
        // Decode options for each result
        foreach ($results as $result) {
            if ($result->options) {
                $result->options = json_decode($result->options, true);
            }
        }
        
        return $results;
    }
    
    /**
     * Calculate assessment score
     */
    public function calculate_score($assignment_id) {
        $responses = $this->get_by_assignment($assignment_id);
        
        if (empty($responses)) {
            return 0;
        }
        
        $total_score = 0;
        $count = 0;
        
        foreach ($responses as $response) {
            if ($response->response_value && is_numeric($response->response_value)) {
                $total_score += floatval($response->response_value);
                $count++;
            }
        }
        
        return $count > 0 ? round($total_score / $count, 2) : 0;
    }
}