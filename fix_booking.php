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
    setFlashMessage("error", "Please login to fix bookings.");
    
    // Redirect to login page
    header("Location: login.php");
    exit();
}

// Include database connection
$conn = require_once 'config/database.php';

// Set page title
$page_title = "Fix Booking";

// Include header
require_once 'includes/templates/header.php';

// Check if reference is provided
if (!isset($_GET['reference'])) {
    echo '<div class="container mx-auto px-4 py-8">';
    echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">';
    echo '<p>No booking reference provided.</p>';
    echo '</div>';
    echo '<a href="index.php" class="btn-primary">Return to Home</a>';
    echo '</div>';
    
    // Include footer
    require_once 'includes/templates/footer.php';
    exit();
}

$booking_reference = $_GET['reference'];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['fix_booking'])) {
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Update bookings with the specified reference
        $sql = "UPDATE bookings SET user_id = ? WHERE booking_reference = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $_SESSION['user_id'], $booking_reference);
        $stmt->execute();
        
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        
        // Log activity
        logActivity("Booking", "Fixed booking with reference: " . $booking_reference);
        
        // Commit transaction
        $conn->commit();
        
        // Set success message
        setFlashMessage("success", "Successfully fixed booking with reference: " . $booking_reference);
        
        // Redirect to booking confirmation page
        header("Location: booking_confirmation.php?reference=" . $booking_reference);
        exit();
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        
        // Set error message
        setFlashMessage("error", "Error fixing booking: " . $e->getMessage());
    }
}

// Get booking details
$sql = "SELECT b.id, b.booking_reference, b.user_id, b.schedule_id, b.seat_number, b.passenger_name, b.passenger_phone, 
        b.passenger_id_number, b.amount, b.status, b.created_at,
        s.departure_time, s.arrival_time,
        r.origin, r.destination,
        bs.name AS bus_name, bs.type AS bus_type
        FROM bookings b
        JOIN schedules s ON b.schedule_id = s.id
        JOIN routes r ON s.route_id = r.id
        JOIN buses bs ON s.bus_id = bs.id
        WHERE b.booking_reference = ?
        LIMIT 1";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("s", $booking_reference);
    
    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $booking = $result->fetch_assoc();
        } else {
            echo '<div class="container mx-auto px-4 py-8">';
            echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">';
            echo '<p>Booking not found with reference: ' . $booking_reference . '</p>';
            echo '</div>';
            echo '<a href="index.php" class="btn-primary">Return to Home</a>';
            echo '</div>';
            
            // Include footer
            require_once 'includes/templates/footer.php';
            exit();
        }
    } else {
        echo '<div class="container mx-auto px-4 py-8">';
        echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">';
        echo '<p>Error retrieving booking: ' . $stmt->error . '</p>';
        echo '</div>';
        echo '<a href="index.php" class="btn-primary">Return to Home</a>';
        echo '</div>';
        
        // Include footer
        require_once 'includes/templates/footer.php';
        exit();
    }
    
    // Close statement
    $stmt->close();
}
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Fix Booking</h1>
    
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Booking Details</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-600">Booking Reference</p>
                <p class="font-bold"><?php echo $booking['booking_reference']; ?></p>
            </div>
            
            <div>
                <p class="text-sm text-gray-600">Status</p>
                <p>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        <?php 
                        switch ($booking['status']) {
                            case 'confirmed':
                                echo 'bg-green-100 text-green-800';
                                break;
                            case 'pending':
                                echo 'bg-yellow-100 text-yellow-800';
                                break;
                            case 'cancelled':
                                echo 'bg-red-100 text-red-800';
                                break;
                            case 'completed':
                                echo 'bg-blue-100 text-blue-800';
                                break;
                            default:
                                echo 'bg-gray-100 text-gray-800';
                        }
                        ?>">
                        <?php echo ucfirst($booking['status']); ?>
                    </span>
                </p>
            </div>
            
            <div>
                <p class="text-sm text-gray-600">Route</p>
                <p class="font-bold"><?php echo $booking['origin']; ?> to <?php echo $booking['destination']; ?></p>
            </div>
            
            <div>
                <p class="text-sm text-gray-600">Passenger</p>
                <p class="font-bold"><?php echo $booking['passenger_name']; ?></p>
            </div>
            
            <div>
                <p class="text-sm text-gray-600">Seat Number</p>
                <p class="font-bold"><?php echo $booking['seat_number']; ?></p>
            </div>
            
            <div>
                <p class="text-sm text-gray-600">Amount</p>
                <p class="font-bold"><?php echo formatCurrency($booking['amount']); ?></p>
            </div>
        </div>
        
        <?php if ($booking['user_id'] == 0 || $booking['user_id'] == null): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 my-4">
                <p>This booking is not associated with any user account. Click the button below to associate it with your account.</p>
                
                <form method="post" action="" class="mt-4">
                    <input type="hidden" name="booking_reference" value="<?php echo $booking['booking_reference']; ?>">
                    <button type="submit" name="fix_booking" class="btn-primary">
                        <i class="fas fa-wrench mr-2"></i> Fix This Booking
                    </button>
                </form>
            </div>
        <?php elseif ($booking['user_id'] != $_SESSION['user_id']): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 my-4">
                <p>This booking is associated with another user account. You cannot modify it.</p>
            </div>
        <?php else: ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 my-4">
                <p>This booking is already associated with your account.</p>
                <p class="mt-2"><a href="booking_confirmation.php?reference=<?php echo $booking['booking_reference']; ?>" class="text-primary-600 hover:underline">View Booking Details</a></p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="text-center">
        <a href="index.php" class="btn-secondary">
            <i class="fas fa-home mr-2"></i> Return to Home
        </a>
    </div>
</div>

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>
