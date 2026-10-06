<?php
session_start();

// Check session or "Remember Me" cookie
if (!isset($_SESSION['admin'])) {
    header("Location: login/login.php");
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SEGREBIN</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="css/profile.css">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>
<div class="container">
  <aside class="sidebar">
            <div class="logo">
          <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3QgeD0iMyIgeT0iMyIgd2lkdGg9IjE4IiBoZWlnaHQ9IjE4IiByeD0iMiIgc3Ryb2tlPSIjMzMzMzMzIiBzdHJva2Utd2lkdGg9IjIiLz4KPHJlY3QgeD0iNyIgeT0iNyIgd2lkdGg9IjQiIGhlaWdodD0iNCIgZmlsbD0iIzMzMzMzMyIvPgo8cmVjdCB4PSIxMyIgeT0iNyIgd2lkdGg9IjQiIGhlaWdodD0iNCIgZmlsbD0iIzMzMzMzMyIvPgo8cmVjdCB4PSI3IiB5PSIxMyIgd2lkdGg9IjQiIGhlaWdodD0iNCIgZmlsbD0iIzMzMzMzMyIvPgo8cmVjdCB4PSIxMyIgeT0iMTMiIHdpZHRoPSI0IiBoZWlnaHQ9IjQiIGZpbGw9IiMzMzMzMzMiLz4KPC9zdmc+" alt="Logo">
          <span>SEGREBIN</span>
        </div>
    <nav class="nav-menu">
      <ul>
        <li><a href="admin.php"><i class="fa-solid fa-home"></i><span>Dashboard</span></a></li>
        <li><a href="analytics.php"><i class="fa-solid fa-chart-line"></i><span>Analytics</span></a></li>
        <li><a href="employees.php"><i class="fa-solid fa-users"></i><span>Employees</span></a></li>
        <li><a href="archive.php"><i class="fa-solid fa-box-archive"></i><span>Archive</span></a></li>
      </ul>
      <div class="nav-section">
        <h3>ACCOUNT PAGES</h3>
        <ul>
          <li class="active"><a href="profile.php"><i class="fa-solid fa-user"></i><span>Profile</span></a></li>
          <li><a href="logout.php" id="logoutBtn"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Logout</span></a></li>
        </ul>
      </div>
    </nav>
  </aside>

  <main class="main-content">
    <header class="header">
      <div class="header-left"><h1>User Profile</h1></div>
      <div class="header-right">
        <div class="user-menu">
          <div class="user-profile" id="userProfileTrigger">
            <div class="avatar-container">
              <div class="avatar">
                <div class="avatar-dot"></div>
              </div>
            </div>
            <div class="user-name"><?php echo htmlspecialchars(explode(' ', $_SESSION['admin_name'])[0]); ?></div>
            <i class="fa-solid fa-chevron-down"></i>
          </div>
          <button class="btn-icon notification-btn">
            <i class="fa-solid fa-bell"></i>
            <span class="notification-badge">3</span>
          </button>
          <button class="btn-icon"><i class="fa-solid fa-gear"></i></button>
        </div>
      </div>
      <button class="mobile-menu-toggle"><i class="fa-solid fa-bars"></i></button>

      <div class="user-dropdown" id="userDropdown">
        <div class="dropdown-header">
          <div class="dropdown-avatar"><i class="fa-solid fa-user"></i></div>
          <div class="dropdown-user-info">
            <div class="dropdown-user-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
            <div class="dropdown-user-email"><?php echo htmlspecialchars($_SESSION['admin_email']); ?></div>
          </div>
        </div>
        <div class="dropdown-menu">
          <a href="profile.php" class="dropdown-menu-item"><i class="fa-regular fa-user"></i><span>View Profile</span></a>
          <a href="#" class="dropdown-menu-item"><i class="fa-solid fa-gear"></i><span>Account Settings</span></a>
          <a href="logout.php" class="dropdown-menu-item"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Logout</span></a>
        </div>
      </div>
    </header>

    <!-- Main profile content here (same as your existing HTML structure) -->
    <!-- Profile Content -->
    <div class="profile-content">
      <!-- Profile Sidebar -->
      <div class="profile-sidebar">
        <div class="profile-avatar" id="profileAvatarContainer">
          <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-a39BP6mgkVlhRgEVf4pHqXPp6bFClU.png" alt="John Smith" id="profileImage">
          <div class="avatar-edit-overlay">
            <i class="fa-solid fa-pen-to-square"></i>
          </div>
        </div>
        <h2 class="profile-name">John Smith</h2>
        <div class="profile-title">Waste Management Supervisor</div>
        <div class="profile-location">Bay Area, San Francisco, CA</div>
        
        <div class="profile-stats">
          <div class="stat-item">
            <div class="stat-value">42</div>
            <div class="stat-label">BINS MANAGED</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">8</div>
            <div class="stat-label">ROUTES</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">95%</div>
            <div class="stat-label">EFFICIENCY</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">3</div>
            <div class="stat-label">YEARS</div>
          </div>
        </div>
        
        <div class="profile-contact">
          <div class="contact-item">
            <div class="contact-icon">
              <i class="fa-solid fa-envelope"></i>
            </div>
            <div class="contact-info">johnsmith@example.com</div>
          </div>
          
          <div class="contact-item">
            <div class="contact-icon">
              <i class="fa-solid fa-mobile-screen"></i>
            </div>
            <div class="contact-info">(098) 765-4321</div>
          </div>
          <div class="contact-item">
            <div class="contact-icon">
              <i class="fa-solid fa-location-dot"></i>
            </div>
            <div class="contact-info">Bay Area, San Francisco, CA</div>
          </div>
        </div>
      </div>
      
      <!-- Profile Main Content -->
      <div class="profile-main">
        <!-- Personal Information -->
        <div class="profile-details">
          <div class="section-title">
            <div class="section-title-text">
              <i class="fa-solid fa-user"></i> Personal Information
            </div>
            <button class="edit-button" id="editPersonalBtn">
              <i class="fa-solid fa-pen"></i> Edit
            </button>
          </div>
          <div class="detail-row">
            <div class="detail-label">Full Name</div>
            <div class="detail-value" id="fullNameValue">Johnatan Smith</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Username</div>
            <div class="detail-value" id="usernameValue">jsmith</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Email</div>
            <div class="detail-value" id="emailValue">johnsmith@example.com</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Phone</div>
            <div class="detail-value" id="phoneValue">(097) 234-5678</div>
          </div>
        
          <div class="detail-row">
            <div class="detail-label">Address</div>
            <div class="detail-value" id="addressValue">Bay Area, San Francisco, CA</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Employee ID</div>
            <div class="detail-value" id="employeeIdValue">WM-2023-0042</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Date of Birth</div>
            <div class="detail-value" id="dobValue">January 15, 1985</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Password</div>
            <div class="detail-value">••••••••</div>
          </div>
        </div>
        
        <!-- Work Information -->
        <div class="work-info">
          <div class="section-title">
            <div class="section-title-text">
              <i class="fa-solid fa-briefcase"></i> Work Information
            </div>
            <!-- Removed edit button for work information as requested -->
          </div>
          <div class="detail-row">
            <div class="detail-label">Position</div>
            <div class="detail-value" id="positionValue">Waste Management Supervisor</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Department</div>
            <div class="detail-value" id="departmentValue">Operations</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Start Date</div>
            <div class="detail-value" id="startDateValue">March 10, 2020</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Assigned Areas</div>
            <div class="detail-value" id="areasValue">Central Park, Main Street, City Hall, Shopping Mall, Riverside</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Supervisor</div>
            <div class="detail-value" id="supervisorValue">Robert Johnson</div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Shift</div>
            <div class="detail-value" id="shiftValue">Morning (6:00 AM - 2:00 PM)</div>
          </div>
        </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });
    }

    // Tab functionality
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabBtns.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to current button and content
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });

    // User dropdown positioning and toggle
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
        // Prevent overflow bottom
        if (top + dropdownHeight > window.innerHeight - 8) {
            top = rect.top - dropdownHeight - 8;
        }

        userDropdown.style.left = left + 'px';
        userDropdown.style.top = top + 'px';
    }

    if (userProfileTrigger && userDropdown) {
        userProfileTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            if (userDropdown.classList.contains('active')) {
                userDropdown.classList.remove('active');
                return;
            }
            userDropdown.classList.add('active');
            positionDropdown();
        });

        document.addEventListener('click', function(e) {
            if (!userProfileTrigger.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        });

        window.addEventListener('resize', function() {
            if (userDropdown.classList.contains('active')) {
                positionDropdown();
            }
        });

        window.addEventListener('scroll', function() {
            if (userDropdown.classList.contains('active')) {
                positionDropdown();
            }
        });
    }

    // Close sidebar if minimized and clicking outside
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

    // Logout functionality
    const logoutBtn = document.getElementById('logoutBtn');
    const dropdownLogoutBtn = document.querySelector('.dropdown-menu-item[href="logout.php"]');
    const logoutModal = document.getElementById('logoutModal');
    const cancelLogout = document.getElementById('cancelLogout');
    const confirmLogout = document.getElementById('confirmLogout');

    function showLogoutModal() {
        logoutModal.classList.add('active');
    }

    function hideLogoutModal() {
        logoutModal.classList.remove('active');
    }

    function handleLogout() {
        // Redirect to logout.php which will handle the session destruction
        window.location.href = "logout.php";
    }

    // Add logout functionality to logout button
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showLogoutModal();
        });
    }

    // Add logout functionality to dropdown logout button
    if (dropdownLogoutBtn) {
        dropdownLogoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            userDropdown.classList.remove('active'); // Close the dropdown
            showLogoutModal();
        });
    }

    // Modal button handlers
    if (cancelLogout) {
        cancelLogout.addEventListener('click', function(e) {
            e.preventDefault();
            hideLogoutModal();
        });
    }

    if (confirmLogout) {
        confirmLogout.addEventListener('click', function(e) {
            e.preventDefault();
            hideLogoutModal();
            handleLogout();
        });
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === logoutModal) {
            logoutModal.classList.remove('active');
        }
    });
});
</script>
</body>
</html>
