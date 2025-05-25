<?php
/**
 * Loading Overlay Component
 * 
 * Displays a loading overlay for form submissions and page transitions
 * 
 * @return void
 */

function renderLoadingOverlay() {
?>
<div id="loading-overlay" class="loading-overlay">
    <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center">
        <div class="loading-spinner mb-4"></div>
        <p class="text-gray-700 font-medium" id="loading-message">Processing your request...</p>
    </div>
</div>

<script>
    // Loading overlay functionality
    const loadingOverlay = {
        overlay: document.getElementById('loading-overlay'),
        messageElement: document.getElementById('loading-message'),
        
        show: function(message = 'Processing your request...') {
            if (!this.overlay) return;
            
            this.messageElement.textContent = message;
            this.overlay.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        },
        
        hide: function() {
            if (!this.overlay) return;
            
            this.overlay.classList.remove('active');
            document.body.style.overflow = ''; // Restore scrolling
        },
        
        setMessage: function(message) {
            if (!this.messageElement) return;
            
            this.messageElement.textContent = message;
        }
    };

    // Initialize form loading states
    document.addEventListener('DOMContentLoaded', function() {
        // Add loading state to all forms
        const forms = document.querySelectorAll('form:not([data-no-loading])');
        
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                // Check if form is valid
                if (this.checkValidity()) {
                    // Find submit button
                    const submitBtn = this.querySelector('button[type="submit"], input[type="submit"]');
                    
                    if (submitBtn) {
                        // Save original text
                        submitBtn.dataset.originalText = submitBtn.innerHTML;
                        
                        // Add loading class
                        submitBtn.classList.add('btn-loading');
                        
                        // Show loading overlay
                        loadingOverlay.show('Processing your submission...');
                    }
                }
            });
        });
        
        // Add loading state to booking links
        const bookingLinks = document.querySelectorAll('a[href*="select_seats.php"], a[href*="passenger_details.php"], a[href*="payment.php"]');
        
        bookingLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                loadingOverlay.show('Loading next step...');
            });
        });
    });
</script>
<?php
}
?>
