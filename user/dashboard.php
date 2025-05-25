<?php
// Include session configuration
require_once '../config/session_config.php';

// Start session
session_start();

// Include functions
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Set message
    setFlashMessage("error", "Please login to access your dashboard.");

    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Set page title
$page_title = "Dashboard";

// Include header
require_once '../includes/templates/header.php';

// Include database connection
$conn = require_once '../config/database.php';

// Get user details
$sql = "SELECT first_name, last_name, email, phone, created_at FROM users WHERE id = ?";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("i", $_SESSION['user_id']);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
        }
    }

    // Close statement
    $stmt->close();
}

// Get upcoming bookings
$sql = "SELECT b.booking_reference, b.seat_number, b.status, b.created_at,
        s.departure_time, s.arrival_time,
        r.origin, r.destination,
        bs.name AS bus_name
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        JOIN buses bs ON s.bus_id = bs.id
        WHERE b.user_id = ? AND s.departure_time > NOW() AND b.status != 'cancelled'
        ORDER BY s.departure_time ASC
        LIMIT 5";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("i", $_SESSION['user_id']);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        $upcoming_bookings = [];
        while ($row = $result->fetch_assoc()) {
            $upcoming_bookings[] = $row;
        }
    }

    // Close statement
    $stmt->close();
}

// Get booking statistics
$sql = "SELECT
        COUNT(*) AS total_bookings,
        SUM(CASE WHEN s.departure_time > NOW() AND b.status != 'cancelled' THEN 1 ELSE 0 END) AS upcoming_bookings,
        SUM(CASE WHEN s.departure_time < NOW() AND b.status = 'confirmed' THEN 1 ELSE 0 END) AS completed_bookings,
        SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_bookings,
        SUM(b.amount) AS total_spent
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        WHERE b.user_id = ?";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("i", $_SESSION['user_id']);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $stats = $result->fetch_assoc();
        }
    }

    // Close statement
    $stmt->close();
}

// Get recent activity (last 3 bookings)
$sql = "SELECT b.booking_reference, b.status, b.created_at,
        r.origin, r.destination
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        WHERE b.user_id = ?
        ORDER BY b.created_at DESC
        LIMIT 3";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("i", $_SESSION['user_id']);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        $recent_activity = [];
        while ($row = $result->fetch_assoc()) {
            $recent_activity[] = $row;
        }
    }

    // Close statement
    $stmt->close();
}

// Calculate profile completion percentage
$profile_fields = ['first_name', 'last_name', 'email', 'phone'];
$filled_fields = 0;

foreach ($profile_fields as $field) {
    if (!empty($user[$field])) {
        $filled_fields++;
    }
}

$profile_completion = ($filled_fields / count($profile_fields)) * 100;
?>

