<?php
// This component displays the bus layout for seat selection
// It expects the following variables to be set:
// - $schedule: The schedule details including bus capacity
// - $booked_seats: Array of already booked seat numbers

// Default values if not set
$schedule = $schedule ?? null;
$booked_seats = $booked_seats ?? [];

if (!$schedule) {
    echo '<div class="p-4 bg-yellow-100 text-yellow-800 rounded-lg">Please select a schedule to view the bus layout.</div>';
    return;
}

// Get bus capacity
$capacity = $schedule['capacity'] ?? 44; // Default to 44 seats if not specified

// Calculate layout dimensions
$rows = ceil($capacity / 4);
$remaining_seats = $capacity % 4;
?>

<div class="bus-layout">
    <!-- Bus Front -->
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

        <!-- Driver Area -->
        <div class="driver-area mb-4 flex items-center">
            <div class="driver-seat">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="steering-wheel ml-2">
                <i class="fas fa-steering-wheel"></i>
            </div>
        </div>

        <!-- Bus Floor with Seats -->
        <div class="bus-floor">
            <div class="seat-rows">
                <?php
                // Generate bus layout based on capacity
                $seat_number = 1;
                
                // Define seat letters for columns
                $seat_letters = ['A', 'B', 'C', 'D'];
                
                for ($row = 1; $row <= $rows; $row++) {
                    echo '<div class="seat-row">';
                    echo '<div class="row-number">' . $row . '</div>';
                    
                    // Generate seats for this row (A, B, aisle, C, D)
                    for ($col = 0; $col < 4; $col++) {
                        // Add aisle space between B and C seats
                        if ($col == 2) {
                            echo '<div class="seat aisle"></div>';
                            continue;
                        }
                        
                        // Calculate actual seat position (0=A, 1=B, 2=C, 3=D)
                        $actual_col = $col > 2 ? $col - 1 : $col;
                        
                        // Generate seat ID
                        $seat_id = $seat_letters[$actual_col] . $row;
                        
                        // Check if we've exceeded capacity
                        if ($seat_number > $capacity) {
                            echo '<div class="seat-placeholder"></div>';
                            continue;
                        }
                        
                        // Check if seat is booked
                        $is_booked = in_array($seat_id, $booked_seats);
                        
                        // Determine seat class
                        $seat_class = $is_booked ? 'seat booked' : 'seat available';
                        
                        // Add window class for window seats (A and D)
                        if ($actual_col == 0 || $actual_col == 3) {
                            $seat_class .= ' window';
                        }
                        
                        echo '<div class="' . $seat_class . '" data-seat="' . $seat_id . '">';
                        echo $seat_id;
                        echo '</div>';
                        
                        $seat_number++;
                    }
                    
                    echo '</div>'; // End seat-row
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="seat-legend mt-4 flex flex-wrap justify-center gap-4">
        <div class="flex items-center">
            <div class="seat available w-8 h-8 mr-2"></div>
            <span class="text-sm">Available</span>
        </div>
        <div class="flex items-center">
            <div class="seat selected w-8 h-8 mr-2"></div>
            <span class="text-sm">Selected</span>
        </div>
        <div class="flex items-center">
            <div class="seat booked w-8 h-8 mr-2"></div>
            <span class="text-sm">Booked</span>
        </div>
        <div class="flex items-center">
            <div class="seat window available w-8 h-8 mr-2"></div>
            <span class="text-sm">Window</span>
        </div>
    </div>
</div>

<style>
    /* Bus Layout Styles */
    .bus-layout {
        width: 100%;
        max-width: 480px;
        margin: 0 auto;
        perspective: 1000px;
    }

    .bus-front {
        background-color: #334155;
        color: white;
        text-align: center;
        padding: 8px;
        border-radius: 8px 8px 0 0;
        font-weight: bold;
    }

    .bus-container {
        background-color: #f8fafc;
        border-radius: 0 0 12px 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transform: rotateX(5deg);
        transform-style: preserve-3d;
        position: relative;
    }

    /* Bus Roof Styles */
    .bus-roof {
        background-color: #334155;
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
        background-color: #fbbf24;
        border-radius: 50%;
        animation: blink 2s infinite;
    }

    @keyframes blink {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 1; }
    }

    /* Driver Area Styles */
    .driver-area {
        padding: 10px;
        background-color: #e5e7eb;
        border-radius: 8px;
        width: fit-content;
    }

    .driver-seat {
        width: 30px;
        height: 30px;
        background-color: #9ca3af;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1f2937;
    }

    .steering-wheel {
        color: #1f2937;
    }

    /* Bus Floor Styles */
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

    /* Seat Rows Styles */
    .seat-rows {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .seat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
    }

    .row-number {
        position: absolute;
        left: -20px;
        width: 20px;
        text-align: center;
        font-size: 0.8rem;
        color: #6b7280;
    }

    /* Seat Styles */
    .seat {
        width: 40px;
        height: 40px;
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
        color: white;
        border: 2px solid #1d4ed8;
    }

    .seat-placeholder {
        width: 40px;
        height: 40px;
    }

    /* Legend Styles */
    .seat-legend .seat {
        cursor: default;
        transform: none !important;
    }
</style>
