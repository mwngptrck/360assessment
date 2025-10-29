# Org360 Assessments - Installation Guide

## Quick Start

### 1. Install the Plugin

**Via WordPress Admin:**
1. Log in to your WordPress admin panel
2. Go to Plugins > Add New
3. Click "Upload Plugin"
4. Choose `org360-assessments.zip`
5. Click "Install Now"
6. Click "Activate Plugin"

**Via FTP:**
1. Extract `org360-assessments.zip`
2. Upload the `org360-assessments` folder to `/wp-content/plugins/`
3. Activate the plugin in WordPress admin

### 2. Install TCPDF Library (Required for PDF Reports)

The plugin requires TCPDF library for PDF report generation:

1. Download TCPDF from: https://github.com/tecnickcom/TCPDF/releases/latest
2. Extract the downloaded archive
3. Copy all files from the TCPDF folder to:
   ```
   /wp-content/plugins/org360-assessments/includes/tcpdf/
   ```
4. Verify that `tcpdf.php` exists at:
   ```
   /wp-content/plugins/org360-assessments/includes/tcpdf/tcpdf.php
   ```

**Alternative: Using Composer**
```bash
cd /wp-content/plugins/org360-assessments/includes/
composer require tecnickcom/tcpdf
```

### 3. Initial Configuration

After activation, the plugin will automatically:
- Create database tables
- Create frontend pages with shortcodes
- Create a default admin user

**Default Admin Credentials:**
- Email: `admin@org360.local`
- Password: `Admin@123`

⚠️ **IMPORTANT:** Change these credentials immediately!

### 4. Configure Settings

1. Go to **Org360 Assessments > Settings**
2. Set your **Organization Name**
3. Configure **Email Settings**:
   - From Email
   - From Name
   - Enable/Disable Notifications
4. Configure **Registration Settings**:
   - Enable/Disable Registration
   - Require Email Verification

### 5. Create Your First Assessment

1. Go to **Org360 Assessments > Assessments**
2. Click **Add New**
3. Enter assessment title and description
4. Add questions:
   - Choose question type
   - Enter question text
   - Add options (for multiple choice)
   - Mark as required/optional
5. Click **Create Assessment**

### 6. Add Users

**Option A: Admin Creates Users**
1. Go to **Org360 Assessments > Users**
2. Click **Add New**
3. Fill in user details
4. Assign role (Admin, Manager, Employee, Assessor)
5. Set status (Active, Pending, Inactive)

**Option B: Frontend Registration**
1. Share the registration page URL with users
2. Users register themselves
3. They receive verification email
4. After verification, they can log in

### 7. Assign Assessments

1. Go to **Org360 Assessments > Assessments**
2. Click **Assign** next to an assessment
3. Select users to assign
4. Click **Assign to Selected Users**
5. Users receive email notification

## Frontend Pages

The plugin creates these pages automatically:

| Page | URL | Purpose |
|------|-----|---------|
| Login | `/org360-login/` | User login |
| Register | `/org360-register/` | User registration |
| Dashboard | `/org360-dashboard/` | User dashboard |
| Assessments | `/org360-assessments/` | View assigned assessments |
| Complete | `/org360-complete-assessment/` | Complete assessments |
| Results | `/org360-results/` | View results |
| Forgot Password | `/org360-forgot-password/` | Password reset request |
| Reset Password | `/org360-reset-password/` | Reset password |

## User Workflow

### For Employees:
1. Register or receive login credentials
2. Verify email (if required)
3. Log in to dashboard
4. View assigned assessments
5. Complete assessments
6. View results

### For Managers:
1. Log in to admin panel
2. Create assessments
3. Assign to employees
4. View reports
5. Export PDFs

### For Admins:
1. Full access to all features
2. Manage users
3. Manage assessments
4. View all reports
5. Configure settings

## Troubleshooting

### PDF Generation Not Working
- Ensure TCPDF library is installed correctly
- Check file permissions on the tcpdf folder
- Verify PHP memory limit (minimum 128MB recommended)

### Email Not Sending
- Check WordPress email configuration
- Verify SMTP settings
- Test with a plugin like WP Mail SMTP
- Check spam folders

### Users Can't Register
- Verify registration is enabled in settings
- Check email verification settings
- Ensure registration page exists

### Database Errors
- Check database permissions
- Verify WordPress database prefix
- Try deactivating and reactivating the plugin

### Frontend Pages Not Working
- Go to Settings > Permalinks
- Click "Save Changes" to flush rewrite rules
- Check if pages exist in Pages menu

## Security Best Practices

1. **Change Default Credentials** immediately after installation
2. **Use Strong Passwords** for all users
3. **Enable Email Verification** for registrations
4. **Regular Backups** of your database
5. **Keep WordPress Updated** to latest version
6. **Use SSL Certificate** (HTTPS) for your site
7. **Limit Admin Access** to trusted users only

## System Requirements

- **WordPress:** 6.0 or higher
- **PHP:** 8.0 or higher
- **MySQL:** 5.7 or higher
- **Memory:** 128MB minimum (256MB recommended)
- **Disk Space:** 50MB minimum

## Support

For support and questions:
- Email: support@org360assessments.com
- Documentation: https://org360assessments.com/docs

## Credits

Developed by NinjaTech AI
Version 1.0.0