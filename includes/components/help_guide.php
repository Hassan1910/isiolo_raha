<?php
/**
 * Help Guide Component
 * 
 * Provides a help guide component for contextual help
 * 
 * @param string $page - The current page
 * @return void
 */

function renderHelpGuide($page = '') {
    // Define help content for different pages
    $helpContent = [
        'index' => [
            'title' => 'How to Book a Bus Ticket',
            'steps' => [
                'Select your origin and destination from the dropdown menus.',
                'Choose your travel date using the date picker.',
                'Select the number of passengers.',
                'Click "Search Buses" to see available options.',
                'On the results page, select your preferred bus.',
                'Choose your seats and complete the booking process.'
            ]
        ],
        'search_results' => [
            'title' => 'Finding the Right Bus',
            'steps' => [
                'Browse through the available buses for your route.',
                'Use the quick filters to sort by price, departure time, or duration.',
                'Click on a bus to view more details and select seats.',
                'You can use the filter button to modify your search criteria.'
            ]
        ],
        'select_seats' => [
            'title' => 'Selecting Your Seats',
            'steps' => [
                'Click on available seats (green) to select them.',
                'Click again on a selected seat to deselect it.',
                'Gray seats are already booked and cannot be selected.',
                'You need to select the exact number of seats as your passenger count.',
                'Once you\'ve selected your seats, click "Continue" to proceed.'
            ]
        ],
        'passenger_details' => [
            'title' => 'Entering Passenger Information',
            'steps' => [
                'Fill in the required details for each passenger.',
                'The first passenger\'s details will be pre-filled with your account information.',
                'ID number is optional but recommended for verification at the bus station.',
                'Ensure phone numbers are correct as they may be used for booking confirmations.',
                'Click "Continue to Payment" when all details are complete.'
            ]
        ],
        'payment' => [
            'title' => 'Completing Your Payment',
            'steps' => [
                'Review your booking details to ensure everything is correct.',
                'Choose your preferred payment method.',
                'Follow the instructions to complete the payment process.',
                'Wait for the confirmation page before closing the browser.',
                'You\'ll receive a booking confirmation with your ticket details.'
            ]
        ],
        'booking_confirmation' => [
            'title' => 'Your Booking is Confirmed',
            'steps' => [
                'Your booking is now confirmed and tickets are reserved.',
                'You can print your ticket or save it on your device.',
                'The QR code can be scanned at the bus station for verification.',
                'Your booking details are also available in your account under "My Bookings".',
                'If you need to cancel or modify your booking, please contact customer support.'
            ]
        ],
        'user_dashboard' => [
            'title' => 'Managing Your Account',
            'steps' => [
                'View your upcoming trips and booking history.',
                'Access your tickets and booking details.',
                'Update your profile information.',
                'Check for special offers and promotions.',
                'Contact customer support if you need assistance.'
            ]
        ]
    ];
    
    // Get help content for the current page
    $content = isset($helpContent[$page]) ? $helpContent[$page] : [
        'title' => 'Need Help?',
        'steps' => [
            'If you need assistance, please contact our customer support team.',
            'Call us at +254 700 000 000 or email support@isioloraha.com.',
            'Our support team is available Monday to Friday, 8:00 AM to 6:00 PM.',
            'For urgent matters, please call our 24/7 helpline at +254 700 000 001.'
        ]
    ];
?>
<div id="help-guide-button" class="fixed bottom-6 left-6 z-40">
    <button class="w-12 h-12 rounded-full bg-primary-600 text-white flex items-center justify-center shadow-lg transform transition-all duration-300 hover:bg-primary-500 hover:scale-110">
        <i class="fas fa-question"></i>
    </button>
</div>

<div id="help-guide-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="help-guide-content">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-question-circle text-primary-600 mr-2"></i> <?php echo $content['title']; ?>
            </h3>
            <button id="close-help-guide" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="mb-6">
            <div class="space-y-4">
                <?php foreach ($content['steps'] as $index => $step): ?>
                <div class="flex">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 mr-3">
                        <?php echo $index + 1; ?>
                    </div>
                    <div class="pt-1">
                        <p class="text-gray-700"><?php echo $step; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-600">
                <i class="fas fa-info-circle text-primary-600 mr-2"></i>
                Still need help? Contact our support team at <strong>support@isioloraha.com</strong> or call <strong>+254 700 000 000</strong>.
            </p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const helpGuideButton = document.getElementById('help-guide-button');
        const helpGuideModal = document.getElementById('help-guide-modal');
        const helpGuideContent = document.getElementById('help-guide-content');
        const closeHelpGuide = document.getElementById('close-help-guide');
        
        if (helpGuideButton && helpGuideModal && closeHelpGuide) {
            // Show help guide
            helpGuideButton.addEventListener('click', function() {
                helpGuideModal.classList.remove('hidden');
                setTimeout(() => {
                    helpGuideContent.classList.remove('scale-95', 'opacity-0');
                    helpGuideContent.classList.add('scale-100', 'opacity-100');
                }, 10);
            });
            
            // Hide help guide
            closeHelpGuide.addEventListener('click', function() {
                helpGuideContent.classList.remove('scale-100', 'opacity-100');
                helpGuideContent.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    helpGuideModal.classList.add('hidden');
                }, 300);
            });
            
            // Close on outside click
            helpGuideModal.addEventListener('click', function(e) {
                if (e.target === helpGuideModal) {
                    closeHelpGuide.click();
                }
            });
        }
    });
</script>
<?php
}
?>
