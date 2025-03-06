<?php
// Starting session
session_start();

// Checking if admin is logged in
if (!isset($_SESSION['admin_id'])) {
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
$password = "";
$dbname = "fundraisingcommunity";

// Creating connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Checking connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetching data from the database
$sql = "SELECT id, Name, Email, Message FROM contactus";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="Queries.css" rel="stylesheet">
    <title>Queries</title>
    <style>
        body {
            background-color: <?php echo $theme === 'dark' ? '#3e3636' : '#f4f4f4'; ?>;
            
        }
        .title{
            color: <?php echo $theme === 'dark' ? '#fff' : '#000'; ?>;
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
                <!-- Center the links and move them to the right -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="AHome.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Queries.php">Queries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Feedback.php">Feedback</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="OtherCampaigns.php">AddCampaign</a>
                    </li>
                </ul>
            </div>
            <!-- Profile Icon and Logout Button -->
            <div class="d-flex align-items-center">
                <a href="Amanageprofile.php" class="text-white me-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-person-fill" viewBox="0 0 16 16">
                        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                    </svg>
                </a>

                <!-- Styled Logout Button -->
                <a href="logout.php" class="btn btn-outline-danger btn-sm px-4" style="border-radius: 20px; color:red;" onclick="return confirm('Are you sure you want to log out?')">
                    <i class="fa fa-sign-out"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    <!-- NAVBAR ENDS -->

    <!-- QUERIES SECTION STARTS -->
    <div class="container mt-5">
        <h2 class="title">Contact Us Queries</h2>
        <table class="table table-striped mt-4">
            <thead>
                <tr>
                    <th class="title">Name</th>
                    <th class="title">Email</th>
                    <th class="title">Message</th>
                    <th class="title">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="title" ><?php echo htmlspecialchars($row["Name"]); ?></td>
                            <td class="title"><?php echo htmlspecialchars($row["Email"]); ?></td>
                            <td class="title">
                                <?php echo htmlspecialchars(substr($row["Message"], 0, 50)); ?>...
                            </td>
                            <td>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewMessageModal<?php echo $row['id']; ?>">View</button>
                            <a href="mailto:<?php echo htmlspecialchars($row["Email"]); ?>" class="btn btn-success btn-sm">Reply</a>
                            <form method="post" action="delete_query.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($row["id"]); ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                            </td>
                        </tr>

                        <!-- Modal for Viewing the Full Message -->
                        <div class="modal fade" id="viewMessageModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="viewMessageModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title" id="viewMessageModalLabel<?php echo $row['id']; ?>">Message from <?php echo htmlspecialchars($row["Name"]); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body">
                                <?php echo htmlspecialchars($row["Message"]); ?>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                              </div>
                            </div>
                          </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No queries found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- CONTACT INFORMATION SECTION ENDS -->

    <!-- Bootstrap 5 Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
</body>
</html>
