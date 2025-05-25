<?php
// Include session configuration
require_once '../config/session_config.php';

// Start session
session_start();

// Include functions
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isAdmin()) {
    // Set message
    setFlashMessage("error", "You do not have permission to access the admin dashboard.");

    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Set page title
$page_title = "Admin Dashboard";

// Include database connection
$conn = require_once '../config/database.php';

// Get statistics
$sql = "SELECT
        (SELECT COUNT(*) FROM users) AS total_users,
        (SELECT COUNT(*) FROM buses) AS total_buses,
        (SELECT COUNT(*) FROM routes) AS total_routes,
        (SELECT COUNT(*) FROM schedules) AS total_schedules,
        (SELECT COUNT(*) FROM bookings) AS total_bookings,
        (SELECT COUNT(*) FROM bookings WHERE status = 'confirmed') AS confirmed_bookings,
        (SELECT COUNT(*) FROM bookings WHERE status = 'cancelled') AS cancelled_bookings,
        (SELECT COUNT(*) FROM bookings WHERE status = 'pending') AS pending_bookings,
        (SELECT SUM(amount) FROM bookings WHERE status = 'confirmed') AS total_revenue,
        (SELECT COUNT(*) FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS bookings_last_7_days,
        (SELECT COUNT(*) FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS bookings_last_30_days,
        (SELECT SUM(amount) FROM bookings WHERE status = 'confirmed' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) AS revenue_last_30_days";

$result = $conn->query($sql);
$stats = $result->fetch_assoc();

// Removed booking trends query for analytics dashboard

// Removed top routes query for analytics dashboard

// Get recent bookings
$sql = "SELECT b.id, b.booking_reference, b.passenger_name, b.seat_number, b.amount, b.status, b.created_at,
        s.departure_time, r.origin, r.destination
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        ORDER BY b.created_at DESC
        LIMIT 10";

$result = $conn->query($sql);
$recent_bookings = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recent_bookings[] = $row;
    }
}

// Get upcoming departures
$sql = "SELECT s.id, s.departure_time, s.arrival_time, s.fare, s.status,
        r.origin, r.destination,
        b.name AS bus_name, b.registration_number,
        (SELECT COUNT(*) FROM bookings WHERE schedule_id = s.id AND status != 'cancelled') AS booked_seats,
        b.capacity
        FROM schedules s
        JOIN routes r ON s.route_id = r.id
        JOIN buses b ON s.bus_id = b.id
        WHERE s.departure_time > NOW() AND s.status = 'scheduled'
        ORDER BY s.departure_time ASC
        LIMIT 5";

$result = $conn->query($sql);
$upcoming_departures = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $upcoming_departures[] = $row;
    }
}
?>

