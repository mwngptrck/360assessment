<?php
/**
 * Questionnaire Management Class
 * Handles questionnaires that group questions by competency
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Questionnaire {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'org360_questionnaires';
    }
    
    /**
     * Create questionnaires table
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'org360_questionnaires';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            assessment_id bigint(20) UNSIGNED NOT NULL,
            competency_id bigint(20) UNSIGNED NOT NULL,
            title varchar(255) NOT NULL,
            description text,
            order_num int(11) NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY assessment_id (assessment_id),
            KEY competency_id (competency_id),
            KEY order_num (order_num)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create a new questionnaire
     */
    public function create($data) {
        global $wpdb;
        
        if (empty($data['assessment_id']) || empty($data['competency_id']) || empty($data['title'])) {
            return new WP_Error('missing_fields', __('Required fields are missing.', 'org360-assessments'));
        }
        
        $insert_data = array(
            'assessment_id' => intval($data['assessment_id']),
            'competency_id' => intval($data['competency_id']),
            'title' => sanitize_text_field($data['title']),
            'description' => isset($data['description']) ? wp_kses_post($data['description']) : '',
            'order_num' => isset($data['order_num']) ? intval($data['order_num']) : 0
        );
        
        $result = $wpdb->insert($this->table_name, $insert_data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return new WP_Error('creation_failed', __('Failed to create questionnaire.', 'org360-assessments'));
    }
    
    /**
     * Get questionnaire by ID
     */
    public function get($questionnaire_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $questionnaire_id
        ));
    }
    
    /**
     * Update questionnaire
     */
    public function update($questionnaire_id, $data) {
        global $wpdb;
        
        $update_data = array();
        
        if (isset($data['title'])) {
            $update_data['title'] = sanitize_text_field($data['title']);
        }
        
        if (isset($data['description'])) {
            $update_data['description'] = wp_kses_post($data['description']);
        }
        
        if (isset($data['competency_id'])) {
            $update_data['competency_id'] = intval($data['competency_id']);
        }
        
        if (isset($data['order_num'])) {
            $update_data['order_num'] = intval($data['order_num']);
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        return $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $questionnaire_id)
        );
    }
    
    /**
     * Delete questionnaire
     */
    public function delete($questionnaire_id) {
        global $wpdb;
        
        // Delete related questions
        $question_obj = new Org360_Question();
        $questions = $question_obj->get_by_questionnaire($questionnaire_id);
        foreach ($questions as $question) {
            $question_obj->delete($question->id);
        }
        
        return $wpdb->delete(
            $this->table_name,
            array('id' => $questionnaire_id)
        );
    }
    
    /**
     * Get questionnaires by assessment
     */
    public function get_by_assessment($assessment_id) {
        global $wpdb;
        
        $competency_table = $wpdb->prefix . 'org360_competencies';
        
        $query = "SELECT q.*, c.name as competency_name 
                  FROM {$this->table_name} q 
                  LEFT JOIN {$competency_table} c ON q.competency_id = c.id 
                  WHERE q.assessment_id = %d 
                  ORDER BY q.order_num ASC";
        
        return $wpdb->get_results($wpdb->prepare($query, $assessment_id));
    }
    
    /**
     * Get questionnaire with questions
     */
    public function get_with_questions($questionnaire_id) {
        $questionnaire = $this->get($questionnaire_id);
        
        if (!$questionnaire) {
            return null;
        }
        
        $question_obj = new Org360_Question();
        $questionnaire->questions = $question_obj->get_by_questionnaire($questionnaire_id);
        
        return $questionnaire;
    }
    
    /**
     * Reorder questionnaires
     */
    public function reorder($questionnaire_ids) {
        global $wpdb;
        
        $order = 0;
        foreach ($questionnaire_ids as $questionnaire_id) {
            $wpdb->update(
                $this->table_name,
                array('order_num' => $order),
                array('id' => intval($questionnaire_id))
            );
            $order++;
        }
        
        return true;
    }
}