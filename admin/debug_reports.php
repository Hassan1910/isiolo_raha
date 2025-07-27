<?php
// Debug version of reports.php to identify issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Reports Page Debug Information</h1>";

// Test 1: Session Check
echo "<h2>1. Session Status</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "✓ Session started<br>";
} else {
    echo "✓ Session already active<br>";
}

echo "Session ID: " . session_id() . "<br>";
echo "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . "<br>";
echo "User Role: " . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'NOT SET') . "<br>";

// Test 2: File Includes
echo "<h2>2. File Includes</h2>";
try {
    require_once '../includes/functions.php';
    echo "✓ functions.php loaded<br>";
} catch (Exception $e) {
    echo "✗ Error loading functions.php: " . $e->getMessage() . "<br>";
}

try {
    require_once '../config/config.php';
    echo "✓ config.php loaded<br>";
    echo "APP_URL: " . (defined('APP_URL') ? APP_URL : 'NOT DEFINED') . "<br>";
    echo "APP_NAME: " . (defined('APP_NAME') ? APP_NAME : 'NOT DEFINED') . "<br>";
} catch (Exception $e) {
    echo "✗ Error loading config.php: " . $e->getMessage() . "<br>";
}

// Test 3: Database Connection
echo "<h2>3. Database Connection</h2>";
try {
    $conn = require_once '../config/database.php';
    if ($conn && $conn->ping()) {
        echo "✓ Database connection successful<br>";
        echo "Database: " . $conn->get_server_info() . "<br>";
    } else {
        echo "✗ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
}

// Test 4: Table Existence
echo "<h2>4. Database Tables</h2>";
if (isset($conn) && $conn) {
    $tables = ['users', 'bookings', 'schedules', 'routes', 'buses', 'payments'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "✓ Table '$table' exists<br>";
            
            // Check table structure for key tables
            if (in_array($table, ['bookings', 'schedules', 'routes', 'buses'])) {
                $structure = $conn->query("DESCRIBE $table");
                if ($structure) {
                    echo "&nbsp;&nbsp;Columns: ";
                    $columns = [];
                    while ($row = $structure->fetch_assoc()) {
                        $columns[] = $row['Field'];
                    }
                    echo implode(', ', $columns) . "<br>";
                }
            }
        } else {
            echo "✗ Table '$table' does not exist<br>";
        }
    }
}

// Test 5: Sample Data
echo "<h2>5. Sample Data Check</h2>";
if (isset($conn) && $conn) {
    $queries = [
        'users' => "SELECT COUNT(*) as count FROM users",
        'bookings' => "SELECT COUNT(*) as count FROM bookings",
        'schedules' => "SELECT COUNT(*) as count FROM schedules",
        'routes' => "SELECT COUNT(*) as count FROM routes",
        'buses' => "SELECT COUNT(*) as count FROM buses"
    ];
    
    foreach ($queries as $table => $query) {
        try {
            $result = $conn->query($query);
            if ($result) {
                $row = $result->fetch_assoc();
                echo "✓ $table: " . $row['count'] . " records<br>";
            }
        } catch (Exception $e) {
            echo "✗ Error querying $table: " . $e->getMessage() . "<br>";
        }
    }
}

// Test 6: Reports Query Test
echo "<h2>6. Reports Query Test</h2>";
if (isset($conn) && $conn) {
    $date_from = date('Y-m-d', strtotime('-7 days'));
    $date_to = date('Y-m-d');
    
    echo "Testing date range: $date_from to $date_to<br>";
    
    $test_query = "SELECT
                    DATE(b.created_at) AS booking_date,
                    COUNT(*) AS num_bookings,
                    SUM(CASE WHEN b.status = 'confirmed' THEN b.amount ELSE 0 END) AS revenue,
                    SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_bookings,
                    COUNT(DISTINCT b.user_id) AS unique_customers
                    FROM bookings b
                    WHERE DATE(b.created_at) BETWEEN ? AND ?
                    GROUP BY DATE(b.created_at)
                    ORDER BY booking_date DESC";
    
    try {
        $stmt = $conn->prepare($test_query);
        if ($stmt) {
            $stmt->bind_param("ss", $date_from, $date_to);
            $stmt->execute();
            $result = $stmt->get_result();
            echo "✓ Query executed successfully<br>";
            echo "✓ Found " . $result->num_rows . " result rows<br>";
            
            if ($result->num_rows > 0) {
                echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
                echo "<tr><th>Date</th><th>Bookings</th><th>Revenue</th><th>Cancelled</th><th>Customers</th></tr>";
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['booking_date'] . "</td>";
                    echo "<td>" . $row['num_bookings'] . "</td>";
                    echo "<td>" . $row['revenue'] . "</td>";
                    echo "<td>" . $row['cancelled_bookings'] . "</td>";
                    echo "<td>" . $row['unique_customers'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            $stmt->close();
        } else {
            echo "✗ Failed to prepare query: " . $conn->error . "<br>";
        }
    } catch (Exception $e) {
        echo "✗ Query error: " . $e->getMessage() . "<br>";
    }
}

// Test 7: Template System
echo "<h2>7. Template System Test</h2>";
if (file_exists('../includes/templates/admin_template.php')) {
    echo "✓ admin_template.php exists<br>";
} else {
    echo "✗ admin_template.php not found<br>";
}

if (file_exists('../includes/templates/admin_header.php')) {
    echo "✓ admin_header.php exists<br>";
} else {
    echo "✗ admin_header.php not found<br>";
}

if (file_exists('../includes/templates/admin_sidebar.php')) {
    echo "✓ admin_sidebar.php exists<br>";
} else {
    echo "✗ admin_sidebar.php not found<br>";
}

// Test 8: Function Availability
echo "<h2>8. Function Availability</h2>";
$functions = ['formatCurrency', 'logActivity', 'setFlashMessage', 'getFlashMessage'];
foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "✓ Function '$func' available<br>";
    } else {
        echo "✗ Function '$func' not available<br>";
    }
}

echo "<h2>Debug Complete</h2>";
echo "<p><a href='reports.php'>Try Reports Page</a> | <a href='index.php'>Back to Dashboard</a></p>";
?>
