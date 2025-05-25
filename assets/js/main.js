/**
 * Main JavaScript file for Isiolo Raha Bus Booking System
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM loaded - initializing Isiolo Raha JS");

    // Initialize seat selection functionality
    initSeatSelection();

    // Initialize date pickers
    initDatePickers();

    // Initialize form validation
    initFormValidation();

    // Paystack integration removed

    // Initialize scroll animations
    initScrollAnimations();

    // Initialize sticky header
    initStickyHeader();

    // Initialize travel date handler
    initTravelDateHandler();

    // Initialize reveal animations
    initRevealAnimations();

    // Initialize hero slider
    initHeroSlider();

    // Initialize testimonial slider
    initTestimonialSlider();

    // Add spin animation class
    document.documentElement.style.setProperty('--animate-duration', '.5s');

    // Add animation to the search form
    const searchForm = document.querySelector('.search-form-container');
    if (searchForm) {
        setTimeout(() => {
            searchForm.classList.add('zoom-in');
        }, 300);
    }

    // Initialize button hover effects
    initButtonHoverEffects();
});

/**
 * Initialize hero slider with background images
 */
function initHeroSlider() {
    console.log("Initializing hero slider");
    const slider = document.querySelector('.hero-slider');
    if (!slider) {
        console.error("Hero slider container not found");
        return;
    }

    const slides = slider.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.slider-dot');
    console.log(`Found ${slides.length} slides and ${dots.length} navigation dots`);

    let currentSlide = 0;
    let slideInterval;

    // Function to activate a specific slide
    function activateSlide(index) {
        console.log(`Activating slide ${index}`);
        // Remove active class from all slides and dots
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));

        // Add active class to current slide and dot
        slides[index].classList.add('active');
        dots[index].classList.add('active');

        // Update currentSlide
        currentSlide = index;
    }

    // Function to move to the next slide
    function nextSlide() {
        const newIndex = (currentSlide + 1) % slides.length;
        console.log(`Auto-advancing to slide ${newIndex}`);
        activateSlide(newIndex);
    }

    // Start automatic slideshow
    function startSlideshow() {
        console.log("Starting slideshow timer");
        slideInterval = setInterval(nextSlide, 5000); // Change slide every 5 seconds
    }

    // Stop automatic slideshow
    function stopSlideshow() {
        console.log("Stopping slideshow timer");
        clearInterval(slideInterval);
    }

    // Add click events to dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            console.log(`Dot ${index} clicked`);
            stopSlideshow();
            activateSlide(index);
            startSlideshow();
        });
    });

    // Initialize slider
    console.log("Starting initial slideshow");
    startSlideshow();
}

/**
 * Initialize seat selection functionality
 */
function initSeatSelection() {
    const seatContainer = document.querySelector('.bus-layout');
    const selectedSeatsInput = document.getElementById('selected_seats');
    const totalAmountElement = document.getElementById('total_amount');
    const seatPriceElement = document.getElementById('seat_price');

    if (!seatContainer) return;

    let selectedSeats = [];

    // Get seat price if available
    const seatPrice = seatPriceElement ? parseFloat(seatPriceElement.value) : 0;

    // Add click event to all available seats
    const availableSeats = seatContainer.querySelectorAll('.seat.available');
    availableSeats.forEach(seat => {
        seat.addEventListener('click', function() {
            const seatNumber = this.dataset.seat;

            if (this.classList.contains('selected')) {
                // Deselect seat
                this.classList.remove('selected');
                selectedSeats = selectedSeats.filter(seat => seat !== seatNumber);
            } else {
                // Select seat
                this.classList.add('selected');
                selectedSeats.push(seatNumber);
            }

            // Update hidden input with selected seats
            if (selectedSeatsInput) {
                selectedSeatsInput.value = selectedSeats.join(',');
            }

            // Update total amount if price element exists
            if (totalAmountElement && seatPrice) {
                const totalAmount = selectedSeats.length * seatPrice;
                totalAmountElement.textContent = formatCurrency(totalAmount);

                // Update hidden input for total amount if it exists
                const totalAmountInput = document.getElementById('total_amount_input');
                if (totalAmountInput) {
                    totalAmountInput.value = totalAmount;
                }
            }
        });
    });
}

/**
 * Initialize date pickers
 */
