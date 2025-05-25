<?php
// Set page title
$page_title = "Frequently Asked Questions";

// Include header
require_once 'includes/templates/header.php';
?>

<!-- Hero Section -->
<section class="bg-primary-700 text-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-4">Frequently Asked Questions</h1>
        <p class="text-xl max-w-2xl mx-auto">
            Find answers to common questions about our services, booking process, and policies.
        </p>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <!-- FAQ Categories -->
            <div class="mb-8">
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="#booking" class="px-4 py-2 bg-primary-100 text-primary-700 rounded-full hover:bg-primary-200 transition duration-300">Booking & Tickets</a>
                    <a href="#payment" class="px-4 py-2 bg-primary-100 text-primary-700 rounded-full hover:bg-primary-200 transition duration-300">Payment</a>
                    <a href="#travel" class="px-4 py-2 bg-primary-100 text-primary-700 rounded-full hover:bg-primary-200 transition duration-300">Travel Information</a>
                    <a href="#cancellation" class="px-4 py-2 bg-primary-100 text-primary-700 rounded-full hover:bg-primary-200 transition duration-300">Cancellation & Refunds</a>
                    <a href="#luggage" class="px-4 py-2 bg-primary-100 text-primary-700 rounded-full hover:bg-primary-200 transition duration-300">Luggage</a>
                </div>
            </div>
            
            <!-- Booking & Tickets -->
            <div id="booking" class="mb-10">
                <h2 class="text-2xl font-bold mb-6 pb-2 border-b border-gray-200">Booking & Tickets</h2>
                
                <div class="space-y-6">
                    <!-- Question 1 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <button class="faq-question w-full flex justify-between items-center focus:outline-none">
                            <span class="text-lg font-semibold">How do I book a ticket?</span>
                            <i class="fas fa-chevron-down text-primary-600 transition-transform duration-300"></i>
                        </button>
                        <div class="faq-answer mt-4 text-gray-600 hidden">
                            <p>Booking a ticket with Isiolo Raha is easy! You can book online through our website by following these steps:</p>
                            <ol class="list-decimal ml-5 mt-2 space-y-1">
                                <li>Go to our homepage and use the search form to enter your origin, destination, travel date, and number of passengers.</li>
                                <li>Select your preferred bus from the search results.</li>
                                <li>Choose your seat(s) from the available options.</li>
                                <li>Enter passenger details.</li>
                                <li>Complete payment.</li>
                                <li>Receive your e-ticket via email or view it in your account dashboard.</li>
                            </ol>
                            <p class="mt-2">Alternatively, you can visit any of our booking offices across Kenya to book in person.</p>
                        </div>
                    </div>
                    
                    <!-- Question 2 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <button class="faq-question w-full flex justify-between items-center focus:outline-none">
                            <span class="text-lg font-semibold">Do I need to create an account to book a ticket?</span>
                            <i class="fas fa-chevron-down text-primary-600 transition-transform duration-300"></i>
                        </button>
                        <div class="faq-answer mt-4 text-gray-600 hidden">
                            <p>Yes, you need to create an account or log in to book tickets online. This helps us provide you with better service, allows you to view your booking history, and makes future bookings faster.</p>
                            <p class="mt-2">Creating an account is quick and easy - you just need to provide your name, email address, phone number, and create a password.</p>
                        </div>
                    </div>
                    
                    <!-- Question 3 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <button class="faq-question w-full flex justify-between items-center focus:outline-none">
                            <span class="text-lg font-semibold">How far in advance can I book a ticket?</span>
                            <i class="fas fa-chevron-down text-primary-600 transition-transform duration-300"></i>
                        </button>
                        <div class="faq-answer mt-4 text-gray-600 hidden">
                            <p>You can book tickets up to 30 days in advance. We recommend booking early, especially for popular routes or during peak travel seasons, to ensure you get your preferred seats and travel times.</p>
                        </div>
                    </div>
                    
                    <!-- Question 4 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <button class="faq-question w-full flex justify-between items-center focus:outline-none">
                            <span class="text-lg font-semibold">Do I need to print my ticket?</span>
                            <i class="fas fa-chevron-down text-primary-600 transition-transform duration-300"></i>
                        </button>
                        <div class="faq-answer mt-4 text-gray-600 hidden">
                            <p>No, you don't need to print your ticket. You can show the digital ticket on your smartphone or tablet at the bus station. However, you will need to present a valid ID that matches the name on the ticket.</p>
                            <p class="mt-2">If you prefer a printed ticket, you can print the e-ticket sent to your email or visit our booking office to get a printed copy.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment -->
            <div id="payment" class="mb-10">
                <h2 class="text-2xl font-bold mb-6 pb-2 border-b border-gray-200">Payment</h2>
                
                <div class="space-y-6">
                    <!-- Question 1 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <button class="faq-question w-full flex justify-between items-center focus:outline-none">
                            <span class="text-lg font-semibold">What payment methods do you accept?</span>
                            <i class="fas fa-chevron-down text-primary-600 transition-transform duration-300"></i>
                        </button>
                        <div class="faq-answer mt-4 text-gray-600 hidden">
                            <p>We accept the following payment methods:</p>
                            <ul class="list-disc ml-5 mt-2 space-y-1">
                                <li>Credit/Debit Cards (Visa, Mastercard) via Paystack</li>
                                <li>M-Pesa (Pay at station option)</li>
                                <li>Cash (at our booking offices)</li>
                            </ul>
                            <p class="mt-2">All online payments are processed securely through Paystack, a trusted payment gateway.</p>
                        </div>
                    </div>
                    
                    <!-- Question 2 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <button class="faq-question w-full flex justify-between items-center focus:outline-none">
                            <span class="text-lg font-semibold">Is it safe to pay online?</span>
                            <i class="fas fa-chevron-down text-primary-600 transition-transform duration-300"></i>
                        </button>
                        <div class="faq-answer mt-4 text-gray-600 hidden">
                            <p>Yes, it is completely safe to pay online. We use Paystack, a secure payment gateway that encrypts your card details and ensures that your payment information is protected. We do not store your card details on our servers.</p>
                        </div>
                    </div>
                    
                    <!-- Question 3 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <button class="faq-question w-full flex justify-between items-center focus:outline-none">
                            <span class="text-lg font-semibold">Can I pay for multiple tickets in one transaction?</span>
                            <i class="fas fa-chevron-down text-primary-600 transition-transform duration-300"></i>
                        </button>
                        <div class="faq-answer mt-4 text-gray-600 hidden">
                            <p>Yes, you can book and pay for multiple tickets in a single transaction. During the booking process, you can select the number of passengers and provide details for each passenger.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Travel Information -->
            <div id="travel" class="mb-10">
                <h2 class="text-2xl font-bold mb-6 pb-2 border-b border-gray-200">Travel Information</h2>
                
                <div class="space-y-6">
                    <!-- Question 1 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <button class="faq-question w-full flex justify-between items-center focus:outline-none">
                            <span class="text-lg font-semibold">How early should I arrive at the bus station?</span>
                            <i class="fas fa-chevron-down text-primary-600 transition-transform duration-300"></i>
                        </button>
                        <div class="faq-answer mt-4 text-gray-600 hidden">
                            <p>We recommend arriving at the bus station at least 30 minutes before the scheduled departure time. This allows sufficient time for ticket verification, luggage handling, and boarding.</p>
                            <p class="mt-2">During peak travel seasons or holidays, it's advisable to arrive even earlier, around 45-60 minutes before departure.</p>
                        </div>
                    </div>
                    
                    <!-- Question 2 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <button class="faq-question w-full flex justify-between items-center focus:outline-none">
                            <span class="text-lg font-semibold">What identification do I need to board the bus?</span>
                            <i class="fas fa-chevron-down text-primary-600 transition-transform duration-300"></i>
                        </button>
                        <div class="faq-answer mt-4 text-gray-600 hidden">
                            <p>You need to present a valid government-issued photo ID that matches the name on your ticket. Acceptable forms of ID include:</p>
                            <ul class="list-disc ml-5 mt-2 space-y-1">
                                <li>National ID card</li>
                                <li>Passport</li>
                                <li>Driver's license</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Question 3 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <button class="faq-question w-full flex justify-between items-center focus:outline-none">
                            <span class="text-lg font-semibold">What amenities are available on the bus?</span>
                            <i class="fas fa-chevron-down text-primary-600 transition-transform duration-300"></i>
                        </button>
                        <div class="faq-answer mt-4 text-gray-600 hidden">
                            <p>The amenities vary depending on the bus type:</p>
                            <ul class="list-disc ml-5 mt-2 space-y-1">
                                <li><strong>Standard Buses:</strong> Air conditioning, reclining seats, onboard entertainment</li>
                                <li><strong>Executive Buses:</strong> Air conditioning, spacious reclining seats, WiFi, USB charging ports, onboard entertainment</li>
                                <li><strong>Luxury Buses:</strong> Air conditioning, extra-wide reclining seats, WiFi, USB charging ports, onboard entertainment, complimentary refreshments</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Cancellation & Refunds -->
            <div id="cancellation" class="mb-10">
                <h2 class="text-2xl font-bold mb-6 pb-2 border-b border-gray-200">Cancellation & Refunds</h2>
                
                <div class="space-y-6">
                    <!-- Question 1 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <button class="faq-question w-full flex justify-between items-center focus:outline-none">
                            <span class="text-lg font-semibold">Can I cancel my booking?</span>
                            <i class="fas fa-chevron-down text-primary-600 transition-transform duration-300"></i>
                        </button>
                        <div class="faq-answer mt-4 text-gray-600 hidden">
                            <p>Yes, you can cancel your booking, but our refund policy depends on how far in advance you cancel:</p>
                            <ul class="list-disc ml-5 mt-2 space-y-1">
                                <li>Cancellations made more than 24 hours before departure: 75% refund</li>
                                <li>Cancellations made 12-24 hours before departure: 50% refund</li>
                                <li>Cancellations made less than 12 hours before departure: No refund</li>
                            </ul>
                            <p class="mt-2">To cancel your booking, log in to your account, go to "My Bookings," and select the booking you wish to cancel.</p>
                        </div>
                    </div>
                    
                    <!-- Question 2 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <button class="faq-question w-full flex justify-between items-center focus:outline-none">
                            <span class="text-lg font-semibold">How long does it take to process a refund?</span>
                            <i class="fas fa-chevron-down text-primary-600 transition-transform duration-300"></i>
                        </button>
                        <div class="faq-answer mt-4 text-gray-600 hidden">
                            <p>Refunds are typically processed within 7-10 business days. The time it takes for the refund to appear in your account depends on your payment method and bank processing times.</p>
                        </div>
                    </div>
                    
                    <!-- Question 3 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <button class="faq-question w-full flex justify-between items-center focus:outline-none">
                            <span class="text-lg font-semibold">Can I change my travel date or time?</span>
                            <i class="fas fa-chevron-down text-primary-600 transition-transform duration-300"></i>
                        </button>
                        <div class="faq-answer mt-4 text-gray-600 hidden">
                            <p>Yes, you can change your travel date or time, subject to availability and the following conditions:</p>
                            <ul class="list-disc ml-5 mt-2 space-y-1">
                                <li>Changes made more than 24 hours before departure: No fee</li>
                                <li>Changes made 12-24 hours before departure: 10% fee</li>
                                <li>Changes made less than 12 hours before departure: 25% fee</li>
                            </ul>
                            <p class="mt-2">If the new fare is higher than the original fare, you will need to pay the difference. If it's lower, the difference will be refunded according to our refund policy.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Luggage -->
            <div id="luggage" class="mb-10">
                <h2 class="text-2xl font-bold mb-6 pb-2 border-b border-gray-200">Luggage</h2>
                
                <div class="space-y-6">
                    <!-- Question 1 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <button class="faq-question w-full flex justify-between items-center focus:outline-none">
                            <span class="text-lg font-semibold">How much luggage can I bring?</span>
                            <i class="fas fa-chevron-down text-primary-600 transition-transform duration-300"></i>
                        </button>
                        <div class="faq-answer mt-4 text-gray-600 hidden">
                            <p>Each passenger is allowed:</p>
                            <ul class="list-disc ml-5 mt-2 space-y-1">
                                <li>One piece of luggage (up to 20kg) to be stored in the luggage compartment</li>
                                <li>One small carry-on bag (up to 5kg) that can fit under the seat or in the overhead compartment</li>
                            </ul>
                            <p class="mt-2">Additional luggage may be allowed subject to space availability and payment of extra charges.</p>
                        </div>
                    </div>
                    
                    <!-- Question 2 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <button class="faq-question w-full flex justify-between items-center focus:outline-none">
                            <span class="text-lg font-semibold">Are there any items I cannot bring on the bus?</span>
                            <i class="fas fa-chevron-down text-primary-600 transition-transform duration-300"></i>
                        </button>
                        <div class="faq-answer mt-4 text-gray-600 hidden">
                            <p>For safety and comfort reasons, the following items are not allowed on our buses:</p>
                            <ul class="list-disc ml-5 mt-2 space-y-1">
                                <li>Dangerous goods (explosives, flammable materials, etc.)</li>
                                <li>Weapons of any kind</li>
                                <li>Illegal substances</li>
                                <li>Unpleasant-smelling items</li>
                                <li>Live animals (except service animals)</li>
                                <li>Oversized items that cannot be safely stored</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Question 3 -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <button class="faq-question w-full flex justify-between items-center focus:outline-none">
                            <span class="text-lg font-semibold">Is my luggage insured?</span>
                            <i class="fas fa-chevron-down text-primary-600 transition-transform duration-300"></i>
                        </button>
                        <div class="faq-answer mt-4 text-gray-600 hidden">
                            <p>While we take utmost care of your luggage, we have limited liability for loss or damage. We recommend keeping valuable items, important documents, and fragile items in your carry-on bag.</p>
                            <p class="mt-2">For additional protection, we recommend obtaining travel insurance that covers luggage.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Still Have Questions -->
            <div class="bg-primary-50 rounded-lg p-8 text-center">
                <h2 class="text-2xl font-bold mb-4">Still Have Questions?</h2>
                <p class="text-gray-600 mb-6">
                    If you couldn't find the answer to your question, please don't hesitate to contact us.
                    Our customer support team is ready to assist you.
                </p>
                <a href="contact.php" class="btn-primary">
                    <i class="fas fa-envelope mr-2"></i> Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

<script>
    // FAQ Toggle
    document.addEventListener('DOMContentLoaded', function() {
        const faqQuestions = document.querySelectorAll('.faq-question');
        
        faqQuestions.forEach(question => {
            question.addEventListener('click', function() {
                const answer = this.nextElementSibling;
                const icon = this.querySelector('i');
                
                // Toggle answer visibility
                answer.classList.toggle('hidden');
                
                // Rotate icon
                if (answer.classList.contains('hidden')) {
                    icon.style.transform = 'rotate(0deg)';
                } else {
                    icon.style.transform = 'rotate(180deg)';
                }
            });
        });
        
        // Check if URL has hash and open that section
        if (window.location.hash) {
            const hash = window.location.hash;
            const targetQuestion = document.querySelector(`${hash} .faq-question`);
            if (targetQuestion) {
                setTimeout(() => {
                    targetQuestion.click();
                    targetQuestion.scrollIntoView({ behavior: 'smooth' });
                }, 100);
            }
        }
    });
</script>

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>
