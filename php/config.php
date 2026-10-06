<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$dbname = "u693579284_segrebin";
$username = "u693579284_root";
$password = "Segrebin098624";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $username, $password, $dbname);
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
