<?php
// Include session configuration
require_once '../config/session_config.php';

// Start session
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Set message
    setFlashMessage("error", "You do not have permission to access the admin dashboard.");

    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Set page title
$page_title = "Booking Details";

// Include header
require_once '../includes/templates/header.php';

// Include functions
require_once '../includes/functions.php';

// Include database connection
$conn = require_once '../config/database.php';

// Include config file for APP_URL
require_once '../config/config.php';

// Check if booking ID or reference is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $booking_id = intval($_GET['id']);
    $booking_reference = null;
} elseif (isset($_GET['reference']) && !empty($_GET['reference'])) {
    $booking_id = null;
    $booking_reference = $_GET['reference'];
} else {
    setFlashMessage("error", "Invalid booking ID or reference.");
    header("Location: bookings.php");
    exit();
}

// Log the request for debugging
logActivity("Admin", "Viewing booking details with " . ($booking_id ? "ID: $booking_id" : "Reference: $booking_reference"));

// Process status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
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
    if (isset($_GET['reference'])) {
        header("Location: booking_details.php?reference=" . $_GET['reference']);
    } else {
        header("Location: booking_details.php?id=" . $booking_id);
    }
    exit();
}

// Get booking details
if ($booking_id) {
    // Query by ID
    $sql = "SELECT b.*,
            s.departure_time, s.arrival_time, s.fare,
            r.origin, r.destination, r.distance, r.duration,
            bs.name AS bus_name, bs.registration_number, bs.type AS bus_type, bs.capacity,
            CONCAT(u.first_name, ' ', u.last_name) AS user_name, u.email AS user_email, u.phone AS user_phone
            FROM bookings b
            JOIN schedules s ON b.schedule_id = s.id
            JOIN routes r ON s.route_id = r.id
            JOIN buses bs ON s.bus_id = bs.id
            LEFT JOIN users u ON b.user_id = u.id
            WHERE b.id = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        setFlashMessage("error", "Database error: " . $conn->error);
        header("Location: bookings.php");
        exit();
    }

    $stmt->bind_param("i", $booking_id);
} else {
    // Query by reference
    $sql = "SELECT b.*,
            s.departure_time, s.arrival_time, s.fare,
            r.origin, r.destination, r.distance, r.duration,
            bs.name AS bus_name, bs.registration_number, bs.type AS bus_type, bs.capacity,
            CONCAT(u.first_name, ' ', u.last_name) AS user_name, u.email AS user_email, u.phone AS user_phone
            FROM bookings b
            JOIN schedules s ON b.schedule_id = s.id
            JOIN routes r ON s.route_id = r.id
            JOIN buses bs ON s.bus_id = bs.id
            LEFT JOIN users u ON b.user_id = u.id
            WHERE b.booking_reference = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        setFlashMessage("error", "Database error: " . $conn->error);
        header("Location: bookings.php");
        exit();
    }

    $stmt->bind_param("s", $booking_reference);
}

if (!$stmt->execute()) {
    setFlashMessage("error", "Query execution error: " . $stmt->error);
    $stmt->close();
    header("Location: bookings.php");
    exit();
}

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    setFlashMessage("error", "Booking not found.");
    $stmt->close();
    header("Location: bookings.php");
    exit();
}

$booking = $result->fetch_assoc();
$stmt->close();

// Set booking_id from the result if we queried by reference
if (!$booking_id && $booking) {
    $booking_id = $booking['id'];
}

// Get payment details
$sql = "SELECT * FROM payments WHERE booking_id = ?";
$payment = null;

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    setFlashMessage("error", "Payment query error: " . $conn->error);
} else {
    $stmt->bind_param("i", $booking_id);

    if ($stmt->execute()) {
        $payment_result = $stmt->get_result();
        if ($payment_result && $payment_result->num_rows > 0) {
            $payment = $payment_result->fetch_assoc();
        }
    } else {
        setFlashMessage("error", "Payment query execution error: " . $stmt->error);
    }

    $stmt->close();
}
// Generate booking URL for QR code
$booking_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/isioloraha/booking_confirmation.php?reference=" . $booking['booking_reference'];

