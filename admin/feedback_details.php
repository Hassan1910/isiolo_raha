<?php
// Include session configuration
require_once '../config/session_config.php';

// Start session
session_start();

// Include functions
require_once '../includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Set message
    setFlashMessage("error", "You do not have permission to access the admin dashboard.");

    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Check if feedback ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage("error", "Invalid feedback ID.");
    header("Location: feedback.php");
    exit();
}

$feedback_id = intval($_GET['id']);

// Set page title
$page_title = "Feedback Details";

// Include database connection
$conn = require_once '../config/database.php';

// Start output buffering to capture content for the template
ob_start();

// Process form submission for status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $status = $_POST['status'];

    // Update feedback status
    $sql = "UPDATE feedback SET status = ? WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $status, $feedback_id);

        if ($stmt->execute()) {
            setFlashMessage("success", "Feedback status updated successfully.");
        } else {
            setFlashMessage("error", "Error updating feedback status.");
        }

        $stmt->close();
    }
}

// Process form submission for sending response
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_response'])) {
    $response = trim($_POST['response']);

    if (empty($response)) {
        setFlashMessage("error", "Response cannot be empty.");
    } else {
        // In a real application, you would send an email here
        // For now, we'll just update the status to 'responded'

        $sql = "UPDATE feedback SET status = 'responded' WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $feedback_id);

            if ($stmt->execute()) {
                // Log the response
                logActivity("Feedback", "Responded to feedback ID: " . $feedback_id);

                setFlashMessage("success", "Response sent successfully.");
            } else {
                setFlashMessage("error", "Error sending response.");
            }

            $stmt->close();
        }
    }
}

// Get feedback details
$sql = "SELECT f.*, u.first_name, u.last_name, u.email as user_email
        FROM feedback f
        LEFT JOIN users u ON f.user_id = u.id
        WHERE f.id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $feedback_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $feedback = $result->fetch_assoc();

        // If the feedback was unread, mark it as read
        if ($feedback['status'] === 'unread') {
            $update_sql = "UPDATE feedback SET status = 'read' WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $feedback_id);
            $update_stmt->execute();
            $update_stmt->close();

            // Update the status in our current data
            $feedback['status'] = 'read';
        }
    } else {
        setFlashMessage("error", "Feedback not found.");
        header("Location: feedback.php");
        exit();
    }

    $stmt->close();
} else {
    setFlashMessage("error", "Error retrieving feedback details.");
    header("Location: feedback.php");
    exit();
}
?>

<div class="bg-gray-100 py-6">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Feedback Details</h1>
            <a href="feedback.php" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i> Back to Feedback
            </a>
        </div>

        <!-- Feedback Details -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-xl font-bold"><?php echo $feedback['subject']; ?></h2>
                    <p class="text-gray-600">
                        From: <?php echo $feedback['name']; ?> (<?php echo $feedback['email']; ?>)
                        <?php if (!empty($feedback['user_id'])): ?>
                            <span class="badge badge-info">Registered User</span>
                        <?php endif; ?>
                    </p>
                    <p class="text-gray-500 text-sm">
                        Submitted on <?php echo date('d M, Y H:i', strtotime($feedback['created_at'])); ?>
                    </p>
                </div>

                <div>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $feedback_id); ?>" method="post" class="flex items-center">
                        <input type="hidden" name="feedback_id" value="<?php echo $feedback_id; ?>">
                        <select name="status" class="form-input mr-2 w-auto">
                            <option value="unread" <?php echo $feedback['status'] === 'unread' ? 'selected' : ''; ?>>Unread</option>
                            <option value="read" <?php echo $feedback['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                            <option value="responded" <?php echo $feedback['status'] === 'responded' ? 'selected' : ''; ?>>Responded</option>
                        </select>
                        <button type="submit" name="update_status" class="btn-sm btn-primary">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <h3 class="font-bold mb-2">Message:</h3>
                <div class="bg-gray-50 p-4 rounded-md">
                    <?php echo nl2br(htmlspecialchars($feedback['message'])); ?>
                </div>
            </div>
        </div>

        <!-- Response Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Send Response</h2>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $feedback_id); ?>" method="post">
                <div class="mb-4">
                    <label for="response" class="form-label">Response Message</label>
                    <textarea name="response" id="response" rows="6" class="form-input" required></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" name="send_response" class="btn-primary">
                        <i class="fas fa-paper-plane mr-2"></i> Send Response
                    </button>
                </div>
            </form>

            <div class="mt-4 text-sm text-gray-500">
                <p>
                    <i class="fas fa-info-circle mr-1"></i>
                    This response will be sent to <?php echo $feedback['email']; ?> and the feedback will be marked as responded.
                </p>
            </div>
        </div>
    </div>
</div>

<?php
// Get the buffered content
$admin_content = ob_get_clean();

// Include the admin template
require_once '../includes/templates/admin_template.php';
?>

