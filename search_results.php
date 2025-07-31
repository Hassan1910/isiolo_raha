<?php
// Set page title
$page_title = "Search Results";

// Include header
require_once 'includes/templates/header.php';

// Include booking progress component
require_once 'includes/components/booking_progress.php';

// Include database connection
$conn = require_once 'config/database.php';

// Initialize variables
$origin = $destination = $travel_date = $return_date = $passengers = $journey_type = "";
$origin_err = $destination_err = $travel_date_err = $return_date_err = "";
$schedules = [];
$return_schedules = [];
$search_performed = false;

// Process search parameters
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Check if search parameters are provided
    if (isset($_GET["origin"]) && isset($_GET["destination"]) && isset($_GET["travel_date"])) {
        $search_performed = true;

        // Validate origin
        if (empty(trim($_GET["origin"]))) {
            $origin_err = "Please select an origin.";
        } else {
            $origin = trim($_GET["origin"]);
        }

        // Validate destination
        if (empty(trim($_GET["destination"]))) {
            $destination_err = "Please select a destination.";
        } else {
            $destination = trim($_GET["destination"]);
        }

        // Validate travel date
        if (empty(trim($_GET["travel_date"]))) {
            $travel_date_err = "Please select a travel date.";
        } else {
            $travel_date = trim($_GET["travel_date"]);
        }

        // Get passengers
        $passengers = isset($_GET["passengers"]) ? intval($_GET["passengers"]) : 1;
        
        // Set journey type to one-way only
        $journey_type = "one-way";

        // Check input errors before searching
        if (empty($origin_err) && empty($destination_err) && empty($travel_date_err)) {
            // Prepare a select statement
            $sql = "SELECT s.id, s.departure_time, s.arrival_time, s.fare, s.status,
                    r.origin, r.destination, r.distance,
                    b.name AS bus_name, b.type AS bus_type, b.capacity,
                    (SELECT COUNT(*) FROM bookings WHERE schedule_id = s.id AND status != 'cancelled') AS booked_seats
                    FROM schedules s
                    JOIN routes r ON s.route_id = r.id
                    JOIN buses b ON s.bus_id = b.id
                    WHERE r.origin = ? AND r.destination = ?
                    AND DATE(s.departure_time) = ?
                    AND s.status = 'scheduled'
                    ORDER BY s.departure_time ASC";

            if ($stmt = $conn->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("sss", $param_origin, $param_destination, $param_travel_date);

                // Set parameters
                $param_origin = $origin;
                $param_destination = $destination;
                $param_travel_date = $travel_date;

                // Attempt to execute the prepared statement
                if ($stmt->execute()) {
                    $result = $stmt->get_result();

                    // Fetch schedules
                    while ($row = $result->fetch_assoc()) {
                        $schedules[] = $row;
                    }
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                $stmt->close();
            }
            
            // No return journey search needed as we only support one-way trips
        }
    } else {
        // Redirect to home page if no search parameters
        if (empty($_GET)) {
            header("Location: index.php");
            exit;
        }
    }
}

// Get formatted travel date
$formatted_travel_date = !empty($travel_date) ? date('l, F j, Y', strtotime($travel_date)) : '';
?>

