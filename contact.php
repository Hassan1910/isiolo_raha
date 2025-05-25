<?php
// Include session configuration
require_once 'config/session_config.php';

// Start session
session_start();

// Set page title
$page_title = "Contact Us";

// Include header
require_once 'includes/templates/header.php';

// Include database connection
$conn = require_once 'config/database.php';

// Initialize variables
$name = $email = $subject = $message = "";
$name_err = $email_err = $subject_err = $message_err = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate subject
    if (empty(trim($_POST["subject"]))) {
        $subject_err = "Please enter a subject.";
    } else {
        $subject = trim($_POST["subject"]);
    }

    // Validate message
    if (empty(trim($_POST["message"]))) {
        $message_err = "Please enter your message.";
    } else {
        $message = trim($_POST["message"]);
    }

    // Check input errors before inserting in database
    if (empty($name_err) && empty($email_err) && empty($subject_err) && empty($message_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO feedback (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("issss", $param_user_id, $param_name, $param_email, $param_subject, $param_message);

            // Set parameters
            $param_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $param_name = $name;
            $param_email = $email;
            $param_subject = $subject;
            $param_message = $message;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Set success message
                setFlashMessage("success", "Your message has been sent successfully. We will get back to you soon.");

                // Clear form
                $name = $email = $subject = $message = "";
            } else {
                setFlashMessage("error", "Oops! Something went wrong. Please try again later.");
            }

            // Close statement
            $stmt->close();
        }
    }
}
?>

