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
<link rel="stylesheet" href="css/analytics.css">
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
      <li class="active">
          <a href="analytics.php">
              <i class="fa-solid fa-chart-line"></i>
              <span>Analytics</span>
          </a>
      </li>
      <li>
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

 <!-- Main Content -->
  <main class="main-content">
    <!-- Header -->
    <header class="header">
      <div class="header-left">
        <h1>Analytics</h1>
        <div class="search-bar">
          <i class="fa-solid fa-search"></i>
          <input type="text" placeholder="Type here...">
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
          <a href="#" class="dropdown-menu-item">
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

    <!-- Analytics Content -->
    <div class="analytics-content">
      <!-- Status Cards -->
      <div class="status-cards">
        <div class="status-card">
          <div class="status-icon blue">
            <i class="fa-solid fa-bottle-water"></i>
          </div>
          <div class="status-info">
            <h3>Plastics</h3>
            <p>1,250 kg collected</p>
          </div>
        </div>
        <div class="status-card">
          <div class="status-icon yellow">
            <i class="fa-solid fa-newspaper"></i>
          </div>
          <div class="status-info">
            <h3>Paper</h3>
            <p>980 kg collected</p>
          </div>
        </div>
        <div class="status-card">
          <div class="status-icon green">
            <i class="fa-solid fa-box"></i>
          </div>
          <div class="status-info">
            <h3>Styrofoam</h3>
            <p>450 kg collected</p>
          </div>
        </div>
      </div>

      <!-- Analytics Grid -->
      <div class="analytics-grid">
        <!-- Monthly Collection Trends - Full Width -->
        <div class="analytics-card analytics-card-big">
          <div class="analytics-card-header">
            <div class="analytics-card-title">
              <h2>Monthly Collection Trends</h2>
              <p>Waste collected across all categories</p>
            </div>
            <div class="date-selector">
              <span>Jan 1 - Dec 31, 2023</span>
              <i class="fa-solid fa-chevron-down"></i>
            </div>
          </div>
          <div style="height: 300px;" id="collectionTrendsChart"></div>
        </div>

        <!-- Location-Based Data -->
        <div class="analytics-card">
          <div class="analytics-card-header">
            <div class="analytics-card-title">
              <h2>Location-Based Data</h2>
              <p>Areas generating most waste</p>
            </div>
          </div>
          <div class="chart-container" id="locationChart"></div>
          <div class="progress-container">
            <div>
              <div class="progress-item">
                <div class="progress-label">
                  <div class="progress-label-color blue"></div>
                  <span>Main Campus</span>
                </div>
                <div class="progress-value">42%</div>
              </div>
              <div class="progress-bar">
                <div class="progress-fill blue" style="width: 42%"></div>
              </div>
            </div>
            <div>
              <div class="progress-item">
                <div class="progress-label">
                  <div class="progress-label-color teal"></div>
                  <span>Research Building</span>
                </div>
                <div class="progress-value">28%</div>
              </div>
              <div class="progress-bar">
                <div class="progress-fill teal" style="width: 28%"></div>
              </div>
            </div>
            <div>
              <div class="progress-item">
                <div class="progress-label">
                  <div class="progress-label-color yellow"></div>
                  <span>Cafeteria</span>
                </div>
                <div class="progress-value">18%</div>
              </div>
              <div class="progress-bar">
                <div class="progress-fill yellow" style="width: 18%"></div>
              </div>
            </div>
            <div>
              <div class="progress-item">
                <div class="progress-label">
                  <div class="progress-label-color green"></div>
                  <span>Admin Building</span>
                </div>
                <div class="progress-value">12%</div>
              </div>
              <div class="progress-bar">
                <div class="progress-fill green" style="width: 12%"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Common Waste Types -->
        <div class="analytics-card">
          <div class="analytics-card-header">
            <div class="analytics-card-title">
              <h2>Common Waste Types</h2>
              <p>Most frequently disposed items</p>
            </div>
          </div>
          <div class="chart-container" id="wasteTypesChart"></div>
          <div class="progress-container">
            <div>
              <div class="progress-item">
                <div class="progress-label">
                  <div class="progress-label-color blue"></div>
                  <span>Plastic</span>
                </div>
                <div class="progress-value">40%</div>
              </div>
              <div class="progress-bar">
                <div class="progress-fill blue" style="width: 35%"></div>
              </div>
            </div>
            <div>
              <div class="progress-item">
                <div class="progress-label">
                  <div class="progress-label-color yellow"></div>
                  <span>Styrofoam</span>
                </div>
                <div class="progress-value">30%</div>
              </div>
              <div class="progress-bar">
                <div class="progress-fill yellow" style="width: 25%"></div>
              </div>
            </div>
            <div>
              <div class="progress-item">
                <div class="progress-label">
                  <div class="progress-label-color teal"></div>
                  <span>Paper</span>
                </div>
                <div class="progress-value">30%</div>
              </div>
              <div class="progress-bar">
                <div class="progress-fill teal" style="width: 20%"></div>
              </div>
            </div>
           
          </div>
        </div>
      </div>

      <!-- Waste Collection History -->
      <div class="analytics-card">
        <div class="analytics-card-header">
          <div class="analytics-card-title">
            <h2>Waste Collection History</h2>
            <p>Recent collection activities across all bins</p>
          </div>
          <div>
            <button class="btn-icon">
              <i class="fa-solid fa-filter"></i>
            </button>
          </div>
        </div>
        <table class="collection-table">
          <thead>
            <tr>
              <th>DATE</th>
              <th>BIN ID</th>
              <th>LOCATION</th>
              <th>WASTE TYPE</th>
              <th>COLLECTOR</th>
              <th>AMOUNT</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Mar 21, 2023</td>
              <td>BIN-1042</td>
              <td>Main Campus</td>
              <td><span class="waste-type plastic">Plastic</span></td>
              <td>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <div class="collector-avatar">J</div>
                  <span>John Davis</span>
                </div>
              </td>
              <td>24.5 kg</td>
              <td><i class="fa-solid fa-ellipsis-vertical"></i></td>
            </tr>
            <tr>
              <td>Mar 20, 2023</td>
              <td>BIN-1038</td>
              <td>Research Building</td>
              <td><span class="waste-type paper">Paper</span></td>
              <td>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <div class="collector-avatar">M</div>
                  <span>Maria Rodriguez</span>
                </div>
              </td>
              <td>18.2 kg</td>
              <td><i class="fa-solid fa-ellipsis-vertical"></i></td>
            </tr>
            <tr>
              <td>Mar 19, 2023</td>
              <td>BIN-1027</td>
              <td>Cafeteria</td>
              <td><span class="waste-type styrofoam">Styrofoam</span></td>
              <td>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <div class="collector-avatar">R</div>
                  <span>Robert Chen</span>
                </div>
              </td>
              <td>15.8 kg</td>
              <td><i class="fa-solid fa-ellipsis-vertical"></i></td>
            </tr>
            <tr>
              <td>Mar 18, 2023</td>
              <td>BIN-1015</td>
              <td>Admin Building</td>
              <td><span class="waste-type plastic">Plastic</span></td>
              <td>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <div class="collector-avatar">S</div>
                  <span>Sarah Johnson</span>
                </div>
              </td>
              <td>21.3 kg</td>
              <td><i class="fa-solid fa-ellipsis-vertical"></i></td>
            </tr>
            <tr>
              <td>Mar 17, 2023</td>
              <td>BIN-1042</td>
              <td>Main Campus</td>
              <td><span class="waste-type paper">Paper</span></td>
              <td>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <div class="collector-avatar">J</div>
                  <span>John Davis</span>
                </div>
              </td>
              <td>19.7 kg</td>
              <td><i class="fa-solid fa-ellipsis-vertical"></i></td>
            </tr>
            <tr>
              <td>Mar 16, 2023</td>
              <td>BIN-1031</td>
              <td>Research Building</td>
              <td><span class="waste-type styrofoam">Styrofoam</span></td>
              <td>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                  <div class="collector-avatar">M</div>
                  <span>Maria Rodriguez</span>
                </div>
              </td>
              <td>12.4 kg</td>
              <td><i class="fa-solid fa-ellipsis-vertical"></i></td>
            </tr>
          </tbody>
        </table>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
          <div>1 to 6 items of 24</div>
          <div style="display: flex; gap: 0.5rem;">
            <button class="btn-icon">
              <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button class="btn-icon">
              <i class="fa-solid fa-chevron-right"></i>
            </button>
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
    </div>
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

