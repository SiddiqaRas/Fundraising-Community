<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fundraisingcommunity";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get campaign_id from the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $campaign_id = $_GET['id'];

    // Fetch campaign details
    $sql = "
        SELECT ac.*, aci.image_url, aci.image_type
        FROM approved_campaigns ac
        LEFT JOIN approved_campaign_images aci 
        ON ac.campaign_id = aci.approved_campaign_id
        WHERE ac.campaign_id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $campaign_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $campaign = [];
    while ($row = $result->fetch_assoc()) {
        $campaign['campaign_id'] = $row['campaign_id'];
        $campaign['campaignTitle'] = $row['campaignTitle'];
        $campaign['campaignRunBy'] = $row['campaignRunBy'];
        $campaign['campaignDescription'] = $row['campaignDescription'];
        $campaign['amountToRaise'] = $row['amountToRaise'];
        $campaign['tags'] = $row['tags'];
        $campaign['template_id'] = $row['template_id'];
        $campaign['user_id_campaign'] = $row['user_id_campaign'];
        $campaign['images'][] = [
            'url' => $row['image_url'],
            'type' => $row['image_type']
        ];
    }

    // Generate HTML for main and supporting images
    $supportingImagesHTML = '';
    $mainImageHTML = '';

    if (!empty($campaign['images'])) {
        foreach ($campaign['images'] as $image) {
            if ($image['type'] === 'main') {
                $mainImageHTML .= "<img src='{$image['url']}' alt='Main Image'>";
            } elseif ($image['type'] === 'supporting') {
                $supportingImagesHTML .= "<img src='{$image['url']}' alt='Supporting Image'>";
            }
        }
    }
} else {
    echo "Invalid campaign ID.";
    exit();
}
$sql_comments = "
    SELECT u.name,cc.comment, cc.rating
    FROM campaign_comments cc
    JOIN users u ON cc.cuser_id = u.id
    WHERE cc.ucampaign_id = ?
";

$stmt_comments = $conn->prepare($sql_comments);
$stmt_comments->bind_param("i", $campaign_id);
$stmt_comments->execute();
$comments_result = $stmt_comments->get_result();

