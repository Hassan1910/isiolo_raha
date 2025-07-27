<?php
// Include session configuration
require_once '../config/session_config.php';

// Include functions
require_once '../includes/functions.php';

// Set page title
$page_title = 'User Management';

// Include database connection
$conn = require_once '../config/database.php';

// Get database connection
$mysqli = $conn;

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                $email = trim($_POST['email']);
                $full_name = trim($_POST['full_name']);
                $phone = trim($_POST['phone']);
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $role = $_POST['role'];
                
                // Check if email already exists
                $check_stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                
                if ($result->fetch_assoc()) {
                    $error_message = "Email already exists.";
                } else {
                    $stmt = $mysqli->prepare("INSERT INTO users (first_name, last_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssss", $full_name, $last_name, $email, $phone, $password, $role);
                    $last_name = ''; // Set empty last name
                    if ($stmt->execute()) {
                        $success_message = "User added successfully.";
                    } else {
                        $error_message = "Failed to add user.";
                    }
                }
                break;
            
            case 'delete_user':
                $user_id = $_POST['user_id'];
                $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute()) {
                    $success_message = "User deleted successfully.";
                } else {
                    $error_message = "Error deleting user.";
                }
                break;
        }
    }
}

// Get all users
$stmt = $mysqli->prepare("SELECT id, first_name, last_name, email, phone, role, created_at FROM users ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

// Get user statistics
$stats_stmt = $mysqli->prepare("
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_users,
        SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as regular_users
    FROM users
");
$stats_stmt->execute();
$result = $stats_stmt->get_result();
$stats = $result->fetch_assoc();

// Start output buffering to capture content for the template
ob_start();
?>

<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <!-- Admin Navigation Breadcrumb -->
    <div class="text-sm breadcrumbs mb-4">
        <ul class="flex items-center space-x-2 text-gray-600">
            <li><a href="index.php" class="hover:text-primary-600 transition-colors"><i class="fas fa-home mr-1"></i> Dashboard</a></li>
            <li><i class="fas fa-chevron-right text-xs mx-1"></i></li>
            <li class="text-primary-700 font-medium">User Management</li>
        </ul>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-users text-primary-600 mr-3"></i> User Management
        </h1>
        <a href="index.php" class="btn-secondary flex items-center transition-transform hover:-translate-x-1">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <!-- Display Flash Messages -->
    <?php displayFlashMessages(); ?>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="text-xl font-bold"><?php echo $stats['total_users']; ?></p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                <i class="fas fa-user-shield text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Administrators</p>
                <p class="text-xl font-bold"><?php echo $stats['admin_users']; ?></p>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                <i class="fas fa-user text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Regular Users</p>
                <p class="text-xl font-bold"><?php echo $stats['regular_users']; ?></p>
            </div>
        </div>
    </div>

    <!-- Users List -->
    <div class="bg-white rounded-lg shadow-md p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="p-2 rounded-full bg-primary-100 text-primary-600 mr-3">
                    <i class="fas fa-list"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800">All Users</h2>
            </div>

            <!-- Search and Filter Controls -->
            <div class="flex flex-col md:flex-row gap-3">
                <div class="relative">
                    <input type="text" id="user-search" placeholder="Search users..."
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search"></i>
                    </div>
                </div>

                <div class="flex gap-2">
                    <select id="filter-role" class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="all">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>

                    <button type="button" onclick="showAddUserModal()" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg shadow-sm transition-colors flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add User
                    </button>
                </div>
            </div>
        </div>

        <?php if (empty($users)): ?>
            <div class="text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                <div class="text-gray-400 text-5xl mb-3">
                    <i class="fas fa-users"></i>
                </div>
                <p class="text-gray-500 mb-2">No users found in the system.</p>
                <p class="text-gray-500 text-sm">Add your first user using the button above.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border-b border-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-user mr-2 text-gray-400"></i> User Details
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-envelope mr-2 text-gray-400"></i> Email
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-phone mr-2 text-gray-400"></i> Phone
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center">
                                    <i class="fas fa-tag mr-2 text-gray-400"></i> Role
                                </div>
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <div class="flex items-center justify-end">
                                    <i class="fas fa-cog mr-2 text-gray-400"></i> Actions
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="user-table-body">
                        <?php foreach ($users as $user): ?>
                            <tr class="user-row hover:bg-gray-50"
                                data-name="<?php echo strtolower(trim($user['first_name'] . ' ' . $user['last_name'])); ?>"
                                data-email="<?php echo strtolower($user['email']); ?>"
                                data-role="<?php echo $user['role']; ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full
                                            <?php echo ($user['role'] == 'admin') ? 'bg-purple-100 text-purple-600' : 'bg-green-100 text-green-600'; ?>">
                                            <i class="fas fa-<?php echo ($user['role'] == 'admin') ? 'user-shield' : 'user'; ?>"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars(trim($user['first_name'] . ' ' . $user['last_name'])); ?></div>
                                            <div class="text-xs text-gray-500">ID: <?php echo $user['id']; ?> | Added: <?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php echo ($user['role'] == 'admin') ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'; ?>">
                                        <i class="fas fa-<?php echo ($user['role'] == 'admin') ? 'user-shield' : 'user'; ?> mr-1"></i>
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors">
                                                <i class="fas fa-trash-alt mr-1"></i> Delete
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 rounded-md">
                                            <i class="fas fa-shield-alt mr-1"></i> Protected
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (for future implementation) -->
            <div class="flex items-center justify-between mt-6">
                <div class="text-sm text-gray-500">
                    Showing <span class="font-medium"><?php echo count($users); ?></span> users
                </div>

                <div class="hidden md:flex">
                    <!-- Pagination controls would go here -->
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="fixed inset-0 z-50 overflow-auto bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-user-plus text-primary-600 mr-2"></i> Add New User
            </h2>
            <button type="button" onclick="closeAddUserModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form method="POST" class="p-6">
            <input type="hidden" name="action" value="add_user">

            <div class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-envelope text-gray-400 mr-1"></i> Email Address
                    </label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           placeholder="user@example.com">
                </div>
                
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-user text-gray-400 mr-1"></i> Full Name
                    </label>
                    <input type="text" id="full_name" name="full_name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           placeholder="John Doe">
                </div>
                
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-phone text-gray-400 mr-1"></i> Phone Number
                    </label>
                    <input type="text" id="phone" name="phone"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           placeholder="+254 700 000000">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-lock text-gray-400 mr-1"></i> Password
                    </label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           placeholder="••••••••">
                </div>
                
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fas fa-user-tag text-gray-400 mr-1"></i> User Role
                    </label>
                    <select id="role" name="role"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="user">Regular User</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" onclick="closeAddUserModal()" 
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition-colors">
                    <i class="fas fa-times mr-2"></i> Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md shadow-sm transition-colors">
                    <i class="fas fa-save mr-2"></i> Add User
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Custom animations for modal */
#addUserModal > div {
    transition: transform 0.2s ease-out, opacity 0.2s ease-out;
    transform: scale(0.95);
    opacity: 0;
}

#addUserModal > div.scale-100 {
    transform: scale(1);
    opacity: 1;
}