function initDatePickers() {
    // This is a placeholder for date picker initialization
    // You can implement a custom date picker or use a library
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        // Add any custom date picker initialization here
    });
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate="true"]');

    forms.forEach(form => {
        // Add input focus/blur animations
        const formInputs = form.querySelectorAll('input, select');
        formInputs.forEach(input => {
            // Add focus event
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('scale-102');
                this.parentElement.classList.add('z-10');
            });

            // Add blur event
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('scale-102');
                this.parentElement.classList.remove('z-10');

                // Validate on blur
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.classList.add('border-red-500', 'bg-red-50');
                    showInputError(this, 'This field is required');
                } else {
                    this.classList.remove('border-red-500', 'bg-red-50');
                    removeInputError(this);
                }
            });

            // Add input event for select elements
            if (input.tagName.toLowerCase() === 'select') {
                input.addEventListener('change', function() {
                    if (this.value) {
                        this.classList.add('text-gray-800', 'font-medium');
                    } else {
                        this.classList.remove('text-gray-800', 'font-medium');
                    }
                });
            }
        });

        // Form submission validation
        form.addEventListener('submit', function(event) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('border-red-500', 'bg-red-50');
                    showInputError(field, 'This field is required');
                } else {
                    field.classList.remove('border-red-500', 'bg-red-50');
                    removeInputError(field);
                }
            });

            // Origin and destination validation
            const origin = form.querySelector('#origin');
            const destination = form.querySelector('#destination');

            if (origin && destination && origin.value && destination.value) {
                if (origin.value === destination.value) {
                    isValid = false;
                    destination.classList.add('border-red-500', 'bg-red-50');
                    showInputError(destination, 'Origin and destination cannot be the same');
                }
            }

            // Travel date validation
            const travelDate = form.querySelector('#travel_date');
            const returnDate = form.querySelector('#return_date');

            if (travelDate && returnDate && travelDate.value && returnDate.value) {
                if (new Date(returnDate.value) < new Date(travelDate.value)) {
                    isValid = false;
                    returnDate.classList.add('border-red-500', 'bg-red-50');
                    showInputError(returnDate, 'Return date cannot be earlier than departure date');
                }
            }

            if (!isValid) {
                event.preventDefault();

                // Scroll to first error
                const firstError = form.querySelector('.border-red-500');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }

                // Show error alert
                showFormError('Please correct the errors in the form');
            } else {
                // Add loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Searching...';
                }
            }
        });
    });
}

/**
 * Show input error message
 */
function showInputError(inputElement, message) {
    // Remove existing error
    removeInputError(inputElement);

    // Create new error message
    const errorElement = document.createElement('p');
    errorElement.classList.add('error-message', 'text-red-500', 'text-xs', 'mt-1', 'font-medium');
    errorElement.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i>${message}`;

    // Insert after input parent (to account for icon wrapper)
    inputElement.parentElement.insertAdjacentElement('afterend', errorElement);
}

/**
 * Remove input error message
 */
function removeInputError(inputElement) {
    const parent = inputElement.parentElement.parentElement;
    const errorElement = parent.querySelector('.error-message');
    if (errorElement) {
        errorElement.remove();
    }
}

/**
 * Show form error message
 */
function showFormError(message) {
    // Check if error alert already exists
    if (document.querySelector('.form-error-alert')) {
        return;
    }

    // Create error alert
    const errorAlert = document.createElement('div');
    errorAlert.classList.add('form-error-alert', 'bg-red-100', 'border-l-4', 'border-red-500', 'text-red-700', 'p-4', 'mb-6', 'rounded-md', 'flex', 'items-start');
    errorAlert.innerHTML = `
        <div class="flex-shrink-0 mr-3">
            <i class="fas fa-exclamation-triangle text-red-500 text-lg"></i>
        </div>
        <div>
            <p class="font-medium">${message}</p>
        </div>
        <button type="button" class="ml-auto text-red-500 hover:text-red-700" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    // Add to form
    const searchForm = document.querySelector('.search-form-container form');
    if (searchForm) {
        searchForm.insertAdjacentElement('afterbegin', errorAlert);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (errorAlert.parentElement) {
                errorAlert.remove();
            }
        }, 5000);
    }
}

/**
 * Payment integration placeholder
 * Paystack integration has been removed
 */

