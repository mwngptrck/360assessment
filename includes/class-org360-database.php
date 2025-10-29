<?php
/**
 * Database Management Class
 * Handles all database operations and table creation
 */

if (!defined('ABSPATH')) {
    exit;
}

class Org360_Database {
    
    /**
     * Create all plugin tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Users table
        $table_users = $wpdb->prefix . 'org360_users';
        $sql_users = "CREATE TABLE $table_users (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            full_name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            password varchar(255) NOT NULL,
            role varchar(50) NOT NULL DEFAULT 'employee',
            status varchar(20) NOT NULL DEFAULT 'pending',
            department_id bigint(20) UNSIGNED DEFAULT NULL,
            verification_token varchar(255) DEFAULT NULL,
            reset_token varchar(255) DEFAULT NULL,
            reset_token_expiry datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY role (role),
            KEY status (status),
            KEY department_id (department_id)
        ) $charset_collate;";
        
        // Assessments table
        $table_assessments = $wpdb->prefix . 'org360_assessments';
        $sql_assessments = "CREATE TABLE $table_assessments (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text,
            created_by bigint(20) UNSIGNED NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'draft',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY created_by (created_by),
            KEY status (status)
        ) $charset_collate;";
        
        // Questions table
        $table_questions = $wpdb->prefix . 'org360_questions';
        $sql_questions = "CREATE TABLE $table_questions (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            assessment_id bigint(20) UNSIGNED DEFAULT NULL,
            questionnaire_id bigint(20) UNSIGNED DEFAULT NULL,
            question_text text NOT NULL,
            question_type varchar(50) NOT NULL,
            options text,
            order_num int(11) NOT NULL DEFAULT 0,
            required tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY assessment_id (assessment_id),
            KEY questionnaire_id (questionnaire_id),
            KEY order_num (order_num)
        ) $charset_collate;";
        
        // Assignments table
        $table_assignments = $wpdb->prefix . 'org360_assignments';
        $sql_assignments = "CREATE TABLE $table_assignments (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            assessment_id bigint(20) UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            assigned_by bigint(20) UNSIGNED NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            assigned_at datetime DEFAULT CURRENT_TIMESTAMP,
            completed_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_assignment (assessment_id, user_id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";
        
        // Responses table
        $table_responses = $wpdb->prefix . 'org360_responses';
        $sql_responses = "CREATE TABLE $table_responses (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            assignment_id bigint(20) UNSIGNED NOT NULL,
            question_id bigint(20) UNSIGNED NOT NULL,
            response_text text,
            response_value varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY assignment_id (assignment_id),
            KEY question_id (question_id)
        ) $charset_collate;";
        
        // Reports table
        $table_reports = $wpdb->prefix . 'org360_reports';
        $sql_reports = "CREATE TABLE $table_reports (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            assignment_id bigint(20) UNSIGNED NOT NULL,
            report_data longtext,
            generated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY assignment_id (assignment_id)
        ) $charset_collate;";
        
        // Settings table
        $table_settings = $wpdb->prefix . 'org360_settings';
        $sql_settings = "CREATE TABLE $table_settings (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            setting_key varchar(255) NOT NULL,
            setting_value longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";
        
        // Execute table creation
        dbDelta($sql_users);
        dbDelta($sql_assessments);
        dbDelta($sql_questions);
        dbDelta($sql_assignments);
        dbDelta($sql_responses);
        dbDelta($sql_reports);
        dbDelta($sql_settings);
        
        // Create competencies and questionnaires tables
        Org360_Competency::create_table();
        Org360_Questionnaire::create_table();
        Org360_Department::create_table();
        
        // Insert default settings
        self::insert_default_settings();
    }
    
    /**
     * Insert default settings
     */
    private static function insert_default_settings() {
        global $wpdb;
        $table = $wpdb->prefix . 'org360_settings';
        
        $default_settings = array(
            'organization_name' => 'Org360 Assessments',
            'from_email' => get_option('admin_email'),
            'from_name' => get_option('blogname'),
            'enable_email_notifications' => '1',
            'registration_enabled' => '1',
            'require_email_verification' => '1'
        );
        
        foreach ($default_settings as $key => $value) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE setting_key = %s",
                $key
            ));
            
            if (!$exists) {
                $wpdb->insert($table, array(
                    'setting_key' => $key,
                    'setting_value' => $value
                ));
            }
        }
    }
    
    /**
     * Drop all plugin tables
     */
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'org360_users',
            $wpdb->prefix . 'org360_departments',
            $wpdb->prefix . 'org360_assessments',
            $wpdb->prefix . 'org360_competencies',
            $wpdb->prefix . 'org360_questionnaires',
            $wpdb->prefix . 'org360_questions',
            $wpdb->prefix . 'org360_assignments',
            $wpdb->prefix . 'org360_responses',
            $wpdb->prefix . 'org360_reports',
            $wpdb->prefix . 'org360_settings'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
    
    /**
     * Get setting value
     */
    public static function get_setting($key, $default = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'org360_settings';
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM $table WHERE setting_key = %s",
            $key
        ));
        
        return $value !== null ? $value : $default;
    }
    
    /**
     * Update setting value
     */
    public static function update_setting($key, $value) {
        global $wpdb;
        $table = $wpdb->prefix . 'org360_settings';
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE setting_key = %s",
            $key
        ));
        
        if ($exists) {
            return $wpdb->update(
                $table,
                array('setting_value' => $value),
                array('setting_key' => $key)
            );
        } else {
            return $wpdb->insert($table, array(
                'setting_key' => $key,
                'setting_value' => $value
            ));
        }
    }
}