<div class="bg-gray-50 py-6">
    <div class="container mx-auto px-4">
        <!-- Booking Progress -->
        <?php renderBookingProgress('search'); ?>

        <!-- Journey Details Summary -->
        <?php if ($search_performed): ?>
        <div class="bg-gradient-to-r from-primary-700 to-primary-600 rounded-lg shadow-lg p-4 md:p-6 mb-6 text-white relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 bg-pattern opacity-10"></div>

            <div class="flex flex-col md:flex-row md:items-center justify-between relative z-10">
                <div class="mb-4 md:mb-0">
                    <div class="flex items-center mb-3">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mr-3">
                                <i class="fas fa-map-marker-alt text-white"></i>
                            </div>
                            <div>
                                <span class="text-xs text-white/70">From</span>
                                <h2 class="text-lg font-bold"><?php echo htmlspecialchars($origin); ?></h2>
                            </div>
                        </div>

                        <div class="mx-2 md:mx-4 flex items-center">
                            <div class="w-16 md:w-24 h-[2px] bg-white/30 relative">
                                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-white/20 flex items-center justify-center">
                                    <i class="fas fa-plane text-xs text-white transform rotate-90"></i>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mr-3">
                                <i class="fas fa-map-marker-alt text-white"></i>
                            </div>
                            <div>
                                <span class="text-xs text-white/70">To</span>
                                <h2 class="text-lg font-bold"><?php echo htmlspecialchars($destination); ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center text-sm">
                        <div class="flex items-center mr-4 mb-2 md:mb-0">
                            <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center mr-2">
                                <i class="far fa-calendar-alt text-xs text-white"></i>
                            </div>
                            <span><?php echo $formatted_travel_date; ?></span>
                        </div>

                        <div class="flex items-center">
                            <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center mr-2">
                                <i class="fas fa-user text-xs text-white"></i>
                            </div>
                            <span><?php echo $passengers; ?> Passenger<?php echo $passengers > 1 ? 's' : ''; ?></span>
                        </div>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <a href="index.php" class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-md transition-all duration-300 inline-flex items-center text-sm border border-white/20 backdrop-blur-sm">
                        <i class="fas fa-search mr-2"></i> New Search
                    </a>
                    <button id="filter-toggle-btn" class="bg-white text-primary-700 px-4 py-2 rounded-md hover:bg-gray-100 transition-all duration-300 inline-flex items-center text-sm">
                        <i class="fas fa-filter mr-2"></i> <span id="filter-text">Filters</span>
                    </button>
                </div>
            </div>
                </div>
            <?php else: ?>
                <div class="text-center py-12 px-4">
                    <div class="relative w-24 h-24 mx-auto mb-6">
                        <div class="absolute inset-0 bg-gray-200 rounded-full animate-pulse"></div>
                        <div class="absolute inset-0 flex items-center justify-center text-5xl text-gray-400">
                            <i class="fas fa-bus-alt"></i>
                        </div>
                    </div>

                    <h3 class="text-2xl font-bold mb-3 text-gray-800">No Outbound Buses Found</h3>

                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        We couldn't find any buses for <span class="font-semibold text-primary-700"><?php echo htmlspecialchars($origin); ?></span> to
                        <span class="font-semibold text-primary-700"><?php echo htmlspecialchars($destination); ?></span> on
                        <span class="font-semibold text-primary-700"><?php echo $formatted_travel_date; ?></span>.
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Return Journey Section removed - Only one-way trips supported -->

        <!-- Search Filters -->
        <div id="search-filters" class="bg-white rounded-lg shadow-lg mb-8 border-t-4 border-primary-600 slide-in-left transition-all duration-300 overflow-hidden" style="<?php echo $search_performed ? 'max-height: 0; opacity: 0;' : ''; ?>">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4 flex items-center justify-between">
                    <span class="flex items-center">
                        <i class="fas fa-filter text-primary-600 mr-2"></i> Search Filters
                    </span>
                    <?php if ($search_performed): ?>
                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                        Modify your search
                    </span>
                    <?php endif; ?>
                </h2>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="space-y-4">
                    <!-- Journey Type - One Way Only -->
                    <div class="mb-4">
                        <div class="flex bg-gray-100 rounded-lg p-1">
                            <button type="button" id="filter-one-way-tab" class="flex-1 py-2 px-4 text-sm font-medium rounded-md bg-white text-primary-700 shadow-sm" disabled>
                                <i class="fas fa-arrow-right mr-2"></i> One Way Only
                            </button>
                        </div>
                        <input type="hidden" name="journey_type" id="filter-journey-type" value="one-way">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Origin -->
                        <div class="form-group hover:shadow-md transition-all duration-300">
                            <label for="origin" class="form-label">From</label>
                            <div class="relative">
                                <div class="form-icon-wrapper">
                                    <i class="form-icon fas fa-map-marker-alt text-primary-600"></i>
                                    <select name="origin" id="origin" class="form-input pl-10" required>
                                        <option value="">Select Origin</option>
                                        <?php
                                        $sql = "SELECT DISTINCT origin FROM routes ORDER BY origin";
                                        $result = $conn->query($sql);

                                        if ($result && $result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                $selected = ($origin == $row['origin']) ? 'selected' : '';
                                                echo '<option value="' . $row['origin'] . '" ' . $selected . '>' . $row['origin'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Destination -->
                        <div class="form-group hover:shadow-md transition-all duration-300">
                            <label for="destination" class="form-label">To</label>
                            <div class="relative">
                                <div class="form-icon-wrapper">
                                    <i class="form-icon fas fa-map-marker-alt text-primary-600"></i>
                                    <select name="destination" id="destination" class="form-input pl-10" required>
                                        <option value="">Select Destination</option>
                                        <?php
                                        $sql = "SELECT DISTINCT destination FROM routes ORDER BY destination";
                                        $result = $conn->query($sql);

                                        if ($result && $result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                $selected = ($destination == $row['destination']) ? 'selected' : '';
                                                echo '<option value="' . $row['destination'] . '" ' . $selected . '>' . $row['destination'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Travel Date -->
                        <div class="form-group hover:shadow-md transition-all duration-300">
                            <label for="travel_date" class="form-label">Travel Date</label>
                            <div class="relative">
                                <div class="form-icon-wrapper">
                                    <i class="form-icon far fa-calendar-alt text-primary-600"></i>
                                    <input type="date" name="travel_date" id="travel_date" class="form-input pl-10" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo $travel_date; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Return Date removed - Only one-way trips supported -->

                        <!-- Number of Passengers -->
                        <div class="form-group hover:shadow-md transition-all duration-300">
                            <label for="passengers" class="form-label">Passengers</label>
                            <div class="relative">
                                <div class="form-icon-wrapper">
                                    <i class="form-icon fas fa-users text-primary-600"></i>
                                    <select name="passengers" id="passengers" class="form-input pl-10">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo ($passengers == $i) ? 'selected' : ''; ?>><?php echo $i; ?> Passenger<?php echo $i > 1 ? 's' : ''; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="search-btn inline-flex items-center">
                            <i class="fas fa-search mr-2"></i> Update Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Results -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden border-t-4 border-primary-600 slide-in-right">
            <!-- Seat Preview Modal (Hidden by default) -->
            <div id="seat-preview-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
                <div class="bg-white rounded-xl shadow-2xl p-6 max-w-lg w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="modal-content">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-bus-alt text-primary-600 mr-2"></i> <span id="preview-bus-name">Bus Name</span>
                        </h3>
                        <button id="close-preview-modal" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200 text-sm">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <i class="fas fa-route text-primary-600 mr-2"></i>
                                <span id="preview-route">Route Information</span>
                            </div>
                            <span id="preview-bus-type" class="px-2 py-1 bg-primary-100 text-primary-800 rounded-full text-xs font-medium">Bus Type</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-users text-primary-600 mr-2"></i>
                                <span id="preview-passengers">2 Passengers</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-couch text-primary-600 mr-2"></i>
                                <span id="preview-available-seats">20 Available</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h4 class="font-bold text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-chair text-primary-600 mr-2"></i> Quick Seat Preview
                        </h4>

                        <div class="bg-gray-100 p-4 rounded-lg border border-gray-200 relative">
                            <!-- Simplified Bus Layout -->
                            <div class="bus-preview-layout">
                                <div class="bus-preview-front">
                                    <i class="fas fa-steering-wheel"></i>
                                </div>

                                <div class="bus-preview-seats">
                                    <!-- Seats will be generated dynamically -->
                                </div>

                                <div class="text-center text-xs text-gray-500 mt-2">
                                    This is a simplified preview. More seats will be available on the next screen.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <button id="cancel-preview" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>

                        <a href="#" id="proceed-to-select" class="btn-primary px-6 py-2 inline-flex items-center">
                            <i class="fas fa-ticket-alt mr-2"></i> Continue to Seat Selection
                        </a>
                    </div>
                </div>
            </div>
            <div class="p-4 md:p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-2">
                    <h2 class="text-xl font-bold flex items-center">
                        <i class="fas fa-bus text-primary-600 mr-2"></i> 
                            Available Buses
                        <?php if (!empty($schedules)): ?>
                            <span class="ml-2 bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                <?php echo count($schedules); ?> Found
                            </span>
                        <?php endif; ?>
                    </h2>

                    <?php if (!empty($schedules)): ?>
                    <div class="mt-2 sm:mt-0 flex items-center text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Click on a bus to view more details
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($search_performed && !empty($schedules)): ?>
                <div class="flex flex-wrap gap-2 mt-3">
                    <!-- Quick filters -->
                    <div class="text-xs text-gray-500 mr-1 flex items-center">Quick filters:</div>
                    <button class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-800 px-2 py-1 rounded-full transition-all duration-200 flex items-center" onclick="sortBuses('price')">
                        <i class="fas fa-sort-amount-down-alt mr-1"></i> Price: Low to High
                    </button>
                    <button class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-800 px-2 py-1 rounded-full transition-all duration-200 flex items-center" onclick="sortBuses('time')">
                        <i class="fas fa-clock mr-1"></i> Earliest Departure
                    </button>
                    <button class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-800 px-2 py-1 rounded-full transition-all duration-200 flex items-center" onclick="sortBuses('duration')">
                        <i class="fas fa-hourglass-half mr-1"></i> Shortest Duration
                    </button>
                    <button class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-800 px-2 py-1 rounded-full transition-all duration-200 flex items-center" onclick="filterBuses('luxury')">
                        <i class="fas fa-star mr-1"></i> Luxury Buses
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($schedules)): ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($schedules as $index => $schedule): ?>
                        <?php
                        // Calculate duration
                        $departure = new DateTime($schedule['departure_time']);
                        $arrival = new DateTime($schedule['arrival_time']);
                        $duration = $departure->diff($arrival);

                        // Format duration
                        $duration_str = '';
                        if ($duration->h > 0) {
                            $duration_str .= $duration->h . 'h ';
                        }
                        $duration_str .= $duration->i . 'm';

                        // Calculate available seats
                        $available_seats = $schedule['capacity'] - $schedule['booked_seats'];

                        // Set animation delay class based on index
                        $delay_class = 'delay-' . (($index % 5) + 1) . '00';
                        ?>

                        <div class="bus-card p-4 sm:p-6 hover:bg-gray-50 transition-all duration-300 cursor-pointer fade-in <?php echo $delay_class; ?> relative"
                             onclick="toggleBusDetails(<?php echo $index; ?>)"
                             data-price="<?php echo $schedule['fare']; ?>"
                             data-departure="<?php echo strtotime($schedule['departure_time']); ?>"
                             data-duration="<?php echo $duration->h * 60 + $duration->i; ?>"
                             data-type="<?php echo strtolower($schedule['bus_type']); ?>">

                            <?php if (strtolower($schedule['bus_type']) === 'luxury'): ?>
                            <div class="absolute top-0 right-0 bg-gradient-to-r from-yellow-400 to-yellow-600 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">
                                <i class="fas fa-star mr-1"></i> LUXURY
                            </div>
                            <?php endif; ?>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                                <!-- Bus Info -->
                                <div class="flex items-center md:col-span-3">
                                    <div class="flex-shrink-0 bg-primary-100 p-3 rounded-full mr-4 relative">
                                        <i class="text-xl text-primary-600 fas <?php echo strtolower($schedule['bus_type']) === 'luxury' ? 'fa-bus-alt' : 'fa-bus'; ?>"></i>
                                        <?php if ($available_seats <= 5 && $available_seats > 0): ?>
                                        <span class="absolute -top-1 -right-1 flex h-4 w-4">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-4 w-4 bg-yellow-500"></span>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-800"><?php echo $schedule['bus_name']; ?></h3>
                                        <div class="flex items-center">
                                            <span class="text-sm text-gray-500 mr-2"><?php echo ucfirst($schedule['bus_type']); ?></span>
                                            <?php if (strtolower($schedule['bus_type']) === 'luxury'): ?>
                                            <div class="flex">
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                            </div>
                                            <?php elseif (strtolower($schedule['bus_type']) === 'executive'): ?>
                                            <div class="flex">
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                                <i class="far fa-star text-yellow-400 text-xs"></i>
                                            </div>
                                            <?php elseif (strtolower($schedule['bus_type']) === 'standard'): ?>
                                            <div class="flex">
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                                <i class="far fa-star text-yellow-400 text-xs"></i>
                                                <i class="far fa-star text-yellow-400 text-xs"></i>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Journey Info -->
                                <div class="md:col-span-6 grid grid-cols-7 gap-1 items-center">
                                    <!-- Departure -->
                                    <div class="col-span-2 text-center md:text-left">
                                        <div class="font-bold text-gray-800 text-lg"><?php echo date('h:i A', strtotime($schedule['departure_time'])); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo date('D, d M', strtotime($schedule['departure_time'])); ?></div>
                                        <div class="text-xs text-gray-400 mt-1"><?php echo $schedule['origin']; ?></div>
                                    </div>

                                    <!-- Duration -->
                                    <div class="col-span-3 flex flex-col items-center justify-center px-2">
                                        <div class="text-xs text-gray-500 mb-1 font-medium">
                                            <i class="far fa-clock mr-1"></i><?php echo $duration_str; ?>
                                        </div>
                                        <div class="w-full flex items-center">
                                            <div class="h-2 w-2 bg-primary-600 rounded-full"></div>
                                            <div class="flex-1 h-0.5 border-t-2 border-dashed border-gray-300 mx-1 relative">
                                                <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-xs text-gray-400">
                                                    <i class="fas fa-bus text-primary-500"></i>
                                                </div>
                                            </div>
                                            <div class="h-2 w-2 bg-primary-600 rounded-full"></div>
                                        </div>
                                        <div class="text-xs text-gray-400 mt-1"><?php echo round($schedule['distance'], 1); ?> km</div>
                                    </div>

                                    <!-- Arrival -->
                                    <div class="col-span-2 text-center md:text-right">
                                        <div class="font-bold text-gray-800 text-lg"><?php echo date('h:i A', strtotime($schedule['arrival_time'])); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo date('D, d M', strtotime($schedule['arrival_time'])); ?></div>
                                        <div class="text-xs text-gray-400 mt-1"><?php echo $schedule['destination']; ?></div>
                                    </div>
                                </div>

                                <!-- Fare & Action -->
                                <div class="md:col-span-3 flex flex-col sm:items-end">
                                    <div class="text-xl font-bold text-primary-600 mb-2">
                                        <?php echo formatCurrency($schedule['fare']); ?>
                                        <span class="text-xs text-gray-500 font-normal">/person</span>
                                    </div>

                                    <div class="flex items-center justify-between sm:justify-end w-full">
                                        <div class="mr-3">
                                            <?php if ($available_seats > 10): ?>
                                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 flex items-center">
                                                    <i class="fas fa-check-circle mr-1"></i> <?php echo $available_seats; ?> seats
                                                </span>
                                            <?php elseif ($available_seats > 0): ?>
                                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 flex items-center">
                                                    <i class="fas fa-exclamation-circle mr-1"></i> <?php echo $available_seats; ?> seats left!
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 flex items-center">
                                                    <i class="fas fa-times-circle mr-1"></i> Fully booked
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div>
                                            <i class="fas fa-chevron-down text-gray-400 bus-details-toggle-<?php echo $index; ?> transition-transform duration-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bus Details (Hidden by default) -->
                            <div class="bus-details-<?php echo $index; ?> hidden mt-4 pt-4 border-t border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <!-- Bus Features -->
                                    <div>
                                        <h4 class="font-bold text-gray-800 mb-2 flex items-center">
                                            <i class="fas fa-list-ul text-primary-600 mr-2"></i> Bus Features
                                        </h4>
                                        <ul class="text-sm text-gray-600 space-y-1">
                                            <li class="flex items-center"><i class="fas fa-check text-primary-500 mr-2"></i> Air Conditioning</li>
                                            <li class="flex items-center"><i class="fas fa-check text-primary-500 mr-2"></i> Comfortable Seating</li>
                                            <?php if (strtolower($schedule['bus_type']) === 'luxury' || strtolower($schedule['bus_type']) === 'executive'): ?>
                                                <li class="flex items-center"><i class="fas fa-check text-primary-500 mr-2"></i> Onboard Entertainment</li>
                                                <li class="flex items-center"><i class="fas fa-check text-primary-500 mr-2"></i> USB Charging Ports</li>
                                                <li class="flex items-center"><i class="fas fa-check text-primary-500 mr-2"></i> Refreshments</li>
                                            <?php endif; ?>
                                            <?php if (strtolower($schedule['bus_type']) === 'luxury'): ?>
                                                <li class="flex items-center"><i class="fas fa-check text-primary-500 mr-2"></i> Extra Leg Room</li>
                                                <li class="flex items-center"><i class="fas fa-check text-primary-500 mr-2"></i> Recliner Seats</li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>

                                    <!-- Boarding Points -->
                                    <div>
                                        <h4 class="font-bold text-gray-800 mb-2 flex items-center">
                                            <i class="fas fa-map-marker-alt text-primary-600 mr-2"></i> Boarding & Drop Points
                                        </h4>
                                        <div class="text-sm text-gray-600">
                                            <div class="flex items-start mb-2">
                                                <div class="flex-shrink-0 w-6 h-6 bg-primary-100 rounded-full flex items-center justify-center mr-2 mt-0.5">
                                                    <i class="fas fa-arrow-up text-xs text-primary-600"></i>
                                                </div>
                                                <div>
                                                    <p class="font-semibold">Boarding: <?php echo $schedule['origin']; ?></p>
                                                    <p class="text-xs text-gray-500"><?php echo $schedule['origin']; ?> Bus Terminal, <?php echo date('h:i A', strtotime($schedule['departure_time'])); ?></p>
                                                </div>
                                            </div>
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0 w-6 h-6 bg-primary-100 rounded-full flex items-center justify-center mr-2 mt-0.5">
                                                    <i class="fas fa-arrow-down text-xs text-primary-600"></i>
                                                </div>
                                                <div>
                                                    <p class="font-semibold">Drop-off: <?php echo $schedule['destination']; ?></p>
                                                    <p class="text-xs text-gray-500"><?php echo $schedule['destination']; ?> Bus Terminal, Est. <?php echo date('h:i A', strtotime($schedule['arrival_time'])); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Booking Info -->
                                    <div>
                                        <h4 class="font-bold text-gray-800 mb-2 flex items-center">
                                            <i class="fas fa-info-circle text-primary-600 mr-2"></i> Booking Information
                                        </h4>
                                        <ul class="text-sm text-gray-600 space-y-1 mb-4">
                                            <li class="flex items-start">
                                                <i class="fas fa-users text-primary-500 mr-2 mt-1"></i>
                                                <span>
                                                    <span class="font-semibold">Capacity:</span> <?php echo $schedule['capacity']; ?> seats
                                                </span>
                                            </li>
                                            <li class="flex items-start">
                                                <i class="fas fa-couch text-primary-500 mr-2 mt-1"></i>
                                                <span>
                                                    <span class="font-semibold">Available:</span>
                                                    <?php echo $available_seats; ?> seats
                                                </span>
                                            </li>
                                            <li class="flex items-start">
                                                <i class="fas fa-money-bill-wave text-primary-500 mr-2 mt-1"></i>
                                                <span>
                                                    <span class="font-semibold">Total for <?php echo $passengers; ?>:</span>
                                                    <?php echo formatCurrency($schedule['fare'] * $passengers); ?>
                                                </span>
                                            </li>
                                        </ul>

                                        <?php if ($available_seats >= $passengers): ?>
                                            <!-- Individual Booking Button -->
                                            <a href="select_seats.php?schedule_id=<?php echo $schedule['id']; ?>&passengers=<?php echo $passengers; ?>"
                                               class="select-seats-btn w-full text-center py-3 flex items-center justify-center relative overflow-hidden group mb-3"
                                               data-schedule-id="<?php echo $schedule['id']; ?>"
                                               data-passengers="<?php echo $passengers; ?>"
                                               data-bus-name="<?php echo $schedule['bus_name']; ?>"
                                               data-bus-type="<?php echo $schedule['bus_type']; ?>"
                                               data-available-seats="<?php echo $available_seats; ?>"
                                               data-tooltip="Click to select from <?php echo $available_seats; ?> available seats">
                                                <span class="relative z-10 flex items-center">
                                                    <i class="fas fa-ticket-alt mr-2"></i> Select Seats
                                                </span>
                                                <span class="absolute inset-0 bg-primary-600 transform transition-transform duration-300 group-hover:scale-105"></span>
                                                <span class="absolute right-0 top-0 h-full w-12 flex items-center justify-center bg-primary-700 text-white text-xs font-bold">
                                                    <?php echo $available_seats; ?>
                                                </span>
                                                <span class="absolute bottom-0 left-0 w-full h-1 bg-primary-800 transform origin-left transition-transform duration-500 scale-x-0 group-hover:scale-x-100"></span>
                                            </a>
                                            
                                            
                                            
                                            <?php if (!isset($_SESSION['user_id'])): ?>
                                            <div class="text-xs text-center mt-2 text-primary-600">
                                                <i class="fas fa-info-circle mr-1"></i> You'll be asked to login or register to complete your booking
                                            </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <button class="btn-secondary w-full text-center text-sm py-3 opacity-70 cursor-not-allowed flex items-center justify-center relative overflow-hidden">
                                                <i class="fas fa-ticket-alt mr-2"></i> Not Available
                                                <span class="absolute right-0 top-0 h-full w-12 flex items-center justify-center bg-gray-700 text-white text-xs font-bold">
                                                    0
                                                </span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Return journey sections removed - Only one-way trips supported -->

                    <?php if ($search_performed): ?>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 max-w-lg mx-auto text-left">
                        <h4 class="font-bold text-blue-800 mb-2 flex items-center">
                            <i class="fas fa-lightbulb text-blue-500 mr-2"></i> Suggestions
                        </h4>
                        <ul class="text-blue-700 text-sm space-y-2 ml-6 list-disc">
                            <li>Try searching for a different date - buses might be available on other days</li>
                            <li>Check for alternative routes or nearby destinations</li>
                            <li>Consider booking in advance for popular routes</li>
                            <li>Contact our support team for assistance with your travel plans</li>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="index.php" class="btn-primary inline-flex items-center">
                            <i class="fas fa-search mr-2"></i> New Search
                        </a>
                        <a href="routes.php" class="btn-secondary inline-flex items-center">
                            <i class="fas fa-route mr-2"></i> View All Routes
                        </a>
                        <a href="contact.php" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-lg transition-all duration-300 inline-flex items-center">
                            <i class="fas fa-envelope mr-2 text-gray-500"></i> Contact Support
                        </a>
                    </div>
                </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize filter toggle button
        const filterToggleBtn = document.getElementById('filter-toggle-btn');
        const searchFilters = document.getElementById('search-filters');
        const filterText = document.getElementById('filter-text');

        if (filterToggleBtn && searchFilters) {
            filterToggleBtn.addEventListener('click', function() {
                if (searchFilters.style.maxHeight === '0px' || searchFilters.style.maxHeight === '') {
                    searchFilters.style.maxHeight = '1000px';
                    searchFilters.style.opacity = '1';
                    filterText.textContent = 'Hide Filters';
                    filterToggleBtn.querySelector('i').classList.remove('fa-filter');
                    filterToggleBtn.querySelector('i').classList.add('fa-times');
                } else {
                    searchFilters.style.maxHeight = '0px';
                    searchFilters.style.opacity = '0';
                    filterText.textContent = 'Filters';
                    filterToggleBtn.querySelector('i').classList.remove('fa-times');
                    filterToggleBtn.querySelector('i').classList.add('fa-filter');
                }
            });
        }

        // Add loading indicator
        const searchForm = document.querySelector('form');
        if (searchForm) {
            searchForm.addEventListener('submit', function() {
                // Create loading overlay
                const loadingOverlay = document.createElement('div');
                loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                loadingOverlay.id = 'loading-overlay';

                const loadingContent = document.createElement('div');
                loadingContent.className = 'bg-white p-6 rounded-lg shadow-xl flex flex-col items-center';

                const spinner = document.createElement('div');
                spinner.className = 'animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-primary-600 mb-4';

                const loadingText = document.createElement('p');
                loadingText.className = 'text-gray-700 text-lg font-medium';
                loadingText.textContent = 'Searching for buses...';

                loadingContent.appendChild(spinner);
                loadingContent.appendChild(loadingText);
                loadingOverlay.appendChild(loadingContent);

                document.body.appendChild(loadingOverlay);
            });
        }
    });

    // Toggle bus details
    function toggleBusDetails(index) {
        const detailsElement = document.querySelector(`.bus-details-${index}`);
        const toggleIcon = document.querySelector(`.bus-details-toggle-${index}`);

        if (detailsElement.classList.contains('hidden')) {
            // Hide all other open details first
            document.querySelectorAll('[class^="bus-details-"]').forEach(el => {
                if (!el.classList.contains('hidden') && el !== detailsElement) {
                    el.classList.add('hidden');
                    const idx = el.className.match(/bus-details-(\d+)/)[1];
                    document.querySelector(`.bus-details-toggle-${idx}`).classList.remove('rotate-180');
                }
            });

            // Show this one
            detailsElement.classList.remove('hidden');
            detailsElement.classList.add('animate-slideDown');
            toggleIcon.classList.add('rotate-180');
        } else {
            detailsElement.classList.add('hidden');
            toggleIcon.classList.remove('rotate-180');
        }
    }

    // Sort buses by price, time, or duration
    function sortBuses(criteria) {
        const busContainer = document.querySelector('.divide-y.divide-gray-200');
        if (!busContainer) return;

        const busCards = Array.from(busContainer.querySelectorAll('.bus-card'));

        busCards.sort((a, b) => {
            if (criteria === 'price') {
                return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
            } else if (criteria === 'time') {
                return parseInt(a.dataset.departure) - parseInt(b.dataset.departure);
            } else if (criteria === 'duration') {
                return parseInt(a.dataset.duration) - parseInt(b.dataset.duration);
            }
            return 0;
        });

        // Clear container and append sorted cards
        busContainer.innerHTML = '';
        busCards.forEach(card => {
            busContainer.appendChild(card);
        });

        // Show sorting feedback
        showSortingFeedback(criteria);
    }

    // Filter buses by type
    function filterBuses(type) {
        const busContainer = document.querySelector('.divide-y.divide-gray-200');
        if (!busContainer) return;

        const busCards = Array.from(busContainer.querySelectorAll('.bus-card'));

        busCards.forEach(card => {
            if (type === 'all' || card.dataset.type === type) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });

        // Show filtering feedback
        showSortingFeedback('type-' + type);
    }

    // Show sorting/filtering feedback
    function showSortingFeedback(criteria) {
        // Create or get feedback element
        let feedback = document.getElementById('sort-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.id = 'sort-feedback';
            feedback.className = 'fixed bottom-4 right-4 bg-primary-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 transform translate-y-20 opacity-0 transition-all duration-300';
            document.body.appendChild(feedback);
        }

        // Set feedback message
        let message = '';
        switch(criteria) {
            case 'price':
                message = 'Sorted by lowest price';
                break;
            case 'time':
                message = 'Sorted by earliest departure';
                break;
            case 'duration':
                message = 'Sorted by shortest duration';
                break;
            case 'type-luxury':
                message = 'Showing luxury buses only';
                break;
            default:
                message = 'Sorted successfully';
        }

        feedback.textContent = message;

        // Show feedback
        feedback.style.transform = 'translate(0)';
        feedback.style.opacity = '1';

        // Hide after 3 seconds
        setTimeout(() => {
            feedback.style.transform = 'translateY(20px)';
            feedback.style.opacity = '0';
        }, 3000);
    }

    // switchFilterJourneyType function removed - Only one-way trips supported
</script>

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>