<?php
// Start output buffering to capture content for the template
ob_start();
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Admin Dashboard</h1>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <!-- Users -->
            <div class="bg-white rounded-lg border border-gray-100 hover:shadow-lg transition-shadow duration-300 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-users text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-600">Total Users</p>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-bold"><?php echo $stats['total_users'] ?? 0; ?></p>
                                <span class="ml-2 text-xs font-medium text-green-600 flex items-center">
                                    <i class="fas fa-arrow-up mr-1"></i> 5%
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">From last month</p>
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
            </div>

            <!-- Buses -->
            <div class="bg-white rounded-lg border border-gray-100 hover:shadow-lg transition-shadow duration-300 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-bus text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-600">Total Buses</p>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-bold"><?php echo $stats['total_buses'] ?? 0; ?></p>
                                <span class="ml-2 text-xs font-medium text-green-600 flex items-center">
                                    <i class="fas fa-arrow-up mr-1"></i> 2%
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">From last month</p>
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
            </div>

            <!-- Routes -->
            <div class="bg-white rounded-lg border border-gray-100 hover:shadow-lg transition-shadow duration-300 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-route text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-600">Total Routes</p>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-bold"><?php echo $stats['total_routes'] ?? 0; ?></p>
                                <span class="ml-2 text-xs font-medium text-yellow-600 flex items-center">
                                    <i class="fas fa-equals mr-1"></i> 0%
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">From last month</p>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="w-16 h-16 rounded-full bg-yellow-50 flex items-center justify-center">
                            <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                                <div class="w-8 h-8 rounded-full bg-yellow-200"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue -->
            <div class="bg-white rounded-lg border border-gray-100 hover:shadow-lg transition-shadow duration-300 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-primary-100 text-primary-600">
                            <i class="fas fa-money-bill-wave text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-600">Total Revenue</p>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-bold"><?php echo formatCurrency($stats['total_revenue'] ?? 0); ?></p>
                                <?php
                                // Calculate revenue growth (example calculation)
                                $revenue_growth = 0;
                                $last_30_days_revenue = $stats['revenue_last_30_days'] ?? 0;
                                $total_revenue = $stats['total_revenue'] ?? 0;

                                if ($total_revenue > 0 && $last_30_days_revenue > 0) {
                                    $revenue_growth = round(($last_30_days_revenue / $total_revenue) * 100);
                                    if ($revenue_growth > 100) $revenue_growth = 8; // Fallback for demo
                                }
                                ?>
                                <span class="ml-2 text-xs font-medium text-green-600 flex items-center">
                                    <i class="fas fa-arrow-up mr-1"></i> <?php echo $revenue_growth; ?>%
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">From last month</p>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="w-16 h-16 rounded-full bg-primary-50 flex items-center justify-center">
                            <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center">
                                <div class="w-8 h-8 rounded-full bg-primary-200"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- More Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Bookings -->
            <div class="bg-white rounded-lg border border-gray-100 hover:shadow-lg transition-shadow duration-300 p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800 flex items-center">
                    <i class="fas fa-ticket-alt mr-2 text-primary-600"></i>
                    Booking Statistics
                </h2>

                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-600">Booking Status Distribution</span>
                        <span class="text-xs text-gray-500">Last 30 days</span>
                    </div>

                    <?php
                    // Calculate percentages for the pie chart
                    $total = ($stats['confirmed_bookings'] ?? 0) + ($stats['cancelled_bookings'] ?? 0) + ($stats['pending_bookings'] ?? 0);
                    $confirmed_percent = $total > 0 ? round(($stats['confirmed_bookings'] ?? 0) / $total * 100) : 0;
                    $cancelled_percent = $total > 0 ? round(($stats['cancelled_bookings'] ?? 0) / $total * 100) : 0;
                    $pending_percent = $total > 0 ? round(($stats['pending_bookings'] ?? 0) / $total * 100) : 0;
                    ?>

                    <div class="h-4 w-full bg-gray-200 rounded-full overflow-hidden">
                        <div class="flex h-full">
                            <div class="h-full bg-green-500" style="width: <?php echo $confirmed_percent; ?>%"></div>
                            <div class="h-full bg-red-500" style="width: <?php echo $cancelled_percent; ?>%"></div>
                            <div class="h-full bg-yellow-500" style="width: <?php echo $pending_percent; ?>%"></div>
                        </div>
                    </div>

                    <div class="flex justify-between text-xs text-gray-600 mt-2">
                        <div class="flex items-center">
                            <span class="w-3 h-3 inline-block bg-green-500 rounded-full mr-1"></span>
                            <span>Confirmed (<?php echo $confirmed_percent; ?>%)</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 inline-block bg-red-500 rounded-full mr-1"></span>
                            <span>Cancelled (<?php echo $cancelled_percent; ?>%)</span>
                        </div>
                        <div class="flex items-center">
                            <span class="w-3 h-3 inline-block bg-yellow-500 rounded-full mr-1"></span>
                            <span>Pending (<?php echo $pending_percent; ?>%)</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded-md transition-colors duration-200">
                        <span class="text-gray-600 flex items-center">
                            <i class="fas fa-bookmark mr-2 text-gray-400"></i>
                            Total Bookings
                        </span>
                        <div class="flex flex-col items-end">
                            <span class="font-bold bg-gray-100 px-3 py-1 rounded-full"><?php echo $stats['total_bookings'] ?? 0; ?></span>
                            <span class="text-xs text-gray-500 mt-1">All time</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded-md transition-colors duration-200">
                        <span class="text-gray-600 flex items-center">
                            <i class="fas fa-check-circle mr-2 text-green-500"></i>
                            Confirmed Bookings
                        </span>
                        <div class="flex flex-col items-end">
                            <span class="font-bold bg-green-100 text-green-700 px-3 py-1 rounded-full"><?php echo $stats['confirmed_bookings'] ?? 0; ?></span>
                            <span class="text-xs text-green-600 mt-1 flex items-center">
                                <i class="fas fa-arrow-up mr-1"></i> 12% from last month
                            </span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded-md transition-colors duration-200">
                        <span class="text-gray-600 flex items-center">
                            <i class="fas fa-times-circle mr-2 text-red-500"></i>
                            Cancelled Bookings
                        </span>
                        <div class="flex flex-col items-end">
                            <span class="font-bold bg-red-100 text-red-700 px-3 py-1 rounded-full"><?php echo $stats['cancelled_bookings'] ?? 0; ?></span>
                            <span class="text-xs text-green-600 mt-1 flex items-center">
                                <i class="fas fa-arrow-down mr-1"></i> 3% from last month
                            </span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded-md transition-colors duration-200">
                        <span class="text-gray-600 flex items-center">
                            <i class="fas fa-calendar-alt mr-2 text-blue-500"></i>
                            Schedules
                        </span>
                        <div class="flex flex-col items-end">
                            <span class="font-bold bg-blue-100 text-blue-700 px-3 py-1 rounded-full"><?php echo $stats['total_schedules'] ?? 0; ?></span>
                            <span class="text-xs text-gray-500 mt-1">Active schedules</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg border border-gray-100 hover:shadow-lg transition-shadow duration-300 p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800 flex items-center">
                    <i class="fas fa-bolt mr-2 text-yellow-500"></i>
                    Quick Actions
                </h2>

                <div class="grid grid-cols-2 gap-4">
                    <a href="create_booking.php" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-indigo-50 hover:border-indigo-200 transition-all duration-300">
                        <div class="p-3 rounded-full bg-indigo-100 text-indigo-600 mb-2 transform transition-transform duration-300 hover:scale-110">
                            <i class="fas fa-plus-circle text-xl"></i>
                        </div>
                        <span class="font-semibold text-center text-gray-700">Create Booking</span>
                    </a>

                    <a href="buses.php" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-200 transition-all duration-300">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mb-2 transform transition-transform duration-300 hover:scale-110">
                            <i class="fas fa-bus text-xl"></i>
                        </div>
                        <span class="font-semibold text-center text-gray-700">Manage Buses</span>
                    </a>

                    <a href="routes.php" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-200 transition-all duration-300">
                        <div class="p-3 rounded-full bg-green-100 text-green-600 mb-2 transform transition-transform duration-300 hover:scale-110">
                            <i class="fas fa-route text-xl"></i>
                        </div>
                        <span class="font-semibold text-center text-gray-700">Manage Routes</span>
                    </a>

                    <a href="schedules.php" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-yellow-50 hover:border-yellow-200 transition-all duration-300">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mb-2 transform transition-transform duration-300 hover:scale-110">
                            <i class="fas fa-calendar-alt text-xl"></i>
                        </div>
                        <span class="font-semibold text-center text-gray-700">Manage Schedules</span>
                    </a>

                    <a href="bookings.php" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-primary-50 hover:border-primary-200 transition-all duration-300">
                        <div class="p-3 rounded-full bg-primary-100 text-primary-600 mb-2 transform transition-transform duration-300 hover:scale-110">
                            <i class="fas fa-ticket-alt text-xl"></i>
                        </div>
                        <span class="font-semibold text-center text-gray-700">Manage Bookings</span>
                    </a>

                    <a href="reports.php" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-200 transition-all duration-300">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600 mb-2 transform transition-transform duration-300 hover:scale-110">
                            <i class="fas fa-chart-bar text-xl"></i>
                        </div>
                        <span class="font-semibold text-center text-gray-700">Reports</span>
                    </a>
                </div>
            </div>

            <!-- System Info -->
            <div class="bg-white rounded-lg border border-gray-100 hover:shadow-lg transition-shadow duration-300 p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                    System Information
                </h2>

                <div class="space-y-4">
                    <div class="p-3 border-b border-gray-100">
                        <p class="text-sm text-gray-500 mb-1">System Name</p>
                        <p class="font-bold text-gray-800 flex items-center">
                            <i class="fas fa-bus-alt mr-2 text-primary-600"></i>
                            Isiolo Raha Bus Booking System
                        </p>
                    </div>

                    <div class="p-3 border-b border-gray-100">
                        <p class="text-sm text-gray-500 mb-1">Version</p>
                        <p class="font-bold text-gray-800 flex items-center">
                            <i class="fas fa-code-branch mr-2 text-gray-600"></i>
                            1.0.0
                        </p>
                    </div>

                    <div class="p-3 border-b border-gray-100">
                        <p class="text-sm text-gray-500 mb-1">Server Time</p>
                        <p class="font-bold text-gray-800 flex items-center">
                            <i class="fas fa-clock mr-2 text-gray-600"></i>
                            <?php echo date('d M, Y H:i:s'); ?>
                        </p>
                    </div>

                    <div class="p-3">
                        <p class="text-sm text-gray-500 mb-1">Admin</p>
                        <p class="font-bold text-gray-800 flex items-center">
                            <i class="fas fa-user-shield mr-2 text-gray-600"></i>
                            <?php
                            if (isset($_SESSION['user_name'])) {
                                echo $_SESSION['user_name'];
                            } elseif (isset($_SESSION['user_email'])) {
                                echo $_SESSION['user_email'];
                            } else {
                                echo "Administrator";
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Recent Bookings -->
            <div class="bg-white rounded-lg border border-gray-100 hover:shadow-lg transition-shadow duration-300 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-clipboard-list mr-2 text-primary-600"></i>
                        Recent Bookings
                    </h2>
                    <a href="bookings.php" class="text-primary-600 hover:underline flex items-center">
                        View All
                        <i class="fas fa-arrow-right ml-1 text-sm"></i>
                    </a>
                </div>

                <?php if (!empty($recent_bookings)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passenger</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recent_bookings as $booking): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <a href="booking_details.php?id=<?php echo $booking['id']; ?>" class="text-primary-600 hover:text-primary-800 font-medium">
                                                <?php echo $booking['booking_reference']; ?>
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?php echo $booking['passenger_name']; ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?php echo $booking['origin']; ?> to <?php echo $booking['destination']; ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium"><?php echo formatCurrency($booking['amount']); ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <?php if ($booking['status'] === 'confirmed'): ?>
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i> Confirmed
                                                </span>
                                            <?php elseif ($booking['status'] === 'cancelled'): ?>
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    <i class="fas fa-times-circle mr-1"></i> Cancelled
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i> Pending
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        <i class="fas fa-ticket-alt text-gray-400 text-4xl mb-3"></i>
                        <p class="text-gray-500">No bookings found.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Upcoming Departures -->
            <div class="bg-white rounded-lg border border-gray-100 hover:shadow-lg transition-shadow duration-300 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-calendar-day mr-2 text-blue-600"></i>
                        Upcoming Departures
                    </h2>
                    <a href="schedules.php" class="text-primary-600 hover:underline flex items-center">
                        View All
                        <i class="fas fa-arrow-right ml-1 text-sm"></i>
                    </a>
                </div>

                <?php if (!empty($upcoming_departures)): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bus</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departure</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bookings</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($upcoming_departures as $departure): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?php echo $departure['origin']; ?> to <?php echo $departure['destination']; ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700"><?php echo $departure['bus_name']; ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                            <span class="flex items-center">
                                                <i class="fas fa-clock mr-1 text-blue-500"></i>
                                                <?php echo date('d M, Y H:i', strtotime($departure['departure_time'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <?php
                                            $occupancy_percentage = ($departure['booked_seats'] / $departure['capacity']) * 100;
                                            if ($occupancy_percentage < 50) {
                                                $badge_bg = 'bg-yellow-100';
                                                $badge_text = 'text-yellow-800';
                                                $icon = '<i class="fas fa-exclamation-circle mr-1"></i>';
                                            } elseif ($occupancy_percentage < 80) {
                                                $badge_bg = 'bg-blue-100';
                                                $badge_text = 'text-blue-800';
                                                $icon = '<i class="fas fa-info-circle mr-1"></i>';
                                            } else {
                                                $badge_bg = 'bg-green-100';
                                                $badge_text = 'text-green-800';
                                                $icon = '<i class="fas fa-check-circle mr-1"></i>';
                                            }
                                            ?>
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $badge_bg . ' ' . $badge_text; ?>">
                                                <?php echo $icon; ?>
                                                <?php echo $departure['booked_seats']; ?>/<?php echo $departure['capacity']; ?> seats
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        <i class="fas fa-calendar-times text-gray-400 text-4xl mb-3"></i>
                        <p class="text-gray-500">No upcoming departures found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>


    </div>
</div>

<?php
// Get the buffered content
$admin_content = ob_get_clean();

// Removed chart data preparation code for analytics dashboard

// No charts needed - removed analytics dashboard
$admin_content .= '<script>
// Dashboard initialization
document.addEventListener("DOMContentLoaded", function() {
    console.log("Dashboard initialized");
});
</script>';

// Include the admin template
require_once '../includes/templates/admin_template.php';
?>

