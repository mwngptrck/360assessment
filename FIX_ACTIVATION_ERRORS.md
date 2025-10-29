# Fix for Plugin Activation Errors

## 🔧 Common Activation Issues and Solutions

### Issue 1: Session Management During Activation
**Problem**: Multiple `session_start()` calls during plugin activation can cause fatal errors.

**Solution**: Add session checks and proper error handling.

### Issue 2: Database Table Creation
**Problem**: Missing tables or incorrect table structure can cause activation failures.

**Solution**: Ensure all required tables are created properly.

### Issue 3: Missing Dependencies
**Problem**: Required files not being loaded in correct order.

**Solution**: Verify all dependencies are properly included.

## 🛠️ Quick Fix Steps

### Step 1: Fix Session Issues
Add proper session checks in the main plugin file:

```php
// In org360-assessments.php, replace the init function:
public function init() {
    // Only start session on frontend or when needed
    if (!is_admin() && !session_id()) {
        session_start();
    }
    
    // Initialize components
    if (is_admin()) {
        new Org360_Admin();
    }
    
    new Org360_Public();
    new Org360_Shortcodes();
}
```

### Step 2: Fix Database Creation
Ensure database tables are created in correct order:

```php
// In class-org360-database.php, ensure proper table creation order:
public static function create_tables() {
    // Create departments table first (new dependency)
    self::create_table('departments');
    
    // Then create users table (depends on departments)
    self::create_table('users');
    
    // Then create other tables
    self::create_table('assessments');
    self::create_table('competencies');
    self::create_table('questionnaires');
    self::create_table('questions');
    self::create_table('assignments');
    self::create_table('responses');
    self::create_table('reports');
    self::create_table('settings');
}
```

### Step 3: Add Error Handling
Add try-catch blocks for database operations:

```php
public function activate() {
    try {
        // Create database tables
        Org360_Database::create_tables();
        
        // Create default pages
        $this->create_default_pages();
        
        // Create default admin user if none exists
        $this->create_default_admin();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation flag
        update_option('org360_activated', true);
        update_option('org360_version', ORG360_VERSION);
        
    } catch (Exception $e) {
        // Log error and show user-friendly message
        error_log('Org360 Activation Error: ' . $e->getMessage());
        wp_die('Plugin activation failed. Please check error logs.');
    }
}
```

## 🔍 Debugging Steps

### 1. Check PHP Error Logs
Look for specific error messages in your PHP error log:
```bash
# Common locations:
/var/log/php_errors.log
/var/log/apache2/error.log
/var/log/nginx/error.log
```

### 2. Enable WordPress Debug Mode
Add to wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### 3. Check for Specific Errors
Common WordPress plugin activation errors:
- "Cannot modify header information" - Usually session-related
- "Call to undefined function" - Missing dependencies
- "Table doesn't exist" - Database creation issues

## 🚨 Critical Fixes Needed

### Fix 1: Session Handling in Main Plugin File
Replace the problematic session handling:

**Current Issue** (lines ~160):
```php
// This can cause issues during activation
if (!session_id()) {
    session_start();
}
```

**Fix**:
```php
// Only start session when needed, not during activation
public function init() {
    // Don't start session during plugin activation
    if (defined('WP_INSTALLING') && WP_INSTALLING) {
        return;
    }
    
    // Only start session for actual user interactions
    if (!is_admin() && !session_id() && !headers_sent()) {
        session_start();
    }
    
    // Initialize components
    if (is_admin()) {
        new Org360_Admin();
    }
    
    new Org360_Public();
    new Org360_Shortcodes();
}
```

### Fix 2: Database Table Dependencies
Ensure tables are created in correct order:

**Create departments table before users table** since users now have department_id foreign key.

### Fix 3: Include Missing Department Class
Make sure the department class is included before it's used:

```php
// In load_dependencies function, ensure this order:
require_once ORG360_PLUGIN_DIR . 'includes/class-org360-department.php';
require_once ORG360_PLUGIN_DIR . 'includes/class-org360-user.php';
```

## 🎯 Immediate Solution

If you're experiencing activation errors, here are immediate steps:

### Option 1: Manual Fix
1. Extract the plugin files
2. Apply the fixes mentioned above
3. Re-zip and upload

### Option 2: Clean Installation
1. Delete any existing plugin files
2. Clear WordPress cache
3. Upload the corrected version
4. Activate with debug mode enabled

### Option 3: Debug Mode Activation
1. Enable WordPress debug mode
2. Try activation and check error logs
3. Apply specific fixes based on error messages

## 📧 Support

If you continue experiencing issues:
1. Check your PHP error logs for specific error messages
2. Enable WordPress debug mode for detailed error information
3. Provide the exact error message for targeted assistance

The plugin structure is solid, but these session and dependency issues need to be resolved for smooth activation.