$comments = [];
while ($row = $comments_result->fetch_assoc()) {
    $comments[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Display Campaign</title>
    <style>
     
        /* General container styling */
        .container {
            background-color: #f9f9f9;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            margin: 100px auto;
            word-wrap: break-word;
        }

        /* Campaign title styling */
        h2 {
            font-size: 2rem;
            color: black;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: capitalize;
        }

        /* Text content styling */
        p {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 10px;
        }
        /* Highlighted labels like "Run By" */
        p strong {
            color: #212529;
            font-weight: 600;
        }
        /* Add spacing between elements */
        .mt-5 {
            margin-top: 2rem !important;
        }

        /* Close button styling */
        .close-button {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: transparent;
            border: none;
            font-size: 1.5rem;
            color: #000;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        /* Disapprove button styling */
        .donate-btn {
            background-color: black;
            color: white;
            position: absolute;
            top: 10px;
            right: 10px;
            margin-top: 10px;
            margin-right: 10px;
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .donate-btn:hover {
            background-color: grey;
        }
        /* Text styling */
        .text-primary {
            color: black !important;
        }
        #previewSection {
            padding: 20px;
            border: 2px solid black;
            border-radius: 8px;
            margin: 0 auto;
            max-width: 700px; /* Ensure the main preview section has a defined width */
            text-align: left; /* Left-align content */
            display: flex;
            flex-direction: column;
            align-items: stretch; /* Stretch content to occupy full width */
            overflow: hidden; /* Prevent overflow */
        }
        .supporting-images-container {
            display: flex; /* Enable flexbox */
            flex-wrap: wrap; /* Allow images to wrap if there are too many */
            gap: 25px; /* Space between images */
            justify-content: center; /* Center-align images */   
        }
    /* Adjust individual image styling */
        .supporting-images-container img {
            border-radius: 5px; /* Optional rounded corners */
            width: 250px; /* Fixed width for uniformity */
            height: 200px; /* Fixed height for uniformity */
            margin: 5px; /* Space between images */
            border: 1px solid #ccc; /* Border around images */
            cursor: pointer; /* Pointer cursor for interactivity */
        }
        .main-images-container img {
            border-radius: 5px; /* Optional rounded corners */
            width: 500px; /* Fixed width for uniformity */
            height: 350px; /* Fixed height for uniformity */
            margin: 5px; /* Space between images */
            border: 1px solid #ccc; /* Border around images */
            cursor: pointer; /* Pointer cursor for interactivity */
        
        }
        .main-images-container{
            justify-content: center; /* Center-align images */
            align-items: center; 
            display: flex; /* Enable flexbox */  
        }
        .template-info {
            text-align: left; /* Left-align text */
            overflow-wrap: break-word; /* Wrap long words */
            word-wrap: break-word; /* For backward compatibility */
            word-break: break-word; /* Break words at any point */
            width: 100%; /* Ensure the content stays within the container */
            padding: 10px;
            box-sizing: border-box; /* Include padding in the width calculation */
        }
        .star {
            color: gray;
            padding: 0 3px;
            font-size: 22px;
        }#display-section{
            padding: 10px;
            border-radius: 8px;
            margin: 0 auto;
            max-width: 700px; /* Ensure the main preview section has a defined width */
            text-align: left; /* Left-align content */
            display: flex;
            flex-direction: column;
            align-items: stretch; /* Stretch content to occupy full width */
            overflow: hidden; /* Prevent overflow */
        }
        .full-star{
            color: black !important;
            padding: 0 3px;
            font-size: 22px;
        }
    </style>
    
</head>
<body>
<div class="container mt-5">
        <!-- Close button -->
        <button class="close-button" onclick="window.location.href='Home.php'">&times;</button>
        <!-- Approve and Disapprove buttons -->
        <!-- Donate button -->
    <form action="Login.html" >
        <button type="submit" class="donate-btn" name="donate" >Donate</button>
    </form>
        
    <div id="previewSection" class="mt-4 d-flex flex-column justify-content-center align-items-center">
        <div id="templateInfo" class="template-info">   
        <?php
        switch ($campaign['template_id']) {
            case 'template1':
                echo "
                      <h2 class='text-primary'>{$campaign['campaignTitle']}</h2>
                      <p><strong>Run By:</strong> {$campaign['campaignRunBy']}</p>
                      <div class='supporting-images-container'>
                      $supportingImagesHTML
                      </div>
                      <p><strong>Description:</strong> {$campaign['campaignDescription']}</p>
                      <p><strong>Amount to Raise:</strong> \${$campaign['amountToRaise']}</p>
                      <p><strong>Tags:</strong> {$campaign['tags']}</p>
                      <div class='main-images-container'>
                      $mainImageHTML
                      </div>
                      
                      
                      
                    ";
                break;
                case 'template2':
                    echo "
                        <div class='main-images-container'>
                        $mainImageHTML
                        </div>
                        <h2 class='text-primary'>{$campaign['campaignTitle']}</h2>
                        <p><strong>Description:</strong> {$campaign['campaignDescription']}</p>
                        <p><strong>Amount to Raise:</strong> \${$campaign['amountToRaise']}</p>
                        <p><strong>Tags:</strong> {$campaign['tags']}</p>
                        <p><strong>Run By:</strong> {$campaign['campaignRunBy']}</p>
                        <div class='supporting-images-container'>
                        $supportingImagesHTML
                        </div>
                        
                    ";
                    break;
    
                case 'template3':
                    echo "
                        <h2 class='text-primary'>{$campaign['campaignTitle']}</h2>
                        <p><strong>Run By:</strong> {$campaign['campaignRunBy']}</p>
                        <p><strong>Description:</strong> {$campaign['campaignDescription']}</p>
                        <div class='main-images-container'>
                        $mainImageHTML
                        </div>
                        <p><strong>Amount to Raise:</strong> \${$campaign['amountToRaise']}</p>
                        <p><strong>Tags:</strong> {$campaign['tags']}</p>
                        <div class='supporting-images-container'>
                        $supportingImagesHTML
                        </div>
                    ";
                    break;
    
                case 'template4':
                    echo "
                        <div class='main-images-container'>
                        $mainImageHTML
                        </div>
                        <div class='supporting-images-container'>
                        $supportingImagesHTML
                        </div>
                        <h2 class='text-primary'>{$campaign['campaignTitle']}</h2>
                        <p><strong>Run By:</strong> {$campaign['campaignRunBy']}</p>
                        <p><strong>Description:</strong> {$campaign['campaignDescription']}</p>
                        <p><strong>Amount to Raise:</strong> \${$campaign['amountToRaise']}</p>
                        <p><strong>Tags:</strong> {$campaign['tags']}</p>
                        
                    ";
                    break;
    
                default:
                    echo "<p>Template not found.</p>";
            }
        ?>
        </div> 
    </div>
    <div id="display-section" class="mt-4 d-flex flex-column justify-content-center align-items-center">
        <div class="d-flex flex-column justify-content-center align-items-center" style="width: 650px;">
            <?php if (count($comments) > 0): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-card p-2 border rounded shadow-sm mb-3" style="width: 100%; background-color: #f9f9f9;">
                        <!-- Flexbox container for rating and name -->
                        <div class="d-flex align-items-center">
                        <p class="mb-0"><strong><?php echo htmlspecialchars($comment['name']); ?></strong></p>
                            <p class="mb-0 mr-2"><strong>&nbsp</strong>
                                <?php
                                    // Display stars based on the rating value
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $comment['rating']) {
                                            echo '<span class="full-star">&#9733;</span>'; // Full star
                                        } else {
                                            echo '<span class="star">&#9734;</span>'; // Empty star
                                        }
                                    }
                                ?>
                            </p>
                            
                        </div>
                        
                        <p> <?php echo htmlspecialchars($comment['comment']); ?></p>
                        <hr>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No comments available for this campaign.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>