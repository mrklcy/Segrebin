<?php
session_start();
require_once 'php/config.php';  // Updated path to config.php in the php folder

if (!isset($_SESSION['admin'])) {
    header("Location: login/login.php");
    exit;
}

// Check if database connection is working
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
}

// Fetch admin data
$admin_username = $_SESSION['admin']; // Fixed: use 'admin' not 'username'
$stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
$stmt->bind_param("s", $admin_username);
$stmt->execute();
$admin_data = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SEGREBIN</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/employees.css">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>
<div class="container">
<!-- Sidebar -->
<aside class="sidebar">
  <div class="logo">
      <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3QgeD0iMyIgeT0iMyIgd2lkdGg9IjE4IiBoZWlnaHQ9IjE4IiByeD0iMiIgc3Ryb2tlPSIjMzMzMzMzIiBzdHJva2Utd2lkdGg9IjIiLz4KPHJlY3QgeD0iNyIgeT0iNyIgd2lkdGg9IjQiIGhlaWdodD0iNCIgZmlsbD0iIzMzMzMzMyIvPgo8cmVjdCB4PSIxMyIgeT0iNyIgd2lkdGg9IjQiIGhlaWdodD0iNCIgZmlsbD0iIzMzMzMzMyIvPgo8cmVjdCB4PSI3IiB5PSIxMyIgd2lkdGg9IjQiIGhlaWdodD0iNCIgZmlsbD0iIzMzMzMzMyIvPgo8cmVjdCB4PSIxMyIgeT0iMTMiIHdpZHRoPSI0IiBoZWlnaHQ9IjQiIGZpbGw9IiMzMzMzMzMiLz4KPC9zdmc+" alt="Logo">
    <span>SEGREBIN</span>
  </div>
 <nav class="nav-menu">
  <ul>
      <li>
          <a href="admin.php">
              <i class="fa-solid fa-home"></i>
              <span>Dashboard</span>
          </a>
      </li>
      <li>
          <a href="analytics.php">
              <i class="fa-solid fa-chart-line"></i>
              <span>Analytics</span>
          </a>
      </li>
      <li class="active">
          <a href="employees.php">
              <i class="fa-solid fa-users"></i>
              <span>Employees</span>
          </a>
      </li>
       <li>
        <a href="archive.php">
          <i class="fa-solid fa-box-archive"></i>
          <span>Archive</span>
        </a>
      </li>
  </ul>

  <div class="nav-section">
      <h3>ACCOUNT PAGES</h3>
      <ul>
          <li>
              <a href="profile.php">
                  <i class="fa-solid fa-user"></i>
                  <span>Profile</span>
              </a>
          </li>
          <li>
              <a href="#" id="logoutBtn">
                  <i class="fa-solid fa-arrow-right-from-bracket"></i>
                  <span>Logout</span>
              </a>
          </li>
      </ul>
  </div>
