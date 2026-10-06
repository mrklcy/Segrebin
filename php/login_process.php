<?php
session_start();

$host = "mysql.hostinger.com";
$dbname = "u693579284_segrebin";
$username = "u693579284_root";
$password = "Segrebin098624";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty($_POST["admin"]) || empty($_POST["password"])) {
            header("Location: ../login.php?error=emptyfields");
            exit();
        }

        $admin = trim($_POST["admin"]);
        $password = $_POST["password"];

        // Secure query using prepared statements
        $stmt = $conn->prepare("SELECT password FROM admin WHERE admin = :admin LIMIT 1");
        $stmt->bindParam(":admin", $admin, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch();

        // Verify password using password_verify() (this is where hashing happens)
        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["loggedin"] = true;
            $_SESSION["admin"] = $admin;
            header("Location: ../dashboard.php");
            exit();
        } else {
            header("Location: ../login.php?error=invalidcredentials");
            exit();
        }
    }
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage(), 3, "../error_log.txt");
    header("Location: ../login.php?error=servererror");
    exit();
}
?>