<!-- Hero Section -->
<section class="relative bg-primary-700 text-white py-20">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
            <defs>
                <pattern id="contactPattern" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M0 20 L20 0 L40 20 L20 40 Z" fill="none" stroke="currentColor" stroke-width="1"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#contactPattern)"/>
        </svg>
    </div>

    <div class="container mx-auto px-4 text-center relative z-10">
        <div class="animate-fadeIn">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 tracking-tight">Contact Us</h1>
            <div class="w-24 h-1 bg-white mx-auto mb-6 rounded-full"></div>
            <p class="text-xl max-w-2xl mx-auto leading-relaxed">
                Have questions or need assistance? We're here to help. Reach out to our team using the form below.
            </p>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <!-- Section Title -->
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Get In Touch With Us</h2>
            <div class="w-16 h-1 bg-primary-500 mx-auto mb-4 rounded-full"></div>
            <p class="text-gray-600 max-w-2xl mx-auto">We're here to answer any questions you may have about our services. Reach out to us and we'll respond as soon as we can.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Contact Information -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-8 mb-8 transform transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
                        <span class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center text-white mr-3">
                            <i class="fas fa-headset"></i>
                        </span>
                        Contact Details
                    </h2>

                    <div class="space-y-8">
                        <!-- Address -->
                        <div class="flex items-start group">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center text-primary-600 group-hover:bg-primary-200 transition-all duration-300">
                                    <i class="fas fa-map-marker-alt text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Our Location</h3>
                                <p class="text-gray-600 mt-1 group-hover:text-primary-600 transition-all duration-300">123 Isiolo Road, Nairobi, Kenya</p>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="flex items-start group">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center text-primary-600 group-hover:bg-primary-200 transition-all duration-300">
                                    <i class="fas fa-phone-alt text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Phone Number</h3>
                                <p class="text-gray-600 mt-1 group-hover:text-primary-600 transition-all duration-300">+254 700 000 000</p>
                                <p class="text-gray-600 group-hover:text-primary-600 transition-all duration-300">+254 711 111 111</p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="flex items-start group">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center text-primary-600 group-hover:bg-primary-200 transition-all duration-300">
                                    <i class="fas fa-envelope text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Email Address</h3>
                                <a href="mailto:info@isioloraha.com" class="text-gray-600 mt-1 block hover:text-primary-600 transition-all duration-300">info@isioloraha.com</a>
                                <a href="mailto:support@isioloraha.com" class="text-gray-600 block hover:text-primary-600 transition-all duration-300">support@isioloraha.com</a>
                            </div>
                        </div>

                        <!-- Business Hours -->
                        <div class="flex items-start group">
                            <div class="flex-shrink-0 mt-1">
                                <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center text-primary-600 group-hover:bg-primary-200 transition-all duration-300">
                                    <i class="fas fa-clock text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-800">Business Hours</h3>
                                <p class="text-gray-600 mt-1 group-hover:text-primary-600 transition-all duration-300">Monday - Friday: 8:00 AM - 8:00 PM</p>
                                <p class="text-gray-600 group-hover:text-primary-600 transition-all duration-300">Saturday - Sunday: 9:00 AM - 5:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="bg-white rounded-xl shadow-lg p-8 transform transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
                        <span class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center text-white mr-3">
                            <i class="fas fa-share-alt"></i>
                        </span>
                        Connect With Us
                    </h2>

                    <div class="flex flex-wrap gap-4">
                        <a href="#" class="w-12 h-12 rounded-lg bg-blue-600 flex items-center justify-center text-white hover:bg-blue-700 transform transition-all duration-300 hover:scale-110 hover:rotate-3 hover:shadow-lg">
                            <i class="fab fa-facebook-f text-lg"></i>
                        </a>
                        <a href="#" class="w-12 h-12 rounded-lg bg-blue-400 flex items-center justify-center text-white hover:bg-blue-500 transform transition-all duration-300 hover:scale-110 hover:rotate-3 hover:shadow-lg">
                            <i class="fab fa-twitter text-lg"></i>
                        </a>
                        <a href="#" class="w-12 h-12 rounded-lg bg-pink-600 flex items-center justify-center text-white hover:bg-pink-700 transform transition-all duration-300 hover:scale-110 hover:rotate-3 hover:shadow-lg">
                            <i class="fab fa-instagram text-lg"></i>
                        </a>
                        <a href="#" class="w-12 h-12 rounded-lg bg-red-600 flex items-center justify-center text-white hover:bg-red-700 transform transition-all duration-300 hover:scale-110 hover:rotate-3 hover:shadow-lg">
                            <i class="fab fa-youtube text-lg"></i>
                        </a>
                        <a href="#" class="w-12 h-12 rounded-lg bg-green-600 flex items-center justify-center text-white hover:bg-green-700 transform transition-all duration-300 hover:scale-110 hover:rotate-3 hover:shadow-lg">
                            <i class="fab fa-whatsapp text-lg"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-8 transform transition-all duration-300 hover:shadow-xl">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
                        <span class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center text-white mr-3">
                            <i class="fas fa-envelope-open-text"></i>
                        </span>
                        Send Us a Message
                    </h2>

                    <?php if (isset($_SESSION['flash']) && $_SESSION['flash']['type'] === 'success'): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r flex items-center animate-fadeIn">
                        <div class="mr-3 text-xl">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <p><?php echo $_SESSION['flash']['message']; ?></p>
                    </div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" data-validate="true" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div class="relative group">
                                <label for="name" class="form-label flex items-center">
                                    <i class="fas fa-user text-primary-500 mr-2"></i>
                                    Your Name <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="text" name="name" id="name"
                                    class="form-input pl-10 transition-all duration-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 <?php echo (!empty($name_err)) ? 'border-red-500 bg-red-50' : 'border-gray-300'; ?>"
                                    value="<?php echo $name; ?>"
                                    placeholder="John Doe"
                                    required>
                                <div class="absolute left-3 top-[2.4rem] text-gray-400 pointer-events-none">
                                    <i class="fas fa-user"></i>
                                </div>
                                <?php if (!empty($name_err)): ?>
                                    <p class="text-red-500 text-xs mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        <?php echo $name_err; ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <!-- Email -->
                            <div class="relative group">
                                <label for="email" class="form-label flex items-center">
                                    <i class="fas fa-envelope text-primary-500 mr-2"></i>
                                    Your Email <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="email" name="email" id="email"
                                    class="form-input pl-10 transition-all duration-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 <?php echo (!empty($email_err)) ? 'border-red-500 bg-red-50' : 'border-gray-300'; ?>"
                                    value="<?php echo $email; ?>"
                                    placeholder="example@email.com"
                                    required>
                                <div class="absolute left-3 top-[2.4rem] text-gray-400 pointer-events-none">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <?php if (!empty($email_err)): ?>
                                    <p class="text-red-500 text-xs mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        <?php echo $email_err; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Subject -->
                        <div class="relative group">
                            <label for="subject" class="form-label flex items-center">
                                <i class="fas fa-tag text-primary-500 mr-2"></i>
                                Subject <span class="text-red-500 ml-1">*</span>
                            </label>
                            <input type="text" name="subject" id="subject"
                                class="form-input pl-10 transition-all duration-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 <?php echo (!empty($subject_err)) ? 'border-red-500 bg-red-50' : 'border-gray-300'; ?>"
                                value="<?php echo $subject; ?>"
                                placeholder="How can we help you?"
                                required>
                            <div class="absolute left-3 top-[2.4rem] text-gray-400 pointer-events-none">
                                <i class="fas fa-tag"></i>
                            </div>
                            <?php if (!empty($subject_err)): ?>
                                <p class="text-red-500 text-xs mt-1 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <?php echo $subject_err; ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Message -->
                        <div class="relative group">
                            <label for="message" class="form-label flex items-center">
                                <i class="fas fa-comment-alt text-primary-500 mr-2"></i>
                                Message <span class="text-red-500 ml-1">*</span>
                            </label>
                            <div class="relative message-container">
                                <textarea name="message" id="message" rows="8"
                                    class="form-input pl-10 pr-4 transition-all duration-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 resize-y message-textarea <?php echo (!empty($message_err)) ? 'border-red-500 bg-red-50' : 'border-gray-300'; ?>"
                                    placeholder="How can we assist you? Please provide details about your inquiry..."
                                    maxlength="1000"
                                    required><?php echo $message; ?></textarea>
                                <div class="absolute left-3 top-3 text-gray-400 pointer-events-none">
                                    <i class="fas fa-comment-alt"></i>
                                </div>

                                <!-- Character counter -->
                                <div class="absolute bottom-3 right-3 text-xs text-gray-500 character-counter">
                                    <span id="message-char-count">0</span>/1000
                                </div>

                                <!-- Resize handle indicator -->
                                <div class="absolute bottom-1 right-1 text-gray-400 pointer-events-none resize-handle-icon">
                                    <i class="fas fa-grip-lines-vertical fa-rotate-45 opacity-50"></i>
                                </div>
                            </div>

                            <!-- Message tools -->
                            <div class="flex justify-between items-center mt-2">
                                <!-- Message suggestions -->
                                <div class="flex flex-wrap gap-2 message-suggestions">
                                    <button type="button" class="text-xs bg-gray-100 hover:bg-primary-100 text-gray-700 hover:text-primary-700 px-2 py-1 rounded-full transition-all duration-200 suggestion-pill">
                                        <i class="fas fa-ticket-alt mr-1 text-primary-500"></i> Book a ticket
                                    </button>
                                    <button type="button" class="text-xs bg-gray-100 hover:bg-primary-100 text-gray-700 hover:text-primary-700 px-2 py-1 rounded-full transition-all duration-200 suggestion-pill">
                                        <i class="fas fa-question-circle mr-1 text-primary-500"></i> Help with booking
                                    </button>
                                    <button type="button" class="text-xs bg-gray-100 hover:bg-primary-100 text-gray-700 hover:text-primary-700 px-2 py-1 rounded-full transition-all duration-200 suggestion-pill">
                                        <i class="fas fa-route mr-1 text-primary-500"></i> Question about routes
                                    </button>
                                    <button type="button" class="text-xs bg-gray-100 hover:bg-primary-100 text-gray-700 hover:text-primary-700 px-2 py-1 rounded-full transition-all duration-200 suggestion-pill">
                                        <i class="fas fa-money-bill-wave mr-1 text-primary-500"></i> Refund request
                                    </button>
                                </div>

                                <!-- Clear button -->
                                <button type="button" id="clear-message" class="text-xs bg-gray-200 hover:bg-red-100 text-gray-700 hover:text-red-700 px-3 py-1 rounded-md transition-all duration-200 flex items-center">
                                    <i class="fas fa-eraser mr-1"></i> Clear
                                </button>
                            </div>

                            <?php if (!empty($message_err)): ?>
                                <p class="text-red-500 text-xs mt-1 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <?php echo $message_err; ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center pt-4">
                            <button type="submit" id="submit-btn" class="btn-primary group relative overflow-hidden px-8 py-4">
                                <span class="relative z-10 flex items-center justify-center">
                                    <i class="fas fa-paper-plane mr-2 group-hover:translate-x-1 transition-transform duration-300"></i>
                                    <span>Send Message</span>
                                </span>
                                <span class="absolute inset-0 bg-primary-700 scale-x-0 group-hover:scale-x-100 origin-left transition-transform duration-300"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <!-- Section Title -->
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">Find Us</h2>
            <div class="w-16 h-1 bg-primary-500 mx-auto mb-4 rounded-full"></div>
            <p class="text-gray-600 max-w-2xl mx-auto">Visit our office at 123 Isiolo Road, Nairobi, Kenya. We're conveniently located in the heart of the city.</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 transform transition-all duration-300 hover:shadow-xl">
            <div class="relative">
                <!-- Map Frame -->
                <div class="rounded-lg overflow-hidden">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.8177756766064!2d36.8170119!3d-1.2830731!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f10d22f42bf05%3A0x5f90b691d4f699be!2sNairobi%2C%20Kenya!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus"
                        width="100%"
                        height="500"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        class="rounded-lg shadow-inner">
                    </iframe>
                </div>

                <!-- Map Overlay Card -->
                <div class="absolute top-4 left-4 bg-white p-4 rounded-lg shadow-lg max-w-xs hidden md:block">
                    <h3 class="font-bold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-map-marker-alt text-primary-500 mr-2"></i>
                        Our Location
                    </h3>
                    <p class="text-gray-600 text-sm">123 Isiolo Road, Nairobi, Kenya</p>
                    <div class="mt-3">
                        <a href="https://goo.gl/maps/1234567890" target="_blank" class="text-primary-600 text-sm font-medium hover:text-primary-700 flex items-center">
                            <i class="fas fa-directions mr-1"></i> Get Directions
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div class="p-4 rounded-lg bg-gray-50 hover:bg-primary-50 transition-all duration-300">
                    <i class="fas fa-car text-primary-500 text-2xl mb-2"></i>
                    <h3 class="font-semibold text-gray-800">Parking Available</h3>
                    <p class="text-gray-600 text-sm">Free parking for our customers</p>
                </div>
                <div class="p-4 rounded-lg bg-gray-50 hover:bg-primary-50 transition-all duration-300">
                    <i class="fas fa-bus text-primary-500 text-2xl mb-2"></i>
                    <h3 class="font-semibold text-gray-800">Public Transport</h3>
                    <p class="text-gray-600 text-sm">Bus stops within 100m</p>
                </div>
                <div class="p-4 rounded-lg bg-gray-50 hover:bg-primary-50 transition-all duration-300">
                    <i class="fas fa-wheelchair text-primary-500 text-2xl mb-2"></i>
                    <h3 class="font-semibold text-gray-800">Accessibility</h3>
                    <p class="text-gray-600 text-sm">Wheelchair accessible entrance</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize contact form
    initContactForm();

    // Initialize animations
    initContactAnimations();
});

