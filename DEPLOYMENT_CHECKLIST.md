# InfinityFree Deployment Checklist

## Pre-Deployment Preparation

### 1. InfinityFree Account Setup
- [ ] Created InfinityFree account at infinityfree.net
- [ ] Chosen subdomain (e.g., yourproject.infinityfreeapp.com)
- [ ] Noted FTP credentials from control panel
- [ ] Created MySQL database in control panel
- [ ] Noted database credentials (hostname, username, password, database name)

### 2. Update Configuration Files

#### Database Configuration
- [ ] Updated `config/database_production.php` with InfinityFree database credentials:
  - [ ] DB_HOST (e.g., sql200.infinityfree.com)
  - [ ] DB_USER (e.g., if0_12345678)
  - [ ] DB_PASS (your database password)
  - [ ] DB_NAME (e.g., if0_12345678_isioloraha)

#### Paystack Configuration (if using live payments)
- [ ] Updated `config/paystack_production.php` with live Paystack keys:
  - [ ] PAYSTACK_PUBLIC_KEY (pk_live_...)
  - [ ] PAYSTACK_SECRET_KEY (sk_live_...)
  - [ ] PAYSTACK_WEBHOOK_SECRET

### 3. File Preparation
- [ ] Installed Composer dependencies locally: `composer install --no-dev --optimize-autoloader`
- [ ] Removed development-only files:
  - [ ] `.git/` folder
  - [ ] Local database dumps
  - [ ] Test files (if any)
- [ ] Verified all file permissions are correct

## Deployment Process

### 4. File Upload
- [ ] Connected to InfinityFree FTP (ftpupload.net, port 21)
- [ ] Navigated to `/htdocs/` directory on server
- [ ] Uploaded all project files to `/htdocs/`
- [ ] Verified file structure is correct on server

### 5. Database Setup
- [ ] Accessed phpMyAdmin through InfinityFree control panel
- [ ] Selected your database
- [ ] Imported `isioloraha NEW.sql` file OR
- [ ] Ran database initialization by visiting: `https://your-subdomain.infinityfreeapp.com/config/init_db.php`
- [ ] Verified all tables were created successfully
- [ ] **IMPORTANT**: Deleted or renamed `init_db.php` after running (security)

## Post-Deployment Testing

### 6. Basic Functionality Tests
- [ ] Website loads: `https://your-subdomain.infinityfreeapp.com`
- [ ] Database connection works (no connection errors)
- [ ] User registration works
- [ ] User login works
- [ ] Admin login works (admin@isioloraha.com / admin123)
- [ ] Booking process works
- [ ] Payment integration works (test mode first)

### 7. Admin Panel Tests
- [ ] Admin dashboard accessible
- [ ] Can view bookings
- [ ] Can manage buses and routes
- [ ] Can view reports
- [ ] Can manage users

### 8. Security Checks
- [ ] Changed default admin password
- [ ] HTTPS is working (green lock icon)
- [ ] Sensitive files are protected (try accessing `/config/database_production.php`)
- [ ] Error messages don't reveal sensitive information
- [ ] File uploads work correctly (if applicable)

## Production Configuration

### 9. Final Production Settings
- [ ] Updated Paystack to live keys (if ready for live payments)
- [ ] Configured custom domain (if applicable)
- [ ] Set up regular database backups
- [ ] Configured email settings (if using email features)
- [ ] Tested all payment flows with small amounts

### 10. Monitoring Setup
- [ ] Set up error monitoring
- [ ] Configured log file monitoring
- [ ] Set up uptime monitoring (optional)
- [ ] Documented backup procedures

## Troubleshooting Common Issues

### Database Connection Issues
- [ ] Verified database credentials in `database_production.php`
- [ ] Checked that database exists and is accessible
- [ ] Ensured database user has proper permissions

### File Permission Issues
- [ ] Set files to 644 permissions
- [ ] Set directories to 755 permissions
- [ ] Verified `.htaccess` files are uploaded

### SSL/HTTPS Issues
- [ ] Verified InfinityFree SSL is enabled
- [ ] Checked that HTTPS redirect is working
- [ ] Updated all internal links to use HTTPS

### Payment Issues
- [ ] Verified Paystack keys are correct
- [ ] Checked webhook URLs are accessible
- [ ] Tested with small amounts first

## Post-Launch Maintenance

### 11. Regular Tasks
- [ ] Weekly database backups
- [ ] Monthly file backups
- [ ] Monitor error logs
- [ ] Update dependencies as needed
- [ ] Monitor site performance

### 12. Documentation
- [ ] Document any custom configurations
- [ ] Keep record of database credentials
- [ ] Document backup procedures
- [ ] Create user manual (if needed)

## Emergency Contacts

- InfinityFree Support: Available through control panel
- Paystack Support: support@paystack.com
- Domain Provider: (if using custom domain)

## Notes

_Add any specific notes about your deployment here:_

---

**Deployment Date**: _______________
**Deployed By**: _______________
**Live URL**: _______________
**Database**: _______________
**Admin Credentials Changed**: [ ] Yes [ ] No