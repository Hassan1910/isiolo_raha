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
    // Set message
    setFlashMessage("error", "Please login to create a group booking.");

    // Redirect to login page
    header("Location: login.php");
    exit();
}

// Initialize variables
$schedule_id = isset($_GET['schedule_id']) ? intval($_GET['schedule_id']) : "";
$passengers = isset($_GET['passengers']) ? intval($_GET['passengers']) : 5; // Default to 5 passengers for group booking
$error = "";
$schedules = [];
$booked_seats = [];
$schedule = null;

// Get all active schedules for the dropdown
$sql = "SELECT s.id, s.departure_time, s.fare,
        r.origin, r.destination,
        b.name AS bus_name, b.registration_number, b.capacity
        FROM schedules s
        JOIN routes r ON s.route_id = r.id
        JOIN buses b ON s.bus_id = b.id
        WHERE s.status = 'scheduled' AND s.departure_time > NOW()
        ORDER BY s.departure_time ASC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
}

// If schedule_id is provided, get schedule details
if ($schedule_id) {
    $sql = "SELECT s.id, s.departure_time, s.arrival_time, s.fare, s.status,
            r.origin, r.destination, r.distance,
            b.name AS bus_name, b.type AS bus_type, b.capacity
            FROM schedules s
            JOIN routes r ON s.route_id = r.id
            JOIN buses b ON s.bus_id = b.id
            WHERE s.id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $schedule_id);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $schedule = $result->fetch_assoc();
            } else {
                $error = "Schedule not found.";
            }
        } else {
            $error = "Something went wrong. Please try again later.";
        }

        // Close statement
        $stmt->close();
    }

    // Get booked seats for this schedule
    $sql = "SELECT seat_number FROM bookings WHERE schedule_id = ?";

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

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate schedule_id
    if (empty($_POST["schedule_id"])) {
        $error = "Please select a schedule.";
    } else {
        $schedule_id = intval($_POST["schedule_id"]);
    }

    // Validate selected seats
    if (empty($_POST["selected_seats"])) {
        $error = "Please select at least one seat.";
    } else {
        $selected_seats = $_POST["selected_seats"];
    }

    // Validate group name
    if (empty($_POST["group_name"])) {
        $error = "Please enter a group name.";
    } else {
        $group_name = $_POST["group_name"];
    }

    // Validate contact person
    if (empty($_POST["contact_person"])) {
        $error = "Please enter contact person name.";
    } else {
        $contact_person = $_POST["contact_person"];
    }

    // Validate contact phone
    if (empty($_POST["contact_phone"])) {
        $error = "Please enter contact phone number.";
    } else {
        $contact_phone = $_POST["contact_phone"];
    }

    // Optional contact email
    $contact_email = $_POST["contact_email"] ?? "";

    // Validate total passengers
    if (empty($_POST["total_passengers"]) || intval($_POST["total_passengers"]) < 1) {
        $error = "Please enter a valid number of passengers.";
    } else {
        $total_passengers = intval($_POST["total_passengers"]);
    }

    // Optional notes
    $notes = $_POST["notes"] ?? "";

    // If no errors, store booking data in session
    if (empty($error)) {
        // Store group booking data in session
        $_SESSION['group_booking_data'] = [
            'schedule_id' => $schedule_id,
            'selected_seats' => $selected_seats,
            'group_name' => $group_name,
            'contact_person' => $contact_person,
            'contact_phone' => $contact_phone,
            'contact_email' => $contact_email,
            'total_passengers' => $total_passengers,
            'notes' => $notes,
            'total_amount' => $_POST["total_amount"]
        ];

        // Redirect to group passenger details page
        header("Location: group_passenger_details.php");
        exit();
    }
}

// Set page title
$page_title = "Group Booking";

// Include header
require_once 'includes/templates/header.php';
?>

