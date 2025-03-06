<?php
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

// Server-side validation and password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form data
    $adminEmail = trim($_POST['adminEmail']);
    $adminPassword = $_POST['adminnPassword'];
    $adminConfirmPassword = $_POST['admincPassword'];

    $errors = []; // Array to hold errors

    // Validate email
    if (empty($adminEmail)) {
        $errors['adminEmail'] = 'Email address cannot be empty.';
    } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $errors['adminEmail'] = 'Invalid email address.';
    } else {
        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT * FROM admin_signup WHERE email = ?");
        $stmt->bind_param("s", $adminEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors['adminEmail'] = 'Email address not found.';
        }
        $stmt->close();
    }

    // Validate password
    if (empty($adminPassword)) {
        $errors['adminPassword'] = 'New password cannot be empty.';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,20}$/', $adminPassword)) {
        $errors['adminPassword'] = 'Password must be 8-20 characters long, contain at least one uppercase letter, one lowercase letter, one digit and one special character.';
    }

    // Confirm password
    if ($adminPassword !== $adminConfirmPassword) {
        $errors['adminConfirmPassword'] = 'Passwords do not match.';
    }

    // If no errors, proceed to update the password in the database
    if (empty($errors)) {
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

        // Update the password in the database
        $stmt = $conn->prepare("UPDATE admin_signup SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $adminEmail);

        if ($stmt->execute()) {
            echo "Password reset successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        // Output errors in a readable format
        echo "<ul>";
        foreach ($errors as $key => $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
        echo '<button onclick="window.location.href=\'ForgotP.html\'">Forgot Password</button>';
    }
}

// Close the connection
$conn->close();
?>