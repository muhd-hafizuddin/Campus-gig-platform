/**
 * JomBantu - Admin JavaScript
 * Handles admin panel functionality
 */

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
                alert(`Reviewing report: ${reportTitle}`);
                // In a real app, this would open a modal or redirect
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
            
            if (confirm(`Are you sure you want to ${action} user ${userId}?`)) {
                // Simulate API call
                this.disabled = true;
                this.textContent = 'Processing...';
                
                setTimeout(() => {
                    alert(`User ${userId} has been ${action}ed`);
                    // In a real app, this would update the UI
                    this.textContent = action === 'suspend' ? 'Unsuspend' : 'Suspend';
                    this.classList.toggle('suspend-btn');
                    this.classList.toggle('unsuspend-btn');
                    this.disabled = false;
                }, 1000);
            }
        });
    });
}

function initJobModeration() {
    // Job approval/rejection
    document.querySelectorAll('.approve-btn, .reject-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const jobId = this.closest('tr').querySelector('td:first-child').textContent;
            const action = this.classList.contains('approve-btn') ? 'approve' : 'reject';
            
            if (confirm(`Are you sure you want to ${action} job ${jobId}?`)) {
                // Simulate API call
                const row = this.closest('tr');
                this.disabled = true;
                this.textContent = 'Processing...';
                
                setTimeout(() => {
                    row.querySelector('.status-badge').textContent = 
                        action === 'approve' ? 'Active' : 'Rejected';
                    row.querySelector('.status-badge').className = 
                        `status-badge status-${action === 'approve' ? 'active' : 'reported'}`;
                    
                    // Remove action buttons
                    row.querySelectorAll('.approve-btn, .reject-btn').forEach(b => b.remove());
                    
                    alert(`Job ${jobId} has been ${action}d`);
                }, 1000);
            }
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

// Modal system for admin actions
function openModal(title, content) {
    const modal = document.createElement('div');
    modal.className = 'admin-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>${title}</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">${content}</div>
            <div class="modal-footer">
                <button class="btn btn-secondary close-modal">Cancel</button>
                <button class="btn btn-primary confirm-action">Confirm</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    // Add event listeners
    modal.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', () => closeModal(modal));
    });
    
    modal.querySelector('.confirm-action').addEventListener('click', () => {
        alert('Action confirmed!');
        closeModal(modal);
    });
}

function closeModal(modal) {
    modal.remove();
    document.body.style.overflow = '';
}