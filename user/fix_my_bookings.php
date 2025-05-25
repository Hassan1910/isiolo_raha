<?php
// Include session configuration
require_once 'config/session_config.php';

// Start session
session_start();

// Include functions
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Set message
    setFlashMessage("error", "Please login to access this page.");

    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Set page title
$page_title = "Fix My Bookings";

// Include header
require_once '../includes/templates/header.php';

// Include database connection
$conn = require_once '../config/database.php';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['fix_bookings'])) {
    // Begin transaction
    $conn->begin_transaction();

    try {
        // Get user ID
        $user_id = $_SESSION['user_id'];

        // Get booking reference
        $booking_reference = $_POST['booking_reference'];

        // Update bookings with the specified reference
        $sql = "UPDATE bookings SET user_id = ? WHERE booking_reference = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $booking_reference);
        $stmt->execute();

        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        // Log activity
        logActivity("User", "Fixed booking with reference: " . $booking_reference);

        // Commit transaction
        $conn->commit();

        // Set success message
        setFlashMessage("success", "Successfully fixed booking with reference: " . $booking_reference);

        // Redirect to booking confirmation page
        header("Location: ../booking_confirmation.php?reference=" . $booking_reference);
        exit();
    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();

        // Set error message
        setFlashMessage("error", "Error fixing booking: " . $e->getMessage());
    }
}
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Fix My Booking</h1>
        <a href="dashboard.php" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Fix a Booking</h2>

        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6">
            <p>If you're experiencing issues with a booking, you can fix it by entering the booking reference below.</p>
            <p class="mt-2">This will associate the booking with your account, allowing you to view and manage it.</p>
        </div>

        <form method="post" action="" class="max-w-md">
            <div class="mb-4">
                <label for="booking_reference" class="form-label">Booking Reference</label>
                <input type="text" name="booking_reference" id="booking_reference" class="form-input" required>
                <p class="text-sm text-gray-500 mt-1">Enter the booking reference you received during booking.</p>
            </div>

            <button type="submit" name="fix_bookings" class="btn-primary">
                <i class="fas fa-wrench mr-2"></i> Fix Booking
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">My Recent Bookings</h2>

        <?php
        // Get user's recent bookings
        $sql = "SELECT DISTINCT booking_reference, MAX(created_at) as booking_date,
                (SELECT status FROM bookings b2 WHERE b2.booking_reference = b1.booking_reference LIMIT 1) as status
                FROM bookings b1
                WHERE user_id = ?
                GROUP BY booking_reference
                ORDER BY booking_date DESC
                LIMIT 5";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("i", $_SESSION['user_id']);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo '<div class="overflow-x-auto">';
                    echo '<table class="min-w-full bg-white border border-gray-200">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Reference</th>';
                    echo '<th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date</th>';
                    echo '<th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>';
                    echo '<th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';

                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td class="py-2 px-4 border-b border-gray-200">' . $row['booking_reference'] . '</td>';
                        echo '<td class="py-2 px-4 border-b border-gray-200">' . formatDate($row['booking_date']) . '</td>';
                        echo '<td class="py-2 px-4 border-b border-gray-200">';
                        echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ';

                        switch ($row['status']) {
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

                        echo '">' . ucfirst($row['status']) . '</span>';
                        echo '</td>';
                        echo '<td class="py-2 px-4 border-b border-gray-200">';
                        echo '<a href="../booking_confirmation.php?reference=' . $row['booking_reference'] . '" class="text-primary-600 hover:underline">View</a>';
                        echo '</td>';
                        echo '</tr>';
                    }

                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                } else {
                    echo '<div class="text-center py-4">';
                    echo '<p class="text-gray-500">You have no recent bookings.</p>';
                    echo '</div>';
                }
            } else {
                echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">';
                echo '<p>Error retrieving bookings: ' . $stmt->error . '</p>';
                echo '</div>';
            }

            // Close statement
            $stmt->close();
        }
        ?>
    </div>
</div>

<?php
// Include admin footer (no footer content)
require_once '../includes/templates/admin_footer.php';
?>
