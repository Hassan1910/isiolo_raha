<?php
// Set page title
$page_title = "Home";

// Include header
require_once 'includes/templates/header.php';

// Include database connection
$conn = require_once 'config/database.php';

// Check for group booking flag and display message
$group_booking_message = '';
if (isset($_GET['group_booking']) && $_GET['group_booking'] == '1') {
    $group_booking_message = '<div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Group Booking:</strong> To book for a group, please first search for your desired route and select a schedule. Group booking options will be available during the booking process.
                </p>
            </div>
        </div>
    </div>';
}

// Get popular routes with additional information
$sql = "SELECT r.*,
        (SELECT MIN(s.fare) FROM schedules s WHERE s.route_id = r.id) as min_fare,
        (SELECT COUNT(*) FROM schedules s WHERE s.route_id = r.id AND s.status = 'scheduled') as schedule_count
        FROM routes r
        LIMIT 6";
$result = $conn->query($sql);
$popular_routes = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $popular_routes[] = $row;
    }
}
?>



<!-- Hero Section -->
<section class="hero relative" style="background-image: url('https://placehold.co/1920x1080/15803d/FFFFFF/png?text=Bus+Travel'); background-size: cover; background-position: center; min-height: 600px; position: relative; overflow: hidden;">
    <div class="absolute inset-0 gradient-animation"></div>
    <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.05\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

    <!-- Floating Elements -->
    <div class="floating-element absolute w-24 h-24 rounded-full bg-white bg-opacity-10 top-1/4 left-1/4 animate-float" style="animation-delay: 0s;"></div>
    <div class="floating-element absolute w-16 h-16 rounded-full bg-white bg-opacity-10 bottom-1/4 right-1/3 animate-float" style="animation-delay: 2s;"></div>
    <div class="floating-element absolute w-20 h-20 rounded-full bg-white bg-opacity-10 top-1/3 right-1/4 animate-float" style="animation-delay: 1s;"></div>

    <div class="container mx-auto px-4 py-16 text-center relative z-10 text-white max-w-3xl">
        <h1 class="text-4xl md:text-5xl font-bold mb-4 leading-tight fade-in">Discover Kenya with <span class="gradient-text">Isiolo Raha</span></h1>
        <p class="text-xl md:text-2xl mb-8 opacity-90 fade-in delay-100">Safe, comfortable, and affordable bus travel to your favorite destinations</p>
        <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-6 fade-in delay-200">
            <a href="javascript:void(0)" onclick="smoothScrollTo('search-form')" class="bg-white text-primary-700 hover:bg-gray-100 font-bold py-4 px-8 rounded-full inline-block transition duration-300 ease-in-out transform hover:scale-105 hover:shadow-lg">
                <i class="fas fa-ticket-alt mr-2"></i> Book Your Trip
            </a>

            <a href="routes.php" class="bg-transparent text-white hover:bg-white hover:text-primary-700 font-bold py-4 px-8 rounded-full inline-block transition duration-300 ease-in-out border-2 border-white">
                <i class="fas fa-route mr-2"></i> Explore Routes
            </a>
        </div>
        <div class="absolute bottom-5 left-1/2 transform -translate-x-1/2 text-white bounce">
            <a href="javascript:void(0)" onclick="smoothScrollTo('search-form')" class="text-white text-opacity-80 hover:text-opacity-100">
                <i class="fas fa-chevron-down text-2xl"></i>
            </a>
        </div>
        <div class="flex justify-center mt-12 space-x-2">
            <span class="w-3 h-3 rounded-full bg-white"></span>
            <span class="w-3 h-3 rounded-full bg-white bg-opacity-50"></span>
            <span class="w-3 h-3 rounded-full bg-white bg-opacity-50"></span>
            <span class="w-3 h-3 rounded-full bg-white bg-opacity-50"></span>
        </div>
    </div>

    <!-- Wave Separator -->
    <div class="absolute bottom-0 left-0 w-full overflow-hidden" style="height: 50px; transform: translateY(1px);">
        <svg class="absolute bottom-0 overflow-hidden" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" version="1.1" viewBox="0 0 2560 100" x="0" y="0">
            <polygon fill="#f9fafb" points="2560 0 2560 100 0 100"></polygon>
        </svg>
    </div>
</section>

<style>
.gradient-animation {
    background: linear-gradient(45deg, rgba(21, 128, 61, 0.9), rgba(22, 101, 52, 0.85), rgba(20, 83, 45, 0.8), rgba(16, 185, 129, 0.85));
    background-size: 400% 400%;
    animation: gradient 15s ease infinite;
}

