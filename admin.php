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

// Fetch statistics with error handling
try {
    $bin_count = $conn->query("SELECT COUNT(*) as count FROM bins")->fetch_assoc()['count'] ?? 0;
    $area_count = $conn->query("SELECT COUNT(DISTINCT location) as count FROM bins")->fetch_assoc()['count'] ?? 0;
    $waste_collected = $conn->query("SELECT SUM(amount) as total FROM collection_logs")->fetch_assoc()['total'] ?? 0;
    $efficiency_rate = $conn->query("SELECT AVG(efficiency) as rate FROM collection_stats")->fetch_assoc()['rate'] ?? 0;

    // Fetch the three main bins with their real-time data
    try {
        $main_bins_query = "SELECT b.bin_id, b.location, b.waste_type, b.fill_level, b.status, b.last_updated, 
                                   sc.trigger_pin, sc.echo_pin, sc.low_threshold, sc.medium_threshold, sc.high_threshold
                            FROM bins b 
                            LEFT JOIN sensor_config sc ON b.bin_id = sc.bin_id 
                            WHERE b.bin_id IN ('BIN001', 'BIN002', 'BIN003') 
                            ORDER BY b.bin_id";
        
        $main_bins_result = $conn->query($main_bins_query);
        $main_bins = [];
        if ($main_bins_result) {
            while ($row = $main_bins_result->fetch_assoc()) {
                $main_bins[] = $row;
            }
        }
    } catch (Exception $e) {
        // If tables don't exist yet, set empty array
        $main_bins = [];
        error_log("Error fetching main bins: " . $e->getMessage());
    }

    // Fetch bin levels for other stats
    $bin_levels = $conn->query("SELECT bin_id, location, fill_level FROM bins ORDER BY fill_level DESC LIMIT 5");

    // Fetch recent updates
    $recent_updates = $conn->query("SELECT * FROM bin_updates ORDER BY timestamp DESC LIMIT 5");

    // Fetch collection logs
    $collection_logs = $conn->query("SELECT * FROM collection_logs ORDER BY collection_time DESC LIMIT 5");

    // Fetch recent activity logs (with error handling)
    try {
        $activity_logs = $conn->query("SELECT * FROM activity_logs ORDER BY timestamp DESC LIMIT 4");
    } catch (Exception $e) {
        $activity_logs = null; // Set to null if table doesn't exist
    }
} catch (Exception $e) {
    // Set default values if tables don't exist
    $bin_count = 0;
    $area_count = 0;
    $waste_collected = 0;
    $efficiency_rate = 0;
    $bin_levels = null;
    $recent_updates = null;
    $collection_logs = null;
    $activity_logs = null;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<title>SEGREBIN</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="css/admin.css">
        <link rel="stylesheet" href="css/notifications.css">
                <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script src="js/ultrasonic_updates.js"></script>
        <script>
            // Pass PHP data to JavaScript
            const binData = <?php echo json_encode($main_bins); ?>;
            const refreshUrl = '<?php echo $_SERVER['PHP_SELF']; ?>';
        </script>
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
                <li class="active">
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
                <h1>Dashboard</h1>
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
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>NUMBER OF BINS</h3>
                        <h2><?php echo number_format($bin_count); ?></h2>
                        <p><span class="positive">+12%</span> since last month</p>
                    </div>
                    <div class="stat-icon blue">
                        <i class="fa-solid fa-dumpster"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>ACTIVE AREAS</h3>
                        <h2>42</h2>
                        <p><span class="positive">+5</span> since last week</p>
                    </div>
                    <div class="stat-icon red">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>WASTE COLLECTED</h3>
                        <h2>8.7 tons</h2>
                        <p><span class="positive">+18%</span> since last quarter</p>
                    </div>
                    <div class="stat-icon teal">
                        <i class="fa-solid fa-recycle"></i>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>EFFICIENCY RATE</h3>
                        <h2>87.4%</h2>
                        <p><span class="positive">+3.2%</span> than last month</p>
                    </div>
                    <div class="stat-icon orange">
                        <i class="fa-solid fa-gauge-high"></i>
                    </div>
                </div>
            </div>

            <!-- Bin Levels -->
            <div class="bin-levels">
                <div class="bin-levels-header">
                    <h2><?php echo htmlspecialchars($main_bins[0]['location'] ?? 'Main Location'); ?></h2>
                    <div class="bin-controls">
                        <button id="refreshBinLevels" class="btn-refresh" title="Refresh Bin Levels">
                            <i class="fa-solid fa-rotate"></i>
                        </button>
                        <div class="sound-toggle">
                            <input type="checkbox" id="soundToggle" checked>
                            <label for="soundToggle" title="Toggle Alert Sounds">
                                <i class="fa-solid fa-volume-high"></i>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="bin-location-address">
                    <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($main_bins[0]['location'] ?? 'Main Location'); ?>
                </div>
                <div class="bin-circles">
                    <?php if (!empty($main_bins)): ?>
                        <?php foreach ($main_bins as $bin): ?>
                            <?php
                            // Determine the CSS class based on fill level
                            $level_class = 'level-low';
                            if ($bin['fill_level'] >= 90) {
                                $level_class = 'level-full';
                            } elseif ($bin['fill_level'] >= 70) {
                                $level_class = 'level-high';
                            } elseif ($bin['fill_level'] >= 40) {
                                $level_class = 'level-medium';
                            }
                            
                            // Format the last updated time
                            $last_updated = 'Never';
                            if ($bin['last_updated']) {
                                $timestamp = strtotime($bin['last_updated']);
                                $time_diff = time() - $timestamp;
                                if ($time_diff < 60) {
                                    $last_updated = 'Just now';
                                } elseif ($time_diff < 3600) {
                                    $last_updated = round($time_diff / 60) . ' min ago';
                                } elseif ($time_diff < 86400) {
                                    $last_updated = round($time_diff / 3600) . ' hour ago';
                                } else {
                                    $last_updated = round($time_diff / 86400) . ' day ago';
                                }
                            }
                            ?>
                            <div class="bin-circle" data-bin-id="<?php echo htmlspecialchars($bin['bin_id']); ?>">
                                <div class="circle <?php echo $level_class; ?>">
                                    <span><?php echo number_format($bin['fill_level'], 1); ?>%</span>
                                </div>
                                <div class="bin-name"><?php echo ucfirst(htmlspecialchars($bin['waste_type'])); ?></div>
                                <div class="last-updated">Updated: <?php echo $last_updated; ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fallback if no bins found -->
                        <div class="bin-circle">
                            <div class="circle level-low">
                                <span>0%</span>
                            </div>
                            <div class="bin-name">No Data</div>
                            <div class="last-updated">No bins configured</div>
                        </div>
                        <?php if (isset($e)): ?>
                            <div class="bin-error-message">
                                <p>Database Error: <?php echo htmlspecialchars($e->getMessage()); ?></p>
                                <p>Please run the database setup script first.</p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="bin-status-indicator">
                    <div class="status-item">
                        <span class="status-dot low"></span>
                        <span>Low (0-25%)</span>
                    </div>
                    <div class="status-item">
                        <span class="status-dot medium"></span>
                        <span>Medium (25-70%)</span>
                    </div>
                    <div class="status-item">
                        <span class="status-dot high"></span>
                        <span>High (70-90%)</span>
                    </div>
                    <div class="status-item">
                        <span class="status-dot full"></span>
                        <span>Full (90-100%)</span>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <div class="chart-container">
                    <div class="chart-header">
                        <div>
                            <h2>Waste Collected Over Time</h2>
                            <p><i class="fa-solid fa-arrow-up"></i> 18% increase in 2023</p>
                        </div>
                    </div>
                    <div class="chart" id="wasteChart"></div>
                </div>
                
                <div class="updates-container">
                    <div class="tabs">
                        <button class="tab-btn active" data-tab="bin-updates">
                            <i class="fa-solid fa-bell"></i> Bin Updates
                        </button>
                        <button class="tab-btn" data-tab="collection-logs">
                            <i class="fa-solid fa-clipboard-list"></i> Collection Logs
                        </button>
                    </div>
                    
                    <div id="bin-updates" class="tab-content active">
                        <table class="updates-table">
                            <thead>
                                <tr>
                                    <th>Bin</th>
                                    <th>Action</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="bin-id">Bin #D429</td>
                                    <td>
                                        <span class="action-status emptied">
                                            <i class="fa-solid fa-check-circle"></i> Emptied
                                        </span>
                                    </td>
                                    <td class="time-ago">10 minutes ago</td>
                                </tr>
                                <tr>
                                    <td class="bin-id">Bin #C315</td>
                                    <td>
                                        <span class="action-status alert">
                                            <i class="fa-solid fa-triangle-exclamation"></i> Alert
                                        </span>
                                    </td>
                                    <td class="time-ago">45 minutes ago</td>
                                </tr>
                                <tr>
                                    <td class="bin-id">Bin #F612</td>
                                    <td>
                                        <span class="action-status maintenance">
                                            <i class="fa-solid fa-wrench"></i> Maintenance
                                        </span>
                                    </td>
                                    <td class="time-ago">2 hours ago</td>
                                </tr>
                                <tr>
                                    <td class="bin-id">Bin #G745</td>
                                    <td>
                                        <span class="action-status installed">
                                            <i class="fa-solid fa-plus-circle"></i> Installed
                                        </span>
                                    </td>
                                    <td class="time-ago">Yesterday</td>
                                </tr>
                                <tr>
                                    <td class="bin-id">Bin #A142</td>
                                    <td>
                                        <span class="action-status emptied">
                                            <i class="fa-solid fa-check-circle"></i> Emptied
                                        </span>
                                    </td>
                                    <td class="time-ago">Yesterday</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="collection-logs" class="tab-content">
                        <table class="updates-table">
                            <thead>
                                <tr>
                                    <th>Bin</th>
                                    <th>Amount</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="bin-id">Bin #D429</td>
                                    <td class="amount">120 kg</td>
                                    <td class="time-ago">10 minutes ago</td>
                                </tr>
                                <tr>
                                    <td class="bin-id">Bin #B238</td>
                                    <td class="amount">85 kg</td>
                                    <td class="time-ago">3 hours ago</td>
                                </tr>
                                <tr>
                                    <td class="bin-id">Bin #A142</td>
                                    <td class="amount">45 kg</td>
                                    <td class="time-ago">Yesterday</td>
                                </tr>
                                <tr>
                                    <td class="bin-id">Bin #E531</td>
                                    <td class="amount">30 kg</td>
                                    <td class="time-ago">Yesterday</td>
                                </tr>
                                <tr>
                                    <td class="bin-id">Bin #C315</td>
                                    <td class="amount">110 kg</td>
                                    <td class="time-ago">2 days ago</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tables Section -->
            <div class="tables-section">
                <div class="bin-locations">
                    <h2>Bin Locations</h2>
                    <div class="map-container">
                        <div class="map-bg"></div>
                        
                        <!-- Map Roads -->
                        <div class="map-roads">
                            <div class="map-road map-road-h" style="top: 30%; left: 0; width: 100%;"></div>
                            <div class="map-road map-road-h" style="top: 60%; left: 0; width: 100%;"></div>
                            <div class="map-road map-road-v" style="left: 25%; top: 0; height: 100%;"></div>
                            <div class="map-road map-road-v" style="left: 50%; top: 0; height: 100%;"></div>
                            <div class="map-road map-road-v" style="left: 75%; top: 0; height: 100%;"></div>
                        </div>
                        
                        <!-- Map Controls -->
                        <div class="map-controls">
                            <button class="map-control-btn">+</button>
                            <button class="map-control-btn">−</button>
                        </div>
                        
                        <!-- Map Pins -->
                        <div class="map-pin" style="top: 30%; left: 25%;">
                            <div class="map-pin-icon"></div>
                            <div class="map-pin-label">Bin #A142 - Central Park</div>
                        </div>
                        
                        <div class="map-pin" style="top: 45%; left: 40%;">
                            <div class="map-pin-icon warning"></div>
                            <div class="map-pin-label">Bin #B238 - Main Street</div>
                        </div>
                        
                        <div class="map-pin" style="top: 60%; left: 65%;">
                            <div class="map-pin-icon alert"></div>
                            <div class="map-pin-label">Bin #C315 - City Hall</div>
                        </div>
                        
                        <div class="map-pin" style="top: 25%; left: 70%;">
                            <div class="map-pin-icon alert"></div>
                            <div class="map-pin-label">Bin #D429 - Shopping Mall</div>
                        </div>
                        
                        <div class="map-pin" style="top: 70%; left: 30%;">
                            <div class="map-pin-icon"></div>
                            <div class="map-pin-label">Bin #E531 - Riverside</div>
                        </div>
                        
                        <!-- Map Info Window -->
                        <div class="map-info-window">
                            <h3>Bin #C315</h3>
                            <p><strong>Location:</strong> City Hall</p>
                            <p><strong>Last Emptied:</strong> 2 days ago</p>
                            <p><strong>Fill Level:</strong> 90%</p>
                            <p><strong>Status:</strong> <span class="status high">Needs Attention</span></p>
                        </div>
                    </div>
                </div>
                <div class="recent-logs">
                    <h2>Recent Logs</h2>
                    <div class="log-list">
                        <div class="log-item">
                            <div class="log-avatar">JD</div>
                            <div class="log-info">
                                <h3>John Doe</h3>
                                <p>Logged in as Administrator</p>
                            </div>
                            <div class="log-time">5 min ago</div>
                        </div>
                        <div class="log-item">
                            <div class="log-avatar">AS</div>
                            <div class="log-info">
                                <h3>Alice Smith</h3>
                                <p>Logged in as Operator</p>
                            </div>
                            <div class="log-time">30 min ago</div>
                        </div>
                        <div class="log-item">
                            <div class="log-avatar">RJ</div>
                            <div class="log-info">
                                <h3>Robert Johnson</h3>
                                <p>Logged in as Driver</p>
                            </div>
                            <div class="log-time">1 hour ago</div>
                        </div>
                        <div class="log-item">
                            <div class="log-avatar">ML</div>
                            <div class="log-info">
                                <h3>Maria Lopez</h3>
                                <p>Logged in as Supervisor</p>
                            </div>
                            <div class="log-time">3 hours ago</div>
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
        </div>
    </main>
</div>

<!-- Notification Container for Ultrasonic Sensor Alerts -->
<div class="notification-container"></div>

<!-- Ultrasonic Sensor Settings Modal -->
<div class="sensor-settings-modal" id="sensorSettingsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ultrasonic Sensor Settings</h3>
            <button class="modal-close" id="closeSensorSettings">&times;</button>
        </div>
        <div class="modal-body">
            <form id="thresholdForm">
                <div class="form-group">
                    <label for="low_threshold">Low Threshold (%)</label>
                    <input type="number" id="low_threshold" name="low_threshold" value="25" min="0" max="100">
                </div>
                <div class="form-group">
                    <label for="medium_threshold">Medium Threshold (%)</label>
                    <input type="number" id="medium_threshold" name="medium_threshold" value="40" min="0" max="100">
                </div>
                <div class="form-group">
                    <label for="high_threshold">High Threshold (%)</label>
                    <input type="number" id="high_threshold" name="high_threshold" value="70" min="0" max="100">
                </div>
                <div class="form-group">
                    <label for="critical_threshold">Critical Threshold (%)</label>
                    <input type="number" id="critical_threshold" name="critical_threshold" value="90" min="0" max="100">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Save Settings</button>
                    <button type="button" class="btn-secondary" id="resetThresholds">Reset to Default</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- User Dropdown Menu - MOVED OUTSIDE FOR BETTER POSITIONING -->
<div class="user-dropdown" id="userDropdown">
    <div class="dropdown-header">
        <div class="dropdown-avatar">
            <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-CnYOTm2EYgnfTTQZgktcT0hHcsfTLf.png" alt="User Avatar">
        </div>
        <div class="dropdown-user-info">
            <div class="dropdown-user-name"><?php echo htmlspecialchars($admin_data['username']); ?></div>
            <div class="dropdown-user-email"><?php echo htmlspecialchars($admin_data['email'] ?? 'admin@example.com'); ?></div>
        </div>
    </div>
    <div class="dropdown-menu">
        <a href="profile.php" class="dropdown-menu-item">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle with enhanced mobile experience
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
            
            // Prevent body scroll when sidebar is open on mobile
            if (sidebar.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });
        
        // Close sidebar when clicking on a link (mobile optimization)
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 991) {
                    sidebar.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });
    }
    
    // Enhanced mobile touch handling
    let touchStartX = 0;
    let touchEndX = 0;
    
    document.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    document.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
    
    function handleSwipe() {
        const swipeThreshold = 50;
        const swipeDistance = touchEndX - touchStartX;
        
        // Swipe right to open sidebar
        if (swipeDistance > swipeThreshold && touchStartX < 50) {
            if (window.innerWidth <= 991 && !sidebar.classList.contains('active')) {
                sidebar.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        }
        
        // Swipe left to close sidebar
        if (swipeDistance < -swipeThreshold && sidebar.classList.contains('active')) {
            if (window.innerWidth <= 991) {
                sidebar.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
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

    // User dropdown toggle with proper positioning - FIXED
    const userProfileTrigger = document.getElementById('userProfileTrigger');
    const userDropdown = document.getElementById('userDropdown');
    
    if (userProfileTrigger && userDropdown) {
        userProfileTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            
            if (userDropdown.classList.contains('active')) {
                userDropdown.classList.remove('active');
                return;
            }
            
            // Get the position of the user profile trigger
            const rect = userProfileTrigger.getBoundingClientRect();
            const dropdownWidth = 280;
            const dropdownHeight = 200; // Approximate height
            
            // Calculate position
            let left = rect.left + (rect.width / 2) - (dropdownWidth / 2);
            let top = rect.bottom + 8;
            
            // Ensure dropdown doesn't go off-screen horizontally
            const windowWidth = window.innerWidth;
            if (left + dropdownWidth > windowWidth - 20) {
                left = windowWidth - dropdownWidth - 20;
            }
            if (left < 20) {
                left = 20;
            }
            
            // Ensure dropdown doesn't go off-screen vertically
            const windowHeight = window.innerHeight;
            if (top + dropdownHeight > windowHeight - 20) {
                top = rect.top - dropdownHeight - 8;
            }
            
            // Position the dropdown
            userDropdown.style.left = left + 'px';
            userDropdown.style.top = top + 'px';
            
            // Show dropdown
            userDropdown.classList.add('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userProfileTrigger.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        });
        
        // Reposition dropdown on window resize
        window.addEventListener('resize', function() {
            if (userDropdown.classList.contains('active')) {
                userDropdown.classList.remove('active');
            }
        });
    }

    // Initialize Waste Collection Chart
    if (document.getElementById('wasteChart') && typeof ApexCharts !== 'undefined') {
        const options = {
            chart: {
                height: 300,
                type: 'line',
                toolbar: {
                    show: false
                },
                background: 'transparent',
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },
            colors: ['#10b981'],
            series: [{
                name: 'Waste Collected (tons)',
                data: [4.2, 5.1, 6.7, 5.8, 8.2, 7.3, 8.9, 9.2, 10.5]
            }],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 4
            },
            grid: {
                borderColor: 'rgba(0, 0, 0, 0.1)',
                row: {
                    colors: ['transparent'],
                    opacity: 0.5
                }
            },
            xaxis: {
                categories: ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
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
                min: 0,
                max: 12,
                tickAmount: 6,
                labels: {
                    style: {
                        colors: '#6c757d'
                    },
                    formatter: function(val) {
                        return val.toFixed(1) + ' tons';
                    }
                }
            },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: function(val) {
                        return val.toFixed(1) + ' tons';
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    gradientToColors: ['#34d399'],
                    shadeIntensity: 1,
                    type: 'horizontal',
                    opacityFrom: 1,
                    opacityTo: 1
                }
            }
        };
        
        const chart = new ApexCharts(document.getElementById('wasteChart'), options);
        chart.render();
    }

    // Map pin click functionality
    const mapPins = document.querySelectorAll('.map-pin');
    const mapInfoWindow = document.querySelector('.map-info-window');
    
    mapPins.forEach(pin => {
        pin.addEventListener('click', function() {
            // In a real implementation, this would update the info window with data
            // from the specific pin that was clicked
            mapInfoWindow.style.display = 'block';
        });
    });

    // Add click event listeners to bin level circles for ripple effect
    const binCircles = document.querySelectorAll('.circle');
    
    binCircles.forEach(circle => {
        // Enhanced click and touch handling for mobile
        const handleBinClick = function(e) {
            e.preventDefault();
            
            // Create ripple element
            const ripple = document.createElement('div');
            ripple.classList.add('fill-animation');
            this.appendChild(ripple);
            
            // Remove ripple after animation completes
            setTimeout(() => {
                ripple.remove();
            }, 800);
            
            // Show bin info (could be expanded to show a modal with more details)
            const binName = this.closest('.bin-circle').querySelector('.bin-name').textContent;
            const fillLevel = this.querySelector('span').textContent;
            
            console.log(`Bin: ${binName}, Fill Level: ${fillLevel}`);
            
            // Mobile-friendly bin info display
            if (window.innerWidth <= 768) {
                showMobileBinInfo(binName, fillLevel);
            }
            
            // You could trigger a modal or update an info panel here
        };
        
        // Add both click and touch events for better mobile support
        circle.addEventListener('click', handleBinClick);
        circle.addEventListener('touchend', handleBinClick);
        
        // Prevent default touch behavior to avoid double-triggering
        circle.addEventListener('touchstart', function(e) {
            e.preventDefault();
        });
    });
    
    // Mobile-friendly bin info display
    function showMobileBinInfo(binName, fillLevel) {
        // Create a temporary mobile notification
        const mobileInfo = document.createElement('div');
        mobileInfo.className = 'mobile-bin-info';
        mobileInfo.innerHTML = `
            <div class="mobile-bin-info-content">
                <h3>${binName}</h3>
                <p>Fill Level: ${fillLevel}</p>
                <button class="mobile-bin-info-close">&times;</button>
            </div>
        `;
        
        document.body.appendChild(mobileInfo);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (mobileInfo.parentNode) {
                mobileInfo.remove();
            }
        }, 3000);
        
        // Close button functionality
        const closeBtn = mobileInfo.querySelector('.mobile-bin-info-close');
        closeBtn.addEventListener('click', () => {
            mobileInfo.remove();
        });
    }

    // Add counter animation to bin level percentages
    function animateValue(obj, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            obj.innerHTML = value + '%';
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    // Animate bin level percentages when they come into view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const span = entry.target.querySelector('span');
                const endValue = parseInt(span.textContent);
                span.textContent = '0%';
                animateValue(span, 0, endValue, 1500);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    binCircles.forEach(circle => {
        observer.observe(circle);
    });

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
            
            // Show the goodbye modal
            goodbyeModal.classList.add('active');
            
            // Wait 2 seconds before redirecting
            setTimeout(function() {
                // Perform logout by sending request to logout.php
                fetch('logout.php')
                    .then(() => {
                        window.location.href = "login.php";
                    })
                    .catch(() => {
                        // Redirect anyway even if fetch fails
                        window.location.href = "login.php";
                    });
            }, 2000);
        });
    }

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === logoutModal) {
            logoutModal.classList.remove('active');
        }
    });

    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.sidebar');
        const toggle = document.querySelector('.mobile-menu-toggle');
        // Only run if sidebar is active and screen is <= 992px
        if (
            sidebar.classList.contains('active') &&
            window.innerWidth <= 992 &&
            !sidebar.contains(e.target) &&
            !toggle.contains(e.target)
        ) {
            sidebar.classList.remove('active');
        }
    });

    // Bin Levels Refresh Functionality
    const refreshBinLevelsBtn = document.getElementById('refreshBinLevels');
    
    if (refreshBinLevelsBtn) {
        refreshBinLevelsBtn.addEventListener('click', function() {
            // Show loading state
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            this.disabled = true;
            
            // Fetch updated bin data from the server
            fetch('php/ultrasonic_sensor.php?action=get_bins')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.bins) {
                        updateBinCircles(data.bins);
                    } else {
                        console.error('Failed to fetch bin data:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error refreshing bin levels:', error);
                })
                .finally(() => {
                    // Restore button state
                    this.innerHTML = '<i class="fa-solid fa-rotate"></i>';
                    this.disabled = false;
                });
        });
    }

    // Function to update bin circles with new data
    function updateBinCircles(bins) {
        bins.forEach(bin => {
            const binCircle = document.querySelector(`[data-bin-id="${bin.bin_id}"]`);
            if (binCircle) {
                const circle = binCircle.querySelector('.circle');
                const percentageSpan = circle.querySelector('span');
                const lastUpdated = binCircle.querySelector('.last-updated');
                
                // Update percentage
                percentageSpan.textContent = bin.fill_level + '%';
                
                // Update circle class based on fill level
                circle.className = 'circle';
                if (bin.fill_level >= 90) {
                    circle.classList.add('level-full');
                } else if (bin.fill_level >= 70) {
                    circle.classList.add('level-high');
                } else if (bin.fill_level >= 40) {
                    circle.classList.add('level-medium');
                } else {
                    circle.classList.add('level-low');
                }
                
                // Update last updated time
                if (bin.last_updated) {
                    const timestamp = new Date(bin.last_updated);
                    const now = new Date();
                    const timeDiff = Math.floor((now - timestamp) / 1000);
                    
                    let timeText = 'Just now';
                    if (timeDiff >= 60 && timeDiff < 3600) {
                        timeText = Math.floor(timeDiff / 60) + ' min ago';
                    } else if (timeDiff >= 3600 && timeDiff < 86400) {
                        timeText = Math.floor(timeDiff / 3600) + ' hour ago';
                    } else if (timeDiff >= 86400) {
                        timeText = Math.floor(timeDiff / 86400) + ' day ago';
                    }
                    
                    lastUpdated.textContent = 'Updated: ' + timeText;
                }
                
                // Add animation effect
                circle.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    circle.style.transform = 'scale(1)';
                }, 200);
            }
        });
    }
});
</script>
</body>
</html>