<div class="bg-gray-50 min-h-screen">
    <!-- Breadcrumb navigation -->
    <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center text-sm text-gray-600">
                <a href="<?php echo APP_URL; ?>" class="hover:text-primary-600 transition-colors">Home</a>
                <i class="fas fa-chevron-right text-xs mx-2 text-gray-400"></i>
                <span class="font-medium text-gray-800">Dashboard</span>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Welcome section -->
        <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl shadow-lg mb-8 overflow-hidden">
            <div class="p-6 md:p-8 flex flex-col md:flex-row items-center justify-between">
                <div class="mb-4 md:mb-0">
                    <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">
                        Welcome back, <?php echo $user['first_name']; ?>!
                    </h1>
                    <p class="text-primary-100">
                        <?php
                        $hour = date('H');
                        if ($hour < 12) {
                            echo "Good morning! Ready to plan your next journey?";
                        } elseif ($hour < 18) {
                            echo "Good afternoon! Where would you like to travel today?";
                        } else {
                            echo "Good evening! Looking for a comfortable ride?";
                        }
                        ?>
                    </p>

                    <?php if (!empty($upcoming_bookings)): ?>
                    <div class="mt-3 bg-white bg-opacity-20 rounded-lg px-4 py-2 inline-block">
                        <p class="text-white text-sm flex items-center">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            <span>Your next trip: <strong><?php echo date('d M', strtotime($upcoming_bookings[0]['departure_time'])); ?></strong> to <strong><?php echo $upcoming_bookings[0]['destination']; ?></strong></span>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="flex space-x-3">
                    <a href="../index.php#search-form" class="bg-white text-primary-700 hover:bg-primary-50 px-6 py-3 rounded-lg font-medium shadow-md transition-all duration-300 transform hover:scale-105 flex items-center">
                        <i class="fas fa-search mr-2"></i> Book a Trip
                    </a>
                    <?php if (!empty($upcoming_bookings)): ?>
                    <a href="booking_details.php?reference=<?php echo $upcoming_bookings[0]['booking_reference']; ?>" class="bg-primary-500 text-white hover:bg-primary-400 px-6 py-3 rounded-lg font-medium shadow-md transition-all duration-300 transform hover:scale-105 flex items-center">
                        <i class="fas fa-ticket-alt mr-2"></i> View Next Trip
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Bookings -->
            <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-primary-500 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 animate-on-load opacity-0 translate-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-primary-100 text-primary-600 shadow-sm">
                            <i class="fas fa-ticket-alt text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm font-medium">Total Bookings</p>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total_bookings'] ?? 0; ?></p>
                                <span class="ml-2 text-xs font-medium text-gray-500">all time</span>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="w-12 h-12 rounded-full bg-primary-50 flex items-center justify-center">
                            <div class="w-8 h-8 rounded-full bg-primary-100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Trips -->
            <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-blue-500 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 animate-on-load opacity-0 translate-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 shadow-sm">
                            <i class="fas fa-calendar-alt text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm font-medium">Upcoming Trips</p>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-bold text-gray-800"><?php echo $stats['upcoming_bookings'] ?? 0; ?></p>
                                <span class="ml-2 text-xs font-medium text-gray-500">scheduled</span>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center">
                            <div class="w-8 h-8 rounded-full bg-blue-100"></div>
                        </div>
                    </div>
                </div>
                <?php if ($stats['upcoming_bookings'] > 0): ?>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <a href="bookings.php?status=confirmed" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                        <span>View all upcoming trips</span>
                        <i class="fas fa-chevron-right ml-1 text-xs"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Completed Trips -->
            <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-green-500 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 animate-on-load opacity-0 translate-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600 shadow-sm">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm font-medium">Completed Trips</p>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-bold text-gray-800"><?php echo $stats['completed_bookings'] ?? 0; ?></p>
                                <span class="ml-2 text-xs font-medium text-gray-500">journeys</span>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center">
                            <div class="w-8 h-8 rounded-full bg-green-100"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Spent -->
            <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-yellow-500 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 animate-on-load opacity-0 translate-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 shadow-sm">
                            <i class="fas fa-money-bill-wave text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm font-medium">Total Spent</p>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-bold text-gray-800"><?php echo formatCurrency($stats['total_spent'] ?? 0); ?></p>
                                <span class="ml-2 text-xs font-medium text-gray-500">all time</span>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="w-12 h-12 rounded-full bg-yellow-50 flex items-center justify-center">
                            <div class="w-8 h-8 rounded-full bg-yellow-100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left column -->
            <div class="lg:col-span-1">
                <!-- User Profile -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
                    <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-4">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-bold text-white">Profile</h2>
                            <a href="profile.php" class="text-white hover:text-primary-100 transition-colors">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="flex flex-col items-center mb-6">
                            <div class="w-24 h-24 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 mb-4 ring-4 ring-primary-50">
                                <span class="text-3xl font-bold"><?php echo substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1); ?></span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h3>
                            <p class="text-gray-500 text-sm">Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                        </div>

                        <!-- Profile completion progress -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Profile Completion</span>
                                <span class="text-sm font-medium text-primary-600"><?php echo round($profile_completion); ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-primary-600 h-2.5 rounded-full" style="width: <?php echo $profile_completion; ?>%"></div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-primary-50 flex items-center justify-center text-primary-600 mr-3">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Email</p>
                                    <p class="font-medium text-gray-800"><?php echo $user['email']; ?></p>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-primary-50 flex items-center justify-center text-primary-600 mr-3">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Phone</p>
                                    <p class="font-medium text-gray-800"><?php echo $user['phone'] ?: 'Not provided'; ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <a href="profile.php" class="w-full block text-center bg-primary-50 hover:bg-primary-100 text-primary-700 font-medium py-2 px-4 rounded-lg transition-colors">
                                <i class="fas fa-user-edit mr-2"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-lg font-bold text-gray-800">Recent Activity</h2>
                    </div>

                    <div class="p-6">
                        <?php if (!empty($recent_activity)): ?>
                            <div class="space-y-4">
                                <?php foreach ($recent_activity as $activity): ?>
                                    <div class="flex items-start">
                                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 mr-3 flex-shrink-0">
                                            <i class="fas fa-ticket-alt"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800">
                                                <?php echo $activity['origin']; ?> to <?php echo $activity['destination']; ?>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                <span class="inline-block px-2 py-1 rounded-full <?php echo $activity['status'] == 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?> text-xs mr-2">
                                                    <?php echo ucfirst($activity['status']); ?>
                                                </span>
                                                <?php echo timeAgo($activity['created_at']); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-6">
                                <div class="text-4xl text-gray-300 mb-3">
                                    <i class="fas fa-history"></i>
                                </div>
                                <p class="text-gray-500">No recent activity</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right column (wider) -->
            <div class="lg:col-span-2">
                <!-- Upcoming Trips -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-800 flex items-center">
                            <i class="fas fa-calendar-alt text-primary-600 mr-2"></i> Upcoming Trips
                        </h2>
                        <a href="bookings.php?status=confirmed" class="text-primary-600 hover:text-primary-800 text-sm font-medium transition-colors">
                            View All <i class="fas fa-chevron-right ml-1 text-xs"></i>
                        </a>
                    </div>

                    <div class="p-6">
                        <?php if (!empty($upcoming_bookings)): ?>
                            <div class="relative">
                                <!-- Timeline Line -->
                                <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200 hidden md:block"></div>

                                <div class="space-y-8">
                                    <?php foreach ($upcoming_bookings as $index => $booking):
                                        $days_until = ceil((strtotime($booking['departure_time']) - time()) / (60 * 60 * 24));
                                        $is_soon = $days_until <= 3;
                                        $is_today = $days_until < 1;
                                        $status_color = $is_today ? 'red' : ($is_soon ? 'yellow' : 'blue');
                                        $time_label = $is_today ? 'Today' : ($is_soon ? 'Soon' : $days_until . ' days');
                                    ?>
                                        <div class="flex flex-col md:flex-row">
                                            <!-- Timeline Dot for Desktop -->
                                            <div class="hidden md:flex flex-col items-center mr-6">
                                                <div class="w-16 h-16 rounded-full bg-<?php echo $status_color; ?>-100 border-4 border-white shadow-md flex items-center justify-center z-10">
                                                    <?php if ($is_today): ?>
                                                        <span class="text-red-600 font-bold text-xs">TODAY</span>
                                                    <?php elseif ($is_soon): ?>
                                                        <span class="text-yellow-600 font-bold text-xs"><?php echo $days_until; ?> DAYS</span>
                                                    <?php else: ?>
                                                        <span class="text-blue-600 font-bold text-xs"><?php echo $days_until; ?> DAYS</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-xs font-medium text-gray-500 mt-2">
                                                    <?php echo date('d M', strtotime($booking['departure_time'])); ?>
                                                </div>
                                            </div>

                                            <!-- Card Content -->
                                            <div class="bg-white border border-gray-200 hover:border-primary-200 rounded-xl p-5 transition-all duration-300 hover:shadow-md flex-1 <?php echo $is_today ? 'border-red-200 bg-red-50' : ($is_soon ? 'border-yellow-200' : ''); ?>">
                                                <!-- Mobile Timeline Indicator -->
                                                <div class="md:hidden flex items-center mb-3">
                                                    <div class="w-10 h-10 rounded-full bg-<?php echo $status_color; ?>-100 flex items-center justify-center mr-3">
                                                        <i class="fas fa-calendar-day text-<?php echo $status_color; ?>-600"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-bold text-gray-800">
                                                            <?php echo date('d M, Y', strtotime($booking['departure_time'])); ?>
                                                        </div>
                                                        <div class="text-xs text-<?php echo $status_color; ?>-600 font-medium">
                                                            <?php echo $time_label; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex flex-col md:flex-row justify-between md:items-center mb-4">
                                                    <div class="mb-3 md:mb-0">
                                                        <div class="flex items-center">
                                                            <i class="fas fa-map-marker-alt text-primary-600 mr-2"></i>
                                                            <h3 class="font-bold text-gray-800 text-lg"><?php echo $booking['origin']; ?> to <?php echo $booking['destination']; ?></h3>
                                                        </div>
                                                        <p class="text-sm text-gray-600 mt-1"><?php echo $booking['bus_name']; ?> • Seat <?php echo $booking['seat_number']; ?></p>
                                                    </div>
                                                    <div>
                                                        <span class="inline-block px-3 py-1 rounded-full bg-green-100 text-green-800 text-sm font-medium">
                                                            <?php echo ucfirst($booking['status']); ?>
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white bg-opacity-80 rounded-lg p-4 border border-gray-100">
                                                    <div class="mb-3 sm:mb-0">
                                                        <p class="text-xs text-gray-500 mb-1">DEPARTURE</p>
                                                        <div class="flex items-center">
                                                            <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                                                            <p class="font-medium"><?php echo date('d M, Y', strtotime($booking['departure_time'])); ?></p>
                                                        </div>
                                                        <div class="flex items-center mt-1">
                                                            <i class="fas fa-clock text-blue-600 mr-2"></i>
                                                            <p class="font-medium"><?php echo date('h:i A', strtotime($booking['departure_time'])); ?></p>
                                                        </div>
                                                    </div>

                                                    <div class="hidden sm:block">
                                                        <div class="w-24 h-0.5 bg-gray-300 relative">
                                                            <div class="absolute top-1/2 left-0 transform -translate-y-1/2 w-3 h-3 rounded-full bg-blue-500"></div>
                                                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-white border-2 border-primary-500 flex items-center justify-center">
                                                                <i class="fas fa-bus text-xs text-primary-600"></i>
                                                            </div>
                                                            <div class="absolute top-1/2 right-0 transform -translate-y-1/2 w-3 h-3 rounded-full bg-green-500"></div>
                                                        </div>
                                                    </div>

                                                    <div>
                                                        <p class="text-xs text-gray-500 mb-1">ARRIVAL</p>
                                                        <div class="flex items-center">
                                                            <i class="fas fa-calendar-alt text-green-600 mr-2"></i>
                                                            <p class="font-medium"><?php echo date('d M, Y', strtotime($booking['arrival_time'])); ?></p>
                                                        </div>
                                                        <div class="flex items-center mt-1">
                                                            <i class="fas fa-clock text-green-600 mr-2"></i>
                                                            <p class="font-medium"><?php echo date('h:i A', strtotime($booking['arrival_time'])); ?></p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mt-4 flex justify-between items-center">
                                                    <div class="text-sm text-gray-500">
                                                        <i class="fas fa-ticket-alt mr-1"></i> Booking ID: <?php echo $booking['booking_reference']; ?>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <?php if ($is_today || $is_soon): ?>
                                                        <a href="../print_ticket.php?reference=<?php echo $booking['booking_reference']; ?>" class="inline-flex items-center text-gray-600 hover:text-gray-800 font-medium transition-colors text-sm bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-md">
                                                            <i class="fas fa-print mr-1"></i> Print
                                                        </a>
                                                        <?php endif; ?>
                                                        <a href="booking_details.php?reference=<?php echo $booking['booking_reference']; ?>" class="inline-flex items-center text-primary-600 hover:text-primary-800 font-medium transition-colors text-sm">
                                                            View Details <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-10">
                                <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-calendar-times text-3xl text-gray-400"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800 mb-2">No Upcoming Trips</h3>
                                <p class="text-gray-600 mb-6 max-w-md mx-auto">
                                    You don't have any upcoming trips scheduled. Ready to plan your next journey?
                                </p>
                                <a href="../index.php#search-form" class="inline-flex items-center justify-center bg-primary-600 hover:bg-primary-700 text-white font-medium py-3 px-6 rounded-lg transition-colors shadow-md">
                                    <i class="fas fa-search mr-2"></i> Find a Bus
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-lg font-bold text-gray-800 flex items-center">
                            <i class="fas fa-bolt text-primary-600 mr-2"></i> Quick Actions
                        </h2>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <a href="../index.php#search-form" class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-primary-50 to-primary-100 hover:from-primary-100 hover:to-primary-200 rounded-xl transition-all duration-300 group transform hover:-translate-y-1 hover:shadow-md">
                                <div class="w-14 h-14 rounded-full bg-white shadow-sm flex items-center justify-center text-primary-600 mb-3 group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-search text-xl"></i>
                                </div>
                                <span class="font-medium text-primary-700 text-center">Book a Trip</span>
                                <span class="text-xs text-primary-500 mt-1">Find buses</span>
                            </a>

                            <a href="bookings.php" class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 rounded-xl transition-all duration-300 group transform hover:-translate-y-1 hover:shadow-md">
                                <div class="w-14 h-14 rounded-full bg-white shadow-sm flex items-center justify-center text-blue-600 mb-3 group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-list text-xl"></i>
                                </div>
                                <span class="font-medium text-blue-700 text-center">My Bookings</span>
                                <span class="text-xs text-blue-500 mt-1">View history</span>
                            </a>

                            <a href="../routes.php" class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-xl transition-all duration-300 group transform hover:-translate-y-1 hover:shadow-md">
                                <div class="w-14 h-14 rounded-full bg-white shadow-sm flex items-center justify-center text-purple-600 mb-3 group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-route text-xl"></i>
                                </div>
                                <span class="font-medium text-purple-700 text-center">Explore Routes</span>
                                <span class="text-xs text-purple-500 mt-1">Popular destinations</span>
                            </a>

                            <a href="../contact.php" class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 hover:from-yellow-100 hover:to-yellow-200 rounded-xl transition-all duration-300 group transform hover:-translate-y-1 hover:shadow-md">
                                <div class="w-14 h-14 rounded-full bg-white shadow-sm flex items-center justify-center text-yellow-600 mb-3 group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-headset text-xl"></i>
                                </div>
                                <span class="font-medium text-yellow-700 text-center">Support</span>
                                <span class="text-xs text-yellow-500 mt-1">Get help</span>
                            </a>
                        </div>

                        <!-- Popular Destinations -->
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                                <i class="fas fa-star text-yellow-500 mr-2"></i> Popular Destinations
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                <a href="../index.php#search-form?destination=Mombasa" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-full text-sm font-medium text-gray-700 transition-colors">
                                    <i class="fas fa-map-marker-alt text-red-500 mr-1.5"></i> Mombasa
                                </a>
                                <a href="../index.php#search-form?destination=Kisumu" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-full text-sm font-medium text-gray-700 transition-colors">
                                    <i class="fas fa-map-marker-alt text-red-500 mr-1.5"></i> Kisumu
                                </a>
                                <a href="../index.php#search-form?destination=Nakuru" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-full text-sm font-medium text-gray-700 transition-colors">
                                    <i class="fas fa-map-marker-alt text-red-500 mr-1.5"></i> Nakuru
                                </a>
                                <a href="../index.php#search-form?destination=Eldoret" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-full text-sm font-medium text-gray-700 transition-colors">
                                    <i class="fas fa-map-marker-alt text-red-500 mr-1.5"></i> Eldoret
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Quick Action Button (Floating) -->
        <div class="fixed bottom-6 right-6 lg:hidden z-50">
            <button id="mobileActionBtn" class="w-14 h-14 rounded-full bg-primary-600 text-white shadow-lg flex items-center justify-center focus:outline-none">
                <i class="fas fa-plus text-xl"></i>
            </button>

            <div id="mobileActionMenu" class="hidden absolute bottom-16 right-0 bg-white rounded-lg shadow-xl w-56 overflow-hidden">
                <div class="p-3 border-b border-gray-100">
                    <h3 class="font-medium text-gray-800">Quick Actions</h3>
                </div>
                <div class="py-2">
                    <a href="../index.php#search-form" class="flex items-center px-4 py-3 hover:bg-gray-50">
                        <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 mr-3">
                            <i class="fas fa-search"></i>
                        </div>
                        <span>Book a Trip</span>
                    </a>
                    <a href="bookings.php" class="flex items-center px-4 py-3 hover:bg-gray-50">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mr-3">
                            <i class="fas fa-list"></i>
                        </div>
                        <span>My Bookings</span>
                    </a>
                    <a href="profile.php" class="flex items-center px-4 py-3 hover:bg-gray-50">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 mr-3">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <span>Edit Profile</span>
                    </a>
                    <a href="../contact.php" class="flex items-center px-4 py-3 hover:bg-gray-50">
                        <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 mr-3">
                            <i class="fas fa-headset"></i>
                        </div>
                        <span>Contact Support</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add JavaScript for mobile menu toggle -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileActionBtn = document.getElementById('mobileActionBtn');
        const mobileActionMenu = document.getElementById('mobileActionMenu');

        if (mobileActionBtn && mobileActionMenu) {
            mobileActionBtn.addEventListener('click', function() {
                mobileActionMenu.classList.toggle('hidden');

                // Change icon based on menu state
                const icon = mobileActionBtn.querySelector('i');
                if (mobileActionMenu.classList.contains('hidden')) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-plus');
                } else {
                    icon.classList.remove('fa-plus');
                    icon.classList.add('fa-times');
                }
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!mobileActionBtn.contains(event.target) && !mobileActionMenu.contains(event.target)) {
                    mobileActionMenu.classList.add('hidden');
                    const icon = mobileActionBtn.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-plus');
                }
            });
        }

        // Add animation classes to elements
        const animatedElements = document.querySelectorAll('.animate-on-load');
        animatedElements.forEach((element, index) => {
            setTimeout(() => {
                element.classList.add('opacity-100', 'translate-y-0');
            }, 100 * index);
        });
    });
</script>

<?php
// Include footer
require_once '../includes/templates/footer.php';
?>
