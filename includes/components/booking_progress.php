<?php
/**
 * Booking Progress Component
 * 
 * Displays a progress bar for the booking process
 * 
 * @param string $current_step The current step in the booking process
 * @return void
 */

function renderBookingProgress($current_step) {
        $steps = [
            'search' => [
                'name' => 'Search',
                'icon' => 'fa-search',
                'description' => 'Find your route'
            ],
            'select' => [
                'name' => 'Select',
                'icon' => 'fa-chair',
                'description' => 'Choose your seat'
            ],
            'details' => [
                'name' => 'Details',
                'icon' => 'fa-user',
                'description' => 'Passenger information'
            ],
            'payment' => [
                'name' => 'Payment',
                'icon' => 'fa-credit-card',
                'description' => 'Complete payment'
            ],
            'confirm' => [
                'name' => 'Confirm',
                'icon' => 'fa-check-circle',
                'description' => 'Booking confirmed'
            ]
        ];

    // Find the current step index
    $current_index = array_search($current_step, array_keys($steps));
    
    // If step not found, default to first step
    if ($current_index === false) {
        $current_index = 0;
    }
?>
<div class="booking-progress-container mb-6">
    <div class="hidden sm:flex justify-between items-center mb-2">
        <?php foreach ($steps as $key => $step): ?>
            <?php 
                $step_index = array_search($key, array_keys($steps));
                $status = '';
                
                if ($step_index < $current_index) {
                    $status = 'completed';
                } elseif ($step_index === $current_index) {
                    $status = 'current';
                } else {
                    $status = 'upcoming';
                }
            ?>
            <div class="booking-step flex flex-col items-center">
                <div class="step-icon-container <?php echo $status; ?>">
                    <div class="step-icon">
                        <i class="fas <?php echo $step['icon']; ?>"></i>
                    </div>
                    <?php if ($status === 'completed'): ?>
                        <div class="step-check">
                            <i class="fas fa-check"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="step-name mt-2 font-medium <?php echo $status === 'current' ? 'text-primary-600' : ($status === 'completed' ? 'text-green-600' : 'text-gray-400'); ?>">
                    <?php echo $step['name']; ?>
                </div>
                <div class="step-description text-xs <?php echo $status === 'current' ? 'text-primary-500' : ($status === 'completed' ? 'text-green-500' : 'text-gray-400'); ?>">
                    <?php echo $step['description']; ?>
                </div>
            </div>
            
            <?php if ($step_index < count($steps) - 1): ?>
                <div class="step-connector flex-1 mx-2 h-1 rounded-full <?php echo $step_index < $current_index ? 'bg-green-500' : 'bg-gray-300'; ?>"></div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    
    <!-- Mobile Progress Bar -->
    <div class="sm:hidden mb-4">
        <div class="flex justify-between items-center mb-1">
            <div class="text-sm font-medium text-primary-600">
                <?php echo $steps[$current_step]['name']; ?> 
                <span class="text-gray-500 text-xs">
                    (Step <?php echo $current_index + 1; ?> of <?php echo count($steps); ?>)
                </span>
            </div>
            <div class="text-xs text-gray-500">
                <?php echo $steps[$current_step]['description']; ?>
            </div>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-primary-600 h-2.5 rounded-full" style="width: <?php echo (($current_index + 1) / count($steps)) * 100; ?>%"></div>
        </div>
    </div>
</div>

<style>
.booking-step {
    position: relative;
    z-index: 10;
}

.step-icon-container {
    position: relative;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.step-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f3f4f6;
    color: #9ca3af;
    transition: all 0.3s ease;
}

.step-icon-container.current .step-icon {
    background-color: #dbeafe;
    color: #2563eb;
    transform: scale(1.1);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
}

.step-icon-container.completed .step-icon {
    background-color: #d1fae5;
    color: #059669;
}

.step-check {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: #10b981;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    border: 2px solid white;
}

.step-connector {
    height: 3px;
    transition: background-color 0.3s ease;
}
</style>
<?php
    }
?>
