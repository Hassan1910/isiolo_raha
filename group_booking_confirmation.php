<?php
// Include session configuration
require_once 'config/session_config.php';

// Start session
session_start();

// Include database connection
$conn = require_once 'config/database.php';

// Include functions
require_once 'includes/functions.php';

// Check if reference is provided
if (!isset($_GET['reference'])) {
    // Set message
    setFlashMessage("error", "Invalid booking reference.");

    // Redirect to home page
    header("Location: index.php");
    exit();
}

// Get reference from URL
$booking_reference = $_GET['reference'];

// Get group booking details
$sql = "SELECT gb.*, 
        s.departure_time, s.arrival_time,
        r.origin, r.destination,
        bs.name AS bus_name, bs.type AS bus_type
        FROM group_bookings gb
        JOIN schedules s ON gb.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        JOIN buses bs ON s.bus_id = bs.id
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
        setFlashMessage("error", "Something went wrong. Please try again later.");
        header("Location: index.php");
        exit();
    }

    // Close statement
    $stmt->close();
}

// Set page title
$page_title = "Group Booking Confirmation";

// Include header
require_once 'includes/templates/header.php';
?>

<div class="bg-gray-100 py-6">
    <div class="container mx-auto px-4">
        <!-- Success Message -->
        <?php if (isset($_SESSION['flash_messages']['success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p class="font-bold">Success!</p>
                <p><?php echo $_SESSION['flash_messages']['success']; ?></p>
            </div>
            <?php unset($_SESSION['flash_messages']['success']); ?>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-users text-primary-500 mr-3"></i> Group Booking Confirmation
                    </h1>
                    <p class="text-gray-500 mt-1">Your group booking has been confirmed!</p>
                </div>
                <div class="flex space-x-3">
                    <a href="print_group_ticket.php?reference=<?php echo $booking_reference; ?>" target="_blank" class="btn-primary flex items-center">
                        <i class="fas fa-print mr-2"></i> Print Tickets
                    </a>
                    <a href="index.php" class="btn-secondary flex items-center">
                        <i class="fas fa-home mr-2"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Group Booking Details -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-info-circle text-primary-500 mr-2"></i> Group Details
                    </h2>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Booking Reference</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($booking_reference); ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Group Name</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($group_booking['group_name']); ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Contact Person</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($group_booking['contact_person']); ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Contact Phone</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($group_booking['contact_phone']); ?></p>
                        </div>
                        <?php if (!empty($group_booking['contact_email'])): ?>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Contact Email</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($group_booking['contact_email']); ?></p>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Total Passengers</p>
                            <p class="font-semibold"><?php echo $group_booking['total_passengers']; ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Booking Status</p>
                            <p class="font-semibold">
                                <?php if ($group_booking['status'] == 'confirmed'): ?>
                                    <span class="text-green-600">Confirmed</span>
                                <?php elseif ($group_booking['status'] == 'pending'): ?>
                                    <span class="text-yellow-600">Pending</span>
                                <?php else: ?>
                                    <span class="text-red-600"><?php echo ucfirst($group_booking['status']); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Total Amount</p>
                            <p class="font-bold text-primary-600 text-xl"><?php echo formatCurrency($group_booking['total_amount']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-bus text-primary-500 mr-2"></i> Trip Details
                    </h2>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Route</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($group_booking['origin'] . ' to ' . $group_booking['destination']); ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Departure</p>
                            <p class="font-semibold"><?php echo date('d M Y, h:i A', strtotime($group_booking['departure_time'])); ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Arrival</p>
                            <p class="font-semibold"><?php echo date('d M Y, h:i A', strtotime($group_booking['arrival_time'])); ?></p>
                        </div>
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Bus</p>
                            <p class="font-semibold"><?php echo htmlspecialchars($group_booking['bus_name'] . ' (' . $group_booking['bus_type'] . ')'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Passenger List -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-users text-primary-500 mr-2"></i> Passenger List
                    </h2>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seat</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passenger Name</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Number</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td class="py-3 px-4 whitespace-nowrap"><?php echo htmlspecialchars($booking['seat_number']); ?></td>
                                    <td class="py-3 px-4 whitespace-nowrap"><?php echo htmlspecialchars($booking['passenger_name']); ?></td>
                                    <td class="py-3 px-4 whitespace-nowrap"><?php echo htmlspecialchars($booking['passenger_phone']); ?></td>
                                    <td class="py-3 px-4 whitespace-nowrap"><?php echo htmlspecialchars($booking['passenger_id_number'] ?: '-'); ?></td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <?php if ($booking['status'] == 'confirmed'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Confirmed
                                            </span>
                                        <?php elseif ($booking['status'] == 'pending'): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- QR Code for Booking -->
                    <div class="mt-8 text-center">
                        <h3 class="text-lg font-bold mb-3">Scan QR Code to View Booking</h3>
                        <div class="inline-block p-4 bg-white border border-gray-200 rounded-lg">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode(getBaseUrl() . '/check_booking.php?reference=' . $booking_reference); ?>" 
                                alt="QR Code" class="mx-auto">
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Scan this code to quickly access your booking details</p>
                    </div>

                    <!-- Important Notes -->
                    <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <h3 class="text-lg font-bold mb-2 text-yellow-800">Important Notes</h3>
                        <ul class="list-disc list-inside text-sm text-yellow-800 space-y-1">
                            <li>Please arrive at least 30 minutes before departure time.</li>
                            <li>Bring a valid ID for verification.</li>
                            <li>Print your tickets or have them ready on your phone.</li>
                            <li>For any changes to your booking, please contact our office at least 6 hours before departure.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>
