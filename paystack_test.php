<?php
// Display all errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include configuration
require_once 'config/config.php';

// Include functions
require_once 'includes/functions.php';

// Include Paystack functions
require_once 'includes/paystack.php';

// Set page title
$page_title = "Paystack Test";

// Generate test data
$test_email = 'test@example.com';
$test_amount = 100; // A small amount for testing
$test_reference = "TEST_" . time();
$callback_url = APP_URL . '/paystack_callback.php';

// Function to check if cURL is enabled
function is_curl_enabled() {
    return function_exists('curl_version');
}

// Function to check if a URL is accessible
function is_url_accessible($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $http_code >= 200 && $http_code < 300;
}

// Check if Paystack API is accessible
function is_paystack_accessible() {
    return is_url_accessible('https://api.paystack.co');
}

// Include header
require_once 'includes/templates/header.php';
?>

<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h1 class="text-2xl font-bold mb-4">Paystack Integration Test</h1>
        <p class="mb-4">This page helps diagnose issues with the Paystack payment integration.</p>

        <!-- System Information -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h2 class="text-lg font-semibold mb-2">System Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                    <p><strong>cURL Enabled:</strong> <?php echo is_curl_enabled() ? 'Yes' : 'No'; ?></p>
                    <p><strong>Paystack API Accessible:</strong> <?php echo is_paystack_accessible() ? 'Yes' : 'No'; ?></p>
                </div>
                <div>
                    <p><strong>Paystack Public Key:</strong> <?php echo substr(PAYSTACK_PUBLIC_KEY, 0, 10) . '...'; ?></p>
                    <p><strong>Paystack Secret Key:</strong> <?php echo substr(PAYSTACK_SECRET_KEY, 0, 10) . '...'; ?></p>
                    <p><strong>Paystack Currency:</strong> <?php echo PAYSTACK_CURRENCY; ?></p>
                </div>
            </div>
        </div>

        <!-- Test Configuration -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h2 class="text-lg font-semibold mb-2">Test Configuration</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p><strong>Test Email:</strong> <?php echo $test_email; ?></p>
                    <p><strong>Test Amount:</strong> <?php echo formatCurrency($test_amount, PAYSTACK_CURRENCY); ?></p>
                    <p><strong>Test Reference:</strong> <?php echo $test_reference; ?></p>
                </div>
                <div>
                    <p><strong>Callback URL:</strong> <?php echo $callback_url; ?></p>
                    <p><strong>App URL:</strong> <?php echo APP_URL; ?></p>
                </div>
            </div>
        </div>

        <!-- Test Buttons -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Test Direct URL -->
            <div class="border rounded-lg p-4">
                <h3 class="font-semibold mb-2">Test Direct URL</h3>
                <p class="text-sm mb-4">This will open the Paystack checkout page directly.</p>

                <?php
                // Generate the direct Paystack URL
                $checkout_url = getPaystackCheckoutUrl($test_email, $test_amount, $test_reference, $callback_url);
                ?>

                <a href="<?php echo $checkout_url; ?>" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded inline-block">
                    Test Direct URL (<?php echo formatCurrency($test_amount, PAYSTACK_CURRENCY); ?>)
                </a>
            </div>

            <!-- Test Inline Integration -->
            <div class="border rounded-lg p-4">
                <h3 class="font-semibold mb-2">Test Inline Integration</h3>
                <p class="text-sm mb-4">This will use the Paystack inline JavaScript integration.</p>

                <button id="paystack-inline-button" class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded">
                    Test Inline Integration (<?php echo formatCurrency($test_amount, PAYSTACK_CURRENCY); ?>)
                </button>
            </div>
        </div>
    </div>

    <!-- Troubleshooting Tips -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Troubleshooting Tips</h2>

        <div class="space-y-4">
            <div class="p-3 bg-gray-50 rounded-lg">
                <h3 class="font-medium">1. Check API Keys</h3>
                <p class="text-sm text-gray-600">Make sure your Paystack API keys are correct and properly formatted.</p>
            </div>

            <div class="p-3 bg-gray-50 rounded-lg">
                <h3 class="font-medium">2. Verify Currency Support</h3>
                <p class="text-sm text-gray-600">Ensure your Paystack account supports <?php echo PAYSTACK_CURRENCY; ?>.</p>
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
                <h3 class="font-medium">5. Check Network Connectivity</h3>
                <p class="text-sm text-gray-600">Ensure your server can connect to the Paystack API.</p>
            </div>
        </div>
    </div>
</div>

<!-- Paystack JavaScript Integration -->
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the Paystack button
    const paystackButton = document.getElementById('paystack-inline-button');

    // Add click event listener to the Paystack button
    paystackButton.addEventListener('click', function(e) {
        e.preventDefault();

        // Initialize Paystack payment
        const handler = PaystackPop.setup({
            key: '<?php echo PAYSTACK_PUBLIC_KEY; ?>',
            email: '<?php echo $test_email; ?>',
            amount: <?php echo $test_amount * 100; ?>, // Convert to smallest currency unit (cents)
            currency: '<?php echo PAYSTACK_CURRENCY; ?>',
            ref: '<?php echo $test_reference; ?>',
            callback: function(response) {
                // Show success message
                alert('Payment completed! Reference: ' + response.reference);
                // Redirect to verification page
                window.location.href = '<?php echo $callback_url; ?>?reference=' + response.reference;
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
