<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Function to calculate fill level percentage from ultrasonic sensor reading
function calculateFillLevel($sensorReading, $binHeight, $emptyDistance) {
    // Convert sensor reading to distance from sensor to waste surface
    $distanceToWaste = $sensorReading;
    
    // Calculate actual waste height
    $wasteHeight = $binHeight - $distanceToWaste;
    
    // Calculate fill percentage (0-100%)
    $fillPercentage = ($wasteHeight / $binHeight) * 100;
    
    // Ensure percentage is within valid range
    $fillPercentage = max(0, min(100, $fillPercentage));
    
    return round($fillPercentage, 1);
}

// Function to determine bin status based on fill level
function getBinStatus($fillLevel) {
    if ($fillLevel >= 90) {
        return 'full';
    } elseif ($fillLevel >= 70) {
        return 'high';
    } elseif ($fillLevel >= 40) {
        return 'medium';
    } else {
        return 'low';
    }
}

// Handle POST request from ultrasonic sensor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception('Invalid JSON input');
        }
        
        // Validate required fields
        $required_fields = ['bin_id', 'sensor_reading', 'bin_height', 'empty_distance', 'timestamp'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
        
        $bin_id = $input['bin_id'];
        $sensor_reading = floatval($input['sensor_reading']);
        $bin_height = floatval($input['bin_height']);
        $empty_distance = floatval($input['empty_distance']);
        $timestamp = $input['timestamp'];
        
        // Calculate fill level
        $fill_level = calculateFillLevel($sensor_reading, $bin_height, $empty_distance);
        $status = getBinStatus($fill_level);
        
        // Begin transaction
        $conn->begin_transaction();
        
        try {
            // Update bin table with new fill level
            $update_bin = $conn->prepare("
                UPDATE bins 
                SET fill_level = ?, 
                    last_updated = NOW(),
                    status = ?
                WHERE bin_id = ?
            ");
            $update_bin->bind_param("ds", $fill_level, $status, $bin_id);
            $update_bin->execute();
            
            if ($update_bin->affected_rows === 0) {
                throw new Exception("Bin not found: $bin_id");
            }
            
            // Insert sensor reading log
            $insert_reading = $conn->prepare("
                INSERT INTO sensor_readings 
                (bin_id, sensor_reading, bin_height, empty_distance, calculated_fill_level, timestamp, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $insert_reading->bind_param("sdddd", $bin_id, $sensor_reading, $bin_height, $empty_distance, $fill_level, $timestamp);
            $insert_reading->execute();
            
            // Insert bin update log
            $insert_update = $conn->prepare("
                INSERT INTO bin_updates 
                (bin_id, action, details, timestamp)
                VALUES (?, 'sensor_update', ?, NOW())
            ");
            $update_details = "Fill level updated to {$fill_level}% via ultrasonic sensor";
            $insert_update->bind_param("ss", $bin_id, $update_details);
            $insert_update->execute();
            
            // Check if bin needs immediate attention
            if ($fill_level >= 90) {
                // Insert alert for full bin
                $insert_alert = $conn->prepare("
                    INSERT INTO bin_alerts 
                    (bin_id, alert_type, message, severity, timestamp, is_resolved)
                    VALUES (?, 'bin_full', ?, 'high', NOW(), 0)
                ");
                $alert_message = "Bin is {$fill_level}% full and requires immediate collection";
                $insert_alert->bind_param("ss", $bin_id, $alert_message);
                $insert_alert->execute();
            }
            
            // Commit transaction
            $conn->commit();
            
            // Return success response
            echo json_encode([
                'success' => true,
                'message' => 'Sensor data processed successfully',
                'data' => [
                    'bin_id' => $bin_id,
                    'fill_level' => $fill_level,
                    'status' => $status,
                    'timestamp' => $timestamp
                ]
            ]);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

// Handle GET request to retrieve bin levels
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $action = $_GET['action'] ?? null;
        $bin_id = $_GET['bin_id'] ?? null;
        
        if ($action === 'get_bins') {
            // Get the three main bins for dashboard refresh
            $stmt = $conn->prepare("
                SELECT b.bin_id, b.location, b.waste_type, b.fill_level, b.status, b.last_updated,
                       sc.trigger_pin, sc.echo_pin, sc.low_threshold, sc.medium_threshold, sc.high_threshold
                FROM bins b 
                LEFT JOIN sensor_config sc ON b.bin_id = sc.bin_id 
                WHERE b.bin_id IN ('BIN001', 'BIN002', 'BIN003') 
                ORDER BY b.bin_id
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $bins = [];
            while ($row = $result->fetch_assoc()) {
                $bins[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'bins' => $bins
            ]);
            
        } elseif ($bin_id) {
            // Get specific bin data
            $stmt = $conn->prepare("
                SELECT b.*, 
                       sr.sensor_reading, 
                       sr.calculated_fill_level,
                       sr.timestamp as last_sensor_reading
                FROM bins b
                LEFT JOIN sensor_readings sr ON b.bin_id = sr.bin_id
                WHERE b.bin_id = ?
                ORDER BY sr.timestamp DESC
                LIMIT 1
            ");
            $stmt->bind_param("s", $bin_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if (!$result) {
                throw new Exception("Bin not found: $bin_id");
            }
            
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            
        } else {
            // Get all bins with latest sensor data
            $stmt = $conn->prepare("
                SELECT b.*, 
                       COALESCE(sr.calculated_fill_level, b.fill_level) as current_fill_level,
                       sr.timestamp as last_sensor_reading
                FROM bins b
                LEFT JOIN (
                    SELECT bin_id, calculated_fill_level, timestamp
                    FROM sensor_readings sr1
                    WHERE timestamp = (
                        SELECT MAX(timestamp) 
                        FROM sensor_readings sr2 
                        WHERE sr2.bin_id = sr1.bin_id
                    )
                ) sr ON b.bin_id = sr.bin_id
                ORDER BY b.location, b.waste_type
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $bins = [];
            while ($row = $result->fetch_assoc()) {
                $bins[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $bins
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}
?>
