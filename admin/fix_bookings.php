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

// Set page title
$page_title = "Fix Bookings";

// Include header
require_once '../includes/templates/admin_header.php';

// Include database connection
$conn = require_once '../config/database.php';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['fix_bookings'])) {
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Get default user ID (admin)
        $admin_id = $_SESSION['user_id'];
        
        // Update bookings with null or 0 user_id
        $sql = "UPDATE bookings SET user_id = ? WHERE user_id IS NULL OR user_id = 0";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        
        // Log activity
        logActivity("Admin", "Fixed " . $affected_rows . " bookings with missing user_id");
        
        // Commit transaction
        $conn->commit();
        
        // Set success message
        setFlashMessage("success", "Successfully fixed " . $affected_rows . " bookings with missing user_id.");
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        
        // Set error message
        setFlashMessage("error", "Error fixing bookings: " . $e->getMessage());
    }
}

// Get bookings with null or 0 user_id
$sql = "SELECT id, booking_reference, schedule_id, seat_number, passenger_name, status, created_at 
        FROM bookings 
        WHERE user_id IS NULL OR user_id = 0";

$result = $conn->query($sql);
$invalid_bookings = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $invalid_bookings[] = $row;
    }
}
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Fix Bookings</h1>
        <a href="index.php" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Bookings with Missing User ID</h2>
        
        <?php if (empty($invalid_bookings)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                <p>No bookings with missing user ID found. All bookings are valid.</p>
            </div>
        <?php else: ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                <p>Found <?php echo count($invalid_bookings); ?> bookings with missing user ID. Click the button below to fix them.</p>
                <form method="post" action="" class="mt-2">
                    <button type="submit" name="fix_bookings" class="bg-primary-600 hover:bg-primary-500 text-white font-bold py-2 px-4 rounded">
                        Fix All Bookings
                    </button>
                </form>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reference</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Passenger</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Seat</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invalid_bookings as $booking): ?>
                            <tr>
                                <td class="py-2 px-4 border-b border-gray-200"><?php echo $booking['id']; ?></td>
                                <td class="py-2 px-4 border-b border-gray-200"><?php echo $booking['booking_reference']; ?></td>
                                <td class="py-2 px-4 border-b border-gray-200"><?php echo $booking['passenger_name']; ?></td>
                                <td class="py-2 px-4 border-b border-gray-200"><?php echo $booking['seat_number']; ?></td>
                                <td class="py-2 px-4 border-b border-gray-200">
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
                                </td>
                                <td class="py-2 px-4 border-b border-gray-200"><?php echo formatDate($booking['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include footer
require_once '../includes/templates/admin_footer.php';
?>


