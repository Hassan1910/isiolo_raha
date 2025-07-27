<?php
// Include session configuration
require_once 'config/session_config.php';

// Start session
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Set page title
$page_title = "Forgot Password";

// Include header
require_once 'includes/templates/header.php';

// Include database connection
$conn = require_once 'config/database.php';

// Include config for constants
require_once 'config/config.php';

// Include functions
require_once 'includes/functions.php';

// Initialize variables
$email = "";
$password = "";
$confirm_password = "";
$email_err = "";
$password_err = "";
$confirm_password_err = "";
$success_msg = "";

// Determine current step
$step = 1;
if (isset($_SESSION['reset_email'])) {
    $step = 2;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($step == 1) {
        // Step 1: Email Verification
        
        // Validate email
        if (empty(trim($_POST["email"]))) {
            $email_err = "Please enter your email address.";
        } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email address.";
        } else {
            $email = trim($_POST["email"]);
        }
        
        // Check if email exists in database
        if (empty($email_err)) {
            $sql = "SELECT id, first_name, email FROM users WHERE email = ?";
            
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $email);
                
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows == 1) {
                        // Email exists, store in session and proceed to step 2
                        $_SESSION['reset_email'] = $email;
                        $step = 2;
                        
                        // Log the activity
                        $user = $result->fetch_assoc();
                        logActivity('password_reset_requested', 'Password reset requested for email: ' . $email, $user['id']);
                    } else {
                        $email_err = "No account found with that email address.";
                    }
                } else {
                    $email_err = "Something went wrong. Please try again later.";
                }
                $stmt->close();
            }
        }
    } elseif ($step == 2) {
        // Step 2: Password Reset
        
        // Validate password
        if (empty(trim($_POST["password"]))) {
            $password_err = "Please enter a new password.";
        } elseif (strlen(trim($_POST["password"])) < 6) {
            $password_err = "Password must have at least 6 characters.";
        } else {
            $password = trim($_POST["password"]);
        }
        
        // Validate confirm password
        if (empty(trim($_POST["confirm_password"]))) {
            $confirm_password_err = "Please confirm your password.";
        } else {
            $confirm_password = trim($_POST["confirm_password"]);
            if (empty($password_err) && ($password != $confirm_password)) {
                $confirm_password_err = "Password confirmation does not match.";
            }
        }
        
        // Update password if no errors
        if (empty($password_err) && empty($confirm_password_err)) {
            $update_sql = "UPDATE users SET password = ? WHERE email = ?";
            
            if ($stmt = $conn->prepare($update_sql)) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT, ["cost" => HASH_COST]);
                $stmt->bind_param("ss", $hashed_password, $_SESSION['reset_email']);
                
                if ($stmt->execute()) {
                    // Get user ID for logging
                    $user_sql = "SELECT id FROM users WHERE email = ?";
                    $user_id = null;
                    if ($user_stmt = $conn->prepare($user_sql)) {
                        $user_stmt->bind_param("s", $_SESSION['reset_email']);
                        $user_stmt->execute();
                        $user_result = $user_stmt->get_result();
                        if ($user_result->num_rows == 1) {
                            $user_data = $user_result->fetch_assoc();
                            $user_id = $user_data['id'];
                        }
                        $user_stmt->close();
                    }
                    
                    // Log the activity
                    logActivity('password_reset_completed', 'Password reset completed successfully', $user_id);
                    
                    // Clear session data
                    unset($_SESSION['reset_email']);
                    
                    // Set success message
                    setFlashMessage("success", "Your password has been reset successfully. You can now login with your new password.");
                    
                    // Redirect to login page
                    header("Location: login.php");
                    exit();
                } else {
                    $password_err = "Something went wrong. Please try again later.";
                }
                $stmt->close();
            }
        }
    }
}
?>

