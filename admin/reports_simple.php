<?php
// Simplified Reports Page - Core functionality only
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration and functions
require_once '../config/config.php';
require_once '../includes/functions.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database connection
try {
    $conn = require_once '../config/database.php';
    if (!$conn) throw new Exception("Database connection failed");
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

// Get parameters
$report_type = $_GET['type'] ?? 'daily';
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Initialize variables
$report_data = [];
$total_bookings = 0;
$total_revenue = 0;
$total_cancelled = 0;

// Simple query for daily reports
$sql = "SELECT
        DATE(b.created_at) AS booking_date,
        COUNT(*) AS num_bookings,
        SUM(CASE WHEN b.status = 'confirmed' THEN b.amount ELSE 0 END) AS revenue,
        SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_bookings,
        COUNT(DISTINCT b.user_id) AS unique_customers
        FROM bookings b
        WHERE DATE(b.created_at) BETWEEN ? AND ?
        GROUP BY DATE(b.created_at)
        ORDER BY booking_date DESC";

// Execute query
try {
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ss", $date_from, $date_to);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $report_data[] = $row;
            $total_bookings += $row['num_bookings'];
            $total_revenue += $row['revenue'];
            $total_cancelled += $row['cancelled_bookings'];
        }
        $stmt->close();
    }
} catch (Exception $e) {
    $error_message = "Query Error: " . $e->getMessage();
}

// Include header
require_once '../includes/templates/admin_header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Reports & Analytics</h1>
        <p class="text-gray-600 mt-2">View booking statistics and performance metrics</p>
    </div>

    <!-- Error Display -->
    <?php if (isset($error_message)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
        <br><a href="debug_reports.php" class="underline">Run Debug Test</a>
    </div>
    <?php endif; ?>

    <!-- Report Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Report Filters</h2>
        <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="daily" <?php echo $report_type === 'daily' ? 'selected' : ''; ?>>Daily Report</option>
                    <option value="weekly" <?php echo $report_type === 'weekly' ? 'selected' : ''; ?>>Weekly Report</option>
                    <option value="monthly" <?php echo $report_type === 'monthly' ? 'selected' : ''; ?>>Monthly Report</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                <input type="date" name="date_from" value="<?php echo $date_from; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                <input type="date" name="date_to" value="<?php echo $date_to; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    Generate Report
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                    <i class="fas fa-ticket-alt text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Total Bookings</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $total_bookings; ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Total Revenue</p>
                    <p class="text-2xl font-bold text-gray-800">
                        <?php echo function_exists('formatCurrency') ? formatCurrency($total_revenue) : 'KES ' . number_format($total_revenue, 2); ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
                    <i class="fas fa-times-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm">Cancelled Bookings</p>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $total_cancelled; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Data Table -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">Report Data</h2>
        
        <?php if (empty($report_data)): ?>
        <div class="text-center py-8">
            <div class="text-gray-400 text-4xl mb-4">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-600 mb-2">No Data Available</h3>
            <p class="text-gray-500">No booking data found for the selected period.</p>
            <div class="mt-4">
                <a href="init_reports_tables.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    Initialize Sample Data
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bookings</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cancelled</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customers</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($report_data as $row): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo date('d M, Y', strtotime($row['booking_date'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo $row['num_bookings']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo function_exists('formatCurrency') ? formatCurrency($row['revenue']) : 'KES ' . number_format($row['revenue'], 2); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo $row['cancelled_bookings']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo $row['unique_customers']; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Navigation Links -->
    <div class="mt-6 text-center">
        <a href="index.php" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors mr-2">
            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
        </a>
        <a href="debug_reports.php" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition-colors mr-2">
            <i class="fas fa-bug mr-2"></i>Debug Test
        </a>
        <a href="reports.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
            <i class="fas fa-chart-line mr-2"></i>Full Reports
        </a>
    </div>
</div>

<?php require_once '../includes/templates/admin_footer.php'; ?>