// Generate QR code URL
$qr_code_url = "../simple_qr.php?url=" . urlencode($booking_url);
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Booking Details</h1>
        <div>
            <a href="bookings.php" class="btn-secondary mr-2 no-print">
                <i class="fas fa-arrow-left mr-2"></i> Back to Bookings
            </a>
            <a href="javascript:window.print();" class="btn-primary no-print">
                <i class="fas fa-print mr-2"></i> Print
            </a>
        </div>
    </div>

    <!-- Display Flash Messages -->
    <div class="no-print">
        <?php displayFlashMessages(); ?>
    </div>

    <!-- Booking Status and Actions -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6 no-print">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h2 class="text-xl font-bold mb-2">Booking #<?php echo $booking['booking_reference']; ?></h2>
                <p class="text-gray-600">Created on <?php echo date('d M Y, H:i', strtotime($booking['created_at'])); ?></p>
            </div>
            <div class="mt-4 md:mt-0">
                <span class="badge badge-lg <?php
                    echo ($booking['status'] == 'confirmed') ? 'badge-success' :
                        (($booking['status'] == 'pending') ? 'badge-warning' : 'badge-danger');
                ?>">
                    <?php echo ucfirst($booking['status']); ?>
                </span>

                <?php if ($booking['status'] != 'cancelled' && strtotime($booking['departure_time']) > time()): ?>
                    <div class="mt-2 flex space-x-2">
                        <?php if ($booking['status'] == 'pending'): ?>
                            <form method="post" action="" class="inline-block">
                                <input type="hidden" name="current_status" value="<?php echo $booking['status']; ?>">
                                <input type="hidden" name="status" value="confirmed">
                                <button type="submit" name="update_status" class="btn-sm btn-success" onclick="return confirm('Are you sure you want to confirm this booking?');">
                                    <i class="fas fa-check mr-1"></i> Confirm
                                </button>
                            </form>
                        <?php endif; ?>

                        <form method="post" action="" class="inline-block">
                            <input type="hidden" name="current_status" value="<?php echo $booking['status']; ?>">
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" name="update_status" class="btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?');">
                                <i class="fas fa-ban mr-1"></i> Cancel
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Receipt Style Booking Details -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <style>
            .receipt {
                width: 100%;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px 40px;
                box-sizing: border-box;
                background-color: white;
                font-family: 'Arial', sans-serif;
                line-height: 1.4;
                font-size: 14px;
            }

            .receipt-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }

            .company-name {
                color: #15803d;
                font-size: 24px;
                font-weight: bold;
                margin: 0;
            }

            .company-tagline {
                color: #666;
                font-size: 14px;
                margin: 0;
            }

            .section-divider {
                border-top: 1px solid #15803d;
                margin: 15px 0;
            }

            .receipt-info {
                display: flex;
                justify-content: space-between;
                margin-bottom: 15px;
            }

            .status-confirmed {
                color: #15803d;
                font-weight: bold;
                font-size: 16px;
            }

            .section-title {
                font-weight: bold;
                margin-bottom: 10px;
                font-size: 16px;
            }

            .journey-details {
                margin-bottom: 15px;
            }

            .journey-row {
                display: flex;
                margin-bottom: 5px;
            }

            .journey-label {
                width: 180px;
                font-weight: bold;
            }

            .details-container {
                display: flex;
                justify-content: space-between;
                margin-bottom: 15px;
            }

            .details-column {
                width: 48%;
            }

            .detail-row {
                margin-bottom: 5px;
            }

            .detail-label {
                font-weight: bold;
            }

            .payment-details {
                display: flex;
                justify-content: space-between;
                margin-bottom: 15px;
            }

            .amount-paid {
                color: #15803d;
                font-size: 20px;
                font-weight: bold;
                text-align: right;
            }

            .qr-container {
                text-align: center;
                margin-bottom: 15px;
            }

            .qr-container img {
                width: 120px;
                height: 120px;
            }

            .verification-text {
                text-align: center;
                font-size: 12px;
                color: #666;
                margin-top: 5px;
            }

            .important-info {
                margin-bottom: 15px;
            }

            .important-info ul {
                padding-left: 20px;
                margin: 5px 0;
            }

            .important-info li {
                margin-bottom: 5px;
            }

            .footer-text {
                text-align: center;
                font-size: 12px;
                color: #666;
            }

            .no-print {
                display: block;
            }

            @media print {
                @page {
                    size: A4 portrait;
                    margin: 0;
                }

                body {
                    margin: 0;
                    padding: 0;
                    background-color: white;
                    width: 100%;
                    height: 100%;
                }

                .no-print {
                    display: none !important;
                }

                /* Hide header and footer when printing */
                header, footer, nav, .navbar, .site-header, .site-footer {
                    display: none !important;
                }

                /* Hide the page title */
                h1.text-3xl {
                    display: none !important;
                }

                .receipt {
                    width: 100%;
                    max-width: none;
                    margin: 0;
                    padding: 20px 40px;
                    box-sizing: border-box;
                    page-break-after: always;
                    height: 100%;
                }

                .page-break {
                    page-break-after: always;
                }

                /* Ensure the receipt fits on one page */
                .receipt-header, .receipt-info, .journey-details,
                .details-container, .payment-details, .qr-container,
                .important-info, .footer-text {
                    page-break-inside: avoid;
                }
            }
        </style>

        <!-- Receipt Preview -->
        <div class="receipt">
            <!-- Receipt Header -->
            <div class="receipt-header">
                <div>
                    <h1 class="company-name">Isiolo Raha Bus</h1>
                    <p class="company-tagline">Official Receipt & Ticket</p>
                </div>
                <div>
                    <img src="../assets/images/isioloraha logo.png" alt="Isiolo Raha Logo" width="60">
                </div>
            </div>

            <hr class="section-divider">

            <!-- Receipt Number & Date -->
            <div class="receipt-info">
                <div>
                    <span class="detail-label">Receipt No:</span>
                    <span><?php echo $booking['booking_reference']; ?></span>
                </div>
                <div>
                    <span class="detail-label">Date:</span>
                    <span><?php echo date('d M Y, H:i', strtotime($booking['created_at'])); ?></span>
                </div>
            </div>

            <!-- Status & Payment Method -->
            <div class="receipt-info">
                <div class="status-confirmed"><?php echo ucfirst($booking['status']); ?></div>
                <div>
                    <span class="detail-label">Payment Method:</span>
                    <span><?php echo !empty($payment) ? ucfirst($payment['payment_method']) : 'N/A'; ?></span>
                </div>
            </div>

            <!-- Journey Details -->
            <div class="journey-details">
                <div class="section-title">Journey Details</div>
                <div class="journey-row">
                    <div class="journey-label">Route:</div>
                    <div><?php echo $booking['origin']; ?> to <?php echo $booking['destination']; ?></div>
                </div>
                <div class="journey-row">
                    <div class="journey-label">Departure:</div>
                    <div><?php echo date('d M Y, H:i', strtotime($booking['departure_time'])); ?></div>
                </div>
                <div class="journey-row">
                    <div class="journey-label">Arrival:</div>
                    <div><?php echo date('d M Y, H:i', strtotime($booking['arrival_time'])); ?></div>
                </div>
                <div class="journey-row">
                    <div class="journey-label">Duration:</div>
                    <div><?php echo formatDuration($booking['duration']); ?></div>
                </div>
            </div>

            <!-- Passenger & Bus Details -->
            <div class="details-container">
                <div class="details-column">
                    <div class="section-title">Passenger Details</div>
                    <div class="detail-row">
                        <div class="detail-label">Name:</div>
                        <div><?php echo $booking['passenger_name']; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Phone:</div>
                        <div><?php echo $booking['passenger_phone']; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">ID Number:</div>
                        <div><?php echo $booking['passenger_id_number']; ?></div>
                    </div>
                </div>
                <div class="details-column">
                    <div class="section-title">Bus Details</div>
                    <div class="detail-row">
                        <div class="detail-label">Bus Name:</div>
                        <div><?php echo $booking['bus_name']; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Registration:</div>
                        <div><?php echo $booking['registration_number']; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Type:</div>
                        <div><?php echo ucfirst($booking['bus_type']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Seat Number:</div>
                        <div><?php echo $booking['seat_number']; ?></div>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="payment-details">
                <div>
                    <div class="section-title">Payment Details</div>
                    <div class="detail-row">
                        <span class="detail-label">Transaction Ref:</span>
                        <span><?php echo !empty($payment) ? $payment['transaction_reference'] : 'IRE' . substr($booking['booking_reference'], 2); ?></span>
                    </div>
                </div>
                <div>
                    <div class="detail-label">Amount Paid:</div>
                    <div class="amount-paid"><?php echo formatCurrency(!empty($payment) ? $payment['amount'] : $booking['fare']); ?></div>
                </div>
            </div>

            <!-- QR Code -->
            <div class="qr-container">
                <img src="<?php echo $qr_code_url; ?>" alt="QR Code">
                <p class="verification-text">Scan for verification</p>
            </div>

            <!-- Important Information -->
            <div class="important-info">
                <div class="detail-label">Important Information:</div>
                <ul>
                    <li>Please arrive at least 30 minutes before departure.</li>
                    <li>Present this ticket (printed or digital) at the boarding gate.</li>
                    <li>Valid ID matching the passenger details is required.</li>
                </ul>
            </div>

            <!-- Receipt Footer -->
            <div class="footer-text">
                <p>Thank you for choosing Isiolo Raha Bus Services!</p>
                <p>For support, call: +254 700 000 000 or email: support@isioloraha.com</p>
                <p>This is an official receipt and serves as proof of payment.</p>
            </div>
        </div>
    </div>

    <!-- Additional Information Section (Only visible in admin view, not when printing) -->
    <div class="grid grid-cols-1 gap-6 mb-6 no-print">
        <?php if (!empty($booking['user_name'])): ?>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold mb-4 border-b pb-2">Booking Information</h3>
                <div class="mt-2">
                    <h4 class="font-semibold mb-2">Booked By</h4>
                    <p><i class="fas fa-user mr-2 text-gray-500"></i> <?php echo $booking['user_name']; ?></p>
                    <p><i class="fas fa-envelope mr-2 text-gray-500"></i> <?php echo $booking['user_email']; ?></p>
                    <p><i class="fas fa-phone mr-2 text-gray-500"></i> <?php echo $booking['user_phone']; ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Additional print styles to ensure header is hidden -->
<style>
@media print {
    /* Hide all header elements */
    header, .header, nav, .navbar, .site-header, .site-footer, footer {
        display: none !important;
    }

    /* Hide the page title and container */
    .container > h1, .container > .flex {
        display: none !important;
    }

    /* Remove any margins and padding from body */
    body {
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Ensure the receipt is the only thing visible */
    .container {
        padding-top: 0 !important;
        margin-top: 0 !important;
    }

    /* Hide any navigation elements */
    .breadcrumb, .breadcrumbs, .navigation {
        display: none !important;
    }
}
</style>

<?php
// Include footer
require_once '../includes/templates/footer.php';
?>
