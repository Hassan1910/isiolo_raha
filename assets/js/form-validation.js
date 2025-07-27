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
    // Name validation (first_name, last_name)
    else if ((input.name === 'first_name' || input.name === 'last_name') && input.value.trim() !== '') {
        const nameRegex = /^[a-zA-Z\s]+$/;
        if (!nameRegex.test(input.value.trim())) {
            isValid = false;
            errorMessage = 'Name should only contain letters and spaces';
        } else if (input.value.trim().length < 2) {
            isValid = false;
            errorMessage = 'Name must be at least 2 characters long';
        }
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
        const phoneRegex = /^[+]?[0-9\s\-\(\)]+$/;
        if (!phoneRegex.test(input.value.trim())) {
            isValid = false;
            errorMessage = 'Phone number should only contain numbers, spaces, hyphens, parentheses, and plus sign';
        } else {
            // Clean phone number for length validation
            const cleanedPhone = input.value.trim().replace(/[^0-9+]/g, '');
            if (cleanedPhone.length < 10 || cleanedPhone.length > 13) {
                isValid = false;
                errorMessage = 'Please enter a valid phone number (10-13 digits)';
            }
        }
    }
    // Password validation
    else if (input.type === 'password' && input.name === 'password' && input.value.trim() !== '') {
        const password = input.value.trim();
        if (password.length < 8) {
            isValid = false;
            errorMessage = 'Password must be at least 8 characters long';
        } else if (!/(?=.*[a-z])/.test(password)) {
            isValid = false;
            errorMessage = 'Password must contain at least one lowercase letter';
        } else if (!/(?=.*[A-Z])/.test(password)) {
            isValid = false;
            errorMessage = 'Password must contain at least one uppercase letter';
        } else if (!/(?=.*\d)/.test(password)) {
            isValid = false;
            errorMessage = 'Password must contain at least one number';
        } else if (!/(?=.*[@$!%*?&])/.test(password)) {
            isValid = false;
            errorMessage = 'Password must contain at least one special character (@$!%*?&)';
        }
    }
    // Password confirmation validation
    else if (input.name === 'confirm_password' && input.value.trim() !== '') {
        const passwordInput = document.querySelector('input[name="password"]');
        if (passwordInput && input.value !== passwordInput.value) {
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
/**
 * Initialize form helpers
 */
function initFormHelpers() {
    // Name fields - only allow letters and spaces
    const nameInputs = document.querySelectorAll('input[name="first_name"], input[name="last_name"]');
    nameInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            // Remove any characters that are not letters or spaces
            this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
        });
        
        input.addEventListener('keypress', function(e) {
            // Prevent typing numbers and special characters
            const char = String.fromCharCode(e.which);
            if (!/[a-zA-Z\s]/.test(char)) {
                e.preventDefault();
            }
        });
    });
    
    // Phone number field - only allow numbers, spaces, hyphens, parentheses, and plus sign
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            // Remove any characters that are not allowed
            this.value = this.value.replace(/[^0-9+\s\-\(\)]/g, '');
        });
        
        input.addEventListener('keypress', function(e) {
            // Prevent typing letters and other special characters
            const char = String.fromCharCode(e.which);
            if (!/[0-9+\s\-\(\)]/.test(char)) {
                e.preventDefault();
            }
        });
    });
    
    // Password strength indicator
    const passwordInput = document.querySelector('input[name="password"]');
    if (passwordInput) {
        // Create password strength indicator
        const strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'password-strength-indicator mt-2';
        strengthIndicator.innerHTML = `
            <div class="text-xs text-gray-600 mb-1">Password strength:</div>
            <div class="strength-bar bg-gray-200 rounded-full h-2 mb-2">
                <div class="strength-fill bg-red-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
            <div class="strength-requirements text-xs space-y-1">
                <div class="requirement" data-requirement="length">
                    <span class="text-red-500">✗</span> At least 8 characters
                </div>
                <div class="requirement" data-requirement="lowercase">
                    <span class="text-red-500">✗</span> One lowercase letter
                </div>
                <div class="requirement" data-requirement="uppercase">
                    <span class="text-red-500">✗</span> One uppercase letter
                </div>
                <div class="requirement" data-requirement="number">
                    <span class="text-red-500">✗</span> One number
                </div>
                <div class="requirement" data-requirement="special">
                    <span class="text-red-500">✗</span> One special character (@$!%*?&)
                </div>
            </div>
        `;
        
        passwordInput.parentNode.appendChild(strengthIndicator);
        
        // Update strength indicator on input
        passwordInput.addEventListener('input', function() {
            updatePasswordStrength(this.value, strengthIndicator);
            
            // Also validate confirm password when password changes
            const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');
            if (confirmPasswordInput && confirmPasswordInput.value !== '') {
                validateInput(confirmPasswordInput);
            }
        });
    }
    
    // Real-time validation for confirm password
    const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            validateInput(this);
        });
    }
}

/**
 * Update password strength indicator
 */
function updatePasswordStrength(password, indicator) {
    const requirements = {
        length: password.length >= 8,
        lowercase: /[a-z]/.test(password),
        uppercase: /[A-Z]/.test(password),
        number: /\d/.test(password),
        special: /[@$!%*?&]/.test(password)
    };
    
    const strengthFill = indicator.querySelector('.strength-fill');
    const requirementElements = indicator.querySelectorAll('.requirement');
    
    // Calculate strength percentage
    const metRequirements = Object.values(requirements).filter(Boolean).length;
    const strengthPercentage = (metRequirements / 5) * 100;
    
    // Update strength bar
    strengthFill.style.width = strengthPercentage + '%';
    
    // Update color based on strength
    if (strengthPercentage < 40) {
        strengthFill.className = 'strength-fill bg-red-500 h-2 rounded-full transition-all duration-300';
    } else if (strengthPercentage < 80) {
        strengthFill.className = 'strength-fill bg-yellow-500 h-2 rounded-full transition-all duration-300';
    } else {
        strengthFill.className = 'strength-fill bg-green-500 h-2 rounded-full transition-all duration-300';
    }
    
    // Update requirement indicators
    requirementElements.forEach(element => {
        const requirement = element.dataset.requirement;
        const checkmark = element.querySelector('span');
        
        if (requirements[requirement]) {
            checkmark.className = 'text-green-500';
            checkmark.textContent = '✓';
        } else {
            checkmark.className = 'text-red-500';
            checkmark.textContent = '✗';
        }
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