@keyframes gradient {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0% {
        transform: translateY(0px) translateX(0px);
    }
    50% {
        transform: translateY(-20px) translateX(10px);
    }
    100% {
        transform: translateY(0px) translateX(0px);
    }
}
</style>

<!-- Search Form Section -->
<section id="search-form" class="py-10 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="search-form-container rounded-xl bg-white shadow-xl border border-gray-100 p-8 max-w-4xl mx-auto transform -translate-y-16 z-20 slide-in-top">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold mb-2 gradient-text">Find Your Perfect Journey</h2>
                <p class="text-gray-600">Book your bus tickets in just a few clicks</p>
                <div class="w-24 h-1 bg-primary-500 mx-auto mt-4 rounded-full"></div>
            </div>

            <?php if ($group_booking_message): ?>
                <?php echo $group_booking_message; ?>
            <?php endif; ?>

            <div class="flex justify-center mb-6 rounded-full bg-gray-100 p-1 max-w-md mx-auto">
                <div class="journey-tab active w-1/2 text-center py-3 px-4 rounded-full cursor-pointer transition-all" id="one-way-tab" onclick="switchJourneyType('one-way')">
                    <i class="fas fa-long-arrow-alt-right mr-2"></i> One Way
                </div>
                <div class="journey-tab w-1/2 text-center py-3 px-4 rounded-full cursor-pointer transition-all" id="round-trip-tab" onclick="switchJourneyType('round-trip')">
                    <i class="fas fa-exchange-alt mr-2"></i> Round Trip
                </div>
            </div>

            <form action="search_results.php" method="get" class="space-y-8" data-validate="true">
                <input type="hidden" name="journey_type" id="journey_type" value="one-way">

                <div class="relative">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Origin -->
                        <div class="form-group slide-in-left">
                            <label for="origin" class="form-label flex items-center text-gray-700" style="font-weight: 500; margin-bottom: 0.5rem; color: #374151;">
                                <i class="fas fa-map-marker-alt text-primary-600 mr-2"></i> From
                            </label>
                            <div class="relative mt-1">
                                <div class="form-icon-wrapper" style="position: absolute; top: 0; bottom: 0; left: 0; display: flex; align-items: center; padding-left: 1rem; pointer-events: none;">
                                    <i class="fas fa-map-marker-alt form-icon" style="color: #16a34a; font-size: 1.25rem;"></i>
                                </div>
                                <select name="origin" id="origin" class="form-input pl-14 w-full" required style="width: 100%; padding: 0.75rem 1rem 0.75rem 3.5rem; border-radius: 0.5rem; border: 1px solid #e5e7eb; background-color: #f9fafb; transition: all 0.2s; color: #1f2937; appearance: none; background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e'); background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                                    <option value="">Select Origin</option>
                                    <?php
                                    $sql = "SELECT DISTINCT origin FROM routes ORDER BY origin";
                                    $result = $conn->query($sql);

                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo '<option value="' . $row['origin'] . '">' . $row['origin'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Destination -->
                        <div class="form-group slide-in-right">
                            <label for="destination" class="form-label flex items-center text-gray-700" style="font-weight: 500; margin-bottom: 0.5rem; color: #374151;">
                                <i class="fas fa-map-pin text-primary-600 mr-2"></i> To
                            </label>
                            <div class="relative mt-1">
                                <div class="form-icon-wrapper" style="position: absolute; top: 0; bottom: 0; left: 0; display: flex; align-items: center; padding-left: 1rem; pointer-events: none;">
                                    <i class="fas fa-map-pin form-icon" style="color: #16a34a; font-size: 1.25rem;"></i>
                                </div>
                                <select name="destination" id="destination" class="form-input pl-14 w-full" required style="width: 100%; padding: 0.75rem 1rem 0.75rem 3.5rem; border-radius: 0.5rem; border: 1px solid #e5e7eb; background-color: #f9fafb; transition: all 0.2s; color: #1f2937; appearance: none; background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e'); background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                                    <option value="">Select Destination</option>
                                    <?php
                                    $sql = "SELECT DISTINCT destination FROM routes ORDER BY destination";
                                    $result = $conn->query($sql);

                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo '<option value="' . $row['destination'] . '">' . $row['destination'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Swap destinations button -->
                        <div class="swap-destinations flex items-center justify-center rounded-full bg-white shadow-md border border-gray-100 w-10 h-10 absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 text-primary-600 cursor-pointer hover:scale-110 transition-all z-10" onclick="swapDestinations()" title="Swap Origin and Destination">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Departure Date -->
                    <div class="form-group slide-in-left delay-100">
                        <label for="travel_date" class="form-label flex items-center text-gray-700" style="font-weight: 500; margin-bottom: 0.5rem; color: #374151;">
                            <i class="fas fa-calendar-alt text-primary-600 mr-2"></i> Departure Date
                        </label>
                        <div class="relative mt-1">
                            <div class="form-icon-wrapper" style="position: absolute; top: 0; bottom: 0; left: 0; display: flex; align-items: center; padding-left: 1rem; pointer-events: none;">
                                <i class="fas fa-calendar form-icon" style="color: #16a34a; font-size: 1.25rem;"></i>
                            </div>
                            <input type="date" name="travel_date" id="travel_date" class="form-input pl-14 w-full" required min="<?php echo date('Y-m-d'); ?>" style="width: 100%; padding: 0.75rem 1rem 0.75rem 3.5rem; border-radius: 0.5rem; border: 1px solid #e5e7eb; background-color: #f9fafb; transition: all 0.2s; color: #1f2937;">
                        </div>
                    </div>

                    <!-- Return Date (hidden by default) -->
                    <div class="form-group slide-in-right delay-100 hidden" id="return-date-group">
                        <label for="return_date" class="form-label flex items-center text-gray-700" style="font-weight: 500; margin-bottom: 0.5rem; color: #374151;">
                            <i class="fas fa-calendar-check text-primary-600 mr-2"></i> Return Date
                        </label>
                        <div class="relative mt-1">
                            <div class="form-icon-wrapper" style="position: absolute; top: 0; bottom: 0; left: 0; display: flex; align-items: center; padding-left: 1rem; pointer-events: none;">
                                <i class="fas fa-calendar-check form-icon" style="color: #16a34a; font-size: 1.25rem;"></i>
                            </div>
                            <input type="date" name="return_date" id="return_date" class="form-input pl-14 w-full" min="<?php echo date('Y-m-d'); ?>" style="width: 100%; padding: 0.75rem 1rem 0.75rem 3.5rem; border-radius: 0.5rem; border: 1px solid #e5e7eb; background-color: #f9fafb; transition: all 0.2s; color: #1f2937;">
                        </div>
                    </div>

                    <!-- Number of Passengers -->
                    <div class="form-group slide-in-left delay-200 md:col-span-2 md:col-span-1">
                        <label for="passengers" class="form-label flex items-center text-gray-700" style="font-weight: 500; margin-bottom: 0.5rem; color: #374151;">
                            <i class="fas fa-users text-primary-600 mr-2"></i> Passengers
                        </label>
                        <div class="relative mt-1">
                            <div class="form-icon-wrapper" style="position: absolute; top: 0; bottom: 0; left: 0; display: flex; align-items: center; padding-left: 1rem; pointer-events: none;">
                                <i class="fas fa-user form-icon" style="color: #16a34a; font-size: 1.25rem;"></i>
                            </div>
                            <select name="passengers" id="passengers" class="form-input pl-14 w-full" style="width: 100%; padding: 0.75rem 1rem 0.75rem 3.5rem; border-radius: 0.5rem; border: 1px solid #e5e7eb; background-color: #f9fafb; transition: all 0.2s; color: #1f2937; appearance: none; background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e'); background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                                <option value="1">1 Passenger</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="text-center fade-in delay-200 pt-6">
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-8 rounded-full inline-flex items-center justify-center shadow-lg transition transform hover:scale-105 hover:shadow-xl">
                        <i class="fas fa-search mr-2"></i> Search Available Buses
                    </button>
                    <p class="mt-4 text-sm text-gray-500">
                        Looking for a specific route? <a href="routes.php" class="text-primary-600 hover:underline font-medium">View all routes</a>
                    </p>

                </div>
            </form>
        </div>
    </div>
</section>

<style>
.gradient-animation {
    background: linear-gradient(45deg, rgba(21, 128, 61, 0.9), rgba(22, 101, 52, 0.85), rgba(20, 83, 45, 0.8), rgba(16, 185, 129, 0.85));
    background-size: 400% 400%;
    animation: gradient 15s ease infinite;
}

@keyframes gradient {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0% {
        transform: translateY(0px) translateX(0px);
    }
    50% {
        transform: translateY(-20px) translateX(10px);
    }
    100% {
        transform: translateY(0px) translateX(0px);
    }
}

.journey-tab {
    transition: all 0.3s ease;
}

.journey-tab.active {
    background-color: #16a34a;
    color: white;
    font-weight: 500;
    box-shadow: 0 4px 10px rgba(21, 128, 61, 0.25);
}

.slide-in-top {
    animation: slideInTop 0.5s ease-out;
}

@keyframes slideInTop {
    from {
        transform: translateY(-30px);
        opacity: 0;
    }
    to {
        transform: translateY(-16px);
        opacity: 1;
    }
}
</style>

<!-- Quick Stats Section -->
<section class="py-10 bg-primary-50">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8">
            <div class="text-center p-6 bg-white rounded-xl shadow-sm reveal">
                <div class="text-3xl font-bold text-primary-600 mb-2">10,000+</div>
                <div class="text-gray-600 text-sm md:text-base">Happy Customers</div>
            </div>
            <div class="text-center p-6 bg-white rounded-xl shadow-sm reveal">
                <div class="text-3xl font-bold text-primary-600 mb-2">20+</div>
                <div class="text-gray-600 text-sm md:text-base">Popular Routes</div>
            </div>
            <div class="text-center p-6 bg-white rounded-xl shadow-sm reveal">
                <div class="text-3xl font-bold text-primary-600 mb-2">50+</div>
                <div class="text-gray-600 text-sm md:text-base">Modern Buses</div>
            </div>
            <div class="text-center p-6 bg-white rounded-xl shadow-sm reveal">
                <div class="text-3xl font-bold text-primary-600 mb-2">5+</div>
                <div class="text-gray-600 text-sm md:text-base">Years Experience</div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Routes Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1 rounded-full bg-primary-100 text-primary-700 text-sm font-medium mb-3">TOP DESTINATIONS</span>
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Popular Routes</h2>
            <div class="w-24 h-1 bg-primary-500 mx-auto rounded-full"></div>
            <p class="text-gray-600 max-w-2xl mx-auto mt-4">Discover our most popular routes with competitive prices and frequent departures</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($popular_routes as $index => $route): ?>
                <div class="route-card reveal" data-delay="<?php echo ($index % 3) * 100; ?>">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <?php if (isset($route['min_fare']) && $route['min_fare']): ?>
                            <div class="absolute top-4 right-4 bg-primary-600 text-white px-3 py-1 rounded-full text-sm font-bold">
                                From <?php echo formatCurrency($route['min_fare']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center mr-3">
                                    <i class="fas fa-map-marker-alt text-primary-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold"><?php echo $route['origin']; ?></h3>
                                    <p class="text-gray-500 text-sm">Origin</p>
                                </div>
                            </div>

                            <div class="relative pl-5 my-4">
                                <div class="h-16 border-l-2 border-dashed border-gray-300 absolute left-0 top-0"></div>
                                <div class="flex items-center text-gray-500 text-sm">
                                    <i class="fas fa-road mr-2 text-gray-400"></i>
                                    <span><?php echo $route['distance'] ? htmlspecialchars($route['distance']) . ' km' : 'Distance not specified'; ?></span>
                                </div>
                                <div class="flex items-center text-gray-500 text-sm mt-2">
                                    <i class="fas fa-clock mr-2 text-gray-400"></i>
                                    <span>
                                        <?php
                                        if (isset($route['duration']) && $route['duration']) {
                                            $hours = floor($route['duration'] / 60);
                                            $minutes = $route['duration'] % 60;
                                            echo $hours > 0 ? $hours . 'h ' : '';
                                            echo $minutes > 0 ? $minutes . 'm' : '';
                                        } else {
                                            echo 'Duration not specified';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center mb-6">
                                <div class="w-10 h-10 rounded-full bg-secondary-100 flex items-center justify-center mr-3">
                                    <i class="fas fa-map-pin text-secondary-600"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold"><?php echo $route['destination']; ?></h3>
                                    <p class="text-gray-500 text-sm">Destination</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <div class="flex items-center">
                                    <i class="fas fa-bus mr-1 text-primary-500"></i>
                                    <span><?php echo isset($route['schedule_count']) ? $route['schedule_count'] : '0'; ?> Schedules</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-alt mr-1 text-primary-500"></i>
                                    <span>Daily Trips</span>
                                </div>
                            </div>

                            <a href="search_results.php?origin=<?php echo urlencode($route['origin']); ?>&destination=<?php echo urlencode($route['destination']); ?>&travel_date=<?php echo date('Y-m-d'); ?>" class="block text-center py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-500 transition duration-300">
                                <i class="fas fa-calendar-alt mr-2"></i> View Schedule
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($popular_routes)): ?>
                <div class="col-span-full text-center py-8">
                    <div class="bg-white rounded-xl shadow-md p-8 max-w-lg mx-auto">
                        <i class="fas fa-route text-gray-300 text-5xl mb-4"></i>
                        <p class="text-gray-500">No routes available at the moment. Please check back later.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-10">
            <a href="routes.php" class="inline-flex items-center text-primary-600 hover:text-primary-700 font-semibold bg-white py-3 px-6 rounded-full shadow-sm hover:shadow transition-all duration-300">
                View All Routes <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-20 bg-gradient-to-b from-white to-primary-50 relative overflow-hidden">
    <!-- Background Pattern Elements -->
    <div class="absolute top-0 left-0 w-full h-full opacity-5 pointer-events-none">
        <div class="absolute top-10 left-10 w-40 h-40 rounded-full border-8 border-primary-300"></div>
        <div class="absolute bottom-10 right-10 w-60 h-60 rounded-full border-8 border-primary-300"></div>
        <div class="absolute top-1/2 right-1/4 w-20 h-20 rounded-full bg-primary-300"></div>
    </div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-16">
            <span class="inline-block px-4 py-1 rounded-full bg-primary-100 text-primary-700 text-sm font-medium mb-3 shadow-sm transform transition-transform hover:scale-105">OUR ADVANTAGES</span>
            <h2 class="text-3xl md:text-4xl font-bold mb-4 relative inline-block">
                Why Choose Isiolo Raha
                <div class="w-full h-1 bg-primary-500 absolute -bottom-2 left-0 rounded-full"></div>
            </h2>
            <p class="text-gray-600 max-w-2xl mx-auto mt-8">Experience the best bus travel service in Kenya with our modern fleet, professional staff, and customer-focused approach.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Comfort -->
            <div class="feature-card reveal">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 h-full">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon">
                            <i class="fas fa-couch"></i>
                        </div>
                    </div>
                    <div class="p-6 pt-4">
                        <h3 class="text-xl font-bold mb-3 text-center text-gray-800">Comfort</h3>
                        <p class="text-gray-600 text-center mb-6">Enjoy spacious seating, air conditioning, and entertainment systems on all our buses.</p>
                        <div class="mt-auto text-center">
                            <a href="about.php#comfort" class="feature-btn">
                                Learn More <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Safety -->
            <div class="feature-card reveal" data-delay="100">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 h-full">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                    </div>
                    <div class="p-6 pt-4">
                        <h3 class="text-xl font-bold mb-3 text-center text-gray-800">Safety</h3>
                        <p class="text-gray-600 text-center mb-6">Your safety is our priority with regular maintenance and professional drivers.</p>
                        <div class="mt-auto text-center">
                            <a href="about.php#safety" class="feature-btn">
                                Learn More <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Convenience -->
            <div class="feature-card reveal" data-delay="200">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 h-full">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                    <div class="p-6 pt-4">
                        <h3 class="text-xl font-bold mb-3 text-center text-gray-800">Convenience</h3>
                        <p class="text-gray-600 text-center mb-6">Book tickets online, choose your seats, and pay securely from anywhere.</p>
                        <div class="mt-auto text-center">
                            <a href="about.php#convenience" class="feature-btn">
                                Learn More <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reliability -->
            <div class="feature-card reveal" data-delay="300">
                <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 h-full">
                    <div class="feature-icon-wrapper">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="p-6 pt-4">
                        <h3 class="text-xl font-bold mb-3 text-center text-gray-800">Reliability</h3>
                        <p class="text-gray-600 text-center mb-6">Count on us for punctual departures and arrivals on all our routes.</p>
                        <div class="mt-auto text-center">
                            <a href="about.php#reliability" class="feature-btn">
                                Learn More <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Feature Card Styles */
.feature-card {
    transition: all 0.3s ease;
}

.feature-icon-wrapper {
    position: relative;
    height: 80px;
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    display: flex;
    justify-content: center;
    overflow: visible; /* Changed from hidden to visible */
    margin-bottom: 30px; /* Added margin to make space for the icon */
}

.feature-icon-wrapper::before {
    content: '';
    position: absolute;
    width: 150%;
    height: 100%;
    background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
    top: 0;
    left: -50%;
    transform: skewX(-20deg);
    transition: all 0.6s ease;
}

.feature-card:hover .feature-icon-wrapper::before {
    left: 100%;
}

.feature-icon {
    width: 70px;
    height: 70px;
    background-color: #16a34a;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.75rem;
    position: absolute;
    bottom: -35px; /* Adjusted to position icon properly */
    box-shadow: 0 4px 10px rgba(22, 163, 74, 0.3);
    transition: all 0.3s ease;
    border: 4px solid white;
    z-index: 10; /* Added to ensure icon is above other elements */
}

.feature-card:hover .feature-icon {
    transform: scale(1.1) rotate(5deg);
}

.feature-btn {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1.25rem;
    background-color: #f0fdf4;
    color: #16a34a;
    border-radius: 9999px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.feature-btn:hover {
    background-color: #16a34a;
    color: white;
    box-shadow: 0 4px 6px rgba(22, 163, 74, 0.2);
}

.feature-btn i {
    transition: transform 0.3s ease;
}

.feature-btn:hover i {
    transform: translateX(3px);
}

@media (max-width: 768px) {
    .feature-icon-wrapper {
        height: 70px;
        margin-bottom: 25px;
    }

    .feature-icon {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
        bottom: -30px;
    }

    .feature-card .p-6 {
        padding-top: 1rem;
    }
}

/* Pulse animation for feature icons */
.animate-pulse {
    animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}
</style>

<!-- Testimonials Section -->
<section class="py-20 bg-gradient-to-b from-gray-50 to-white relative overflow-hidden">
    <!-- Background Pattern Elements -->
    <div class="absolute top-0 left-0 w-40 h-40 bg-primary-100 rounded-full opacity-20 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-60 h-60 bg-primary-100 rounded-full opacity-20 translate-x-1/3 translate-y-1/3"></div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1 rounded-full bg-primary-100 text-primary-700 text-sm font-medium mb-3 animate-pulse">WHAT PEOPLE SAY</span>
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Customer Testimonials</h2>
            <div class="w-24 h-1 bg-primary-500 mx-auto rounded-full mb-6"></div>
            <p class="text-gray-600 max-w-2xl mx-auto">Hear what our satisfied customers have to say about their experience traveling with Isiolo Raha Bus Services.</p>
        </div>

        <!-- Testimonial Slider Container -->
        <div class="testimonial-slider-container max-w-6xl mx-auto relative">
            <!-- Large Quote Icon -->
            <div class="absolute -top-6 left-0 text-8xl text-primary-100 opacity-70 z-0">
                <i class="fas fa-quote-left"></i>
            </div>

            <!-- Testimonial Slider -->
            <div class="testimonial-slider overflow-hidden">
                <div class="testimonial-slider-track flex transition-transform duration-500" id="testimonialTrack">
                    <!-- Testimonial 1 -->
                    <div class="testimonial-slide w-full md:w-1/2 lg:w-1/3 flex-shrink-0 px-4">
                        <div class="testimonial-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 h-full flex flex-col">
                            <div class="flex items-center mb-6">
                                <div class="w-16 h-16 rounded-full overflow-hidden mr-4 border-2 border-primary-500 p-0.5">
                                    <?php if (file_exists(__DIR__ . '/assets/images/testimonials/john.jpg')): ?>
                                        <img src="<?php echo APP_URL; ?>/assets/images/testimonials/john.jpg" alt="John Kamau" class="w-full h-full object-cover rounded-full">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-primary-100 flex items-center justify-center text-primary-600 text-xl font-bold">
                                            JK
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h4 class="font-bold text-lg text-gray-800">John Kamau</h4>
                                    <p class="text-primary-600 text-sm font-medium">Regular Traveler</p>
                                </div>
                            </div>
                            <p class="text-gray-600 mb-6 flex-grow italic">"I've been using Isiolo Raha for my monthly trips to Nairobi. The online booking system is so convenient and the buses are always clean and comfortable."</p>
                            <div class="flex text-yellow-400 mt-auto">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 2 -->
                    <div class="testimonial-slide w-full md:w-1/2 lg:w-1/3 flex-shrink-0 px-4">
                        <div class="testimonial-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 h-full flex flex-col">
                            <div class="flex items-center mb-6">
                                <div class="w-16 h-16 rounded-full overflow-hidden mr-4 border-2 border-primary-500 p-0.5">
                                    <?php if (file_exists(__DIR__ . '/assets/images/testimonials/sarah.jpg')): ?>
                                        <img src="<?php echo APP_URL; ?>/assets/images/testimonials/sarah.jpg" alt="Sarah Wanjiku" class="w-full h-full object-cover rounded-full">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-primary-100 flex items-center justify-center text-primary-600 text-xl font-bold">
                                            SW
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h4 class="font-bold text-lg text-gray-800">Sarah Wanjiku</h4>
                                    <p class="text-primary-600 text-sm font-medium">Business Traveler</p>
                                </div>
                            </div>
                            <p class="text-gray-600 mb-6 flex-grow italic">"As someone who travels for business frequently, I appreciate the punctuality and professionalism of Isiolo Raha. Their online payment system is secure and hassle-free."</p>
                            <div class="flex text-yellow-400 mt-auto">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 3 -->
                    <div class="testimonial-slide w-full md:w-1/2 lg:w-1/3 flex-shrink-0 px-4">
                        <div class="testimonial-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 h-full flex flex-col">
                            <div class="flex items-center mb-6">
                                <div class="w-16 h-16 rounded-full overflow-hidden mr-4 border-2 border-primary-500 p-0.5">
                                    <?php if (file_exists(__DIR__ . '/assets/images/testimonials/michael.jpg')): ?>
                                        <img src="<?php echo APP_URL; ?>/assets/images/testimonials/michael.jpg" alt="Michael Omondi" class="w-full h-full object-cover rounded-full">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-primary-100 flex items-center justify-center text-primary-600 text-xl font-bold">
                                            MO
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h4 class="font-bold text-lg text-gray-800">Michael Omondi</h4>
                                    <p class="text-primary-600 text-sm font-medium">Student</p>
                                </div>
                            </div>
                            <p class="text-gray-600 mb-6 flex-grow italic">"As a student, I appreciate the affordable fares and the reliable service. The buses are always on time and the staff is very helpful. Highly recommended!"</p>
                            <div class="flex text-yellow-400 mt-auto">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 4 -->
                    <div class="testimonial-slide w-full md:w-1/2 lg:w-1/3 flex-shrink-0 px-4">
                        <div class="testimonial-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 h-full flex flex-col">
                            <div class="flex items-center mb-6">
                                <div class="w-16 h-16 rounded-full overflow-hidden mr-4 border-2 border-primary-500 p-0.5">
                                    <?php if (file_exists(__DIR__ . '/assets/images/testimonials/jane.jpg')): ?>
                                        <img src="<?php echo APP_URL; ?>/assets/images/testimonials/jane.jpg" alt="Jane Muthoni" class="w-full h-full object-cover rounded-full">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-primary-100 flex items-center justify-center text-primary-600 text-xl font-bold">
                                            JM
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h4 class="font-bold text-lg text-gray-800">Jane Muthoni</h4>
                                    <p class="text-primary-600 text-sm font-medium">Family Traveler</p>
                                </div>
                            </div>
                            <p class="text-gray-600 mb-6 flex-grow italic">"Traveling with my family was a breeze with Isiolo Raha. The staff was very accommodating and helped us with our luggage. The journey was comfortable and my kids enjoyed it too!"</p>
                            <div class="flex text-yellow-400 mt-auto">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 5 -->
                    <div class="testimonial-slide w-full md:w-1/2 lg:w-1/3 flex-shrink-0 px-4">
                        <div class="testimonial-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 h-full flex flex-col">
                            <div class="flex items-center mb-6">
                                <div class="w-16 h-16 rounded-full overflow-hidden mr-4 border-2 border-primary-500 p-0.5">
                                    <?php if (file_exists(__DIR__ . '/assets/images/testimonials/david.jpg')): ?>
                                        <img src="<?php echo APP_URL; ?>/assets/images/testimonials/david.jpg" alt="David Njoroge" class="w-full h-full object-cover rounded-full">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-primary-100 flex items-center justify-center text-primary-600 text-xl font-bold">
                                            DN
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h4 class="font-bold text-lg text-gray-800">David Njoroge</h4>
                                    <p class="text-primary-600 text-sm font-medium">Frequent Traveler</p>
                                </div>
                            </div>
                            <p class="text-gray-600 mb-6 flex-grow italic">"I've tried many bus services, but Isiolo Raha stands out for their consistency and quality. The buses are modern, the drivers are professional, and the journey is always pleasant."</p>
                            <div class="flex text-yellow-400 mt-auto">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slider Controls -->
            <div class="flex justify-center items-center mt-10 space-x-4">
                <button id="prevTestimonial" class="w-12 h-12 rounded-full bg-white shadow-md flex items-center justify-center text-primary-600 hover:bg-primary-50 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2" aria-label="Previous testimonial">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <div class="testimonial-dots flex space-x-2"></div>

                <button id="nextTestimonial" class="w-12 h-12 rounded-full bg-white shadow-md flex items-center justify-center text-primary-600 hover:bg-primary-50 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2" aria-label="Next testimonial">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section relative overflow-hidden py-24" style="background: linear-gradient(135deg, #15803d 0%, #166534 50%, #14532d 100%); color: white;">
    <!-- Enhanced Background Pattern -->
    <div class="absolute inset-0 opacity-20">
        <div class="absolute inset-0 bg-pattern"></div>
    </div>

    <!-- Decorative Elements -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-white opacity-5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-80 h-80 bg-white opacity-5 rounded-full translate-x-1/3 translate-y-1/3"></div>

    <!-- Floating Bus Icons -->
    <div class="absolute left-10 top-1/4 text-white text-opacity-20 text-6xl animate-float-slow hidden md:block">
        <i class="fas fa-bus"></i>
    </div>
    <div class="absolute right-10 bottom-1/4 text-white text-opacity-20 text-5xl animate-float-slow-reverse hidden md:block" style="animation-delay: 1s;">
        <i class="fas fa-ticket-alt"></i>
    </div>

    <div class="container mx-auto px-4 text-center relative z-10">
        <!-- Heading with Highlight -->
        <div class="inline-block relative mb-6">
            <span class="inline-block px-4 py-1 rounded-full bg-white bg-opacity-20 text-white text-sm font-medium mb-3 animate-pulse">
                BOOK YOUR JOURNEY
            </span>
        </div>

        <h2 class="text-3xl md:text-5xl font-bold mb-6 text-white leading-tight">
            Ready to <span class="relative inline-block">
                Travel
                <svg class="absolute -bottom-2 left-0 w-full" height="6" viewBox="0 0 200 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 3C50 0.5 150 0.5 200 3" stroke="white" stroke-width="5" stroke-linecap="round"/>
                </svg>
            </span> with Us?
        </h2>

        <p class="text-xl md:text-2xl mb-10 max-w-2xl mx-auto text-white text-opacity-90">
            Book your tickets now and experience the best bus travel service in Kenya.
            <span class="font-medium">Safe, comfortable, and affordable.</span>
        </p>

        <div class="flex flex-col sm:flex-row justify-center items-center space-y-6 sm:space-y-0 sm:space-x-6">
            <!-- Primary CTA Button with Enhanced Styling -->
            <a href="javascript:void(0)" onclick="smoothScrollTo('search-form')"
               class="group relative overflow-hidden bg-white text-primary-700 font-bold py-4 px-10 rounded-full inline-flex items-center transition-all duration-300 hover:bg-opacity-95 hover:shadow-xl transform hover:scale-105 hover:-translate-y-1">
                <span class="relative z-10 flex items-center">
                    <i class="fas fa-ticket-alt mr-3 text-xl group-hover:animate-bounce-subtle"></i>
                    <span>Book Your Ticket</span>
                </span>
                <span class="absolute inset-0 bg-gradient-to-r from-white via-green-50 to-white bg-size-200 bg-pos-0 group-hover:bg-pos-100 transition-all duration-500"></span>
            </a>



            <!-- Secondary CTA Button with Enhanced Styling -->
            <a href="contact.php"
               class="group relative overflow-hidden border-2 border-white text-white font-bold py-4 px-10 rounded-full inline-flex items-center transition-all duration-300 hover:bg-white hover:text-primary-700 hover:shadow-xl transform hover:scale-105 hover:-translate-y-1">
                <span class="relative z-10 flex items-center">
                    <i class="fas fa-phone-alt mr-3 text-xl group-hover:rotate-12 transition-transform duration-300"></i>
                    <span>Contact Us</span>
                </span>
            </a>
        </div>

        <!-- Trust Indicators -->
        <div class="trust-indicators flex justify-center items-center mt-12 space-x-8 text-white text-opacity-80">
            <div class="flex items-center">
                <i class="fas fa-shield-alt mr-2"></i>
                <span class="text-sm">Secure Booking</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-clock mr-2"></i>
                <span class="text-sm">24/7 Support</span>
            </div>
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span class="text-sm">Easy Cancellation</span>
            </div>
        </div>
    </div>
</section>

<!-- Add custom animations for the CTA section -->
<style>
.animate-float-slow {
    animation: floatSlow 8s ease-in-out infinite;
}

.animate-float-slow-reverse {
    animation: floatSlow 8s ease-in-out infinite reverse;
}

@keyframes floatSlow {
    0%, 100% {
        transform: translateY(0) rotate(0);
    }
    50% {
        transform: translateY(-20px) rotate(5deg);
    }
}

.animate-bounce-subtle {
    animation: bounceSlight 1s infinite;
}

@keyframes bounceSlight {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-3px);
    }
}

.bg-size-200 {
    background-size: 200% 100%;
}

.bg-pos-0 {
    background-position: 0% 0%;
}

.bg-pos-100 {
    background-position: 100% 0%;
}
</style>

<!-- Add scroll animations JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reveal animations on scroll
    const revealElements = document.querySelectorAll('.reveal');

    function checkReveal() {
        revealElements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;

            if (elementTop < windowHeight - 100) {
                const delay = element.dataset.delay || 0;
                setTimeout(() => {
                    element.classList.add('active');
                }, delay);
            }
        });
    }

    // Initial check
    checkReveal();

    // Check on scroll
    window.addEventListener('scroll', checkReveal);

    // Add hover effect to feature cards
    const featureCards = document.querySelectorAll('.feature-card');
    featureCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.querySelector('.feature-icon').classList.add('animate-pulse');
        });

        card.addEventListener('mouseleave', function() {
            this.querySelector('.feature-icon').classList.remove('animate-pulse');
        });
    });
});
</script>

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>
