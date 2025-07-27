<?php
/**
 * Test script for forgot password functionality
 * This script tests the two-step forgot password process
 */

// Include session configuration
require_once 'config/session_config.php';

// Start session
session_start();

// Include database connection
$conn = require_once 'config/database.php';

// Include functions
require_once 'includes/functions.php';

echo "<h1>Forgot Password Test</h1>";

// Test 1: Check if a test user exists
$test_email = "test@example.com";
$sql = "SELECT id, email, password FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $test_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<p><strong>Test Setup:</strong> Creating test user...</p>";
    
    // Create test user
    $insert_sql = "INSERT INTO users (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $first_name = "Test";
    $last_name = "User";
    $phone = "+254700000000";
    $password = password_hash("oldpassword123", PASSWORD_BCRYPT, ["cost" => 10]);
    
    $insert_stmt->bind_param("sssss", $first_name, $last_name, $test_email, $phone, $password);
    
    if ($insert_stmt->execute()) {
        echo "<p>✅ Test user created successfully!</p>";
    } else {
        echo "<p>❌ Failed to create test user</p>";
        exit();
    }
    $insert_stmt->close();
} else {
    echo "<p>✅ Test user already exists</p>";
}

// Test 2: Simulate Step 1 - Email verification
echo "<h2>Step 1: Email Verification</h2>";
$_SESSION['reset_email'] = $test_email;
echo "<p>✅ Email stored in session: " . $_SESSION['reset_email'] . "</p>";

// Test 3: Simulate Step 2 - Password reset
echo "<h2>Step 2: Password Reset</h2>";
$new_password = "newpassword123";
$hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ["cost" => 10]);

$update_sql = "UPDATE users SET password = ? WHERE email = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ss", $hashed_password, $_SESSION['reset_email']);

if ($update_stmt->execute()) {
    echo "<p>✅ Password updated successfully!</p>";
    
    // Test password verification
    $verify_sql = "SELECT password FROM users WHERE email = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("s", $test_email);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $user_data = $verify_result->fetch_assoc();
    
    if (password_verify($new_password, $user_data['password'])) {
        echo "<p>✅ Password verification successful!</p>";
    } else {
        echo "<p>❌ Password verification failed!</p>";
    }
    $verify_stmt->close();
} else {
    echo "<p>❌ Failed to update password</p>";
}

// Clean up
unset($_SESSION['reset_email']);
echo "<p>✅ Session data cleared</p>";

echo "<h2>Test Complete!</h2>";
echo "<p><a href='forgot_password.php'>Test the actual forgot password page</a></p>";
echo "<p><a href='login.php'>Try logging in with: test@example.com / newpassword123</a></p>";

$stmt->close();
$update_stmt->close();
$conn->close();
?>