#addUserModal > div.scale-95 {
    transform: scale(0.95);
    opacity: 0;
}

/* Fade in animation for alerts */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.alert {
    animation: fadeIn 0.3s ease-out forwards;
}
</style>

<script>
// User modal functions
function showAddUserModal() {
    document.getElementById('addUserModal').classList.remove('hidden');
    // Add animation classes
    setTimeout(() => {
        document.querySelector('#addUserModal > div').classList.add('scale-100');
        document.querySelector('#addUserModal > div').classList.remove('scale-95');
    }, 10);
}

function closeAddUserModal() {
    // Add animation classes for closing
    document.querySelector('#addUserModal > div').classList.add('scale-95');
    document.querySelector('#addUserModal > div').classList.remove('scale-100');
    
    // Delay hiding the modal to allow for animation
    setTimeout(() => {
        document.getElementById('addUserModal').classList.add('hidden');
    }, 200);
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('addUserModal');
    if (event.target === modal) {
        closeAddUserModal();
    }
}

// User search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const userSearch = document.getElementById('user-search');
    const roleFilter = document.getElementById('filter-role');
    const userRows = document.querySelectorAll('.user-row');

    function filterUsers() {
        const searchTerm = userSearch.value.toLowerCase();
        const roleValue = roleFilter.value;

        userRows.forEach(row => {
            const matchesSearch = row.dataset.name.includes(searchTerm) || 
                                row.dataset.email.includes(searchTerm);
            const matchesRole = roleValue === 'all' || row.dataset.role === roleValue;
            
            row.style.display = (matchesSearch && matchesRole) ? '' : 'none';
        });
    }

    if (userSearch) userSearch.addEventListener('input', filterUsers);
    if (roleFilter) roleFilter.addEventListener('change', filterUsers);
});
</script>

<?php
// Capture the content for the admin template
$admin_content = ob_get_clean();

// Include the admin template
include '../includes/templates/admin_template.php';
?>