/**
 * Format currency
 *
 * @param {number} amount - The amount to format
 * @return {string} The formatted amount
 */
function formatCurrency(amount) {
    return 'KES ' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

/**
 * Toggle password visibility
 *
 * @param {string} inputId - The ID of the password input
 * @param {string} toggleId - The ID of the toggle button
 */
function togglePasswordVisibility(inputId, toggleId) {
    const passwordInput = document.getElementById(inputId);
    const toggleButton = document.getElementById(toggleId);

    if (!passwordInput || !toggleButton) return;

    toggleButton.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        // Toggle icon
        if (type === 'password') {
            this.innerHTML = '<i class="fas fa-eye"></i>';
        } else {
            this.innerHTML = '<i class="fas fa-eye-slash"></i>';
        }
    });
}

/**
 * Initialize scroll animations
 */
function initScrollAnimations() {
    // Only run on larger screens to avoid performance issues on mobile
    if (window.innerWidth < 768) return;

    const animateElements = document.querySelectorAll('.feature-box, .route-card, .testimonial-card');

    // Create an intersection observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                // Unobserve after animation is added
                observer.unobserve(entry.target);
            }
        });
    }, {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    });

    // Observe each element
    animateElements.forEach(element => {
        // Remove any existing animation classes
        element.classList.remove('fade-in');
        observer.observe(element);
    });
}

/**
 * Initialize sticky header
 */
function initStickyHeader() {
    const header = document.querySelector('nav');
    const heroSection = document.querySelector('.hero');

    if (!header || !heroSection) return;

    const heroBottom = heroSection.offsetTop + heroSection.offsetHeight;

    window.addEventListener('scroll', () => {
        if (window.scrollY > heroBottom - 100) {
            header.classList.add('fixed', 'top-0', 'left-0', 'right-0', 'z-50', 'shadow-md', 'animate-slideDown');
        } else {
            header.classList.remove('fixed', 'top-0', 'left-0', 'right-0', 'z-50', 'shadow-md', 'animate-slideDown');
        }
    });
}

/**
 * Smooth scroll to element
 *
 * @param {string} elementId - The ID of the element to scroll to
 */
function smoothScrollTo(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;

    const headerOffset = 80;
    const elementPosition = element.getBoundingClientRect().top;
    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

    window.scrollTo({
        top: offsetPosition,
        behavior: 'smooth'
    });
}

/**
 * Switch journey type (one-way, round-trip)
 */
function switchJourneyType(type) {
    const oneWayTab = document.getElementById('one-way-tab');
    const roundTripTab = document.getElementById('round-trip-tab');
    const returnDateGroup = document.getElementById('return-date-group');
    const journeyTypeInput = document.getElementById('journey_type');

    if (!oneWayTab || !roundTripTab || !returnDateGroup || !journeyTypeInput) return;

    // Update active tab
    if (type === 'one-way') {
        oneWayTab.classList.add('active');
        roundTripTab.classList.remove('active');
        returnDateGroup.classList.add('hidden');
        returnDateGroup.querySelector('input').required = false;
        journeyTypeInput.value = 'one-way';
    } else {
        oneWayTab.classList.remove('active');
        roundTripTab.classList.add('active');
        returnDateGroup.classList.remove('hidden');
        returnDateGroup.classList.add('slide-in-right');
        returnDateGroup.querySelector('input').required = true;
        journeyTypeInput.value = 'round-trip';
    }

    // Ensure proper animation by removing and re-adding
    returnDateGroup.classList.remove('slide-in-right');
    void returnDateGroup.offsetWidth; // Force reflow
    if (type === 'round-trip') {
        returnDateGroup.classList.add('slide-in-right');
    }
}

/**
 * Swap origin and destination
 */
