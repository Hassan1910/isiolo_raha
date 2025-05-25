<?php
// Start session
session_start();

// Include functions
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isAdmin()) {
    // Set message
    setFlashMessage("error", "You do not have permission to access this page.");

    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Check if booking ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Booking ID is required");
}

$booking_id = intval($_GET['id']);

// Include database connection
$conn = require_once '../config/database.php';

// Include config file for APP_URL
require_once '../config/config.php';

// Get booking details
$sql = "SELECT b.*,
        s.departure_time, s.arrival_time,
        r.origin, r.destination,
        bs.name AS bus_name, bs.registration_number,
        CONCAT(IFNULL(u.first_name, ''), ' ', IFNULL(u.last_name, '')) AS user_name,
        u.email AS user_email, u.phone
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        JOIN buses bs ON s.bus_id = bs.id
        LEFT JOIN users u ON b.user_id = u.id
        WHERE b.id = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $booking_id);
$result = $stmt->execute();

if ($result === false) {
    die("Error executing query: " . $stmt->error);
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Booking not found");
}

$booking = $result->fetch_assoc();
$stmt->close();

// Get company details
$company_name = "Isiolo Raha";
$company_address = "Isiolo, Kenya";
$company_phone = "+254 700 000000";
$company_email = "info@isioloraha.com";
$company_website = "www.isioloraha.com";

// Define base URL if APP_URL is not defined
if (!defined('APP_URL')) {
    define('APP_URL', 'http://localhost/isioloraha');
}

// Generate QR code URL
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode(APP_URL . "/booking_details.php?reference=" . $booking['booking_reference']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Ticket - <?php echo $booking['booking_reference']; ?></title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .ticket-container {
            width: 210mm;
            min-height: 148mm;
            margin: 0 auto;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
            page-break-after: always;
        }
        .ticket-header {
            padding: 20px;
            border-bottom: 2px dashed #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            max-width: 150px;
        }
        .company-info {
            text-align: right;
        }
        .ticket-title {
            text-align: center;
            margin: 20px 0;
            color: #16a34a;
        }
        .ticket-body {
            padding: 0 20px;
            display: flex;
        }
        .ticket-details {
            flex: 3;
            padding-right: 20px;
        }
        .ticket-qr {
            flex: 1;
            text-align: center;
            padding: 20px;
            border-left: 1px dashed #ddd;
        }
        .detail-row {
            display: flex;
            margin-bottom: 15px;
        }
        .detail-label {
            flex: 1;
            font-weight: bold;
            color: #666;
        }
        .detail-value {
            flex: 2;
            font-weight: bold;
        }
        .ticket-footer {
            padding: 20px;
            border-top: 2px dashed #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .status-confirmed {
            background-color: #dcfce7;
            color: #166534;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
        }
        .status-pending {
            background-color: #fef9c3;
            color: #854d0e;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
        }
        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
        }
        .bus-features {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8fafc;
            border-radius: 10px;
        }
        .feature-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .feature-item {
            background-color: #e0f2fe;
            color: #0369a1;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }
        .reference-number {
            font-size: 24px;
            font-weight: bold;
            color: #16a34a;
            margin-bottom: 10px;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(22, 163, 74, 0.05);
            z-index: 0;
            pointer-events: none;
        }
        @media print {
            body {
                background-color: white;
            }
            .ticket-container {
                box-shadow: none;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; padding: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background-color: #16a34a; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Print Ticket
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background-color: #6b7280; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Close
        </button>
    </div>

    <div class="ticket-container">
        <div class="watermark"><?php echo strtoupper($booking['status']); ?></div>

        <div class="ticket-header">
            <div>
                <img src="<?php echo APP_URL; ?>/assets/images/isioloraha logo.png" alt="Isiolo Raha Logo" class="logo">
            </div>
            <div class="company-info">
                <h3 style="margin: 0;"><?php echo $company_name; ?></h3>
                <p style="margin: 5px 0;"><?php echo $company_address; ?></p>
                <p style="margin: 5px 0;"><?php echo $company_phone; ?></p>
                <p style="margin: 5px 0;"><?php echo $company_email; ?></p>
            </div>
        </div>

        <h1 class="ticket-title">BUS TICKET</h1>

        <div class="ticket-body">
            <div class="ticket-details">
                <div class="detail-row">
                    <div class="detail-label">Booking Reference:</div>
                    <div class="detail-value reference-number"><?php echo $booking['booking_reference']; ?></div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value">
                        <span class="status-<?php echo $booking['status']; ?>">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Passenger:</div>
                    <div class="detail-value"><?php echo $booking['passenger_name']; ?></div>
                </div>

                <?php if (!empty($booking['phone'])): ?>
                <div class="detail-row">
                    <div class="detail-label">Phone:</div>
                    <div class="detail-value"><?php echo $booking['phone']; ?></div>
                </div>
                <?php endif; ?>

                <div class="detail-row">
                    <div class="detail-label">Route:</div>
                    <div class="detail-value"><?php echo $booking['origin']; ?> to <?php echo $booking['destination']; ?></div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Travel Date:</div>
                    <div class="detail-value"><?php echo date('d M Y', strtotime($booking['departure_time'])); ?></div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Departure Time:</div>
                    <div class="detail-value"><?php echo date('h:i A', strtotime($booking['departure_time'])); ?></div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Bus:</div>
                    <div class="detail-value"><?php echo $booking['bus_name']; ?> (<?php echo $booking['registration_number']; ?>)</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Seat Number:</div>
                    <div class="detail-value"><?php echo $booking['seat_number']; ?></div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Amount Paid:</div>
                    <div class="detail-value">KES <?php echo number_format($booking['amount'], 2); ?></div>
                </div>

                <!-- Bus amenities section removed as features column doesn't exist in the database -->
            </div>

            <div class="ticket-qr">
                <img src="<?php echo $qr_code_url; ?>" alt="QR Code">
                <p style="margin-top: 10px; font-size: 12px;">Scan to verify ticket</p>
            </div>
        </div>

        <div class="ticket-footer">
            <p>This is an official ticket. Please present this ticket before boarding.</p>
            <p>For inquiries, please contact us at <?php echo $company_phone; ?> or <?php echo $company_email; ?></p>
            <p>Thank you for choosing <?php echo $company_name; ?>!</p>
        </div>
    </div>
</body>
</html>
