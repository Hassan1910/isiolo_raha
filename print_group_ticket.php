<?php
// Include session configuration
require_once 'config/session_config.php';

// Start session
session_start();

// Include functions
require_once 'includes/functions.php';

// Check if reference is provided
if (!isset($_GET['reference'])) {
    die("Invalid booking reference.");
}

$booking_reference = $_GET['reference'];

// Include database connection
$conn = require_once 'config/database.php';

// Get group booking details
$sql = "SELECT gb.*, 
        s.departure_time, s.arrival_time,
        r.origin, r.destination,
        bs.name AS bus_name, bs.type AS bus_type, bs.registration_number,
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
            die("Group booking not found.");
        }
    } else {
        die("Something went wrong. Please try again later.");
    }

    // Close statement
    $stmt->close();
}

// Get individual bookings
$sql = "SELECT b.*, p.payment_method, p.status AS payment_status
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
        die("Something went wrong. Please try again later.");
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Booking Ticket - <?php echo htmlspecialchars($booking_reference); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @media print {
            body {
                font-size: 12pt;
                color: #000;
                background-color: #fff;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-after: always;
            }
            
            a {
                text-decoration: none;
                color: #000;
            }
            
            .container {
                width: 100%;
                max-width: 100%;
                padding: 0;
                margin: 0;
            }
            
            .ticket {
                border: 1px solid #000;
                page-break-inside: avoid;
            }
            
            .ticket-header {
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
            }
            
            .ticket-body {
                padding: 15px;
            }
            
            .qr-code {
                page-break-inside: avoid;
            }
            
            .group-info {
                margin-bottom: 20px;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .logo {
            max-width: 150px;
        }
        
        .print-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .group-info {
            background-color: #fff;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .group-info h2 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
        }
        
        .ticket {
            background-color: #fff;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            page-break-inside: avoid;
        }
        
        .ticket-header {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            border-radius: 5px 5px 0 0;
            display: flex;
            justify-content: space-between;
        }
        
        .ticket-body {
            padding: 15px;
        }
        
        .ticket-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .ticket-col {
            flex: 1;
        }
        
        .ticket-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .ticket-value {
            font-weight: bold;
        }
        
        .qr-code {
            text-align: center;
            margin-top: 15px;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="assets/images/isiolorahalogo.png" alt="Isiolo Raha Logo" class="logo">
            <button onclick="window.print()" class="print-btn no-print">
                <i class="fas fa-print"></i> Print Tickets
            </button>
        </div>
        
        <div class="group-info">
            <h2>Group Booking: <?php echo htmlspecialchars($group_booking['group_name']); ?></h2>
            
            <div class="info-row">
                <div>
                    <div class="info-label">Booking Reference</div>
                    <div><?php echo htmlspecialchars($booking_reference); ?></div>
                </div>
                <div>
                    <div class="info-label">Booking Date</div>
                    <div><?php echo date('d M Y, h:i A', strtotime($group_booking['created_at'])); ?></div>
                </div>
            </div>
            
            <div class="info-row">
                <div>
                    <div class="info-label">Contact Person</div>
                    <div><?php echo htmlspecialchars($group_booking['contact_person']); ?></div>
                </div>
                <div>
                    <div class="info-label">Contact Phone</div>
                    <div><?php echo htmlspecialchars($group_booking['contact_phone']); ?></div>
                </div>
            </div>
            
            <div class="info-row">
                <div>
                    <div class="info-label">Total Passengers</div>
                    <div><?php echo $group_booking['total_passengers']; ?></div>
                </div>
                <div>
                    <div class="info-label">Total Amount</div>
                    <div><?php echo formatCurrency($group_booking['total_amount']); ?></div>
                </div>
            </div>
            
            <div class="info-row">
                <div>
                    <div class="info-label">Route</div>
                    <div><?php echo htmlspecialchars($group_booking['origin'] . ' to ' . $group_booking['destination']); ?></div>
                </div>
                <div>
                    <div class="info-label">Bus</div>
                    <div><?php echo htmlspecialchars($group_booking['bus_name'] . ' (' . $group_booking['registration_number'] . ')'); ?></div>
                </div>
            </div>
            
            <div class="info-row">
                <div>
                    <div class="info-label">Departure</div>
                    <div><?php echo date('d M Y, h:i A', strtotime($group_booking['departure_time'])); ?></div>
                </div>
                <div>
                    <div class="info-label">Arrival</div>
                    <div><?php echo date('d M Y, h:i A', strtotime($group_booking['arrival_time'])); ?></div>
                </div>
            </div>
            
            <div class="qr-code">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode(getBaseUrl() . '/check_booking.php?reference=' . $booking_reference); ?>" 
                    alt="QR Code">
                <p>Scan to verify booking</p>
            </div>
        </div>
        
        <h2>Individual Tickets</h2>
        
        <?php foreach ($bookings as $index => $booking): ?>
            <div class="ticket">
                <div class="ticket-header">
                    <div>
                        <strong>Seat <?php echo htmlspecialchars($booking['seat_number']); ?></strong>
                    </div>
                    <div>
                        <strong>Ticket #<?php echo $index + 1; ?> of <?php echo count($bookings); ?></strong>
                    </div>
                </div>
                
                <div class="ticket-body">
                    <div class="ticket-row">
                        <div class="ticket-col">
                            <div class="ticket-label">Passenger Name</div>
                            <div class="ticket-value"><?php echo htmlspecialchars($booking['passenger_name']); ?></div>
                        </div>
                        <div class="ticket-col">
                            <div class="ticket-label">ID Number</div>
                            <div class="ticket-value"><?php echo htmlspecialchars($booking['passenger_id_number'] ?: 'N/A'); ?></div>
                        </div>
                    </div>
                    
                    <div class="ticket-row">
                        <div class="ticket-col">
                            <div class="ticket-label">From</div>
                            <div class="ticket-value"><?php echo htmlspecialchars($group_booking['origin']); ?></div>
                        </div>
                        <div class="ticket-col">
                            <div class="ticket-label">To</div>
                            <div class="ticket-value"><?php echo htmlspecialchars($group_booking['destination']); ?></div>
                        </div>
                    </div>
                    
                    <div class="ticket-row">
                        <div class="ticket-col">
                            <div class="ticket-label">Departure</div>
                            <div class="ticket-value"><?php echo date('d M Y, h:i A', strtotime($group_booking['departure_time'])); ?></div>
                        </div>
                        <div class="ticket-col">
                            <div class="ticket-label">Bus</div>
                            <div class="ticket-value"><?php echo htmlspecialchars($group_booking['bus_name']); ?></div>
                        </div>
                    </div>
                    
                    <div class="ticket-row">
                        <div class="ticket-col">
                            <div class="ticket-label">Group</div>
                            <div class="ticket-value"><?php echo htmlspecialchars($group_booking['group_name']); ?></div>
                        </div>
                        <div class="ticket-col">
                            <div class="ticket-label">Booking Ref</div>
                            <div class="ticket-value"><?php echo htmlspecialchars($booking_reference); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($index < count($bookings) - 1): ?>
                <div class="page-break"></div>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <div class="footer">
            <p>Thank you for choosing Isiolo Raha Bus Services!</p>
            <p>For inquiries, please call: 0700 000 000 | Email: info@isioloraha.com</p>
        </div>
    </div>
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            // Delay printing to ensure everything is loaded
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
