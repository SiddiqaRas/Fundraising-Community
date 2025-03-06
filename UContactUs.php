<?php
session_start();
// Checking if admin is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: Login.html");
    exit();
}
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST["name"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $message = htmlspecialchars(trim($_POST["message"]));

    // Server-side validation
    if (empty($name)) {
        die("Name cannot be empty.");
    } elseif (!preg_match("/^[a-zA-Z0-9\s]{3,20}$/", $name)) {
        die("Error: Name must be between 3 and 20 characters and can only contain letters, numbers, and spaces.");
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="ContactUs.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Contact Us</title>
    <style>
        body{
            background-color:<?php echo ($theme === 'dark') ? '#3e3636' : '#f4f4f4'; ?>;
            color:<?php echo ($theme === 'dark') ? '#fff' : '#000'; ?>
        }
        .btn-outline-danger{
            color:#DC3445 !important;
            border-color:#DC3445 !important;
            margin-right: 1px;
            background-color:black !important;
        }
        .btn-outline-danger:hover{
            color:white !important;
            background-color:#DC3445 !important;
        }
    </style>
</head>
<body>
    <!-- NAVBAR STARTS -->
    <nav class="navbar sticky-top navbar-expand-lg navbar-dark" style="background-color: black;">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="UHome.php">
                <img src="logo.PNG" alt="" width="50" height="40">
                <span class="ms-2">FundRaisingC.</span>
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="UHome.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-expanded="false">
                            Campaign
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <li><a class="dropdown-item" style="padding:8px;" href="UOngoingC.php">Ongoing Campaign</a></li>
                            <li><a class="dropdown-item" style="padding:8px;" href="UCompletedC.php ">Completed Campaign</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="UGuide.php">Guide</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="UContactUs.php">Contact Us</a>
                    </li>
                    <li>
                        <div class="search-container">
                            <form id="searchForm" action="Search.php" method="GET">
                                    <input type="text" id="searchInput" placeholder="Search.." style="width: 240px; height: 40px;" name="search">
                                    <button type="submit" style="border: none; position: relative; right: 45px; top: 0; height: 100%; width: 40px; background-color: white; color: black;">
                                        <i class="fa fa-search"></i>
                                    </button>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="d-flex align-items-center ms-auto">
                <a href="ManageP.php"><svg xmlns="http://www.w3.org/2000/svg" style="margin-right: 20px;" width="16" height="16" fill="white" class="bi bi-person-fill" viewBox="0 0 16 16">
                    <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                </svg></a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm px-4" style="border-radius: 20px;"
                onclick="return confirm('Are you sure you want to log out?')">
                    <i class="fa fa-sign-out"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    <!-- NAVBAR ENDS -->

    <div class="container">
        <div class="row mt-4">
            <div class="col-md-6">
                <img src="CU.PNG" height="500px" width="460px" style="margin-top: 15px; margin-left: 80px;">
            </div>
            <div class="col-md-6">
                <h2 style="margin-top: 15px;">Contact Us</h2>
                <form style="width: 500px; height:750px;" method="post" action="UContactUs.php" id="contactusForm">
                    <div id="contactusError1" class="alert alert-danger" style="display: none;"></div>
                    <div class="mb-3">
                        <label for="name" class="form-label" style="margin-top: 18px;">Name</label>
                        <input type="text" class="form-control" id="contactName" name="name"  placeholder="Enter Your Name">
                    </div>
                    <div id="contactusError2" class="alert alert-danger" style="display: none;"></div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="contactEmail" name="email"  placeholder="Enter Your Email Address">
                    </div>
                    <div id="contactusError3" class="alert alert-danger" style="display: none;"></div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="contactText" name="message" rows="4" ></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 d-flex justify-content-start align-items-center logo">
                    <img src="logo.PNG" alt="logo">
                    <span>FundRaisingC.</span>
                </div>
                <div class="col-md-4 d-flex justify-content-center social-links">
                    <a href="https://facebook.com" target="_blank">Facebook</a>
                    <a href="https://instagram.com" target="_blank">Instagram</a>
                    <a href="https://twitter.com" target="_blank">Twitter</a>
                </div>
                <div class="col-md-4 d-flex justify-content-center section-links"> <!-- Centered -->
                    <a href="UCompletedC.php">Completed C</a>
                    <a href="UContactUs.php">Contact Us</a>
                    <a href="UGuide.php">Guide</a>
                    <a href="UHome.php">Home</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        const nameRegex = /^[a-zA-Z0-9\s]{3,20}$/;
        const emailRegex =  /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        const textRegex= /^(?=.*[a-zA-Z].{9,})(?!\s*$)[a-zA-Z0-9\s]*$/;


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
            hideAllErrorMessages();

            const form = document.getElementById('contactusForm');
            const name = form.querySelector('#contactName').value.trim();
            const email = form.querySelector('#contactEmail').value.trim();
            const text = form.querySelector('#contactText').value.trim();
            

            let isValid = true;

            if (name === "") {
                showErrorMessage('contactusError1', 'Name cannot be empty.', 'contactName');
                isValid = false;
            } else if (!nameRegex.test(name)) {
                showErrorMessage('contactusError1', 'Name must be 3-20 characters long and can only contain letters.', 'contactName');
                isValid = false;
            }
            if (email === "") {
                showErrorMessage('contactusError2', 'Email address cannot be empty.', 'contactEmail');
                isValid = false;
            } else if (!emailRegex.test(email)) {
                showErrorMessage('contactusError2', 'Invalid email address.', 'contactEmail');
                isValid = false;
            }
            if (text === "") {
                showErrorMessage('contactusError3', 'Text cannot be empty.', 'contactText');
                isValid = false;
            } else if (!textRegex.test(text)) {
                showErrorMessage('contactusError3', 'Text can only contain alphabets (minimum 10), numbers and no special characters ', 'contactText');
                isValid = false;
            }
            return isValid;
        }

        

        document.getElementById('contactusForm').addEventListener('submit', function (event) {
            if (!validateUserForm()) {
                event.preventDefault();
            }
        });

        


    </script>
    <script>
        document.getElementById("searchInput").addEventListener("focus", function() {
            window.location.href = "Search.php";
        });
    </script>
</body>
</html>