</nav>
</aside>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Main Content -->
<main class="main-content">
  <!-- Header -->
  <header class="header">
    <div class="header-left">
      <h1>Employee Directory</h1>
      <div class="search-bar">
        <i class="fa-solid fa-search"></i>
        <input type="text" placeholder="Search employees...">
      </div>
    </div>
    <div class="header-right">
      <div class="user-menu">
        <div class="user-profile" id="userProfileTrigger">
            <div class="avatar-container">
                <div class="avatar">
                    <div class="avatar-dot"></div>
                </div>
                <!-- Removed notification-badge from avatar -->
            </div>
            <div class="user-name"><?php echo htmlspecialchars($admin_data['username']); ?></div>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
        <button class="btn-icon notification-btn">
            <i class="fa-solid fa-bell"></i>
            <span class="notification-badge">3</span>
        </button>
        <button class="btn-icon">
          <i class="fa-solid fa-gear"></i>
        </button>
      </div>
    </div>
    <button class="mobile-menu-toggle">
      <i class="fa-solid fa-bars"></i>
    </button>

    <!-- User Dropdown Menu -->
    <div class="user-dropdown" id="userDropdown">
      <div class="dropdown-header">
        <div class="dropdown-avatar">
          <i class="fa-solid fa-user"></i>
        </div>
        <div class="dropdown-user-info">
        <div class="dropdown-user-name"><?php echo htmlspecialchars($admin_data['username']); ?></div>
            <div class="dropdown-user-email"><?php echo htmlspecialchars($admin_data['email'] ?? 'admin@example.com'); ?></div>
          </div>
      </div>
      <div class="dropdown-menu">
        <a href="#" class="dropdown-menu-item" onclick="viewProfile()">
          <i class="fa-regular fa-user"></i>
          <span>View Profile</span>
        </a>
        <a href="#" class="dropdown-menu-item">
            <i class="fa-solid fa-gear"></i>
            <span>Account Settings</span>
          </a>
        <a href="#" class="dropdown-menu-item" id="dropdownLogoutBtn">
          <i class="fa-solid fa-arrow-right-from-bracket"></i>
          <span>Logout</span>
        </a>
      </div>
    </div>
  </header>

  <!-- Employee Content -->
  <div class="employee-content">
    <div class="employee-table-container">
      <div class="table-header">
        <h2 class="table-title">Employee List</h2>
        <button class="add-employee-btn" onclick="openAddEmployeeModal()">
          <i class="fa-solid fa-plus"></i> Add Employee
        </button>
      </div>
      <table class="employee-table">
        <thead>
          <tr>
            <th>EMPLOYEE NAME <i class="fa-solid fa-sort"></i></th>
            <th>POSITION <i class="fa-solid fa-sort"></i></th>
            <th>EMPLOYEE NUMBER <i class="fa-solid fa-sort"></i></th>
            <th>USERNAME <i class="fa-solid fa-sort"></i></th>
            <th>PASSWORD <i class="fa-solid fa-sort"></i></th>
            <th>DATE EMPLOYED <i class="fa-solid fa-sort"></i></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <!-- Employee data will be loaded dynamically from the database -->
        </tbody>
      </table>
    </div>
  </div>



  <!-- Footer -->
  <footer class="footer">
    <div class="footer-left">
        <p>© 2025, made by <a href="#">SEGRE TEAM</a><i class="fa-solid fa-dumpster"></i></p>
    </div>
    <div class="footer-right">
      <a href="#">SEGRE TEAM</a>
      <a href="#">About Us</a>
      <a href="#">Blog</a>
      <a href="#">License</a>
    </div>
  </footer>
</main>
</div>

