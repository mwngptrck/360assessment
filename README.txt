=== Org360 Assessments ===
Contributors: NinjaTech AI
Tags: assessment, 360-degree, performance, evaluation, hr
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A comprehensive 360-degree assessment system for organizations with independent user management and assessment workflows.

== Description ==

Org360 Assessments is a premium-grade WordPress plugin that allows organizations to create, assign, and manage 360-degree performance assessments. The plugin features a complete, self-contained user management system independent of WordPress users.

= Key Features =

* **Independent User Management** - Complete user system separate from WordPress users
* **Custom User Roles** - Admin, Manager, Employee, and Assessor roles with specific permissions
* **Assessment Creation** - Create assessments with multiple question types
* **Question Types** - Text, Long Text, Multiple Choice, Rating Scale, Yes/No
* **Assignment System** - Assign assessments to specific users or bulk assign
* **Email Notifications** - Automated emails for registration, assignments, and completions
* **PDF Reports** - Generate and export individual or batch PDF reports
* **Frontend Dashboard** - Complete user dashboard for managing assessments
* **Secure Authentication** - Email verification, password reset, and secure login
* **Responsive Design** - Works on all devices

= User Roles =

* **Admin** - Full access to all features
* **Manager** - Can create and assign assessments, view reports
* **Employee** - Can complete assigned assessments and view own results
* **Assessor** - Can evaluate assigned employees

= Frontend Pages =

The plugin automatically creates the following pages:
* Login
* Registration
* User Dashboard
* View Assessments
* Complete Assessment
* View Results
* Forgot Password
* Reset Password

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to Plugins > Add New
3. Click "Upload Plugin"
4. Choose the org360-assessments.zip file
5. Click "Install Now"
6. Activate the plugin

= Manual Installation =

1. Upload the `org360-assessments` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to Org360 Assessments in the admin menu

= Post-Installation Setup =

1. **Install TCPDF Library (Required for PDF Reports)**
   - Download TCPDF from: https://github.com/tecnickcom/TCPDF/releases
   - Extract the TCPDF folder
   - Copy the entire TCPDF folder contents to: `/wp-content/plugins/org360-assessments/includes/tcpdf/`
   - The tcpdf.php file should be at: `/wp-content/plugins/org360-assessments/includes/tcpdf/tcpdf.php`

2. **Configure Settings**
   - Go to Org360 Assessments > Settings
   - Set your organization name
   - Configure email settings
   - Adjust registration settings

3. **Create Users**
   - Go to Org360 Assessments > Users
   - Add new users or allow frontend registration

4. **Create Assessments**
   - Go to Org360 Assessments > Assessments
   - Click "Add New"
   - Add questions and configure settings

5. **Assign Assessments**
   - Select an assessment
   - Click "Assign"
   - Choose users to assign

= Default Admin Credentials =

A default admin user is created on activation:
* **Email:** admin@org360.local
* **Password:** Admin@123

**IMPORTANT:** Change these credentials immediately after installation!

== Frequently Asked Questions ==

= Does this plugin use WordPress users? =

No, Org360 Assessments has its own independent user management system. This allows for complete separation between WordPress users and assessment users.

= Can users register themselves? =

Yes, users can register via the frontend registration page. You can enable/disable this feature in the settings.

= How do I export reports? =

Go to Org360 Assessments > Reports in the admin panel. You can export individual reports as PDF or select multiple reports for batch export as a ZIP file.

= What question types are supported? =

The plugin supports:
* Text Answer (short text)
* Long Text Answer (textarea)
* Multiple Choice (radio buttons)
* Rating Scale (1-5 scale)
* Yes/No

= Can I customize email templates? =

Email templates are built-in and use your organization name and settings. The emails are professionally designed and mobile-responsive.

= Is the plugin secure? =

Yes, the plugin follows WordPress security best practices including:
* Nonce verification for all forms
* Input sanitization and validation
* SQL injection protection
* XSS protection
* Secure password hashing
* Email verification

= Can I customize the frontend design? =

Yes, the plugin includes CSS classes that you can override in your theme's custom CSS.

== Screenshots ==

1. Admin Dashboard - Overview of all statistics
2. User Management - Create and manage users
3. Assessment Creation - Build custom assessments
4. Frontend Dashboard - User view of assigned assessments
5. Assessment Completion - User completing an assessment
6. Results View - Detailed assessment results
7. PDF Report - Generated assessment report

== Changelog ==

= 1.0.0 =
* Initial release
* Complete user management system
* Assessment creation and management
* Assignment system
* Email notifications
* PDF report generation
* Frontend dashboard
* Responsive design
* Security features

== Upgrade Notice ==

= 1.0.0 =
Initial release of Org360 Assessments plugin.

== Additional Information ==

= Support =

For support, please contact: support@org360assessments.com

= Documentation =

Full documentation available at: https://org360assessments.com/docs

= Requirements =

* WordPress 6.0 or higher
* PHP 8.0 or higher
* MySQL 5.7 or higher
* TCPDF library (for PDF generation)

= Credits =

Developed by NinjaTech AI