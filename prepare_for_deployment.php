<?php
/**
 * Deployment Preparation Script
 * 
 * This script helps prepare your Isiolo Raha project for InfinityFree deployment
 * Run this script locally before uploading to production
 */

echo "<h1>Isiolo Raha - InfinityFree Deployment Preparation</h1>";

// Check if running locally
if (in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])) {
    echo "<p style='color: green;'>✓ Running on local environment - safe to proceed</p>";
} else {
    echo "<p style='color: red;'>✗ This script should only be run locally for security reasons</p>";
    exit();
}

echo "<h2>Deployment Preparation Checklist</h2>";

// Check required files
$requiredFiles = [
    'config/database_production.php' => 'Production database configuration',
    'config/paystack_production.php' => 'Production Paystack configuration',
    'logs/.htaccess' => 'Logs directory protection',
    'INFINITYFREE_DEPLOYMENT_GUIDE.md' => 'Deployment guide',
    'DEPLOYMENT_CHECKLIST.md' => 'Deployment checklist'
];

echo "<h3>Required Files Check</h3>";
foreach ($requiredFiles as $file => $description) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ {$file} - {$description}</p>";
    } else {
        echo "<p style='color: red;'>✗ {$file} - {$description} (MISSING)</p>";
    }
}

// Check configuration
echo "<h3>Configuration Check</h3>";

// Check if production database config has been updated
if (file_exists('config/database_production.php')) {
    $prodDbContent = file_get_contents('config/database_production.php');
    if (strpos($prodDbContent, 'if0_12345678') !== false) {
        echo "<p style='color: orange;'>⚠ database_production.php contains placeholder values - update with your InfinityFree credentials</p>";
    } else {
        echo "<p style='color: green;'>✓ database_production.php appears to be configured</p>";
    }
}

// Check if production Paystack config has been updated
if (file_exists('config/paystack_production.php')) {
    $prodPaystackContent = file_get_contents('config/paystack_production.php');
    if (strpos($prodPaystackContent, 'your_live_public_key_here') !== false) {
        echo "<p style='color: orange;'>⚠ paystack_production.php contains placeholder values - update with your live Paystack keys when ready</p>";
    } else {
        echo "<p style='color: green;'>✓ paystack_production.php appears to be configured</p>";
    }
}

// Check Composer dependencies
echo "<h3>Dependencies Check</h3>";
if (file_exists('vendor/autoload.php')) {
    echo "<p style='color: green;'>✓ Composer dependencies are installed</p>";
} else {
    echo "<p style='color: red;'>✗ Composer dependencies not found - run 'composer install --no-dev --optimize-autoloader'</p>";
}

// Check for development files that should be removed
echo "<h3>Development Files Check</h3>";
$devFiles = [
    '.git',
    'node_modules',
    '.env.local',
    'test_*.php',
    'debug_*.php'
];

$foundDevFiles = [];
foreach ($devFiles as $pattern) {
    $matches = glob($pattern);
    if (!empty($matches)) {
        $foundDevFiles = array_merge($foundDevFiles, $matches);
    }
}

if (empty($foundDevFiles)) {
    echo "<p style='color: green;'>✓ No development files found</p>";
} else {
    echo "<p style='color: orange;'>⚠ Found development files that should be removed before deployment:</p>";
    foreach ($foundDevFiles as $file) {
        echo "<p style='margin-left: 20px;'>- {$file}</p>";
    }
}

// Security checks
echo "<h3>Security Check</h3>";

// Check .htaccess protection
if (file_exists('.htaccess')) {
    $htaccessContent = file_get_contents('.htaccess');
    if (strpos($htaccessContent, 'config\.|database\.php') !== false) {
        echo "<p style='color: green;'>✓ .htaccess contains config file protection</p>";
    } else {
        echo "<p style='color: orange;'>⚠ .htaccess may not have adequate config file protection</p>";
    }
} else {
    echo "<p style='color: red;'>✗ .htaccess file not found</p>";
}

// Check logs directory protection
if (file_exists('logs/.htaccess')) {
    echo "<p style='color: green;'>✓ Logs directory is protected</p>";
} else {
    echo "<p style='color: red;'>✗ Logs directory protection missing</p>";
}

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li>Review and address any issues listed above</li>";
echo "<li>Update database_production.php with your InfinityFree database credentials</li>";
echo "<li>Update paystack_production.php with your live Paystack keys (when ready)</li>";
echo "<li>Run 'composer install --no-dev --optimize-autoloader' if not done</li>";
echo "<li>Remove any development files listed above</li>";
echo "<li>Follow the INFINITYFREE_DEPLOYMENT_GUIDE.md for complete deployment instructions</li>";
echo "<li>Use DEPLOYMENT_CHECKLIST.md during deployment</li>";
echo "</ol>";

echo "<h2>Important Reminders</h2>";
echo "<ul>";
echo "<li><strong>Backup your local database</strong> before deployment</li>";
echo "<li><strong>Test thoroughly</strong> on InfinityFree before going live</li>";
echo "<li><strong>Change default admin password</strong> immediately after deployment</li>";
echo "<li><strong>Use test Paystack keys</strong> initially, switch to live keys only when ready</li>";
echo "<li><strong>Delete this script</strong> after deployment for security</li>";
echo "</ul>";

echo "<p style='background: #f0f0f0; padding: 10px; border-left: 4px solid #007cba;'>";
echo "<strong>Ready for deployment?</strong> Follow the step-by-step guide in INFINITYFREE_DEPLOYMENT_GUIDE.md";
echo "</p>";

?>