<!-- Add Employee Modal -->
<div class="modal-overlay" id="addEmployeeModal">
  <button class="modal-close" onclick="closeAddEmployeeModal()">
    <i class="fa-solid fa-times"></i>
  </button>
  <div class="modal">
    <div class="add-employee-form">
      <h2 class="form-title">Add New Employee</h2>
      <form id="newEmployeeForm">
        <!-- Picture Upload Section -->
        <div class="picture-upload">
          <div class="picture-preview" id="picturePreview" onclick="document.getElementById('employeePicture').click()">
            <i class="fa-solid fa-camera fa-2x"></i>
            <img id="previewImage" src="#" alt="Employee Picture">
            <div class="overlay">
              <i class="fa-solid fa-pen fa-lg"></i>
            </div>
          </div>
          <input type="file" id="employeePicture" class="picture-input" accept="image/*">
          <label class="picture-label">Click to upload employee picture</label>
        </div>
        
        <div class="form-group">
          <label for="employeeName" class="form-label">Employee Name</label>
          <input type="text" id="employeeName" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="employeePosition" class="form-label">Position</label>
          <select id="employeePosition" class="form-select" required>
            <option value="">Select Position</option>
            <option value="Driver">Driver</option>
            <option value="Waste Collector">Waste Collector</option>
            <option value="Admin">Admin</option>
            <option value="Supervisor">Supervisor</option>
            <option value="Mechanic">Mechanic</option>
          </select>
        </div>
        <div class="form-group">
          <label for="employeeNumber" class="form-label">Employee Number</label>
          <input type="text" id="employeeNumber" class="form-control" readonly value="Auto-generated">
        </div>
        <div class="form-group">
          <label for="employeeUsername" class="form-label">Username</label>
          <input type="text" id="employeeUsername" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="employeePassword" class="form-label">Password</label>
          <input type="password" id="employeePassword" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="employeeDate" class="form-label">Date Employed</label>
          <input type="date" id="employeeDate" class="form-control" required>
        </div>
        <div class="form-actions">
          <button type="button" class="btn-cancel" onclick="closeAddEmployeeModal()">Cancel</button>
          <button type="submit" class="btn-submit">Add Employee</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal-overlay" id="editEmployeeModal">
  <button class="modal-close" onclick="closeEditEmployeeModal()">
    <i class="fa-solid fa-times"></i>
  </button>
  <div class="modal">
    <div class="add-employee-form">
      <h2 class="form-title">Edit Employee</h2>
      <form id="editEmployeeForm">
        <!-- Picture Upload Section -->
        <div class="picture-upload">
          <div class="picture-preview has-image" id="editPicturePreview" onclick="document.getElementById('editEmployeePicture').click()">
            <i class="fa-solid fa-camera fa-2x"></i>
            <img id="editPreviewImage" src="#" alt="Employee Picture">
            <div class="overlay">
              <i class="fa-solid fa-pen fa-lg"></i>
            </div>
          </div>
          <input type="file" id="editEmployeePicture" class="picture-input" accept="image/*">
          <label class="picture-label">Click to change employee picture</label>
        </div>
        
        <input type="hidden" id="editEmployeeId">
        <div class="form-group">
          <label for="editEmployeeName" class="form-label">Employee Name</label>
          <input type="text" id="editEmployeeName" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="editEmployeePosition" class="form-label">Position</label>
          <select id="editEmployeePosition" class="form-select" required>
            <option value="">Select Position</option>
            <option value="Driver">Driver</option>
            <option value="Waste Collector">Waste Collector</option>
            <option value="Admin">Admin</option>
            <option value="Supervisor">Supervisor</option>
            <option value="Mechanic">Mechanic</option>
          </select>
        </div>
        <div class="form-group">
          <label for="editEmployeeNumber" class="form-label">Employee Number</label>
          <input type="text" id="editEmployeeNumber" class="form-control" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
        </div>
        <div class="form-group">
          <label for="editEmployeeUsername" class="form-label">Username</label>
          <input type="text" id="editEmployeeUsername" class="form-control" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
        </div>
        <div class="form-group">
          <label for="editEmployeePassword" class="form-label">Password</label>
          <input type="password" id="editEmployeePassword" class="form-control" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
        </div>
        <div class="form-group">
          <label for="editEmployeeDate" class="form-label">Date Employed</label>
          <input type="date" id="editEmployeeDate" class="form-control" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
        </div>
        <div class="form-actions">
          <button type="button" class="btn-cancel" onclick="closeEditEmployeeModal()">Cancel</button>
          <button type="submit" class="btn-submit">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Logout Confirmation Modal -->
<div class="logout-modal" id="logoutModal">
  <div class="logout-modal-content">
      <div class="logout-modal-gif">
          <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/cute-cat-cry-KUNBbQzJlFR6KE0O2nFvUPN6QkAmJp.gif" alt="Cute cat crying" width="150">
      </div>
      <h2 class="logout-modal-title">Are you sure?</h2>
      <p>Do you really want to logout from your account?</p>
      <div class="logout-modal-buttons">
          <button class="logout-modal-btn logout-modal-btn-cancel" id="cancelLogout">Cancel</button>
          <button class="logout-modal-btn logout-modal-btn-confirm" id="confirmLogout">Yes, Logout</button>
      </div>
  </div>
