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
    setFlashMessage("error", "Please login to access your bookings.");

    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Set page title
$page_title = "My Bookings";

// Include header
require_once '../includes/templates/header.php';

// Include database connection
$conn = require_once '../config/database.php';

// Initialize variables
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get user's bookings
$sql = "SELECT DISTINCT b.booking_reference,
        MIN(s.departure_time) AS departure_time,
        MIN(r.origin) AS origin,
        MIN(r.destination) AS destination,
        COUNT(b.id) AS total_seats,
        SUM(b.amount) AS total_amount,
        MIN(b.status) AS status,
        MAX(b.created_at) AS booking_date
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        WHERE b.user_id = ?";

// Add status filter if provided
if (!empty($status_filter)) {
    $sql .= " AND b.status = ?";
}

// Add search filter if provided
if (!empty($search)) {
    $sql .= " AND (b.booking_reference LIKE ? OR r.origin LIKE ? OR r.destination LIKE ?)";
}

$sql .= " GROUP BY b.booking_reference ORDER BY MAX(b.created_at) DESC";

$stmt = $conn->prepare($sql);

// Bind parameters based on filters
if (!empty($status_filter) && !empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("issss", $_SESSION['user_id'], $status_filter, $search_param, $search_param, $search_param);
} elseif (!empty($status_filter)) {
    $stmt->bind_param("is", $_SESSION['user_id'], $status_filter);
} elseif (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("isss", $_SESSION['user_id'], $search_param, $search_param, $search_param);
} else {
    $stmt->bind_param("i", $_SESSION['user_id']);
}

$stmt->execute();
$result = $stmt->get_result();
$bookings = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

$stmt->close();

// Get booking statistics
$sql = "SELECT
        COUNT(DISTINCT booking_reference) AS total_bookings,
        SUM(CASE WHEN s.departure_time > NOW() AND b.status != 'cancelled' THEN 1 ELSE 0 END) AS upcoming_bookings,
        SUM(CASE WHEN s.departure_time < NOW() AND b.status = 'confirmed' THEN 1 ELSE 0 END) AS completed_bookings,
        SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_bookings,
        SUM(b.amount) AS total_spent
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        WHERE b.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$stmt->close();
?>

