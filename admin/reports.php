<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration first
require_once '../config/config.php';

// Include functions
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Set message
    if (function_exists('setFlashMessage')) {
        setFlashMessage("error", "You do not have permission to access the admin dashboard.");
    }

    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Set page title
$page_title = "Reports & Analytics";

// Include database connection with error handling
try {
    $conn = require_once '../config/database.php';
    if (!$conn || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn ? $conn->connect_error : "Connection object is null"));
    }
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage() . "<br><a href='debug_reports.php'>Run Debug</a>");
}

// Initialize variables
$report_type = isset($_GET['type']) ? $_GET['type'] : 'daily';
// Set default date range to last 30 days to show actual data
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

// For weekly and monthly reports, adjust the date range if not explicitly set
if (!isset($_GET['date_from']) && !isset($_GET['date_to'])) {
    if ($report_type === 'weekly') {
        // Set date range to current week
        $date_from = date('Y-m-d', strtotime('monday this week'));
        $date_to = date('Y-m-d', strtotime('sunday this week'));
    } elseif ($report_type === 'monthly') {
        // Set date range to current month
        $date_from = date('Y-m-01'); // First day of current month
        $date_to = date('Y-m-t'); // Last day of current month
    }
}

// Generate report based on type
$report_data = [];
$total_bookings = 0;
$total_revenue = 0;
$total_cancelled = 0;

// SQL query based on report type
$sql = "";
switch ($report_type) {
    case 'daily':
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
        break;
    case 'weekly':
        $sql = "SELECT
                YEARWEEK(b.created_at, 1) AS booking_week,
                MIN(DATE(b.created_at)) AS week_start,
                MAX(DATE(b.created_at)) AS week_end,
                COUNT(*) AS num_bookings,
                SUM(CASE WHEN b.status = 'confirmed' THEN b.amount ELSE 0 END) AS revenue,
                SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_bookings,
                COUNT(DISTINCT b.user_id) AS unique_customers
                FROM bookings b
                WHERE DATE(b.created_at) BETWEEN ? AND ?
                GROUP BY YEARWEEK(b.created_at, 1)
                ORDER BY booking_week DESC";
        break;
    case 'monthly':
        $sql = "SELECT
                DATE_FORMAT(b.created_at, '%Y-%m') AS booking_month,
                MIN(DATE(b.created_at)) AS month_start,
                MAX(DATE(b.created_at)) AS month_end,
                COUNT(*) AS num_bookings,
                SUM(CASE WHEN b.status = 'confirmed' THEN b.amount ELSE 0 END) AS revenue,
                SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_bookings,
                COUNT(DISTINCT b.user_id) AS unique_customers
                FROM bookings b
                WHERE DATE(b.created_at) BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(b.created_at, '%Y-%m')
                ORDER BY booking_month DESC";
        break;
    case 'route':
        $sql = "SELECT
                r.origin,
                r.destination,
                COUNT(*) AS num_bookings,
                SUM(CASE WHEN b.status = 'confirmed' THEN b.amount ELSE 0 END) AS revenue,
                SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_bookings,
                COUNT(DISTINCT b.user_id) AS unique_customers
                FROM bookings b
                JOIN schedules s ON b.schedule_id = s.id
                JOIN routes r ON s.route_id = r.id
                WHERE DATE(b.created_at) BETWEEN ? AND ?
                GROUP BY r.id
                ORDER BY num_bookings DESC";
        break;
    case 'bus':
        $sql = "SELECT
                bs.name AS bus_name,
                bs.registration_number,
                COUNT(*) AS num_bookings,
                SUM(CASE WHEN b.status = 'confirmed' THEN b.amount ELSE 0 END) AS revenue,
                SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_bookings,
                COUNT(DISTINCT b.user_id) AS unique_customers
                FROM bookings b
                JOIN schedules s ON b.schedule_id = s.id
                JOIN buses bs ON s.bus_id = bs.id
                WHERE DATE(b.created_at) BETWEEN ? AND ?
                GROUP BY bs.id
                ORDER BY num_bookings DESC";
        break;
}

// Execute query with error handling
try {
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $date_from, $date_to);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $report_data[] = $row;

                // Calculate totals
                $total_bookings += $row['num_bookings'];
                $total_revenue += $row['revenue'];
                $total_cancelled += $row['cancelled_bookings'];
            }
        }

        $stmt->close();
    } else {
        throw new Exception("Failed to prepare main query: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Reports Query Error: " . $e->getMessage());
    // Continue with empty data rather than crashing
}

// Get top 5 routes for the selected period
$top_routes_sql = "SELECT
                  r.origin,
                  r.destination,
                  COUNT(*) AS num_bookings,
                  SUM(CASE WHEN b.status = 'confirmed' THEN b.amount ELSE 0 END) AS revenue
                  FROM bookings b
                  JOIN schedules s ON b.schedule_id = s.id
                  JOIN routes r ON s.route_id = r.id
                  WHERE DATE(b.created_at) BETWEEN ? AND ?
                  GROUP BY r.id
                  ORDER BY num_bookings DESC
                  LIMIT 5";

$top_routes = [];
try {
    if ($stmt = $conn->prepare($top_routes_sql)) {
        $stmt->bind_param("ss", $date_from, $date_to);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $top_routes[] = $row;
            }
        }

        $stmt->close();
    } else {
        throw new Exception("Failed to prepare top routes query: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Top Routes Query Error: " . $e->getMessage());
    // Continue with empty data rather than crashing
}

// Log activity (with error handling)
try {
    if (function_exists('logActivity')) {
        logActivity("Reports", "Generated " . $report_type . " report for period " . $date_from . " to " . $date_to);
    }
} catch (Exception $e) {
    error_log("Activity logging error: " . $e->getMessage());
}

// Initialize admin content variable
$admin_content = '';

// Start output buffering to capture content for the template
ob_start();
?>
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Reports & Analytics</h1>
    <a href="index.php" class="btn-secondary no-print">
        <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
    </a>
</div>