</div>

<!-- See You Next Time Modal -->
<div class="goodbye-modal" id="goodbyeModal">
  <div class="goodbye-modal-content">
      <div class="goodbye-modal-img">
          <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/see-you-1024x1024-xL1YmsjjRBMbKQSzAiFzyG1xXpS62u.gif" alt="See You Next Time">
      </div>
  </div>
</div>

<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
<script src="script/employee-operations.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Mobile menu toggle
  const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
  const sidebar = document.querySelector('.sidebar');
  const sidebarOverlay = document.getElementById('sidebarOverlay');

  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', function() {
      sidebar.classList.toggle('active');
      if (sidebarOverlay) {
        sidebarOverlay.classList.toggle('active');
      }
    });
  }

  // Close sidebar when clicking overlay
  if (sidebarOverlay) {
    sidebarOverlay.addEventListener('click', function() {
      sidebar.classList.remove('active');
      sidebarOverlay.classList.remove('active');
    });
  }

  // Close sidebar when clicking outside on mobile
  document.addEventListener('click', function(e) {
    if (window.innerWidth <= 992 && 
        !sidebar.contains(e.target) && 
        !mobileMenuToggle.contains(e.target)) {
      sidebar.classList.remove('active');
      if (sidebarOverlay) {
        sidebarOverlay.classList.remove('active');
      }
    }
  });

  // User profile dropdown toggle
  const userProfileTrigger = document.getElementById('userProfileTrigger');
  const userDropdown = document.getElementById('userDropdown');

  function positionDropdown() {
      if (!userProfileTrigger || !userDropdown) return;

      const rect = userProfileTrigger.getBoundingClientRect();
      const dropdownWidth = userDropdown.offsetWidth || 280;
      const dropdownHeight = userDropdown.offsetHeight || 200;

      let left = rect.left + (rect.width / 2) - (dropdownWidth / 2);
      let top = rect.bottom + 8;

      // Responsive adjustment for mobile
      if (window.innerWidth <= 992) {
          left = Math.max(8, rect.left);
          top = rect.bottom + 8;
          userDropdown.style.width = '90vw';
          userDropdown.style.maxWidth = '320px';
      } else {
          userDropdown.style.width = '250px';
          userDropdown.style.maxWidth = '';
      }

      // Prevent overflow right
      if (left + dropdownWidth > window.innerWidth - 8) {
          left = window.innerWidth - dropdownWidth - 8;
      }
      // Prevent overflow left
      if (left < 8) {
          left = 8;
      }
      // Prevent overflow top
      if (top + dropdownHeight > window.innerHeight - 8) {
          top = window.innerHeight - dropdownHeight - 8;
      }

      userDropdown.style.left = `${left}px`;
      userDropdown.style.top = `${top}px`;
    }

  if (userProfileTrigger && userDropdown) {
    userProfileTrigger.addEventListener('click', function(e) {
      e.stopPropagation();
      userDropdown.classList.toggle('active');
      positionDropdown();
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!userProfileTrigger.contains(e.target) && !userDropdown.contains(e.target)) {
        userDropdown.classList.remove('active');
      }
    });
  }

  // Picture upload preview with resizing
  const employeePicture = document.getElementById('employeePicture');
  const picturePreview = document.getElementById('picturePreview');
  const previewImage = document.getElementById('previewImage');
  
  if (employeePicture) {
    employeePicture.addEventListener('change', function(event) {
      if (event.target.files && event.target.files[0]) {
        resizeAndPreviewImage(event.target.files[0], previewImage, picturePreview);
      }
    });
  }
  
  // Edit picture upload preview with resizing
  const editEmployeePicture = document.getElementById('editEmployeePicture');
  const editPicturePreview = document.getElementById('editPicturePreview');
  const editPreviewImage = document.getElementById('editPreviewImage');
  
  if (editEmployeePicture) {
    editEmployeePicture.addEventListener('change', function(event) {
      if (event.target.files && event.target.files[0]) {
        resizeAndPreviewImage(event.target.files[0], editPreviewImage, editPicturePreview);
      }
    });
  }

  // Logout confirmation modal functionality
  const logoutModal = document.getElementById('logoutModal');
  const goodbyeModal = document.getElementById('goodbyeModal');
  const logoutBtn = document.getElementById('logoutBtn');
  const dropdownLogoutBtn = document.getElementById('dropdownLogoutBtn');
  const cancelLogoutBtn = document.getElementById('cancelLogout');
  const confirmLogoutBtn = document.getElementById('confirmLogout');

  // Show modal when logout button is clicked
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
      e.preventDefault();
      logoutModal.classList.add('active');
    });
  }

  // Also show modal when logout is clicked from dropdown
  if (dropdownLogoutBtn) {
    dropdownLogoutBtn.addEventListener('click', function(e) {
      e.preventDefault();
      logoutModal.classList.add('active');
      userDropdown.classList.remove('active'); // Close the dropdown
    });
  }

  // Hide modal when cancel button is clicked
  if (cancelLogoutBtn) {
    cancelLogoutBtn.addEventListener('click', function() {
      logoutModal.classList.remove('active');
    });
  }

  // Handle logout confirmation
  if (confirmLogoutBtn) {
    confirmLogoutBtn.addEventListener('click', function() {
      // Hide the logout confirmation modal
      logoutModal.classList.remove('active');
      
      // Redirect to logout.php which will handle the session destruction
      window.location.href = "logout.php";
    });
  }

  // Close sidebar on click outside when minimized (<=992px)
  document.addEventListener('click', function(e) {
    if (
      sidebar.classList.contains('active') &&
      window.innerWidth <= 992 &&
      !sidebar.contains(e.target) &&
      !mobileMenuToggle.contains(e.target)
    ) {
      sidebar.classList.remove('active');
    }
  });
});