<div class="flex flex-col md:flex-row max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden mt-10">
    <!-- Welcome Sidebar - Hidden on mobile, visible on md screens and up -->
    <div class="bg-primary-700 text-white md:w-1/3 p-8 hidden md:flex flex-col justify-center items-center">
        <div class="text-center">
            <img src="assets/images/isioloraha logo.png" alt="Isiolo Raha Logo" class="w-24 h-24 mx-auto mb-6" onerror="this.src='https://placehold.co/100x100/15803d/FFFFFF/png?text=IR'; this.onerror=null;">
            
            <?php if ($step == 1): ?>
                <h2 class="text-2xl font-bold mb-4">Reset Your Password</h2>
                <p class="mb-6">We'll help you get back into your account.</p>
                <div class="text-sm opacity-80">
                    <p>Enter your email address to verify your account and reset your password.</p>
                </div>
            <?php else: ?>
                <h2 class="text-2xl font-bold mb-4">Create New Password</h2>
                <p class="mb-6">You're almost done! Create a new secure password.</p>
                <div class="text-sm opacity-80">
                    <p>Email verified: <strong><?php echo htmlspecialchars($_SESSION['reset_email']); ?></strong></p>
                </div>
            <?php endif; ?>
            
            <div class="mt-8 text-sm">
                <p>Need help? Contact our support team</p>
                <p class="font-bold mt-2">+254 700 000 000</p>
                <p class="mt-1">support@isioloraha.com</p>
            </div>
        </div>
    </div>
    
    <!-- Form Section -->
    <div class="w-full md:w-2/3">
        <div class="py-4 px-6 bg-primary-700 text-white text-center md:hidden">
            <h2 class="text-2xl font-bold">
                <?php echo ($step == 1) ? 'Forgot Password' : 'Create New Password'; ?>
            </h2>
        </div>
        
        <div class="py-6 px-8">
            <?php if ($step == 1): ?>
                <!-- Step 1: Email Verification -->
                <h2 class="text-2xl font-bold mb-6 text-gray-800 hidden md:block">Forgot Password</h2>
                <p class="text-gray-600 mb-6">Enter your email address to verify your account.</p>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" data-validate="true">
                    <!-- Email -->
                    <div class="mb-6">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" class="form-input <?php echo (!empty($email_err)) ? 'border-red-500' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email address" required>
                        <?php if (!empty($email_err)): ?>
                            <p class="text-red-500 text-xs mt-1"><?php echo $email_err; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="mb-4">
                        <button type="submit" class="btn-primary w-full">
                            <i class="fas fa-search mr-2"></i> Verify Email
                        </button>
                    </div>
                    
                    <div class="text-center">
                        <a href="login.php" class="text-primary-600 hover:underline">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Login
                        </a>
                    </div>
                </form>
                
            <?php else: ?>
                <!-- Step 2: Password Reset -->
                <h2 class="text-2xl font-bold mb-6 text-gray-800 hidden md:block">Create New Password</h2>
                <p class="text-gray-600 mb-6">Enter your new password below.</p>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" data-validate="true">
                    <!-- New Password -->
                    <div class="mb-4">
                        <label for="password" class="form-label">New Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" class="form-input pr-10 <?php echo (!empty($password_err)) ? 'border-red-500' : ''; ?>" placeholder="Enter your new password" required>
                            <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 focus:outline-none">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if (!empty($password_err)): ?>
                            <p class="text-red-500 text-xs mt-1"><?php echo $password_err; ?></p>
                        <?php endif; ?>
                        <p class="text-gray-500 text-xs mt-1">Password must be at least 6 characters long</p>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="relative">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-input pr-10 <?php echo (!empty($confirm_password_err)) ? 'border-red-500' : ''; ?>" placeholder="Confirm your new password" required>
                            <button type="button" id="toggle-confirm-password" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 focus:outline-none">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if (!empty($confirm_password_err)): ?>
                            <p class="text-red-500 text-xs mt-1"><?php echo $confirm_password_err; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="mb-4">
                        <button type="submit" class="btn-primary w-full">
                            <i class="fas fa-key mr-2"></i> Reset Password
                        </button>
                    </div>
                    
                    <div class="text-center">
                        <a href="login.php" class="text-primary-600 hover:underline">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Login
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Toggle password visibility
    if (document.getElementById('toggle-password')) {
        togglePasswordVisibility('password', 'toggle-password');
    }
    if (document.getElementById('toggle-confirm-password')) {
        togglePasswordVisibility('confirm_password', 'toggle-confirm-password');
    }
</script>

<?php require_once 'includes/templates/footer.php'; ?>