<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';

// Get the current page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?></title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom Tailwind Configuration -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        secondary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    boxShadow: {
                        'nav': '0 4px 12px -1px rgba(0, 0, 0, 0.1), 0 2px 6px -1px rgba(0, 0, 0, 0.06)',
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/responsive.css?v=<?php echo time(); ?>">


    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        /* Base navigation styles */
        #main-nav {
            position: relative;
            z-index: 50;
            transition: all 0.3s ease;
        }
        /* Sticky navigation styles */
        .sticky-nav {
            position: fixed !important;
            top: 0;
            left: 0;
            right: 0;
            background-color: rgba(21, 128, 61, 0.95);
            backdrop-filter: blur(8px);
            box-shadow: var(--tw-shadow-nav);
        }
        .nav-link {
            position: relative;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: white;
            transition: width 0.3s ease;
        }
        .nav-link:hover::after, .nav-link.active::after {
            width: 100%;
        }
        .dropdown-menu {
            transform-origin: top;
            transform: scaleY(0);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        .dropdown:hover .dropdown-menu {
            transform: scaleY(1);
            opacity: 1;
        }
        /* Ensure all navigation links are clickable */
        nav a {
            cursor: pointer;
            position: relative;
            z-index: 51;
        }

        /* Mobile menu animation */
        #mobile-menu {
            transition: all 0.3s ease-in-out;
            max-height: 0;
            overflow: hidden;
        }

        #mobile-menu:not(.hidden) {
            max-height: 500px;
        }

        /* Mobile menu button animation */
        #mobile-menu-button i {
            transition: transform 0.3s ease-in-out;
        }

        #mobile-menu-button:hover i {
            transform: scale(1.1);
        }

        /* Mobile menu links hover effect */
        #mobile-menu a {
            transition: all 0.2s ease-in-out;
        }

        #mobile-menu a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        /* Sticky navigation styles */
        .sticky-nav {
            backdrop-filter: blur(10px);
            background-color: rgba(21, 128, 61, 0.95);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-primary-700 text-white shadow-md" id="main-nav">
        <div class="container mx-auto px-4 py-2">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <a href="<?php echo APP_URL; ?>/index.php" class="flex items-center">
                    <img src="<?php echo APP_URL; ?>/assets/images/isioloraha logo.png" alt="Isiolo Raha Logo" class="h-16 w-auto">
                </a>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="md:hidden focus:outline-none p-2 rounded-lg hover:bg-primary-600 transition-colors duration-200">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-6 items-center">
                    <a href="<?php echo APP_URL; ?>/index.php" class="nav-link <?php echo $current_page === 'index.php' ? 'active font-semibold' : ''; ?>">Home</a>
                    <a href="<?php echo APP_URL; ?>/routes.php" class="nav-link <?php echo $current_page === 'routes.php' ? 'active font-semibold' : ''; ?>">Routes</a>
                    <a href="<?php echo APP_URL; ?>/about.php" class="nav-link <?php echo $current_page === 'about.php' ? 'active font-semibold' : ''; ?>">About Us</a>
                    <a href="<?php echo APP_URL; ?>/contact.php" class="nav-link <?php echo $current_page === 'contact.php' ? 'active font-semibold' : ''; ?>">Contact</a>
                    <a href="<?php echo APP_URL; ?>/faq.php" class="nav-link <?php echo $current_page === 'faq.php' ? 'active font-semibold' : ''; ?>">FAQ</a>

                    <?php if (isLoggedIn()): ?>
                        <div class="relative dropdown group">
                            <button class="flex items-center space-x-1 focus:outline-none nav-link">
                                <i class="fas fa-user-circle mr-1"></i>
                                <span><?php
                                if (isset($_SESSION['user_name'])) {
                                    echo $_SESSION['user_name'];
                                } elseif (isset($_SESSION['user_email'])) {
                                    echo $_SESSION['user_email'];
                                } else {
                                    echo "User";
                                }
                                ?></span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                                <a href="<?php echo APP_URL; ?>/user/dashboard.php" class="block px-4 py-2 text-gray-800 hover:bg-primary-100 hover:text-primary-700">
                                    <i class="fas fa-tachometer-alt mr-2 text-primary-600"></i> Dashboard
                                </a>
                                <a href="<?php echo APP_URL; ?>/user/bookings.php" class="block px-4 py-2 text-gray-800 hover:bg-primary-100 hover:text-primary-700">
                                    <i class="fas fa-ticket-alt mr-2 text-primary-600"></i> My Bookings
                                </a>
                                <a href="<?php echo APP_URL; ?>/user/profile.php" class="block px-4 py-2 text-gray-800 hover:bg-primary-100 hover:text-primary-700">
                                    <i class="fas fa-user-edit mr-2 text-primary-600"></i> Profile
                                </a>
                                <?php if (isAdmin()): ?>
                                    <a href="<?php echo APP_URL; ?>/admin/index.php" class="block px-4 py-2 text-gray-800 hover:bg-primary-100 hover:text-primary-700">
                                        <i class="fas fa-user-shield mr-2 text-primary-600"></i> Admin Dashboard
                                    </a>
                                <?php endif; ?>
                                <div class="border-t border-gray-200 my-1"></div>
                                <a href="<?php echo APP_URL; ?>/logout.php" class="block px-4 py-2 text-gray-800 hover:bg-red-100 hover:text-red-700">
                                    <i class="fas fa-sign-out-alt mr-2 text-red-600"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo APP_URL; ?>/login.php" class="bg-white text-primary-700 px-4 py-2 rounded-md hover:bg-gray-100 transition-all duration-300">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </a>
                        <a href="<?php echo APP_URL; ?>/register.php" class="bg-primary-600 text-white px-4 py-2 rounded-md border border-primary-500 hover:bg-primary-500 transition-all duration-300">
                            <i class="fas fa-user-plus mr-1"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden hidden mt-3 space-y-2">
                <a href="<?php echo APP_URL; ?>/index.php" class="block py-2 <?php echo $current_page === 'index.php' ? 'font-bold' : ''; ?>">
                    <i class="fas fa-home mr-2"></i> Home
                </a>
                <a href="<?php echo APP_URL; ?>/routes.php" class="block py-2 <?php echo $current_page === 'routes.php' ? 'font-bold' : ''; ?>">
                    <i class="fas fa-route mr-2"></i> Routes
                </a>
                <a href="<?php echo APP_URL; ?>/about.php" class="block py-2 <?php echo $current_page === 'about.php' ? 'font-bold' : ''; ?>">
                    <i class="fas fa-info-circle mr-2"></i> About Us
                </a>
                <a href="<?php echo APP_URL; ?>/contact.php" class="block py-2 <?php echo $current_page === 'contact.php' ? 'font-bold' : ''; ?>">
                    <i class="fas fa-envelope mr-2"></i> Contact
                </a>
                <a href="<?php echo APP_URL; ?>/faq.php" class="block py-2 <?php echo $current_page === 'faq.php' ? 'font-bold' : ''; ?>">
                    <i class="fas fa-question-circle mr-2"></i> FAQ
                </a>

                <?php if (isLoggedIn()): ?>
                    <div class="border-t border-primary-600 my-2 pt-2">
                        <a href="<?php echo APP_URL; ?>/user/dashboard.php" class="block py-2">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>
                        <a href="<?php echo APP_URL; ?>/user/bookings.php" class="block py-2">
                            <i class="fas fa-ticket-alt mr-2"></i> My Bookings
                        </a>
                        <a href="<?php echo APP_URL; ?>/user/profile.php" class="block py-2">
                            <i class="fas fa-user-edit mr-2"></i> Profile
                        </a>
                        <?php if (isAdmin()): ?>
                            <a href="<?php echo APP_URL; ?>/admin/index.php" class="block py-2">
                                <i class="fas fa-user-shield mr-2"></i> Admin Dashboard
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo APP_URL; ?>/logout.php" class="block py-2 text-red-300">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                <?php else: ?>
                    <div class="border-t border-primary-600 my-2 pt-2 flex space-x-2">
                        <a href="<?php echo APP_URL; ?>/login.php" class="block py-2 px-4 bg-white text-primary-700 rounded-md w-1/2 text-center">
                            <i class="fas fa-sign-in-alt mr-1"></i> Login
                        </a>
                        <a href="<?php echo APP_URL; ?>/register.php" class="block py-2 px-4 bg-primary-600 border border-primary-500 rounded-md w-1/2 text-center">
                            <i class="fas fa-user-plus mr-1"></i> Register
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <script>
        // Initialize navigation on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    mobileMenu.classList.toggle('hidden');

                    // Toggle hamburger icon
                    const icon = mobileMenuButton.querySelector('i');
                    if (mobileMenu.classList.contains('hidden')) {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    } else {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');
                    }
                });

                // Close mobile menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (!mobileMenuButton.contains(e.target) && !mobileMenu.contains(e.target)) {
                        mobileMenu.classList.add('hidden');
                        const icon = mobileMenuButton.querySelector('i');
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                });

                // Close mobile menu when window is resized to desktop
                window.addEventListener('resize', function() {
                    if (window.innerWidth >= 768) {
                        mobileMenu.classList.add('hidden');
                        const icon = mobileMenuButton.querySelector('i');
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                });
            }

            // Fix navigation links
            fixNavigationLinks();


        });

        // Sticky navigation
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('main-nav');
            if (window.scrollY > 50) {
                nav.classList.add('sticky-nav');
            } else {
                nav.classList.remove('sticky-nav');
            }
        });

        // Function to fix navigation links
        function fixNavigationLinks() {
            // Get all navigation links
            const navLinks = document.querySelectorAll('nav a');

            // Fix each link
            navLinks.forEach(link => {
                let href = link.getAttribute('href');
                if (!href) return; // Skip links with no href attribute

                // Make all links clickable
                link.style.cursor = 'pointer';
                link.style.pointerEvents = 'auto'; // Ensure pointer events are enabled

                // Only fix hash links or empty links
                if (href === '#' || href === 'javascript:void(0)' || href === '') {
                    // For empty links, use the PHP-generated APP_URL from the data attribute
                    const appUrl = '<?php echo APP_URL; ?>';
                    const linkText = link.textContent.trim().toLowerCase();

                    // Map common link text to URLs
                    const linkMap = {
                        'home': appUrl + '/index.php',
                        'routes': appUrl + '/routes.php',
                        'about us': appUrl + '/about.php',
                        'contact': appUrl + '/contact.php',
                        'faq': appUrl + '/faq.php',
                        'login': appUrl + '/login.php',
                        'register': appUrl + '/register.php',
                        'dashboard': appUrl + '/user/dashboard.php',
                        'my bookings': appUrl + '/user/bookings.php',
                        'profile': appUrl + '/user/profile.php',
                        'admin dashboard': appUrl + '/admin/index.php',
                        'logout': appUrl + '/logout.php'
                    };

                    if (linkMap[linkText]) {
                        link.setAttribute('href', linkMap[linkText]);

                    }
                }

                // Add click event listener as a fallback
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href && href !== '#' && href !== 'javascript:void(0)') {
                        window.location.href = href;
                    }
                });
            });
        }


    </script>

    <!-- Flash Messages -->
    <?php $flash = getFlashMessage(); ?>
    <?php if ($flash): ?>
        <div class="container mx-auto px-4 mt-4">
            <div class="<?php echo $flash['type'] === 'success' ? 'bg-green-100 border-green-500 text-green-700' : ($flash['type'] === 'error' ? 'bg-red-100 border-red-500 text-red-700' : 'bg-blue-100 border-blue-500 text-blue-700'); ?> border-l-4 p-4 mb-4 rounded-r flex items-center">
                <div class="mr-3 text-xl">
                    <i class="fas <?php echo $flash['type'] === 'success' ? 'fa-check-circle text-green-500' : ($flash['type'] === 'error' ? 'fa-times-circle text-red-500' : 'fa-info-circle text-blue-500'); ?>"></i>
                </div>
                <p><?php echo isset($flash['html']) && $flash['html'] ? $flash['message'] : htmlspecialchars($flash['message']); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="flex-grow"><?php // Main content will go here ?>
