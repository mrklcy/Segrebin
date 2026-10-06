<?php
session_start();
require_once '../php/config.php';

// Keep error reporting consistent with config.php for debugging
// ini_set('display_errors', 0);
// error_reporting(0);

// Check if database connection is working
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
}

$error = '';
$identifier = '';
$remember = false;

function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function detectSQLInjection($input) {
    $blacklist = ['--', ';', '/*', '*/', '@@', '@', 'char', 'nchar', 'varchar', 'alter', 'drop', 'insert', 'delete', 'update', 'select'];
    foreach ($blacklist as $keyword) {
        if (stripos($input, $keyword) !== false) return true;
    }
    return false;
}

if (!isset($_SESSION['sqli_attempts'])) {
    $_SESSION['sqli_attempts'] = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = clean_input($_POST['identifier'] ?? '');
    $password = clean_input($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    if (detectSQLInjection($identifier) || detectSQLInjection($password)) {
        $_SESSION['sqli_attempts']++;

        if ($_SESSION['sqli_attempts'] >= 3) {
            header("Location: login.php");
            exit;
        }

        $error = "Invalid input detected.";
    } else {
        try {
            // 1️⃣ Check admin table
            $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            $stmt->bind_param("s", $identifier);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();

            if ($admin && ($password === $admin['password'] || password_verify($password, $admin['password']))) {
                $_SESSION['admin'] = $admin['username'];
                $_SESSION['sqli_attempts'] = 0;

                header("Location: ../admin.php");
                exit;
            }

            // 2️⃣ Check personel table
            $stmt = $conn->prepare("SELECT * FROM personel WHERE username = ?");
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            $stmt->bind_param("s", $identifier);
            $stmt->execute();
            $result = $stmt->get_result();
            $employee = $result->fetch_assoc();

            if ($employee && ($password === $employee['password'] || password_verify($password, $employee['password']))) {
                $_SESSION['employee'] = $employee['username'];
                $_SESSION['sqli_attempts'] = 0;

                header("Location: ../employeeDashboard.php");
                exit;
            }

            $error = "Wrong username or password.";
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>



    
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Smart Bin Monitoring - Login</title>
        <style>
            /* Reset and base styles */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            }
    
            body {
                background-color: #f8fafc;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            }
    
            /* Container styles */
            .container {
                width: 100%;
                max-width: 64rem;
            }
    
           .login-container {
                display: flex;
                overflow: hidden;
                border-radius: 0.75rem;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
                background-color: white;
                height: 32rem; /* Fixed height for layout consistency */
            }
    
            .info-panel {
                display: none;
                width: 50%;
                background-color: #27ae60;
                padding: 2rem;
                color: white;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
            
            .login-form-container {
                width: 100%;
                padding: 2rem;
            
                /* Add this to avoid stretching the container */
                overflow-y: auto;
            }
    
    
           .info-panel-content {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                height: 100%;
            }
            
            .login-form-container {
                overflow: auto;
              }
    
    
    
    
            .logo {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin-bottom: 2rem;
            }
    
            .logo-text {
                font-size: 1.25rem;
                font-weight: bold;
            }
    
            .info-content {
                flex: 1;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
    
            .info-title {
                font-size: 1.875rem;
                font-weight: bold;
                margin-bottom: 1rem;
            }
    
            .info-description {
                color: #e6f7ef; /* Changed to lighter green */
                margin-bottom: 2rem;
            }
    
            .feature {
                display: flex;
                align-items: flex-start;
                gap: 0.75rem;
                margin-bottom: 1rem;
            }
    
            .feature-icon {
                background-color: #2ecc71; /* Changed to brighter green */
                padding: 0.5rem;
                border-radius: 9999px;
                margin-top: 0.25rem;
            }
    
            .feature-content h3 {
                font-weight: 500;
            }
    
            .feature-content p {
                color: #d1f2e4; /* Changed to lighter green */
                font-size: 0.875rem;
            }
    
            .copyright {
                margin-top: auto;
                font-size: 0.875rem;
                color: #d1f2e4; /* Changed to lighter green */
            }
    
            /* Right side - login form */
            .login-form-container {
                width: 100%;
                padding: 2rem;
            }
    
            .mobile-logo {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin-bottom: 2rem;
            }
    
            .mobile-logo-text {
                font-size: 1.125rem;
                font-weight: bold;
            }
    
            .form-wrapper {
                max-width: 28rem;
                margin: 0 auto;
            }
    
            .form-header {
                margin-bottom: 2rem;
            }
    
            .form-title {
                font-size: 1.5rem;
                font-weight: bold;
                color: #0f172a;
            }
    
            .form-subtitle {
                color: #64748b;
            }
    
            .error-message {
                margin-bottom: 1rem;
                padding: 0.75rem;
                background-color: #fef2f2;
                border: 1px solid #fecaca;
                color: #dc2626;
                font-size: 0.875rem;
                border-radius: 0.5rem;
            }
    
            .success-message {
                margin-bottom: 1rem;
                padding: 0.75rem;
                background-color: #f0fdf4;
                border: 1px solid #bbf7d0;
                color: #16a34a;
                font-size: 0.875rem;
                border-radius: 0.5rem;
            }
    
            form {
                display: flex;
                flex-direction: column;
                gap: 1.5rem;
            }
    
            .form-group {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }
    
            .form-group-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
    
            label {
                color: #334155;
                font-size: 0.875rem;
            }
    
            .forgot-password {
                font-size: 0.875rem;
                color: #2ecc71; /* Changed from blue to green */
                text-decoration: none;
            }
    
            .forgot-password:hover {
                color: #27ae60; /* Changed to darker green */
            }
    
            .input-wrapper {
                position: relative;
            }
    
            input {
                width: 100%;
                padding: 0.5rem 0.75rem;
                border: 1px solid #cbd5e1;
                border-radius: 0.375rem;
                font-size: 0.875rem;
                color: #0f172a;
                transition: border-color 0.2s;
            }
    
            input:focus {
                outline: none;
                border-color: #2ecc71; /* Changed from blue to green */
                box-shadow: 0 0 0 1px #2ecc71; /* Changed from blue to green */
            }
    
            .password-input {
                padding-right: 2.5rem;
            }
    
            .toggle-password {
                position: absolute;
                right: 0.75rem;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                cursor: pointer;
                color: #94a3b8;
            }
    
            .toggle-password:hover {
                color: #475569;
            }
    
            .remember-me {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
    
            .checkbox {
                width: 1rem;
                height: 1rem;
                border-radius: 0.25rem;
                accent-color: #2ecc71; /* Changed from blue to green */
            }
    
            .remember-label {
                font-size: 0.875rem;
                color: #475569;
            }
    
            .submit-button {
                width: 100%;
                padding: 0.625rem;
                background-color: #2ecc71; /* Changed from blue to green */
                color: white;
                border: none;
                border-radius: 0.375rem;
                font-weight: 500;
                cursor: pointer;
                transition: background-color 0.2s;
            }
    
            .submit-button:hover {
                background-color: #27ae60; /* Changed to darker green */
            }
    
            .submit-button:disabled {
                background-color: #a7e9c3; /* Changed to lighter green */
                cursor: not-allowed;
            }
    
            .signup-text {
                text-align: center;
                font-size: 0.875rem;
                color: #64748b;
            }
    
            .signup-link {
                color: #2ecc71; /* Changed from blue to green */
                text-decoration: none;
                font-weight: 500;
            }
    
            .signup-link:hover {
                color: #27ae60; /* Changed to darker green */
            }
    
            /* Responsive styles */
            @media (min-width: 768px) {
                .info-panel {
                    display: block;
                }
    
                .login-form-container {
                    width: 50%;
                }
    
                .mobile-logo {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="login-container">
                <!-- Left side - illustration -->
                <div class="info-panel">
                    <div class="info-panel-content">
                        <div class="logo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"></path>
                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                            </svg>
                            <span class="logo-text">SegreBin</span>
                        </div>
                        
                        <div class="info-content">
                            <h1 class="info-title">Smart Bin Monitoring Using Image Processing</h1>
                            <p class="info-description">
                                Monitor bin levels, optimize collection routes, and improve waste management efficiency with our advanced image processing technology.
                            </p>
                            
                            <div class="features">
                                <div class="feature">
                                    <div class="feature-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2" />
                                            <path d="M8 15v-6" />
                                            <path d="M12 15v-3" />
                                            <path d="M16 15V9" />
                                        </svg>
                                    </div>
                                    <div class="feature-content">
                                        <h3>Real-time Analytics</h3>
                                        <p>Monitor fill levels and collection efficiency</p>
                                    </div>
                                </div>
                                
                                <div class="feature">
                                    <div class="feature-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                        </svg>
                                    </div>
                                    <div class="feature-content">
                                        <h3>Secure Access</h3>
                                        <p>Role-based permissions and data protection</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="copyright">
                            © <?php echo date('Y'); ?> SegreBin Technologies
                        </div>
                    </div>
                </div>
                
                <!-- Right side - login form -->
                <div class="login-form-container">
                    <div class="mobile-logo">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2ecc71" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18"></path>
                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                        </svg>
                        <span class="mobile-logo-text">SegreBin</span>
                    </div>
                    
                    <div class="form-wrapper">
                        <div class="form-header">
                            <h2 class="form-title">Welcome back</h2>
                            <p class="form-subtitle">Sign in to your account to continue</p>
                        </div>
                        

                        
                        <?php if ($error): ?>
                        <div class="error-message">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                        <?php endif; ?>
                        
                        <form id="login-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <div class="form-group">
                               <label for="identifier">Username</label>
                                <input 
                                    id="identifier" 
                                    name="identifier"
                                    type="text" 
                                    placeholder="username or you@company.com" 
                                    value="<?php echo htmlspecialchars($identifier); ?>"
                                    required
                                />
                            </div>
                            
                            <div class="form-group">
                                <div class="form-group-header">
                                    <label for="password">Password</label>
                                    <a href="forgot-password.php" class="forgot-password">Forgot password?</a>
                                </div>
                                <div class="input-wrapper">
                                    <input 
                                        id="password" 
                                        name="password"
                                        type="password"
                                        class="password-input"
                                        required
                                    />
                                    <button 
                                        type="button"
                                        class="toggle-password"
                                        id="toggle-password"
                                        aria-label="Toggle password visibility"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                                            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                                            <line x1="2" x2="22" y1="2" y2="22"></line>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="remember-me">
                                <input 
                                    type="checkbox" 
                                    id="remember" 
                                    name="remember"
                                    class="checkbox"
                                    <?php echo $remember ? 'checked' : ''; ?>
                                />
                                <label for="remember" class="remember-label">
                                    Remember me for 30 days
                                </label>
                            </div>
                            
                            <!-- Honeypot field -->
                            <div style="display:none;">
                                <input type="text" name="website" autocomplete="off" tabindex="-1">
                            </div>
                            
                            <button 
                                type="submit" 
                                class="submit-button"
                                id="submit-button"
                            >
                                Sign in
                            </button>
                            
                            <div class="signup-text">
                                Don't have an account?
                                <a href="contact.php" class="signup-link">
                                    Contact administrator
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const passwordInput = document.getElementById('password');
                const togglePasswordButton = document.getElementById('toggle-password');
    
                // Toggle password visibility
                togglePasswordButton.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Change the eye icon
                    if (type === 'password') {
                        this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                            <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
                            <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
                            <line x1="2" x2="22" y1="2" y2="22"></line>
                        </svg>`;
                    } else {
                        this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>`;
                    }
                });
            });
        </script>
    </body>
    </html>