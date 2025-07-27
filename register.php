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
$page_title = "Register";

// Include header
require_once 'includes/templates/header.php';

// Include database connection
$conn = require_once 'config/database.php';

// Initialize variables
$first_name = $last_name = $email = $phone = $password = $confirm_password = "";
$first_name_err = $last_name_err = $email_err = $phone_err = $password_err = $confirm_password_err = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate first name
    if (empty(trim($_POST["first_name"]))) {
        $first_name_err = "Please enter your first name.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", trim($_POST["first_name"]))) {
        $first_name_err = "First name should only contain letters and spaces.";
    } elseif (strlen(trim($_POST["first_name"])) < 2) {
        $first_name_err = "First name must be at least 2 characters long.";
    } else {
        $first_name = trim($_POST["first_name"]);
    }

    // Validate last name
    if (empty(trim($_POST["last_name"]))) {
        $last_name_err = "Please enter your last name.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", trim($_POST["last_name"]))) {
        $last_name_err = "Last name should only contain letters and spaces.";
    } elseif (strlen(trim($_POST["last_name"])) < 2) {
        $last_name_err = "Last name must be at least 2 characters long.";
    } else {
        $last_name = trim($_POST["last_name"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE email = ?";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_email);

            // Set parameters
            $param_email = trim($_POST["email"]);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Store result
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $email_err = "This email is already taken.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Validate phone
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter your phone number.";
    } elseif (!preg_match("/^[+]?[0-9\s\-\(\)]+$/", trim($_POST["phone"]))) {
        $phone_err = "Phone number should only contain numbers, spaces, hyphens, parentheses, and plus sign.";
    } else {
        // Clean phone number (remove spaces, hyphens, parentheses)
        $cleaned_phone = preg_replace("/[^0-9+]/", "", trim($_POST["phone"]));
        
        // Validate phone number length (assuming Kenyan format)
        if (strlen($cleaned_phone) < 10 || strlen($cleaned_phone) > 13) {
            $phone_err = "Please enter a valid phone number (10-13 digits).";
        } else {
            $phone = trim($_POST["phone"]);
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input errors before inserting in database
    if (empty($first_name_err) && empty($last_name_err) && empty($email_err) && empty($phone_err) && empty($password_err) && empty($confirm_password_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO users (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssss", $param_first_name, $param_last_name, $param_email, $param_phone, $param_password);

            // Set parameters
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_email = $email;
            $param_phone = $phone;
            $param_password = password_hash($password, PASSWORD_BCRYPT, ["cost" => HASH_COST]);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Set success message
                setFlashMessage("success", "Registration successful!");

                // Store user data in session variables
                $_SESSION["user_id"] = $conn->insert_id;
                $_SESSION["user_email"] = $email;
                $_SESSION["user_name"] = $first_name . " " . $last_name;
                $_SESSION["user_role"] = "user"; // Default role for new users

                // Log activity
                logActivity("Registration", "User registered successfully");

                // Check if there's a pending booking
                if (isset($_SESSION['pending_booking'])) {
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
                    // No pending booking, redirect to user dashboard
                    header("Location: user/dashboard.php");
                }
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
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

            <h2 class="text-2xl font-bold mb-4 text-center">Create Your Account</h2>

            <?php if (isset($_SESSION['pending_booking'])): ?>
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 mb-4 border border-white/20">
                <div class="text-center mb-2">
                    <i class="fas fa-ticket-alt text-2xl mb-2"></i>
                    <h3 class="font-bold">Continue Your Booking</h3>
                </div>
                <p class="text-sm mb-3">Register to continue with your bus booking from
                    <strong><?php
                        // Get origin and destination if available
                        if (isset($_SESSION['search_data'])) {
                            echo $_SESSION['search_data']['origin'] . ' to ' . $_SESSION['search_data']['destination'];
                        } else {
                            echo 'your selected route';
                        }
                    ?></strong>
                </p>
                <div class="text-xs opacity-75">Your booking details are saved and will be restored after registration.</div>
            </div>
            <?php else: ?>
            <div class="mb-4 text-center">
                <p class="mb-2">Create an account to book tickets and manage your journeys.</p>
                <p class="text-sm opacity-75">Enjoy easy booking, ticket management, and more.</p>
            </div>
            <?php endif; ?>

            <div class="mt-auto text-sm opacity-75 text-center">
                <p>Need help? Contact our support team</p>
                <p>+254 700 000 000 | support@isioloraha.com</p>
            </div>
        </div>

        <!-- Right Column: Registration Form -->
        <div class="md:col-span-2 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="py-4 px-6 bg-primary-50 border-b border-primary-100">
                <h2 class="text-2xl font-bold text-primary-700">Register Now</h2>
                <?php if (isset($_SESSION['pending_booking'])): ?>
                <p class="text-sm text-primary-600 mt-1">Create an account to continue with your booking</p>
                <?php endif; ?>
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="py-6 px-6">
                <!-- First Name -->
                <div class="mb-4">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" name="first_name" id="first_name" class="form-input <?php echo (!empty($first_name_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $first_name; ?>" placeholder="Enter your first name" required>
                    <?php if (!empty($first_name_err)): ?>
                        <p class="text-red-500 text-xs mt-1"><?php echo $first_name_err; ?></p>
                    <?php endif; ?>
                </div>

                <!-- Last Name -->
                <div class="mb-4">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" name="last_name" id="last_name" class="form-input <?php echo (!empty($last_name_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $last_name; ?>" placeholder="Enter your last name" required>
                    <?php if (!empty($last_name_err)): ?>
                        <p class="text-red-500 text-xs mt-1"><?php echo $last_name_err; ?></p>
                    <?php endif; ?>
                </div>
            
            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-input <?php echo (!empty($email_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $email; ?>" placeholder="Enter your email address" required>
                <?php if (!empty($email_err)): ?>
                    <p class="text-red-500 text-xs mt-1"><?php echo $email_err; ?></p>
                <?php endif; ?>
            </div>

            <!-- Phone -->
            <div class="mb-4">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" name="phone" id="phone" class="form-input <?php echo (!empty($phone_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $phone; ?>" placeholder="Enter your phone number (e.g., +254 700 000 000)" required>
                <?php if (!empty($phone_err)): ?>
                    <p class="text-red-500 text-xs mt-1"><?php echo $phone_err; ?></p>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="relative">
                    <input type="password" name="password" id="password" class="form-input pr-10 <?php echo (!empty($password_err)) ? 'border-red-500' : ''; ?>" placeholder="Enter a strong password" required>
                    <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 focus:outline-none">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <?php if (!empty($password_err)): ?>
                    <p class="text-red-500 text-xs mt-1"><?php echo $password_err; ?></p>
                <?php endif; ?>
            </div>

            <!-- Confirm Password -->
            <div class="mb-6">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="relative">
                    <input type="password" name="confirm_password" id="confirm_password" class="form-input pr-10 <?php echo (!empty($confirm_password_err)) ? 'border-red-500' : ''; ?>" placeholder="Confirm your password" required>
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
                <?php if (isset($_SESSION['pending_booking'])): ?>
                <button type="submit" class="btn-primary w-full py-3 text-lg relative overflow-hidden group">
                    <span class="absolute right-0 top-0 h-full w-12 bg-white/20 transform translate-x-12 skew-x-[-15deg] transition-transform duration-700 group-hover:translate-x-[-12rem]"></span>
                    <i class="fas fa-user-plus mr-2"></i> Register & Continue Booking
                </button>
                <?php else: ?>
                <button type="submit" class="btn-primary w-full py-3">
                    <i class="fas fa-user-plus mr-2"></i> Register
                </button>
                <?php endif; ?>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Already have an account? <a href="login.php" class="text-primary-600 hover:underline font-medium">Login</a>
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
    togglePasswordVisibility('confirm_password', 'toggle-confirm-password');
</script>

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>
