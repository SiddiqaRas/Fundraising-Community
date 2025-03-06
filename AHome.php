<?php
// Starting Session
session_start();

// Checking if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // If not logged in, redirect to login page
    header("Location: Login.html");
    exit();
}

// Fetching theme preference
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
    SELECT c.id, c.campaignTitle, c.campaignRunBy, c.campaignDescription, c.amountToRaise, c.tags,c.blockchain_id, c.template_id,
           ci.image_url, ci.image_type
    FROM campaigns c
    LEFT JOIN campaign_images ci ON c.id = ci.campaign_id
    ORDER BY c.id DESC
";

$result = $conn->query($sql);

// Group campaigns and images
$campaigns = [];
while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    if (!isset($campaigns[$id])) {
        // Initialize campaign details
        $campaigns[$id] = [
            'id'=>$row['id'],
            'campaignTitle' => $row['campaignTitle'],
            'campaignRunBy' => $row['campaignRunBy'],
            'campaignDescription' => $row['campaignDescription'],
            'amountToRaise' => $row['amountToRaise'],
            'tags' => $row['tags'],
            'blockchain_id'=>$row['blockchain_id'],
            'template_id' => $row['template_id'],
            'mainImage' => null,
            'supportingImages' => [],
        ];
    }

    // Add images
    if ($row['image_type'] === 'main') {
        $campaigns[$id]['mainImage'] = $row['image_url'];
    } elseif ($row['image_type'] === 'supporting') {
        $campaigns[$id]['supportingImages'][] = $row['image_url'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Ahome.css">
    <title>Approve Campaign</title>
    <style>
        body {
            background-color: <?php echo $theme === 'dark' ? '#3e3636' : '#f4f4f4'; ?>;
            color: <?php echo $theme === 'dark' ? '#fff' : '#000'; ?>;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
            margin-top: 9px;
            color:<?php echo $theme === 'dark' ? 'white' : ' black'; ?>; /* Light grey */
        }
        .card {
            background-color:<?php echo $theme === 'dark' ? 'black' : ' #d3d3d3'; ?>; /* Light grey */
            
        }
        .btn-custom {
            border-radius: 8px;
            
        }
        .btn-container {
            display: flex;
            justify-content: center; /* Center the buttons */
            align-items: center; /* Vertically center the buttons */
        }

        .card-img-top {
            height: 180px;

        }

        .img-thumbnail {
            border-radius: 4px;
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
    <h2 class="text-center mb-4"><br>Approve Campaign</h2>
    <div class="container mt-5">
        <div class="row">
            <?php foreach ($campaigns as $campaign): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <!-- Main Image and Title -->
                            <img src="<?php echo $campaign['mainImage']; ?>" alt="Main Image" class="card-img-top img-fluid">
                            <h5 class="card-title"><?php echo $campaign['campaignTitle']; ?></h5>
                            

                            <!-- Action Buttons -->
                            <div class="btn-container mt-3">
                                <?php
                                if (isset($campaign['id'], $campaign['blockchain_id']) && !empty($campaign['id']) && !empty($campaign['blockchain_id'])) {
                                    echo '<a href="UserDetails.php?id=' . $campaign['id'] . '&blockchain_id=' . urlencode($campaign['blockchain_id']) . '" class="btn btn-secondary btn-custom">Display</a>';
                                } else {
                                    echo '<span class="text-danger">No ID or blockchain ID found for this campaign</span>';
                                }
                                ?>
                                
                                
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
