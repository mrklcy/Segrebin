<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $FName = $_POST['FName'];
    $MName = $_POST['MName'];
    $LName = $_POST['LName'];
    $Age = $_POST['Age'];
    $Position = $_POST['Position'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Input Validation
    if (empty($FName) || empty($LName) || empty($email) || empty($address) || empty($username) || empty($password)) {
        echo "All required fields must be filled.";
        exit;
    }

    if (!filter_var($Age, FILTER_VALIDATE_INT)) {
        echo "Age must be a number.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit;
    }

    if (strlen($address) < 5 || !preg_match("/^[a-zA-Z0-9\s,.-]+$/", $address)) {
        echo "Address must be at least 5 characters and contain only valid characters.";
        exit;
    }

    if (strlen($username) < 4 || !preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
        echo "Username must be at least 4 characters long and contain only letters, numbers, or underscores.";
        exit;
    }

    // Allow List: Only accept predefined positions
    $allowed_positions = ['Admin', 'Driver', 'Collector'];
    if (!in_array($Position, $allowed_positions)) {
        echo "Invalid position selected.";
        exit;
    }

    // Check for duplicate username
    $check = $conn->prepare("SELECT id FROM personel WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo "Username is already taken.";
        exit;
    }
    $check->close();

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL Insert Statement
    $stmt = $conn->prepare("INSERT INTO personel (FName, MName, LName, Age, Position, email, address, username, password)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisssss", $FName, $MName, $LName, $Age, $Position, $email, $address, $username, $hashed_password);

    // Execute and handle result
    if ($stmt->execute()) {
        header("Location: ../personel.php?success=added");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>
