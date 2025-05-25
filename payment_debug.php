<?php
// Include configuration
require_once 'config/config.php';

// Include functions
require_once 'includes/functions.php';

// Set page title
$page_title = "Payment Debug";

// Include header
require_once 'includes/templates/header.php';

// Check if we have booking data in session
$has_booking_data = isset($_SESSION['booking_data']) && isset($_SESSION['booking_data']['passenger_details']);
$booking_data = $has_booking_data ? $_SESSION['booking_data'] : null;

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_email = '';

if ($is_logged_in) {
    // Include database connection
    $conn = require_once 'config/database.php';
    
    // Get user details
    $sql = "SELECT email FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                $user_email = $user['email'];
            }
        }
        $stmt->close();
    }
}
?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h1 class="text-2xl font-bold mb-4">Payment Debug Tool</h1>
        <p class="mb-4">This page helps diagnose issues with the Paystack payment integration.</p>
        
        <div class="bg-blue-50 p-4 rounded-lg mb-6">
            <h2 class="font-semibold mb-2">System Information</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li>PHP Version: <?php echo phpversion(); ?></li>
                <li>Server: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></li>
                <li>User Agent: <?php echo $_SERVER['HTTP_USER_AGENT']; ?></li>
                <li>User Logged In: <?php echo $is_logged_in ? 'Yes' : 'No'; ?></li>
                <li>User Email: <?php echo $user_email ? $user_email : 'Not available'; ?></li>
                <li>Booking Data Available: <?php echo $has_booking_data ? 'Yes' : 'No'; ?></li>
                <li>Paystack Public Key: <span class="font-mono text-xs"><?php echo PAYSTACK_PUBLIC_KEY; ?></span></li>
            </ul>
        </div>
        
        <?php if ($has_booking_data): ?>
        <div class="bg-green-50 p-4 rounded-lg mb-6">
            <h2 class="font-semibold mb-2">Booking Information</h2>
            <ul class="list-disc pl-5 space-y-1">
                <li>Reference: <?php echo $booking_data['booking_reference']; ?></li>
                <li>Total Amount: <?php echo formatCurrency($booking_data['total_amount']); ?></li>
                <li>Selected Seats: <?php echo $booking_data['selected_seats']; ?></li>
                <li>Passenger Count: <?php echo count($booking_data['passenger_details']); ?></li>
            </ul>
        </div>
        <?php else: ?>
        <div class="bg-yellow-50 p-4 rounded-lg mb-6">
            <h2 class="font-semibold mb-2">No Booking Data</h2>
            <p>There is no booking data in the session. Please start a new booking process.</p>
        </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Test Direct URL -->
            <div class="border rounded-lg p-4">
                <h3 class="font-semibold mb-2">Test Direct URL</h3>
                <p class="text-sm mb-4">This will open the Paystack checkout page directly.</p>
                
                <?php
                // Generate test parameters
                $test_amount = 100; // A small amount for testing
                $test_reference = "TEST_" . time();
                $test_email = $user_email ? $user_email : 'test@example.com';
                
                // Generate the direct Paystack URL
                $paystack_url = "https://checkout.paystack.com/";
                $amount_in_cents = (int)($test_amount * 100);
                
                $params = [
                    'key' => 'pk_test_c7f8306f56b3fe44259a7e8d8a025c3f69e8102b',
                    'email' => $test_email,
                    'amount' => $amount_in_cents,
                    'currency' => 'KES',
                    'ref' => $test_reference,
                    'callback_url' => APP_URL . '/index.php'
                ];
                $checkout_url = $paystack_url . '?' . http_build_query($params);
                ?>
                
                <a href="<?php echo $checkout_url; ?>" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded inline-block">
                    Test Direct URL (KES <?php echo $test_amount; ?>)
                </a>
            </div>
            
            <!-- Test Form Submission -->
            <div class="border rounded-lg p-4">
                <h3 class="font-semibold mb-2">Test Form Submission</h3>
                <p class="text-sm mb-4">This will submit a form to the Paystack checkout page.</p>
                
                <form action="https://checkout.paystack.com/" method="GET">
                    <input type="hidden" name="key" value="pk_test_c7f8306f56b3fe44259a7e8d8a025c3f69e8102b">
                    <input type="hidden" name="email" value="<?php echo $test_email; ?>">
                    <input type="hidden" name="amount" value="<?php echo $amount_in_cents; ?>">
                    <input type="hidden" name="currency" value="KES">
                    <input type="hidden" name="ref" value="<?php echo $test_reference; ?>">
                    <input type="hidden" name="callback_url" value="<?php echo APP_URL; ?>/index.php">
                    
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded">
                        Test Form Submission (KES <?php echo $test_amount; ?>)
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Troubleshooting Tips</h2>
        
        <div class="space-y-4">
            <div class="p-3 bg-gray-50 rounded-lg">
                <h3 class="font-medium">1. Check API Keys</h3>
                <p class="text-sm text-gray-600">Make sure your Paystack API keys are correct and properly formatted.</p>
            </div>
            
            <div class="p-3 bg-gray-50 rounded-lg">
                <h3 class="font-medium">2. Verify Currency Support</h3>
                <p class="text-sm text-gray-600">Ensure your Paystack account supports KES (Kenyan Shillings).</p>
            </div>
            
            <div class="p-3 bg-gray-50 rounded-lg">
                <h3 class="font-medium">3. Check Amount Format</h3>
                <p class="text-sm text-gray-600">The amount should be in the lowest denomination (cents) and an integer.</p>
            </div>
            
            <div class="p-3 bg-gray-50 rounded-lg">
                <h3 class="font-medium">4. Verify Callback URL</h3>
                <p class="text-sm text-gray-600">Make sure your callback URL is properly formatted and accessible.</p>
            </div>
            
            <div class="p-3 bg-gray-50 rounded-lg">
                <h3 class="font-medium">5. Try Different Browsers</h3>
                <p class="text-sm text-gray-600">Some browsers may have issues with the Paystack checkout page.</p>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>
