<?php
/**
 * Database Import Script
 * 
 * This script imports the SQL file into your database
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'isioloraha');

// Path to SQL file
$sqlFile = 'isioloraha.sql';

// Check if file exists
if (!file_exists($sqlFile)) {
    die("Error: SQL file not found at $sqlFile");
}

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Starting database import...</h2>";

// Read SQL file
$sql = file_get_contents($sqlFile);

// Execute multi query
if ($conn->multi_query($sql)) {
    echo "<p>Database imported successfully!</p>";
    
    // Process all result sets
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    
    if ($conn->error) {
        echo "<p>Error: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Error importing database: " . $conn->error . "</p>";
}

// Close connection
$conn->close();

echo "<h2>Import completed!</h2>";
echo "<p>You can now <a href='index.php'>go to the homepage</a> and start using the system.</p>";
echo "<p>Login with the following credentials:</p>";
echo "<ul>";
echo "<li>Email: admin@isioloraha.com</li>";
echo "<li>Password: admin123</li>";
echo "</ul>";
?>
