<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fundraisingcommunity";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$adminName = "Saba";
$adminEmail = "saba@gmail.com";
$adminPassword = "F4c269a2/6"; // Plain text password
// Array to store validation errors
$errors = [];

// Check for empty fields
if (empty($adminName) || empty($adminEmail) || empty($adminPassword)) {
    $errors[] = "Error: All fields are required.";
}

// Name Validation: Allows letters, numbers, and spaces, and requires 3 to 20 characters
if (!preg_match("/^[a-zA-Z0-9\s]{3,20}$/", $adminName)) {
    $errors[] = "Error: Name must be between 3 and 20 characters and can only contain letters, numbers, and spaces.";
}

// Email Validation: Checks if the email is in a standard format
if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Error: Invalid email format.";
}

// Check if email already exists
$sql = "SELECT admin_id FROM admin_signup WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $adminEmail);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $errors[] = "Error: An account with this email already exists.";
}

// Password Strength Validation: 8 to 20 characters, includes uppercase, lowercase, and number
if (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,20}$/", $adminPassword)) {
    $errors[] = "Error: Password must be 8-20 characters long, contain at least one uppercase letter, one lowercase letter,one digit nd one special character.";
}

// If there are any errors, display them and stop further processing
if (!empty($errors)) {
    echo implode("<br>", $errors);
} else {
    // No errors, proceed with inserting admin data

    // Hash the password
    $hashedPassword = password_hash($adminPassword, PASSWORD_BCRYPT);

    // Insert admin data
    $sql = "INSERT INTO admin_signup (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $adminName, $adminEmail, $hashedPassword);

    if ($stmt->execute()) {
        echo "Admin registered successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Close connections
$stmt->close();
$conn->close();
?>
