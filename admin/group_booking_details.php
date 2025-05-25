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

// Check if reference is provided
if (!isset($_GET['reference'])) {
    // Set message
    setFlashMessage("error", "Invalid booking reference.");

    // Redirect to group bookings page
    header("Location: group_bookings.php");
    exit();
}

// Get reference from URL
$booking_reference = $_GET['reference'];

// Get group booking details
$sql = "SELECT gb.*, 
        s.departure_time, s.arrival_time, s.fare,
        r.origin, r.destination, r.distance, r.duration,
        bs.name AS bus_name, bs.registration_number, bs.type AS bus_type, bs.capacity,
        CONCAT(u.first_name, ' ', u.last_name) AS user_name, u.email AS user_email, u.phone AS user_phone
        FROM group_bookings gb
        JOIN schedules s ON gb.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        JOIN buses bs ON s.bus_id = bs.id
        JOIN users u ON gb.user_id = u.id
        WHERE gb.booking_reference = ?";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("s", $booking_reference);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $group_booking = $result->fetch_assoc();
        } else {
            setFlashMessage("error", "Group booking not found.");
            header("Location: group_bookings.php");
            exit();
        }
    } else {
        setFlashMessage("error", "Something went wrong. Please try again later.");
        header("Location: group_bookings.php");
        exit();
    }

    // Close statement
    $stmt->close();
}

// Get individual bookings
$sql = "SELECT b.*, p.payment_method, p.status AS payment_status, p.transaction_reference
        FROM bookings b
        LEFT JOIN payments p ON b.id = p.booking_id
        WHERE b.booking_reference = ?
        ORDER BY b.seat_number";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("s", $booking_reference);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $bookings = [];

        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
    } else {
        setFlashMessage("error", "Something went wrong. Please try again later.");
        header("Location: group_bookings.php");
        exit();
    }

    // Close statement
    $stmt->close();
}

// Process form submission for status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Update group booking status
        $sql = "UPDATE group_bookings SET status = ? WHERE booking_reference = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $new_status, $booking_reference);
        $stmt->execute();
        $stmt->close();
        
        // Update individual bookings status
        $sql = "UPDATE bookings SET status = ? WHERE booking_reference = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $new_status, $booking_reference);
        $stmt->execute();
        $stmt->close();
        
        // Log activity
        logActivity("Admin", "Updated group booking status: " . $booking_reference . " to " . $new_status);
        
        // Commit transaction
        $conn->commit();
        
        // Set success message
        setFlashMessage("success", "Group booking status updated successfully.");
        
        // Redirect to refresh the page
        header("Location: group_booking_details.php?reference=" . $booking_reference);
        exit();
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        
        // Set error message
        setFlashMessage("error", "Error updating status: " . $e->getMessage());
    }
}

// Set page title
$page_title = "Group Booking Details";

