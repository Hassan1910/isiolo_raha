<?php
// Include session configuration
require_once 'config/session_config.php';

// Start session
session_start();

// Include database connection
$conn = require_once 'config/database.php';

// Include functions
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Store booking parameters in session
    if (isset($_GET["schedule_id"])) {
        // Get schedule details to store in session
        $schedule_id = intval($_GET["schedule_id"]);
        $passengers = isset($_GET["passengers"]) ? max(1, intval($_GET["passengers"])) : 1;

        // Get schedule details from database
        $sql = "SELECT s.id, s.departure_time, s.arrival_time, s.fare,
                r.origin, r.destination,
                b.name AS bus_name, b.type AS bus_type
                FROM schedules s
                JOIN routes r ON s.route_id = r.id
                JOIN buses b ON s.bus_id = b.id
                WHERE s.id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $schedule_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $schedule_data = $result->fetch_assoc();

                // Store comprehensive booking data in session
                $_SESSION['pending_booking'] = [
                    'schedule_id' => $schedule_id,
                    'passengers' => $passengers,
                    'journey_type' => isset($_GET["journey_type"]) ? trim($_GET["journey_type"]) : "outbound",
                    'return_to' => 'select_seats.php',
                    'timestamp' => time() // Add timestamp for expiration handling
                ];

                // Store search data separately for better UX in login/register pages
                $_SESSION['search_data'] = [
                    'origin' => $schedule_data['origin'],
                    'destination' => $schedule_data['destination'],
                    'departure_time' => $schedule_data['departure_time'],
                    'bus_name' => $schedule_data['bus_name'],
                    'bus_type' => $schedule_data['bus_type'],
                    'fare' => $schedule_data['fare']
                ];
            }

            $stmt->close();
        }
    }

    // Set message with more details
    setFlashMessage("info", "Please login or register to continue with your booking. Your selection has been saved.");

    // Redirect to login page
    header("Location: login.php");
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate schedule_id
    if (empty($_POST["schedule_id"])) {
        setFlashMessage("error", "Invalid schedule selected.");
        header("Location: index.php");
        exit();
    } else {
        $schedule_id = intval($_POST["schedule_id"]);
    }

    // Validate selected seats
    if (empty($_POST["selected_seats"])) {
        setFlashMessage("error", "Please select at least one seat.");
        header("Location: select_seats.php?schedule_id=" . $schedule_id . "&passengers=" . (isset($_POST["passengers"]) ? intval($_POST["passengers"]) : 1));
        exit();
    }

    // Store booking data in session
    $_SESSION['booking_data'] = [
        'schedule_id' => $schedule_id,
        'selected_seats' => $_POST["selected_seats"],
        'total_amount' => $_POST["total_amount"],
        'journey_type' => isset($_POST["journey_type"]) ? trim($_POST["journey_type"]) : "outbound"
    ];

    // Redirect to passenger details page
    header("Location: passenger_details.php");
    exit();
}

// Initialize variables
$schedule_id = $passengers = 0;
$schedule = null;
$booked_seats = [];

// Process request parameters
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Validate schedule_id
    if (empty($_GET["schedule_id"])) {
        setFlashMessage("error", "Invalid schedule selected.");
        header("Location: index.php");
        exit();
    } else {
        $schedule_id = intval($_GET["schedule_id"]);
    }

    // Check if user is returning from login/registration with pending booking
    $returning_from_login = false;
    if (isset($_SESSION['pending_booking'])) {
        // Set a welcome back message
        if ($_SESSION['pending_booking']['schedule_id'] == $schedule_id) {
            $returning_from_login = true;
            setFlashMessage("success", "Welcome back! You can now continue with your booking.");
        }

        // Clear pending booking data
        unset($_SESSION['pending_booking']);
    }

    // Clear search data if it exists
    if (isset($_SESSION['search_data'])) {
        unset($_SESSION['search_data']);
    }

    // Get passengers - ensure it's at least 1
    $passengers = isset($_GET["passengers"]) ? max(1, intval($_GET["passengers"])) : 1;
    
    // Get journey type
    $journey_type = isset($_GET["journey_type"]) ? trim($_GET["journey_type"]) : "outbound";

    // Get schedule details
    $sql = "SELECT s.id, s.departure_time, s.arrival_time, s.fare, s.status,
            r.origin, r.destination, r.distance,
            b.name AS bus_name, b.type AS bus_type, b.capacity
            FROM schedules s
            JOIN routes r ON s.route_id = r.id
            JOIN buses b ON s.bus_id = b.id
            WHERE s.id = ? AND s.status = 'scheduled'";

    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $schedule_id);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $schedule = $result->fetch_assoc();
            } else {
                setFlashMessage("error", "Schedule not found.");
                header("Location: index.php");
                exit();
            }
        } else {
            setFlashMessage("error", "Something went wrong. Please try again later.");
            header("Location: index.php");
            exit();
        }

        // Close statement
        $stmt->close();
    }

    // Get booked seats
    $sql = "SELECT seat_number FROM bookings WHERE schedule_id = ? AND status IN ('confirmed', 'pending')";

    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $schedule_id);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $booked_seats[] = $row['seat_number'];
            }
        }

        // Close statement
        $stmt->close();
    }
}


// Set page title
$page_title = "Select Seats";

// Include header
require_once 'includes/templates/header.php';

// Include booking progress component
require_once 'includes/components/booking_progress.php';
?>

