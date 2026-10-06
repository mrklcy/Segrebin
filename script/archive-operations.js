// Archive Operations JavaScript
class ArchiveManager {
    constructor() {
        this.initializeEventListeners();
        this.loadArchivedEmployees();
    }

    initializeEventListeners() {
        // Search functionality
        const searchInput = document.querySelector('.search-bar input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
        }
    }

    async loadArchivedEmployees() {
        try {
            console.log('Loading archived employees...');
            const formData = new FormData();
            formData.append('action', 'get_archived');

            const response = await fetch('php/process_employee.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            console.log('Archived employees response:', result);
            
            if (result.success) {
                console.log('Archived employees loaded successfully:', result.employees);
                this.updateArchiveTable(result.employees);
            } else {
                console.error('Error loading archived employees:', result.message);
            }
        } catch (error) {
            console.error('Error loading archived employees:', error);
        }
    }

    updateArchiveTable(employees) {
        console.log('Updating archive table with:', employees);
        const archiveTbody = document.querySelector('#archiveTable tbody') || document.querySelector('.employee-table tbody');
        if (!archiveTbody) {
            console.error('Archive table body not found');
            return;
        }

        archiveTbody.innerHTML = '';

        if (employees.length === 0) {
            console.log('No archived employees to display');
            archiveTbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem;">
                        <div style="color: var(--text-muted);">
                            <i class="fa-solid fa-archive" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                            <h3>No Archived Employees</h3>
                            <p>The archive is empty. Deleted employees will appear here.</p>
                        </div>
                    </td>
                </tr>
            `;
        } else {
            console.log(`Creating ${employees.length} archived employee rows`);
            employees.forEach((employee, index) => {
                console.log(`Creating archived row ${index + 1} for employee:`, employee);
                const row = this.createArchivedEmployeeRow(employee);
                archiveTbody.appendChild(row);
            });
        }
    }

    createArchivedEmployeeRow(employee) {
        const row = document.createElement('tr');
        row.setAttribute('data-archive-id', employee.id);
        
        // Format date for display
        const date = new Date(employee.date_employed);
        const formattedDate = date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
        
        // Store the original date for editing (YYYY-MM-DD format)
        const originalDate = employee.date_employed;
        row.setAttribute('data-original-date', originalDate);
        
        console.log('Creating archived row for employee:', {
            id: employee.id,
            name: employee.employee_name,
            originalDate: originalDate,
            formattedDate: formattedDate
        });

        // Create profile image or initial
        let profileImage = '';
        if (employee.profile_image) {
            profileImage = `<div class="employee-avatar"><img src="${employee.profile_image}" alt="${employee.employee_name}"></div>`;
        } else {
            const initial = employee.employee_name.charAt(0);
            const colors = ['#34d399', '#fbbf24', '#60a5fa', '#a78bfa', '#f87171'];
            const randomColor = colors[Math.floor(Math.random() * colors.length)];
            profileImage = `<div class="employee-avatar" style="background-color: ${randomColor};"><span>${initial}</span></div>`;
        }

        row.innerHTML = `
            <td>
                <div class="employee-col">
                    ${profileImage}
                    <div class="employee-name">${employee.employee_name}</div>
                </div>
            </td>
            <td>
                <div class="employee-position">${employee.position}</div>
            </td>
            <td>
                <div class="employee-number">${employee.employee_number}</div>
            </td>
            <td>
                <div class="employee-username">${employee.username}</div>
            </td>
            <td>
                <div class="employee-password" data-password="••••••••">••••••••</div>
            </td>
            <td>${formattedDate}</td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn restore-btn" onclick="archiveManager.restoreEmployee(${employee.id}, '${employee.employee_name}')" title="Restore" style="background: #10b981; color: white;">
                        <i class="fa-solid fa-undo"></i>
                    </button>
                    <button class="action-btn view-btn" onclick="archiveManager.viewArchivedEmployee(${employee.id})" title="View Details" style="background: #3b82f6; color: white;">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </td>
        `;

        return row;
    }

    async restoreEmployee(archiveId, employeeName) {
        if (confirm(`Are you sure you want to restore ${employeeName}? This will move them back to the active employees list.`)) {
            try {
                const formData = new FormData();
                formData.append('action', 'restore');
                formData.append('archiveId', archiveId);

                const response = await fetch('php/process_employee.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    this.showNotification(result.message, 'success');
                    this.loadArchivedEmployees(); // Refresh archive table
                    // Redirect to employees page after successful restore
                    setTimeout(() => {
                        window.location.href = 'employees.php';
                    }, 1500);
                } else {
                    this.showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showNotification('An error occurred while restoring the employee.', 'error');
            }
        }
    }

    viewArchivedEmployee(archiveId) {
        // This could open a modal to show archived employee details
        this.showNotification('View archived employee details - Feature coming soon!', 'info');
    }

    handleSearch(searchTerm) {
        const rows = document.querySelectorAll('.archive-table tbody tr');
        const searchLower = searchTerm.toLowerCase();

        rows.forEach(row => {
            // Skip the "no employees" row
            if (row.querySelector('td[colspan]')) {
                return;
            }

            const name = row.querySelector('.employee-name')?.textContent?.toLowerCase() || '';
            const position = row.querySelector('.employee-position')?.textContent?.toLowerCase() || '';
            const number = row.querySelector('.employee-number')?.textContent?.toLowerCase() || '';
            const username = row.querySelector('.employee-username')?.textContent?.toLowerCase() || '';

            if (name.includes(searchLower) || position.includes(searchLower) || 
                number.includes(searchLower) || username.includes(searchLower)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fa-solid fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;

        // Add to page
        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => notification.classList.add('show'), 100);

        // Remove after 5 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }
}

// Initialize Archive Manager when DOM is loaded
let archiveManager;
document.addEventListener('DOMContentLoaded', function() {
    archiveManager = new ArchiveManager();
});

// Global function for loading archived employees
function loadArchivedEmployees() {
    if (archiveManager) {
        archiveManager.loadArchivedEmployees();
    }
}

// Global function for restoring archived employees
function restoreEmployee(archiveId, employeeName) {
    if (archiveManager) {
        archiveManager.restoreEmployee(archiveId, employeeName);
    }
}