<!-- Report Filters -->
<div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-gray-100 hover:shadow-lg transition-all duration-300">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-filter text-primary-600 mr-2"></i> Report Filters
        </h2>
        <div class="flex space-x-2">
            <button type="button" id="last7days" class="px-3 py-1 text-xs font-medium rounded-full border border-gray-200 hover:bg-gray-50 transition-colors">Last 7 Days</button>
            <button type="button" id="last30days" class="px-3 py-1 text-xs font-medium rounded-full border border-gray-200 hover:bg-gray-50 transition-colors">Last 30 Days</button>
            <button type="button" id="thisMonth" class="px-3 py-1 text-xs font-medium rounded-full border border-gray-200 hover:bg-gray-50 transition-colors">This Month</button>
        </div>
    </div>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" id="reportForm" class="space-y-4 md:space-y-0 md:grid md:grid-cols-12 md:gap-4">
        <!-- Report Type -->
        <div class="md:col-span-3">
            <label for="type" class="form-label flex items-center">
                <i class="fas fa-chart-pie text-primary-500 mr-2"></i> Report Type
            </label>
            <div class="relative">
                <select name="type" id="type" class="form-input pl-10 pr-10 py-3 border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm">
                    <option value="daily" <?php echo $report_type === 'daily' ? 'selected' : ''; ?>>Daily Report</option>
                    <option value="weekly" <?php echo $report_type === 'weekly' ? 'selected' : ''; ?>>Weekly Report</option>
                    <option value="monthly" <?php echo $report_type === 'monthly' ? 'selected' : ''; ?>>Monthly Report</option>
                    <option value="route" <?php echo $report_type === 'route' ? 'selected' : ''; ?>>Route Report</option>
                    <option value="bus" <?php echo $report_type === 'bus' ? 'selected' : ''; ?>>Bus Report</option>
                </select>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-chart-bar text-gray-400"></i>
                </div>
            </div>
        </div>

        <!-- Date From -->
        <div class="md:col-span-3">
            <label for="date_from" class="form-label flex items-center">
                <i class="fas fa-calendar-alt text-primary-500 mr-2"></i> Date From
            </label>
            <div class="relative">
                <input type="date" name="date_from" id="date_from" class="form-input pl-10 py-3 border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm" value="<?php echo $date_from; ?>">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-calendar-day text-gray-400"></i>
                </div>
            </div>
        </div>

        <!-- Date To -->
        <div class="md:col-span-3">
            <label for="date_to" class="form-label flex items-center">
                <i class="fas fa-calendar-alt text-primary-500 mr-2"></i> Date To
            </label>
            <div class="relative">
                <input type="date" name="date_to" id="date_to" class="form-input pl-10 py-3 border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm" value="<?php echo $date_to; ?>">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-calendar-day text-gray-400"></i>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="md:col-span-3 flex items-end">
            <button type="submit" class="w-full btn-primary py-3 px-6 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-medium flex items-center justify-center transition-all duration-300 shadow-md hover:shadow-lg">
                <i class="fas fa-search mr-2"></i> Generate Report
            </button>
        </div>
    </form>

    <!-- Export Options -->
    <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end space-x-2 export-buttons no-print">
        <button type="button" id="exportCSV" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
            <i class="fas fa-file-csv mr-2 text-green-600"></i> Export CSV
        </button>
        <button type="button" id="exportPDF" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
            <i class="fas fa-file-pdf mr-2 text-red-600"></i> Export PDF
        </button>
        <button type="button" id="printReport" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
            <i class="fas fa-print mr-2 text-blue-600"></i> Print
        </button>
    </div>
</div>

<!-- Report Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Total Bookings -->
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-all duration-300 fade-in">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="p-4 rounded-full bg-blue-100 text-blue-600 mr-4">
                    <i class="fas fa-ticket-alt text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Bookings</p>
                    <div class="flex items-baseline">
                        <p class="text-3xl font-bold text-gray-800"><?php echo $total_bookings; ?></p>
                        <?php
                        // This would normally compare with previous period data
                        $trend = 1; // 1 for up, -1 for down, 0 for no change
                        $percent = 12.5; // Example percentage
                        if ($trend > 0):
                        ?>
                            <span class="ml-2 text-xs font-medium text-green-600 flex items-center">
                                <i class="fas fa-arrow-up mr-1"></i> <?php echo $percent; ?>%
                            </span>
                        <?php elseif ($trend < 0): ?>
                            <span class="ml-2 text-xs font-medium text-red-600 flex items-center">
                                <i class="fas fa-arrow-down mr-1"></i> <?php echo abs($percent); ?>%
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center">
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                        <div class="w-8 h-8 rounded-full bg-blue-200"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">From <?php echo date('M d', strtotime($date_from)); ?> to <?php echo date('M d', strtotime($date_to)); ?></span>
                <a href="#" class="text-xs text-blue-600 hover:text-blue-800 font-medium">View Details</a>
            </div>
        </div>
    </div>

    <!-- Total Revenue -->
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-all duration-300 fade-in delay-100">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="p-4 rounded-full bg-green-100 text-green-600 mr-4">
                    <i class="fas fa-money-bill-wave text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Revenue</p>
                    <div class="flex items-baseline">
                        <p class="text-3xl font-bold text-gray-800"><?php echo formatCurrency($total_revenue); ?></p>
                        <?php
                        // This would normally compare with previous period data
                        $trend = 1; // 1 for up, -1 for down, 0 for no change
                        $percent = 8.3; // Example percentage
                        if ($trend > 0):
                        ?>
                            <span class="ml-2 text-xs font-medium text-green-600 flex items-center">
                                <i class="fas fa-arrow-up mr-1"></i> <?php echo $percent; ?>%
                            </span>
                        <?php elseif ($trend < 0): ?>
                            <span class="ml-2 text-xs font-medium text-red-600 flex items-center">
                                <i class="fas fa-arrow-down mr-1"></i> <?php echo abs($percent); ?>%
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="w-16 h-16 rounded-full bg-green-50 flex items-center justify-center">
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                        <div class="w-8 h-8 rounded-full bg-green-200"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">From <?php echo date('M d', strtotime($date_from)); ?> to <?php echo date('M d', strtotime($date_to)); ?></span>
                <a href="#" class="text-xs text-green-600 hover:text-green-800 font-medium">View Details</a>
            </div>
        </div>
    </div>

    <!-- Cancelled Bookings -->
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-all duration-300 fade-in delay-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="p-4 rounded-full bg-red-100 text-red-600 mr-4">
                    <i class="fas fa-times-circle text-2xl"></i>
                </div>
                <div>
                    <p class="text-gray-500 text-sm font-medium">Cancelled Bookings</p>
                    <div class="flex items-baseline">
                        <p class="text-3xl font-bold text-gray-800"><?php echo $total_cancelled; ?></p>
                        <?php
                        // This would normally compare with previous period data
                        $trend = -1; // 1 for up, -1 for down, 0 for no change
                        $percent = 5.2; // Example percentage
                        if ($trend > 0):
                        ?>
                            <span class="ml-2 text-xs font-medium text-red-600 flex items-center">
                                <i class="fas fa-arrow-up mr-1"></i> <?php echo $percent; ?>%
                            </span>
                        <?php elseif ($trend < 0): ?>
                            <span class="ml-2 text-xs font-medium text-green-600 flex items-center">
                                <i class="fas fa-arrow-down mr-1"></i> <?php echo abs($percent); ?>%
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                        <div class="w-8 h-8 rounded-full bg-red-200"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">From <?php echo date('M d', strtotime($date_from)); ?> to <?php echo date('M d', strtotime($date_to)); ?></span>
                <a href="#" class="text-xs text-red-600 hover:text-red-800 font-medium">View Details</a>
            </div>
        </div>
    </div>
