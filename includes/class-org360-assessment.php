<?php
/**
 * Assessment Management Class
 * Handles all assessment-related operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Assessment {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'org360_assessments';
    }
    
    /**
     * Create a new assessment
     */
    public function create($data) {
        global $wpdb;
        
        // Validate required fields
        if (empty($data['title']) || empty($data['created_by'])) {
            return new WP_Error('missing_fields', __('Required fields are missing.', 'org360-assessments'));
        }
        
        // Prepare data
        $insert_data = array(
            'title' => sanitize_text_field($data['title']),
            'description' => isset($data['description']) ? wp_kses_post($data['description']) : '',
            'created_by' => intval($data['created_by']),
            'status' => isset($data['status']) ? sanitize_text_field($data['status']) : 'draft'
        );
        
        $result = $wpdb->insert($this->table_name, $insert_data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return new WP_Error('creation_failed', __('Failed to create assessment.', 'org360-assessments'));
    }
    
    /**
     * Get assessment by ID
     */
    public function get($assessment_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $assessment_id
        ));
    }
    
    /**
     * Update assessment
     */
    public function update($assessment_id, $data) {
        global $wpdb;
        
        $update_data = array();
        
        if (isset($data['title'])) {
            $update_data['title'] = sanitize_text_field($data['title']);
        }
        
        if (isset($data['description'])) {
            $update_data['description'] = wp_kses_post($data['description']);
        }
        
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        return $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $assessment_id)
        );
    }
    
    /**
     * Delete assessment
     */
    public function delete($assessment_id) {
        global $wpdb;
        
        // Delete related questions
        $question_obj = new Org360_Question();
        $questions = $question_obj->get_by_assessment($assessment_id);
        foreach ($questions as $question) {
            $question_obj->delete($question->id);
        }
        
        // Delete related assignments
        $assignment_obj = new Org360_Assignment();
        $assignments = $assignment_obj->get_by_assessment($assessment_id);
        foreach ($assignments as $assignment) {
            $assignment_obj->delete($assignment->id);
        }
        
        // Delete assessment
        return $wpdb->delete(
            $this->table_name,
            array('id' => $assessment_id)
        );
    }
    
    /**
     * Get all assessments
     */
    public function get_all($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'status' => '',
            'created_by' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 100,
            'offset' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        
        if (!empty($args['status'])) {
            $where[] = $wpdb->prepare("status = %s", $args['status']);
        }
        
        if (!empty($args['created_by'])) {
            $where[] = $wpdb->prepare("created_by = %d", $args['created_by']);
        }
        
        $where_clause = implode(' AND ', $where);
        
        $query = "SELECT * FROM {$this->table_name} 
                  WHERE {$where_clause} 
                  ORDER BY {$args['orderby']} {$args['order']} 
                  LIMIT {$args['limit']} OFFSET {$args['offset']}";
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Count assessments
     */
    public function count($args = array()) {
        global $wpdb;
        
        $where = array('1=1');
        
        if (!empty($args['status'])) {
            $where[] = $wpdb->prepare("status = %s", $args['status']);
        }
        
        if (!empty($args['created_by'])) {
            $where[] = $wpdb->prepare("created_by = %d", $args['created_by']);
        }
        
        $where_clause = implode(' AND ', $where);
        
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}");
    }
    
    /**
     * Get assessment with questions
     */
    public function get_with_questions($assessment_id) {
        $assessment = $this->get($assessment_id);
        
        if (!$assessment) {
            return null;
        }
        
        $question_obj = new Org360_Question();
        $assessment->questions = $question_obj->get_by_assessment($assessment_id);
        
        return $assessment;
    }
    
    /**
     * Publish assessment
     */
    public function publish($assessment_id) {
        return $this->update($assessment_id, array('status' => 'published'));
    }
    
    /**
     * Archive assessment
     */
    public function archive($assessment_id) {
        return $this->update($assessment_id, array('status' => 'archived'));
    }
    
    /**
     * Get assessment statistics
     */
    public function get_statistics($assessment_id) {
        global $wpdb;
        
        $assignment_table = $wpdb->prefix . 'org360_assignments';
        
        $stats = array(
            'total_assigned' => 0,
            'completed' => 0,
            'pending' => 0,
            'in_progress' => 0
        );
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT status, COUNT(*) as count 
             FROM {$assignment_table} 
             WHERE assessment_id = %d 
             GROUP BY status",
            $assessment_id
        ));
        
        foreach ($results as $result) {
            $stats['total_assigned'] += $result->count;
            $stats[$result->status] = $result->count;
        }
        
        return $stats;
    }
}