// Include header
require_once 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-users"></i> Group Booking Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="group_bookings.php">Group Bookings</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Success Message -->
            <?php if (isset($_SESSION['flash_messages']['success'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-check"></i> Success!</h5>
                    <?php echo $_SESSION['flash_messages']['success']; ?>
                </div>
                <?php unset($_SESSION['flash_messages']['success']); ?>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if (isset($_SESSION['flash_messages']['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-ban"></i> Error!</h5>
                    <?php echo $_SESSION['flash_messages']['error']; ?>
                </div>
                <?php unset($_SESSION['flash_messages']['error']); ?>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-4">
                    <!-- Group Booking Info -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Group Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <h2><?php echo htmlspecialchars($group_booking['group_name']); ?></h2>
                                <span class="badge badge-<?php echo getStatusBadgeClass($group_booking['status']); ?> p-2">
                                    <?php echo ucfirst($group_booking['status']); ?>
                                </span>
                            </div>
                            
                            <strong><i class="fas fa-ticket-alt mr-1"></i> Booking Reference</strong>
                            <p class="text-muted"><?php echo htmlspecialchars($booking_reference); ?></p>
                            
                            <hr>
                            
                            <strong><i class="fas fa-user mr-1"></i> Contact Person</strong>
                            <p class="text-muted"><?php echo htmlspecialchars($group_booking['contact_person']); ?></p>
                            
                            <hr>
                            
                            <strong><i class="fas fa-phone mr-1"></i> Contact Phone</strong>
                            <p class="text-muted"><?php echo htmlspecialchars($group_booking['contact_phone']); ?></p>
                            
                            <?php if (!empty($group_booking['contact_email'])): ?>
                                <hr>
                                <strong><i class="fas fa-envelope mr-1"></i> Contact Email</strong>
                                <p class="text-muted"><?php echo htmlspecialchars($group_booking['contact_email']); ?></p>
                            <?php endif; ?>
                            
                            <hr>
                            
                            <strong><i class="fas fa-users mr-1"></i> Total Passengers</strong>
                            <p class="text-muted"><?php echo $group_booking['total_passengers']; ?></p>
                            
                            <hr>
                            
                            <strong><i class="fas fa-money-bill mr-1"></i> Total Amount</strong>
                            <p class="text-muted"><?php echo formatCurrency($group_booking['total_amount']); ?></p>
                            
                            <?php if (!empty($group_booking['notes'])): ?>
                                <hr>
                                <strong><i class="fas fa-sticky-note mr-1"></i> Notes</strong>
                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($group_booking['notes'])); ?></p>
                            <?php endif; ?>
                            
                            <hr>
                            
                            <strong><i class="fas fa-calendar-alt mr-1"></i> Booking Date</strong>
                            <p class="text-muted"><?php echo date('d M Y, h:i A', strtotime($group_booking['created_at'])); ?></p>
                        </div>
                    </div>
                    
                    <!-- Trip Info -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Trip Information</h3>
                        </div>
                        <div class="card-body">
                            <strong><i class="fas fa-route mr-1"></i> Route</strong>
                            <p class="text-muted"><?php echo htmlspecialchars($group_booking['origin'] . ' to ' . $group_booking['destination']); ?></p>
                            
                            <hr>
                            
                            <strong><i class="fas fa-clock mr-1"></i> Departure</strong>
                            <p class="text-muted"><?php echo date('d M Y, h:i A', strtotime($group_booking['departure_time'])); ?></p>
                            
                            <hr>
                            
                            <strong><i class="fas fa-clock mr-1"></i> Arrival</strong>
                            <p class="text-muted"><?php echo date('d M Y, h:i A', strtotime($group_booking['arrival_time'])); ?></p>
                            
                            <hr>
                            
                            <strong><i class="fas fa-bus mr-1"></i> Bus</strong>
                            <p class="text-muted">
                                <?php echo htmlspecialchars($group_booking['bus_name']); ?> (<?php echo htmlspecialchars($group_booking['registration_number']); ?>)<br>
                                <small><?php echo ucfirst($group_booking['bus_type']); ?> - <?php echo $group_booking['capacity']; ?> seats</small>
                            </p>
                            
                            <hr>
                            
                            <strong><i class="fas fa-money-bill mr-1"></i> Fare per Seat</strong>
                            <p class="text-muted"><?php echo formatCurrency($group_booking['fare']); ?></p>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?reference=' . $booking_reference); ?>" method="post">
                                <div class="form-group">
                                    <label for="status">Update Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="pending" <?php echo ($group_booking['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo ($group_booking['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="cancelled" <?php echo ($group_booking['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        <option value="completed" <?php echo ($group_booking['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </div>
                                <button type="submit" name="update_status" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Update Status
                                </button>
                            </form>
                            
                            <div class="mt-3">
                                <a href="../print_group_ticket.php?reference=<?php echo $booking_reference; ?>" target="_blank" class="btn btn-info btn-block">
                                    <i class="fas fa-print"></i> Print Tickets
                                </a>
                                <a href="mailto:<?php echo htmlspecialchars($group_booking['contact_email'] ?: $group_booking['user_email']); ?>" class="btn btn-default btn-block">
                                    <i class="fas fa-envelope"></i> Email Customer
                                </a>
                                <a href="tel:<?php echo htmlspecialchars($group_booking['contact_phone']); ?>" class="btn btn-default btn-block">
                                    <i class="fas fa-phone"></i> Call Customer
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <!-- Passenger List -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Passenger List</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Seat</th>
                                            <th>Passenger Name</th>
                                            <th>Phone</th>
                                            <th>ID Number</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($booking['seat_number']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['passenger_name']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['passenger_phone']); ?></td>
                                                <td><?php echo htmlspecialchars($booking['passenger_id_number'] ?: 'N/A'); ?></td>
                                                <td><?php echo formatCurrency($booking['amount']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo getStatusBadgeClass($booking['status']); ?>">
                                                        <?php echo ucfirst($booking['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- QR Code -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">QR Code</h3>
                        </div>
                        <div class="card-body text-center">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode(getBaseUrl() . '/check_booking.php?reference=' . $booking_reference); ?>" 
                                alt="QR Code" class="img-fluid">
                            <p class="mt-2">Scan to verify booking</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
// Helper function to get badge class based on status
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'confirmed':
            return 'success';
        case 'pending':
            return 'warning';
        case 'cancelled':
            return 'danger';
        case 'completed':
            return 'info';
        default:
            return 'secondary';
    }
}

// Include footer
require_once 'includes/footer.php';
?>