</div>

<!-- Report Data -->
<div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-gray-100 hover:shadow-lg transition-all duration-300">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-table text-primary-600 mr-2"></i>
                <?php
                echo ucfirst($report_type) . " Report ";
                echo "<span class='text-gray-500 font-normal ml-2'>(" . date('d M, Y', strtotime($date_from)) . " - " . date('d M, Y', strtotime($date_to)) . ")</span>";
                ?>
            </h2>
            <p class="text-sm text-gray-500 mt-1">Detailed breakdown of booking data for the selected period</p>
        </div>

        <div class="flex items-center space-x-2 no-print">
            <div class="relative">
                <input type="text" id="tableSearch" placeholder="Search..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
            <select id="entriesPerPage" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
                <option value="50">50 rows</option>
                <option value="100">100 rows</option>
            </select>
        </div>
    </div>

    <?php if (empty($report_data)): ?>
        <div class="text-center py-16 bg-gray-50 rounded-lg border border-gray-200">
            <div class="text-gray-400 text-5xl mb-4">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h3 class="text-xl font-medium text-gray-600 mb-2">No Data Available</h3>
            <p class="text-gray-500 max-w-md mx-auto">There is no booking data available for the selected period. Try adjusting your date range or report type.</p>
            <button type="button" class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                <i class="fas fa-sync-alt mr-2"></i> Try Different Filters
            </button>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <?php if ($report_type === 'daily'): ?>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <?php elseif ($report_type === 'weekly'): ?>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Week</th>
                        <?php elseif ($report_type === 'monthly'): ?>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                        <?php elseif ($report_type === 'route'): ?>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                        <?php elseif ($report_type === 'bus'): ?>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bus</th>
                        <?php endif; ?>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bookings</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cancelled</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customers</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($report_data as $index => $row): ?>
                        <tr class="<?php echo $index % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?> hover:bg-gray-100 transition-colors">
                            <?php if ($report_type === 'daily'): ?>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900"><?php echo date('d M, Y', strtotime($row['booking_date'])); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo date('l', strtotime($row['booking_date'])); ?></div>
                                </td>
                            <?php elseif ($report_type === 'weekly'): ?>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900"><?php echo date('d M', strtotime($row['week_start'])) . ' - ' . date('d M, Y', strtotime($row['week_end'])); ?></div>
                                    <div class="text-xs text-gray-500">Week <?php echo date('W', strtotime($row['week_start'])); ?>, <?php echo date('Y', strtotime($row['week_start'])); ?></div>
                                </td>
                            <?php elseif ($report_type === 'monthly'): ?>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900"><?php echo date('M Y', strtotime($row['month_start'] . '-01')); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo date('F', strtotime($row['month_start'] . '-01')); ?></div>
                                </td>
                            <?php elseif ($report_type === 'route'): ?>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900"><?php echo $row['origin'] . ' to ' . $row['destination']; ?></div>
                                    <div class="text-xs text-gray-500">Route ID: <?php echo isset($row['route_id']) ? $row['route_id'] : 'N/A'; ?></div>
                                </td>
                            <?php elseif ($report_type === 'bus'): ?>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900"><?php echo $row['bus_name']; ?></div>
                                    <div class="text-xs text-gray-500"><?php echo $row['registration_number']; ?></div>
                                </td>
                            <?php endif; ?>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $row['num_bookings']; ?></div>
                                <?php
                                // Calculate percentage of total
                                $bookingPercent = ($total_bookings > 0) ? round(($row['num_bookings'] / $total_bookings) * 100) : 0;
                                ?>
                                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: <?php echo $bookingPercent; ?>%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1"><?php echo $bookingPercent; ?>% of total</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo formatCurrency($row['revenue']); ?></div>
                                <?php
                                // Calculate percentage of total
                                $revenuePercent = ($total_revenue > 0) ? round(($row['revenue'] / $total_revenue) * 100) : 0;
                                ?>
                                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                    <div class="bg-green-600 h-1.5 rounded-full" style="width: <?php echo $revenuePercent; ?>%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1"><?php echo $revenuePercent; ?>% of total</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $row['cancelled_bookings']; ?></div>
                                <?php
                                // Calculate cancellation rate
                                $cancellationRate = ($row['num_bookings'] > 0) ? round(($row['cancelled_bookings'] / $row['num_bookings']) * 100) : 0;
                                ?>
                                <div class="text-xs <?php echo $cancellationRate > 20 ? 'text-red-600' : 'text-gray-500'; ?> mt-1">
                                    <?php echo $cancellationRate; ?>% rate
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $row['unique_customers']; ?></div>
                                <?php
                                // Calculate average bookings per customer
                                $avgBookingsPerCustomer = ($row['unique_customers'] > 0) ? round(($row['num_bookings'] / $row['unique_customers']), 1) : 0;
                                ?>
                                <div class="text-xs text-gray-500 mt-1">
                                    <?php echo $avgBookingsPerCustomer; ?> bookings/customer
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2 no-print">
                                    <button type="button" class="text-blue-600 hover:text-blue-900" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="text-green-600 hover:text-green-900" title="Export">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button type="button" class="text-gray-600 hover:text-gray-900" title="More Options">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between mt-4 pagination no-print">
            <div class="text-sm text-gray-700">
                Showing <span class="font-medium">1</span> to <span class="font-medium"><?php echo min(10, count($report_data)); ?></span> of <span class="font-medium"><?php echo count($report_data); ?></span> results
            </div>
            <div class="flex space-x-1">
                <button type="button" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    Previous
                </button>
                <button type="button" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                    1
                </button>
                <?php if (count($report_data) > 10): ?>
                <button type="button" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    2
                </button>
                <?php endif; ?>
                <?php if (count($report_data) > 20): ?>
                <button type="button" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    3
                </button>
                <?php endif; ?>
                <?php if (count($report_data) > 10): ?>
                <button type="button" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Next
                </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <!-- Bookings Chart -->
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-all duration-300">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-chart-line text-blue-600 mr-2"></i> Bookings Trend
            </h2>
            <div class="flex space-x-2">
                <button type="button" class="chart-period-btn active px-2 py-1 text-xs font-medium rounded-md bg-blue-100 text-blue-700" data-chart="bookings" data-period="week">Week</button>
                <button type="button" class="chart-period-btn px-2 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-700 hover:bg-blue-50" data-chart="bookings" data-period="month">Month</button>
                <button type="button" class="chart-period-btn px-2 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-700 hover:bg-blue-50" data-chart="bookings" data-period="year">Year</button>
            </div>
        </div>
        <?php if (empty($report_data)): ?>
            <div class="text-center py-16 bg-gray-50 rounded-lg border border-gray-200">
                <div class="text-gray-400 text-4xl mb-3">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-600 mb-1">No Booking Data</h3>
                <p class="text-gray-500 text-sm">There is no booking data available for the selected period.</p>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-gray-500">Total Bookings</span>
                        <div class="text-2xl font-bold text-gray-800"><?php echo $total_bookings; ?></div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500">Average Daily</span>
                        <?php
                        // Calculate days in period
                        $date1 = new DateTime($date_from);
                        $date2 = new DateTime($date_to);
                        $interval = $date1->diff($date2);
                        $days = $interval->days + 1; // Include both start and end dates
                        $avg_daily = $days > 0 ? round($total_bookings / $days, 1) : 0;
                        ?>
                        <div class="text-2xl font-bold text-gray-800"><?php echo $avg_daily; ?></div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500">Peak Day</span>
                        <div class="text-2xl font-bold text-gray-800">
                            <?php
                            // Find peak day (this is simplified - in real app would need more logic)
                            $peak_day = !empty($report_data) ? date('D', strtotime($report_data[0]['booking_date'] ?? date('Y-m-d'))) : 'N/A';
                            echo $peak_day;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div style="height: 300px;" class="relative">
                <canvas id="bookingsChart"></canvas>
                <div class="absolute top-2 right-2 text-xs text-gray-500 bg-white bg-opacity-75 px-2 py-1 rounded">
                    <i class="fas fa-info-circle mr-1"></i> Click on legend to toggle
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Revenue Chart -->
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-all duration-300">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-chart-area text-green-600 mr-2"></i> Revenue Trend
            </h2>
            <div class="flex space-x-2">
                <button type="button" class="chart-period-btn active px-2 py-1 text-xs font-medium rounded-md bg-green-100 text-green-700" data-chart="revenue" data-period="week">Week</button>
                <button type="button" class="chart-period-btn px-2 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-700 hover:bg-green-50" data-chart="revenue" data-period="month">Month</button>
                <button type="button" class="chart-period-btn px-2 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-700 hover:bg-green-50" data-chart="revenue" data-period="year">Year</button>
            </div>
        </div>
        <?php if (empty($report_data)): ?>
            <div class="text-center py-16 bg-gray-50 rounded-lg border border-gray-200">
                <div class="text-gray-400 text-4xl mb-3">
                    <i class="fas fa-chart-area"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-600 mb-1">No Revenue Data</h3>
                <p class="text-gray-500 text-sm">There is no revenue data available for the selected period.</p>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-gray-500">Total Revenue</span>
                        <div class="text-2xl font-bold text-gray-800"><?php echo formatCurrency($total_revenue); ?></div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500">Average Per Booking</span>
                        <?php
                        $avg_per_booking = $total_bookings > 0 ? round($total_revenue / $total_bookings, 2) : 0;
                        ?>
                        <div class="text-2xl font-bold text-gray-800"><?php echo formatCurrency($avg_per_booking); ?></div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500">Projected Monthly</span>
                        <?php
                        // Simple projection based on daily average
                        $daily_avg = $days > 0 ? $total_revenue / $days : 0;
                        $projected_monthly = $daily_avg * 30; // Approximate month
                        ?>
                        <div class="text-2xl font-bold text-gray-800"><?php echo formatCurrency($projected_monthly); ?></div>
                    </div>
                </div>
            </div>
            <div style="height: 300px;" class="relative">
                <canvas id="revenueChart"></canvas>
                <div class="absolute top-2 right-2 text-xs text-gray-500 bg-white bg-opacity-75 px-2 py-1 rounded">
                    <i class="fas fa-info-circle mr-1"></i> Hover for details
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bookings vs Cancellations Chart -->
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-all duration-300">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-chart-bar text-purple-600 mr-2"></i> Bookings vs Cancellations
            </h2>
            <div class="flex items-center">
                <span class="text-xs text-gray-500 mr-2">Chart Type:</span>
                <div class="flex space-x-2">
                    <button type="button" class="chart-type-btn active px-2 py-1 text-xs font-medium rounded-md bg-purple-100 text-purple-700" data-chart="bookingStatus" data-type="bar">Bar</button>
                    <button type="button" class="chart-type-btn px-2 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-700 hover:bg-purple-50" data-chart="bookingStatus" data-type="line">Line</button>
                </div>
            </div>
        </div>
        <?php if (empty($report_data)): ?>
            <div class="text-center py-16 bg-gray-50 rounded-lg border border-gray-200">
                <div class="text-gray-400 text-4xl mb-3">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-600 mb-1">No Booking Status Data</h3>
                <p class="text-gray-500 text-sm">There is no booking status data available for the selected period.</p>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-gray-500">Cancellation Rate</span>
                        <?php
                        $cancellation_rate = $total_bookings > 0 ? round(($total_cancelled / $total_bookings) * 100, 1) : 0;
                        ?>
                        <div class="text-2xl font-bold <?php echo $cancellation_rate > 15 ? 'text-red-600' : 'text-gray-800'; ?>">
                            <?php echo $cancellation_rate; ?>%
                        </div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500">Confirmed Bookings</span>
                        <?php
                        $confirmed = $total_bookings - $total_cancelled;
                        ?>
                        <div class="text-2xl font-bold text-gray-800"><?php echo $confirmed; ?></div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500">Cancelled Bookings</span>
                        <div class="text-2xl font-bold text-gray-800"><?php echo $total_cancelled; ?></div>
                    </div>
                </div>
            </div>
            <div style="height: 300px;">
                <canvas id="bookingStatusChart"></canvas>
            </div>
        <?php endif; ?>
    </div>

    <!-- Top Routes Pie Chart -->
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-all duration-300">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-route text-yellow-600 mr-2"></i> Top Routes Distribution
            </h2>
            <div class="flex items-center">
                <span class="text-xs text-gray-500 mr-2">Chart Type:</span>
                <div class="flex space-x-2">
                    <button type="button" class="chart-type-btn active px-2 py-1 text-xs font-medium rounded-md bg-yellow-100 text-yellow-700" data-chart="topRoutes" data-type="pie">Pie</button>
                    <button type="button" class="chart-type-btn px-2 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-700 hover:bg-yellow-50" data-chart="topRoutes" data-type="doughnut">Doughnut</button>
                </div>
            </div>
        </div>
        <?php if (empty($top_routes)): ?>
            <div class="text-center py-16 bg-gray-50 rounded-lg border border-gray-200">
                <div class="text-gray-400 text-4xl mb-3">
                    <i class="fas fa-route"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-600 mb-1">No Route Data</h3>
                <p class="text-gray-500 text-sm">There is no route data available for the selected period.</p>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs text-gray-500">Top Route</span>
                        <div class="text-lg font-bold text-gray-800 truncate max-w-[150px]">
                            <?php echo !empty($top_routes) ? $top_routes[0]['origin'] . ' to ' . $top_routes[0]['destination'] : 'N/A'; ?>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500">Routes Count</span>
                        <div class="text-2xl font-bold text-gray-800"><?php echo count($top_routes); ?></div>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500">Top Route Revenue</span>
                        <div class="text-2xl font-bold text-gray-800">
                            <?php echo !empty($top_routes) ? formatCurrency($top_routes[0]['revenue']) : 'N/A'; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div style="height: 300px;" class="relative">
                <canvas id="topRoutesChart"></canvas>
                <div class="absolute top-2 right-2 text-xs text-gray-500 bg-white bg-opacity-75 px-2 py-1 rounded">
                    <i class="fas fa-info-circle mr-1"></i> Click segments for details
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Top Routes Table -->
<div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-all duration-300">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-route text-primary-600 mr-2"></i> Top Performing Routes
            </h2>
            <p class="text-sm text-gray-500 mt-1">Most popular routes by booking volume and revenue</p>
        </div>

        <div class="flex items-center space-x-2">
            <button type="button" id="viewAllRoutes" class="px-4 py-2 text-sm font-medium text-primary-700 bg-primary-50 rounded-lg hover:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                <i class="fas fa-list-ul mr-2"></i> View All Routes
            </button>
            <button type="button" id="routeAnalysis" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                <i class="fas fa-chart-line mr-2"></i> Route Analysis
            </button>
        </div>
    </div>

    <?php if (empty($top_routes)): ?>
        <div class="text-center py-16 bg-gray-50 rounded-lg border border-gray-200">
            <div class="text-gray-400 text-5xl mb-4">
                <i class="fas fa-route"></i>
            </div>
            <h3 class="text-xl font-medium text-gray-600 mb-2">No Route Data Available</h3>
            <p class="text-gray-500 max-w-md mx-auto">There is no route data available for the selected period. Try adjusting your date range.</p>
            <button type="button" class="mt-4 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                <i class="fas fa-sync-alt mr-2"></i> Try Different Filters
            </button>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bookings</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($top_routes as $index => $route): ?>
                        <tr class="<?php echo $index % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?> hover:bg-gray-100 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($index === 0): ?>
                                    <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-100 text-yellow-800 font-bold">1</div>
                                <?php elseif ($index === 1): ?>
                                    <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-700 font-bold">2</div>
                                <?php elseif ($index === 2): ?>
                                    <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-50 text-yellow-700 font-bold">3</div>
                                <?php else: ?>
                                    <div class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-600 font-bold"><?php echo $index + 1; ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900"><?php echo $route['origin'] . ' to ' . $route['destination']; ?></div>
                                <div class="text-xs text-gray-500">
                                    <i class="fas fa-map-marker-alt text-red-500 mr-1"></i> <?php echo $route['origin']; ?>
                                    <i class="fas fa-long-arrow-alt-right mx-1 text-gray-400"></i>
                                    <i class="fas fa-map-marker-alt text-green-500 mr-1"></i> <?php echo $route['destination']; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $route['num_bookings']; ?></div>
                                <?php
                                // Calculate percentage of total bookings
                                $total_route_bookings = array_sum(array_column($top_routes, 'num_bookings'));
                                $bookingPercent = ($total_route_bookings > 0) ? round(($route['num_bookings'] / $total_route_bookings) * 100) : 0;
                                ?>
                                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: <?php echo $bookingPercent; ?>%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1"><?php echo $bookingPercent; ?>% of top routes</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo formatCurrency($route['revenue']); ?></div>
                                <?php
                                // Calculate percentage of total revenue
                                $total_route_revenue = array_sum(array_column($top_routes, 'revenue'));
                                $revenuePercent = ($total_route_revenue > 0) ? round(($route['revenue'] / $total_route_revenue) * 100) : 0;
                                ?>
                                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
                                    <div class="bg-green-600 h-1.5 rounded-full" style="width: <?php echo $revenuePercent; ?>%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1"><?php echo $revenuePercent; ?>% of top routes</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                // Calculate performance score (simplified example)
                                $avg_revenue_per_booking = $route['num_bookings'] > 0 ? $route['revenue'] / $route['num_bookings'] : 0;
                                $performance_score = min(100, round($bookingPercent * 0.6 + $revenuePercent * 0.4));

                                // Determine performance level
                                $performance_class = '';
                                $performance_text = '';

                                if ($performance_score >= 80) {
                                    $performance_class = 'bg-green-100 text-green-800';
                                    $performance_text = 'Excellent';
                                } elseif ($performance_score >= 60) {
                                    $performance_class = 'bg-blue-100 text-blue-800';
                                    $performance_text = 'Good';
                                } elseif ($performance_score >= 40) {
                                    $performance_class = 'bg-yellow-100 text-yellow-800';
                                    $performance_text = 'Average';
                                } else {
                                    $performance_class = 'bg-red-100 text-red-800';
                                    $performance_text = 'Below Average';
                                }
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $performance_class; ?>">
                                    <?php echo $performance_text; ?>
                                </span>
                                <div class="text-xs text-gray-500 mt-1">
                                    <?php echo formatCurrency($avg_revenue_per_booking); ?> per booking
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <button type="button" class="text-blue-600 hover:text-blue-900" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="text-green-600 hover:text-green-900" title="View Schedule">
                                        <i class="fas fa-calendar-alt"></i>
                                    </button>
                                    <button type="button" class="text-purple-600 hover:text-purple-900" title="View Analytics">
                                        <i class="fas fa-chart-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (count($top_routes) > 5): ?>
        <div class="mt-4 text-center">
            <button type="button" class="px-4 py-2 text-sm font-medium text-primary-700 hover:text-primary-800 focus:outline-none transition-colors">
                <i class="fas fa-chevron-down mr-1"></i> Show More Routes
            </button>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