<div class="bg-gray-100 py-6">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-ticket-alt text-primary-500 mr-3"></i> My Bookings
                    </h1>
                    <p class="text-gray-500 mt-1">View and manage all your bus bookings in one place</p>
                </div>
                <div class="flex space-x-3">
                    <a href="../index.php#search-form" class="btn-primary flex items-center">
                        <i class="fas fa-plus mr-2"></i> New Booking
                    </a>
                    <a href="dashboard.php" class="btn-secondary flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Booking Statistics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Bookings -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 shadow-sm">
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
            </div>

            <!-- Upcoming Bookings -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 shadow-sm">
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
            </div>

            <!-- Completed Bookings -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 shadow-sm">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm font-medium">Completed Trips</p>
                        <div class="flex items-baseline">
                            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['completed_bookings'] ?? 0; ?></p>
                            <span class="ml-2 text-xs font-medium text-gray-500">traveled</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Spent -->
            <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-primary-500 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-primary-100 text-primary-600 shadow-sm">
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
            </div>
        </div>

        <!-- Booking Filters -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <div class="mb-4 flex flex-wrap items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-700 mb-2 md:mb-0">
                    <i class="fas fa-filter text-primary-500 mr-2"></i> Filter Bookings
                </h3>

                <!-- Quick Filter Buttons -->
                <div class="flex flex-wrap gap-2">
                    <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium <?php echo empty($status_filter) && empty($search) ? 'bg-primary-100 text-primary-700 border border-primary-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> transition-colors duration-200">
                        <i class="fas fa-list-ul mr-1.5"></i> All
                    </a>
                    <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?status=confirmed" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium <?php echo $status_filter === 'confirmed' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> transition-colors duration-200">
                        <i class="fas fa-check-circle mr-1.5"></i> Confirmed
                    </a>
                    <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?status=pending" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium <?php echo $status_filter === 'pending' ? 'bg-yellow-100 text-yellow-700 border border-yellow-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> transition-colors duration-200">
                        <i class="fas fa-clock mr-1.5"></i> Pending
                    </a>
                    <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?status=cancelled" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium <?php echo $status_filter === 'cancelled' ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?> transition-colors duration-200">
                        <i class="fas fa-times-circle mr-1.5"></i> Cancelled
                    </a>
                </div>
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="mt-4">
                <div class="flex flex-col md:flex-row gap-4">
                    <!-- Status Filter -->
                    <div class="flex-1">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-input bg-gray-50 border-gray-300 focus:ring-primary-500 focus:border-primary-500 rounded-lg shadow-sm">
                            <option value="">All Status</option>
                            <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="flex-1">
                        <label for="search" class="form-label">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" name="search" id="search" class="form-input pl-10 bg-gray-50 border-gray-300 focus:ring-primary-500 focus:border-primary-500 rounded-lg shadow-sm" placeholder="Search by reference, origin, destination..." value="<?php echo $search; ?>">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-end">
                        <button type="submit" class="btn-primary w-full md:w-auto flex items-center justify-center">
                            <i class="fas fa-filter mr-2"></i> Apply Filters
                        </button>
                    </div>
                </div>

                <?php if (!empty($status_filter) || !empty($search)): ?>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-gray-600 mr-2">Active filters:</span>
                    <?php if (!empty($status_filter)): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-2">
                        Status: <?php echo ucfirst($status_filter); ?>
                        <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?><?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>" class="ml-1 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times-circle"></i>
                        </a>
                    </span>
                    <?php endif; ?>

                    <?php if (!empty($search)): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        Search: "<?php echo htmlspecialchars($search); ?>"
                        <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?><?php echo !empty($status_filter) ? '?status=' . urlencode($status_filter) : ''; ?>" class="ml-1 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times-circle"></i>
                        </a>
                    </span>
                    <?php endif; ?>

                    <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="ml-auto text-primary-600 hover:text-primary-700 text-sm font-medium">
                        <i class="fas fa-undo mr-1"></i> Clear all filters
                    </a>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Bookings List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-800">
                    <i class="fas fa-history text-primary-500 mr-2"></i> Booking History
                </h2>
                <span class="text-sm text-gray-500">
                    <?php echo count($bookings); ?> booking<?php echo count($bookings) !== 1 ? 's' : ''; ?> found
                </span>
            </div>

            <?php if (empty($bookings)): ?>
                <div class="text-center py-16 px-4">
                    <div class="bg-gray-50 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-ticket-alt text-5xl text-gray-300"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-gray-800">No Bookings Found</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        <?php if (!empty($status_filter) || !empty($search)): ?>
                            No bookings match your current filters. Try adjusting your search criteria.
                        <?php else: ?>
                            You haven't made any bookings yet. Start by booking a trip to your favorite destination!
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($status_filter) || !empty($search)): ?>
                        <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="btn-secondary mr-2">
                            <i class="fas fa-undo mr-2"></i> Clear Filters
                        </a>
                    <?php endif; ?>
                    <a href="../index.php#search-form" class="btn-primary">
                        <i class="fas fa-search mr-2"></i> Find a Bus
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Travel Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seats</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($bookings as $booking): ?>
                                <?php
                                    $is_upcoming = strtotime($booking['departure_time']) > time();
                                    $row_class = $is_upcoming ? 'hover:bg-green-50' : 'hover:bg-gray-50';
                                ?>
                                <tr class="<?php echo $row_class; ?> transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo $booking['booking_reference']; ?></div>
                                        <div class="text-xs text-gray-500"><?php echo date('d M Y', strtotime($booking['booking_date'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo $booking['origin']; ?></div>
                                                <div class="flex items-center text-xs text-gray-500">
                                                    <i class="fas fa-long-arrow-alt-right text-gray-400 mx-1"></i>
                                                    <?php echo $booking['destination']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo date('d M, Y', strtotime($booking['departure_time'])); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo date('h:i A', strtotime($booking['departure_time'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <i class="fas fa-user-friends text-gray-400 mr-1"></i> <?php echo $booking['total_seats']; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo formatCurrency($booking['total_amount']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($booking['status'] === 'confirmed'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                <i class="fas fa-check-circle mr-1"></i> Confirmed
                                            </span>
                                        <?php elseif ($booking['status'] === 'pending'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                <i class="fas fa-clock mr-1"></i> Pending
                                            </span>
                                        <?php elseif ($booking['status'] === 'cancelled'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                                <i class="fas fa-times-circle mr-1"></i> Cancelled
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                                <i class="fas fa-info-circle mr-1"></i> <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <a href="../booking_confirmation.php?reference=<?php echo $booking['booking_reference']; ?>" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-150">
                                                <i class="fas fa-eye mr-1.5"></i> View
                                            </a>

                                            <?php if ($booking['status'] === 'confirmed'): ?>
                                                <a href="../print_ticket.php?reference=<?php echo $booking['booking_reference']; ?>" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-150" target="_blank">
                                                    <i class="fas fa-print mr-1.5"></i> Print
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Placeholder - Can be implemented if needed -->
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Showing <span class="font-medium"><?php echo count($bookings); ?></span> booking<?php echo count($bookings) !== 1 ? 's' : ''; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../includes/templates/footer.php';
?>

<!-- Custom JavaScript for Bookings Page -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add animation to the stats cards
    const statCards = document.querySelectorAll('.border-l-4');
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('animate-fadeIn');
        }, 100 * index);
    });

    // Add loading state to filter form
    const filterForm = document.querySelector('form');
    if (filterForm) {
        filterForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Applying...';
            }

            // Add loading overlay
            const loadingOverlay = document.createElement('div');
            loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            loadingOverlay.innerHTML = `
                <div class="bg-white p-5 rounded-lg shadow-lg flex items-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mr-3"></div>
                    <p class="text-gray-700">Loading your bookings...</p>
                </div>
            `;
            document.body.appendChild(loadingOverlay);
        });
    }

    // Add hover effects to table rows
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.classList.add('shadow-sm');
            this.style.transform = 'translateY(-1px)';
        });

        row.addEventListener('mouseleave', function() {
            this.classList.remove('shadow-sm');
            this.style.transform = 'translateY(0)';
        });
    });

    // Add click animation to action buttons
    const actionButtons = document.querySelectorAll('.inline-flex');
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.classList.add('scale-95');
            setTimeout(() => {
                this.classList.remove('scale-95');
            }, 100);
        });
    });

    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });
});

// Add custom animation class
document.head.insertAdjacentHTML('beforeend', `
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
`);
</script>
