<?php
// Starting session
session_start();

// Checking if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // If not logged in, redirect to login page
    header("Location: Login.html");
    exit();
}

// Assign the admin ID
$adminID = $_SESSION['admin_id'];

$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';

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
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $feedbackTitle = htmlspecialchars(trim($_POST["feedback-title"]));
    $feedbackType = htmlspecialchars(trim($_POST["feedback-type"]));
    $feedbackDetails = htmlspecialchars(trim($_POST["feedback-details"]));
    $priorityLevel = htmlspecialchars(trim($_POST["priority-level"]));

    $errors = []; // Array to hold validation errors

    // Server-side validation
    if (empty($feedbackTitle)) {
        $errors['title'] = "Feedback Title cannot be empty.";
    } elseif (strlen($feedbackTitle) < 3 || strlen($feedbackTitle) > 30) {
        $errors['title'] = "Feedback Title must be between 3 and 15 characters.";
    }

    if (empty($feedbackType)) {
        $errors['type'] = "Feedback Type must be selected.";
    }

    if (empty($feedbackDetails)) {
        $errors['details'] = "Feedback Details cannot be empty.";
    } elseif (strlen($feedbackDetails) < 10 || strlen($feedbackDetails) > 1000) {
        $errors['details'] = "Feedback Details must be between 10 and 600 characters.";
    }

    if (empty($priorityLevel)) {
        $errors['priority'] = "Priority Level must be selected.";
    }

    // If no errors, proceed to insert feedback
    if (empty($errors)) {
        // Execution of query
        $stmt = $conn->prepare("INSERT INTO feedback (feedback_title, feedback_type, feedback_details, priority_level, adminID) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssi", $feedbackTitle, $feedbackType, $feedbackDetails, $priorityLevel, $adminID);

            if ($stmt->execute()) {
                echo "<script>alert('Feedback submitted successfully');</script>";
                header("Location: AHome.php");
                exit();
            } else {
                echo "<script>alert('Error: " . $stmt->error . "');</script>";
            }

            $stmt->close();
        } else {
            echo "<script>alert('Error in preparing statement: " . $conn->error . "');</script>";
        }
    } else {
        foreach ($errors as $error) {
            echo "<script>alert('$error');</script>";
        }
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="Feedback.css" rel="stylesheet">
    <title>Feedback</title>
    <style>
        body {
            background-color: <?php echo $theme === 'dark' ? '#3e3636' : '#f4f4f4'; ?>;
        }
        .title, .form-label {
            color: <?php echo $theme === 'dark' ? '#fff' : '#000'; ?>;
        }
        .format-info {
            font-size: 0.875em;
            color: <?php echo $theme === 'dark' ? '#fff' : '#000'; ?>;
            margin-top: 5px;
        }
        .alert-danger {
            background-color: <?php echo $theme === 'dark' ? '#3e3636' : '#f4f4f4'; ?>;
            color: red;
        }
        .btn-primary{
            background-color:black !important; 

        }
    </style>
</head>
<body>
    <!-- NAVBAR STARTS -->
    <nav class="navbar sticky-top navbar-expand-lg navbar-dark" style="background-color: black;">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="AHome.php">
                <img src="logo.PNG" alt="" width="50" height="40">
                <span class="ms-2">FundRaisingC.</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="AHome.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="Queries.php">Queries</a></li>
                    <li class="nav-item"><a class="nav-link" href="Feedback.php">Feedback</a></li>
                    <li class="nav-item"><a class="nav-link" href="OtherCampaigns.php">AddCampaign</a></li>
                </ul>
            </div>
            <div class="d-flex align-items-center">
                <a href="Amanageprofile.php" class="text-white me-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-person-fill" viewBox="0 0 16 16">
                        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                    </svg>
                </a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm px-4" style="border-radius: 20px;" onclick="return confirm('Are you sure you want to log out?')">
                    <i class="fa fa-sign-out"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    <!-- NAVBAR ENDS -->

    <!-- Feedback Form -->
    <div class="container">
        <div class="col-md-6">
            <h2 style="margin-top: 15px;" class="title">Submit Feedback</h2>
            <form style="width: 500px;" method="post" action="Feedback.php" id="feedbackForm" onsubmit="return validateUserForm()">
                <div id="feedbackError1" class="alert alert-danger" style="display: <?php echo isset($errors['title']) ? 'block' : 'none'; ?>;"><?php echo $errors['title'] ?? ''; ?></div>
                <div class="mb-3">
                    <label for="feedback-title" class="form-label">Feedback Title</label>
                    <input type="text" class="form-control" id="feedbackTitle" name="feedback-title" value="<?php echo htmlspecialchars($feedbackTitle ?? ''); ?>" placeholder="Enter Feedback Title">
                    <div class="format-info">Must be 3-30 characters and can include letters,numbers, spaces, hyphens</div>
                </div>
                <div id="feedbackError2" class="alert alert-danger" style="display: <?php echo isset($errors['type']) ? 'block' : 'none'; ?>;"><?php echo $errors['type'] ?? ''; ?></div>
                <div class="mb-3">
                    <label for="feedback-type" class="form-label">Feedback Type</label>
                    <select class="form-control" id="feedbackType" name="feedback-type">
                        <option value="">Select Feedback Type</option>
                        <option value="system-issue" <?php echo (isset($feedbackType) && $feedbackType == 'system-issue') ? 'selected' : ''; ?>>System Issue</option>
                        <option value="user-inquiry" <?php echo (isset($feedbackType) && $feedbackType == 'user-inquiry') ? 'selected' : ''; ?>>User Inquiry</option>
                        <option value="general-comment" <?php echo (isset($feedbackType) && $feedbackType == 'general-comment') ? 'selected' : ''; ?>>General Comment</option>
                        <option value="suggestion" <?php echo (isset($feedbackType) && $feedbackType == 'suggestion') ? 'selected' : ''; ?>>Suggestion</option>
                    </select>
                </div>
                <div id="feedbackError3" class="alert alert-danger" style="display: <?php echo isset($errors['details']) ? 'block' : 'none'; ?>;"><?php echo $errors['details'] ?? ''; ?></div>
                <div class="mb-3">
                    <label for="feedback-details" class="form-label">Feedback Details</label>
                    <textarea class="form-control" id="feedbackDetails" name="feedback-details" rows="5" placeholder="Enter Feedback Details"><?php echo htmlspecialchars($feedbackDetails ?? ''); ?></textarea>
                    <div class="format-info">Must be between 10-1000 characters.</div>
                </div>
                <div id="feedbackError4" class="alert alert-danger" style="display: <?php echo isset($errors['priority']) ? 'block' : 'none'; ?>;"><?php echo $errors['priority'] ?? ''; ?></div>
                <div class="mb-3">
                    <label for="priority-level" class="form-label">Priority Level</label>
                    <select class="form-control" id="priorityLevel" name="priority-level">
                        <option value="">Select Priority Level</option>
                        <option value="high" <?php echo (isset($priorityLevel) && $priorityLevel == 'high') ? 'selected' : ''; ?>>High</option>
                        <option value="medium" <?php echo (isset($priorityLevel) && $priorityLevel == 'medium') ? 'selected' : ''; ?>>Medium</option>
                        <option value="low" <?php echo (isset($priorityLevel) && $priorityLevel == 'low') ? 'selected' : ''; ?>>Low</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Submit Feedback</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Scripts -->
    <script>
        const feedbacktitleRegex = /^[a-zA-Z0-9\s\-]{3,30}$/; // Allows 3-15 characters: letters, numbers, spaces, hyphens
        const feedbackdetailsRegex = /^.{10,1000}$/; // Minimum 10 characters;

        function showErrorMessage(elementId, message, inputId) {
            const errorElement = document.getElementById(elementId);
            errorElement.style.display = 'block';
            errorElement.textContent = message;

            const inputElement = document.getElementById(inputId);
            inputElement.classList.add('error-border');
        }

        function hideAllErrorMessages() {
            const errorElements = document.querySelectorAll('.alert-danger');
            errorElements.forEach(element => {
                element.style.display = 'none';
            });

            const inputElements = document.querySelectorAll('.form-control');
            inputElements.forEach(element => {
                element.classList.remove('error-border');
            });
        }

        function validateUserForm() {
            hideAllErrorMessages(); // Clear previous errors

            const feedbackTitle = document.getElementById("feedbackTitle").value.trim();
            const feedbackType = document.getElementById("feedbackType").value;
            const feedbackDetails = document.getElementById("feedbackDetails").value.trim();
            const priorityLevel = document.getElementById("priorityLevel").value;

            let isValid = true; // Assume form is valid initially

            // Validate Feedback Title
            if (!feedbacktitleRegex.test(feedbackTitle)) {
                showErrorMessage('feedbackError1', 'Invalid Feedback Title.', 'feedbackTitle');
                isValid = false; // Mark as invalid
            }

            // Validate Feedback Type
            if (feedbackType === '') {
                showErrorMessage('feedbackError2', 'Please select a Feedback Type.', 'feedbackType');
                isValid = false; // Mark as invalid
            }

            // Validate Feedback Details
            if (!feedbackdetailsRegex.test(feedbackDetails)) {
                showErrorMessage('feedbackError3', 'Invalid Feedback Details.', 'feedbackDetails');
                isValid = false; // Mark as invalid
            }

            // Validate Priority Level
            if (priorityLevel === '') {
                showErrorMessage('feedbackError4', 'Please select a Priority Level.', 'priorityLevel');
                isValid = false; // Mark as invalid
            }

            // Return validation result
            return isValid;
        }

    </script>
</body>
</html>
