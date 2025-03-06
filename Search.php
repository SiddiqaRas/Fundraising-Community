<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "fundraisingcommunity");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchWord = isset($_POST["searchWord"]) ? $conn->real_escape_string($_POST["searchWord"]) : '';
    $filter = isset($_POST["filter"]) ? $_POST["filter"] : 'all';

    $conditions = [];

    if ($filter == "title") {
        $conditions[] = "campaignTitle LIKE '%$searchWord%'";
    } elseif ($filter == "tags") {
        $conditions[] = "tags LIKE '%$searchWord%'";
    } elseif ($filter == "run_by") {
        $conditions[] = "campaignRunBy LIKE '%$searchWord%'";
    } elseif ($filter == "completed") {
        $conditions[] = "status = 1"; // Only fetch completed campaigns
    } elseif ($filter == "description") {
        $conditions[] = "campaignDescription LIKE '%$searchWord%'";
    } else { // "All" case
        $conditions[] = "(campaignTitle LIKE '%$searchWord%' OR tags LIKE '%$searchWord%' OR campaignRunBy LIKE '%$searchWord%' OR campaignDescription LIKE '%$searchWord%')";
    }

    // Build SQL Query
    $sql = "SELECT campaign_id, campaignTitle, status FROM approved_campaigns";
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $result = $conn->query($sql);

    $ongoingCampaigns = "";
    $completedCampaigns = "";

    // Categorize results into Ongoing and Completed
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Check if the user is logged in
            if (isset($_SESSION['user_id'])) {
                // User is logged in
                if ($row["status"] == 1) {
                    // Completed Campaign
                    $completedCampaigns .= '<div class="result-item"><a href="UCompletedC.php?id=' . $row["campaign_id"] . '">' . $row["campaignTitle"] . '</a></div>';
                } else {
                    // Ongoing Campaign
                    $ongoingCampaigns .= '<div class="result-item"><a href="UOngoingC.php?id=' . $row["campaign_id"] . '">' . $row["campaignTitle"] . '</a></div>';
                }
            } else {
                // User is not logged in
                if ($row["status"] == 1) {
                    // Completed Campaign
                    $completedCampaigns .= '<div class="result-item"><a href="CompletedC.php?id=' . $row["campaign_id"] . '">' . $row["campaignTitle"] . '</a></div>';
                } else {
                    // Ongoing Campaign
                    $ongoingCampaigns .= '<div class="result-item"><a href="OngoingC.php?id=' . $row["campaign_id"] . '">' . $row["campaignTitle"] . '</a></div>';
                }
            }
        }
    }
    // Display categorized results
    if ($ongoingCampaigns) {
        // echo '<div class="text-center">';
        echo '<h4 class="text-success">Ongoing Campaigns</h4>';
        echo $ongoingCampaigns;
        echo '</div>';
    }
    
    if ($completedCampaigns) {
        // echo '<div class="text-center">';
        echo '<h4 class="text-danger mt-4">Completed Campaigns</h4>';
        echo $completedCampaigns;
        echo '</div>';
    }

    if (!$ongoingCampaigns && !$completedCampaigns) {
        echo '<p class="text-muted">No results found.</p>';
    }

    $conn->close();
    exit; // Stop further execution
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- <link href="Homestyle.css" rel="stylesheet"> -->

    <style>
        .form-select {
            width: 20%;
            margin-right: 10px;
            border: 1px solid #ccc;
        }
        .form-select:focus, .form-control:focus {
            border-color: black !important;
            box-shadow: 0 0 0.1px black !important;
            outline: none;
        }
        .form-control {
            width: 70%;
            margin-right: 10px;
            border: 1px solid #ccc;
        }
        .result-item {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 10px;
            box-shadow: 0 0 0.3px black !important;
        }
        .result-item a {
            text-decoration: none;
            color: #000;
            font-weight: bold;
        }
        .result-item a:hover {
            text-decoration: underline;
        }
        .nav-link {
            margin-left: 45px;
            color: #BFBFBF;
        }
        .btn1 {
            background-color: white;
            margin-right: 15px;
            border-color: gray;
            padding: 8px 20px;
            color: black;
            border-radius: 4px;
            transition: background-color 0.3s, color 0.3s, border-color 0.3s;
        }

        .btn1:hover {
            background-color: black;
            color: white;
        }
        .btn1:focus, .btn1:focus + .btn1 {
            outline: 0;
            box-shadow: 0 0 0 0 rgb(13 110 253 / 25%);
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
   <!-- NAVBAR FOR LOGGED-IN USERS -->
<?php if (isset($_SESSION['user_id'])): ?>
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
                            <li><a class="dropdown-item" style="padding:8px;" href="UCompletedC.php">Completed Campaign</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="UGuide.php">Guide</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="UContactUs.php">Contact Us</a>
                    </li>
                    <li>
                    </li>
                </ul>
                    <div class="d-flex align-items-center ms-auto">
                    <a href="notifications.php" class="me-3">
                        <svg xmlns="http://www.w3.org/2000/svg" style="margin-right: 20px;" width="16" height="16" fill="white" class="bi bi-bell-fill" viewBox="0 0 16 16">
                            <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zm.5-14a5.5 5.5 0 0 1 5.5 5.5v.9l.943 2.83A1 1 0 0 1 14 12H2a1 1 0 0 1-.943-1.27l.943-2.83v-.9A5.5 5.5 0 0 1 7.5 2z"/>
                        </svg>
                    </a>
                    <a href="ManageP.html"><svg xmlns="http://www.w3.org/2000/svg" style="margin-right: 20px;" width="16" height="16" fill="white" class="bi bi-person-fill" viewBox="0 0 16 16">
                        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                        </svg></a>
                        
                        <a href="logout.php" class="btn btn-outline-danger btn-sm px-4" style="border-radius: 20px;" onclick="return confirm('Are you sure you want to log out?')">
                        <i class="fa fa-sign-out"></i> Logout
                        </a>
                           
                        
                    </div>    
            </div>
        </div>
    </nav>
    <!-- NAVBAR ENDS -->
<?php else: ?>
    <!-- NAVBAR FOR NON-LOGGED-IN USERS -->
    <!-- NAVBAR STARTS -->
    <nav class="navbar sticky-top navbar-expand-lg navbar-dark" style="background-color: black;">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="Home.php">
                <img src="logo.PNG" alt="" width="50" height="40">
                <span class="ms-2">FundRaisingC.</span>
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="Home.php">Home</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="Guide.html">Guide</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ContactUs.html">Contact Us</a>
                    </li>
                    <li>
                       
                    </li>
                </ul>
            </div>
            <div>
                <a class="btn1" style="margin-right: 7px; text-decoration:none;" href="USignup.php" role="button">Sign Up</a>
                <a class="btn1" style="margin-right: 7px; text-decoration:none;" href="Login.html" role="button">Log In</a>
                
            </div>
        </div>
    </nav>
    <!-- NAVBAR ENDS -->
<?php endif; ?>

    <div class="bg-light d-flex flex-column align-items-center pt-5">
        <!-- Search Form -->
        <div class="card p-4 shadow-sm w-75">
            <form id="searchForm" class="d-flex gap-2">
                <div class="d-flex w-100">
                    <select id="searchFilter" class="form-select" required>
                        <option value="all">All</option>
                        <option value="title">Title</option>
                        <option value="tags">Tags</option>
                        <option value="run_by">Run By</option>
                        <option value="description">Description</option>
                        <option value="completed">Completed</option>
                    </select>
                    <input type="text" id="searchWord" class="form-control" placeholder="Enter a word" required>
                    <button type="submit" class="btn btn-dark"><b>Search</b></button>
                </div>
            </form>
        </div>

        <h2 class="mt-4">Search Results</h2>
        <div id="results" class="mt-3 p-4 bg-white rounded shadow-sm w-75"></div>
    </div>

    <script>
        $(document).ready(function(){
            $("#searchForm").submit(function(event){
                event.preventDefault();
                
                var filter = $("#searchFilter").val();
                var searchWord = $("#searchWord").val();
                
                $.ajax({
                    type: "POST",
                    url: "",
                    data: { filter: filter, searchWord: searchWord },
                    success: function(response) {
                        $("#results").html(response);
                    }
                });
            });

            $("#searchFilter").change(function() {
                $("#searchWord").prop("disabled", $(this).val() == "completed").val("");
            });
        });
    </script>
</body>
</html>