// Prepare data for charts
$chart_labels = [];
$bookings_data = [];
$revenue_data = [];
$cancelled_data = [];

foreach ($report_data as $row) {
    if ($report_type === 'daily') {
        $chart_labels[] = date('d M', strtotime($row['booking_date']));
    } elseif ($report_type === 'weekly') {
        $chart_labels[] = date('d M', strtotime($row['week_start'])) . ' - ' . date('d M', strtotime($row['week_end']));
    } elseif ($report_type === 'monthly') {
        $chart_labels[] = date('M Y', strtotime($row['month_start'] . '-01'));
    } elseif ($report_type === 'route') {
        $chart_labels[] = $row['origin'] . ' to ' . $row['destination'];
    } elseif ($report_type === 'bus') {
        $chart_labels[] = $row['bus_name'];
    }

    $bookings_data[] = $row['num_bookings'];
    $revenue_data[] = $row['revenue'];
    $cancelled_data[] = $row['cancelled_bookings'];
}

// Reverse arrays to show chronological order
if ($report_type === 'daily' || $report_type === 'weekly' || $report_type === 'monthly') {
    $chart_labels = array_reverse($chart_labels);
    $bookings_data = array_reverse($bookings_data);
    $revenue_data = array_reverse($revenue_data);
    $cancelled_data = array_reverse($cancelled_data);
}

