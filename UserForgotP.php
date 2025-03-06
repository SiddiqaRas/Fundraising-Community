<?php
// Database connection details
$servername = "localhost";
$username = "root";
$dbpassword = "";
$dbname = "fundraisingcommunity";

// Create connection
$conn = new mysqli($servername, $username, $dbpassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Server-side validation and password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form data
    $userEmail = isset($_POST['userEmail']) ? trim($_POST['userEmail']) : '';
    $userPassword = isset($_POST['usernPassword']) ? $_POST['usernPassword'] : '';
    $userConfirmPassword = isset($_POST['usercPassword']) ? $_POST['usercPassword'] : '';


    $errors = []; // Array to hold errors

    // Validate email
    if (empty($userEmail)) {
        $errors['userEmail'] = 'Email address cannot be empty.';
    } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $errors['userEmail'] = 'Invalid email address.';
    } else {
        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $userEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors['userEmail'] = 'Email address not found.';
        }
        $stmt->close();
    }

    // Validate password
    if (empty($userPassword)) {
        $errors['userPassword'] = 'New password cannot be empty.';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,20}$/', $userPassword)) {
        $errors['userPassword'] = 'Password must be 8-20 characters long, contain at least one uppercase letter, one lowercase letter ,one digit and one special character.';
    }

    if (empty($userConfirmPassword)) {
        $errors['userConfirmPassword'] = 'password cannot be empty.';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,20}$/', $userConfirmPassword)) {
        $errors['userConfirmPassword'] = 'Password must be 8-20 characters long, contain at least one uppercase letter, one lowercase letter ,one digit and one special character.';
    }

    // Confirm password
    if ($userPassword !== $userConfirmPassword) {
        $errors['userConfirmPassword'] = 'Passwords do not match.';
    }

    // If no errors, proceed to update the password in the database
    if (empty($errors)) {
        $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);

        // Update the password in the database
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $userEmail);

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