<?php
// Include session configuration
require_once '../config/session_config.php';

// Include functions
require_once '../includes/functions.php';

// Set page title
$page_title = "Manage Bookings";

// Include database connection
$conn = require_once '../config/database.php';

// Initialize variables
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Process status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $booking_id = intval($_POST['booking_id']);
    $new_status = $_POST['status'];
    $current_status = $_POST['current_status'];

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Update booking status
        $sql = "UPDATE bookings SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_status, $booking_id);
        $stmt->execute();
        $stmt->close();

        // If status changed to confirmed and current status is pending, update payment status
        if ($new_status == 'confirmed' && $current_status == 'pending') {
            $sql = "UPDATE payments SET status = 'completed', payment_date = NOW() WHERE booking_id = ? AND status = 'pending'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            $stmt->close();
        }

        // Log activity
        logActivity("Admin", "Updated booking #" . $booking_id . " status from " . $current_status . " to " . $new_status);

        // Commit transaction
        $conn->commit();

        // Set success message
        setFlashMessage("success", "Booking status updated successfully.");
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();

        // Set error message
        setFlashMessage("error", "Error updating booking status: " . $e->getMessage());
    }

    // Redirect to avoid form resubmission
    header("Location: bookings.php" . (empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING']));
    exit();
}

// Build query based on filters
$params = [];
$types = "";

$sql = "SELECT b.*,
        s.departure_time, s.arrival_time,
        r.origin, r.destination,
        bs.name AS bus_name, bs.registration_number,
        CONCAT(u.first_name, ' ', u.last_name) AS user_name, u.email AS user_email
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        JOIN buses bs ON s.bus_id = bs.id
        LEFT JOIN users u ON b.user_id = u.id
        WHERE 1=1";

// Add date range filter
$sql .= " AND DATE(b.created_at) BETWEEN ? AND ?";
$types .= "ss";
$params[] = $date_from;
$params[] = $date_to;

// Add status filter if selected
if (!empty($status_filter)) {
    $sql .= " AND b.status = ?";
    $types .= "s";
    $params[] = $status_filter;
}

// Add search filter if provided
if (!empty($search)) {
    $search_term = "%" . $search . "%";
    $sql .= " AND (b.booking_reference LIKE ? OR b.passenger_name LIKE ? OR r.origin LIKE ? OR r.destination LIKE ?)";
    $types .= "ssss";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

// Order by created_at descending
$sql .= " ORDER BY b.created_at DESC";

// Prepare and execute query
$bookings = [];

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    // Handle prepare error
    setFlashMessage("error", "Database error: " . $conn->error);
} else {
    // Bind parameters if any
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    // Execute the query
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row;
            }
        }
    } else {
        // Handle execute error
        setFlashMessage("error", "Query execution error: " . $stmt->error);
    }

    $stmt->close();
}

// Get booking statistics
$stats_sql = "SELECT
             COUNT(*) AS total_bookings,
             SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_bookings,
             SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_bookings,
             SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_bookings,
             SUM(CASE WHEN status = 'confirmed' THEN amount ELSE 0 END) AS total_revenue
             FROM bookings
             WHERE DATE(created_at) BETWEEN ? AND ?";

// Initialize stats with default values
$stats = [
    'total_bookings' => 0,
    'confirmed_bookings' => 0,
    'pending_bookings' => 0,
    'cancelled_bookings' => 0,
    'total_revenue' => 0
];

$stats_stmt = $conn->prepare($stats_sql);
if ($stats_stmt === false) {
    // Handle prepare error
    setFlashMessage("error", "Statistics query error: " . $conn->error);
} else {
    $stats_stmt->bind_param("ss", $date_from, $date_to);

    if ($stats_stmt->execute()) {
        $stats_result = $stats_stmt->get_result();
        if ($stats_result && $stats_result->num_rows > 0) {
            $stats = $stats_result->fetch_assoc();
        }
    } else {
        // Handle execute error
        setFlashMessage("error", "Statistics query execution error: " . $stats_stmt->error);
    }

    $stats_stmt->close();
}
?>

<?php
// Start output buffering to capture content for the template
ob_start();
?>

