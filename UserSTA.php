<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: Login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Database credentials
    $host = 'localhost'; // Replace with your database host
    $username = 'root'; // Replace with your database username
    $dbpassword = ''; // Replace with your database password
    $dbname = 'fundraisingcommunity'; // Replace with your database name

    // Create database connection
    $conn = new mysqli($host, $username, $dbpassword, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve campaign data from POST
    $campaignTitle = $conn->real_escape_string($_POST['campaignTitle']);
    $campaignRunBy = $conn->real_escape_string($_POST['campaignRunBy']);
    $campaignDescription = $conn->real_escape_string($_POST['campaignDescription']);
    $amountToRaise = $conn->real_escape_string($_POST['amountToRaise']);
    $tags = $conn->real_escape_string($_POST['tags']);
    $mainImage = $conn->real_escape_string($_POST['mainImage']);
    $supportingImages = isset($_POST['supportingImages']) ? explode(',', $_POST['supportingImages']) : [];
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0; // Assuming user_id is stored in session
    $selectedTemplate = $conn->real_escape_string($_POST['selectedTemplate']);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert campaign into campaigns table
        $sql_campaign = "INSERT INTO campaigns (campaignTitle, campaignRunBy, campaignDescription, amountToRaise, tags, user_id, created_at, template_id) 
                         VALUES ('$campaignTitle', '$campaignRunBy', '$campaignDescription', '$amountToRaise', '$tags', '$userId', NOW(),'$selectedTemplate')";

        if (!$conn->query($sql_campaign)) {
            throw new Exception("Error inserting campaign: " . $conn->error);
        }

        // Get the last inserted campaign ID
        $campaignId = $conn->insert_id;

        // Generate the blockchain_id (e.g., b1, b2, b3)
        $blockchainId = "b" . $campaignId; // Create the prefixed ID based on the campaign ID
         
        // Update the campaign with the blockchain_id
        $sql_update_blockchain_id = "UPDATE campaigns SET blockchain_id = '$blockchainId' WHERE id = '$campaignId'";

        if (!$conn->query($sql_update_blockchain_id)) {
            throw new Exception("Error updating blockchain_id: " . $conn->error);
        }

        // Insert main image into campaign_images table
        $sql_main_image = "INSERT INTO campaign_images (campaign_id, image_url, image_type) 
                           VALUES ('$campaignId', '$mainImage', 'main')";

        if (!$conn->query($sql_main_image)) {
            throw new Exception("Error inserting main image: " . $conn->error);
        }

        // Insert supporting images into campaign_images table
        foreach ($supportingImages as $supportingImage) {
            $supportingImage = $conn->real_escape_string($supportingImage);
            $sql_supporting_image = "INSERT INTO campaign_images (campaign_id, image_url, image_type) 
                                     VALUES ('$campaignId', '$supportingImage', 'supporting')";

            if (!$conn->query($sql_supporting_image)) {
                throw new Exception("Error inserting supporting image: " . $conn->error);
            }
        }

        // Commit transaction
        $conn->commit();

        echo "Campaign and images saved successfully!";
        // Optionally, redirect to another page
        header("Location: UserSuccess.php?blockchain_id=".$blockchainId);
        exit();
    } catch (Exception $e) {
        // Rollback transaction in case of error
        $conn->rollback();
        echo "Transaction failed: " . $e->getMessage();
    }

    // Close connection
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>