<!-- See You Next Time Modal -->
<div class="goodbye-modal" id="goodbyeModal">
  <div class="goodbye-modal-content">
      <div class="goodbye-modal-img">
          <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/see-you-1024x1024-xL1YmsjjRBMbKQSzAiFzyG1xXpS62u.gif" alt="See You Next Time">
      </div>
  </div>
</div>

<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script>
document.addEventListener('DOMContentLoaded', function() {
  // Mobile menu toggle
  const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
  const sidebar = document.querySelector('.sidebar');

  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener('click', function() {
      sidebar.classList.toggle('active');
    });
  }

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

  // Initialize Collection Trends Chart
  if (document.getElementById('collectionTrendsChart') && typeof ApexCharts !== 'undefined') {
    const collectionTrendsOptions = {
      chart: {
        height: 300,
        type: 'line',
        toolbar: {
          show: false
        },
        background: 'transparent'
      },
      colors: ['#4ade80', '#fbbf24', '#10b981'],
      series: [
        {
          name: 'Plastic',
          data: [120, 145, 132, 165, 175, 190, 205, 215, 225, 210, 200, 235]
        },
        {
          name: 'Paper',
          data: [90, 110, 105, 125, 130, 140, 135, 150, 145, 160, 155, 170]
        },
        {
          name: 'Styrofoam',
          data: [45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100]
        }
      ],
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'smooth',
        width: 3
      },
      grid: {
        borderColor: 'rgba(0, 0, 0, 0.1)',
        row: {
          colors: ['transparent'],
          opacity: 0.5
        },
        yaxis: {
          lines: {
            show: true
          }
        },
        xaxis: {
          lines: {
            show: true
          }
        }
      },
      xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        labels: {
          style: {
            colors: '#6c757d'
          }
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      yaxis: {
        labels: {
          style: {
            colors: '#6c757d'
          },
          formatter: function(val) {
            return val + ' kg';
          }
        }
      },
      tooltip: {
        theme: 'light',
        y: {
          formatter: function(val) {
            return val + ' kg';
          }
        }
      }
    };
    
    const collectionTrendsChart = new ApexCharts(document.getElementById('collectionTrendsChart'), collectionTrendsOptions);
    collectionTrendsChart.render();
  }

  // Initialize Location Chart
  if (document.getElementById('locationChart') && typeof ApexCharts !== 'undefined') {
    const locationOptions = {
      chart: {
        height: 200,
        type: 'bar',
        toolbar: {
          show: false
        },
        background: 'transparent'
      },
      colors: ['#4ade80', '#34d399', '#fbbf24', '#10b981'],
      series: [{
        name: 'Waste Generated',
        data: [42, 28, 18, 12]
      }],
      plotOptions: {
        bar: {
          columnWidth: '50%',
          borderRadius: 4,
          distributed: true
        }
      },
      dataLabels: {
        enabled: false
      },
      grid: {
        borderColor: 'rgba(0, 0, 0, 0.1)',
        row: {
          colors: ['transparent'],
          opacity: 0.5
        },
        show: false
      },
      xaxis: {
        categories: ['Main Campus', 'Research', 'Cafeteria', 'Admin'],
        labels: {
          show: false
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      yaxis: {
        labels: {
          show: false
        }
      },
      tooltip: {
        theme: 'light',
        y: {
          formatter: function(val) {
            return val + '%';
          }
        }
      }
    };
    
    const locationChart = new ApexCharts(document.getElementById('locationChart'), locationOptions);
    locationChart.render();
  }

  // Initialize Waste Types Chart
  if (document.getElementById('wasteTypesChart') && typeof ApexCharts !== 'undefined') {
    const wasteTypesOptions = {
      chart: {
        height: 200,
        type: 'donut',
        background: 'transparent'
      },
      colors: ['#4ade80', '#fbbf24', '#34d399'],
      series: [40, 30, 30,],
      labels: ['Plastic Bottles', 'Food Packaging', 'Paper Cups'],
      dataLabels: {
        enabled: false
      },
      legend: {
        show: false
      },
      stroke: {
        width: 0
      },
      tooltip: {
        theme: 'light',
        y: {
          formatter: function(val) {
            return val + '%';
          }
        }
      }
    };
    
    const wasteTypesChart = new ApexCharts(document.getElementById('wasteTypesChart'), wasteTypesOptions);
    wasteTypesChart.render();
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

  // Close modal when clicking outside
  window.addEventListener('click', function(e) {
    if (e.target === logoutModal) {
      logoutModal.classList.remove('active');
    }
  });

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


     </script>
</body>
</html>