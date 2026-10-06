<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once "php/config.php"; // Ensure this is correctly pointing to your DB config

$response = [
    'wasteData' => [],
    'monthlyData' => [],
    'historyData' => []
];

// ✅ Ensure database is connected
if (!$conn) {
    die(json_encode(["error" => "Database connection failed"]));
}

$wasteTypeFilter = isset($_GET['waste_type']) && $_GET['waste_type'] !== 'all' ? $_GET['waste_type'] : null;
$dateFilter = isset($_GET['date_range']) ? $_GET['date_range'] : '30';

// ✅ Fetch Waste Distribution Data
$sql = "SELECT waste_type, SUM(amount_kg) AS total_amount 
        FROM collection 
        WHERE collection_date >= CURDATE() - INTERVAL ? DAY";

$params = [$dateFilter];
$types = "i";

if ($wasteTypeFilter) {
    $sql .= " AND waste_type = ?";
    $params[] = $wasteTypeFilter;
    $types .= "s";
}
$sql .= " GROUP BY waste_type";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $response['wasteData'][] = $row;
}
$stmt->close();

// ✅ Fetch Monthly Trends Data
$sql = "SELECT DATE_FORMAT(collection_date, '%Y-%m') AS month, SUM(amount_kg) AS total_amount 
        FROM collection 
        WHERE collection_date >= CURDATE() - INTERVAL ? DAY";

$params = [$dateFilter];
$types = "i";

if ($wasteTypeFilter) {
    $sql .= " AND waste_type = ?";
    $params[] = $wasteTypeFilter;
    $types .= "s";
}
$sql .= " GROUP BY month ORDER BY month ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $response['monthlyData'][] = $row;
}
$stmt->close();

// ✅ Fetch Collection History
$sql = "SELECT collection_date, waste_type, amount_kg, collector_name 
        FROM collection 
        WHERE collection_date >= CURDATE() - INTERVAL ? DAY";

$params = [$dateFilter];
$types = "i";

if ($wasteTypeFilter) {
    $sql .= " AND waste_type = ?";
    $params[] = $wasteTypeFilter;
    $types .= "s";
}
$sql .= " ORDER BY collection_date DESC LIMIT 30";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $response['historyData'][] = $row;
}
$stmt->close();

// ✅ Debugging: Log output for testing
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>
