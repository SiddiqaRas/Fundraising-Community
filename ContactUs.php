<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fundraisingcommunity";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST["name"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $message = htmlspecialchars(trim($_POST["message"]));

    // Server-side validation
    if (empty($name)) {
        die("Name cannot be empty.");
    } elseif(!preg_match("/^[a-zA-Z0-9\s]{3,20}$/", $name)) {
        die("Error: Name must be between 3 and 20 characters and can only contain letters, numbers, and spaces.");
    }
    }

    if (empty($email)) {
        die("Email address cannot be empty.");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email address.");
    }

    if (empty($message)) {
        die("Message cannot be empty.");
    } elseif (strlen($message) < 10) {
        die("Message must be at least 10 characters long.");
    } elseif (strlen($message) > 1500) {
        die("Message is too long. Limit is 1500 characters.");
    }

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO contactus (Name, Email, Message) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sss", $name, $email, $message);

        if ($stmt->execute()) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error in preparing statement: " . $conn->error;
    }
}

$conn->close();
?>
