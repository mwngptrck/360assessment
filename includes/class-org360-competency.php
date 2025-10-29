<?php
/**
 * Competency Management Class
 * Handles competencies for organizing questions
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Competency {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'org360_competencies';
    }
    
    /**
     * Create competencies table
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'org360_competencies';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            order_num int(11) NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY order_num (order_num)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Create a new competency
     */
    public function create($data) {
        global $wpdb;
        
        if (empty($data['name'])) {
            return new WP_Error('missing_fields', __('Competency name is required.', 'org360-assessments'));
        }
        
        $insert_data = array(
            'name' => sanitize_text_field($data['name']),
            'description' => isset($data['description']) ? wp_kses_post($data['description']) : '',
            'order_num' => isset($data['order_num']) ? intval($data['order_num']) : 0
        );
        
        $result = $wpdb->insert($this->table_name, $insert_data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return new WP_Error('creation_failed', __('Failed to create competency.', 'org360-assessments'));
    }
    
    /**
     * Get competency by ID
     */
    public function get($competency_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $competency_id
        ));
    }
    
    /**
     * Update competency
     */
    public function update($competency_id, $data) {
        global $wpdb;
        
        $update_data = array();
        
        if (isset($data['name'])) {
            $update_data['name'] = sanitize_text_field($data['name']);
        }
        
        if (isset($data['description'])) {
            $update_data['description'] = wp_kses_post($data['description']);
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
            array('id' => $competency_id)
        );
    }
    
    /**
     * Delete competency
     */
    public function delete($competency_id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_name,
            array('id' => $competency_id)
        );
    }
    
    /**
     * Get all competencies
     */
    public function get_all() {
        global $wpdb;
        
        return $wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY order_num ASC, name ASC"
        );
    }
    
    /**
     * Reorder competencies
     */
    public function reorder($competency_ids) {
        global $wpdb;
        
        $order = 0;
        foreach ($competency_ids as $competency_id) {
            $wpdb->update(
                $this->table_name,
                array('order_num' => $order),
                array('id' => intval($competency_id))
            );
            $order++;
        }
        
        return true;
    }
}