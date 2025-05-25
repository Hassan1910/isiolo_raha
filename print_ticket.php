<?php
// Include session configuration
require_once 'config/session_config.php';

// Start session
session_start();

// Include functions
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Set message
    setFlashMessage("error", "Please login to access your tickets.");

    // Redirect to login page
    header("Location: login.php");
    exit();
}

// Check if booking reference is provided
if (!isset($_GET['reference']) || empty($_GET['reference'])) {
    setFlashMessage("error", "Invalid booking reference.");
    header("Location: user/bookings.php");
    exit();
}

$booking_reference = $_GET['reference'];

// Include database connection
$conn = require_once 'config/database.php';

// Get booking details
$sql = "SELECT b.id, b.booking_reference, b.seat_number, b.passenger_name, b.passenger_phone,
        b.passenger_id_number, b.amount, b.status, b.created_at,
        s.departure_time, s.arrival_time,
        r.origin, r.destination,
        bs.name AS bus_name, bs.type AS bus_type, bs.registration_number,
        p.transaction_reference, p.payment_method, p.status AS payment_status
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        JOIN buses bs ON s.bus_id = bs.id
        LEFT JOIN payments p ON b.id = p.booking_id
        WHERE b.booking_reference = ?";

// Add user_id condition only if the user is not an admin
$params = [$booking_reference];
$types = "s";

if (!isAdmin()) {
    $sql .= " AND b.user_id = ?";
    $params[] = $_SESSION['user_id'];
    $types .= "i";
}

$sql .= " ORDER BY b.seat_number";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }

        // Get the first booking for general information
        $booking = $bookings[0];
    } else {
        $redirect_url = isAdmin() ? "admin/bookings.php" : "user/bookings.php";
        setFlashMessage("error", "Booking not found or you don't have permission to access it.");
        header("Location: " . $redirect_url);
        exit();
    }

    $stmt->close();
} else {
    setFlashMessage("error", "Error retrieving booking details.");
    header("Location: user/bookings.php");
    exit();
}

// We'll generate QR code data for each ticket in the JavaScript section

