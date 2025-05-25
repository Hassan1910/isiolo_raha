<?php
// Include session configuration
require_once 'config/session_config.php';

// Start session
session_start();

// Set page title
$page_title = "Booking Confirmation";

// Include header
require_once 'includes/templates/header.php';

// Include database connection
$conn = require_once 'config/database.php';

// Check if reference is provided
if (!isset($_GET['reference'])) {
    setFlashMessage("error", "No booking reference provided.");
    header("Location: index.php");
    exit();
}

// Get reference from URL
$booking_reference = $_GET['reference'];

// Get booking details
$sql = "SELECT b.id, b.booking_reference, b.seat_number, b.passenger_name, b.passenger_phone,
        b.passenger_id_number, b.amount, b.status, b.created_at,
        s.departure_time, s.arrival_time,
        r.origin, r.destination,
        bs.name AS bus_name, bs.type AS bus_type,
        p.transaction_reference, p.payment_method, p.status AS payment_status
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        JOIN buses bs ON s.bus_id = bs.id
        LEFT JOIN payments p ON b.id = p.booking_id
        WHERE b.booking_reference = ?
        ORDER BY b.seat_number";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("s", $booking_reference);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $bookings = [];
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row;
            }

            // Get first booking for common details
            $booking = $bookings[0];
        } else {
            setFlashMessage("error", "Booking not found.");
            header("Location: index.php");
            exit();
        }
    } else {
        setFlashMessage("error", "Something went wrong. Please try again later.");
        header("Location: index.php");
        exit();
    }

    // Close statement
    $stmt->close();
}

// Clear booking data from session
if (isset($_SESSION['booking_data'])) {
    unset($_SESSION['booking_data']);
}

// Generate booking URL for QR code
$booking_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/isioloraha/booking_confirmation.php?reference=" . $booking['booking_reference'];
?>

