/**
 * JomBantu - Authentication JavaScript
 * Handles login, registration, and password recovery
 */

document.addEventListener('DOMContentLoaded', function() {
    // Login form handling
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleAuthFormSubmit(this, 'login');
        });
    }

    // Registration form handling
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
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

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
        });
    });
});

function handleAuthFormSubmit(form, action) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';
    
    setTimeout(() => {
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
        
        if (!isValid) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }
        
        // Simulate successful response
        submitBtn.textContent = 'Success!';
        
        setTimeout(() => {
            // Redirect based on action
            switch (action) {
                case 'login':
                    window.location.href = 'index.html';
                    break;
                case 'register':
                    alert('Registration successful! Please check your email for verification.');
                    window.location.href = 'login.html';
                    break;
                case 'forgot-password':
                    alert('Password reset link sent to your email!');
                    window.location.href = 'login.html';
                    break;
            }
        }, 1000);
    }, 1500);
}

// Campus email validation
function validateCampusEmail(email) {
    // This should be updated with your actual campus email pattern
    const campusEmailPattern = /@(student\.)?(uitm|jombantu)\.edu\.my$/i;
    return campusEmailPattern.test(email);
}