<div class="bg-gradient-to-b from-gray-50 to-gray-100 py-8">
    <div class="container mx-auto px-4">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-users text-primary-600 mr-3"></i> Group Booking
                </h1>
                <p class="text-gray-600 mt-2">Book multiple seats for your group in one transaction</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="index.php" class="text-primary-600 hover:text-primary-700 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Home
                </a>
            </div>
        </div>

        <!-- Booking Progress -->
        <div class="mb-8">
            <div class="flex items-center justify-between max-w-3xl mx-auto">
                <!-- Step 1 -->
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full bg-primary-600 text-white flex items-center justify-center font-bold">
                        1
                    </div>
                    <p class="text-sm font-medium mt-2 text-primary-600">Select Schedule & Seats</p>
                </div>

                <!-- Connector -->
                <div class="h-1 flex-1 bg-gray-300 mx-2">
                </div>

                <!-- Step 2 -->
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold">
                        2
                    </div>
                    <p class="text-sm font-medium mt-2 text-gray-500">Passenger Details</p>
                </div>

                <!-- Connector -->
                <div class="h-1 flex-1 bg-gray-300 mx-2">
                </div>

                <!-- Step 3 -->
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold">
                        3
                    </div>
                    <p class="text-sm font-medium mt-2 text-gray-500">Payment</p>
                </div>

                <!-- Connector -->
                <div class="h-1 flex-1 bg-gray-300 mx-2">
                </div>

                <!-- Step 4 -->
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold">
                        4
                    </div>
                    <p class="text-sm font-medium mt-2 text-gray-500">Confirmation</p>
                </div>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md shadow-sm" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3 text-lg"></i>
                    <p><?php echo $error; ?></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-gray-100">
            <div class="border-b border-gray-200 pb-4 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-ticket-alt text-primary-500 mr-2"></i> Create a Group Booking
                </h2>
                <p class="text-gray-600 mt-2">Book multiple seats for your group in one transaction. Fill in the details below to get started.</p>
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="group-booking-form">
                <!-- Schedule Selection -->
                <div class="mb-8 bg-blue-50 p-5 rounded-lg border border-blue-100">
                    <h3 class="text-lg font-semibold mb-4 text-blue-800 flex items-center">
                        <i class="fas fa-calendar-alt text-blue-600 mr-2"></i> Select Your Trip Schedule
                    </h3>

                    <!-- Search/Filter for schedules (on larger screens) -->
                    <div class="mb-4 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="schedule-search" placeholder="Search by destination, date or bus..."
                            class="pl-10 pr-4 py-2 w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    </div>

                    <div class="relative">
                        <label for="schedule_id" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-bus text-gray-500 mr-1"></i> Available Schedules <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="schedule_id" id="schedule_id"
                                class="form-select w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 py-3"
                                required>
                                <option value="">-- Select a Schedule --</option>
                                <?php foreach ($schedules as $s): ?>
                                    <option value="<?php echo $s['id']; ?>" <?php echo ($schedule_id == $s['id']) ? 'selected' : ''; ?>
                                        data-origin="<?php echo $s['origin']; ?>"
                                        data-destination="<?php echo $s['destination']; ?>"
                                        data-date="<?php echo date('d M Y', strtotime($s['departure_time'])); ?>"
                                        data-bus="<?php echo $s['bus_name']; ?>">
                                        <strong><?php echo date('d M Y, h:i A', strtotime($s['departure_time'])); ?></strong> -
                                        <?php echo $s['origin']; ?> to <?php echo $s['destination']; ?> -
                                        <?php echo $s['bus_name']; ?> (<?php echo $s['registration_number']; ?>) -
                                        KES <?php echo number_format($s['fare'], 2); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Select the schedule for your group booking</p>
                    </div>

                    <?php if ($schedule): ?>
                    <!-- Selected Schedule Summary -->
                    <div class="mt-4 p-4 bg-white rounded-lg border border-green-200 shadow-sm">
                        <h4 class="font-medium text-green-800 mb-2 flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-2"></i> Selected Schedule
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Route:</p>
                                <p class="font-medium"><?php echo $schedule['origin']; ?> to <?php echo $schedule['destination']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Departure:</p>
                                <p class="font-medium"><?php echo date('d M Y, h:i A', strtotime($schedule['departure_time'])); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Bus:</p>
                                <p class="font-medium"><?php echo $schedule['bus_name']; ?> (<?php echo $schedule['bus_type']; ?>)</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Fare per Seat:</p>
                                <p class="font-medium">KES <?php echo number_format($schedule['fare'], 2); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Group Information -->
                <div class="mb-8 bg-purple-50 p-5 rounded-lg border border-purple-100">
                    <h3 class="text-lg font-semibold mb-4 text-purple-800 flex items-center">
                        <i class="fas fa-users text-purple-600 mr-2"></i> Group Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <!-- Group Name -->
                        <div class="relative">
                            <label for="group_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Group Name <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-users-class text-gray-400"></i>
                                </div>
                                <input type="text" name="group_name" id="group_name"
                                    class="pl-10 w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50"
                                    placeholder="e.g. School Trip, Family Reunion" required>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Enter a name for your group</p>
                        </div>

                        <!-- Total Passengers -->
                        <div class="relative">
                            <label for="total_passengers" class="block text-sm font-medium text-gray-700 mb-1">
                                Number of Passengers <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user-friends text-gray-400"></i>
                                </div>
                                <input type="number" name="total_passengers" id="total_passengers"
                                    class="pl-10 w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50"
                                    min="2" max="40" value="<?php echo $passengers; ?>" required>
                                <div class="absolute inset-y-0 right-0 flex items-center">
                                    <button type="button" id="decrease-passengers" class="h-full px-2 text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <div class="h-full w-px bg-gray-300"></div>
                                    <button type="button" id="increase-passengers" class="h-full px-2 text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Minimum 2, maximum 40 passengers</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <!-- Contact Person -->
                        <div class="relative">
                            <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-1">
                                Contact Person <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" name="contact_person" id="contact_person"
                                    class="pl-10 w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50"
                                    placeholder="Full name of contact person" required>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Person responsible for the group</p>
                        </div>

                        <!-- Contact Phone -->
                        <div class="relative">
                            <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1">
                                Contact Phone <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input type="tel" name="contact_phone" id="contact_phone"
                                    class="pl-10 w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50"
                                    placeholder="e.g. 0700000000" required>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Phone number for booking confirmation</p>
                        </div>
                    </div>

                    <!-- Contact Email -->
                    <div class="mb-4 relative">
                        <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1">
                            Contact Email <span class="text-gray-500">(Optional)</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input type="email" name="contact_email" id="contact_email"
                                class="pl-10 w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50"
                                placeholder="email@example.com">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">For sending booking confirmation and updates</p>
                    </div>

                    <!-- Notes -->
                    <div class="relative">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Special Requirements <span class="text-gray-500">(Optional)</span>
                        </label>
                        <div class="relative">
                            <div class="absolute top-3 left-3 flex items-start pointer-events-none">
                                <i class="fas fa-sticky-note text-gray-400"></i>
                            </div>
                            <textarea name="notes" id="notes"
                                class="pl-10 w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50"
                                rows="3" placeholder="Any special requirements or additional information"></textarea>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Include any special requirements for your group</p>
                    </div>
                </div>

                <?php if ($schedule): ?>
                    <!-- Seat Selection -->
                    <div class="mb-8 bg-green-50 p-5 rounded-lg border border-green-100">
                        <h3 class="text-lg font-semibold mb-4 text-green-800 flex items-center">
                            <i class="fas fa-chair text-green-600 mr-2"></i> Select Seats
                        </h3>

                        <!-- Seat Selection Instructions -->
                        <div class="mb-4 p-3 bg-white rounded-lg border border-gray-200 shadow-sm">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 bg-blue-100 rounded-full p-2 mr-3">
                                    <i class="fas fa-info-circle text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-800 mb-1">How to Select Seats</h4>
                                    <p class="text-sm text-gray-600">Click on available seats to select them for your group. You need to select <span id="required-seats" class="font-semibold text-primary-600"><?php echo $passengers; ?></span> seats in total.</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 text-xs font-medium text-gray-800">
                                            <span class="w-3 h-3 bg-green-500 rounded-sm mr-1"></span> Selected
                                        </span>
                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 text-xs font-medium text-gray-800">
                                            <span class="w-3 h-3 bg-blue-300 rounded-sm mr-1"></span> Window
                                        </span>
                                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 text-xs font-medium text-gray-800">
                                            <span class="w-3 h-3 bg-gray-300 rounded-sm mr-1"></span> Booked
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Bus Layout will be loaded here -->
                            <div class="lg:col-span-2">
                                <div id="bus-layout" class="mb-4 bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                    <!-- Bus Layout Component -->
                                    <?php include 'includes/components/bus_layout.php'; ?>
                                </div>
                            </div>

                            <!-- Selected Seats Summary -->
                            <div class="lg:col-span-1">
                                <div class="sticky top-4 bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                                    <div class="bg-primary-600 text-white p-4">
                                        <h4 class="font-semibold flex items-center">
                                            <i class="fas fa-receipt mr-2"></i> Booking Summary
                                        </h4>
                                    </div>

                                    <div class="p-4">
                                        <!-- Selection Progress -->
                                        <div class="mb-4">
                                            <div class="flex justify-between text-sm mb-1">
                                                <span>Seat Selection Progress</span>
                                                <span id="selection-progress">0/<?php echo $passengers; ?></span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div id="progress-bar" class="bg-primary-600 h-2.5 rounded-full" style="width: 0%"></div>
                                            </div>
                                        </div>

                                        <!-- Selected Seats -->
                                        <div class="mb-4">
                                            <p class="text-sm font-medium text-gray-700 mb-2">Selected Seats:</p>
                                            <div id="selected-seats-display" class="min-h-[40px] p-2 bg-gray-50 rounded border border-gray-200 text-primary-600 font-medium">
                                                None
                                            </div>
                                            <input type="hidden" name="selected_seats" id="selected_seats" value="">
                                        </div>

                                        <!-- Price Breakdown -->
                                        <div class="border-t border-gray-200 pt-4 mb-4">
                                            <div class="flex justify-between mb-2">
                                                <span class="text-gray-600">Fare per seat:</span>
                                                <span>KES <?php echo number_format($schedule['fare'], 2); ?></span>
                                            </div>
                                            <div class="flex justify-between mb-2">
                                                <span class="text-gray-600">Number of seats:</span>
                                                <span id="seat-count">0</span>
                                            </div>
                                            <div class="flex justify-between font-bold text-lg border-t border-gray-200 pt-2 mt-2">
                                                <span>Total Amount:</span>
                                                <span id="total-amount-display" class="text-primary-600">KES 0.00</span>
                                                <input type="hidden" name="total_amount" id="total_amount" value="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Submit Button -->
                <div class="flex justify-center mt-8">
                    <button type="submit" class="btn-primary text-lg px-8 py-3 rounded-lg flex items-center transition-transform transform hover:scale-105">
                        <i class="fas fa-arrow-right mr-2"></i> Continue to Passenger Details
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Section -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-gray-100">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-question-circle text-primary-500 mr-2"></i> Need Help?
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-blue-50 rounded-lg border border-blue-100">
                    <h4 class="font-medium text-blue-800 mb-2">Group Booking Benefits</h4>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                            <span>Book multiple seats in one transaction</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                            <span>Keep your group together during travel</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                            <span>Manage all tickets from one booking reference</span>
                        </li>
                    </ul>
                </div>
                <div class="p-4 bg-purple-50 rounded-lg border border-purple-100">
                    <h4 class="font-medium text-purple-800 mb-2">Booking Process</h4>
                    <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                        <li>Select your schedule and seats</li>
                        <li>Enter passenger details</li>
                        <li>Complete payment</li>
                        <li>Receive booking confirmation</li>
                    </ol>
                    <p class="mt-2 text-xs text-gray-500">You can print tickets for all passengers after booking</p>
                </div>
                <div class="p-4 bg-amber-50 rounded-lg border border-amber-100">
                    <h4 class="font-medium text-amber-800 mb-2">Contact Support</h4>
                    <p class="text-sm text-gray-600 mb-2">Need assistance with your group booking?</p>
                    <div class="flex items-center text-sm mb-2">
                        <i class="fas fa-phone text-primary-500 mr-2"></i>
                        <span>+254 700 000 000</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <i class="fas fa-envelope text-primary-500 mr-2"></i>
                        <span>support@isioloraha.com</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden input for seat price -->
