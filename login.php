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
$page_title = "Login";

// Include header
require_once 'includes/templates/header.php';

// Include database connection
$conn = require_once 'config/database.php';

// Initialize variables
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Check input errors before authenticating
    if (empty($email_err) && empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, first_name, last_name, email, password, role FROM users WHERE email = ?";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_email);

            // Set parameters
            $param_email = $email;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();

                // Check if email exists
                if ($stmt->num_rows == 1) {
                    // Bind result variables
                    $stmt->bind_result($id, $first_name, $last_name, $email, $hashed_password, $role);

                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION["user_id"] = $id;
                            $_SESSION["user_email"] = $email;
                            $_SESSION["user_name"] = $first_name . " " . $last_name;
                            $_SESSION["user_role"] = $role;

                            // Log activity
                            logActivity("Login", "User logged in successfully");

                            // Check if there's a pending booking
                            if (isset($_SESSION['pending_booking']) && $role !== "admin") {
                                // Get pending booking details
                                $pending_booking = $_SESSION['pending_booking'];

                                // Determine where to redirect based on the stored return_to value
                                if ($pending_booking['return_to'] === 'select_seats.php') {
                                    // Redirect to select seats with the stored parameters
                                    $redirect_url = "select_seats.php?schedule_id=" . $pending_booking['schedule_id'] . "&passengers=" . $pending_booking['passengers'];
                                } else {
                                    // Default redirect to homepage if return_to is not recognized
                                    $redirect_url = "index.php";
                                }

                                // We'll keep the pending_booking data in the session
                                // It will be cleared in the destination page (e.g., select_seats.php)
                                header("Location: " . $redirect_url);
                            } else {
                                // No pending booking, redirect to appropriate page
                                if ($role === "admin") {
                                    header("Location: admin/index.php");
                                } else {
                                    header("Location: user/dashboard.php");
                                }
                            }
                            exit();
                        } else {
                            // Password is not valid
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    // Email doesn't exist
                    $login_err = "Invalid email or password.";
                }
            } else {
                $login_err = "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
}
?>

<div class="max-w-4xl mx-auto mt-10 mb-10">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Left Column: Logo and Information -->
        <div class="md:col-span-1 bg-primary-700 text-white rounded-lg shadow-md p-6 flex flex-col items-center justify-center">
            <img src="assets/images/isioloraha logo.png" alt="Isiolo Raha Logo" class="h-24 w-auto mb-6">

            <h2 class="text-2xl font-bold mb-4 text-center">Welcome Back!</h2>

            <?php if (isset($_SESSION['pending_booking'])): ?>
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mb-4 border border-white/20">
                <div class="text-center mb-2">
                    <i class="fas fa-ticket-alt text-2xl mb-2"></i>
                    <h3 class="font-bold">Continue Your Booking</h3>
                </div>
                <p class="text-sm mb-3">Sign in to continue with your bus booking from
                    <strong><?php
                        // Get origin and destination if available
                        if (isset($_SESSION['search_data'])) {
                            echo $_SESSION['search_data']['origin'] . ' to ' . $_SESSION['search_data']['destination'];
                        } else {
                            echo 'your selected route';
                        }
                    ?></strong>
                </p>
                <div class="text-xs opacity-75">Your booking details are saved and will be restored after login.</div>
            </div>
            <?php else: ?>
            <div class="mb-4 text-center">
                <p class="mb-2">Sign in to access your account and manage your bookings.</p>
                <p class="text-sm opacity-75">View your booking history, print tickets, and more.</p>
            </div>
            <?php endif; ?>

            <div class="mt-auto text-sm opacity-75 text-center">
                <p>Need help? Contact our support team</p>
                <p>+254 700 000 000 | support@isioloraha.com</p>
            </div>
        </div>

        <!-- Right Column: Login Form -->
        <div class="md:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="py-4 px-6 bg-primary-50 border-b border-primary-100">
                <h2 class="text-2xl font-bold text-primary-700">Login to Your Account</h2>
                <?php if (isset($_SESSION['pending_booking'])): ?>
                <p class="text-sm text-primary-600 mt-1">Sign in to continue with your booking</p>
                <?php endif; ?>
            </div>

            <div class="py-6 px-6">
                <?php if (!empty($login_err)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                        <p><?php echo $login_err; ?></p>
                    </div>
                <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" data-validate="true">
            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-input <?php echo (!empty($email_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $email; ?>" required>
                <?php if (!empty($email_err)): ?>
                    <p class="text-red-500 text-xs mt-1"><?php echo $email_err; ?></p>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div class="mb-6">
                <label for="password" class="form-label">Password</label>
                <div class="relative">
                    <input type="password" name="password" id="password" class="form-input pr-10 <?php echo (!empty($password_err)) ? 'border-red-500' : ''; ?>" required>
                    <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 focus:outline-none">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <?php if (!empty($password_err)): ?>
                    <p class="text-red-500 text-xs mt-1"><?php echo $password_err; ?></p>
                <?php endif; ?>
                <div class="text-right mt-1">
                    <a href="forgot_password.php" class="text-sm text-primary-600 hover:underline">Forgot Password?</a>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mb-4">
                <?php if (isset($_SESSION['pending_booking'])): ?>
                <button type="submit" class="btn-primary w-full py-3 text-lg relative overflow-hidden group">
                    <span class="absolute right-0 top-0 h-full w-12 bg-white/20 transform translate-x-12 skew-x-[-15deg] transition-transform duration-700 group-hover:translate-x-[-12rem]"></span>
                    <i class="fas fa-sign-in-alt mr-2"></i> Login & Continue Booking
                </button>
                <?php else: ?>
                <button type="submit" class="btn-primary w-full py-3">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </button>
                <?php endif; ?>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Don't have an account? <a href="register.php" class="text-primary-600 hover:underline font-medium">Register</a>
                </p>
            </div>
        </form>
    </div>
</div>
    </div>
</div>

<script>
    // Toggle password visibility
    togglePasswordVisibility('password', 'toggle-password');
</script>

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>

