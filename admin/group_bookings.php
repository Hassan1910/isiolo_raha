<?php
// Include session configuration
require_once '../config/session_config.php';

// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Set message
    setFlashMessage("error", "You do not have permission to access this page.");

    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Include database connection
$conn = require_once '../config/database.php';

// Include functions
require_once '../includes/functions.php';

// Initialize variables
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query
$sql = "SELECT gb.*, 
        s.departure_time, 
        r.origin, r.destination,
        bs.name AS bus_name,
        CONCAT(u.first_name, ' ', u.last_name) AS user_name
        FROM group_bookings gb
        JOIN schedules s ON gb.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        JOIN buses bs ON s.bus_id = bs.id
        JOIN users u ON gb.user_id = u.id
        WHERE 1=1";

$params = [];
$types = "";

// Add filters
if (!empty($status_filter)) {
    $sql .= " AND gb.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if (!empty($search)) {
    $sql .= " AND (gb.booking_reference LIKE ? OR gb.group_name LIKE ? OR gb.contact_person LIKE ? OR gb.contact_phone LIKE ? OR r.origin LIKE ? OR r.destination LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssssss";
}

if (!empty($date_from)) {
    $sql .= " AND DATE(s.departure_time) >= ?";
    $params[] = $date_from;
    $types .= "s";
}

if (!empty($date_to)) {
    $sql .= " AND DATE(s.departure_time) <= ?";
    $params[] = $date_to;
    $types .= "s";
}

// Add order by
$sql .= " ORDER BY s.departure_time DESC";

// Prepare and execute query
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$group_bookings = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $group_bookings[] = $row;
    }
}

$stmt->close();

// Get booking statistics
$sql = "SELECT
        COUNT(*) AS total_bookings,
        SUM(CASE WHEN s.departure_time > NOW() AND gb.status != 'cancelled' THEN 1 ELSE 0 END) AS upcoming_bookings,
        SUM(CASE WHEN s.departure_time < NOW() AND gb.status = 'confirmed' THEN 1 ELSE 0 END) AS completed_bookings,
        SUM(CASE WHEN gb.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_bookings,
        SUM(gb.total_amount) AS total_revenue
        FROM group_bookings gb
        JOIN schedules s ON gb.schedule_id = s.id";

$result = $conn->query($sql);
$stats = $result->fetch_assoc();

// Set page title
$page_title = "Group Bookings";

// Include header
require_once 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-users"></i> Group Bookings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Group Bookings</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $stats['total_bookings'] ?? 0; ?></h3>
                            <p>Total Group Bookings</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $stats['upcoming_bookings'] ?? 0; ?></h3>
                            <p>Upcoming Group Bookings</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $stats['completed_bookings'] ?? 0; ?></h3>
                            <p>Completed Group Bookings</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo formatCurrency($stats['total_revenue'] ?? 0); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filters</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="search">Search</label>
                                    <input type="text" class="form-control" id="search" name="search" placeholder="Reference, Group, Contact..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status_filter">Status</label>
                                    <select class="form-control" id="status_filter" name="status">
                                        <option value="">All Statuses</option>
                                        <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo ($status_filter == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="cancelled" <?php echo ($status_filter == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        <option value="completed" <?php echo ($status_filter == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_from">From Date</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_to">To Date</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                                <a href="group_bookings.php" class="btn btn-default">
                                    <i class="fas fa-sync"></i> Reset
                                </a>
                                <a href="create_group_booking.php" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Create Group Booking
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Group Bookings Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Group Bookings List</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Group Name</th>
                                    <th>Contact Person</th>
                                    <th>Route</th>
                                    <th>Departure</th>
                                    <th>Passengers</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($group_bookings)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No group bookings found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($group_bookings as $booking): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($booking['booking_reference']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['group_name']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($booking['contact_person']); ?><br>
                                                <small><?php echo htmlspecialchars($booking['contact_phone']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($booking['origin'] . ' to ' . $booking['destination']); ?></td>
                                            <td><?php echo date('d M Y, h:i A', strtotime($booking['departure_time'])); ?></td>
                                            <td><?php echo $booking['total_passengers']; ?></td>
                                            <td><?php echo formatCurrency($booking['total_amount']); ?></td>
                                            <td>
                                                <?php if ($booking['status'] == 'confirmed'): ?>
                                                    <span class="badge badge-success">Confirmed</span>
                                                <?php elseif ($booking['status'] == 'pending'): ?>
                                                    <span class="badge badge-warning">Pending</span>
                                                <?php elseif ($booking['status'] == 'cancelled'): ?>
                                                    <span class="badge badge-danger">Cancelled</span>
                                                <?php else: ?>
                                                    <span class="badge badge-info">Completed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="group_booking_details.php?reference=<?php echo $booking['booking_reference']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="../print_group_ticket.php?reference=<?php echo $booking['booking_reference']; ?>" target="_blank" class="btn btn-sm btn-default">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmCancel('<?php echo $booking['booking_reference']; ?>')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    function confirmCancel(reference) {
        if (confirm('Are you sure you want to cancel this group booking?')) {
            window.location.href = 'cancel_group_booking.php?reference=' + reference;
        }
    }
</script>

<?php
// Include footer
require_once 'includes/footer.php';
?>
