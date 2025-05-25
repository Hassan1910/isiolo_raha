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
    <title><?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?> | Admin</title>

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

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Admin-specific styles */
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .btn-sm {
            @apply py-1 px-3 text-sm rounded-md;
        }

        .btn-primary {
            @apply bg-primary-600 hover:bg-primary-500 text-white font-medium py-2 px-4 rounded-md transition duration-300;
        }

        .btn-secondary {
            @apply bg-gray-600 hover:bg-gray-500 text-white font-medium py-2 px-4 rounded-md transition duration-300;
        }

        .btn-danger {
            @apply bg-red-600 hover:bg-red-500 text-white font-medium py-2 px-4 rounded-md transition duration-300;
        }

        .form-input {
            @apply w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent;
        }

        /* Dropdown styles */
        .dropdown-menu {
            display: none;
        }

        .dropdown-menu.show {
            display: block;
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
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Top Navigation -->
    <nav class="bg-primary-700 text-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-2">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <a href="<?php echo APP_URL; ?>/admin/index.php" class="flex items-center transition-transform duration-300 hover:scale-105">
                    <img src="<?php echo APP_URL; ?>/assets/images/isioloraha logo.png" alt="Isiolo Raha Logo" class="h-16 w-auto">
                    <span class="text-sm ml-3 bg-white text-primary-700 px-3 py-1 rounded-full font-bold shadow-sm">Admin</span>
                </a>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="md:hidden focus:outline-none p-2 rounded-lg hover:bg-primary-600 transition-colors duration-200">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <!-- Quick Links -->
                <div class="hidden md:flex items-center space-x-4">
                    <a href="<?php echo APP_URL; ?>/admin/bookings.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-primary-600 transition-colors duration-200">
                        <i class="fas fa-ticket-alt mr-1"></i>
                        <span>Bookings</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/group_bookings.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-primary-600 transition-colors duration-200">
                        <i class="fas fa-users mr-1"></i>
                        <span>Group Bookings</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/schedules.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-primary-600 transition-colors duration-200">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        <span>Schedules</span>
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/users.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-primary-600 transition-colors duration-200">
                        <i class="fas fa-users mr-1"></i>
                        <span>Users</span>
                    </a>
                </div>

                <!-- User Menu (Desktop) -->
                <div class="hidden md:flex items-center space-x-4">
                    <!-- Notifications -->
                    <div class="relative">
                        <button id="notificationButton" class="p-2 rounded-full hover:bg-primary-600 transition-colors duration-200 relative">
                            <i class="fas fa-bell"></i>
                            <span class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">3</span>
                        </button>
                        <div id="notificationDropdown" class="dropdown-menu absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-xl py-1 z-10 text-gray-800">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <h3 class="font-bold text-gray-800">Notifications</h3>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <a href="#" class="block px-4 py-2 hover:bg-gray-50 border-b border-gray-100">
                                    <div class="flex items-start">
                                        <div class="p-2 bg-primary-100 text-primary-600 rounded-full">
                                            <i class="fas fa-ticket-alt"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium">New booking received</p>
                                            <p class="text-xs text-gray-500">5 minutes ago</p>
                                        </div>
                                    </div>
                                </a>
                                <a href="#" class="block px-4 py-2 hover:bg-gray-50 border-b border-gray-100">
                                    <div class="flex items-start">
                                        <div class="p-2 bg-yellow-100 text-yellow-600 rounded-full">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium">Low seat availability</p>
                                            <p class="text-xs text-gray-500">1 hour ago</p>
                                        </div>
                                    </div>
                                </a>
                                <a href="#" class="block px-4 py-2 hover:bg-gray-50">
                                    <div class="flex items-start">
                                        <div class="p-2 bg-blue-100 text-blue-600 rounded-full">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium">New user registered</p>
                                            <p class="text-xs text-gray-500">3 hours ago</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="px-4 py-2 border-t border-gray-100 text-center">
                                <a href="#" class="text-sm text-primary-600 hover:underline">View all notifications</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile -->
                    <div class="relative">
                        <button id="userProfileButton" class="flex items-center space-x-2 focus:outline-none bg-primary-600 hover:bg-primary-500 transition-colors duration-200 rounded-full pl-2 pr-3 py-1">
                            <div class="w-8 h-8 rounded-full bg-white text-primary-700 flex items-center justify-center">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="hidden md:inline-block font-medium"><?php echo $_SESSION['user_name'] ?? $_SESSION['user_email'] ?? 'Admin'; ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div id="userProfileDropdown" class="dropdown-menu absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl py-1 z-10">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm text-gray-500">Signed in as</p>
                                <p class="text-sm font-medium text-gray-800"><?php echo $_SESSION['user_email'] ?? 'admin@isioloraha.com'; ?></p>
                            </div>
                            <a href="<?php echo APP_URL; ?>/admin/index.php" class="block px-4 py-2 text-gray-800 hover:bg-primary-50 hover:text-primary-700">
                                <i class="fas fa-tachometer-alt mr-2 text-primary-600"></i> Dashboard
                            </a>
                            <a href="<?php echo APP_URL; ?>/user/profile.php" class="block px-4 py-2 text-gray-800 hover:bg-primary-50 hover:text-primary-700">
                                <i class="fas fa-user-edit mr-2 text-primary-600"></i> Profile
                            </a>
                            <a href="<?php echo APP_URL; ?>/admin/settings.php" class="block px-4 py-2 text-gray-800 hover:bg-primary-50 hover:text-primary-700">
                                <i class="fas fa-cog mr-2 text-primary-600"></i> Settings
                            </a>
                            <div class="border-t border-gray-200 my-1"></div>
                            <a href="<?php echo APP_URL; ?>/logout.php" class="block px-4 py-2 text-gray-800 hover:bg-red-50 hover:text-red-700">
                                <i class="fas fa-sign-out-alt mr-2 text-red-600"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden hidden mt-3 space-y-2 border-t border-primary-600 pt-3">
                <a href="<?php echo APP_URL; ?>/admin/index.php" class="block py-2 px-3 rounded-lg hover:bg-primary-600 transition-colors duration-200 <?php echo $current_page === 'index.php' ? 'bg-primary-600 font-bold' : ''; ?>">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>
                <a href="<?php echo APP_URL; ?>/admin/bookings.php" class="block py-2 px-3 rounded-lg hover:bg-primary-600 transition-colors duration-200 <?php echo $current_page === 'bookings.php' ? 'bg-primary-600 font-bold' : ''; ?>">
                    <i class="fas fa-ticket-alt mr-2"></i> Bookings
                </a>
                <a href="<?php echo APP_URL; ?>/admin/group_bookings.php" class="block py-2 px-3 rounded-lg hover:bg-primary-600 transition-colors duration-200 <?php echo $current_page === 'group_bookings.php' ? 'bg-primary-600 font-bold' : ''; ?>">
                    <i class="fas fa-users mr-2"></i> Group Bookings
                </a>
                <a href="<?php echo APP_URL; ?>/admin/schedules.php" class="block py-2 px-3 rounded-lg hover:bg-primary-600 transition-colors duration-200 <?php echo $current_page === 'schedules.php' ? 'bg-primary-600 font-bold' : ''; ?>">
                    <i class="fas fa-calendar-alt mr-2"></i> Schedules
                </a>
                <a href="<?php echo APP_URL; ?>/admin/buses.php" class="block py-2 px-3 rounded-lg hover:bg-primary-600 transition-colors duration-200 <?php echo $current_page === 'buses.php' ? 'bg-primary-600 font-bold' : ''; ?>">
                    <i class="fas fa-bus mr-2"></i> Buses
                </a>
                <a href="<?php echo APP_URL; ?>/admin/routes.php" class="block py-2 px-3 rounded-lg hover:bg-primary-600 transition-colors duration-200 <?php echo $current_page === 'routes.php' ? 'bg-primary-600 font-bold' : ''; ?>">
                    <i class="fas fa-route mr-2"></i> Routes
                </a>
                <a href="<?php echo APP_URL; ?>/admin/users.php" class="block py-2 px-3 rounded-lg hover:bg-primary-600 transition-colors duration-200 <?php echo $current_page === 'users.php' ? 'bg-primary-600 font-bold' : ''; ?>">
                    <i class="fas fa-users mr-2"></i> Users
                </a>
                <a href="<?php echo APP_URL; ?>/admin/reports.php" class="block py-2 px-3 rounded-lg hover:bg-primary-600 transition-colors duration-200 <?php echo $current_page === 'reports.php' ? 'bg-primary-600 font-bold' : ''; ?>">
                    <i class="fas fa-chart-bar mr-2"></i> Reports
                </a>
                <a href="<?php echo APP_URL; ?>/admin/feedback.php" class="block py-2 px-3 rounded-lg hover:bg-primary-600 transition-colors duration-200 <?php echo $current_page === 'feedback.php' ? 'bg-primary-600 font-bold' : ''; ?>">
                    <i class="fas fa-comments mr-2"></i> Feedback
                </a>

                <!-- Mobile User Actions -->
                <div class="border-t border-primary-600 pt-3 mt-3">
                    <div class="flex items-center px-3 py-2 mb-2">
                        <div class="w-8 h-8 rounded-full bg-white text-primary-700 flex items-center justify-center mr-3">
                            <i class="fas fa-user"></i>
                        </div>
                        <span class="font-medium"><?php echo $_SESSION['user_name'] ?? $_SESSION['user_email'] ?? 'Admin'; ?></span>
                    </div>
                    <a href="<?php echo APP_URL; ?>/user/profile.php" class="block py-2 px-3 rounded-lg hover:bg-primary-600 transition-colors duration-200">
                        <i class="fas fa-user-edit mr-2"></i> Profile
                    </a>
                    <a href="<?php echo APP_URL; ?>/admin/settings.php" class="block py-2 px-3 rounded-lg hover:bg-primary-600 transition-colors duration-200">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a>
                    <a href="<?php echo APP_URL; ?>/logout.php" class="block py-2 px-3 rounded-lg hover:bg-red-600 transition-colors duration-200 text-red-200">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="container mx-auto px-4 py-2">
            <div class="flex items-center text-sm text-gray-600">
                <a href="<?php echo APP_URL; ?>/admin/index.php" class="hover:text-primary-600">
                    <i class="fas fa-home"></i>
                </a>
                <span class="mx-2">/</span>
                <?php
                $page_name = '';
                switch ($current_page) {
                    case 'index.php':
                        $page_name = 'Dashboard';
                        break;
                    case 'buses.php':
                        $page_name = 'Buses';
                        break;
                    case 'routes.php':
                        $page_name = 'Routes';
                        break;
                    case 'schedules.php':
                        $page_name = 'Schedules';
                        break;
                    case 'bookings.php':
                        $page_name = 'Bookings';
                        break;
                    case 'users.php':
                        $page_name = 'Users';
                        break;
                    case 'reports.php':
                        $page_name = 'Reports';
                        break;
                    case 'feedback.php':
                        $page_name = 'Feedback';
                        break;
                    default:
                        $page_name = isset($page_title) ? $page_title : 'Admin';
                }
                ?>
                <span class="font-medium text-gray-800"><?php echo $page_name; ?></span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-container px-4 py-6">
