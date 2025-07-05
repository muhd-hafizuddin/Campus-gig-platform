/**
 * JomBantu - Authentication JavaScript (Visual Enhancements Only)
 * PHP handles all validation and business logic
 */

document.addEventListener('DOMContentLoaded', function() {
    // Login form visual enhancements
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Visual feedback only
            submitBtn.disabled = true;
            submitBtn.textContent = 'Logging in...';
            
            // Let PHP handle everything else - don't prevent submission
        });
    }

    // Registration form visual enhancements
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            
            // Visual feedback only
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating account...';
            
            // Let PHP handle everything else - don't prevent submission
        });

        // Real-time password matching (visual feedback only)
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        
        if (password && confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if (this.value !== password.value) {
                    this.style.borderColor = '#ff6b6b';
                    this.title = 'Passwords do not match';
                } else {
                    this.style.borderColor = '#4caf50';
                    this.title = 'Passwords match';
                }
            });
        }
    }

    // Add visual feedback to form inputs
    document.querySelectorAll('input[required]').forEach(input => {
        input.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.style.borderColor = '#ff6b6b';
            } else {
                this.style.borderColor = '#4caf50';
            }
        });

        input.addEventListener('focus', function() {
            this.style.borderColor = '#007bff';
        });
    });

    // Toggle password visibility (optional feature)
    document.querySelectorAll('.toggle-password').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
        });
    });
});

// Optional: Add loading animation
function showLoadingAnimation() {
    // Add spinner or loading animation to the page
    const loader = document.createElement('div');
    loader.id = 'pageLoader';
    loader.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(loader);
}

function hideLoadingAnimation() {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        loader.remove();
    }
}