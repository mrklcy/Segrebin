<?php
$host = "localhost";
$dbname = "u693579284_segrebin";
$username = "u693579284_root";
$password = "Segrebin098624";

// Get POST data
$wasteType = $_POST['waste_type'] ?? '';
$amountKg = $_POST['amount_kg'] ?? '';
$collectorName = $_POST['collector_name'] ?? '';
$collectionDate = date("Y-m-d H:i:s");  // Server time

if ($wasteType === '' || $amountKg === '' || $collectorName === '') {
    echo "Missing required fields.";
    exit;
}

// DB connection
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert into collection
$stmt = $conn->prepare("INSERT INTO collection (collection_date, waste_type, amount_kg, collector_name) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssds", $collectionDate, $wasteType, $amountKg, $collectorName);
$stmt->execute();

echo "Collection data inserted successfully";
$stmt->close();
$conn->close();
?>
