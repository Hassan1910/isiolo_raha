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
    setFlashMessage("error", "Please login to access your profile.");

    // Redirect to login page
    header("Location: ../login.php");
    exit();
}

// Set page title
$page_title = "My Profile";

// Include header
require_once '../includes/templates/header.php';

// Include database connection
$conn = require_once '../config/database.php';

// Initialize variables
$first_name = $last_name = $email = $phone = "";
$first_name_err = $last_name_err = $email_err = $phone_err = "";
$success_msg = "";

// Get user details
$sql = "SELECT first_name, last_name, email, phone FROM users WHERE id = ?";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("i", $_SESSION['user_id']);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $first_name = $user['first_name'];
            $last_name = $user['last_name'];
            $email = $user['email'];
            $phone = $user['phone'];
        }
    }

    // Close statement
    $stmt->close();
}

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate first name
    if (empty(trim($_POST["first_name"]))) {
        $first_name_err = "Please enter your first name.";
    } else {
        $first_name = trim($_POST["first_name"]);
    }
    
    // Validate last name
    if (empty(trim($_POST["last_name"]))) {
        $last_name_err = "Please enter your last name.";
    } else {
        $last_name = trim($_POST["last_name"]);
    }
    
    // Validate phone
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter your phone number.";
    } else {
        $phone = trim($_POST["phone"]);
    }
    
    // Check input errors before updating the database
    if (empty($first_name_err) && empty($last_name_err) && empty($phone_err)) {
        // Prepare an update statement
        $sql = "UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssi", $param_first_name, $param_last_name, $param_phone, $param_id);
            
            // Set parameters
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_phone = $phone;
            $param_id = $_SESSION['user_id'];
            
            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Update session variable
                $_SESSION["user_name"] = $first_name . " " . $last_name;
                
                // Set success message
                $success_msg = "Profile updated successfully.";
                
                // Log activity
                logActivity("Profile Update", "User updated their profile information");
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            
            // Close statement
            $stmt->close();
        }
    }
}
?>

<div class="bg-gray-100 py-6">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">My Profile</h1>
            <a href="dashboard.php" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <?php if (!empty($success_msg)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                    <p><?php echo $success_msg; ?></p>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" name="first_name" id="first_name" class="form-input <?php echo (!empty($first_name_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $first_name; ?>" required>
                        <?php if (!empty($first_name_err)): ?>
                            <p class="text-red-500 text-xs mt-1"><?php echo $first_name_err; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" name="last_name" id="last_name" class="form-input <?php echo (!empty($last_name_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $last_name; ?>" required>
                        <?php if (!empty($last_name_err)): ?>
                            <p class="text-red-500 text-xs mt-1"><?php echo $last_name_err; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Email (Read-only) -->
                <div>
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" class="form-input bg-gray-100" value="<?php echo $email; ?>" readonly>
                    <p class="text-xs text-gray-500 mt-1">Email cannot be changed. Contact support if you need to update your email.</p>
                </div>
                
                <!-- Phone -->
                <div>
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" name="phone" id="phone" class="form-input <?php echo (!empty($phone_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $phone; ?>" required>
                    <?php if (!empty($phone_err)): ?>
                        <p class="text-red-500 text-xs mt-1"><?php echo $phone_err; ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Submit Button -->
                <div>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i> Update Profile
                    </button>
                </div>
            </form>
            
            <div class="border-t border-gray-200 mt-8 pt-8">
                <h2 class="text-xl font-bold mb-4">Change Password</h2>
                <a href="../change_password.php" class="btn-secondary">
                    <i class="fas fa-key mr-2"></i> Change Password
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../includes/templates/footer.php';
?>
