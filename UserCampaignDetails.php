<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}

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
$campaign_id = null; // Initialize the variable
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $campaign_id = $_GET['id'];
    
} else {
    echo "Invalid campaign ID.";
    exit(); // Exit if campaign ID is invalid
}

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



// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment'], $_POST['rating'])) {
    $user_id = $_SESSION['user_id'];
    $comment = trim($_POST['comment']);
    $rating = intval($_POST['rating']);
    
    // Validate rating (1-5)
    if ($rating < 1 || $rating > 5) {
        echo "<p class='text-danger'>Invalid rating. Please select a value between 1 and 5.</p>";
    }

    // Validate comment (ensure no malicious input)
    if (empty($comment)) {
        echo "<p class='text-danger'>Comment is required.</p>";
    } elseif (!preg_match("/^[a-zA-Z0-9\s.,!?'-]+$/", $comment)) {
        echo "<p class='text-danger'>Invalid comment format. Please avoid special characters.</p>";
    }

    // Insert comment into the database if validation passes
    if (!empty($comment) && $rating >= 1 && $rating <= 5) {

        $stmt = $conn->prepare("INSERT INTO campaign_comments (ucampaign_id, cuser_id, comment, rating) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $campaign_id, $user_id, $comment, $rating);

        if ($stmt->execute()) {
            echo "<script>
            alert('Comment submitted successfully!');
            window.location.href = 'UOngoingC.php';
            exit();
          </script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    }
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
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
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
        /* Button container styles */
        .button-container {
            /* border: 1px solid black; */
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-end; /* Align buttons to the right */
            gap: 10px;
            position: absolute;
            top: 20px;
            right: 15px;
        } 
        .donors-btn {
            background-color: #000; /* Match the Donate button's color */
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            /* margin-right: 10px; Add spacing between buttons */
            transition: background-color 0.3s ease;
        }

        .donors-btn:hover {
            background-color: grey; /* Darker shade for hover effect */
        }
        .share-btn {
            background-color: black;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }
        .share-btn:hover {
            background-color: grey;
        }
        /* Media icons dropdown */
        .media-icons {
            display: none; /* Initially hidden */
            position: absolute;
            top: 120%; /* Dropdown appears below the Share button */
            right: 80px;
            background-color: #000;
            padding: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            z-index: 10;
        }

        .media-icons a {
            margin-right: 10px;
            font-size: 1.2rem;
            color: #fff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .media-icons a:hover {
            color:#ffbd68;
        }

        /* Show media icons on Share button hover */
        .share-container:hover .media-icons {
            display: block;
        }


        .donate-form {
            margin: 0; /* Remove default form margin */
        }

        /* Disapprove button styling */
        .donate-btn {
            background-color: black;
            color: white;
            /* margin: 0; Remove extra margins */
            /* position: absolute;
            top: 10px;
            right: 10px; */
            /* margin-top: 10px;
            margin-right: 10px; */
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
        #star-rating {
            font-size: 22px;
            cursor: pointer;
        }
        .star {
            color: gray;
            padding: 0 3px;
            font-size: 22px;
        }
        .star.selected {
            color: black !important;
        }
        .list-group-item {
            font-size: 14px;
            padding: 6px;
        }
        #comment-section{
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
        #display-section{
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
        
        <div class="button-container">
             <!-- Donors button -->
            <button class="donors-btn" onclick="window.location.href='Donors.php?id=<?php echo $campaign['campaign_id']; ?>'">Donors</button>
            <!-- share button  -->
            <div class="share-container">
                <button class="share-btn" id="shareButton">Share</button>
                <div class="media-icons" id="mediaIcons" style="display: none;">
                    <a href="#" id="copyLink" title="Copy Campaign URL"><i class="ri-link"></i></a>
                    <a href="#" id="shareFacebook" title="Share on Facebook"><i class="ri-facebook-line"></i></a>
                    <a href="#" id="shareInstagram" title="Share on Instagram"><i class="ri-instagram-line"></i></a>
                    <a href="#" id="shareWhatsApp" title="Share on WhatsApp"><i class="ri-whatsapp-line"></i></a>
                </div>
            </div>
            <!-- Donate button -->

            <form action="Donate.php" method="GET">
                <button type="submit" class="donate-btn" name="donate" value="donate">Donate</button>
                
                <!-- Hidden fields to pass campaign data to Donate.php -->
                <input type="hidden" name="campaign_id" value="<?php echo $campaign['campaign_id']; ?>">
                <input type="hidden" name="campaignTitle" value="<?php echo $campaign['campaignTitle']; ?>">
                <input type="hidden" name="campaignRunBy" value="<?php echo $campaign['campaignRunBy']; ?>">
                <input type="hidden" name="campaignDescription" value="<?php echo $campaign['campaignDescription']; ?>">
                <input type="hidden" name="amountToRaise" value="<?php echo $campaign['amountToRaise']; ?>">
                <input type="hidden" name="tags" value="<?php echo $campaign['tags']; ?>">
                <input type="hidden" name="template_id" value="<?php echo $campaign['template_id']; ?>">
            </form>

        </div>
    
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
                      <p><strong>Amount to Raise:</strong> {$campaign['amountToRaise']}</p>
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
                        <p><strong>Amount to Raise:</strong> {$campaign['amountToRaise']}</p>
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
                        <p><strong>Amount to Raise:</strong> {$campaign['amountToRaise']}</p>
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
                        <p><strong>Amount to Raise:</strong> {$campaign['amountToRaise']}</p>
                        <p><strong>Tags:</strong> {$campaign['tags']}</p>
                        
                    ";
                    break;
    
                default:
                    echo "<p>Template not found.</p>";
            }
        ?>
        </div> 
    </div>
    <div id="comment-section" class="mt-4 d-flex flex-column justify-content-center align-items-center">
        <!-- Slimmer Comment Form -->
        <form id="commentForm" action="" method="POST" class="p-2 border rounded shadow-sm" style="width: 650px;">
            <div class="mb-1">
                <!-- Comment Label & Star Rating Inline -->
                <div class="d-flex justify-content-between align-items-center">
                    <label for="comment" class="form-label mb-0">Enter your comment:</label>
                    <div id="star-rating" class="d-flex">
                        <span class="star" data-value="1">&#9733;</span>
                        <span class="star" data-value="2">&#9733;</span>
                        <span class="star" data-value="3">&#9733;</span>
                        <span class="star" data-value="4">&#9733;</span>
                        <span class="star" data-value="5">&#9733;</span>
                    </div>
                    <input type="hidden" id="rating" name="rating">
                </div>

                <!-- Comment Box -->
                <textarea class="form-control mt-1" id="comment" name="comment" rows="1" style="height: 20px;"></textarea>
                <small id="commentError" class="text-danger d-none">Comment is required and must be valid.</small>
            </div>

            <!-- Rating Error -->
            <small id="ratingError" class="text-danger d-none">Please select a rating.</small>

            <button type="submit" class="btn btn-primary btn-sm w-20 d-block mx-auto mt-2">Submit</button>
        </form>
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



<script>
    document.getElementById("shareButton").addEventListener("click", function () {
        const mediaIcons = document.getElementById("mediaIcons");
        mediaIcons.style.display = mediaIcons.style.display === "block" ? "none" : "block";
    });

    const campaign = {
        title: "<?php echo htmlspecialchars($campaign['campaignTitle']); ?>",
        description: "<?php echo htmlspecialchars($campaign['campaignDescription']); ?>",
        target: "<?php echo htmlspecialchars($campaign['amountToRaise']); ?>",
        image: "path_to_image.jpg",
        url: window.location.href,
    };

    document.getElementById("copyLink").addEventListener("click", function (event) {
        event.preventDefault();
        navigator.clipboard.writeText(campaign.url).then(() => {
            alert("Campaign URL copied to clipboard!");
        });
    });

    document.getElementById("shareFacebook").addEventListener("click", function (event) {
        event.preventDefault();
        const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(campaign.url)}&quote=${encodeURIComponent(
            campaign.title + '\n' + campaign.description + '\nTarget: ' + campaign.target
        )}`;
        window.open(facebookUrl, "_blank");
    });

    document.getElementById("shareInstagram").addEventListener("click", function (event) {
        event.preventDefault();
        navigator.clipboard.writeText(campaign.url).then(() => {
            alert("Instagram sharing via web is not supported directly. Use the Instagram app to share!");
        });
    });

    document.getElementById("shareWhatsApp").addEventListener("click", function (event) {
        event.preventDefault();
        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(
            campaign.title + '\n' + campaign.description + '\nTarget: ' + campaign.target + '\nLink: ' + campaign.url
        )}`;
        window.open(whatsappUrl, "_blank");
    });
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const stars = document.querySelectorAll("#star-rating .star");
    const ratingInput = document.getElementById("rating");
    const commentInput = document.getElementById("comment");
    const form = document.getElementById("commentForm");
    const commentError = document.getElementById("commentError");
    const ratingError = document.getElementById("ratingError");

    // Regex pattern for comment validation
    const commentRegex = /^[a-zA-Z0-9\s.,!?'-]+$/;

    // Handle star selection
    stars.forEach(star => {
        star.addEventListener("click", function () {
            let ratingValue = this.getAttribute("data-value");
            ratingInput.value = ratingValue;

            // Update star colors
            stars.forEach(s => {
                if (s.getAttribute("data-value") <= ratingValue) {
                    s.style.color = "black"; // Highlight selected stars
                } else {
                    s.style.color = ""; // Remove highlight from unselected stars
                }
            });
        });
    });

    // Form validation
    form.addEventListener("submit", function (event) {
        let valid = true;

        // Validate comment (not empty + must match regex)
        if (commentInput.value.trim() === "" || !commentRegex.test(commentInput.value.trim())) {
            commentError.classList.remove("d-none");
            valid = false;
        } else {
            commentError.classList.add("d-none");
        }

        // Validate rating
        if (ratingInput.value === "") {
            ratingError.classList.remove("d-none");
            valid = false;
        } else {
            ratingError.classList.add("d-none");
        }

        // Prevent form submission if validation fails
        if (!valid) {
            event.preventDefault();
        }
    });
});
</script>

</body>
</html>