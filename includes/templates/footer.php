    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <!-- Company Info -->
                <div>
                    <div class="flex items-center mb-6">
                        <i class="fas fa-bus-alt text-primary-500 text-3xl mr-3"></i>
                        <h3 class="text-2xl font-bold">Isiolo Raha</h3>
                    </div>
                    <p class="mb-4 text-gray-400">Your trusted partner for comfortable and reliable bus travel across Kenya.</p>
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-white hover:bg-primary-600 transition-all duration-300">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-white hover:bg-primary-600 transition-all duration-300">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-white hover:bg-primary-600 transition-all duration-300">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-white hover:bg-primary-600 transition-all duration-300">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-xl font-bold mb-6 border-b border-gray-700 pb-2">Quick Links</h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="<?php echo APP_URL; ?>" class="text-gray-400 hover:text-primary-400 transition-all duration-300 flex items-center">
                                <i class="fas fa-chevron-right text-xs mr-2 text-primary-500"></i> Home
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo APP_URL; ?>/routes.php" class="text-gray-400 hover:text-primary-400 transition-all duration-300 flex items-center">
                                <i class="fas fa-chevron-right text-xs mr-2 text-primary-500"></i> Routes
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo APP_URL; ?>/about.php" class="text-gray-400 hover:text-primary-400 transition-all duration-300 flex items-center">
                                <i class="fas fa-chevron-right text-xs mr-2 text-primary-500"></i> About Us
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo APP_URL; ?>/contact.php" class="text-gray-400 hover:text-primary-400 transition-all duration-300 flex items-center">
                                <i class="fas fa-chevron-right text-xs mr-2 text-primary-500"></i> Contact
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo APP_URL; ?>/faq.php" class="text-gray-400 hover:text-primary-400 transition-all duration-300 flex items-center">
                                <i class="fas fa-chevron-right text-xs mr-2 text-primary-500"></i> FAQ
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-xl font-bold mb-6 border-b border-gray-700 pb-2">Contact Us</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <div class="mt-1 mr-3 w-8 h-8 flex-shrink-0 bg-gray-700 rounded-full flex items-center justify-center text-primary-500">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <span class="text-gray-400">123 Isiolo Road, Nairobi, Kenya</span>
                        </li>
                        <li class="flex items-start">
                            <div class="mt-1 mr-3 w-8 h-8 flex-shrink-0 bg-gray-700 rounded-full flex items-center justify-center text-primary-500">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <span class="text-gray-400">+254 700 000 000</span>
                        </li>
                        <li class="flex items-start">
                            <div class="mt-1 mr-3 w-8 h-8 flex-shrink-0 bg-gray-700 rounded-full flex items-center justify-center text-primary-500">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <span class="text-gray-400">info@isioloraha.com</span>
                        </li>
                        <li class="flex items-start">
                            <div class="mt-1 mr-3 w-8 h-8 flex-shrink-0 bg-gray-700 rounded-full flex items-center justify-center text-primary-500">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="text-gray-400">
                                <p>Monday - Friday: 8:00 AM - 6:00 PM</p>
                                <p>Saturday: 9:00 AM - 5:00 PM</p>
                                <p>Sunday: Closed</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h3 class="text-xl font-bold mb-6 border-b border-gray-700 pb-2">Newsletter</h3>
                    <p class="mb-4 text-gray-400">Subscribe to our newsletter for the latest updates, promotions, and travel tips.</p>
                    <form action="#" method="post" class="space-y-3">
                        <div class="relative">
                            <input type="email" name="email" placeholder="Your email address" required
                                class="w-full px-4 py-3 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-primary-600 hover:bg-primary-500 rounded-lg p-2 transition-all duration-300">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div class="text-xs text-gray-500">
                            By subscribing, you agree to our <a href="#" class="text-primary-500 hover:underline">Privacy Policy</a> and consent to receive updates from us.
                        </div>
                    </form>
                </div>
            </div>

            <hr class="border-gray-700 my-8">

            <!-- Bottom Footer -->
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-400 text-sm mb-4 md:mb-0">
                    &copy; <?php echo date('Y'); ?> Isiolo Raha. All rights reserved.
                </div>
                <div class="text-gray-400 text-sm flex space-x-6">
                    <a href="#" class="hover:text-primary-400 transition-all duration-300">Terms of Service</a>
                    <a href="#" class="hover:text-primary-400 transition-all duration-300">Privacy Policy</a>
                    <a href="#" class="hover:text-primary-400 transition-all duration-300">Cookies</a>
                </div>
            </div>
        </div>

        <!-- Back to top button -->
        <button id="back-to-top" class="fixed bottom-6 right-6 w-12 h-12 rounded-full bg-primary-600 text-white flex items-center justify-center shadow-lg transform transition-all duration-300 hover:bg-primary-500 opacity-0 invisible">
            <i class="fas fa-chevron-up"></i>
        </button>
    </footer>

    <!-- JavaScript -->
    <script>
        // Back to top button
        const backToTopButton = document.getElementById('back-to-top');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopButton.classList.remove('opacity-0', 'invisible');
                backToTopButton.classList.add('opacity-100', 'visible');
            } else {
                backToTopButton.classList.add('opacity-0', 'invisible');
                backToTopButton.classList.remove('opacity-100', 'visible');
            }
        });

        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>

    <!-- Custom JavaScript -->
    <script src="<?php echo APP_URL; ?>/assets/js/main.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/form-validation.js?v=<?php echo time(); ?>"></script>

    <?php
    // Include loading overlay component
    require_once __DIR__ . '/../components/loading_overlay.php';
    renderLoadingOverlay();

    // Include tooltip component
    require_once __DIR__ . '/../components/tooltip.php';
    renderTooltipScript();

    // Include help guide component
    require_once __DIR__ . '/../components/help_guide.php';
    // Get current page for contextual help
    $current_page_name = pathinfo(basename($_SERVER['PHP_SELF']), PATHINFO_FILENAME);
    renderHelpGuide($current_page_name);
    ?>
</body>
</html>
