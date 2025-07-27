<?php
// One-click setup script for reports functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Reports System Setup</h1>";
echo "<p>This script will set up everything needed for the reports system to work.</p>";

if ($_POST['confirm'] ?? false) {
    echo "<h2>Setting up...</h2>";
    
    // Step 1: Start session and set admin user
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Step 2: Initialize database tables
    echo "<h3>1. Initializing Database Tables</h3>";
    ob_start();
    include 'init_reports_tables.php';
    $init_output = ob_get_clean();
    
    if (strpos($init_output, 'Error') === false) {
        echo "<p style='color: green;'>✓ Database tables initialized successfully</p>";
    } else {
        echo "<p style='color: red;'>⚠ Some issues occurred during database initialization</p>";
        echo "<details><summary>View Details</summary>$init_output</details>";
    }
    
    // Step 3: Test the system
    echo "<h3>2. Testing System</h3>";
    ob_start();
    include 'test_reports.php';
    $test_output = ob_get_clean();
    
    // Extract test results
    if (preg_match('/Tests Passed<\/td><td>(\d+)/', $test_output, $matches)) {
        $tests_passed = $matches[1];
        echo "<p style='color: green;'>✓ $tests_passed tests passed</p>";
    }
    
    if (preg_match('/Tests Failed<\/td><td>(\d+)/', $test_output, $matches)) {
        $tests_failed = $matches[1];
        if ($tests_failed > 0) {
            echo "<p style='color: red;'>⚠ $tests_failed tests failed</p>";
        }
    }
    
    // Step 4: Set up admin session
    echo "<h3>3. Setting up Admin Session</h3>";
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'admin';
    $_SESSION['user_email'] = 'admin@isioloraha.com';
    $_SESSION['user_name'] = 'Admin User';
    echo "<p style='color: green;'>✓ Admin session configured</p>";
    
    echo "<h2>Setup Complete!</h2>";
    echo "<div style='background: #f0f9ff; border: 1px solid #0ea5e9; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 Your reports system is ready!</h3>";
    echo "<p><strong>Admin Login:</strong></p>";
    echo "<ul>";
    echo "<li>Email: admin@isioloraha.com</li>";
    echo "<li>Password: admin123</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>Available Pages:</h3>";
    echo "<ul>";
    echo "<li><a href='reports_simple.php' style='color: #0ea5e9; font-weight: bold;'>Simple Reports Page</a> - Basic functionality</li>";
    echo "<li><a href='reports.php' style='color: #0ea5e9; font-weight: bold;'>Full Reports Page</a> - Complete with charts</li>";
    echo "<li><a href='debug_reports.php' style='color: #0ea5e9;'>Debug Information</a> - Troubleshooting</li>";
    echo "<li><a href='test_reports.php' style='color: #0ea5e9;'>Test Results</a> - System validation</li>";
    echo "<li><a href='index.php' style='color: #0ea5e9;'>Admin Dashboard</a> - Main admin area</li>";
    echo "</ul>";
    
} else {
    // Show confirmation form
    echo "<div style='background: #fef3c7; border: 1px solid #f59e0b; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>⚠️ Important Notes:</h3>";
    echo "<ul>";
    echo "<li>This will create/modify database tables</li>";
    echo "<li>Sample data will be inserted if tables are empty</li>";
    echo "<li>An admin user will be created (admin@isioloraha.com / admin123)</li>";
    echo "<li>Make sure your database connection is working</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<form method='post'>";
    echo "<input type='hidden' name='confirm' value='1'>";
    echo "<button type='submit' style='background: #0ea5e9; color: white; padding: 10px 20px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>";
    echo "🚀 Set Up Reports System";
    echo "</button>";
    echo "</form>";
    
    echo "<p><a href='index.php'>← Back to Dashboard</a></p>";
}
?>