<div class="bg-gray-100 py-6">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-6 text-primary-700">Select Your Seats</h1>

        <!-- Welcome back message for users returning from login -->
        <?php if ($returning_from_login): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg animate-fadeIn">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-500"></i>
                </div>
                <div class="ml-3">
                    <p class="font-medium">Welcome back! You can now continue with your booking.</p>
                    <p class="text-sm mt-1">Please select your seats to proceed with your booking.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Fix for passenger count issue - only show for multiple passengers -->
        <?php if ($passengers > 1): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm">
                        If you're having trouble selecting multiple seats, please use one of these links:
                    </p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <a href="<?php echo "select_seats.php?schedule_id={$schedule_id}&passengers=1"; ?>" class="inline-block bg-white hover:bg-gray-100 text-gray-800 font-semibold py-1 px-3 border border-gray-400 rounded shadow text-sm">
                            1 Passenger
                        </a>
                        <a href="<?php echo "select_seats.php?schedule_id={$schedule_id}&passengers=2"; ?>" class="inline-block bg-white hover:bg-gray-100 text-gray-800 font-semibold py-1 px-3 border border-gray-400 rounded shadow text-sm">
                            2 Passengers
                        </a>
                        <a href="<?php echo "select_seats.php?schedule_id={$schedule_id}&passengers=3"; ?>" class="inline-block bg-white hover:bg-gray-100 text-gray-800 font-semibold py-1 px-3 border border-gray-400 rounded shadow text-sm">
                            3 Passengers
                        </a>
                        <a href="<?php echo "select_seats.php?schedule_id={$schedule_id}&passengers=4"; ?>" class="inline-block bg-white hover:bg-gray-100 text-gray-800 font-semibold py-1 px-3 border border-gray-400 rounded shadow text-sm">
                            4 Passengers
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($schedule): ?>
            <!-- Booking Progress -->
            <?php renderBookingProgress('select'); ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Trip Details -->
                <div class="lg:col-span-1 order-2 lg:order-1">
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border-l-4 border-primary-600 transform transition-all hover:shadow-xl">
                        <h2 class="text-2xl font-bold mb-4 text-primary-700 flex items-center">
                            <i class="fas fa-info-circle mr-2"></i> Trip Details
                        </h2>

                        <div class="space-y-5">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-700">
                                    <i class="fas fa-route"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-500">Route</p>
                                    <p class="font-bold text-gray-800"><?php echo $schedule['origin']; ?> to <?php echo $schedule['destination']; ?></p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-700">
                                    <i class="fas fa-bus"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-500">Bus</p>
                                    <p class="font-bold text-gray-800"><?php echo $schedule['bus_name']; ?> <span class="text-xs px-2 py-1 rounded-full bg-gray-200 ml-1"><?php echo ucfirst($schedule['bus_type']); ?></span></p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-700">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-500">Departure</p>
                                    <p class="font-bold text-gray-800"><?php echo date('h:i A', strtotime($schedule['departure_time'])); ?>, <?php echo date('d M, Y', strtotime($schedule['departure_time'])); ?></p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-700">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-500">Arrival</p>
                                    <p class="font-bold text-gray-800"><?php echo date('h:i A', strtotime($schedule['arrival_time'])); ?>, <?php echo date('d M, Y', strtotime($schedule['arrival_time'])); ?></p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center text-primary-700">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-500">Fare per Seat</p>
                                    <p class="font-bold text-primary-600"><?php echo formatCurrency($schedule['fare']); ?></p>
                                </div>
                            </div>

                            <hr class="border-dashed border-gray-200">

                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-2">Selected Seats</p>
                                <p class="font-bold text-gray-800 text-lg" id="selected_seats_display">None</p>
                            </div>

                            <div class="bg-primary-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-600 mb-2">Total Amount</p>
                                <p class="font-bold text-primary-700 text-2xl" id="total_amount"><?php echo formatCurrency(0); ?></p>
                            </div>

                            <div class="text-center mt-4">
                                <a href="<?php echo "index.php"; ?>" class="inline-flex items-center text-primary-600 hover:text-primary-800 transition">
                                    <i class="fas fa-arrow-left mr-2"></i> Change search
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-gray-400">
                        <h2 class="text-xl font-bold mb-4 text-gray-700 flex items-center">
                            <i class="fas fa-info-circle mr-2"></i> Seat Legend
                        </h2>

                        <div class="space-y-3">
                            <!-- Seat Types -->
                            <div class="mb-4">
                                <h3 class="text-sm font-semibold text-gray-600 mb-2 border-b pb-1">Seat Types</h3>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="flex items-center p-2 hover:bg-gray-50 rounded-lg transition">
                                        <div class="seat window available w-10 h-10 mr-3 shadow-sm"></div>
                                        <span class="font-medium text-sm">Window Seat</span>
                                    </div>
                                    <div class="flex items-center p-2 hover:bg-gray-50 rounded-lg transition">
                                        <div class="seat available w-10 h-10 mr-3 shadow-sm" style="background-color: #f3e8ff; color: #6b21a8; border: 1px solid #8b5cf6;"></div>
                                        <span class="font-medium text-sm">Aisle Seat</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Seat Status -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-600 mb-2 border-b pb-1">Seat Status</h3>
                                <div class="space-y-2">
                                    <div class="flex items-center p-2 hover:bg-gray-50 rounded-lg transition">
                                        <div class="seat available w-10 h-10 mr-3 shadow-sm"></div>
                                        <div>
                                            <span class="font-medium block">Available</span>
                                            <span class="text-xs text-gray-500">Click to select</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center p-2 hover:bg-gray-50 rounded-lg transition">
                                        <div class="seat selected w-10 h-10 mr-3 shadow-sm"></div>
                                        <div>
                                            <span class="font-medium block">Selected</span>
                                            <span class="text-xs text-gray-500">Click again to deselect</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center p-2 hover:bg-gray-50 rounded-lg transition">
                                        <div class="seat booked w-10 h-10 mr-3 shadow-sm"></div>
                                        <div>
                                            <span class="font-medium block">Booked</span>
                                            <span class="text-xs text-gray-500">Already taken</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 p-3 bg-blue-50 rounded-lg border border-blue-100">
                            <div class="flex items-start">
                                <div class="text-blue-500 mr-3">
                                    <i class="fas fa-info-circle text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-blue-800 font-medium">Selection Instructions</p>
                                    <p class="text-xs text-blue-600 mt-1">Please select <?php echo $passengers; ?> seat(s) to continue. Window seats offer better views, while aisle seats provide easier access.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seat Selection -->
                <div class="lg:col-span-2 order-1 lg:order-2">
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-4">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-primary-700 flex items-center">
                                <i class="fas fa-chair mr-2"></i> Bus Seating
                            </h2>
                            <div class="bg-primary-100 text-primary-800 px-4 py-1 rounded-full text-sm font-medium">
                                <?php echo $passengers; ?> passenger<?php echo $passengers > 1 ? 's' : ''; ?>
                            </div>
                        </div>

                        <div class="mb-6 p-4 rounded-lg bg-gray-50 border border-gray-200">
                            <p class="text-center text-gray-700">
                                <i class="fas fa-info-circle text-primary-600 mr-2"></i>
                                Please select <?php echo $passengers; ?> seat(s) from the available seats below.
                            </p>

                        </div>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="seat-form">
                            <input type="hidden" name="schedule_id" value="<?php echo $schedule_id; ?>">
                            <input type="hidden" name="selected_seats" id="selected_seats" value="">
                            <input type="hidden" name="total_amount" id="total_amount_input" value="0">
                            <input type="hidden" id="seat_price" value="<?php echo $schedule['fare']; ?>">
                            <input type="hidden" id="max_seats" value="<?php echo $passengers; ?>" data-debug="Passenger count: <?php echo $passengers; ?>">
                            <input type="hidden" name="passengers" id="passengers_input" value="<?php echo $passengers; ?>">
                            <input type="hidden" name="journey_type" value="<?php echo $journey_type; ?>">

                            <!-- Bus Layout -->
                            <div class="relative mx-auto mb-8 max-w-md">
                                <!-- Bus front -->
                                <div class="bus-front">
                                    <i class="fas fa-bus mr-1"></i> Front
                                </div>

                                <div class="bus-container p-4 border-2 border-gray-300 bg-gray-50">
                                    <!-- Bus Roof -->
                                    <div class="bus-roof mb-2">
                                        <div class="bus-roof-lights">
                                            <div class="roof-light"></div>
                                            <div class="roof-light"></div>
                                            <div class="roof-light"></div>
                                            <div class="roof-light"></div>
                                            <div class="roof-light"></div>
                                        </div>
                                    </div>

                                    <div class="bus-floor">
                                        <div class="bus-driver-area">
                                            <div class="bus-steering">
                                                <i class="fas fa-dharmachakra"></i>
                                            </div>
                                            <div class="driver-label">DRIVER</div>
                                        </div>

                                        <div class="bus-entrance mb-4">
                                            <span>ENTRANCE</span>
                                            <div class="entrance-steps">
                                                <div class="step"></div>
                                                <div class="step"></div>
                                            </div>
                                        </div>

                                        <div class="bus-body">
                                            <!-- Seat Type Legend (inside bus) -->
                                            <div class="seat-type-legend">
                                                <div class="legend-item">
                                                    <div class="legend-color window-seat"></div>
                                                    <span>Window</span>
                                                </div>
                                                <div class="legend-item">
                                                    <div class="legend-color aisle-seat"></div>
                                                    <span>Aisle</span>
                                                </div>
                                            </div>

                                            <!-- Column Labels -->
                                            <div class="column-labels">
                                                <div class="column-label">A</div>
                                                <div class="column-label">B</div>
                                                <div class="column-label aisle-label"></div>
                                                <div class="column-label">C</div>
                                                <div class="column-label">D</div>
                                            </div>

                                            <div class="seat-rows">
                                            <?php
                                            // Generate bus layout based on capacity
                                            $capacity = $schedule['capacity'];
                                            $rows = ceil($capacity / 4);

                                            // Calculate how many complete rows of 4 seats we have
                                            $complete_rows = floor($capacity / 4);

                                            // Calculate remaining seats for the last row
                                            $remaining_seats = $capacity % 4;

                                            for ($i = 1; $i <= $rows; $i++) {
                                                echo '<div class="seat-row">';
                                                echo '<div class="row-number">' . $i . '</div>';

                                                // For complete rows, show all seats
                                                if ($i <= $complete_rows) {
                                                    // Left side seats (A)
                                                    $seat_number = 'A' . $i;
                                                    $seat_status = in_array($seat_number, $booked_seats) ? 'booked' : 'available';
                                                    $seat_tooltip = in_array($seat_number, $booked_seats) ? 'Already booked' : 'Window seat';

                                                    echo '<div class="seat ' . $seat_status . ' window left"
                                                        data-seat="' . $seat_number . '">' . $seat_number . '
                                                        <span class="seat-tooltip">' . $seat_tooltip . '</span>
                                                    </div>';

                                                    // Left aisle seats (B)
                                                    $seat_number = 'B' . $i;
                                                    $seat_status = in_array($seat_number, $booked_seats) ? 'booked' : 'available';
                                                    $seat_tooltip = in_array($seat_number, $booked_seats) ? 'Already booked' : 'Aisle seat';

                                                    echo '<div class="seat ' . $seat_status . '"
                                                        data-seat="' . $seat_number . '">' . $seat_number . '
                                                        <span class="seat-tooltip">' . $seat_tooltip . '</span>
                                                    </div>';

                                                    // Aisle
                                                    echo '<div class="aisle-indicator">aisle</div>';

                                                    // Right aisle seats (C)
                                                    $seat_number = 'C' . $i;
                                                    $seat_status = in_array($seat_number, $booked_seats) ? 'booked' : 'available';
                                                    $seat_tooltip = in_array($seat_number, $booked_seats) ? 'Already booked' : 'Aisle seat';

                                                    echo '<div class="seat ' . $seat_status . '"
                                                        data-seat="' . $seat_number . '">' . $seat_number . '
                                                        <span class="seat-tooltip">' . $seat_tooltip . '</span>
                                                    </div>';

                                                    // Right window seats (D)
                                                    $seat_number = 'D' . $i;
                                                    $seat_status = in_array($seat_number, $booked_seats) ? 'booked' : 'available';
                                                    $seat_tooltip = in_array($seat_number, $booked_seats) ? 'Already booked' : 'Window seat';

                                                    echo '<div class="seat ' . $seat_status . ' window right"
                                                        data-seat="' . $seat_number . '">' . $seat_number . '
                                                        <span class="seat-tooltip">' . $seat_tooltip . '</span>
                                                    </div>';
                                                }
                                                // For the last partial row, show only the needed seats
                                                else if ($i == $complete_rows + 1 && $remaining_seats > 0) {
                                                    // Left side seats (A) - always show if there are remaining seats
                                                    $seat_number = 'A' . $i;
                                                    $seat_status = in_array($seat_number, $booked_seats) ? 'booked' : 'available';
                                                    $seat_tooltip = in_array($seat_number, $booked_seats) ? 'Already booked' : 'Window seat';

                                                    echo '<div class="seat ' . $seat_status . ' window left"
                                                        data-seat="' . $seat_number . '">' . $seat_number . '
                                                        <span class="seat-tooltip">' . $seat_tooltip . '</span>
                                                    </div>';

                                                    // Left aisle seats (B) - show if at least 2 remaining seats
                                                    if ($remaining_seats >= 2) {
                                                        $seat_number = 'B' . $i;
                                                        $seat_status = in_array($seat_number, $booked_seats) ? 'booked' : 'available';
                                                        $seat_tooltip = in_array($seat_number, $booked_seats) ? 'Already booked' : 'Aisle seat';

                                                        echo '<div class="seat ' . $seat_status . '"
                                                            data-seat="' . $seat_number . '">' . $seat_number . '
                                                            <span class="seat-tooltip">' . $seat_tooltip . '</span>
                                                        </div>';
                                                    } else {
                                                        echo '<div class="seat aisle"></div>';
                                                    }

                                                    // Aisle
                                                    echo '<div class="aisle-indicator">aisle</div>';

                                                    // Right aisle seats (C) - show if at least 3 remaining seats
                                                    if ($remaining_seats >= 3) {
                                                        $seat_number = 'C' . $i;
                                                        $seat_status = in_array($seat_number, $booked_seats) ? 'booked' : 'available';
                                                        $seat_tooltip = in_array($seat_number, $booked_seats) ? 'Already booked' : 'Aisle seat';

                                                        echo '<div class="seat ' . $seat_status . '"
                                                            data-seat="' . $seat_number . '">' . $seat_number . '
                                                            <span class="seat-tooltip">' . $seat_tooltip . '</span>
                                                        </div>';
                                                    } else {
                                                        echo '<div class="seat aisle"></div>';
                                                    }

                                                    // Right window seats (D) - show if 4 remaining seats
                                                    if ($remaining_seats >= 4) {
                                                        $seat_number = 'D' . $i;
                                                        $seat_status = in_array($seat_number, $booked_seats) ? 'booked' : 'available';
                                                        $seat_tooltip = in_array($seat_number, $booked_seats) ? 'Already booked' : 'Window seat';

                                                        echo '<div class="seat ' . $seat_status . ' window right"
                                                            data-seat="' . $seat_number . '">' . $seat_number . '
                                                            <span class="seat-tooltip">' . $seat_tooltip . '</span>
                                                        </div>';
                                                    } else {
                                                        echo '<div class="seat aisle"></div>';
                                                    }
                                                }
                                                // For any rows beyond capacity, show empty spaces
                                                else {
                                                    echo '<div class="seat aisle"></div>';
                                                    echo '<div class="seat aisle"></div>';
                                                    echo '<div class="aisle-indicator">aisle</div>';
                                                    echo '<div class="seat aisle"></div>';
                                                    echo '<div class="seat aisle"></div>';
                                                }

                                                echo '</div>';
                                            }
                                            ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bus back -->
                                <div class="bus-back">
                                    <i class="fas fa-chevron-down mr-1"></i> Back
                                </div>
                            </div>

                            <div id="selection-info" class="hidden p-4 rounded-lg bg-green-50 border border-green-200 mb-6 fade-in">
                                <p class="text-center text-green-800">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    <span id="selection-text">You've selected all required seats!</span>
                                </p>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn-primary px-8 py-4 flex items-center justify-center mx-auto disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300" id="continue-btn" disabled>
                                    <span>Continue to Passenger Details</span>
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-lg p-8 text-center max-w-lg mx-auto">
                <div class="text-6xl text-gray-300 mb-6">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h3 class="text-2xl font-bold mb-4">Schedule Not Found</h3>
                <p class="text-gray-600 mb-8">
                    The schedule you're looking for doesn't exist or has been cancelled.
                </p>
                <a href="index.php" class="btn-primary inline-flex items-center">
                    <i class="fas fa-search mr-2"></i> Search Again
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    /* Custom styles specifically for this page */
    .bus-layout {
        width: 100%;
        max-width: 480px;
        margin: 0 auto;
        perspective: 1000px;
    }

    .bus-body {
        background-color: #f8fafc;
        border: 2px solid #94a3b8;
        border-radius: 12px;
        padding: 20px 15px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transform: rotateX(5deg);
        transform-style: preserve-3d;
        position: relative;
    }

    /* Bus Roof Styles */
    .bus-roof {
        background-color: #334155;
        border-radius: 12px 12px 0 0;
        height: 20px;
        position: relative;
        overflow: hidden;
    }

    .bus-roof-lights {
        display: flex;
        justify-content: space-around;
        padding: 5px 10px;
    }

    .roof-light {
        width: 8px;
        height: 8px;
        background-color: #fef08a;
        border-radius: 50%;
        box-shadow: 0 0 5px 2px rgba(254, 240, 138, 0.5);
        animation: blink 3s infinite alternate;
    }

    .roof-light:nth-child(odd) {
        animation-delay: 1.5s;
    }

    @keyframes blink {
        0%, 80% { opacity: 0.3; }
        100% { opacity: 1; }
    }

    /* Driver Area Styles */
    .bus-driver-area {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 15px;
        position: relative;
    }

    .driver-label {
        font-size: 10px;
        color: #64748b;
        font-weight: bold;
        margin-top: 5px;
        letter-spacing: 1px;
    }

    /* Column Labels */
    .column-labels {
        display: flex;
        justify-content: space-between;
        padding: 0 10px;
        margin-bottom: 10px;
    }

    .column-label {
        width: 30px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 12px;
        color: #64748b;
        background-color: #f1f5f9;
        border-radius: 4px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .column-label.aisle-label {
        background-color: transparent;
        box-shadow: none;
    }

    /* Seat Type Legend */
    .seat-type-legend {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-bottom: 15px;
        padding: 8px;
        background-color: rgba(255, 255, 255, 0.7);
        border-radius: 8px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        font-size: 11px;
        color: #64748b;
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 3px;
        margin-right: 5px;
    }

    .legend-color.window-seat {
        background-color: #bfdbfe;
        border: 1px solid #3b82f6;
    }

    .legend-color.aisle-seat {
        background-color: #e9d5ff;
        border: 1px solid #8b5cf6;
    }

    /* Enhanced Entrance */
    .bus-entrance {
        width: 80px;
        height: 30px;
        background-color: #334155;
        margin: 0 auto;
        border-radius: 0 0 10px 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #f8fafc;
        font-size: 10px;
        font-weight: bold;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        position: relative;
        overflow: hidden;
    }

    .entrance-steps {
        display: flex;
        flex-direction: column;
        width: 100%;
        margin-top: 2px;
    }

    .step {
        height: 3px;
        background-color: #475569;
        margin-top: 2px;
    }

    /* Seat Rows */
    .seat-rows {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .seat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 50px;
        position: relative;
    }

    .seat-row:not(:last-child)::after {
        content: '';
        position: absolute;
        bottom: -6px;
        left: 30px;
        right: 30px;
        height: 1px;
        background-color: rgba(0,0,0,0.05);
    }

    .row-number {
        width: 24px;
        height: 24px;
        background-color: #f1f5f9;
        color: #64748b;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        margin-right: 8px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    /* Enhanced Seat Styles */
    .seat {
        width: 50px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        position: relative;
        border-radius: 8px 8px 0 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        user-select: none;
        z-index: 10;
    }

    .seat::before {
        content: '';
        position: absolute;
        left: 5px;
        right: 5px;
        bottom: 0;
        height: 6px;
        background-color: rgba(0,0,0,0.1);
        border-radius: 0 0 4px 4px;
        z-index: 0;
    }

    /* Seat Status Styles */
    .seat.available {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #16a34a;
        cursor: pointer;
    }

    .seat.available:hover {
        transform: translateY(-4px) scale(1.05);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        background-color: #bbf7d0;
    }

    .seat.selected {
        background-color: #16a34a;
        color: white;
        border: 2px solid #15803d;
        box-shadow: 0 4px 8px rgba(22, 163, 74, 0.3);
        transform: translateY(-4px);
        font-weight: bold;
        z-index: 20;
    }

    .seat.booked {
        background-color: #f3f4f6;
        color: #9ca3af;
        border: 1px solid #d1d5db;
        cursor: not-allowed;
    }

    .seat.booked::after {
        content: '×';
        position: absolute;
        font-size: 24px;
        color: #ef4444;
        opacity: 0.5;
    }

    .seat.aisle {
        background-color: transparent;
        border: none;
        cursor: default;
        box-shadow: none;
    }

    /* Window and Aisle Seat Styles */
    .seat.window {
        position: relative;
        background-color: #bfdbfe;
        color: #1e40af;
        border: 1px solid #3b82f6;
    }

    .seat.window.available {
        background-color: #dbeafe;
        border: 1px solid #3b82f6;
    }

    .seat.window.selected {
        background-color: #2563eb;
        border: 2px solid #1d4ed8;
    }

    .seat.window::after {
        content: '';
        position: absolute;
        top: 5px;
        width: 4px;
        bottom: 5px;
        background-color: rgba(0,0,0,0.1);
        border-radius: 2px;
        z-index: 0;
    }

    .seat.window.left::after {
        left: 5px;
    }

    .seat.window.right::after {
        right: 5px;
    }

    /* Aisle Seat Styles */
    .seat:not(.window):not(.aisle).available {
        background-color: #f3e8ff;
        color: #6b21a8;
        border: 1px solid #8b5cf6;
    }

    .seat:not(.window):not(.aisle).selected {
        background-color: #8b5cf6;
        color: white;
        border: 2px solid #7c3aed;
    }

    .aisle-indicator {
        height: 100%;
        width: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 10px;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 1px;
        writing-mode: vertical-rl;
        transform: rotate(180deg);
        text-align: center;
        position: relative;
    }

    .aisle-indicator::after {
        content: '';
        position: absolute;
        top: 5px;
        bottom: 5px;
        width: 1px;
        background-color: #cbd5e1;
        left: 50%;
        transform: translateX(-50%);
    }

    /* Bus Steering Wheel */
    .bus-steering {
        width: 50px;
        height: 50px;
        background-color: #334155;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 1.5rem;
        box-shadow: inset 0 0 10px rgba(0,0,0,0.3);
        border: 3px solid #475569;
        position: relative;
        overflow: hidden;
    }

    .bus-steering::before {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        background-color: #1e293b;
        border-radius: 50%;
        z-index: 0;
    }

    .bus-steering i {
        position: relative;
        z-index: 1;
    }

    /* Bus Container */
    .bus-container {
        background-image: url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23f3f4f6' fill-opacity='1' fill-rule='evenodd'%3E%3Ccircle cx='3' cy='3' r='3'/%3E%3Ccircle cx='13' cy='13' r='3'/%3E%3C/g%3E%3C/svg%3E");
        transform-style: preserve-3d;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border: 2px solid #64748b;
    }

    .bus-floor {
        background: repeating-linear-gradient(
            45deg,
            #e5e7eb,
            #e5e7eb 5px,
            #d1d5db 5px,
            #d1d5db 10px
        );
        padding: 15px 10px;
        border-radius: 8px;
        margin-bottom: 10px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Bus Front and Back */
    .bus-front, .bus-back {
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-transform: uppercase;
        font-weight: bold;
        letter-spacing: 1px;
        font-size: 12px;
    }

    .bus-front {
        background-color: #334155;
        color: white;
        border-radius: 12px 12px 0 0;
        position: relative;
        overflow: hidden;
    }

    .bus-front::before, .bus-front::after {
        content: '';
        position: absolute;
        width: 30px;
        height: 15px;
        background-color: #f8fafc;
        border-radius: 50% 50% 0 0;
        top: -5px;
    }

    .bus-front::before {
        left: 20px;
    }

    .bus-front::after {
        right: 20px;
    }

    .bus-back {
        background-color: #334155;
        color: white;
        border-radius: 0 0 12px 12px;
    }

    /* Mobile optimization */
    @media (max-width: 768px) {
        .seat {
            width: 40px;
            height: 36px;
            font-size: 0.8rem;
        }

        .row-number {
            width: 20px;
            height: 20px;
            font-size: 10px;
        }

        .aisle-indicator {
            width: 30px;
            font-size: 8px;
        }

        .seat-row {
            height: 40px;
        }

        .column-label {
            width: 25px;
            height: 18px;
            font-size: 10px;
        }

        .bus-steering {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }

        .driver-label {
            font-size: 8px;
        }

        .bus-entrance {
            width: 60px;
            height: 25px;
            font-size: 8px;
        }

        .seat-type-legend {
            flex-direction: column;
            gap: 5px;
        }

        .legend-item {
            font-size: 10px;
        }

        .bus-roof-lights {
            padding: 3px 5px;
        }

        .roof-light {
            width: 6px;
            height: 6px;
        }

        .bus-front, .bus-back {
            height: 25px;
            font-size: 10px;
        }

        .bus-front::before, .bus-front::after {
            width: 20px;
            height: 10px;
        }
    }

    /* Animation for seat selection */
    @keyframes seatPop {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    .seat.available:active {
        animation: seatPop 0.3s ease;
    }

    /* Tooltip styles */
    .seat-tooltip {
        position: absolute;
        top: -30px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #1f2937;
        color: white;
        padding: 5px 8px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s ease;
        z-index: 100;
        pointer-events: none;
    }

    .seat-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #1f2937 transparent transparent transparent;
    }

    .seat:hover .seat-tooltip {
        opacity: 1;
        visibility: visible;
        top: -35px;
    }

    /* Error message */
    .error-message {
        position: fixed;
        top: 10px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #ef4444;
        color: white;
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 14px;
        z-index: 9999;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    /* Pulse animation for selected seats */
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.4);
        }
        70% {
            box-shadow: 0 0 0 6px rgba(22, 163, 74, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(22, 163, 74, 0);
        }
    }

    .seat.selected {
        animation: pulse 1.5s infinite;
    }

    /* Shake animation for error feedback */
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }

    .seat.shake {
        animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing seat selection');

    // Get passenger count from URL or default to 1
    const urlParams = new URLSearchParams(window.location.search);
    const passengerParam = urlParams.get('passengers');
    const passengerCount = passengerParam ? Math.max(1, parseInt(passengerParam) || 1) : 1;

    // Update all UI elements to show the current passenger count
    document.querySelectorAll('.text-gray-700, .bg-primary-100.text-primary-800').forEach(el => {
        if (el.textContent.includes('passenger')) {
            el.textContent = `${passengerCount} passenger${passengerCount > 1 ? 's' : ''}`;
        }
    });

    // Set hidden fields properly
    document.getElementById('max_seats').value = passengerCount;
    document.getElementById('passengers_input').value = passengerCount;
    console.log('Set passenger count to:', passengerCount);

    // UI elements
    const continueBtn = document.getElementById('continue-btn');
    const selectionInfo = document.getElementById('selection-info');
    const selectionText = document.getElementById('selection-text');
    const selectedSeatsDisplay = document.getElementById('selected_seats_display');
    const selectedSeatsInput = document.getElementById('selected_seats');
    const totalAmountElement = document.getElementById('total_amount');
    const totalAmountInput = document.getElementById('total_amount_input');
    const seatPrice = parseFloat(document.getElementById('seat_price').value);

    // Selected seats tracking
    let selectedSeats = [];

    // Show error message function
    function showError(message) {
        console.log('Error:', message);
        // Remove any existing error messages
        const existingErrors = document.querySelectorAll('.error-message');
        existingErrors.forEach(error => error.remove());

        // Create new error message
        const errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.textContent = message;
        document.body.appendChild(errorElement);

        // Add animation
        setTimeout(() => {
            errorElement.classList.add('show');
        }, 10);

        // Remove after 3 seconds
        setTimeout(() => {
            errorElement.classList.remove('show');
            setTimeout(() => {
                errorElement.remove();
            }, 300);
        }, 3000);
    }

    // Show success message function
    function showSuccess(message) {
        // Remove any existing messages
        const existingMessages = document.querySelectorAll('.success-message');
        existingMessages.forEach(msg => msg.remove());

        // Create new success message
        const messageElement = document.createElement('div');
        messageElement.className = 'success-message';
        messageElement.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${message}`;
        document.body.appendChild(messageElement);

        // Add animation
        setTimeout(() => {
            messageElement.classList.add('show');
        }, 10);

        // Remove after 3 seconds
        setTimeout(() => {
            messageElement.classList.remove('show');
            setTimeout(() => {
                messageElement.remove();
            }, 300);
        }, 3000);
    }

    // First scan for any seats that might already be selected (in case of page refresh)
    function scanForSelectedSeats() {
        const preSelectedSeats = document.querySelectorAll('.seat.selected');
        if (preSelectedSeats.length > 0) {
            console.log('Found pre-selected seats:', preSelectedSeats.length);
            preSelectedSeats.forEach(seat => {
                const seatNumber = seat.getAttribute('data-seat');
                if (seatNumber && !selectedSeats.includes(seatNumber)) {
                    selectedSeats.push(seatNumber);
                    console.log('Added pre-selected seat:', seatNumber);
                }
            });
        }
    }

    // Scan for any seats that might be selected
    scanForSelectedSeats();

    // Find all seats and attach click handlers
    const allSeats = document.querySelectorAll('.seat');
    console.log('Total seats found:', allSeats.length);

    // Add hover effect to show seat details
    function createSeatDetailsPopup(seat) {
        const seatNumber = seat.getAttribute('data-seat');
        if (!seatNumber) return;

        const seatType = seat.classList.contains('window') ? 'Window Seat' : 'Aisle Seat';
        const seatStatus = seat.classList.contains('booked') ? 'Booked' :
                          (seat.classList.contains('selected') ? 'Selected' : 'Available');

        const popup = document.createElement('div');
        popup.className = 'seat-details-popup';
        popup.innerHTML = `
            <div class="seat-number">${seatNumber}</div>
            <div class="seat-type">${seatType}</div>
            <div class="seat-status ${seatStatus.toLowerCase()}">${seatStatus}</div>
        `;

        return popup;
    }

    // Add seat details popup styles
    const style = document.createElement('style');
    style.textContent = `
        .seat-details-popup {
            position: absolute;
            top: -70px;
            left: 50%;
            transform: translateX(-50%) scale(0.9);
            background-color: white;
            border-radius: 8px;
            padding: 8px 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 100;
            opacity: 0;
            transition: all 0.2s ease;
            pointer-events: none;
            min-width: 120px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }

        .seat-details-popup::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -8px;
            border-width: 8px;
            border-style: solid;
            border-color: white transparent transparent transparent;
        }

        .seat-details-popup .seat-number {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .seat-details-popup .seat-type {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .seat-details-popup .seat-status {
            font-size: 11px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 12px;
            display: inline-block;
        }

        .seat-details-popup .seat-status.available {
            background-color: #dcfce7;
            color: #166534;
        }

        .seat-details-popup .seat-status.selected {
            background-color: #16a34a;
            color: white;
        }

        .seat-details-popup .seat-status.booked {
            background-color: #f3f4f6;
            color: #9ca3af;
        }

        .seat.hover .seat-details-popup {
            opacity: 1;
            transform: translateX(-50%) scale(1);
            top: -80px;
        }

        .success-message, .error-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .success-message {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #16a34a;
        }

        .error-message {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #ef4444;
        }

        .success-message.show, .error-message.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    `;
    document.head.appendChild(style);

    allSeats.forEach(seat => {
        // Skip seats that are already booked or not real seats (aisles)
        if (seat.classList.contains('booked') || seat.classList.contains('aisle')) {
            // For booked seats, still add the popup but no click handler
            if (seat.classList.contains('booked')) {
                const popup = createSeatDetailsPopup(seat);
                if (popup) {
                    seat.appendChild(popup);

                    seat.addEventListener('mouseenter', () => {
                        seat.classList.add('hover');
                    });

                    seat.addEventListener('mouseleave', () => {
                        seat.classList.remove('hover');
                    });
                }
            }
            return;
        }

        // Make sure available seats are properly marked
        if (!seat.classList.contains('available')) {
            seat.classList.add('available');
        }

        // Add seat details popup
        const popup = createSeatDetailsPopup(seat);
        if (popup) {
            seat.appendChild(popup);

            seat.addEventListener('mouseenter', () => {
                seat.classList.add('hover');
            });

            seat.addEventListener('mouseleave', () => {
                seat.classList.remove('hover');
            });
        }

        // Add click handler
        seat.addEventListener('click', function() {
            const seatNumber = seat.getAttribute('data-seat');
            const action = selectedSeats.includes(seatNumber) ? 'deselect' : 'select';

            // Optimistically update UI
            if (action === 'select' && selectedSeats.length >= passengerCount) {
                showError(`Maximum ${passengerCount} seat(s) allowed`);
                seat.classList.add('shake');
                setTimeout(() => seat.classList.remove('shake'), 500);
                return;
            }

            // Send request to server to reserve/unreserve the seat
            fetch('create_pending_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    schedule_id: <?php echo $schedule_id; ?>,
                    seat_number: seatNumber,
                    action: action
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (action === 'select') {
                        selectedSeats.push(seatNumber);
                        seat.classList.remove('available');
                        seat.classList.add('selected');
                        showSuccess(data.message);
                    } else {
                        selectedSeats = selectedSeats.filter(s => s !== seatNumber);
                        seat.classList.remove('selected');
                        seat.classList.add('available');
                        showSuccess(data.message);
                    }
                } else {
                    showError(data.message);
                    // Revert UI change if server-side failed
                    if (action === 'select') {
                        seat.classList.remove('selected');
                        seat.classList.add('available');
                    } else {
                        seat.classList.remove('available');
                        seat.classList.add('selected');
                    }
                }
                updateSelectionDisplay();
            })
            .catch(error => {
                showError('An error occurred. Please try again.');
                console.error('Error:', error);
                // Revert UI changes on error
                if (action === 'select') {
                    seat.classList.remove('selected');
                    seat.classList.add('available');
                } else {
                    seat.classList.remove('available');
                    seat.classList.add('selected');
                }
                updateSelectionDisplay();
            });
        });
    });

    // Update all UI based on selected seats
    function updateSelectionDisplay() {
        // Update the button state
        if (selectedSeats.length === passengerCount) {
            continueBtn.disabled = false;
            continueBtn.classList.add('animate-pulse');
            selectionInfo.classList.remove('hidden');
            selectionText.innerHTML = `<i class="fas fa-check-circle text-green-500 mr-2"></i> You've selected all required seats! Click continue to proceed.`;
        } else {
            continueBtn.disabled = true;
            continueBtn.classList.remove('animate-pulse');

            if (selectedSeats.length > 0) {
                const remaining = passengerCount - selectedSeats.length;
                selectionInfo.classList.remove('hidden');
                selectionText.innerHTML = `<i class="fas fa-info-circle text-blue-500 mr-2"></i> Please select ${remaining} more seat${remaining > 1 ? 's' : ''} to continue.`;
            } else {
                selectionInfo.classList.add('hidden');
            }
        }

        // Update selected seats display
        if (selectedSeats.length > 0) {
            // Sort seats for better display
            const sortedSeats = [...selectedSeats].sort((a, b) => {
                const aLetter = a.charAt(0);
                const bLetter = b.charAt(0);
                const aNumber = parseInt(a.substring(1));
                const bNumber = parseInt(b.substring(1));

                if (aNumber !== bNumber) {
                    return aNumber - bNumber;
                }
                return aLetter.localeCompare(bLetter);
            });

            selectedSeatsDisplay.textContent = sortedSeats.join(', ');
            selectedSeatsInput.value = sortedSeats.join(',');

            const totalAmount = selectedSeats.length * seatPrice;
            totalAmountElement.textContent = 'KES ' + totalAmount.toFixed(2);
            totalAmountInput.value = totalAmount;

            // Add animation to total amount
            totalAmountElement.classList.add('animate-pulse');
            setTimeout(() => {
                totalAmountElement.classList.remove('animate-pulse');
            }, 1000);
        } else {
            selectedSeatsDisplay.textContent = 'None';
            selectedSeatsInput.value = '';
            totalAmountElement.textContent = 'KES 0.00';
            totalAmountInput.value = 0;
        }

        console.log('Updated selection display, seats:', selectedSeats);
    }

    // Initialize the display
    updateSelectionDisplay();

    // Add keyboard navigation for accessibility
    let currentFocusedSeat = null;

    document.addEventListener('keydown', function(e) {
        // Only handle keyboard navigation if we have a focused seat
        if (!currentFocusedSeat && e.key === 'Tab') {
            // Find the first available seat and focus it
            const firstAvailableSeat = document.querySelector('.seat.available');
            if (firstAvailableSeat) {
                firstAvailableSeat.focus();
                currentFocusedSeat = firstAvailableSeat;
                currentFocusedSeat.classList.add('keyboard-focus');
            }
            return;
        }

        if (!currentFocusedSeat) return;

        // Handle arrow keys
        if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Enter', 'Space'].includes(e.key)) {
            e.preventDefault(); // Prevent scrolling

            if (e.key === 'Enter' || e.key === ' ') {
                // Simulate click on the focused seat
                currentFocusedSeat.click();
                return;
            }

            // Remove focus from current seat
            currentFocusedSeat.classList.remove('keyboard-focus');

            // Get the current seat's position
            const seatRow = currentFocusedSeat.closest('.seat-row');
            const allRows = Array.from(document.querySelectorAll('.seat-row'));
            const rowIndex = allRows.indexOf(seatRow);

            // Get all seats in the current row
            const seatsInRow = Array.from(seatRow.querySelectorAll('.seat:not(.aisle)'));
            const seatIndex = seatsInRow.indexOf(currentFocusedSeat);

            let nextSeat = null;

            // Navigate based on arrow key
            switch (e.key) {
                case 'ArrowUp':
                    if (rowIndex > 0) {
                        const prevRow = allRows[rowIndex - 1];
                        const seatsInPrevRow = Array.from(prevRow.querySelectorAll('.seat:not(.aisle)'));
                        nextSeat = seatsInPrevRow[seatIndex] || seatsInPrevRow[0];
                    }
                    break;
                case 'ArrowDown':
                    if (rowIndex < allRows.length - 1) {
                        const nextRow = allRows[rowIndex + 1];
                        const seatsInNextRow = Array.from(nextRow.querySelectorAll('.seat:not(.aisle)'));
                        nextSeat = seatsInNextRow[seatIndex] || seatsInNextRow[0];
                    }
                    break;
                case 'ArrowLeft':
                    nextSeat = seatsInRow[seatIndex - 1] || seatsInRow[seatsInRow.length - 1];
                    break;
                case 'ArrowRight':
                    nextSeat = seatsInRow[seatIndex + 1] || seatsInRow[0];
                    break;
            }

            // Focus the next seat if found
            if (nextSeat) {
                nextSeat.focus();
                nextSeat.classList.add('keyboard-focus');
                currentFocusedSeat = nextSeat;
            } else {
                // If no next seat, refocus the current one
                currentFocusedSeat.classList.add('keyboard-focus');
            }
        }
    });

    // Add focus/blur handlers to all seats
    allSeats.forEach(seat => {
        if (seat.classList.contains('aisle')) return;

        seat.setAttribute('tabindex', '0'); // Make seat focusable

        seat.addEventListener('focus', function() {
            currentFocusedSeat = seat;
            seat.classList.add('keyboard-focus');
        });

        seat.addEventListener('blur', function() {
            seat.classList.remove('keyboard-focus');
            if (currentFocusedSeat === seat) {
                currentFocusedSeat = null;
            }
        });
    });

    // Add style for keyboard focus
    const keyboardFocusStyle = document.createElement('style');
    keyboardFocusStyle.textContent = `
        .seat.keyboard-focus {
            outline: 3px solid #3b82f6;
            outline-offset: 2px;
            z-index: 30;
        }
    `;
    document.head.appendChild(keyboardFocusStyle);
});
</script>

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>