<div class="bg-gray-100 py-6">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Success Message -->
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-lg font-bold">Booking Confirmed!</p>
                        <p>Your booking has been successfully confirmed. Your booking reference is <strong><?php echo $booking_reference; ?></strong>.</p>
                    </div>
                </div>
            </div>

            <!-- Booking Receipt Preview -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                <style>
                    /* Google Fonts */
                    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

                    .receipt {
                        width: 100%;
                        max-width: 800px;
                        margin: 0 auto;
                        padding: 30px;
                        box-sizing: border-box;
                        background-color: white;
                        font-family: 'Poppins', sans-serif;
                        line-height: 1.5;
                        font-size: 14px;
                        color: #333;
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

                <!-- Receipt Preview -->
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
                            <span><strong><?php echo $booking['booking_reference']; ?></strong></span>
                        </div>
                        <div>
                            <span class="detail-label">Date:</span>
                            <span><?php echo date('d M Y, H:i', strtotime($booking['created_at'])); ?></span>
                        </div>
                    </div>

                    <!-- Journey Summary -->
                    <div style="text-align: center; margin: 20px 0; background-color: #f0fdf4; padding: 15px; border-radius: 8px;">
                        <div style="font-size: 18px; font-weight: 600; color: #15803d; margin-bottom: 5px;">
                            <?php echo $booking['origin']; ?> to <?php echo $booking['destination']; ?>
                        </div>
                        <div style="font-size: 14px; color: #64748b;">
                            <?php echo date('l, d M Y', strtotime($booking['departure_time'])); ?> • Seat <?php echo $booking['seat_number']; ?>
                        </div>
                    </div>

                    <!-- Journey Details -->
                    <div class="section-title">Journey Details</div>

                    <div class="journey-details">
                        <div class="journey-row">
                            <div class="journey-label">From:</div>
                            <div><strong><?php echo $booking['origin']; ?></strong></div>
                        </div>
                        <div class="journey-row">
                            <div class="journey-label">To:</div>
                            <div><strong><?php echo $booking['destination']; ?></strong></div>
                        </div>
                        <div class="journey-row">
                            <div class="journey-label">Departure:</div>
                            <div><strong><?php echo date('d M Y, h:i A', strtotime($booking['departure_time'])); ?></strong></div>
                        </div>
                        <div class="journey-row">
                            <div class="journey-label">Arrival:</div>
                            <div><strong><?php echo date('d M Y, h:i A', strtotime($booking['arrival_time'])); ?></strong></div>
                        </div>
                    </div>

                    <!-- Passenger & Bus Details -->
                    <div class="details-container">
                        <div class="details-column">
                            <div class="section-title">Passenger Details</div>
                            <div class="detail-row">
                                <span class="detail-label">Name:</span>
                                <span><strong><?php echo $booking['passenger_name']; ?></strong></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">ID Number:</span>
                                <span><?php echo $booking['passenger_id_number'] ?: 'N/A'; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Phone:</span>
                                <span><?php echo $booking['passenger_phone']; ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Seat Number:</span>
                                <span><strong><?php echo $booking['seat_number']; ?></strong></span>
                            </div>
                        </div>

                        <div class="details-column">
                            <div class="section-title">Bus Details</div>
                            <div class="detail-row">
                                <span class="detail-label">Bus Name:</span>
                                <span><strong><?php echo $booking['bus_name']; ?></strong></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Bus Type:</span>
                                <span><?php echo ucfirst($booking['bus_type']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Payment:</span>
                                <span><?php echo ucfirst($booking['payment_method'] ?? 'Online'); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div class="payment-details">
                        <div>
                            <div class="section-title">Payment Details</div>
                            <div class="detail-row">
                                <span class="detail-label">Transaction Ref:</span>
                                <span><?php echo $booking['transaction_reference'] ?? 'IRE' . substr($booking['booking_reference'], 2); ?></span>
                            </div>
                        </div>
                        <div>
                            <div class="detail-label">Amount Paid:</div>
                            <div class="amount-paid"><?php echo formatCurrency($booking['amount']); ?></div>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div class="qr-container">
                        <img src="simple_qr.php?url=<?php echo urlencode($booking_url); ?>" alt="QR Code">
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
            </div>

            <!-- Additional Passenger Details (if multiple passengers) -->
            <?php if (count($bookings) > 1): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6 p-6">
                <h2 class="text-lg font-bold mb-3">All Passengers</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Seat</th>
                                <th>Passenger Name</th>
                                <th>Phone</th>
                                <th>ID Number</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td><?php echo $b['seat_number']; ?></td>
                                    <td><?php echo $b['passenger_name']; ?></td>
                                    <td><?php echo $b['passenger_phone']; ?></td>
                                    <td><?php echo $b['passenger_id_number'] ?: 'N/A'; ?></td>
                                    <td><?php echo formatCurrency($b['amount']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right font-bold">Total:</td>
                                <td class="font-bold"><?php echo formatCurrency(array_sum(array_column($bookings, 'amount'))); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">What would you like to do next?</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="print_ticket.php?reference=<?php echo $booking_reference; ?>"
                       class="flex flex-col items-center justify-center bg-blue-50 hover:bg-blue-100 text-blue-700 p-6 rounded-lg transition-all duration-300 border border-blue-200 hover:shadow-md"
                       target="_blank">
                        <div class="text-3xl mb-2"><i class="fas fa-print"></i></div>
                        <div class="font-semibold">Print Ticket</div>
                        <div class="text-xs text-center mt-1 text-blue-600">Print or save your ticket for your journey</div>
                    </a>

                    <a href="user/bookings.php"
                       class="flex flex-col items-center justify-center bg-green-50 hover:bg-green-100 text-green-700 p-6 rounded-lg transition-all duration-300 border border-green-200 hover:shadow-md">
                        <div class="text-3xl mb-2"><i class="fas fa-list"></i></div>
                        <div class="font-semibold">View All Bookings</div>
                        <div class="text-xs text-center mt-1 text-green-600">See all your bookings in one place</div>
                    </a>

                    <a href="index.php"
                       class="flex flex-col items-center justify-center bg-purple-50 hover:bg-purple-100 text-purple-700 p-6 rounded-lg transition-all duration-300 border border-purple-200 hover:shadow-md">
                        <div class="text-3xl mb-2"><i class="fas fa-home"></i></div>
                        <div class="font-semibold">Back to Home</div>
                        <div class="text-xs text-center mt-1 text-purple-600">Return to the homepage</div>
                    </a>
                </div>

                <div class="mt-6 text-center text-sm text-gray-500">
                    <p>Need help? Contact our support team at <strong>+254 700 000 000</strong> or email <strong>support@isioloraha.com</strong></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code is now generated server-side using Google Charts API -->

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>
