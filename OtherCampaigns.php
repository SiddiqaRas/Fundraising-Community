<?php
// Starting Session
session_start();

// Checking if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: Login.html");
    exit();
}

// Fetching theme preference
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fundraisingcommunity";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handling form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // Validate title
    $title = trim($_POST['title']);
    if (!preg_match('/^[a-zA-Z0-9\s\-]{3,25}$/', $title)) {
        $errors['title'] = "Title must be 3-25 characters long and can include letters, numbers, spaces, and hyphens.";
    }

    // Validate URL
    $url = trim($_POST['url']);
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $errors['url'] = "Please enter a valid URL.";
    }

    // Validate image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $errors['image'] = "Only JPEG, PNG files are allowed.";
        }

        // Check for upload errors
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors['image'] = "An error occurred during file upload.";
        }
    } else {
        $errors['image'] = "Please upload an image.";
    }

    // If no errors, proceed with database insertion
    if (empty($errors)) {
        $uploadDir = 'otheruploads/';
        $imagePath = $uploadDir . basename($_FILES['image']['name']);

        // Check if file already exists
        if (file_exists($imagePath)) {
            $errors['image'] = "File already exists. Please choose a different file.";
        } else {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                // Prepare SQL query
                $stmt = $conn->prepare("INSERT INTO other_campaigns (title, url, image_path, created_by, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("sssi", $title, $url, $imagePath, $_SESSION['admin_id']);

                if ($stmt->execute()) {
                    header("Location: AHome.php");
                    exit();
                } else {
                    die("Error inserting data: " . $stmt->error);
                }

                $stmt->close();
            } else {
                $errors['image'] = "Failed to move the uploaded file.";
            }
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="OtherCampaigns.css">
    <title>Campaigns on Other Platforms</title>
    <style>
        body {
            background-color: <?php echo $theme === 'dark' ? '#3e3636' : '#f4f4f4'; ?>;
            color: <?php echo $theme === 'dark' ? '#fff' : '#000'; ?>;
        }
        .btn-custom {
            width: 100%;
            border-radius: 8px;
            background-color: black !important;
        }
        .form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .form-control {
            width: 100%;
            font-size: 1rem;
            padding: 8px 10px;
        }
        .form-label {
            font-size: 1rem;
        }
        .error-message {
            color: red;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
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

    <div class="form-container">
        <h2 class="text-center mb-4">Add a Campaign</h2>
        <form id="campaignForm" action="OtherCampaigns.php" method="POST" enctype="multipart/form-data" class="d-flex flex-column align-items-center">
            <div class="mb-3 w-75">
                <label for="campaignTitle" class="form-label">Title</label>
                <div class="error-message" id="titleError"></div>
                <input type="text" class="form-control" id="campaignTitle" name="title" placeholder="Enter campaign title" required>
            </div>
            <div class="mb-3 w-75">
                <label for="campaignImage" class="form-label">Image</label>
                <div class="error-message" id="imageError"></div>
                <input type="file" class="form-control" id="campaignImage" name="image" accept="image/*" required>
            </div>
            <div class="mb-3 w-75">
                <label for="campaignUrl" class="form-label">URL</label>
                <div class="error-message" id="urlError"></div>
                <input type="url" class="form-control" id="campaignUrl" name="url" placeholder="Enter campaign URL" required>
            </div>
            <div class="d-flex justify-content-center w-75">
                <button type="submit" class="btn btn-primary btn-custom">Submit</button>
            </div>
        </form>
    </div>

    <script>
        const form = document.getElementById('campaignForm');
        const titleInput = document.getElementById('campaignTitle');
        const imageInput = document.getElementById('campaignImage');
        const urlInput = document.getElementById('campaignUrl');

        const titleError = document.getElementById('titleError');
        const imageError = document.getElementById('imageError');
        const urlError = document.getElementById('urlError');

        form.addEventListener('submit', (e) => {
            let isValid = true;

            // Validate title
            const titleRegex = /^[a-zA-Z0-9\s\-]{3,25}$/;
            if (!titleRegex.test(titleInput.value.trim())) {
                titleError.textContent = "Title must be 3-25 characters long and can include letters, numbers, spaces, and hyphens.";
                isValid = false;
            } else {
                titleError.textContent = "";
            }

            // Validate image
            if (imageInput.files.length === 0) {
                imageError.textContent = "Please upload an image.";
                isValid = false;
            } else {
                const allowedTypes = ['image/jpeg', 'image/png'];
                const fileType = imageInput.files[0].type;
                if (!allowedTypes.includes(fileType)) {
                    imageError.textContent = "Only JPEG and PNG files are allowed.";
                    isValid = false;
                } else {
                    imageError.textContent = "";
                }
            }

            // Validate URL
            if (!urlInput.value.trim() || !urlInput.checkValidity()) {
                urlError.textContent = "Please enter a valid URL.";
                isValid = false;
            } else {
                urlError.textContent = "";
            }

            // Prevent form submission if validation fails
            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
