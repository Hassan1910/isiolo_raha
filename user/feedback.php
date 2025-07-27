<?php
// Include session configuration
require_once '../config/session_config.php';

// Start session
session_start();

// Include functions
require_once '../includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Set message
    setFlashMessage("error", "Please login to access your feedback.");

    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Set page title
$page_title = "My Feedback";

// Include header
require_once '../includes/templates/header.php';

// Include database connection
$conn = require_once '../config/database.php';

// Get user's feedback entries
$sql = "SELECT id, subject, message, admin_response, response_date, status, created_at 
        FROM feedback 
        WHERE user_id = ? 
        ORDER BY created_at DESC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $feedback_entries = [];
    while ($row = $result->fetch_assoc()) {
        $feedback_entries[] = $row;
    }
    
    $stmt->close();
}

// Get feedback statistics for user
$sql = "SELECT 
        COUNT(*) AS total_feedback,
        SUM(CASE WHEN status = 'unread' THEN 1 ELSE 0 END) AS pending_feedback,
        SUM(CASE WHEN status = 'responded' THEN 1 ELSE 0 END) AS responded_feedback
        FROM feedback 
        WHERE user_id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();
    $stmt->close();
}
?>

<div class="bg-gray-50 min-h-screen">
    <!-- Breadcrumb navigation -->
    <div class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center text-sm text-gray-600">
                <a href="<?php echo APP_URL; ?>" class="hover:text-primary-600 transition-colors">Home</a>
                <i class="fas fa-chevron-right text-xs mx-2 text-gray-400"></i>
                <a href="dashboard.php" class="hover:text-primary-600 transition-colors">Dashboard</a>
                <i class="fas fa-chevron-right text-xs mx-2 text-gray-400"></i>
                <span class="font-medium text-gray-800">My Feedback</span>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">My Feedback</h1>
                <p class="text-gray-600">View your submitted feedback and admin responses</p>
            </div>
            <div class="flex space-x-3">
                <a href="dashboard.php" class="btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
                <a href="../contact.php" class="btn-primary">
                    <i class="fas fa-plus mr-2"></i> Submit New Feedback
                </a>
            </div>
        </div>

        <!-- Feedback Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Feedback -->
            <div class="bg-white rounded-lg shadow-md p-6 transform transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-comments text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Total Feedback</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total_feedback'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <!-- Pending Feedback -->
            <div class="bg-white rounded-lg shadow-md p-6 transform transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <i class="fas fa-clock text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Pending Response</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $stats['pending_feedback'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <!-- Responded Feedback -->
            <div class="bg-white rounded-lg shadow-md p-6 transform transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-reply text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-600 text-sm">Responded</p>
                        <p class="text-2xl font-bold text-gray-800"><?php echo $stats['responded_feedback'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback List -->
        <?php if (empty($feedback_entries)): ?>
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <div class="mb-6">
                    <i class="fas fa-comments text-6xl text-gray-300"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">No Feedback Yet</h3>
                <p class="text-gray-600 mb-6">You haven't submitted any feedback yet. We'd love to hear from you!</p>
                <a href="../contact.php" class="btn-primary">
                    <i class="fas fa-plus mr-2"></i> Submit Your First Feedback
                </a>
            </div>
        <?php else: ?>
            <!-- Feedback Cards -->
            <div class="space-y-6">
                <?php foreach ($feedback_entries as $feedback): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden transform transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                        <!-- Feedback Header -->
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800 mb-1"><?php echo htmlspecialchars($feedback['subject']); ?></h3>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        Submitted on <?php echo date('d M, Y \a\t H:i', strtotime($feedback['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <?php if ($feedback['status'] === 'unread'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-envelope mr-1"></i> Pending
                                        </span>
                                    <?php elseif ($feedback['status'] === 'read'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-envelope-open mr-1"></i> Under Review
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-reply mr-1"></i> Responded
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Feedback Content -->
                        <div class="p-6">
                            <!-- Original Message -->
                            <div class="mb-6">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                    <i class="fas fa-user mr-2 text-primary-500"></i>
                                    Your Message
                                </h4>
                                <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-primary-500">
                                    <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($feedback['message'])); ?></p>
                                </div>
                            </div>

                            <!-- Admin Response -->
                            <?php if (!empty($feedback['admin_response'])): ?>
                                <div class="border-t border-gray-200 pt-6">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                        <i class="fas fa-headset mr-2 text-green-500"></i>
                                        Admin Response
                                        <span class="ml-2 text-xs text-gray-500">
                                            (<?php echo date('d M, Y \a\t H:i', strtotime($feedback['response_date'])); ?>)
                                        </span>
                                    </h4>
                                    <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-500">
                                        <p class="text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($feedback['admin_response'])); ?></p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="border-t border-gray-200 pt-6">
                                    <div class="flex items-center justify-center py-8 text-gray-500">
                                        <div class="text-center">
                                            <i class="fas fa-clock text-3xl mb-2"></i>
                                            <p class="text-sm">Waiting for admin response...</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add some custom styles -->
<style>
.feedback-card {
    transition: all 0.3s ease;
}

.feedback-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.status-badge {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.response-section {
    animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}
</style>

<?php
// Include footer
require_once '../includes/templates/footer.php';
?>