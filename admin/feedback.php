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

// Set page title
$page_title = "Manage Feedback";

// Include database connection
$conn = require_once '../config/database.php';

// Start output buffering to capture content for the template
ob_start();

// Process status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $feedback_id = $_POST['feedback_id'];
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

// Get feedback entries
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT f.*, u.first_name, u.last_name, u.email as user_email
        FROM feedback f
        LEFT JOIN users u ON f.user_id = u.id
        WHERE 1=1";

// Add status filter if provided
if (!empty($status_filter)) {
    $sql .= " AND f.status = ?";
}

// Add search filter if provided
if (!empty($search)) {
    $sql .= " AND (f.name LIKE ? OR f.email LIKE ? OR f.subject LIKE ? OR f.message LIKE ?)";
}

$sql .= " ORDER BY f.created_at DESC";

$stmt = $conn->prepare($sql);

// Bind parameters based on filters
if (!empty($status_filter) && !empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("sssss", $status_filter, $search_param, $search_param, $search_param, $search_param);
} elseif (!empty($status_filter)) {
    $stmt->bind_param("s", $status_filter);
} elseif (!empty($search)) {
    $search_param = "%$search%";
    $stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
}

$stmt->execute();
$result = $stmt->get_result();
$feedback_entries = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $feedback_entries[] = $row;
    }
}

$stmt->close();

// Get feedback statistics
$sql = "SELECT
        COUNT(*) AS total_feedback,
        SUM(CASE WHEN status = 'unread' THEN 1 ELSE 0 END) AS unread_feedback,
        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) AS read_feedback,
        SUM(CASE WHEN status = 'responded' THEN 1 ELSE 0 END) AS responded_feedback
        FROM feedback";

$result = $conn->query($sql);
$stats = $result->fetch_assoc();
?>

<div class="bg-gray-100 py-6">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Manage Feedback</h1>
            <a href="index.php" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <!-- Feedback Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <!-- Total Feedback -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-comments text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600">Total Feedback</p>
                        <p class="text-2xl font-bold"><?php echo $stats['total_feedback'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <!-- Unread Feedback -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 text-red-600">
                        <i class="fas fa-envelope text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600">Unread</p>
                        <p class="text-2xl font-bold"><?php echo $stats['unread_feedback'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <!-- Read Feedback -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-envelope-open text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600">Read</p>
                        <p class="text-2xl font-bold"><?php echo $stats['read_feedback'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <!-- Responded Feedback -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-reply text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600">Responded</p>
                        <p class="text-2xl font-bold"><?php echo $stats['responded_feedback'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="space-y-4 md:space-y-0 md:flex md:items-end md:space-x-4">
                <!-- Status Filter -->
                <div class="flex-1">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-input">
                        <option value="">All Status</option>
                        <option value="unread" <?php echo $status_filter === 'unread' ? 'selected' : ''; ?>>Unread</option>
                        <option value="read" <?php echo $status_filter === 'read' ? 'selected' : ''; ?>>Read</option>
                        <option value="responded" <?php echo $status_filter === 'responded' ? 'selected' : ''; ?>>Responded</option>
                    </select>
                </div>

                <!-- Search -->
                <div class="flex-1">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" name="search" id="search" class="form-input" placeholder="Search by name, email, subject..." value="<?php echo $search; ?>">
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Feedback List -->
        <div class="bg-white rounded-lg shadow-md p-6 fade-in">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Feedback Messages</h2>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Click on a row to view details or use the action buttons
                </div>
            </div>

            <?php if (empty($feedback_entries)): ?>
                <div class="text-center py-8">
                    <p class="text-gray-500">No feedback messages found.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto rounded-lg shadow">
                    <table class="table border-collapse feedback-table">
                        <thead>
                            <tr class="bg-gray-100 border-b-2 border-gray-200">
                                <th class="px-4 py-3 text-center">ID</th>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Subject</th>
                                <th class="px-4 py-3 text-center">Date</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feedback_entries as $feedback): ?>
                                <tr onclick="window.location='feedback_details.php?id=<?php echo $feedback['id']; ?>'" class="border-b border-gray-200 hover:bg-gray-50 transition-colors duration-150 <?php echo $feedback['status'] === 'unread' ? 'bg-blue-50' : ''; ?> cursor-pointer">
                                    <td class="px-4 py-3 text-center">
                                        <span class="font-mono text-sm font-medium"><?php echo $feedback['id']; ?></span>
                                    </td>
                                    <td class="px-4 py-3 font-medium">
                                        <div class="flex items-center">
                                            <span class="font-medium"><?php echo $feedback['name']; ?></span>
                                            <?php if ($feedback['status'] === 'unread'): ?>
                                                <span class="unread-indicator"></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="email-cell flex items-center">
                                            <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                            <span class="truncate"><?php echo $feedback['email']; ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="subject-cell <?php echo $feedback['status'] === 'unread' ? 'text-gray-900' : 'text-gray-700'; ?>">
                                            <?php echo $feedback['subject']; ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="date-cell flex flex-col items-center">
                                            <span class="font-medium"><?php echo date('d M, Y', strtotime($feedback['created_at'])); ?></span>
                                            <span class="text-xs"><?php echo date('H:i', strtotime($feedback['created_at'])); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 status-cell">
                                        <?php if ($feedback['status'] === 'unread'): ?>
                                            <span class="badge badge-danger py-1 px-3">
                                                <i class="fas fa-envelope mr-1"></i> Unread
                                            </span>
                                        <?php elseif ($feedback['status'] === 'read'): ?>
                                            <span class="badge badge-warning py-1 px-3">
                                                <i class="fas fa-envelope-open mr-1"></i> Read
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-success py-1 px-3">
                                                <i class="fas fa-reply mr-1"></i> Responded
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 actions-cell">
                                        <div class="flex justify-center space-x-2" onclick="event.stopPropagation();">
                                            <a href="feedback_details.php?id=<?php echo $feedback['id']; ?>"
                                               class="btn-sm btn-primary">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>
                                            <?php if ($feedback['status'] === 'unread'): ?>
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="inline">
                                                <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                                <input type="hidden" name="status" value="read">
                                                <button type="submit" name="update_status"
                                                        class="btn-sm btn-secondary">
                                                    <i class="fas fa-check mr-1"></i> Mark Read
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Get the buffered content
$admin_content = ob_get_clean();

// Add JavaScript for enhanced UI
$admin_content .= <<<HTML
<script>
// Add row hover effect
document.addEventListener('DOMContentLoaded', function() {
    // Add animation to table rows
    const tableRows = document.querySelectorAll('.feedback-table tbody tr');
    tableRows.forEach((row, index) => {
        row.classList.add('fade-in');
        row.style.animationDelay = (index * 0.05) + 's';

        // Add hover effect
        row.addEventListener('mouseover', function() {
            this.classList.add('shadow-sm');
        });

        row.addEventListener('mouseout', function() {
            this.classList.remove('shadow-sm');
        });
    });

    // Add tooltip to status badges
    const badges = document.querySelectorAll('.badge');
    badges.forEach(badge => {
        badge.setAttribute('title', 'Click to change status');
    });
});
</script>
HTML;

// Include the admin template
require_once '../includes/templates/admin_template.php';
?>
