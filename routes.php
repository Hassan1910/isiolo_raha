<?php
// Set page title
$page_title = "Routes";

// Include header
require_once 'includes/templates/header.php';

// Include database connection
$conn = require_once 'config/database.php';

// Get all routes with minimum fare
$sql = "SELECT r.*,
        (SELECT MIN(s.fare) FROM schedules s WHERE s.route_id = r.id) as min_fare,
        (SELECT COUNT(*) FROM schedules s WHERE s.route_id = r.id AND s.status = 'scheduled') as schedule_count
        FROM routes r
        ORDER BY r.origin, r.destination";
$result = $conn->query($sql);
$routes = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $routes[] = $row;
    }
}

// Get unique origins for filter
$sql = "SELECT DISTINCT origin FROM routes ORDER BY origin";
$result = $conn->query($sql);
$origins = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $origins[] = $row['origin'];
    }
}

// Get unique destinations for filter
$sql = "SELECT DISTINCT destination FROM routes ORDER BY destination";
$result = $conn->query($sql);
$destinations = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $destinations[] = $row['destination'];
    }
}
?>

<!-- Hero Section -->
<section class="bg-primary-700 text-white py-16 relative">
    <div class="absolute inset-0 bg-black opacity-30"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center max-w-3xl mx-auto">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 animate-fade-in">Our Routes</h1>
            <p class="text-xl opacity-90 mb-8 animate-fade-in delay-100">
                Explore our extensive network of destinations across Kenya
            </p>
            <div class="flex justify-center space-x-4">
                <a href="#route-list" class="bg-white text-primary-700 hover:bg-gray-100 font-bold py-3 px-6 rounded-full inline-flex items-center transition duration-300 animate-fade-in delay-200">
                    <i class="fas fa-route mr-2"></i> View All Routes
                </a>
                <a href="index.php#search-form" class="bg-transparent text-white hover:bg-white hover:text-primary-700 font-bold py-3 px-6 rounded-full inline-flex items-center border-2 border-white transition duration-300 animate-fade-in delay-300">
                    <i class="fas fa-search mr-2"></i> Search Buses
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Routes Section -->
<section id="route-list" class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
                <i class="fas fa-filter text-primary-600 mr-2"></i> Filter Routes
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Origin Filter -->
                <div>
                    <label for="origin-filter" class="block text-sm font-medium text-gray-700 mb-2">Origin</label>
                    <select id="origin-filter" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">All Origins</option>
                        <?php foreach ($origins as $origin): ?>
                            <option value="<?php echo htmlspecialchars($origin); ?>"><?php echo htmlspecialchars($origin); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Destination Filter -->
                <div>
                    <label for="destination-filter" class="block text-sm font-medium text-gray-700 mb-2">Destination</label>
                    <select id="destination-filter" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">All Destinations</option>
                        <?php foreach ($destinations as $destination): ?>
                            <option value="<?php echo htmlspecialchars($destination); ?>"><?php echo htmlspecialchars($destination); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Search Button -->
                <div class="flex items-end">
                    <button id="filter-button" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300 flex items-center justify-center">
                        <i class="fas fa-search mr-2"></i> Filter Routes
                    </button>
                </div>
            </div>
        </div>

        <div class="mb-6 flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center mb-4 md:mb-0">
                <h2 class="text-3xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-map-marked-alt text-primary-600 mr-3"></i> Available Routes
                </h2>
                <div class="ml-4 px-3 py-1 bg-primary-100 text-primary-700 rounded-full text-sm font-medium">
                    <span id="route-count" class="font-semibold"><?php echo count($routes); ?></span> routes
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <!-- Sort Dropdown -->
                <div class="relative">
                    <button id="sort-dropdown-btn" class="flex items-center space-x-2 bg-white border border-gray-200 rounded-lg px-4 py-2 text-gray-700 hover:bg-gray-50 transition-all duration-200">
                        <i class="fas fa-sort text-primary-600"></i>
                        <span class="text-sm font-medium">Sort</span>
                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                    </button>
                    <div id="sort-dropdown-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 z-20 hidden">
                        <div class="py-1">
                            <button class="sort-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 active" data-sort="default">
                                <i class="fas fa-sort mr-2 text-primary-600"></i> Default
                            </button>
                            <button class="sort-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" data-sort="price-low">
                                <i class="fas fa-sort-amount-down mr-2 text-primary-600"></i> Price: Low to High
                            </button>
                            <button class="sort-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" data-sort="price-high">
                                <i class="fas fa-sort-amount-up mr-2 text-primary-600"></i> Price: High to Low
                            </button>
                            <button class="sort-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" data-sort="popular">
                                <i class="fas fa-fire-alt mr-2 text-primary-600"></i> Most Popular
                            </button>
                        </div>
                    </div>
                </div>

                <!-- View Toggle -->
                <div class="view-toggle flex bg-white border border-gray-200 p-1 rounded-lg shadow-sm">
                    <button id="grid-view-btn" class="view-btn active px-3 py-2 rounded-md flex items-center text-sm font-medium">
                        <i class="fas fa-th-large mr-2"></i> Grid
                    </button>
                    <button id="map-view-btn" class="view-btn px-3 py-2 rounded-md flex items-center text-sm font-medium">
                        <i class="fas fa-map-marked-alt mr-2"></i> Map
                    </button>
                </div>
            </div>
        </div>

        <!-- Map View Container (hidden by default) -->
        <div id="map-view" class="bg-white rounded-xl shadow-md p-4 mb-6 hidden">
            <div id="routes-map" class="w-full h-96 rounded-lg border border-gray-200"></div>
        </div>

        <?php if (!empty($routes)): ?>
            <div id="routes-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($routes as $index => $route): ?>
                    <div class="route-card"
                         data-origin="<?php echo htmlspecialchars($route['origin']); ?>"
                         data-destination="<?php echo htmlspecialchars($route['destination']); ?>">
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
                                        <h3 class="text-xl font-bold"><?php echo htmlspecialchars($route['origin']); ?></h3>
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
                                            if ($route['duration']) {
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
                                        <h3 class="text-xl font-bold"><?php echo htmlspecialchars($route['destination']); ?></h3>
                                        <p class="text-gray-500 text-sm">Destination</p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-bus mr-1 text-primary-500"></i>
                                        <span><?php echo $route['schedule_count']; ?> Schedules</span>
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
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <div class="bg-white rounded-xl shadow-md p-8 max-w-lg mx-auto">
                    <i class="fas fa-route text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">No Routes Available</h3>
                    <p class="text-gray-500">There are no routes available at the moment. Please check back later.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Call to Action Section -->
<section class="py-16 bg-primary-700 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-6">Ready to Book Your Journey?</h2>
        <p class="text-xl mb-8 max-w-3xl mx-auto">
            Experience comfortable and reliable travel with Isiolo Raha. Book your tickets now for a seamless journey.
        </p>
        <a href="index.php#search-form" class="bg-white text-primary-700 hover:bg-gray-100 font-bold py-4 px-8 rounded-full inline-flex items-center transition duration-300">
            <i class="fas fa-ticket-alt mr-2"></i> Book Your Ticket Now
        </a>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filter functionality
        const originFilter = document.getElementById('origin-filter');
        const destinationFilter = document.getElementById('destination-filter');
        const filterButton = document.getElementById('filter-button');
        const routeCards = document.querySelectorAll('.route-card');
        const routeCount = document.getElementById('route-count');
        const routesContainer = document.getElementById('routes-container');

        filterButton.addEventListener('click', function() {
            const selectedOrigin = originFilter.value;
            const selectedDestination = destinationFilter.value;
            let visibleCount = 0;

            routeCards.forEach(card => {
                const cardOrigin = card.getAttribute('data-origin');
                const cardDestination = card.getAttribute('data-destination');

                const originMatch = !selectedOrigin || cardOrigin === selectedOrigin;
                const destinationMatch = !selectedDestination || cardDestination === selectedDestination;

                if (originMatch && destinationMatch) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            routeCount.textContent = visibleCount;

            if (visibleCount === 0) {
                const noResults = document.createElement('div');
                noResults.className = 'col-span-full text-center py-8';
                noResults.innerHTML = `
                    <div class="bg-white rounded-xl shadow-md p-8 max-w-lg mx-auto">
                        <i class="fas fa-search text-gray-300 text-5xl mb-4"></i>
                        <h3 class="text-xl font-bold mb-2">No Routes Found</h3>
                        <p class="text-gray-500">No routes match your filter criteria. Please try different filters.</p>
                    </div>
                `;

                // Remove any existing no results message
                const existingNoResults = routesContainer.querySelector('.col-span-full');
                if (existingNoResults) {
                    existingNoResults.remove();
                }

                routesContainer.appendChild(noResults);
            } else {
                // Remove any existing no results message
                const existingNoResults = routesContainer.querySelector('.col-span-full');
                if (existingNoResults) {
                    existingNoResults.remove();
                }
            }
        });
    });
</script>

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>