function swapDestinations() {
    const originSelect = document.getElementById('origin');
    const destinationSelect = document.getElementById('destination');
    const swapButton = document.querySelector('.swap-destinations');

    if (!originSelect || !destinationSelect) return;

    // Store current values
    const originValue = originSelect.value;
    const destinationValue = destinationSelect.value;

    // Add animation classes
    originSelect.parentElement.parentElement.classList.add('swap-animation-left');
    destinationSelect.parentElement.parentElement.classList.add('swap-animation-right');
    swapButton.classList.add('animate-spin');

    // Swap values after a short delay for animation
    setTimeout(() => {
        originSelect.value = destinationValue;
        destinationSelect.value = originValue;

        // Remove animation classes
        originSelect.parentElement.parentElement.classList.remove('swap-animation-left');
        destinationSelect.parentElement.parentElement.classList.remove('swap-animation-right');
        swapButton.classList.remove('animate-spin');

        // Add a bounce effect
        originSelect.parentElement.parentElement.classList.add('swap-complete');
        destinationSelect.parentElement.parentElement.classList.add('swap-complete');

        // Remove the bounce effect
        setTimeout(() => {
            originSelect.parentElement.parentElement.classList.remove('swap-complete');
            destinationSelect.parentElement.parentElement.classList.remove('swap-complete');
        }, 500);
    }, 300);
}

/**
 * Initialize travel date change handler
 */
function initTravelDateHandler() {
    const travelDate = document.getElementById('travel_date');
    const returnDate = document.getElementById('return_date');

    if (travelDate && returnDate) {
        travelDate.addEventListener('change', function() {
            returnDate.min = this.value;
            if (returnDate.value && returnDate.value < this.value) {
                returnDate.value = this.value;
            }
        });
    }
}

/**
 * Initialize reveal animations
 */
function initRevealAnimations() {
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
}

/**
 * Initialize testimonial slider
 */
function initTestimonialSlider() {
    console.log("Initializing testimonial slider");
    const track = document.getElementById('testimonialTrack');
    const dotsContainer = document.querySelector('.testimonial-dots');
    const prevButton = document.getElementById('prevTestimonial');
    const nextButton = document.getElementById('nextTestimonial');

    if (!track || !dotsContainer || !prevButton || !nextButton) {
        console.error("Testimonial slider elements not found");
        return;
    }

    const slides = track.querySelectorAll('.testimonial-slide');
    if (slides.length === 0) {
        console.error("No testimonial slides found");
        return;
    }

    console.log(`Found ${slides.length} testimonial slides`);

    // Variables for slider
    let currentIndex = 0;
    let slideWidth;
    let slidesToShow = 1; // Default for mobile
    let autoplayInterval;

    // Create dots for pagination
    slides.forEach((_, index) => {
        const dot = document.createElement('button');
        dot.classList.add('testimonial-dot', 'w-3', 'h-3', 'rounded-full', 'bg-gray-300', 'hover:bg-primary-500', 'transition-all', 'duration-300');
        dot.setAttribute('aria-label', `Go to testimonial ${index + 1}`);

        // Add active class to first dot
        if (index === 0) {
            dot.classList.add('bg-primary-500', 'w-6');
        }

        // Add click event
        dot.addEventListener('click', () => {
            goToSlide(index);
        });

        dotsContainer.appendChild(dot);
    });

    // Get all dots
    const dots = dotsContainer.querySelectorAll('.testimonial-dot');

    // Set up slider based on screen size
    function setupSlider() {
        // Determine how many slides to show based on screen width
        if (window.innerWidth >= 1024) {
            slidesToShow = 3; // Desktop
        } else if (window.innerWidth >= 768) {
            slidesToShow = 2; // Tablet
        } else {
            slidesToShow = 1; // Mobile
        }

        // Calculate slide width
        slideWidth = track.parentElement.offsetWidth / slidesToShow;

        // Set width of each slide
        slides.forEach(slide => {
            slide.style.width = `${slideWidth}px`;
        });

        // Update track position
        goToSlide(currentIndex, false);

        console.log(`Slider setup: ${slidesToShow} slides to show, slide width: ${slideWidth}px`);
    }

    // Go to specific slide
    function goToSlide(index, animate = true) {
        // Ensure index is within bounds
        if (index < 0) {
            index = 0;
        } else if (index > slides.length - slidesToShow) {
            index = slides.length - slidesToShow;
        }

        // Update current index
        currentIndex = index;

        // Calculate new position
        const newPosition = -index * slideWidth;

        // Apply transition only if animate is true
        track.style.transition = animate ? 'transform 0.5s ease-in-out' : 'none';
        track.style.transform = `translateX(${newPosition}px)`;

        // Update dots
        dots.forEach((dot, i) => {
            if (i === index) {
                dot.classList.add('bg-primary-500', 'w-6');
                dot.classList.remove('bg-gray-300');
            } else {
                dot.classList.remove('bg-primary-500', 'w-6');
                dot.classList.add('bg-gray-300');
            }
        });

        console.log(`Moved to slide ${index}`);

        // Reset autoplay
        resetAutoplay();
    }

    // Previous slide
    function prevSlide() {
        goToSlide(currentIndex - 1);
    }

    // Next slide
    function nextSlide() {
        goToSlide(currentIndex + 1);
    }

    // Start autoplay
    function startAutoplay() {
        autoplayInterval = setInterval(() => {
            // If at the end, go back to start
            if (currentIndex >= slides.length - slidesToShow) {
                goToSlide(0);
            } else {
                nextSlide();
            }
        }, 5000); // Change slide every 5 seconds

        console.log("Testimonial autoplay started");
    }

    // Reset autoplay
    function resetAutoplay() {
        if (autoplayInterval) {
            clearInterval(autoplayInterval);
        }
        startAutoplay();
    }

    // Add event listeners
    prevButton.addEventListener('click', prevSlide);
    nextButton.addEventListener('click', nextSlide);

    // Handle window resize
    window.addEventListener('resize', () => {
        setupSlider();
    });

    // Initialize slider
    setupSlider();
    startAutoplay();

    // Add touch/swipe support
    let touchStartX = 0;
    let touchEndX = 0;

    track.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    track.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, { passive: true });

    function handleSwipe() {
        const swipeThreshold = 50; // Minimum distance to register as swipe

        if (touchStartX - touchEndX > swipeThreshold) {
            // Swiped left, go to next slide
            nextSlide();
        } else if (touchEndX - touchStartX > swipeThreshold) {
            // Swiped right, go to previous slide
            prevSlide();
        }
    }
}

