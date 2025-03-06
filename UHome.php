<?php
session_start();
// Checking if admin is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: Login.html");
    exit();
}
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
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
    WHERE ac.status = 0
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
$sql = "SELECT other_c_id, title, url, image_path, created_by, created_at FROM other_campaigns ORDER BY created_at DESC LIMIT 6";
$result = $conn->query($sql);

// Check if any campaigns were found
if ($result->num_rows > 0) {
    $campaigns = [];
    while ($row = $result->fetch_assoc()) {
        $campaigns[] = $row;
    }
} else {
    $campaigns = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Home</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            height: 100%;
        }

        .background-image {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }

        .background-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
        }

        .button-container {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .custom-btn {
            background-color: black;
            color: grey !important;
            border: 1px solid black;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 18px;
            border-radius: 12px;
            transition: background-color 0.3s, color 0.3s, border-color 0.3s;
            
        }

        .nav-link {
            margin-left: 45px;
        }

        .search-container {
            display: flex;
            align-items: center;
            margin-left: 55px;
        }

        .search-container input[type="text"] {
            padding: 8px;
            border: none;
        }

        input:focus {
            outline: none !important;
        }

        .about-us-section {
            background-color: white;
            padding: 90px 0;
            height: 90vh !important;
            text-align: center;
        }

        .about-us-image {
            width: 300px;
            margin-bottom: 50px;
        }

        .section-text {
            font-size: 18px;
            text-align: justify;
            max-width: 700px;
            margin: 0 auto;
            font-weight: 500;
        }

        .footer {
            background-color: black;
            height: 30vh;
            padding: 20px 10px;
        }

        .footer .logo img {
            width: 50px;
            height: 40px;
            margin-right: 10px;
            margin-left: 50px;
            margin-top: 30px;
        }

        .footer .social-links,
        .footer .section-links {
            justify-content: center;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .footer a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer a:hover {
            color: #ccc;
        }

        .footer span {
            color: white;
            margin-top: 30px;
            font-size: 19px;
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
        .catchy-line {
            color:black !important;
            font-size: 30px;
            font-weight: 500;
            margin-bottom: 50px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
        }
        .custom-btn:hover {
            color:white !important;
        }
        .dropdown-item:hover{
            background-color:black !important;
            color:white !important;
        }
        .btn-custom {
            border-radius: 8px !important;
            margin-bottom: 15px !important;
        }
        .btn-container {
            display: flex !important;
            justify-content: center !important; /* Center the buttons */
            align-items: center !important; /* Vertically center the buttons */
            margin-top: 2px !important;
        }
        .card-title {
            font-size: 1.25rem !important;
            font-weight: bold !important;
            margin-top: 9px !important;
            color:<?php echo $theme === 'dark' ? 'white' : ' black'; ?>; /* Light grey */
        }

        .card-img-top {
            height: 180px !important;
        }
        .img-thumbnail {
            border-radius: 4px !important;
        }
        .card {
        background-color: #d3d3d3; 
        }
        .campaigns_section {
            background-color: white;
            padding: 90px 0;
            height: 100vh !important;
            text-align: center;
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
                    <div class="d-flex align-items-center ms-auto">
                    <a href="notifications.php" class="me-3">
                        <svg xmlns="http://www.w3.org/2000/svg" style="margin-right: 20px;" width="16" height="16" fill="white" class="bi bi-bell-fill" viewBox="0 0 16 16">
                            <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zm.5-14a5.5 5.5 0 0 1 5.5 5.5v.9l.943 2.83A1 1 0 0 1 14 12H2a1 1 0 0 1-.943-1.27l.943-2.83v-.9A5.5 5.5 0 0 1 7.5 2z"/>
                        </svg>
                    </a>
                    <a href="ManageP.php"><svg xmlns="http://www.w3.org/2000/svg" style="margin-right: 20px;" width="16" height="16" fill="white" class="bi bi-person-fill" viewBox="0 0 16 16">
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
    
    <div class="background-image">
        <img src="BG.jpg" alt="Background Image">
        <div class="content">
            <h1 class="catchy-line">"Make a Difference Today : Donate to a Campaign or Start Your Own to Inspire Change!"</h1>
            <div class="button-container">
                <a href="UOngoingC.php" class="custom-btn">Donate Now</a>
                <a href="CreateC.php" class="custom-btn">Start Campaign</a>
            </div>
        </div>
    </div>

    <!-- ABOUT US SECTION STARTS -->
    <section class="about-us-section">
        <div class="container">
            <img src="AU.png" alt="About Us Image" class="about-us-image">
            <p class="section-text">
                " Alone, we ignite sparks, but together, we create a blaze of change! Join our fundraising community and become a beacon of hope and collective action. With unity and shared vision, we can transform lives and achieve extraordinary feats. Together, our impact is limitless! "
            </p>
        </div>
    </section>
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
                                    echo '<a href="UserCampaignDetails.php?id=' . $appcampaign['campaign_id'] . '" class="btn btn-secondary btn-custom">Display</a>';
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
    <section class="campaigns-section">
    <h2 class="text-center mb-4"><br>Campaigns Running on Other Platforms</h2>
    <div class="container mt-5">
        <div class="row">
            <?php
            // Loop through each campaign and display it in a card
            foreach ($campaigns as $campaign):
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <img src="<?php echo $campaign['image_path']; ?>" class="card-img-top img-fluid" alt="Campaign Image">
                            <h5 class="card-title"><?php echo $campaign['title']; ?></h5>       
                        </div>
                        <div class="btn-container mt-3">
                            <a href="<?php echo $campaign['url']; ?>" class="btn btn-secondary btn-custom" target="blank">Display</a>
                            <br>
                        </div>
                    </div>
                </div>
            <?php
            endforeach;
            ?>
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
        document.getElementById("searchInput").addEventListener("focus", function() {
            window.location.href = "Search.php";
        });
    </script>
</body>
</html>
