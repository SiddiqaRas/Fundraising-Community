<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: Login.html");
    exit();
}

// Display errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and collect form data
    $campaignTitle = $_POST['campaignTitle'] ?? '';
    $campaignRunBy = $_POST['campaignRunBy'] ?? '';
    $campaignDescription = $_POST['campaignDescription'] ?? '';
    $amountToRaise = $_POST['amountToRaise'] ?? '';
    $tags = $_POST['tags'] ?? '';

    // Initialize file upload handling variables
    $mainImage = null;
    $supportingImages = [];

    // Handle file uploads for the main image
    if (isset($_FILES['mainImage']) && $_FILES['mainImage']['error'] === UPLOAD_ERR_OK) {
        $mainImage = $_FILES['mainImage'];
        $mainImagePath = '/uploads/' . basename($mainImage['name']); // Save path
        echo "Uploading main image to: " . $_SERVER['DOCUMENT_ROOT'] . $mainImagePath;  // Debugging path
        if (!move_uploaded_file($mainImage['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $mainImagePath)) {
            $errorMessage = error_get_last()['message'];  // Get the last error message
            echo json_encode(['success' => false, 'message' => 'Failed to upload main image: ' . $errorMessage]);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Main image is required or there was an upload error.']);
        exit;
    }

    // Handle file uploads for supporting images
    if (isset($_FILES['supportingImages']) && is_array($_FILES['supportingImages']['error'])) {
        foreach ($_FILES['supportingImages']['error'] as $index => $error) {
            if ($error === UPLOAD_ERR_OK) {
                $supportingImage = $_FILES['supportingImages']['tmp_name'][$index];
                $supportingImageName = '/uploads/' . basename($_FILES['supportingImages']['name'][$index]);
                echo "Uploading supporting image to: " . $_SERVER['DOCUMENT_ROOT'] . $supportingImageName;  // Debugging path
                if (!move_uploaded_file($supportingImage, $_SERVER['DOCUMENT_ROOT'] . $supportingImageName)) {
                    echo json_encode(['success' => false, 'message' => 'Failed to upload one or more supporting images.']);
                    exit;
                }
                // Add uploaded supporting image path to array
                $supportingImages[] = $supportingImageName;
            } else {
                echo json_encode(['success' => false, 'message' => 'Error uploading supporting images.']);
                exit;
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Supporting images are required or there was an upload error.']);
        exit;
    }

    // Store the form data and file paths in session or database
    $_SESSION['campaignData'] = [
        'campaignTitle' => $campaignTitle,
        'campaignRunBy' => $campaignRunBy,
        'campaignDescription' => $campaignDescription,
        'amountToRaise' => $amountToRaise,
        'tags' => $tags,
        'mainImage' => $mainImagePath,  // Store the main image path
        'supportingImages' => $supportingImages,  // Store the array of supporting images paths
    ];

    header('Location: SelectT.php');
    exit;
} else {
    // If the request is not a POST request, handle the error
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>