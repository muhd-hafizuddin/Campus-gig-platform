/**
 * JomBantu - Admin JavaScript
 * Handles admin panel functionality
 */

// Global function for custom alerts/confirmations (to replace native alert/confirm)
function showCustomModal(title, message, type = 'alert', callback = null) {
    const modalId = 'customAdminModal'; // Consistent ID for the modal
    let modal = document.getElementById(modalId);

    if (!modal) {
        modal = document.createElement('div');
        modal.id = modalId;
        modal.className = 'admin-modal';
        document.body.appendChild(modal);
    }

    let buttonsHtml = '';
    if (type === 'confirm') {
        buttonsHtml = `
            <button class="btn btn-secondary close-modal">Cancel</button>
            <button class="btn btn-primary confirm-action">Confirm</button>
        `;
    } else if (type === 'custom-form') { // Added for custom forms within the modal
        buttonsHtml = ''; // Form will have its own buttons
    }
    else { // 'alert' type
        buttonsHtml = `
            <button class="btn btn-primary close-modal">OK</button>
        `;
    }

    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>${title}</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">${message}</div>
            <div class="modal-footer">
                ${buttonsHtml}
            </div>
        </div>
    `;

    document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open

    // Add event listeners for closing the modal
    modal.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            modal.remove();
            document.body.style.overflow = ''; // Restore scrolling
            if (type === 'confirm' && callback) {
                callback(false); // Call callback with false for cancel
            }
        });
    });

    // Add event listener for confirm action if it's a 'confirm' type modal
    if (type === 'confirm') {
        modal.querySelector('.confirm-action').addEventListener('click', () => {
            modal.remove();
            document.body.style.overflow = ''; // Restore scrolling
            if (callback) {
                callback(true); // Call callback with true for confirm
            }
        });
    }
}


document.addEventListener('DOMContentLoaded', function() {
    // Data tables functionality
    initDataTables();

    // Report handling
    initReportSystem();

    // User management
    initUserManagement();

    // Job moderation
    initJobModeration();

    // Admin dashboard widgets
    initDashboardWidgets();
});

function initDataTables() {
    // This would be replaced with a real datatable library in production
    const tables = document.querySelectorAll('.admin-table');
    
    tables.forEach(table => {
        // Add click handlers for sortable headers
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                const column = this.getAttribute('data-sort');
                const isAsc = this.classList.contains('sort-asc');
                
                // Reset all headers
                headers.forEach(h => {
                    h.classList.remove('sort-asc', 'sort-desc');
                });
                
                // Set new sort direction
                this.classList.add(isAsc ? 'sort-desc' : 'sort-asc');
                
                // In a real app, this would call a function to sort the data
                console.log(`Sorting by ${column} ${isAsc ? 'descending' : 'ascending'}`);
            });
        });
    });
}

function initReportSystem() {
    const reportItems = document.querySelectorAll('.report-item');
    
    reportItems.forEach(item => {
        const reviewBtn = item.querySelector('.review-btn');
        if (reviewBtn) {
            reviewBtn.addEventListener('click', function() {
                const reportTitle = item.querySelector('.report-title').textContent;
                // Using custom modal instead of alert
                showCustomModal('Review Report', `You are reviewing: "${reportTitle}"`, 'alert');
                // In a real app, this would open a more detailed modal or redirect
            });
        }
    });
}

function initUserManagement() {
    // User suspension/unsuspension
    document.querySelectorAll('.suspend-btn, .unsuspend-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.closest('tr').querySelector('td:first-child').textContent;
            const action = this.classList.contains('suspend-btn') ? 'suspend' : 'unsuspend';
            const originalText = this.textContent;
            const currentBtn = this;

            // Using custom modal instead of confirm
            showCustomModal(
                `${action.charAt(0).toUpperCase() + action.slice(1)} User`,
                `Are you sure you want to ${action} user ${userId}?`,
                'confirm',
                (confirmed) => {
                    if (confirmed) {
                        // Simulate API call
                        currentBtn.disabled = true;
                        currentBtn.textContent = 'Processing...';
                        
                        setTimeout(() => {
                            showCustomModal('Action Complete', `User ${userId} has been ${action}ed.`, 'alert');
                            // In a real app, this would update the UI
                            currentBtn.textContent = action === 'suspend' ? 'Unsuspend' : 'Suspend';
                            currentBtn.classList.toggle('suspend-btn');
                            currentBtn.classList.toggle('unsuspend-btn');
                            currentBtn.disabled = false;
                        }, 1000);
                    } else {
                        currentBtn.disabled = false;
                        currentBtn.textContent = originalText;
                    }
                }
            );
        });
    });
}

function initJobModeration() {
    // Job approval/rejection
    document.querySelectorAll('.approve-btn, .reject-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const jobId = this.closest('tr').querySelector('td:first-child').textContent;
            const action = this.classList.contains('approve-btn') ? 'approve' : 'reject';
            const row = this.closest('tr');
            const currentBtn = this;
            const originalText = this.textContent;

            // Using custom modal instead of confirm
            showCustomModal(
                `${action.charAt(0).toUpperCase() + action.slice(1)} Job`,
                `Are you sure you want to ${action} job ${jobId}?`,
                'confirm',
                (confirmed) => {
                    if (confirmed) {
                        // Simulate API call
                        currentBtn.disabled = true;
                        currentBtn.textContent = 'Processing...';
                        
                        setTimeout(() => {
                            row.querySelector('.status-badge').textContent = 
                                action === 'approve' ? 'Active' : 'Rejected';
                            row.querySelector('.status-badge').className = 
                                `status-badge status-${action === 'approve' ? 'active' : 'reported'}`;
                            
                            // Remove action buttons
                            row.querySelectorAll('.approve-btn, .reject-btn').forEach(b => b.remove());
                            
                            showCustomModal('Action Complete', `Job ${jobId} has been ${action}d.`, 'alert');
                        }, 1000);
                    } else {
                        currentBtn.disabled = false;
                        currentBtn.textContent = originalText;
                    }
                }
            );
        });
    });
}

function initDashboardWidgets() {
    // This would connect to real data in production
    console.log('Initializing admin dashboard widgets');
    
    // Simulate loading data
    setTimeout(() => {
        // Update stats
        document.querySelectorAll('.stat-value').forEach(stat => {
            if (!stat.textContent.includes('★')) {
                stat.textContent = Math.floor(Math.random() * 1000);
            }
        });
    }, 500);
}

// Making showCustomModal globally accessible for other scripts
window.showCustomModal = showCustomModal;