/**
 * Initialize button hover effects
 */
function initButtonHoverEffects() {
    // Search button hover effect
    const searchBtn = document.querySelector('.search-btn');
    if (searchBtn) {
        searchBtn.addEventListener('mouseover', function() {
            this.style.backgroundColor = '#15803d';
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 10px 20px rgba(21, 128, 61, 0.3)';
        });

        searchBtn.addEventListener('mouseout', function() {
            this.style.backgroundColor = '#16a34a';
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 8px 15px rgba(21, 128, 61, 0.25)';
        });
    }

    // Journey tabs hover effects
    const journeyTabs = document.querySelectorAll('.journey-tab:not(.active)');
    journeyTabs.forEach(tab => {
        tab.addEventListener('mouseover', function() {
            if (!this.classList.contains('active')) {
                this.style.backgroundColor = '#f9fafb';
                this.style.borderColor = '#d1d5db';
                this.style.transform = 'translateY(-1px)';
                this.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.08)';
            }
        });

        tab.addEventListener('mouseout', function() {
            if (!this.classList.contains('active')) {
                this.style.backgroundColor = '';
                this.style.borderColor = '#e5e7eb';
                this.style.transform = '';
                this.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.05)';
            }
        });
    });

    // Swap destinations button hover effect
    const swapBtn = document.querySelector('.swap-destinations');
    if (swapBtn) {
        swapBtn.addEventListener('mouseover', function() {
            this.style.backgroundColor = '#f0fdf4';
            this.style.color = '#15803d';
            this.style.transform = 'translate(-50%, -50%) scale(1.1)';
            this.style.boxShadow = '0 6px 15px rgba(21, 128, 61, 0.15)';
        });

        swapBtn.addEventListener('mouseout', function() {
            this.style.backgroundColor = 'white';
            this.style.color = '#16a34a';
            this.style.transform = 'translate(-50%, -50%)';
            this.style.boxShadow = '0 4px 10px rgba(0, 0, 0, 0.1)';
        });
    }

    // Testimonial navigation buttons hover effects
    const testimonialButtons = document.querySelectorAll('#prevTestimonial, #nextTestimonial');
    testimonialButtons.forEach(button => {
        button.addEventListener('mouseover', function() {
            this.style.backgroundColor = '#f0fdf4';
            this.style.transform = 'scale(1.05)';
        });

        button.addEventListener('mouseout', function() {
            this.style.backgroundColor = 'white';
            this.style.transform = 'scale(1)';
        });
    });
}