<input type="hidden" id="seat_price" value="<?php echo $schedule ? $schedule['fare'] : 0; ?>">

<script>
    // Initialize variables
    const scheduleSelect = document.getElementById('schedule_id');
    const scheduleSearch = document.getElementById('schedule-search');
    const seatPrice = parseFloat(document.getElementById('seat_price').value) || 0;
    const passengerCountInput = document.getElementById('total_passengers');
    const increasePassengersBtn = document.getElementById('increase-passengers');
    const decreasePassengersBtn = document.getElementById('decrease-passengers');
    const requiredSeatsElement = document.getElementById('required-seats');
    const selectionProgressElement = document.getElementById('selection-progress');
    const progressBarElement = document.getElementById('progress-bar');
    const seatCountElement = document.getElementById('seat-count');
    let selectedSeats = [];

    // Handle schedule search/filter
    if (scheduleSearch && scheduleSelect) {
        scheduleSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const options = scheduleSelect.options;

            for (let i = 0; i < options.length; i++) {
                const option = options[i];
                if (i === 0) continue; // Skip the placeholder option

                const origin = option.getAttribute('data-origin')?.toLowerCase() || '';
                const destination = option.getAttribute('data-destination')?.toLowerCase() || '';
                const date = option.getAttribute('data-date')?.toLowerCase() || '';
                const bus = option.getAttribute('data-bus')?.toLowerCase() || '';
                const text = option.text.toLowerCase();

                if (
                    text.includes(searchTerm) ||
                    origin.includes(searchTerm) ||
                    destination.includes(searchTerm) ||
                    date.includes(searchTerm) ||
                    bus.includes(searchTerm)
                ) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            }
        });
    }

    // Handle schedule selection change
    if (scheduleSelect) {
        scheduleSelect.addEventListener('change', function() {
            const scheduleId = this.value;
            if (scheduleId) {
                // Show loading indicator
                document.body.classList.add('loading');

                // Redirect with a slight delay to show loading
                setTimeout(() => {
                    window.location.href = 'group_booking.php?schedule_id=' + scheduleId + '&passengers=' + passengerCountInput.value;
                }, 300);
            }
        });
    }

    // Handle passenger count buttons
    if (increasePassengersBtn && decreasePassengersBtn && passengerCountInput) {
        increasePassengersBtn.addEventListener('click', function() {
            const currentValue = parseInt(passengerCountInput.value) || 0;
            const maxValue = parseInt(passengerCountInput.getAttribute('max')) || 40;

            if (currentValue < maxValue) {
                passengerCountInput.value = currentValue + 1;

                // Trigger change event
                const event = new Event('change');
                passengerCountInput.dispatchEvent(event);
            }
        });

        decreasePassengersBtn.addEventListener('click', function() {
            const currentValue = parseInt(passengerCountInput.value) || 0;
            const minValue = parseInt(passengerCountInput.getAttribute('min')) || 2;

            if (currentValue > minValue) {
                passengerCountInput.value = currentValue - 1;

                // Trigger change event
                const event = new Event('change');
                passengerCountInput.dispatchEvent(event);
            }
        });
    }

    // Handle passenger count change
    if (passengerCountInput) {
        passengerCountInput.addEventListener('change', function() {
            const scheduleId = scheduleSelect.value;
            if (scheduleId) {
                // Update required seats display if it exists
                if (requiredSeatsElement) {
                    requiredSeatsElement.textContent = this.value;
                }

                // Show loading indicator
                document.body.classList.add('loading');

                // Redirect with a slight delay to show loading
                setTimeout(() => {
                    window.location.href = 'group_booking.php?schedule_id=' + scheduleId + '&passengers=' + this.value;
                }, 300);
            }
        });
    }

    // Initialize seat selection if the bus layout exists
    document.addEventListener('DOMContentLoaded', function() {
        initGroupSeatSelection();

        // Add animation to the progress steps
        document.querySelectorAll('.booking-step').forEach(step => {
            step.classList.add('animate-pulse');
            setTimeout(() => {
                step.classList.remove('animate-pulse');
            }, 1000);
        });
    });

    function initGroupSeatSelection() {
        const allSeats = document.querySelectorAll('.seat');
        const selectedSeatsDisplay = document.getElementById('selected-seats-display');
        const selectedSeatsInput = document.getElementById('selected_seats');
        const totalAmountElement = document.getElementById('total-amount-display');
        const totalAmountInput = document.getElementById('total_amount');
        const passengerCount = parseInt(passengerCountInput.value) || 5;

        if (!allSeats.length) return;

        // Add tooltips to seats
        allSeats.forEach(seat => {
            // Skip seats that are already booked or not real seats (aisles)
            if (seat.classList.contains('booked') || seat.classList.contains('aisle')) {
                if (seat.classList.contains('booked')) {
                    seat.setAttribute('title', 'This seat is already booked');
                }
                return;
            }

            const seatNumber = seat.getAttribute('data-seat');
            seat.setAttribute('title', `Seat ${seatNumber}`);

            // Add hover effect
            seat.addEventListener('mouseenter', function() {
                if (!seat.classList.contains('selected') && !seat.classList.contains('booked')) {
                    seat.classList.add('hover:scale-110', 'hover:shadow-md', 'transition-transform');
                }
            });

            // Add click handler
            seat.addEventListener('click', function() {
                const seatNumber = seat.getAttribute('data-seat');

                // If already selected, deselect it
                if (selectedSeats.includes(seatNumber)) {
                    // Remove from selection
                    selectedSeats = selectedSeats.filter(s => s !== seatNumber);

                    // Update UI
                    seat.classList.remove('selected');
                    if (seat.classList.contains('window')) {
                        seat.classList.add('window', 'available');
                    } else {
                        seat.classList.add('available');
                    }

                    // Add subtle animation
                    seat.animate([
                        { transform: 'scale(1.1)' },
                        { transform: 'scale(1)' }
                    ], {
                        duration: 200,
                        easing: 'ease-out'
                    });
                }
                // If we can select more seats
                else if (selectedSeats.length < passengerCount) {
                    // Add to selection
                    selectedSeats.push(seatNumber);

                    // Update UI
                    seat.classList.remove('available');
                    seat.classList.add('selected');

                    // Add subtle animation
                    seat.animate([
                        { transform: 'scale(0.9)' },
                        { transform: 'scale(1.1)' },
                        { transform: 'scale(1)' }
                    ], {
                        duration: 300,
                        easing: 'ease-out'
                    });
                }
                else {
                    // Show a nicer notification that they can't select more seats
                    const notification = document.createElement('div');
                    notification.className = 'fixed top-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md z-50 animate-fade-in-down';
                    notification.innerHTML = `
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-3 text-lg"></i>
                            <p>You can only select ${passengerCount} seats for this group booking. Please deselect a seat first if you want to change your selection.</p>
                        </div>
                    `;
                    document.body.appendChild(notification);

                    // Remove notification after 4 seconds
                    setTimeout(() => {
                        notification.classList.add('animate-fade-out');
                        setTimeout(() => {
                            document.body.removeChild(notification);
                        }, 300);
                    }, 4000);
                }

                // Update display
                updateSelectionDisplay();
            });
        });

        function updateSelectionDisplay() {
            // Sort seats for better display
            const sortedSeats = [...selectedSeats].sort();

            // Update selection progress
            if (selectionProgressElement) {
                selectionProgressElement.textContent = `${selectedSeats.length}/${passengerCount}`;
            }

            // Update progress bar
            if (progressBarElement) {
                const progressPercentage = (selectedSeats.length / passengerCount) * 100;
                progressBarElement.style.width = `${progressPercentage}%`;

                // Change color based on progress
                if (progressPercentage === 100) {
                    progressBarElement.classList.remove('bg-primary-600');
                    progressBarElement.classList.add('bg-green-500');
                } else {
                    progressBarElement.classList.add('bg-primary-600');
                    progressBarElement.classList.remove('bg-green-500');
                }
            }

            // Update seat count
            if (seatCountElement) {
                seatCountElement.textContent = selectedSeats.length;
            }

            if (sortedSeats.length > 0) {
                // Create a more visual representation of selected seats
                let seatsHTML = '';
                sortedSeats.forEach(seat => {
                    seatsHTML += `<span class="inline-block bg-primary-100 text-primary-800 rounded-md px-2 py-1 text-sm font-medium m-1">${seat}</span>`;
                });
                selectedSeatsDisplay.innerHTML = seatsHTML;
                selectedSeatsInput.value = sortedSeats.join(',');

                const totalAmount = selectedSeats.length * seatPrice;
                totalAmountElement.textContent = 'KES ' + totalAmount.toFixed(2);
                totalAmountInput.value = totalAmount;
            } else {
                selectedSeatsDisplay.innerHTML = '<span class="text-gray-500">None selected</span>';
                selectedSeatsInput.value = '';
                totalAmountElement.textContent = 'KES 0.00';
                totalAmountInput.value = 0;
            }

            // Enable/disable submit button based on selection
            const submitButton = document.querySelector('button[type="submit"]');
            if (submitButton) {
                if (selectedSeats.length === passengerCount) {
                    submitButton.removeAttribute('disabled');
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    submitButton.classList.add('animate-pulse');
                    setTimeout(() => {
                        submitButton.classList.remove('animate-pulse');
                    }, 1000);
                } else {
                    submitButton.setAttribute('disabled', 'disabled');
                    submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
        }
    }

    // Add some animations on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Animate the booking form sections
        const sections = document.querySelectorAll('.bg-blue-50, .bg-purple-50, .bg-green-50');
        sections.forEach((section, index) => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            section.style.transition = 'opacity 0.5s ease, transform 0.5s ease';

            setTimeout(() => {
                section.style.opacity = '1';
                section.style.transform = 'translateY(0)';
            }, 100 * (index + 1));
        });
    });
</script>

<style>
    /* Custom animations */
    @keyframes fade-in-down {
        0% {
            opacity: 0;
            transform: translateY(-10px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fade-out {
        0% {
            opacity: 1;
        }
        100% {
            opacity: 0;
        }
    }

    .animate-fade-in-down {
        animation: fade-in-down 0.3s ease-out;
    }

    .animate-fade-out {
        animation: fade-out 0.3s ease-out;
    }

    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .booking-progress {
            overflow-x: auto;
            padding-bottom: 1rem;
        }

        .booking-progress > div {
            min-width: 80px;
        }
    }
</style>

<?php
// Include footer
require_once 'includes/templates/footer.php';
?>
