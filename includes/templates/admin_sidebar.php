<?php
// Get current page for highlighting active menu item
$current_admin_page = basename($_SERVER['PHP_SELF']);
?>

<div class="bg-white border border-gray-100 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300">
    <div class="p-4 bg-primary-700 text-white">
        <h2 class="text-xl font-bold flex items-center">
            <i class="fas fa-user-shield mr-2"></i> Admin Panel
        </h2>
    </div>

    <div class="p-3">
        <nav class="space-y-1">
            <!-- Dashboard -->
            <a href="<?php echo APP_URL; ?>/admin/index.php"
               class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_admin_page === 'index.php' ? 'bg-primary-100 text-primary-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50 hover:translate-x-1'; ?>">
                <div class="flex items-center justify-center w-8 h-8 rounded-md <?php echo $current_admin_page === 'index.php' ? 'bg-primary-200' : 'bg-gray-100'; ?> mr-3">
                    <i class="fas fa-tachometer-alt <?php echo $current_admin_page === 'index.php' ? 'text-primary-700' : 'text-gray-500'; ?>"></i>
                </div>
                <span class="font-medium">Dashboard</span>
                <?php if ($current_admin_page === 'index.php'): ?>
                    <span class="ml-auto">
                        <i class="fas fa-chevron-right text-primary-500 text-xs"></i>
                    </span>
                <?php endif; ?>
            </a>

            <!-- Management Section -->
            <div class="pt-3 pb-1">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-4">Management</p>
            </div>

            <!-- Buses -->
            <a href="<?php echo APP_URL; ?>/admin/buses.php"
               class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_admin_page === 'buses.php' ? 'bg-primary-100 text-primary-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50 hover:translate-x-1'; ?>">
                <div class="flex items-center justify-center w-8 h-8 rounded-md <?php echo $current_admin_page === 'buses.php' ? 'bg-primary-200' : 'bg-gray-100'; ?> mr-3">
                    <i class="fas fa-bus <?php echo $current_admin_page === 'buses.php' ? 'text-primary-700' : 'text-gray-500'; ?>"></i>
                </div>
                <span class="font-medium">Buses</span>
                <?php if ($current_admin_page === 'buses.php'): ?>
                    <span class="ml-auto">
                        <i class="fas fa-chevron-right text-primary-500 text-xs"></i>
                    </span>
                <?php endif; ?>
            </a>

            <!-- Routes -->
            <a href="<?php echo APP_URL; ?>/admin/routes.php"
               class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_admin_page === 'routes.php' ? 'bg-primary-100 text-primary-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50 hover:translate-x-1'; ?>">
                <div class="flex items-center justify-center w-8 h-8 rounded-md <?php echo $current_admin_page === 'routes.php' ? 'bg-primary-200' : 'bg-gray-100'; ?> mr-3">
                    <i class="fas fa-route <?php echo $current_admin_page === 'routes.php' ? 'text-primary-700' : 'text-gray-500'; ?>"></i>
                </div>
                <span class="font-medium">Routes</span>
                <?php if ($current_admin_page === 'routes.php'): ?>
                    <span class="ml-auto">
                        <i class="fas fa-chevron-right text-primary-500 text-xs"></i>
                    </span>
                <?php endif; ?>
            </a>

            <!-- Schedules -->
            <a href="<?php echo APP_URL; ?>/admin/schedules.php"
               class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_admin_page === 'schedules.php' ? 'bg-primary-100 text-primary-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50 hover:translate-x-1'; ?>">
                <div class="flex items-center justify-center w-8 h-8 rounded-md <?php echo $current_admin_page === 'schedules.php' ? 'bg-primary-200' : 'bg-gray-100'; ?> mr-3">
                    <i class="fas fa-calendar-alt <?php echo $current_admin_page === 'schedules.php' ? 'text-primary-700' : 'text-gray-500'; ?>"></i>
                </div>
                <span class="font-medium">Schedules</span>
                <?php if ($current_admin_page === 'schedules.php'): ?>
                    <span class="ml-auto">
                        <i class="fas fa-chevron-right text-primary-500 text-xs"></i>
                    </span>
                <?php endif; ?>
            </a>

            <!-- Bookings -->
            <a href="<?php echo APP_URL; ?>/admin/bookings.php"
               class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_admin_page === 'bookings.php' ? 'bg-primary-100 text-primary-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50 hover:translate-x-1'; ?>">
                <div class="flex items-center justify-center w-8 h-8 rounded-md <?php echo $current_admin_page === 'bookings.php' ? 'bg-primary-200' : 'bg-gray-100'; ?> mr-3">
                    <i class="fas fa-ticket-alt <?php echo $current_admin_page === 'bookings.php' ? 'text-primary-700' : 'text-gray-500'; ?>"></i>
                </div>
                <span class="font-medium">Bookings</span>
                <?php if ($current_admin_page === 'bookings.php'): ?>
                    <span class="ml-auto">
                        <i class="fas fa-chevron-right text-primary-500 text-xs"></i>
                    </span>
                <?php endif; ?>
            </a>

            <!-- Create Booking -->
            <a href="<?php echo APP_URL; ?>/admin/create_booking.php"
               class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_admin_page === 'create_booking.php' ? 'bg-primary-100 text-primary-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50 hover:translate-x-1'; ?>">
                <div class="flex items-center justify-center w-8 h-8 rounded-md <?php echo $current_admin_page === 'create_booking.php' ? 'bg-primary-200' : 'bg-gray-100'; ?> mr-3">
                    <i class="fas fa-plus-circle <?php echo $current_admin_page === 'create_booking.php' ? 'text-primary-700' : 'text-gray-500'; ?>"></i>
                </div>
                <span class="font-medium">Create Booking</span>
                <?php if ($current_admin_page === 'create_booking.php'): ?>
                    <span class="ml-auto">
                        <i class="fas fa-chevron-right text-primary-500 text-xs"></i>
                    </span>
                <?php endif; ?>
            </a>



            <!-- Users Section -->
            <div class="pt-3 pb-1">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-4">Users & Reports</p>
            </div>

            <!-- Users -->
            <a href="<?php echo APP_URL; ?>/admin/users.php"
               class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_admin_page === 'users.php' ? 'bg-primary-100 text-primary-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50 hover:translate-x-1'; ?>">
                <div class="flex items-center justify-center w-8 h-8 rounded-md <?php echo $current_admin_page === 'users.php' ? 'bg-primary-200' : 'bg-gray-100'; ?> mr-3">
                    <i class="fas fa-users <?php echo $current_admin_page === 'users.php' ? 'text-primary-700' : 'text-gray-500'; ?>"></i>
                </div>
                <span class="font-medium">Users</span>
                <?php if ($current_admin_page === 'users.php'): ?>
                    <span class="ml-auto">
                        <i class="fas fa-chevron-right text-primary-500 text-xs"></i>
                    </span>
                <?php endif; ?>
            </a>

            <!-- Reports -->
            <a href="<?php echo APP_URL; ?>/admin/reports.php"
               class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_admin_page === 'reports.php' ? 'bg-primary-100 text-primary-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50 hover:translate-x-1'; ?>">
                <div class="flex items-center justify-center w-8 h-8 rounded-md <?php echo $current_admin_page === 'reports.php' ? 'bg-primary-200' : 'bg-gray-100'; ?> mr-3">
                    <i class="fas fa-chart-bar <?php echo $current_admin_page === 'reports.php' ? 'text-primary-700' : 'text-gray-500'; ?>"></i>
                </div>
                <span class="font-medium">Reports</span>
                <?php if ($current_admin_page === 'reports.php'): ?>
                    <span class="ml-auto">
                        <i class="fas fa-chevron-right text-primary-500 text-xs"></i>
                    </span>
                <?php endif; ?>
            </a>

            <!-- Feedback -->
            <a href="<?php echo APP_URL; ?>/admin/feedback.php"
               class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 <?php echo $current_admin_page === 'feedback.php' ? 'bg-primary-100 text-primary-700 shadow-sm' : 'text-gray-700 hover:bg-gray-50 hover:translate-x-1'; ?>">
                <div class="flex items-center justify-center w-8 h-8 rounded-md <?php echo $current_admin_page === 'feedback.php' ? 'bg-primary-200' : 'bg-gray-100'; ?> mr-3">
                    <i class="fas fa-comments <?php echo $current_admin_page === 'feedback.php' ? 'text-primary-700' : 'text-gray-500'; ?>"></i>
                </div>
                <span class="font-medium">Feedback</span>
                <?php if ($current_admin_page === 'feedback.php'): ?>
                    <span class="ml-auto">
                        <i class="fas fa-chevron-right text-primary-500 text-xs"></i>
                    </span>
                <?php endif; ?>
            </a>

            <!-- Back to Site -->
            <div class="pt-3 mt-2 border-t border-gray-200">
                <a href="<?php echo APP_URL; ?>/index.php"
                   class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-50 transition-all duration-200 hover:translate-x-1">
                    <div class="flex items-center justify-center w-8 h-8 rounded-md bg-gray-100 mr-3">
                        <i class="fas fa-home text-gray-500"></i>
                    </div>
                    <span class="font-medium">Back to Site</span>
                </a>
            </div>
        </nav>
    </div>
</div>
