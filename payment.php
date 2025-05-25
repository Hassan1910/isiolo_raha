<?php
// Include session configuration
require_once 'config/session_config.php';

// Include configuration
require_once 'config/config.php';

// Start session
session_start();

// Set proper CSP headers directly in PHP to allow all required resources including Paystack
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://js.paystack.co; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src 'self' data: https://* http://*; connect-src https://api.paystack.co; frame-src https://checkout.paystack.com;");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Set message
    setFlashMessage("error", "Please login to book tickets.");

    // Redirect to login page
    header("Location: login.php");
    exit();
}

// Check if booking data exists in session
if (!isset($_SESSION['booking_data']) || !isset($_SESSION['booking_data']['passenger_details'])) {
    // Set message
    setFlashMessage("error", "Please complete the booking process.");

    // Redirect to home page
    header("Location: index.php");
    exit();
}

// Include functions
require_once 'includes/functions.php';

// Include Paystack functions
require_once 'includes/paystack.php';

// Set page title
$page_title = "Payment";

// Include header
require_once 'includes/templates/header.php';

// Include database connection
$conn = require_once 'config/database.php';

// Get booking data from session
$booking_data = $_SESSION['booking_data'];
$schedule_id = $booking_data['schedule_id'];
$selected_seats = explode(',', $booking_data['selected_seats']);
$total_amount = $booking_data['total_amount'];
$passenger_details = $booking_data['passenger_details'];
$booking_reference = $booking_data['booking_reference'];

// Get schedule details
$sql = "SELECT s.id, s.departure_time, s.arrival_time, s.fare, s.status,
        r.origin, r.destination, r.distance,
        b.name AS bus_name, b.type AS bus_type
        FROM schedules s
        JOIN routes r ON s.route_id = r.id
        JOIN buses b ON s.bus_id = b.id
        WHERE s.id = ?";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("i", $schedule_id);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $schedule = $result->fetch_assoc();
        } else {
            setFlashMessage("error", "Schedule not found.");
            header("Location: index.php");
            exit();
        }
    } else {
        setFlashMessage("error", "Something went wrong. Please try again later.");
        header("Location: index.php");
        exit();
    }

    // Close statement
    $stmt->close();
}

// Get user details
$sql = "SELECT email FROM users WHERE id = ?";

if ($stmt = $conn->prepare($sql)) {
    // Bind variables to the prepared statement as parameters
    $stmt->bind_param("i", $_SESSION['user_id']);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
        }
    }

    // Close statement
    $stmt->close();
}
?>