/**
 * Initialize contact form functionality
 */
function initContactForm() {
    const contactForm = document.querySelector('form[data-validate="true"]');
    const submitBtn = document.getElementById('submit-btn');
    const messageTextarea = document.getElementById('message');
    const messageCharCount = document.getElementById('message-char-count');
    const suggestionPills = document.querySelectorAll('.suggestion-pill');
    const clearMessageBtn = document.getElementById('clear-message');

    if (contactForm) {
        // Form submission
        contactForm.addEventListener('submit', function(event) {
            // Check if form is valid (browser validation will handle this)
            if (contactForm.checkValidity()) {
                // Show loading state
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <span class="relative z-10 flex items-center justify-center">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            <span>Sending...</span>
                        </span>
                    `;
                }

                // Form will submit normally
                return true;
            }
        });

        // Input focus effects
        const formInputs = contactForm.querySelectorAll('input, textarea');
        formInputs.forEach(input => {
            // Add focus animation
            input.addEventListener('focus', function() {
                if (this.tagName.toLowerCase() === 'textarea') {
                    this.closest('.message-container').parentElement.classList.add('scale-102');
                } else {
                    this.parentElement.classList.add('scale-102');
                }
                this.classList.add('border-primary-300');
            });

            // Remove focus animation
            input.addEventListener('blur', function() {
                if (this.tagName.toLowerCase() === 'textarea') {
                    this.closest('.message-container').parentElement.classList.remove('scale-102');
                } else {
                    this.parentElement.classList.remove('scale-102');
                }
                if (!this.value) {
                    this.classList.remove('border-primary-300');
                }
            });
        });

        // Message textarea character counter
        if (messageTextarea && messageCharCount) {
            // Update character count on load
            updateCharacterCount(messageTextarea, messageCharCount);

            // Update character count on input
            messageTextarea.addEventListener('input', function() {
                updateCharacterCount(this, messageCharCount);

                // Auto-resize textarea
                autoResizeTextarea(this);
            });

            // Initial auto-resize
            autoResizeTextarea(messageTextarea);
        }

        // Clear message button
        if (clearMessageBtn && messageTextarea && messageCharCount) {
            clearMessageBtn.addEventListener('click', function() {
                // Confirm before clearing if there's text
                if (messageTextarea.value.length > 0) {
                    if (confirm('Are you sure you want to clear your message?')) {
                        // Clear textarea
                        messageTextarea.value = '';

                        // Update character count
                        updateCharacterCount(messageTextarea, messageCharCount);

                        // Reset height
                        messageTextarea.dataset.userResized = 'false';
                        autoResizeTextarea(messageTextarea);

                        // Focus textarea
                        messageTextarea.focus();

                        // Add animation to button
                        this.classList.add('bg-red-200');
                        setTimeout(() => {
                            this.classList.remove('bg-red-200');
                        }, 300);
                    }
                }
            });
        }

        // Message suggestion pills
        if (suggestionPills.length > 0 && messageTextarea) {
            // Set animation delay for each pill
            suggestionPills.forEach((pill, index) => {
                // Set custom property for animation delay
                pill.style.setProperty('--pill-index', index);

                // Add click event
                pill.addEventListener('click', function() {
                    // Get suggestion text
                    const suggestionText = this.textContent.trim();

                    // Add suggestion to textarea
                    if (messageTextarea.value) {
                        messageTextarea.value += '\n\n' + suggestionText + ': ';
                    } else {
                        messageTextarea.value = suggestionText + ': ';
                    }

                    // Update character count
                    updateCharacterCount(messageTextarea, messageCharCount);

                    // Auto-resize textarea
                    messageTextarea.dataset.userResized = 'false'; // Reset user resize flag
                    autoResizeTextarea(messageTextarea);

                    // Add highlight effect to textarea
                    messageTextarea.classList.add('highlight-textarea');
                    setTimeout(() => {
                        messageTextarea.classList.remove('highlight-textarea');
                    }, 1000);

                    // Focus textarea and move cursor to end
                    messageTextarea.focus();
                    messageTextarea.setSelectionRange(messageTextarea.value.length, messageTextarea.value.length);

                    // Add animation to pill
                    this.classList.add('bg-primary-200');
                    setTimeout(() => {
                        this.classList.remove('bg-primary-200');
                    }, 300);

                    // Disable other pills temporarily
                    suggestionPills.forEach(otherPill => {
                        if (otherPill !== this) {
                            otherPill.classList.add('opacity-50');
                            setTimeout(() => {
                                otherPill.classList.remove('opacity-50');
                            }, 500);
                        }
                    });
                });
            });
        }
    }
}

/**
 * Update character count for textarea
 */
function updateCharacterCount(textarea, countElement) {
    const currentLength = textarea.value.length;
    const maxLength = textarea.getAttribute('maxlength');

    // Update count
    countElement.textContent = currentLength;

    // Update color based on length
    if (currentLength > maxLength * 0.7) {
        countElement.parentElement.classList.add('text-orange-500');
    } else {
        countElement.parentElement.classList.remove('text-orange-500');
    }

    if (currentLength > maxLength * 0.9) {
        countElement.parentElement.classList.add('text-red-500');
        countElement.parentElement.classList.remove('text-orange-500');
    } else {
        countElement.parentElement.classList.remove('text-red-500');
    }

    // Add visual feedback when approaching limit
    if (currentLength > maxLength * 0.95) {
        textarea.classList.add('border-red-400');
    } else {
        textarea.classList.remove('border-red-400');
    }
}

/**
 * Auto-resize textarea based on content
 */
function autoResizeTextarea(textarea) {
    // Don't auto-resize if user has manually resized
    if (textarea.dataset.userResized === 'true') {
        return;
    }

    // Get computed styles
    const computedStyle = window.getComputedStyle(textarea);

    // Calculate padding
    const paddingTop = parseFloat(computedStyle.paddingTop);
    const paddingBottom = parseFloat(computedStyle.paddingBottom);

    // Reset height to auto to get correct scrollHeight
    textarea.style.height = 'auto';

    // Calculate line height (fallback to 1.6em if not set)
    const lineHeight = parseFloat(computedStyle.lineHeight) || parseFloat(computedStyle.fontSize) * 1.6;

    // Set minimum height (8 rows)
    const minHeight = lineHeight * 8 + paddingTop + paddingBottom;

    // Set maximum height (20 rows or max-height from CSS)
    const cssMaxHeight = parseFloat(computedStyle.maxHeight);
    const maxHeight = Math.min(lineHeight * 20 + paddingTop + paddingBottom, cssMaxHeight || Infinity);

    // Calculate new height based on content
    const newHeight = Math.min(Math.max(textarea.scrollHeight, minHeight), maxHeight);

    // Apply new height
    textarea.style.height = newHeight + 'px';

    // Add resize detection
    if (!textarea.dataset.resizeListenerAdded) {
        textarea.dataset.resizeListenerAdded = 'true';

        // Create resize observer to detect manual resizing
        const resizeObserver = new ResizeObserver(entries => {
            for (let entry of entries) {
                const height = entry.contentRect.height;

                // If height is different from what we set, user has manually resized
                if (Math.abs(height - newHeight) > 5) {
                    textarea.dataset.userResized = 'true';
                }
            }
        });

        // Observe textarea
        resizeObserver.observe(textarea);

        // Reset user resized flag when content changes significantly
        textarea.addEventListener('input', function(e) {
            // If content changes by more than 50 characters, reset user resized flag
            if (Math.abs(this.value.length - (this.dataset.lastLength || 0)) > 50) {
                this.dataset.userResized = 'false';
            }

            // Store current length
            this.dataset.lastLength = this.value.length;
        });
    }
}

/**
 * Initialize animations for contact page
 */
function initContactAnimations() {
    // Animate contact info items on scroll
    const contactItems = document.querySelectorAll('.flex.items-start.group');

    if (contactItems.length > 0) {
        // Create observer
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    // Add delay based on index
                    setTimeout(() => {
                        entry.target.classList.add('animate-fadeIn');
                    }, index * 150);

                    // Unobserve after animation
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1
        });

        // Observe each item
        contactItems.forEach(item => {
            observer.observe(item);
            // Add initial state
            item.style.opacity = '0';
        });
    }

    // Add hover effect to social media icons
    const socialIcons = document.querySelectorAll('.flex.flex-wrap.gap-4 a');
    socialIcons.forEach(icon => {
        icon.addEventListener('mouseover', function() {
            this.classList.add('animate-bounce');
            setTimeout(() => {
                this.classList.remove('animate-bounce');
            }, 1000);
        });
    });
}
</script>

<style>
/* Additional styles for contact page */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fadeIn {
    animation: fadeIn 0.5s ease-out forwards;
}

.scale-102 {
    transform: scale(1.02);
    z-index: 10;
}

/* Improved form focus styles */
.form-input:focus {
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
}

/* Bounce animation */
@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}

.animate-bounce {
    animation: bounce 1s;
}

/* Message box styles */
.message-container {
    position: relative;
    transition: all 0.3s ease;
    margin-bottom: 0.5rem;
}

.message-textarea {
    transition: all 0.3s ease;
    min-height: 200px;
    max-height: 500px;
    line-height: 1.6;
    font-size: 1rem;
    padding-top: 1rem;
    padding-bottom: 1.5rem;
    resize: vertical; /* Allow vertical resizing */
    border-width: 2px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05) inset;
}

.message-textarea:focus {
    background-color: #fafffe;
    border-color: #86efac;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2), 0 2px 5px rgba(0, 0, 0, 0.05) inset;
}

.message-textarea::placeholder {
    color: #9ca3af;
    font-style: italic;
    opacity: 0.8;
}

.message-textarea:focus::placeholder {
    opacity: 0.6;
}

.character-counter {
    transition: all 0.3s ease;
    opacity: 0.7;
    font-weight: 500;
    background-color: rgba(255, 255, 255, 0.8);
    padding: 0.1rem 0.3rem;
    border-radius: 0.25rem;
}

.message-textarea:focus ~ .character-counter {
    opacity: 1;
    background-color: rgba(220, 252, 231, 0.9);
}

.resize-handle-icon {
    transition: all 0.3s ease;
    opacity: 0.5;
    font-size: 0.75rem;
}

.message-textarea:focus ~ .resize-handle-icon {
    opacity: 0.8;
    color: #22c55e;
}

.message-suggestions {
    transition: all 0.3s ease;
    padding: 0.5rem 0;
}

.suggestion-pill {
    transition: all 0.2s ease;
    cursor: pointer;
    user-select: none;
    font-size: 0.75rem;
    border: 1px solid #e5e7eb;
}

.suggestion-pill:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-color: #86efac;
}

.suggestion-pill:active {
    transform: translateY(0);
    background-color: #dcfce7;
}

/* Pulse animation for suggestion pills */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.message-suggestions:hover .suggestion-pill {
    animation: pulse 2s infinite;
    animation-delay: calc(var(--pill-index) * 0.2s);
}

/* Highlight effect for textarea */
@keyframes highlight {
    0% { box-shadow: 0 0 0 2px rgba(34, 197, 94, 0); }
    50% { box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.5); }
    100% { box-shadow: 0 0 0 2px rgba(34, 197, 94, 0); }
}

.highlight-textarea {
    animation: highlight 1s ease;
}

/* Mobile optimizations for message box */
@media (max-width: 768px) {
    .message-textarea {
        min-height: 150px;
        font-size: 0.95rem;
    }

    .suggestion-pill {
        font-size: 0.65rem;
        padding: 0.25rem 0.75rem;
    }
}

/* Large screen optimizations */
@media (min-width: 1024px) {
    .message-textarea {
        min-height: 220px;
        max-height: 600px;
    }
}
</style>

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>
