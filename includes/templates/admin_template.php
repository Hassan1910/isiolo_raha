<?php
// This file serves as a template for all admin pages
// It includes the header, sidebar, and footer

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Set message
    setFlashMessage("error", "You do not have permission to access the admin dashboard.");

    // Redirect to login page
    header("Location: " . APP_URL . "/login.php");
    exit();
}

// Include header
require_once __DIR__ . '/admin_header.php';
?>

<div class="container mx-auto px-4 py-6">
    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <!-- Sidebar -->
        <div class="md:col-span-1">
            <?php include_once __DIR__ . '/admin_sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="md:col-span-4">
            <!-- Page content will be inserted here -->
            <?php if (isset($admin_content)) echo $admin_content; ?>
        </div>
    </div>
</div>

    </div><!-- End of admin-container -->

    <!-- Back to top button -->
    <button id="back-to-top" class="fixed bottom-6 right-6 bg-primary-600 text-white p-3 rounded-full shadow-lg opacity-0 invisible transition-all duration-300">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }

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

        // Dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Notification dropdown
            const notificationButton = document.getElementById('notificationButton');
            const notificationDropdown = document.getElementById('notificationDropdown');

            if (notificationButton && notificationDropdown) {
                notificationButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    notificationDropdown.classList.toggle('show');

                    // Close user profile dropdown if open
                    if (userProfileDropdown && userProfileDropdown.classList.contains('show')) {
                        userProfileDropdown.classList.remove('show');
                    }
                });
            }

            // User profile dropdown
            const userProfileButton = document.getElementById('userProfileButton');
            const userProfileDropdown = document.getElementById('userProfileDropdown');

            if (userProfileButton && userProfileDropdown) {
                userProfileButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userProfileDropdown.classList.toggle('show');

                    // Close notification dropdown if open
                    if (notificationDropdown && notificationDropdown.classList.contains('show')) {
                        notificationDropdown.classList.remove('show');
                    }
                });
            }

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (notificationDropdown && !notificationButton.contains(e.target) && !notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.remove('show');
                }

                if (userProfileDropdown && !userProfileButton.contains(e.target) && !userProfileDropdown.contains(e.target)) {
                    userProfileDropdown.classList.remove('show');
                }
            });
        });

        // Search functionality for tables
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('table-search');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const table = document.querySelector('.searchable-table');
                    const rows = table.querySelectorAll('tbody tr');

                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        if(text.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Initialize any datepickers
            const datepickers = document.querySelectorAll('.datepicker');
            if (datepickers.length > 0) {
                datepickers.forEach(picker => {
                    picker.addEventListener('focus', function() {
                        this.type = 'date';
                    });
                    picker.addEventListener('blur', function() {
                        if (!this.value) {
                            this.type = 'text';
                        }
                    });
                });
            }
        });
    </script>

    <!-- Custom JavaScript -->
    <script src="<?php echo APP_URL; ?>/assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>