<div class="bg-gradient-to-r from-blue-50 to-indigo-50 min-h-screen py-8">
    <div class="container mx-auto px-4 max-w-6xl">
        <!-- Progress Stepper -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center">
                        <i class="fas fa-search text-white"></i>
                    </div>
                    <span class="text-xs mt-2">Search</span>
                </div>
                <div class="flex-1 h-1 bg-primary-600 mx-2"></div>
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center">
                        <i class="fas fa-chair text-white"></i>
                    </div>
                    <span class="text-xs mt-2">Seats</span>
                </div>
                <div class="flex-1 h-1 bg-primary-600 mx-2"></div>
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <span class="text-xs mt-2">Details</span>
                </div>
                <div class="flex-1 h-1 bg-primary-600 mx-2"></div>
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full bg-primary-600 flex items-center justify-center">
                        <i class="fas fa-credit-card text-white"></i>
                    </div>
                    <span class="text-xs mt-2 font-medium">Payment</span>
                </div>
                <div class="flex-1 h-1 bg-gray-300 mx-2"></div>
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <i class="fas fa-ticket-alt text-white"></i>
                    </div>
                    <span class="text-xs mt-2">Confirmation</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Payment Card -->
            <div class="lg:col-span-2 order-2 lg:order-1">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-primary-500 to-primary-700 p-6">
                        <h2 class="text-2xl font-bold text-white flex items-center">
                            <i class="fas fa-lock mr-3"></i>
                            Secure Payment
                        </h2>
                        <p class="text-primary-100 mt-1">Complete your booking with our secure payment gateway</p>
                    </div>

                    <div class="p-6">
                        <!-- Payment Method Selection -->
                        <div class="mb-6">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-200 flex items-center mb-4">
                                <div class="bg-blue-500 text-white rounded-full p-2 mr-3">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div>
                                    <p class="text-blue-800 font-medium">Payment Information</p>
                                    <p class="text-sm text-blue-700">Payment system is currently being updated</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 mb-4">
                                <!-- Visa logo as inline SVG -->
                                <svg class="h-8" viewBox="0 0 780 500" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="780" height="500" fill="white"/>
                                    <path d="M293.2 348.73L316.57 149.27H362.29L338.85 348.73H293.2Z" fill="#00579F"/>
                                    <path d="M545.47 153.1C535.74 149.27 520.54 145 502.16 145C441.5 145 398.52 177.16 398.1 223.47C397.69 257.14 428.89 275.81 452.33 287.12C476.19 298.69 484.2 306.1 484.2 316.58C483.79 332.67 463.91 340.08 445.4 340.08C420.04 340.08 406.53 336.25 385.42 327.25L377.41 323.42L368.98 362.25C380.4 368.08 402.59 373.5 425.61 373.75C490.38 373.75 532.52 342 533.35 292.54C533.76 265.72 518.14 245.15 482.78 228.47C461.25 217.58 448.57 210.17 448.57 198.6C448.98 188.12 460.83 177.64 486.19 177.64C507.3 177.64 522.5 181.89 533.76 186.55L539.18 189.14L547.61 151.97L545.47 153.1Z" fill="#00579F"/>
                                    <path d="M651.46 149.27H616.43C603.75 149.27 594.43 152.68 589.01 166.75L507.58 348.73H572.35C572.35 348.73 582.08 323.42 584.22 318.38C590.88 318.38 652.29 318.38 660.71 318.38C662.44 324.97 667.86 348.73 667.86 348.73H725L651.46 149.27ZM602.33 285.99C607.33 273.22 626.16 223.47 626.16 223.47C625.75 224.18 630.34 212.76 633.31 205.77L637.07 221.92C637.07 221.92 648.91 276.64 651.46 285.99H602.33Z" fill="#00579F"/>
                                    <path d="M233.79 149.27L173.13 285.99L166.47 254.97C155.05 222.81 125.97 188.12 93.69 170.58L149.29 348.31H214.47L303.34 149.27H233.79Z" fill="#00579F"/>
                                    <path d="M107.2 149.27H7.49L6.66 153.52C82.85 170.58 132.63 209.41 154.15 254.97L131.96 170.58C128.11 156.93 118.79 149.69 107.2 149.27Z" fill="#FAA61A"/>
                                </svg>

                                <!-- Mastercard logo as inline SVG -->
                                <svg class="h-8" viewBox="0 0 131.39 86.9" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="131.39" height="86.9" rx="4" fill="white"/>
                                    <path d="M51.94 15.75H79.45V70.55H51.94V15.75Z" fill="#FF5F00"/>
                                    <path d="M54.22 43.15C54.22 32.29 59.38 22.55 67.3 15.75C61.43 11.13 54.1 8.35 46.2 8.35C27.31 8.35 12.09 23.57 12.09 42.46C12.09 61.35 27.31 76.57 46.2 76.57C54.1 76.57 61.43 73.79 67.3 69.17C59.38 62.37 54.22 52.63 54.22 43.15Z" fill="#EB001B"/>
                                    <path d="M119.3 42.46C119.3 61.35 104.08 76.57 85.19 76.57C77.29 76.57 69.96 73.79 64.09 69.17C72.01 62.37 77.17 52.63 77.17 43.15C77.17 32.29 72.01 22.55 64.09 15.75C69.96 11.13 77.29 8.35 85.19 8.35C104.08 8.35 119.3 23.57 119.3 42.46Z" fill="#F79E1B"/>
                                </svg>

                                <div class="bg-gray-100 rounded-md px-3 py-1 text-xs text-gray-600">
                                    <i class="fas fa-shield-alt mr-1"></i> 256-bit encryption
                                </div>
                            </div>
                        </div>

                        <!-- Payment Options -->
                        <div class="space-y-6">
                            <!-- Payment Information -->
                            <div class="bg-white rounded-lg border border-gray-200 p-6">
                                <div class="flex justify-between items-center mb-5">
                                    <div class="flex items-center">
                                        <i class="fas fa-credit-card text-primary-600 text-xl mr-3"></i>
                                        <div>
                                            <h3 class="font-semibold text-gray-800">Payment Options</h3>
                                            <p class="text-sm text-gray-600">Choose your preferred payment method</p>
                                        </div>
                                    </div>
                                    <span class="text-xl font-bold text-primary-600"><?php echo formatCurrency($total_amount); ?></span>
                                </div>

                                <!-- Payment Methods -->
                                <div class="space-y-4">
                                    <!-- Paystack Option -->
                                    <div class="border border-gray-200 rounded-lg p-4 hover:border-primary-500 transition-colors cursor-pointer">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center">
                                                <div class="w-6 h-6 rounded-full border-2 border-primary-500 flex items-center justify-center mr-3">
                                                    <div class="w-3 h-3 rounded-full bg-primary-500"></div>
                                                </div>
                                                <span class="font-medium">Pay with Paystack</span>
                                            </div>
                                            <img src="https://website-v3-assets.s3.amazonaws.com/assets/img/hero/Paystack-mark-white-twitter.png" alt="Paystack" class="h-8">
                                        </div>

                                        <p class="text-sm text-gray-600 mb-4">Secure payment via Paystack. We support Visa, Mastercard, and more.</p>

                                        <form id="paystack-payment-form">
                                            <input type="hidden" name="email" value="<?php echo $user['email']; ?>">
                                            <input type="hidden" name="amount" value="<?php echo $total_amount; ?>">
                                            <input type="hidden" name="reference" value="<?php echo $booking_reference; ?>">

                                            <button type="button" id="paystack-button" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-lg transition duration-300 ease-in-out flex items-center justify-center">
                                                <i class="fas fa-lock mr-2"></i>
                                                <span class="font-medium">Pay Now with Paystack</span>
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Manual Payment Option -->
                                    <div class="border border-gray-200 rounded-lg p-4 hover:border-primary-500 transition-colors cursor-pointer">
                                        <div class="flex items-center mb-3">
                                            <div class="w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center mr-3">
                                                <div class="w-3 h-3 rounded-full bg-gray-300"></div>
                                            </div>
                                            <span class="font-medium">Contact Customer Service</span>
                                        </div>

                                        <p class="text-sm text-gray-600 mb-4">Need assistance with your payment? Contact our customer service team.</p>

                                        <a href="contact.php" class="w-full bg-primary-600 hover:bg-primary-700 text-white py-3 px-4 rounded-lg transition duration-300 ease-in-out flex items-center justify-center">
                                            <i class="fas fa-phone-alt mr-2"></i>
                                            <span class="font-medium">Contact Customer Service</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="mt-4 text-center">
                                    <p class="text-sm text-gray-600 mb-2">Need assistance with your booking?</p>
                                    <a href="contact.php" class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                                        Contact our support team
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Security Info -->
                        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <i class="fas fa-lock text-primary-600 text-xl mb-2"></i>
                                <h4 class="text-sm font-medium">Secure Payment</h4>
                                <p class="text-xs text-gray-600">All transactions are secure and encrypted</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <i class="fas fa-shield-alt text-primary-600 text-xl mb-2"></i>
                                <h4 class="text-sm font-medium">Protected Info</h4>
                                <p class="text-xs text-gray-600">Your personal data is never shared</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <i class="fas fa-headset text-primary-600 text-xl mb-2"></i>
                                <h4 class="text-sm font-medium">24/7 Support</h4>
                                <p class="text-xs text-gray-600">Get help whenever you need it</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1 order-1 lg:order-2">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden sticky top-6">
                    <div class="bg-gray-800 p-6">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-receipt mr-2"></i> Trip Summary
                        </h2>
                        <p class="text-gray-300 text-sm">Booking Reference: <span class="font-medium"><?php echo $booking_reference; ?></span></p>
                    </div>

                    <div class="p-6 space-y-4">
                        <!-- Trip Details -->
                        <div class="flex items-start space-x-3 pb-3 border-b border-gray-100">
                            <div class="bg-primary-100 text-primary-700 p-2 rounded-lg">
                                <i class="fas fa-bus text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-medium"><?php echo $schedule['origin']; ?> to <?php echo $schedule['destination']; ?></h3>
                                <p class="text-sm text-gray-600"><?php echo $schedule['bus_name']; ?> (<?php echo ucfirst($schedule['bus_type']); ?>)</p>
                            </div>
                        </div>

                        <!-- Departure Details -->
                        <div class="flex items-start space-x-3 pb-3 border-b border-gray-100">
                            <div class="bg-primary-100 text-primary-700 p-2 rounded-lg">
                                <i class="fas fa-calendar-alt text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-medium">Departure Time</h3>
                                <p class="text-sm text-gray-600"><?php echo date('h:i A', strtotime($schedule['departure_time'])); ?></p>
                                <p class="text-sm text-gray-600"><?php echo date('d M, Y', strtotime($schedule['departure_time'])); ?></p>
                            </div>
                        </div>

                        <!-- Seats -->
                        <div class="flex items-start space-x-3 pb-3 border-b border-gray-100">
                            <div class="bg-primary-100 text-primary-700 p-2 rounded-lg">
                                <i class="fas fa-chair text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-medium">Selected Seats</h3>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    <?php foreach ($selected_seats as $seat): ?>
                                    <span class="inline-block bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded"><?php echo $seat; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="space-y-2 pt-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Fare per Seat</span>
                                <span><?php echo formatCurrency($schedule['fare']); ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Number of Seats</span>
                                <span><?php echo count($selected_seats); ?></span>
                            </div>
                            <div class="border-t border-gray-200 pt-2 mt-2">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Total Amount</span>
                                    <span class="font-bold text-xl text-primary-600"><?php echo formatCurrency($total_amount); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Passenger Count -->
                        <div class="bg-gray-50 p-3 rounded-lg mt-4">
                            <div class="flex items-center">
                                <i class="fas fa-users text-gray-500 mr-2"></i>
                                <span class="text-sm text-gray-700"><?php echo count($passenger_details); ?> Passenger<?php echo count($passenger_details) > 1 ? 's' : ''; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Paystack JavaScript Integration -->
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the Paystack button
    const paystackButton = document.getElementById('paystack-button');

    // Add click event listener to the Paystack button
    paystackButton.addEventListener('click', function(e) {
        e.preventDefault();

        // Get form values
        const email = document.querySelector('input[name="email"]').value;
        const amount = parseFloat(document.querySelector('input[name="amount"]').value);
        const reference = document.querySelector('input[name="reference"]').value;

        // Initialize Paystack payment
        const handler = PaystackPop.setup({
            key: '<?php echo PAYSTACK_PUBLIC_KEY; ?>',
            email: email,
            amount: amount * 100, // Convert to smallest currency unit (cents)
            currency: '<?php echo PAYSTACK_CURRENCY; ?>',
            ref: reference,
            callback: function(response) {
                // Show loading message
                alert('Payment completed! Processing your booking...');
                // Redirect to verification page
                window.location.href = 'paystack_callback.php?reference=' + response.reference;
            },
            onClose: function() {
                // Handle when the Paystack modal is closed
                console.log('Payment window closed');
            }
        });

        // Open the Paystack payment modal
        handler.openIframe();
    });
});
</script>

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>
