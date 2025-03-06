<?php
// Starting Session
session_start();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fundraisingcommunity";

// Creating connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Checking connection
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
    return; // Stop further execution if connection fails
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST["email"]) ? htmlspecialchars(trim($_POST["email"])) : '';
    $password = isset($_POST["password"]) ? htmlspecialchars(trim($_POST["password"])) : '';

    // Server-side validation
    if (empty($email)) {
        echo "Email address cannot be empty.";
        return;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email address.";
        return;
    }
    if (empty($password)) {
        echo "Password cannot be empty.";
        return;
    }elseif (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,20}$/", $password)){
        echo "Error: Password must be 8-20 characters long, contain at least one uppercase letter, one lowercase letter, one digit, and one special character.";
        return;
    }
    // Execution of the query to fetch user data
    $stmt = $conn->prepare("SELECT admin_id, name, password FROM admin_signup WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($admin_id, $admin_name, $hashed_password);
            $stmt->fetch();

            // Verify the password
            if (password_verify($password, $hashed_password)) {
                // Store relevant data in the session
                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['admin_name'] = $admin_name;
                $_SESSION['email'] = $email;

                // Redirect to the next page after successful login
                header("Location: AHome.php");
                exit();
            } else {
                echo "Incorrect password.";
                return;
            }
        } else {
            echo "No account found with that email address.";
            return;
        }

        $stmt->close();
    } else {
        echo "Error in preparing statement: " . $conn->error;
        return;
    }
}

$conn->close();
?>
