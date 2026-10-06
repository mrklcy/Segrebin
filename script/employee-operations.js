// Employee Operations JavaScript
class EmployeeManager {
    constructor() {
        this.initializeEventListeners();
        this.loadEmployees();
    }

    initializeEventListeners() {
        // Add Employee Form Submission
        const newEmployeeForm = document.getElementById('newEmployeeForm');
        if (newEmployeeForm) {
            newEmployeeForm.addEventListener('submit', (e) => this.handleAddEmployee(e));
        }

        // Edit Employee Form Submission
        const editEmployeeForm = document.getElementById('editEmployeeForm');
        if (editEmployeeForm) {
            editEmployeeForm.addEventListener('submit', (e) => this.handleEditEmployee(e));
        }

        // Search functionality
        const searchInput = document.querySelector('.search-bar input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
        }
    }

    async handleAddEmployee(event) {
        event.preventDefault();
        
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('employeeName', document.getElementById('employeeName').value);
        formData.append('employeePosition', document.getElementById('employeePosition').value);
        formData.append('employeeUsername', document.getElementById('employeeUsername').value);
        formData.append('employeePassword', document.getElementById('employeePassword').value);
        formData.append('employeeDate', document.getElementById('employeeDate').value);
        
        // Add profile picture if selected
        const pictureInput = document.getElementById('employeePicture');
        if (pictureInput.files[0]) {
            // Resize and crop the image before uploading
            const resizedImage = await this.resizeAndCropImage(pictureInput.files[0], 200, 200);
            formData.append('employeePicture', resizedImage);
        }

        try {
            const response = await fetch('php/process_employee.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification(result.message, 'success');
                this.closeAddEmployeeModal();
                this.loadEmployees(); // Refresh the table
                this.resetAddEmployeeForm();
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('An error occurred while adding the employee.', 'error');
        }
    }

    async handleEditEmployee(event) {
        event.preventDefault();
        
        const formData = new FormData();
        formData.append('action', 'edit');
        formData.append('employeeId', document.getElementById('editEmployeeId').value);
        formData.append('employeeName', document.getElementById('editEmployeeName').value);
        formData.append('employeePosition', document.getElementById('editEmployeePosition').value);
        
        // Add profile picture if selected
        const pictureInput = document.getElementById('editEmployeePicture');
        if (pictureInput.files[0]) {
            // Resize and crop the image before uploading
            const resizedImage = await this.resizeAndCropImage(pictureInput.files[0], 200, 200);
            formData.append('employeePicture', resizedImage);
        }

        try {
            const response = await fetch('php/process_employee.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                this.showNotification(result.message, 'success');
                this.closeEditEmployeeModal();
                this.loadEmployees(); // Refresh the table
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('An error occurred while updating the employee.', 'error');
        }
    }

    async loadEmployees() {
        try {
            console.log('Loading employees...');
            const formData = new FormData();
            formData.append('action', 'get_all');

            const response = await fetch('php/process_employee.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            console.log('Response from server:', result);
            
            if (result.success) {
                console.log('Employees loaded successfully:', result.employees);
                this.updateEmployeeTable(result.employees);
            } else {
                console.error('Error loading employees:', result.message);
            }
        } catch (error) {
            console.error('Error loading employees:', error);
        }
    }

    updateEmployeeTable(employees) {
        console.log('Updating employee table with:', employees);
        const tbody = document.querySelector('.employee-table tbody');
        if (!tbody) {
            console.error('Table body not found');
            return;
        }

        tbody.innerHTML = '';

        if (employees.length === 0) {
            console.log('No employees to display');
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem;">
                        <div style="color: var(--text-muted);">
                            <i class="fa-solid fa-users" style="font-size: 3rem; margin-bottom: 1rem; display: block;"></i>
                            <h3>No Employees Found</h3>
                            <p>Start by adding your first employee using the "Add Employee" button above.</p>
                        </div>
                    </td>
                </tr>
            `;
        } else {
            console.log(`Creating ${employees.length} employee rows`);
            employees.forEach((employee, index) => {
                console.log(`Creating row ${index + 1} for employee:`, employee);
                const row = this.createEmployeeRow(employee);
                tbody.appendChild(row);
            });
        }

        // Reinitialize password reveal functionality
        this.initializePasswordReveal();
    }

    createEmployeeRow(employee) {
        const row = document.createElement('tr');
        row.setAttribute('data-employee-id', employee.id);
        
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
        
        console.log('Creating row for employee:', {
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
                    <button class="action-btn edit-btn" onclick="employeeManager.editEmployee(${employee.id})" title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="action-btn delete-btn" onclick="employeeManager.removeEmployee(${employee.id}, '${employee.employee_name}')" title="Remove">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </td>
        `;

        return row;
    }

    async removeEmployee(employeeId, employeeName) {
        if (confirm(`Are you sure you want to move ${employeeName} to the archive? This action can be undone later.`)) {
            try {
                console.log('Starting archive process for employee:', employeeName, 'ID:', employeeId);
                const formData = new FormData();
                formData.append('action', 'archive');
                formData.append('employeeId', employeeId);

                const response = await fetch('php/process_employee.php', {
                    method: 'POST',
                    body: formData
                });

                console.log('Archive response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                let result;
                try {
                    result = await response.json();
                    console.log('Archive response result:', result);
                } catch (jsonError) {
                    console.error('Error parsing JSON response:', jsonError);
                    throw new Error('Invalid response from server');
                }
                
                if (result.success) {
                    this.showNotification(result.message, 'success');
                    this.loadEmployees(); // Refresh active employees table
                } else {
                    this.showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error archiving employee:', error);
                this.showNotification('An error occurred while archiving the employee.', 'error');
            }
        }
    }

    editEmployee(employeeId) {
        // Fetch employee data and populate edit form
        this.fetchEmployeeData(employeeId);
        this.openEditEmployeeModal();
    }

    async fetchEmployeeData(employeeId) {
        try {
            console.log('Fetching employee data for ID:', employeeId);
            
            // Get the data from the current table row
            const row = document.querySelector(`tr[data-employee-id="${employeeId}"]`);
            console.log('Found row:', row);
            
            if (row) {
                // Get all the employee data from the row
                const name = row.querySelector('.employee-name')?.textContent || '';
                const position = row.querySelector('.employee-position')?.textContent || '';
                const number = row.querySelector('.employee-number')?.textContent || '';
                const username = row.querySelector('.employee-username')?.textContent || '';
                const originalDate = row.getAttribute('data-original-date') || '';
                
                console.log('Extracted data:', { name, position, number, username, originalDate });
                
                // Validate that we have the required data
                if (!name || !position || !number || !username) {
                    throw new Error('Missing required employee data');
                }
                
                // Get profile image if exists
                const avatarImg = row.querySelector('.employee-avatar img');
                const profileImage = avatarImg ? avatarImg.src : null;
                
                // Populate the edit form fields
                const editEmployeeId = document.getElementById('editEmployeeId');
                const editEmployeeName = document.getElementById('editEmployeeName');
                const editEmployeePosition = document.getElementById('editEmployeePosition');
                const editEmployeeNumber = document.getElementById('editEmployeeNumber');
                const editEmployeeUsername = document.getElementById('editEmployeeUsername');
                const editEmployeeDate = document.getElementById('editEmployeeDate');
                
                if (editEmployeeId) editEmployeeId.value = employeeId;
                if (editEmployeeName) editEmployeeName.value = name;
                if (editEmployeePosition) editEmployeePosition.value = position;
                if (editEmployeeNumber) editEmployeeNumber.value = number;
                if (editEmployeeUsername) editEmployeeUsername.value = username;
                
                // Set the date - use original date if available, otherwise today
                if (originalDate && editEmployeeDate) {
                    editEmployeeDate.value = originalDate;
                    console.log('Set date to original:', originalDate);
                } else if (editEmployeeDate) {
                    const today = new Date();
                    const formattedDate = today.toISOString().split('T')[0];
                    editEmployeeDate.value = formattedDate;
                    console.log('Set date to today:', formattedDate);
                }
                
                // Handle profile image preview
                const editPicturePreview = document.getElementById('editPicturePreview');
                const editPreviewImage = document.getElementById('editPreviewImage');
                
                if (profileImage && editPicturePreview && editPreviewImage) {
                    editPreviewImage.src = profileImage;
                    editPicturePreview.classList.add('has-image');
                    console.log('Set profile image:', profileImage);
                } else if (editPicturePreview) {
                    // Show initial if no image
                    const initial = name.charAt(0);
                    const colors = ['#34d399', '#fbbf24', '#60a5fa', '#a78bfa', '#f87171'];
                    const randomColor = colors[Math.floor(Math.random() * colors.length)];
                    
                    editPicturePreview.style.backgroundColor = randomColor;
                    editPicturePreview.innerHTML = `<span style="font-size: 48px; color: white; font-weight: bold;">${initial}</span>`;
                    editPicturePreview.classList.remove('has-image');
                    console.log('Set initial:', initial, 'with color:', randomColor);
                }
                
                this.showNotification('Employee data loaded for editing', 'info');
                console.log('Employee data successfully loaded for editing');
            } else {
                console.error('Row not found for employee ID:', employeeId);
                this.showNotification('Employee data not found', 'error');
            }
        } catch (error) {
            console.error('Error fetching employee data:', error);
            this.showNotification(`Error loading employee data: ${error.message}`, 'error');
        }
    }

    handleSearch(searchTerm) {
        const rows = document.querySelectorAll('.employee-table tbody tr');
        const searchLower = searchTerm.toLowerCase();

        rows.forEach(row => {
            const name = row.querySelector('.employee-name').textContent.toLowerCase();
            const position = row.querySelector('.employee-position').textContent.toLowerCase();
            const number = row.querySelector('.employee-number').textContent.toLowerCase();
            const username = row.querySelector('.employee-username').textContent.toLowerCase();

            if (name.includes(searchLower) || position.includes(searchLower) || 
                number.includes(searchLower) || username.includes(searchLower)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    initializePasswordReveal() {
        const passwordElements = document.querySelectorAll('.employee-password');
        passwordElements.forEach(element => {
            element.addEventListener('mouseenter', function() {
                this.textContent = '••••••••';
            });
            
            element.addEventListener('mouseleave', function() {
                this.textContent = '••••••••';
            });
        });
    }

    resetAddEmployeeForm() {
        const form = document.getElementById('newEmployeeForm');
        if (form) {
            form.reset();
            const picturePreview = document.getElementById('picturePreview');
            if (picturePreview) {
                picturePreview.classList.remove('has-image');
            }
        }
    }

    resizeAndCropImage(file, targetWidth, targetHeight) {
        return new Promise((resolve) => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            
            img.onload = () => {
                // Set canvas dimensions
                canvas.width = targetWidth;
                canvas.height = targetHeight;
                
                // Calculate dimensions to maintain aspect ratio and center crop
                const scale = Math.max(targetWidth / img.width, targetHeight / img.height);
                const scaledWidth = img.width * scale;
                const scaledHeight = img.height * scale;
                
                // Calculate position to center the image
                const x = (targetWidth - scaledWidth) / 2;
                const y = (targetHeight - scaledHeight) / 2;
                
                // Clear canvas and draw the resized and cropped image
                ctx.clearRect(0, 0, targetWidth, targetHeight);
                ctx.drawImage(img, x, y, scaledWidth, scaledHeight);
                
                // Convert to blob
                canvas.toBlob((blob) => {
                    // Create a new file with the resized image
                    const resizedFile = new File([blob], file.name, {
                        type: 'image/jpeg',
                        lastModified: Date.now()
                    });
                    resolve(resizedFile);
                }, 'image/jpeg', 0.9);
            };
            
            img.src = URL.createObjectURL(file);
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

    // Modal functions
    openAddEmployeeModal() {
        const modal = document.getElementById('addEmployeeModal');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Reset form and picture preview
        this.resetAddEmployeeForm();
        
        // Auto-generate employee number
        this.generateEmployeeNumber();
    }

    closeAddEmployeeModal() {
        const modal = document.getElementById('addEmployeeModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    openEditEmployeeModal() {
        const modal = document.getElementById('editEmployeeModal');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Reset the picture preview to default state
        const editPicturePreview = document.getElementById('editPicturePreview');
        if (editPicturePreview) {
            editPicturePreview.innerHTML = '<i class="fa-solid fa-camera fa-2x"></i>';
            editPicturePreview.classList.remove('has-image');
            editPicturePreview.style.backgroundColor = '';
        }
    }

    closeEditEmployeeModal() {
        const modal = document.getElementById('editEmployeeModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
        
        // Reset the edit form
        this.resetEditEmployeeForm();
    }
    
    resetEditEmployeeForm() {
        const form = document.getElementById('editEmployeeForm');
        if (form) {
            form.reset();
            
            // Reset picture preview
            const editPicturePreview = document.getElementById('editPicturePreview');
            if (editPicturePreview) {
                editPicturePreview.innerHTML = '<i class="fa-solid fa-camera fa-2x"></i>';
                editPicturePreview.classList.remove('has-image');
                editPicturePreview.style.backgroundColor = '';
            }
            
            // Clear hidden fields
            document.getElementById('editEmployeeId').value = '';
        }
    }

    generateEmployeeNumber() {
        const rows = document.querySelectorAll('.employee-table tbody tr');
        const lastEmpNumber = rows.length;
        const newEmpNumber = lastEmpNumber + 1;
        const formattedEmpNumber = 'EMP-' + String(newEmpNumber).padStart(3, '0');
        
        const employeeNumberInput = document.getElementById('employeeNumber');
        if (employeeNumberInput) {
            employeeNumberInput.value = formattedEmpNumber;
        }
    }


}

// Initialize Employee Manager when DOM is loaded
let employeeManager;
document.addEventListener('DOMContentLoaded', function() {
    employeeManager = new EmployeeManager();
});

// Global functions for modal operations
function openAddEmployeeModal() {
    if (employeeManager) {
        employeeManager.openAddEmployeeModal();
    }
}

function closeAddEmployeeModal() {
    if (employeeManager) {
        employeeManager.closeAddEmployeeModal();
    }
}

function openEditEmployeeModal() {
    if (employeeManager) {
        employeeManager.openEditEmployeeModal();
    }
}

function closeEditEmployeeModal() {
    if (employeeManager) {
        employeeManager.closeEditEmployeeModal();
    }
}

// Global function for editing employees
function editEmployee(employeeId) {
    if (employeeManager) {
        employeeManager.editEmployee(employeeId);
    }
}

// Global function for removing employees
function removeEmployee(employeeId, employeeName) {
    if (employeeManager) {
        employeeManager.removeEmployee(employeeId, employeeName);
    }
}