// Convert PHP arrays to JSON for JavaScript
$chart_labels_json = json_encode($chart_labels);
$bookings_data_json = json_encode($bookings_data);
$revenue_data_json = json_encode($revenue_data);
$cancelled_data_json = json_encode($cancelled_data);

// Route names and booking counts for pie chart
$route_labels = [];
$route_data = [];
foreach ($top_routes as $route) {
    $route_labels[] = $route['origin'] . ' to ' . $route['destination'];
    $route_data[] = $route['num_bookings'];
}
$route_labels_json = json_encode($route_labels);
$route_data_json = json_encode($route_data);

// Get the buffered content and add it to admin_content
$admin_content .= ob_get_clean();

// Add Chart.js library and JavaScript for charts to admin content
$admin_content .= '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
$admin_content .= '<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>';
$admin_content .= <<<JAVASCRIPT
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    let bookingsChart, revenueChart, bookingStatusChart, topRoutesChart;

    // Date range quick selectors
    document.getElementById('last7days').addEventListener('click', function() {
        const today = new Date();
        const sevenDaysAgo = new Date(today);
        sevenDaysAgo.setDate(today.getDate() - 6);

        document.getElementById('date_from').value = formatDateForInput(sevenDaysAgo);
        document.getElementById('date_to').value = formatDateForInput(today);
    });

    document.getElementById('last30days').addEventListener('click', function() {
        const today = new Date();
        const thirtyDaysAgo = new Date(today);
        thirtyDaysAgo.setDate(today.getDate() - 29);

        document.getElementById('date_from').value = formatDateForInput(thirtyDaysAgo);
        document.getElementById('date_to').value = formatDateForInput(today);
    });

    document.getElementById('thisMonth').addEventListener('click', function() {
        const today = new Date();
        const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

        document.getElementById('date_from').value = formatDateForInput(firstDayOfMonth);
        document.getElementById('date_to').value = formatDateForInput(today);
    });

    // Helper function to format date for input field
    function formatDateForInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `\${year}-\${month}-\${day}`;
    }

    // Export functionality
    document.getElementById('exportCSV').addEventListener('click', function() {
        alert('CSV export functionality would be implemented here.');
        // In a real implementation, this would trigger a download of CSV data
    });

    document.getElementById('exportPDF').addEventListener('click', function() {
        alert('PDF export functionality would be implemented here.');
        // In a real implementation, this would generate a PDF report
    });

    document.getElementById('printReport').addEventListener('click', function() {
        window.print();
    });

    // Table search functionality
    const tableSearch = document.getElementById('tableSearch');
    if (tableSearch) {
        tableSearch.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('table tbody tr');

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Chart type and period toggles
    document.querySelectorAll('.chart-type-btn').forEach(button => {
        button.addEventListener('click', function() {
            const chartId = this.getAttribute('data-chart');
            const chartType = this.getAttribute('data-type');

            // Update active state
            document.querySelectorAll(`[data-chart="\${chartId}"].chart-type-btn`).forEach(btn => {
                btn.classList.remove('active', 'bg-purple-100', 'bg-yellow-100', 'text-purple-700', 'text-yellow-700');
                btn.classList.add('bg-gray-100', 'text-gray-700');
            });

            this.classList.remove('bg-gray-100', 'text-gray-700');

            if (chartId === 'bookingStatus') {
                this.classList.add('active', 'bg-purple-100', 'text-purple-700');
                updateBookingStatusChart(chartType);
            } else if (chartId === 'topRoutes') {
                this.classList.add('active', 'bg-yellow-100', 'text-yellow-700');
                updateTopRoutesChart(chartType);
            }
        });
    });

    document.querySelectorAll('.chart-period-btn').forEach(button => {
        button.addEventListener('click', function() {
            const chartId = this.getAttribute('data-chart');
            const period = this.getAttribute('data-period');

            // Update active state
            document.querySelectorAll(`[data-chart="\${chartId}"].chart-period-btn`).forEach(btn => {
                btn.classList.remove('active', 'bg-blue-100', 'bg-green-100', 'text-blue-700', 'text-green-700');
                btn.classList.add('bg-gray-100', 'text-gray-700');
            });

            this.classList.remove('bg-gray-100', 'text-gray-700');

            if (chartId === 'bookings') {
                this.classList.add('active', 'bg-blue-100', 'text-blue-700');
                // In a real app, this would fetch data for the selected period
                // For now, we'll just show an alert
                alert(`Would load bookings data for \${period} period`);
            } else if (chartId === 'revenue') {
                this.classList.add('active', 'bg-green-100', 'text-green-700');
                alert(`Would load revenue data for \${period} period`);
            }
        });
    });

    // Check if we have data to display
    if ({$chart_labels_json}.length > 0) {
        // Bookings Chart
        const bookingsCtx = document.getElementById('bookingsChart').getContext('2d');
        bookingsChart = new Chart(bookingsCtx, {
            type: 'bar',
            data: {
                labels: {$chart_labels_json},
                datasets: [{
                    label: 'Number of Bookings',
                    data: {$bookings_data_json},
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 6
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1f2937',
                        bodyColor: '#1f2937',
                        borderColor: 'rgba(59, 130, 246, 0.5)',
                        borderWidth: 1,
                        padding: 12,
                        boxPadding: 6,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                return `Bookings: \${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: '#6b7280'
                        },
                        grid: {
                            color: 'rgba(243, 244, 246, 1)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            color: '#6b7280'
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: {$chart_labels_json},
                datasets: [{
                    label: 'Revenue',
                    data: {$revenue_data_json},
                    backgroundColor: 'rgba(34, 197, 94, 0.2)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: 'rgba(34, 197, 94, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 6
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1f2937',
                        bodyColor: '#1f2937',
                        borderColor: 'rgba(34, 197, 94, 0.5)',
                        borderWidth: 1,
                        padding: 12,
                        boxPadding: 6,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                return `Revenue: \${formatCurrency(context.raw)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#6b7280',
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        },
                        grid: {
                            color: 'rgba(243, 244, 246, 1)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            color: '#6b7280'
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });

        // Bookings vs Cancellations Chart
        const bookingStatusCtx = document.getElementById('bookingStatusChart').getContext('2d');
        bookingStatusChart = new Chart(bookingStatusCtx, {
            type: 'bar',
            data: {
                labels: {$chart_labels_json},
                datasets: [
                    {
                        label: 'Bookings',
                        data: {$bookings_data_json},
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Cancellations',
                        data: {$cancelled_data_json},
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 1,
                        borderRadius: 4,
                        barPercentage: 0.6,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 6
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1f2937',
                        bodyColor: '#1f2937',
                        borderColor: 'rgba(156, 163, 175, 0.5)',
                        borderWidth: 1,
                        padding: 12,
                        boxPadding: 6,
                        usePointStyle: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: '#6b7280'
                        },
                        grid: {
                            color: 'rgba(243, 244, 246, 1)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            color: '#6b7280'
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });
    }

    // Top Routes Pie Chart
    if ({$route_labels_json}.length > 0) {
        const routesCtx = document.getElementById('topRoutesChart').getContext('2d');
        topRoutesChart = new Chart(routesCtx, {
            type: 'pie',
            data: {
                labels: {$route_labels_json},
                datasets: [{
                    data: {$route_data_json},
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(139, 92, 246, 0.7)',
                        'rgba(14, 165, 233, 0.7)',
                        'rgba(249, 115, 22, 0.7)',
                        'rgba(236, 72, 153, 0.7)'
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(239, 68, 68, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(139, 92, 246, 1)',
                        'rgba(14, 165, 233, 1)',
                        'rgba(249, 115, 22, 1)',
                        'rgba(236, 72, 153, 1)'
                    ],
                    borderWidth: 2,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1f2937',
                        bodyColor: '#1f2937',
                        borderColor: 'rgba(156, 163, 175, 0.5)',
                        borderWidth: 1,
                        padding: 12,
                        boxPadding: 6,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `\${context.label}: \${value} bookings (\${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Function to update booking status chart type
    function updateBookingStatusChart(type) {
        if (!bookingStatusChart) return;

        bookingStatusChart.config.type = type;

        if (type === 'line') {
            bookingStatusChart.data.datasets.forEach((dataset, index) => {
                if (index === 0) { // Bookings
                    dataset.borderColor = 'rgba(59, 130, 246, 1)';
                    dataset.backgroundColor = 'rgba(59, 130, 246, 0.2)';
                } else { // Cancellations
                    dataset.borderColor = 'rgba(239, 68, 68, 1)';
                    dataset.backgroundColor = 'rgba(239, 68, 68, 0.2)';
                }
                dataset.tension = 0.3;
                dataset.fill = true;
                dataset.pointBackgroundColor = dataset.borderColor;
                dataset.pointBorderColor = '#fff';
                dataset.pointBorderWidth = 2;
                dataset.pointRadius = 4;
                dataset.pointHoverRadius = 6;
            });
        } else { // bar
            bookingStatusChart.data.datasets.forEach((dataset, index) => {
                if (index === 0) { // Bookings
                    dataset.backgroundColor = 'rgba(59, 130, 246, 0.7)';
                    dataset.borderColor = 'rgba(59, 130, 246, 1)';
                } else { // Cancellations
                    dataset.backgroundColor = 'rgba(239, 68, 68, 0.7)';
                    dataset.borderColor = 'rgba(239, 68, 68, 1)';
                }
                dataset.borderWidth = 1;
                dataset.borderRadius = 4;
                dataset.barPercentage = 0.6;
                dataset.categoryPercentage = 0.8;

                // Remove line chart specific properties
                delete dataset.tension;
                delete dataset.fill;
                delete dataset.pointBackgroundColor;
                delete dataset.pointBorderColor;
                delete dataset.pointBorderWidth;
                delete dataset.pointRadius;
                delete dataset.pointHoverRadius;
            });
        }

        bookingStatusChart.update();
    }

    // Function to update top routes chart type
    function updateTopRoutesChart(type) {
        if (!topRoutesChart) return;

        topRoutesChart.config.type = type;

        if (type === 'doughnut') {
            topRoutesChart.options.cutout = '50%';
        } else { // pie
            topRoutesChart.options.cutout = 0;
        }

        topRoutesChart.update();
    }

    // Helper function to format currency
    function formatCurrency(value) {
        return new Intl.NumberFormat('en-KE', {
            style: 'currency',
            currency: 'KES',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }

    // Add animation to cards
    document.querySelectorAll('.fade-in').forEach((element, index) => {
        setTimeout(() => {
            element.classList.add('active');
        }, 100 * index);
    });
});
</script>
JAVASCRIPT;

// Add print-specific CSS
$admin_content .= <<<HTML
<style>
@media print {
    /* Hide elements that shouldn't be printed */
    nav,
    .btn-secondary,
    #back-to-top,
    .md\\:col-span-1,
    form,
    .export-buttons,
    button,
    #tableSearch,
    #entriesPerPage,
    .pagination,
    .no-print,
    .chart-period-btn,
    .chart-type-btn,
    a[href]:after {
        display: none !important;
    }

    /* Adjust layout for printing */
    body {
        background-color: white !important;
        font-size: 12pt;
        color: #000;
    }

    .container {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .md\\:col-span-4 {
        width: 100% !important;
        grid-column: span 5 / span 5 !important;
    }

    .grid {
        display: block !important;
    }

    .md\\:grid-cols-5 {
        grid-template-columns: none !important;
    }

    .md\\:grid-cols-4,
    .md\\:grid-cols-2 {
        grid-template-columns: 1fr !important;
    }

    .shadow-md,
    .shadow-lg,
    .hover\\:shadow-lg,
    .hover\\:shadow-xl {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }

    /* Add page breaks */
    .page-break {
        page-break-after: always;
    }

    /* Ensure report title is visible */
    h1 {
        font-size: 24pt;
        margin-bottom: 20px;
        text-align: center;
    }

    /* Format report header */
    .print-header {
        text-align: center;
        margin-bottom: 20px;
    }

    /* Adjust table for printing */
    table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    th, td {
        border: 1px solid #ddd !important;
        padding: 8px !important;
    }

    /* Add report footer */
    .print-footer {
        text-align: center;
        font-size: 10pt;
        margin-top: 20px;
        border-top: 1px solid #ddd;
        padding-top: 10px;
    }

    /* Hide transition effects */
    .transition-all,
    .hover\\:translate-x-1,
    .hover\\:scale-105,
    .hover\\:-translate-y-1 {
        transition: none !important;
        transform: none !important;
    }

    /* Ensure charts are properly sized */
    canvas {
        max-width: 100% !important;
        height: auto !important;
    }

    /* Add print-only elements */
    .print-only {
        display: block !important;
    }

    /* Ensure proper page margins */
    @page {
        margin: 0.5cm;
    }
}

/* Print-only elements (hidden by default) */
.print-only {
    display: none;
}
</style>

<!-- Print-only header -->
<div class="print-only print-header">
    <h1>Isiolo Raha Bus Booking System</h1>
    <h2><?php echo ucfirst($report_type); ?> Report (<?php echo date('d M, Y', strtotime($date_from)); ?> - <?php echo date('d M, Y', strtotime($date_to)); ?>)</h2>
    <p>Generated on: <?php echo date('d M, Y H:i:s'); ?></p>
</div>

<!-- Print-only footer -->
<div class="print-only print-footer">
    <p>Isiolo Raha Bus Booking System &copy; <?php echo date('Y'); ?></p>
    <p>This report was generated on <?php echo date('d M, Y H:i:s'); ?></p>
</div>
HTML;

// Check if admin template exists and include it
if (file_exists('../includes/templates/admin_template.php')) {
    require_once '../includes/templates/admin_template.php';
} else {
    // Fallback: include header and display content directly
    if (file_exists('../includes/templates/admin_header.php')) {
        require_once '../includes/templates/admin_header.php';
    }
    
    echo $admin_content;
    
    if (file_exists('../includes/templates/admin_footer.php')) {
        require_once '../includes/templates/admin_footer.php';
    } else {
        echo '</div></body></html>';
    }
}
?>

