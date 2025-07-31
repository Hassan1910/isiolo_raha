# InfinityFree Deployment Guide for Isiolo Raha Bus Booking System

This guide will help you deploy your Isiolo Raha Bus Booking System to InfinityFree hosting.

## Prerequisites

1. InfinityFree account (free at infinityfree.net)
2. FTP client (FileZilla recommended)
3. Your project files ready for upload

## Step 1: Prepare Your Files for Deployment

### 1.1 Update Database Configuration

Create a new database configuration file for production:

**File: `config/database_production.php`**
```php
<?php
/**
 * Production Database Configuration for InfinityFree
 */

// InfinityFree database credentials (replace with your actual values)
define('DB_HOST', 'sql200.infinityfree.com'); // Your InfinityFree MySQL hostname
define('DB_USER', 'if0_12345678');           // Your InfinityFree database username
define('DB_PASS', 'your_password');          // Your InfinityFree database password
define('DB_NAME', 'if0_12345678_isioloraha'); // Your InfinityFree database name

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// Return the connection
return $conn;
?>
```

### 1.2 Update Main Configuration

Modify `config/config.php` for production:

```php
// Add this at the top after <?php
$isProduction = !in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);

// Update base URL detection
if ($isProduction) {
    $baseUrl = "https://your-subdomain.infinityfreeapp.com"; // Replace with your actual domain
} else {
    // Keep existing localhost logic
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = "$protocol://$host/isioloraha";
}

// Turn off error reporting in production
if ($isProduction) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
```

### 1.3 Update Database Connection References

Create a smart database loader in `config/database.php`:

```php
<?php
/**
 * Smart Database Configuration Loader
 */

// Detect environment
$isProduction = !in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);

if ($isProduction) {
    // Load production database config
    return require_once 'database_production.php';
} else {
    // Load local database config
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'isioloraha');
    
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    if ($conn->query($sql) === FALSE) {
        die("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db(DB_NAME);
    
    return $conn;
}
?>
```

### 1.4 Update .htaccess for Production

Add these lines to the top of your `.htaccess` file:

```apache
# Force HTTPS on InfinityFree
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Handle InfinityFree subdirectory structure
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

## Step 2: Set Up InfinityFree Account

### 2.1 Create Account
1. Go to infinityfree.net
2. Click "Create Account"
3. Choose a subdomain (e.g., `your-project.infinityfreeapp.com`)
4. Complete registration

### 2.2 Access Control Panel
1. Login to your InfinityFree account
2. Go to "Control Panel"
3. Note your FTP credentials and database information

### 2.3 Create Database
1. In Control Panel, go to "MySQL Databases"
2. Create a new database
3. Note the database name, username, and password
4. Update your `database_production.php` with these credentials

## Step 3: Upload Files

### 3.1 Prepare Files
1. Remove any local-only files:
   - `vendor/` folder (if exists)
   - `.git/` folder
   - Local database dumps
   - Test files

2. Install Composer dependencies locally first:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

### 3.2 Upload via FTP
1. Open FileZilla or your preferred FTP client
2. Connect using your InfinityFree FTP credentials:
   - Host: `ftpupload.net`
   - Username: Your FTP username
   - Password: Your FTP password
   - Port: 21

3. Navigate to `/htdocs/` folder on the server
4. Upload all your project files to this directory

## Step 4: Set Up Database

### 4.1 Import Database Structure
1. Access phpMyAdmin through your InfinityFree control panel
2. Select your database
3. Go to "Import" tab
4. Upload your `isioloraha NEW.sql` file
5. Click "Go" to import

### 4.2 Alternative: Run Database Initialization
1. After uploading files, visit: `https://your-subdomain.infinityfreeapp.com/config/init_db.php`
2. This will create all necessary tables
3. Delete or rename this file after running for security

## Step 5: Configure for Production

### 5.1 Update Paystack Configuration
In `config/paystack_config.php`, ensure you're using live keys for production:

```php
// Use environment detection
$isProduction = !in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);

if ($isProduction) {
    define('PAYSTACK_PUBLIC_KEY', 'pk_live_your_live_public_key');
    define('PAYSTACK_SECRET_KEY', 'sk_live_your_live_secret_key');
} else {
    define('PAYSTACK_PUBLIC_KEY', 'pk_test_c7f8306f56b3fe44259a7e8d8a025c3f69e8102b');
    define('PAYSTACK_SECRET_KEY', 'sk_test_36c2a669d1feb76b51dd0bff57eccdfebea18350');
}
```

### 5.2 Security Considerations
1. Change default admin password immediately
2. Remove or secure test files
3. Ensure sensitive files are protected by .htaccess
4. Enable HTTPS (InfinityFree provides free SSL)

## Step 6: Test Your Deployment

### 6.1 Basic Functionality Test
1. Visit your website: `https://your-subdomain.infinityfreeapp.com`
2. Test user registration and login
3. Test booking process
4. Test admin panel access
5. Test payment integration (use test mode first)

### 6.2 Common Issues and Solutions

**Issue: Database connection errors**
- Solution: Double-check database credentials in `database_production.php`
- Ensure database exists and is accessible

**Issue: File permission errors**
- Solution: InfinityFree has specific file permission requirements
- Most files should be 644, directories should be 755

**Issue: PHP errors**
- Solution: Check PHP version compatibility
- InfinityFree supports PHP 7.4 and 8.x

**Issue: Missing dependencies**
- Solution: Ensure `vendor/` folder is uploaded with Composer dependencies

## Step 7: Domain Configuration (Optional)

### 7.1 Custom Domain
If you have a custom domain:
1. In InfinityFree control panel, go to "Subdomains"
2. Add your custom domain
3. Update DNS records as instructed
4. Update `APP_URL` in your config files

## Step 8: Maintenance and Updates

### 8.1 Regular Backups
- Export database regularly via phpMyAdmin
- Download files via FTP periodically

### 8.2 Updates
- Test updates locally first
- Upload changed files via FTP
- Run any necessary database migrations

## InfinityFree Limitations to Consider

1. **File Size**: Maximum 10MB per file
2. **Database Size**: 400MB limit
3. **Bandwidth**: 5GB per month
4. **CPU Usage**: Limited processing time
5. **Email**: Limited email sending capabilities
6. **Cron Jobs**: Not available on free plan

## Support and Troubleshooting

- InfinityFree Knowledge Base: https://infinityfree.net/support
- Community Forum: Available in control panel
- Check server status: https://status.infinityfree.net

## Security Checklist

- [ ] Changed default admin credentials
- [ ] Removed test files from production
- [ ] Updated Paystack to live keys
- [ ] Enabled HTTPS
- [ ] Protected sensitive directories
- [ ] Disabled error display in production
- [ ] Regular database backups scheduled

---

**Note**: This guide assumes you're using the free InfinityFree plan. Some features may require upgrading to a premium plan.

**Important**: Always test thoroughly in a staging environment before deploying to production.