// User profile function for dropdown menu
function viewProfile() {
  alert('Viewing your profile');
  // In a real implementation, this would navigate to the profile page
  window.location.href = 'profile.html';
}

// Modal click outside to close
document.getElementById('addEmployeeModal').addEventListener('click', function(event) {
  if (event.target === this) {
    closeAddEmployeeModal();
  }
});

document.getElementById('editEmployeeModal').addEventListener('click', function(event) {
  if (event.target === this) {
    closeEditEmployeeModal();
  }
});

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeAddEmployeeModal();
    closeEditEmployeeModal();
  }
});

// Function to resize and preview images
function resizeAndPreviewImage(file, previewElement, previewContainer) {
  const canvas = document.createElement('canvas');
  const ctx = canvas.getContext('2d');
  const img = new Image();
  
  img.onload = function() {
    // Set canvas dimensions for preview (150x150 for modal preview)
    const previewSize = 150;
    canvas.width = previewSize;
    canvas.height = previewSize;
    
    // Calculate dimensions to maintain aspect ratio and center crop
    const scale = Math.max(previewSize / img.width, previewSize / img.height);
    const scaledWidth = img.width * scale;
    const scaledHeight = img.height * scale;
    
    // Calculate position to center the image
    const x = (previewSize - scaledWidth) / 2;
    const y = (previewSize - scaledHeight) / 2;
    
    // Clear canvas and draw the resized and cropped image
    ctx.clearRect(0, 0, previewSize, previewSize);
    ctx.drawImage(img, x, y, scaledWidth, scaledHeight);
    
    // Set the preview image
    previewElement.src = canvas.toDataURL('image/jpeg', 0.9);
    previewContainer.classList.add('has-image');
  };
  
  img.src = URL.createObjectURL(file);
}
</script>
</body>
</html>