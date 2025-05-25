/**
 * Enhanced Form Validation for Isiolo Raha Bus Booking System
 * 
 * This script provides improved form validation with better user feedback
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize enhanced form validation
    initEnhancedFormValidation();
    
    // Initialize password toggle functionality
    initPasswordToggles();
    
    // Initialize form helpers
    initFormHelpers();
});

/**
 * Initialize enhanced form validation
 */
function initEnhancedFormValidation() {
    // Get all forms with data-validate attribute
    const forms = document.querySelectorAll('form[data-validate="true"]');
    
    forms.forEach(form => {
        // Add novalidate attribute to disable browser's default validation
        form.setAttribute('novalidate', '');
        
        // Add submit event listener
        form.addEventListener('submit', function(event) {
            // Check if form is valid
            if (!validateForm(this)) {
                // Prevent form submission if validation fails
                event.preventDefault();
                event.stopPropagation();
            }
        });
        
        // Add input event listeners for real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            // Validate on blur
            input.addEventListener('blur', function() {
                validateInput(this);
            });
            
            // Clear error on input
            input.addEventListener('input', function() {
                const errorElement = this.parentNode.querySelector('.error-message');
                if (errorElement) {
                    errorElement.remove();
                }
                this.classList.remove('border-red-500', 'bg-red-50');
                this.classList.add('border-gray-300');
            });
        });
    });
}

/**
 * Validate an entire form
 * 
 * @param {HTMLFormElement} form - The form to validate
 * @return {boolean} - Whether the form is valid
 */
function validateForm(form) {
    let isValid = true;
    
    // Get all inputs, selects, and textareas
    const inputs = form.querySelectorAll('input, select, textarea');
    
    // Validate each input
    inputs.forEach(input => {
        if (!validateInput(input)) {
            isValid = false;
        }
    });
    
    // If form is invalid, scroll to the first invalid input
    if (!isValid) {
        const firstInvalidInput = form.querySelector('.border-red-500');
        if (firstInvalidInput) {
            firstInvalidInput.focus();
            firstInvalidInput.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }
    }
    
    return isValid;
}

/**
 * Validate a single input
 * 
 * @param {HTMLElement} input - The input to validate
 * @return {boolean} - Whether the input is valid
 */
function validateInput(input) {
    // Skip disabled or non-required inputs with no value
    if (input.disabled || (!input.required && input.value === '')) {
        return true;
    }
    
    let isValid = true;
    let errorMessage = '';
    
    // Remove existing error message
    const existingError = input.parentNode.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    
    // Check for empty required fields
    if (input.required && input.value.trim() === '') {
        isValid = false;
        errorMessage = 'This field is required';
    }
    // Email validation
    else if (input.type === 'email' && input.value.trim() !== '') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(input.value.trim())) {
            isValid = false;
            errorMessage = 'Please enter a valid email address';
        }
    }
    // Phone validation
    else if (input.type === 'tel' && input.value.trim() !== '') {
        const phoneRegex = /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/;
        if (!phoneRegex.test(input.value.trim())) {
            isValid = false;
            errorMessage = 'Please enter a valid phone number';
        }
    }
    // Password validation
    else if (input.type === 'password' && input.dataset.minLength) {
        const minLength = parseInt(input.dataset.minLength);
        if (input.value.length < minLength) {
            isValid = false;
            errorMessage = `Password must be at least ${minLength} characters`;
        }
    }
    // Password confirmation validation
    else if (input.type === 'password' && input.dataset.matches) {
        const matchInput = document.getElementById(input.dataset.matches);
        if (matchInput && input.value !== matchInput.value) {
            isValid = false;
            errorMessage = 'Passwords do not match';
        }
    }
    
    // Update input styling based on validation
    if (!isValid) {
        input.classList.remove('border-gray-300');
        input.classList.add('border-red-500', 'bg-red-50');
        
        // Add error message
        const errorElement = document.createElement('p');
        errorElement.className = 'error-message text-red-500 text-xs mt-1';
        errorElement.textContent = errorMessage;
        input.parentNode.appendChild(errorElement);
    } else {
        input.classList.remove('border-red-500', 'bg-red-50');
        input.classList.add('border-gray-300');
    }
    
    return isValid;
}

/**
 * Initialize password toggle functionality
 */
function initPasswordToggles() {
    const toggleButtons = document.querySelectorAll('.password-toggle');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const passwordInput = document.getElementById(targetId);
            
            if (passwordInput) {
                // Toggle password visibility
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    passwordInput.type = 'password';
                    this.innerHTML = '<i class="fas fa-eye"></i>';
                }
            }
        });
    });
}

/**
 * Initialize form helpers
 */
function initFormHelpers() {
    // Auto-format phone numbers
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            // Get input value and remove non-numeric characters
            let value = this.value.replace(/\D/g, '');
            
            // Format the phone number
            if (value.length > 0) {
                if (value.length <= 3) {
                    this.value = value;
                } else if (value.length <= 6) {
                    this.value = value.slice(0, 3) + '-' + value.slice(3);
                } else {
                    this.value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
                }
            }
        });
    });
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
    
    if (passwordInput && toggleButton) {
        toggleButton.addEventListener('click', function() {
            // Toggle password visibility
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    }
}
