/**
 * JomBantu - Main JavaScript File
 * Core functionality for the platform
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.createElement('button');
    mobileMenuToggle.className = 'mobile-menu-toggle';
    mobileMenuToggle.innerHTML = '☰';
    document.querySelector('header').prepend(mobileMenuToggle);
    
    mobileMenuToggle.addEventListener('click', function() {
        document.querySelector('nav').classList.toggle('active');
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Job card hover effects
    const jobCards = document.querySelectorAll('.job-card');
    jobCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });

    // Notification system
    const notificationBtn = document.querySelector('.notification-btn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            // Using custom modal instead of alert
            window.showCustomModal('Notifications', 'You have new notifications!', 'alert');
            this.querySelector('.badge').style.display = 'none';
        });
    }

    // Form validation for search forms
    const searchForms = document.querySelectorAll('form[role="search"]');
    searchForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const input = this.querySelector('input[type="search"]');
            if (input && input.value.trim() === '') {
                e.preventDefault();
                input.style.borderColor = '#ff6b6b';
                setTimeout(() => {
                    input.style.borderColor = '';
                }, 2000);
            }
        });
    });

    // Initialize tooltips
    const tooltipElems = document.querySelectorAll('[data-tooltip]');
    tooltipElems.forEach(elem => {
        elem.addEventListener('mouseenter', showTooltip);
        elem.addEventListener('mouseleave', hideTooltip);
    });

    // Dark mode toggle (optional)
    const darkModeToggle = document.createElement('button');
    darkModeToggle.className = 'dark-mode-toggle';
    darkModeToggle.innerHTML = '🌓';
    document.querySelector('header').appendChild(darkModeToggle);
    
    darkModeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
    });

    // Check for saved dark mode preference
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
    }
});

function showTooltip(e) {
    const tooltipText = this.getAttribute('data-tooltip');
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = tooltipText;
    
    const rect = this.getBoundingClientRect();
    tooltip.style.left = `${rect.left + rect.width / 2}px`;
    tooltip.style.top = `${rect.top - 40}px`;
    
    document.body.appendChild(tooltip);
    this.tooltip = tooltip;
}

function hideTooltip() {
    if (this.tooltip) {
        this.tooltip.remove();
        this.tooltip = null;
    }
}

// Job application handling - now triggers a modal
if (document.getElementById('applyJobForm')) {
    document.getElementById('applyJobForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const message = this.querySelector('textarea').value;
        if (message.trim() === '') {
            window.showCustomModal('Application Error', 'Please write your application message.', 'alert');
            return;
        }
        
        // Simulate form submission
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Applying...';
        
        setTimeout(() => {
            window.showCustomModal('Application Submitted', 'Your application has been submitted successfully!', 'alert');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Apply for Job';
            // Optionally clear form or redirect
            this.reset();
        }, 1500);
    });
}
