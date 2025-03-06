<?php
// Database connection (make sure to replace with your database credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fundraisingcommunity"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetching campaigns and images from the database
$sql = "
    SELECT ac.campaign_id, ac.campaignTitle, ac.campaignRunBy, ac.campaignDescription, ac.amountToRaise, ac.tags, ac.template_id, ac.user_id_campaign, aci.image_url, aci.image_type
    FROM approved_campaigns ac
    LEFT JOIN approved_campaign_images aci 
    ON ac.campaign_id = aci.approved_campaign_id
    WHERE ac.status = 1
    ORDER BY ac.campaign_id DESC
";

$result = $conn->query($sql);

// Group campaigns and images
$appcampaigns =[];
while ($row = $result->fetch_assoc()) {
    $id = $row['campaign_id'];
    if (!isset($appcampaigns[$id])) {
        // Initialize campaign details
        $appcampaigns[$id] = [
            'campaign_id' => $row['campaign_id'],
            'campaignTitle' => $row['campaignTitle'],
            'campaignRunBy' => $row['campaignRunBy'],
            'campaignDescription' => $row['campaignDescription'],
            'amountToRaise' => $row['amountToRaise'],
            'tags' => $row['tags'],
            'user_id_campaign'=> $row['user_id_campaign'],
            'template_id' => $row['template_id'],
            'mainImage' => null,
            'supportingImages' => [],
        ];
    }

    // Add images
    if ($row['image_type'] === 'main') {
        $appcampaigns[$id]['mainImage'] = $row['image_url'];
    } elseif ($row['image_type'] === 'supporting') {
        $appcampaigns[$id]['supportingImages'][] = $row['image_url'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="CompletedC.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Completed Campaign</title>
    <style>
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
        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-top: 9px;
        }

        .card-img-top {
            height: 180px;
        }

        .img-thumbnail {
            border-radius: 4px;
        }
        .btn-container {
            display: flex;
            justify-content: center; /* Center the buttons */
            align-items: center; /* Vertically center the buttons */
        }
    </style>
</head>
<body>
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-expanded="false">
                            Campaign
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <li><a class="dropdown-item" style="padding:8px;" href="OngoingC.php">Ongoing Campaign</a></li>
                            <li><a class="dropdown-item" style="padding:8px;" href="CompletedC.php">Completed Campaign</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Guide.php">Guide</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ContactUs.html">Contact Us</a>
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
            <div>
                <a class="btn" style="margin-right: 7px;" href="USignup.php" role="button">Sign Up</a>
                <a class="btn" href="Login.html" role="button">Log In</a>
                
            </div>
        </div>
    </nav>
    <!-- NAVBAR ENDS -->
    <section>
            <div class="container-fluid bg-light text-dark py-3 mt-5">
                <header class="text-center">
                    <h1 class="display-8">Ongoing Campaigns</h1>
                </header>
            </div>
            <div class="container mt-5">
            <div class="row">
                <?php foreach ($appcampaigns as $appcampaign): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <!-- Main Image and Title -->
                                <img src="<?php echo $appcampaign['mainImage']; ?>" alt="Main Image" class="card-img-top img-fluid">
                                <h5 class="card-title"><?php echo $appcampaign['campaignTitle']; ?></h5>

                                <!-- Action Buttons -->
                                <div class="btn-container mt-3">
                                    <?php
                                    // Check if 'id' is available
                                    if (isset($appcampaign['campaign_id']) && !empty($appcampaign['campaign_id'])) {
                                        echo '<a href="HomedisplayDetails.php?id=' . $appcampaign['campaign_id'] . '" class="btn btn-secondary btn-custom">Display</a>';
                                    } else {
                                        echo '<span class="text-danger">No ID found for this campaign</span>';
                                    }
                                    ?>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
    </div>
    </section>
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
                    <a href="CompletedC.php">Completed C</a>
                    <a href="ContactUs.html">Contact Us</a>
                    <a href="Guide.php">Guide</a>
                    <a href="Home.php">Home</a>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-IQsoLXl5PILFyD2L6v/pe2dZ2QcI9pXBGPIxSVi8W+PCmIW1Yj0Oj5dme3p6CXp9" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFyD2L6v/pe2dZ2QcI9pXBGPIxSVi8W+PCmIW1Yj0Oj5dme3p6CXp9" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById("searchInput").addEventListener("focus", function() {
            window.location.href = "Search.php";
        });
    </script>
</body>
</html>
