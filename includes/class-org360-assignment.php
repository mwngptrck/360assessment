<?php
/**
 * Assignment Management Class
 * Handles assessment assignments to users
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Assignment {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'org360_assignments';
    }
    
    /**
     * Create a new assignment
     */
    public function create($data) {
        global $wpdb;
        
        // Validate required fields
        if (empty($data['assessment_id']) || empty($data['user_id']) || empty($data['assigned_by'])) {
            return new WP_Error('missing_fields', __('Required fields are missing.', 'org360-assessments'));
        }
        
        // Check if assignment already exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE assessment_id = %d AND user_id = %d",
            $data['assessment_id'],
            $data['user_id']
        ));
        
        if ($exists) {
            return new WP_Error('assignment_exists', __('This assessment is already assigned to this user.', 'org360-assessments'));
        }
        
        // Prepare data
        $insert_data = array(
            'assessment_id' => intval($data['assessment_id']),
            'user_id' => intval($data['user_id']),
            'assigned_by' => intval($data['assigned_by']),
            'status' => 'pending'
        );
        
        $result = $wpdb->insert($this->table_name, $insert_data);
        
        if ($result) {
            $assignment_id = $wpdb->insert_id;
            
            // Send notification email
            $this->send_assignment_notification($assignment_id);
            
            return $assignment_id;
        }
        
        return new WP_Error('creation_failed', __('Failed to create assignment.', 'org360-assessments'));
    }
    
    /**
     * Get assignment by ID
     */
    public function get($assignment_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $assignment_id
        ));
    }
    
    /**
     * Update assignment
     */
    public function update($assignment_id, $data) {
        global $wpdb;
        
        $update_data = array();
        
        if (isset($data['status'])) {
            $update_data['status'] = sanitize_text_field($data['status']);
            
            // Set completed_at if status is completed
            if ($data['status'] === 'completed') {
                $update_data['completed_at'] = current_time('mysql');
            }
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        return $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $assignment_id)
        );
    }
    
    /**
     * Delete assignment
     */
    public function delete($assignment_id) {
        global $wpdb;
        
        // Delete related responses
        $response_table = $wpdb->prefix . 'org360_responses';
        $wpdb->delete($response_table, array('assignment_id' => $assignment_id));
        
        // Delete assignment
        return $wpdb->delete(
            $this->table_name,
            array('id' => $assignment_id)
        );
    }
    
    /**
     * Get assignments by user
     */
    public function get_by_user($user_id, $status = '') {
        global $wpdb;
        
        $where = $wpdb->prepare("user_id = %d", $user_id);
        
        if (!empty($status)) {
            $where .= $wpdb->prepare(" AND status = %s", $status);
        }
        
        return $wpdb->get_results(
            "SELECT * FROM {$this->table_name} WHERE {$where} ORDER BY assigned_at DESC"
        );
    }
    
    /**
     * Get assignments by assessment
     */
    public function get_by_assessment($assessment_id, $status = '') {
        global $wpdb;
        
        $where = $wpdb->prepare("assessment_id = %d", $assessment_id);
        
        if (!empty($status)) {
            $where .= $wpdb->prepare(" AND status = %s", $status);
        }
        
        return $wpdb->get_results(
            "SELECT * FROM {$this->table_name} WHERE {$where} ORDER BY assigned_at DESC"
        );
    }
    
    /**
     * Get assignment with details
     */
    public function get_with_details($assignment_id) {
        global $wpdb;
        
        $assignment = $this->get($assignment_id);
        
        if (!$assignment) {
            return null;
        }
        
        // Get assessment
        $assessment_obj = new Org360_Assessment();
        $assignment->assessment = $assessment_obj->get_with_questions($assignment->assessment_id);
        
        // Get user
        $user_obj = new Org360_User();
        $assignment->user = $user_obj->get($assignment->user_id);
        
        // Get responses if completed
        if ($assignment->status === 'completed') {
            $response_obj = new Org360_Response();
            $assignment->responses = $response_obj->get_by_assignment($assignment_id);
        }
        
        return $assignment;
    }
    
    /**
     * Mark assignment as in progress
     */
    public function mark_in_progress($assignment_id) {
        return $this->update($assignment_id, array('status' => 'in_progress'));
    }
    
    /**
     * Mark assignment as completed
     */
    public function mark_completed($assignment_id) {
        $result = $this->update($assignment_id, array('status' => 'completed'));
        
        if ($result) {
            // Send completion notification
            $this->send_completion_notification($assignment_id);
        }
        
        return $result;
    }
    
    /**
     * Send assignment notification email
     */
    private function send_assignment_notification($assignment_id) {
        $assignment = $this->get_with_details($assignment_id);
        
        if (!$assignment) {
            return;
        }
        
        $email_obj = new Org360_Email();
        $email_obj->send_assignment_notification(
            $assignment->user->email,
            $assignment->user->full_name,
            $assignment->assessment->title
        );
    }
    
    /**
     * Send completion notification email
     */
    private function send_completion_notification($assignment_id) {
        $assignment = $this->get_with_details($assignment_id);
        
        if (!$assignment) {
            return;
        }
        
        $email_obj = new Org360_Email();
        $email_obj->send_completion_notification(
            $assignment->user->email,
            $assignment->user->full_name,
            $assignment->assessment->title
        );
    }
    
    /**
     * Bulk assign assessment to multiple users
     */
    public function bulk_assign($assessment_id, $user_ids, $assigned_by) {
        $results = array(
            'success' => array(),
            'failed' => array()
        );
        
        foreach ($user_ids as $user_id) {
            $result = $this->create(array(
                'assessment_id' => $assessment_id,
                'user_id' => $user_id,
                'assigned_by' => $assigned_by
            ));
            
            if (is_wp_error($result)) {
                $results['failed'][] = $user_id;
            } else {
                $results['success'][] = $user_id;
            }
        }
        
        return $results;
    }
}