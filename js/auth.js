/**
 * JomBantu - Authentication JavaScript
 * Handles login, registration, and password recovery
 */

document.addEventListener('DOMContentLoaded', function() {
    // Login form handling
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission initially to handle validation
            handleAuthFormSubmit(this, 'login');
        });
    }

    // Registration form handling
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission initially to handle validation
            handleAuthFormSubmit(this, 'register');
        });

        // Password confirmation validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        
        if (password && confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if (this.value !== password.value) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
    }

    // Forgot password form handling
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleAuthFormSubmit(this, 'forgot-password');
        });
    }

    // Toggle password visibility (if you add an icon/button for this)
    document.querySelectorAll('.toggle-password').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️'; // Example icons
        });
    });
});

function handleAuthFormSubmit(form, action) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Basic client-side validation
    let isValid = true;
    form.querySelectorAll('[required]').forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = '#ff6b6b';
            setTimeout(() => {
                input.style.borderColor = '';
            }, 2000);
        }
    });

    // Specific validation for registration email (campus email)
    if (action === 'register') {
        const emailInput = form.querySelector('#email');
        if (emailInput && !validateCampusEmail(emailInput.value)) {
            isValid = false;
            emailInput.style.borderColor = '#ff6b6b';
            window.showCustomModal('Validation Error', 'Please use a valid campus email (e.g., @uitm.edu.my or @jombantu.edu.my).', 'alert');
            setTimeout(() => {
                emailInput.style.borderColor = '';
            }, 3000);
        }
    }
    
    if (!isValid) {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
        return;
    }
    
    // Disable button and show processing message
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';

    // For login and registration, let PHP handle the submission and redirection
    if (action === 'login' || action === 'register') {
        form.submit(); // Submit the form normally, PHP will handle redirect
    } else if (action === 'forgot-password') {
        // For forgot-password, simulate success and use JS redirect (no PHP backend for this yet)
        setTimeout(() => {
            window.showCustomModal(
                'Password Reset',
                'Password reset link sent to your email!',
                'alert',
                () => { window.location.href = 'login.html'; } // Redirect after user closes modal
            );
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }, 1500);
    }
}

// Campus email validation
function validateCampusEmail(email) {
    // This should be updated with your actual campus email pattern
    const campusEmailPattern = /@(student\.)?(uitm|jombantu)\.edu\.my$/i;
    return campusEmailPattern.test(email);
}