<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Manage Bookings</h1>
        <a href="index.php" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <!-- Display Flash Messages -->
    <?php displayFlashMessages(); ?>

    <!-- Booking Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-ticket-alt text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-600">Total Bookings</p>
                    <p class="text-xl font-bold"><?php echo $stats['total_bookings'] ?? 0; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-600">Confirmed</p>
                    <p class="text-xl font-bold"><?php echo $stats['confirmed_bookings'] ?? 0; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-600">Pending</p>
                    <p class="text-xl font-bold"><?php echo $stats['pending_bookings'] ?? 0; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-primary-100 text-primary-600">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-600">Revenue</p>
                    <p class="text-xl font-bold"><?php echo formatCurrency($stats['total_revenue'] ?? 0); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Filter Bookings</h2>

        <form method="get" action="" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>" class="form-input w-full">
            </div>

            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>" class="form-input w-full">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status" class="form-select w-full">
                    <option value="">All Statuses</option>
                    <option value="confirmed" <?php echo ($status_filter == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="cancelled" <?php echo ($status_filter == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>

            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <div class="flex">
                    <input type="text" id="search" name="search" value="<?php echo $search; ?>" placeholder="Reference, Name, Route..." class="form-input w-full rounded-r-none">
                    <button type="submit" class="bg-primary-600 hover:bg-primary-500 text-white px-4 rounded-r-md">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <!-- Clear Filters and Loading Indicator -->
            <div class="flex flex-col justify-end">
                <div class="flex space-x-2">
                    <?php if (!empty($status_filter) || !empty($search) || $date_from != date('Y-m-d', strtotime('-30 days')) || $date_to != date('Y-m-d')): ?>
                        <a href="bookings.php" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-md text-sm flex items-center">
                            <i class="fas fa-times mr-2"></i> Clear Filters
                        </a>
                    <?php endif; ?>
                    <div id="loadingIndicator" class="hidden bg-blue-600 text-white px-4 py-2 rounded-md text-sm flex items-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Filtering...
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Bookings List -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">Bookings</h2>

        <?php if (empty($bookings)): ?>
            <div class="text-center py-8">
                <div class="text-5xl text-gray-300 mb-4">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">No Bookings Found</h3>
                <p class="text-gray-600">
                    No bookings match your filter criteria. Try adjusting your filters.
                </p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Total: <?php echo count($bookings); ?> bookings</h3>
                    <div class="flex space-x-2">
                        <button id="printBookings" class="btn-sm bg-gray-600 hover:bg-gray-500 text-white rounded flex items-center">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                        <button id="exportBookings" class="btn-sm bg-green-600 hover:bg-green-500 text-white rounded flex items-center">
                            <i class="fas fa-file-export mr-1"></i> Export
                        </button>
                    </div>
                </div>

                <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden">
                    <thead>
                        <tr class="bg-gray-50 text-gray-700 uppercase text-xs">
                            <th class="py-3 px-4 font-semibold text-left border-b">Reference</th>
                            <th class="py-3 px-4 font-semibold text-left border-b">Passenger</th>
                            <th class="py-3 px-4 font-semibold text-left border-b">Route</th>
                            <th class="py-3 px-4 font-semibold text-left border-b">Travel Date</th>
                            <th class="py-3 px-4 font-semibold text-left border-b">Amount</th>
                            <th class="py-3 px-4 font-semibold text-left border-b">Status</th>
                            <th class="py-3 px-4 font-semibold text-left border-b">Created</th>
                            <th class="py-3 px-4 font-semibold text-left border-b">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($bookings as $booking): ?>
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="py-3 px-4 font-medium text-primary-700">
                                    <?php echo $booking['booking_reference']; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium"><?php echo $booking['passenger_name']; ?></div>
                                    <?php if (!empty($booking['user_name'])): ?>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-user mr-1"></i> <?php echo $booking['user_name']; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-route text-gray-400 mr-2"></i>
                                        <span><?php echo $booking['origin']; ?> to <?php echo $booking['destination']; ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center">
                                        <i class="far fa-calendar-alt text-gray-400 mr-2"></i>
                                        <span><?php echo date('d M Y, H:i', strtotime($booking['departure_time'])); ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-medium">
                                    <?php echo formatCurrency($booking['amount']); ?>
                                </td>
                                <td class="py-3 px-4">
                                    <?php
                                    $status_color = '';
                                    $status_bg = '';
                                    $status_icon = '';

                                    switch ($booking['status']) {
                                        case 'confirmed':
                                            $status_color = 'text-green-800';
                                            $status_bg = 'bg-green-100';
                                            $status_icon = 'fa-check-circle';
                                            break;
                                        case 'pending':
                                            $status_color = 'text-yellow-800';
                                            $status_bg = 'bg-yellow-100';
                                            $status_icon = 'fa-clock';
                                            break;
                                        case 'cancelled':
                                            $status_color = 'text-red-800';
                                            $status_bg = 'bg-red-100';
                                            $status_icon = 'fa-times-circle';
                                            break;
                                        case 'completed':
                                            $status_color = 'text-blue-800';
                                            $status_bg = 'bg-blue-100';
                                            $status_icon = 'fa-check-double';
                                            break;
                                        default:
                                            $status_color = 'text-gray-800';
                                            $status_bg = 'bg-gray-100';
                                            $status_icon = 'fa-info-circle';
                                    }
                                    ?>
                                    <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full <?php echo $status_bg . ' ' . $status_color; ?>">
                                        <i class="fas <?php echo $status_icon; ?> mr-1"></i>
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    <div class="flex items-center">
                                        <i class="far fa-clock text-gray-400 mr-2"></i>
                                        <span><?php echo date('d M Y, H:i', strtotime($booking['created_at'])); ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="booking_details.php?id=<?php echo $booking['id']; ?>" class="btn-sm bg-primary-600 hover:bg-primary-500 text-white rounded flex items-center px-3 py-1.5 text-xs font-medium" title="View Details">
                                            <i class="fas fa-eye mr-1.5"></i> View
                                        </a>

                                        <button type="button" class="btn-sm bg-blue-600 hover:bg-blue-500 text-white rounded flex items-center px-3 py-1.5 text-xs font-medium qr-code-btn" data-reference="<?php echo $booking['booking_reference']; ?>" title="Show QR Code">
                                            <i class="fas fa-qrcode mr-1.5"></i> QR Code
                                        </button>

                                        <button type="button" class="btn-sm bg-purple-600 hover:bg-purple-500 text-white rounded flex items-center px-3 py-1.5 text-xs font-medium print-ticket-btn" data-id="<?php echo $booking['id']; ?>" title="Print Ticket">
                                            <i class="fas fa-ticket-alt mr-1.5"></i> Ticket
                                        </button>

                                        <div class="dropdown relative inline-block">
                                            <button type="button" class="btn-sm bg-gray-600 hover:bg-gray-500 text-white rounded flex items-center px-3 py-1.5 text-xs font-medium dropdown-toggle">
                                                <i class="fas fa-ellipsis-h mr-1.5"></i> More
                                            </button>
                                            <div class="dropdown-menu hidden absolute right-0 mt-1 bg-white rounded-md shadow-lg overflow-hidden z-20 w-48 border border-gray-200">
                                                <?php if ($booking['status'] != 'cancelled' && strtotime($booking['departure_time']) > time()): ?>
                                                    <form method="post" action="" class="w-full">
                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                        <input type="hidden" name="current_status" value="<?php echo $booking['status']; ?>">
                                                        <input type="hidden" name="status" value="cancelled">
                                                        <button type="submit" name="update_status" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center" onclick="return confirm('Are you sure you want to cancel this booking?');">
                                                            <i class="fas fa-ban mr-2"></i> Cancel Booking
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if ($booking['status'] == 'pending'): ?>
                                                    <form method="post" action="" class="w-full">
                                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                        <input type="hidden" name="current_status" value="<?php echo $booking['status']; ?>">
                                                        <input type="hidden" name="status" value="confirmed">
                                                        <button type="submit" name="update_status" class="w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-green-50 flex items-center" onclick="return confirm('Are you sure you want to confirm this booking?');">
                                                            <i class="fas fa-check mr-2"></i> Confirm Booking
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <a href="mailto:<?php echo $booking['user_email'] ?? ''; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                                                    <i class="fas fa-envelope mr-2"></i> Email Passenger
                                                </a>

                                                <a href="booking_history.php?reference=<?php echo $booking['booking_reference']; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center">
                                                    <i class="fas fa-history mr-2"></i> View History
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- QR Code Modal -->
                <div id="qrCodeModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
                    <div class="bg-white rounded-lg p-6 max-w-md w-full">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold">Booking QR Code</h3>
                            <button type="button" class="text-gray-500 hover:text-gray-700" id="closeQrModal">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="text-center p-4" id="qrCodeContainer">
                            <!-- QR code will be inserted here -->
                        </div>
                        <div class="text-center mt-4">
                            <p class="text-sm text-gray-600 mb-2">Scan this QR code to view booking details</p>
                            <button type="button" class="btn-primary" id="printQrCode">
                                <i class="fas fa-print mr-2"></i> Print QR Code
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Get the buffered content
$admin_content = ob_get_clean();

// Include the admin template
require_once '../includes/templates/admin_template.php';
?>

<!-- Custom Styles -->
<style>
    /* Dropdown Styles */
    .dropdown-menu {
        transform: translateY(10px);
        transition: all 0.2s ease-in-out;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .dropdown-menu:before {
        content: '';
        position: absolute;
        top: -6px;
        right: 16px;
        width: 12px;
        height: 12px;
        background-color: white;
        transform: rotate(45deg);
        border-left: 1px solid #e5e7eb;
        border-top: 1px solid #e5e7eb;
    }

    /* Action Button Styles */
    .btn-sm {
        transition: all 0.2s ease;
    }

    .btn-sm:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .btn-sm:active {
        transform: translateY(0);
        box-shadow: none;
    }

    /* Print Styles */
    @media print {
        body {
            background-color: white !important;
        }
        nav, .breadcrumb, .admin-sidebar, .bg-primary-700, .bg-white.border-b,
        #printBookings, #exportBookings, .btn-sm, .btn-primary, .btn-secondary,
        form[method="get"], .qr-code-btn, .print-ticket-btn {
            display: none !important;
        }
        .container, .admin-container, .grid {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .md\:col-span-4 {
            grid-column: span 5 / span 5 !important;
        }
        .shadow-md, .rounded-lg {
            box-shadow: none !important;
            border-radius: 0 !important;
        }
        .bg-white {
            background-color: white !important;
            border: none !important;
        }
        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        th, td {
            border: 1px solid #ddd !important;
            padding: 8px !important;
            font-size: 12px !important;
        }
        th {
            background-color: #f2f2f2 !important;
            font-weight: bold !important;
        }
        .text-3xl {
            font-size: 24px !important;
        }
        .text-xl {
            font-size: 18px !important;
        }
        .py-6, .py-4, .py-3, .py-2 {
            padding-top: 8px !important;
            padding-bottom: 8px !important;
        }
        .px-6, .px-4, .px-3, .px-2 {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }
        .mb-6, .mb-4 {
            margin-bottom: 16px !important;
        }
        .grid-cols-1.md\:grid-cols-4 {
            display: none !important;
        }
        .overflow-x-auto {
            overflow: visible !important;
        }
        @page {
            size: landscape;
            margin: 1cm;
        }
    }
</style>

<!-- QR Code Library -->
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    const filterForm = document.querySelector('form[method="get"]');
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    const statusSelect = document.getElementById('status');
    const searchInput = document.getElementById('search');
    const loadingIndicator = document.getElementById('loadingIndicator');

    // Function to validate date range and show loading indicator
    function validateAndSubmitForm() {
        const dateFrom = dateFromInput ? dateFromInput.value : '';
        const dateTo = dateToInput ? dateToInput.value : '';
        
        // Validate date range
        if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
            alert('"From Date" cannot be later than "To Date". Please check your date selection.');
            return false;
        }
        
        // Show loading indicator
        if (loadingIndicator) {
            loadingIndicator.classList.remove('hidden');
        }
        
        filterForm.submit();
        return true;
    }

    // Add event listeners for auto-submit on filter changes
    if (dateFromInput) {
        dateFromInput.addEventListener('change', function() {
            validateAndSubmitForm();
        });
    }

    if (dateToInput) {
        dateToInput.addEventListener('change', function() {
            validateAndSubmitForm();
        });
    }

    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            validateAndSubmitForm();
        });
    }

    // For search input, add a small delay to avoid too many requests
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                validateAndSubmitForm();
            }, 1000); // Wait 1 second after user stops typing
        });
    }

    // Also validate and show loading indicator when form is submitted manually
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            validateAndSubmitForm();
        });
    }

    // QR Code Modal Functionality
    const qrCodeModal = document.getElementById('qrCodeModal');
    const qrCodeContainer = document.getElementById('qrCodeContainer');
    const closeQrModal = document.getElementById('closeQrModal');
    const qrCodeButtons = document.querySelectorAll('.qr-code-btn');
    const printQrCode = document.getElementById('printQrCode');

    // Print Bookings Button
    const printBookingsBtn = document.getElementById('printBookings');

    // Export Bookings Button
    const exportBookingsBtn = document.getElementById('exportBookings');

    // Print Ticket Buttons
    const printTicketButtons = document.querySelectorAll('.print-ticket-btn');

    // Dropdown Toggle Buttons
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

    // Show QR Code Modal
    qrCodeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookingReference = this.getAttribute('data-reference');
            generateQRCode(bookingReference);
            qrCodeModal.classList.remove('hidden');
        });
    });

    // Close QR Code Modal
    closeQrModal.addEventListener('click', function() {
        qrCodeModal.classList.add('hidden');
    });

    // Close modal when clicking outside
    qrCodeModal.addEventListener('click', function(e) {
        if (e.target === qrCodeModal) {
            qrCodeModal.classList.add('hidden');
        }
    });

    // Generate QR Code
    function generateQRCode(bookingReference) {
        // Clear previous QR code
        qrCodeContainer.innerHTML = '';

        // Generate QR code
        const qr = qrcode(0, 'M');
        const bookingUrl = `${window.location.origin}/isioloraha/booking_details.php?reference=${bookingReference}`;
        qr.addData(bookingUrl);
        qr.make();

        // Create QR code image
        const qrImage = qr.createImgTag(5);

        // Add booking reference and URL below QR code
        qrCodeContainer.innerHTML = `
            ${qrImage}
            <div class="mt-4">
                <p class="font-bold">${bookingReference}</p>
                <p class="text-xs text-gray-500 mt-1 break-all">${bookingUrl}</p>
            </div>
        `;
    }

    // Print QR Code
    printQrCode.addEventListener('click', function() {
        const printWindow = window.open('', '_blank');
        const qrCodeImage = qrCodeContainer.querySelector('img').src;
        const bookingReference = qrCodeContainer.querySelector('.font-bold').textContent;

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Booking QR Code - ${bookingReference}</title>
                <style>
                    body {
                        font-family: 'Arial', sans-serif;
                        text-align: center;
                        padding: 20px;
                    }
                    .logo {
                        max-width: 150px;
                        margin-bottom: 20px;
                    }
                    .qr-container {
                        margin: 20px auto;
                        padding: 20px;
                        border: 1px solid #ddd;
                        border-radius: 10px;
                        max-width: 300px;
                    }
                    .qr-image {
                        width: 200px;
                        height: 200px;
                    }
                    .reference {
                        font-size: 18px;
                        font-weight: bold;
                        margin: 15px 0 5px;
                    }
                    .instructions {
                        font-size: 12px;
                        color: #666;
                        margin-top: 20px;
                    }
                </style>
            </head>
            <body>
                <img src="${window.location.origin}/isioloraha/assets/images/isioloraha logo.png" alt="Isiolo Raha Logo" class="logo">
                <h2>Booking QR Code</h2>
                <div class="qr-container">
                    <img src="${qrCodeImage}" class="qr-image">
                    <p class="reference">Ref: ${bookingReference}</p>
                </div>
                <p class="instructions">Scan this QR code to view booking details</p>
            </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.focus();

        // Print after a short delay to ensure content is loaded
        setTimeout(() => {
            printWindow.print();
        }, 500);
    });

    // Print Bookings
    printBookingsBtn.addEventListener('click', function() {
        window.print();
    });

    // Export Bookings to CSV
    exportBookingsBtn.addEventListener('click', function() {
        // Get table data
        const table = document.querySelector('table');
        const rows = table.querySelectorAll('tbody tr');

        // Create CSV content
        let csvContent = 'Reference,Passenger,Route,Travel Date,Amount,Status,Created\n';

        rows.forEach(row => {
            const reference = row.cells[0].textContent.trim();
            const passenger = row.cells[1].querySelector('.font-medium').textContent.trim();
            const route = row.cells[2].textContent.trim();
            const travelDate = row.cells[3].textContent.trim();
            const amount = row.cells[4].textContent.trim();
            const status = row.cells[5].textContent.trim();
            const created = row.cells[6].textContent.trim();

            // Escape commas in fields
            const escapedRow = [
                `"${reference}"`,
                `"${passenger}"`,
                `"${route}"`,
                `"${travelDate}"`,
                `"${amount}"`,
                `"${status}"`,
                `"${created}"`
            ].join(',');

            csvContent += escapedRow + '\n';
        });

        // Create download link
        const encodedUri = encodeURI('data:text/csv;charset=utf-8,' + csvContent);
        const link = document.createElement('a');
        link.setAttribute('href', encodedUri);
        link.setAttribute('download', `bookings_export_${new Date().toISOString().slice(0,10)}.csv`);
        document.body.appendChild(link);

        // Trigger download
        link.click();

        // Clean up
        document.body.removeChild(link);
    });

    // Print Ticket
    printTicketButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = this.getAttribute('data-id');
            window.open(`print_ticket.php?id=${bookingId}`, '_blank');
        });
    });

    // Handle Dropdown Toggles
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = this.nextElementSibling;

            // Close all other dropdowns first
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu !== dropdown) {
                    menu.classList.add('hidden');
                }
            });

            // Toggle current dropdown
            dropdown.classList.toggle('hidden');
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.add('hidden');
        });
    });

    // Prevent dropdown from closing when clicking inside it
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        menu.addEventListener('click', function(e) {
            // Only stop propagation if not clicking a form button
            if (!e.target.matches('button[type="submit"]')) {
                e.stopPropagation();
            }
        });
    });
});
</script>