// Set content type to PDF (in a real application, you would generate a PDF file)
// For this example, we'll just output HTML that's styled for printing
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo $booking['booking_reference']; ?></title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        @page {
            size: A4 portrait;
            margin: 0.5cm;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.5;
            font-size: 14px;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #f9f9f9;
        }

        .receipt {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            box-sizing: border-box;
            background-color: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
        }

        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .company-name {
            color: #15803d;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .company-tagline {
            color: #666;
            font-size: 14px;
            margin: 0;
            font-weight: 300;
        }

        .section-divider {
            border: none;
            height: 2px;
            background: linear-gradient(to right, #15803d, #dcfce7);
            margin: 15px 0;
        }

        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            align-items: center;
        }

        .status-confirmed {
            color: #15803d;
            font-weight: 600;
            font-size: 16px;
            background-color: #dcfce7;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .section-title {
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 16px;
            color: #15803d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .journey-details {
            margin-bottom: 20px;
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #15803d;
        }

        .journey-row {
            display: flex;
            margin-bottom: 8px;
        }

        .journey-label {
            width: 120px;
            font-weight: 600;
            color: #64748b;
        }

        .details-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 20px;
        }

        .details-column {
            width: 48%;
            background-color: #f8fafc;
            padding: 15px;
            border-radius: 8px;
        }

        .detail-row {
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }

        .detail-label {
            font-weight: 600;
            color: #64748b;
        }

        .payment-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background-color: #f0fdf4;
            padding: 15px;
            border-radius: 8px;
            align-items: center;
        }

        .amount-paid {
            color: #15803d;
            font-size: 24px;
            font-weight: 700;
            text-align: right;
        }

        .qr-container {
            text-align: center;
            margin: 20px auto;
            max-width: 150px;
        }

        .qr-container img {
            width: 120px;
            height: 120px;
            border: 1px solid #e2e8f0;
            padding: 5px;
            background: white;
            border-radius: 4px;
        }

        .verification-text {
            text-align: center;
            font-size: 12px;
            color: #64748b;
            margin-top: 5px;
        }

        .important-info {
            margin-bottom: 20px;
            background-color: #fffbeb;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
        }

        .important-info ul {
            padding-left: 20px;
            margin: 5px 0;
        }

        .important-info li {
            margin-bottom: 5px;
            color: #78350f;
        }

        .footer-text {
            text-align: center;
            font-size: 12px;
            color: #64748b;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed #e2e8f0;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0.5cm;
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

            .receipt {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 15px 20px;
                box-sizing: border-box;
                page-break-after: always;
                height: 100%;
                box-shadow: none;
                border-radius: 0;
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
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Print Button -->
        <div class="flex justify-between items-center mb-6 no-print">
            <h1 class="text-2xl font-bold text-gray-800">Booking Receipt</h1>
            <div>
                <button onclick="window.print()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition duration-300 flex items-center">
                    <i class="fas fa-print mr-2"></i> Print Receipt
                </button>
                <a href="<?php echo isAdmin() ? 'admin/bookings.php' : 'user/bookings.php'; ?>" class="ml-2 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md transition duration-300 inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back
                </a>
            </div>
        </div>

        <?php foreach ($bookings as $index => $ticket): ?>
            <!-- Receipt -->
            <div class="receipt">
                <!-- Receipt Header -->
                <div class="receipt-header">
                    <div>
                        <h1 class="company-name">Isiolo Raha Bus</h1>
                        <p class="company-tagline">Official Receipt & Ticket</p>
                    </div>
                    <div>
                        <img src="assets/images/isioloraha logo.png" alt="Isiolo Raha Logo" width="80">
                    </div>
                </div>

                <hr class="section-divider">

                <!-- Ticket Status Banner -->
                <div style="text-align: center; margin-bottom: 15px;">
                    <div class="status-confirmed">
                        <i class="fas fa-check-circle mr-1"></i> Booking Confirmed
                    </div>
                </div>

                <!-- Receipt Number & Date -->
                <div class="receipt-info">
                    <div>
                        <span class="detail-label">Receipt No:</span>
                        <span><strong><?php echo $ticket['booking_reference']; ?></strong></span>
                    </div>
                    <div>
                        <span class="detail-label">Date:</span>
                        <span><?php echo date('d M Y, H:i', strtotime($ticket['created_at'])); ?></span>
                    </div>
                </div>

                <!-- Journey Summary -->
                <div style="text-align: center; margin: 20px 0; background-color: #f0fdf4; padding: 15px; border-radius: 8px;">
                    <div style="font-size: 18px; font-weight: 600; color: #15803d; margin-bottom: 5px;">
                        <?php echo $ticket['origin']; ?> to <?php echo $ticket['destination']; ?>
                    </div>
                    <div style="font-size: 14px; color: #64748b;">
                        <?php echo date('l, d M Y', strtotime($ticket['departure_time'])); ?> • Seat <?php echo $ticket['seat_number']; ?>
                    </div>
                </div>

                <!-- Journey Details -->
                <div class="section-title">Journey Details</div>

                <div class="journey-details">
                    <div class="journey-row">
                        <div class="journey-label">From:</div>
                        <div><strong><?php echo $ticket['origin']; ?></strong></div>
                    </div>
                    <div class="journey-row">
                        <div class="journey-label">To:</div>
                        <div><strong><?php echo $ticket['destination']; ?></strong></div>
                    </div>
                    <div class="journey-row">
                        <div class="journey-label">Departure:</div>
                        <div><strong><?php echo date('d M Y, h:i A', strtotime($ticket['departure_time'])); ?></strong></div>
                    </div>
                    <div class="journey-row">
                        <div class="journey-label">Arrival:</div>
                        <div><strong><?php echo date('d M Y, h:i A', strtotime($ticket['arrival_time'])); ?></strong></div>
                    </div>
                </div>

                <!-- Passenger & Bus Details -->
                <div class="details-container">
                    <div class="details-column">
                        <div class="section-title">Passenger Details</div>
                        <div class="detail-row">
                            <span class="detail-label">Name:</span>
                            <span><strong><?php echo $ticket['passenger_name']; ?></strong></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">ID Number:</span>
                            <span><?php echo $ticket['passenger_id_number'] ?: 'N/A'; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Phone:</span>
                            <span><?php echo $ticket['passenger_phone']; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Seat Number:</span>
                            <span><strong><?php echo $ticket['seat_number']; ?></strong></span>
                        </div>
                    </div>

                    <div class="details-column">
                        <div class="section-title">Bus Details</div>
                        <div class="detail-row">
                            <span class="detail-label">Bus Name:</span>
                            <span><strong><?php echo $ticket['bus_name']; ?></strong></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Bus Type:</span>
                            <span><?php echo ucfirst($ticket['bus_type']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Registration:</span>
                            <span><?php echo $ticket['registration_number']; ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Payment:</span>
                            <span><?php echo ucfirst($ticket['payment_method'] ?? 'Online'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="payment-details">
                    <div>
                        <div class="section-title">Payment Details</div>
                        <div class="detail-row">
                            <span class="detail-label">Transaction Ref:</span>
                            <span><?php echo $ticket['transaction_reference'] ?? 'IRE' . substr($ticket['booking_reference'], 2); ?></span>
                        </div>
                    </div>
                    <div>
                        <div class="detail-label">Amount Paid:</div>
                        <div class="amount-paid"><?php echo formatCurrency($ticket['amount']); ?></div>
                    </div>
                </div>

                <!-- QR Code -->
                <div class="qr-container">
                    <?php
                        // Generate URL for the ticket
                        $ticket_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
                                     "://$_SERVER[HTTP_HOST]/isioloraha/booking_confirmation.php?reference=" .
                                     $ticket['booking_reference'];
                    ?>
                    <img src="simple_qr.php?url=<?php echo urlencode($ticket_url); ?>" alt="QR Code">
                    <p class="verification-text">Scan for verification</p>
                </div>

                <!-- Important Information -->
                <div class="important-info">
                    <div class="detail-label">Important Information:</div>
                    <ul>
                        <li>Please arrive at least 30 minutes before departure.</li>
                        <li>Present this ticket (printed or digital) at the boarding gate.</li>
                        <li>Valid ID matching the passenger details is required.</li>
                        <li>For any changes to your booking, please contact our office at least 6 hours before departure.</li>
                    </ul>
                </div>

                <!-- Receipt Footer -->
                <div class="footer-text">
                    <p>Thank you for choosing Isiolo Raha Bus Services!</p>
                    <p>For support, call: +254 700 000 000 or email: support@isioloraha.com</p>
                    <p>This is an official receipt and serves as proof of payment.</p>
                </div>
            </div>

            <?php if ($index < count($bookings) - 1): ?>
                <div class="page-break"></div>
            <?php endif; ?>
        <?php endforeach; ?>

        <div class="text-center text-gray-500 text-sm mt-8 no-print">
            <p>Isiolo Raha Bus Booking System</p>
            <p>For support, call: +254 700 000 000 or email: support@isioloraha.com</p>
        </div>
    </div>

    <script>
        // Print functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus the print button when page loads
            document.querySelector('button[onclick="window.print()"]').focus();

            // Add keyboard shortcut for printing (Ctrl+P or Cmd+P)
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                    e.preventDefault();
                    window.print();
                }
            });

            // Optional: Auto-print after a delay
            // setTimeout(function() {
            //     window.print();
            // }, 1000);
        });
    </script>
</body>
</html>
<?php
// Close connection
$conn->close();
?>
