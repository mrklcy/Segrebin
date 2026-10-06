<?php
require_once "config.php";

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Add error logging
error_log("process_employee.php called with action: " . ($_POST['action'] ?? 'none'));

// Check database connection
if (!isset($conn) || $conn->connect_error) {
    error_log("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            addEmployee();
            break;
        case 'edit':
            editEmployee();
            break;
        case 'delete':
            deleteEmployee();
            break;
        case 'archive':
            error_log("Archive action received, calling archiveEmployee()");
            archiveEmployee();
            break;
        case 'get_archived':
            getArchivedEmployees();
            break;
        case 'restore':
            restoreEmployee();
            break;
        case 'get_all':
            getAllEmployees();
            break;
        case 'test_db':
            testDatabase();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function addEmployee() {
    global $conn;
    
    // Collect form data
    $employeeName = trim($_POST['employeeName'] ?? '');
    $position = trim($_POST['employeePosition'] ?? '');
    $username = trim($_POST['employeeUsername'] ?? '');
    $password = $_POST['employeePassword'] ?? '';
    $dateEmployed = $_POST['employeeDate'] ?? '';
    
    // Input Validation
    if (empty($employeeName) || empty($position) || empty($username) || empty($password) || empty($dateEmployed)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
        return;
    }
    
    // Validate position
    $allowed_positions = ['Driver', 'Waste Collector', 'Admin', 'Supervisor', 'Mechanic'];
    if (!in_array($position, $allowed_positions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid position selected.']);
        return;
    }
    
    // Validate username format
    if (strlen($username) < 3 || !preg_match("/^[a-zA-Z0-9._]+$/", $username)) {
        echo json_encode(['success' => false, 'message' => 'Username must be at least 3 characters and contain only letters, numbers, dots, or underscores.']);
        return;
    }
    
    // Validate password strength
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long.']);
        return;
    }
    
    // Check for duplicate username
    $check = $conn->prepare("SELECT id FROM employees WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username is already taken.']);
        $check->close();
        return;
    }
    $check->close();
    
    // Generate employee number
    $stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(employee_number, 5) AS UNSIGNED)) as max_num FROM employees");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $nextNum = ($row['max_num'] ?? 0) + 1;
    $employeeNumber = 'EMP-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    $stmt->close();
    
    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Handle image upload if provided
    $profileImage = null;
    if (isset($_FILES['employeePicture']) && $_FILES['employeePicture']['error'] == 0) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $fileType = $_FILES['employeePicture']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            $uploadDir = '../uploads/employees/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = time() . '_' . $_FILES['employeePicture']['name'];
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['employeePicture']['tmp_name'], $filePath)) {
                $profileImage = 'uploads/employees/' . $fileName;
            }
        }
    }
    
    // Prepare SQL Insert Statement
    $stmt = $conn->prepare("INSERT INTO employees (employee_name, position, employee_number, username, password, date_employed, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $employeeName, $position, $employeeNumber, $username, $hashed_password, $dateEmployed, $profileImage);
    
    // Execute and handle result
    if ($stmt->execute()) {
        $newEmployeeId = $conn->insert_id;
        echo json_encode([
            'success' => true, 
            'message' => 'Employee added successfully!',
            'employee' => [
                'id' => $newEmployeeId,
                'employee_number' => $employeeNumber,
                'employee_name' => $employeeName,
                'position' => $position,
                'username' => $username,
                'date_employed' => $dateEmployed,
                'profile_image' => $profileImage
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding employee: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function editEmployee() {
    global $conn;
    
    $employeeId = $_POST['employeeId'] ?? '';
    $employeeName = trim($_POST['employeeName'] ?? '');
    $position = trim($_POST['employeePosition'] ?? '');
    
    if (empty($employeeId) || empty($employeeName) || empty($position)) {
        echo json_encode(['success' => false, 'message' => 'Required fields cannot be empty.']);
        return;
    }
    
    // Validate position
    $allowed_positions = ['Driver', 'Waste Collector', 'Admin', 'Supervisor', 'Mechanic'];
    if (!in_array($position, $allowed_positions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid position selected.']);
        return;
    }
    
    // Handle image upload if provided
    $profileImage = null;
    if (isset($_FILES['employeePicture']) && $_FILES['employeePicture']['error'] == 0) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $fileType = $_FILES['employeePicture']['type'];
        
        if (in_array($fileType, $allowedTypes)) {
            $uploadDir = '../uploads/employees/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = time() . '_' . $_FILES['employeePicture']['name'];
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['employeePicture']['tmp_name'], $filePath)) {
                $profileImage = 'uploads/employees/' . $fileName;
            }
        }
    }
    
    // Update employee
    if ($profileImage) {
        $stmt = $conn->prepare("UPDATE employees SET employee_name = ?, position = ?, profile_image = ? WHERE id = ?");
        $stmt->bind_param("sssi", $employeeName, $position, $profileImage, $employeeId);
    } else {
        $stmt = $conn->prepare("UPDATE employees SET employee_name = ?, position = ? WHERE id = ?");
        $stmt->bind_param("ssi", $employeeName, $position, $employeeId);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Employee updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating employee: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function deleteEmployee() {
    global $conn;
    
    $employeeId = $_POST['employeeId'] ?? '';
    
    if (empty($employeeId)) {
        echo json_encode(['success' => false, 'message' => 'Employee ID is required.']);
        return;
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get employee data to confirm deletion
        $stmt = $conn->prepare("SELECT employee_name FROM employees WHERE id = ?");
        $stmt->bind_param("i", $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();
        $stmt->close();
        
        if (!$employee) {
            throw new Exception('Employee not found');
        }
        
        // Permanently delete from employees table
        $deleteStmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $deleteStmt->bind_param("i", $employeeId);
        
        if (!$deleteStmt->execute()) {
            throw new Exception('Failed to delete employee: ' . $deleteStmt->error);
        }
        $deleteStmt->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Employee permanently deleted successfully!']);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error deleting employee: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error deleting employee: ' . $e->getMessage()]);
    }
}

function archiveEmployee() {
    global $conn;
    
    $employeeId = $_POST['employeeId'] ?? '';
    
    if (empty($employeeId)) {
        echo json_encode(['success' => false, 'message' => 'Employee ID is required.']);
        return;
    }
    
    error_log("Starting archive process for employee ID: " . $employeeId);
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get employee data to archive
        $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->bind_param("i", $employeeId);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();
        $stmt->close();
        
        if (!$employee) {
            error_log("Employee not found with ID: " . $employeeId);
            throw new Exception('Employee not found with ID: ' . $employeeId);
        }
        
        error_log("Found employee to archive: " . $employee['employee_name']);
        
        // Create archive table if it doesn't exist
        $createArchiveTable = "CREATE TABLE IF NOT EXISTS `employee_archive` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `original_id` int(11) NOT NULL,
            `employee_name` varchar(255) NOT NULL,
            `position` varchar(100) NOT NULL,
            `employee_number` varchar(20) NOT NULL,
            `username` varchar(100) NOT NULL,
            `password` varchar(255) NOT NULL,
            `date_employed` date NOT NULL,
            `profile_image` varchar(500) DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `archived_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($createArchiveTable);
        
        // Insert into archive table
        $archiveStmt = $conn->prepare("INSERT INTO employee_archive (original_id, employee_name, position, employee_number, username, password, date_employed, profile_image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $archiveStmt->bind_param("issssssss", 
            $employee['id'],
            $employee['employee_name'],
            $employee['position'],
            $employee['employee_number'],
            $employee['username'],
            $employee['password'],
            $employee['date_employed'],
            $employee['profile_image'],
            $employee['created_at']
        );
        
        if (!$archiveStmt->execute()) {
            throw new Exception('Failed to archive employee: ' . $archiveStmt->error);
        }
        $archiveStmt->close();
        
        // Delete from active employees table
        $deleteStmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $deleteStmt->bind_param("i", $employeeId);
        
        if (!$deleteStmt->execute()) {
            throw new Exception('Failed to delete employee: ' . $deleteStmt->error);
        }
        $deleteStmt->close();
        
        // Commit transaction
        $conn->commit();
        
        error_log("Successfully archived employee: " . $employee['employee_name'] . " (ID: " . $employeeId . ")");
        $response = ['success' => true, 'message' => 'Employee moved to archive successfully!'];
        error_log("Sending response: " . json_encode($response));
        echo json_encode($response);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error archiving employee ID " . $employeeId . ": " . $e->getMessage());
        $errorResponse = ['success' => false, 'message' => 'Error archiving employee: ' . $e->getMessage()];
        error_log("Sending error response: " . json_encode($errorResponse));
        echo json_encode($errorResponse);
    }
}

function getAllEmployees() {
    global $conn;
    
    try {
        error_log("getAllEmployees function called");
        
        // Check if employees table exists
        $checkTable = $conn->query("SHOW TABLES LIKE 'employees'");
        if ($checkTable->num_rows == 0) {
            error_log("Employees table does not exist");
            echo json_encode(['success' => false, 'message' => 'Employees table does not exist']);
            return;
        }
        
        $stmt = $conn->prepare("SELECT id, employee_name, position, employee_number, username, password, date_employed, profile_image FROM employees ORDER BY created_at DESC");
        if (!$stmt) {
            error_log("Prepare statement failed: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Database prepare failed: ' . $conn->error]);
            return;
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        
        $stmt->close();
        
        // Add debugging information
        error_log("Retrieved " . count($employees) . " employees from database");
        if (count($employees) > 0) {
            error_log("Sample employee data: " . json_encode($employees[0]));
        }
        
        echo json_encode(['success' => true, 'employees' => $employees]);
    } catch (Exception $e) {
        error_log("Error in getAllEmployees: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function testDatabase() {
    global $conn;
    
    try {
        error_log("Testing database connection...");
        
        // Test basic connection
        if ($conn->ping()) {
            error_log("Database connection is working");
        } else {
            error_log("Database connection failed ping");
        }
        
        // Test if employees table exists
        $result = $conn->query("SHOW TABLES LIKE 'employees'");
        $tableExists = $result->num_rows > 0;
        error_log("Employees table exists: " . ($tableExists ? 'Yes' : 'No'));
        
        // Test if table has data
        if ($tableExists) {
            $countResult = $conn->query("SELECT COUNT(*) as count FROM employees");
            $count = $countResult->fetch_assoc()['count'];
            error_log("Employees table has " . $count . " records");
            
            // Show sample data
            $sampleResult = $conn->query("SELECT * FROM employees LIMIT 1");
            if ($sampleResult->num_rows > 0) {
                $sample = $sampleResult->fetch_assoc();
                error_log("Sample employee: " . json_encode($sample));
            }
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Database test completed',
            'connection' => $conn->ping() ? 'OK' : 'Failed',
            'table_exists' => $tableExists,
            'record_count' => $tableExists ? $count : 0
        ]);
        
    } catch (Exception $e) {
        error_log("Error in testDatabase: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database test error: ' . $e->getMessage()]);
    }
}

function getArchivedEmployees() {
    global $conn;
    
    try {
        // Check if archive table exists
        $checkTable = $conn->query("SHOW TABLES LIKE 'employee_archive'");
        if ($checkTable->num_rows == 0) {
            echo json_encode(['success' => true, 'employees' => []]);
            return;
        }
        
        $stmt = $conn->prepare("SELECT * FROM employee_archive ORDER BY archived_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        
        $stmt->close();
        
        echo json_encode(['success' => true, 'employees' => $employees]);
        
    } catch (Exception $e) {
        error_log("Error in getArchivedEmployees: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function restoreEmployee() {
    global $conn;
    
    $archiveId = $_POST['archiveId'] ?? '';
    
    if (empty($archiveId)) {
        echo json_encode(['success' => false, 'message' => 'Archive ID is required.']);
        return;
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get archived employee data
        $stmt = $conn->prepare("SELECT * FROM employee_archive WHERE id = ?");
        $stmt->bind_param("i", $archiveId);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();
        $stmt->close();
        
        if (!$employee) {
            throw new Exception('Archived employee not found');
        }
        
        // Check if username or employee number already exists in active table
        $checkStmt = $conn->prepare("SELECT id FROM employees WHERE username = ? OR employee_number = ?");
        $checkStmt->bind_param("ss", $employee['username'], $employee['employee_number']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $checkStmt->close();
        
        if ($checkResult->num_rows > 0) {
            throw new Exception('Cannot restore: Username or employee number already exists');
        }
        
        // Insert back into active employees table
        $restoreStmt = $conn->prepare("INSERT INTO employees (employee_name, position, employee_number, username, password, date_employed, profile_image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $restoreStmt->bind_param("ssssssss", 
            $employee['employee_name'],
            $employee['position'],
            $employee['employee_number'],
            $employee['username'],
            $employee['password'],
            $employee['date_employed'],
            $employee['profile_image'],
            $employee['created_at']
        );
        
        if (!$restoreStmt->execute()) {
            throw new Exception('Failed to restore employee: ' . $restoreStmt->error);
        }
        $restoreStmt->close();
        
        // Delete from archive table
        $deleteStmt = $conn->prepare("DELETE FROM employee_archive WHERE id = ?");
        $deleteStmt->bind_param("i", $archiveId);
        
        if (!$deleteStmt->execute()) {
            throw new Exception('Failed to remove from archive: ' . $deleteStmt->error);
        }
        $deleteStmt->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Employee restored successfully!']);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error restoring employee: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error restoring employee: ' . $e->getMessage()]);
    }
}
?>
