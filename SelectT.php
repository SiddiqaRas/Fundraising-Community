<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: Login.html");
    exit();
}

// Check if POST data exists, then save to the session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['campaignData'] = $_POST; // Store POST data in session
}

// Retrieve the session data if available
if (!isset($_SESSION['campaignData'])) {
    header('Location: CreateC.php');
    exit;
}

$campaignData = $_SESSION['campaignData'];
$mainImage = isset($campaignData['mainImage']) ? $campaignData['mainImage'] : '';
$supportingImages = isset($campaignData['supportingImages']) ? $campaignData['supportingImages'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Template Selection and Preview</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .template-container {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.template {
    border: 2px solid #ccc;
    padding: 15px;
    border-radius: 8px;
    cursor: pointer;
    text-align: center;
    flex: 1;
    margin: 0 10px;
}

.template:hover {
    border-color: black;
}

.template.selected {
    border-color: black;
    background-color: lightgray;
}

.navigation-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.back-button {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: black;
}

.back-button:hover {
    text-decoration: underline;
}

.submit-button {
    background-color: black;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 5px;
}

.back-button {
    position: absolute;
    top: 10px;
    left: 10px;
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
}

.container {
    margin-bottom: 20px;
}

#previewSection img {
    max-width: 100%; /* Prevent overflow */
    height: auto; /* Maintain aspect ratio */
    display: block; /* Center-align images */
    margin: 20px auto; /* Center images with margin */
    border: 1px solid #ccc; /* Optional border for clarity */
    border-radius: 5px; /* Optional rounded corners */
}

/* Supporting Images Styling */
.image-preview-container img {
    width: 250px; /* Fixed width for uniformity */
    height: 200px; /* Fixed height for uniformity */
    margin: 5px; /* Space between images */
    border: 1px solid #ccc; /* Border around images */
    border-radius: 5px; /* Optional rounded corners */
    cursor: pointer; /* Pointer cursor for interactivity */
}

.image-preview-container {
    display: flex; /* Arrange images in a row */
    flex-wrap: wrap; /* Wrap to next line if needed */
    justify-content: center; /* Center-align images */
    gap: 2px; /* Space between images */
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

.template-info {
    text-align: left; /* Left-align text */
    overflow-wrap: break-word; /* Wrap long words */
    word-wrap: break-word; /* For backward compatibility */
    word-break: break-word; /* Break words at any point */
    width: 100%; /* Ensure the content stays within the container */
    padding: 10px;
    box-sizing: border-box; /* Include padding in the width calculation */
}

.template-info p,
.template-info h3 {
    text-align: left; /* Left-align text */
}


    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation Buttons -->
        <div class="navigation-buttons">
            <a href="CreateC.php">
                <button class="back-button">&larr;</button>
            </a>
            <button class="submit-button" onclick="submitTemplate()">Submit</button>
        </div>

        <h2 class="text-center mb-4">Select and Preview Your Campaign Template</h2>

        <!-- Template Buttons -->
        <div class="template-container">
            <div id="template1" class="template" onclick="selectTemplate('template1')">
                <p>Template 1</p>
            </div>
            <div id="template2" class="template" onclick="selectTemplate('template2')">
                <p>Template 2</p>
            </div>
            <div id="template3" class="template" onclick="selectTemplate('template3')">
                <p>Template 3</p>
            </div>
            <div id="template4" class="template" onclick="selectTemplate('template4')">
                <p>Template 4</p>
            </div>
        </div>

        <!-- Preview Section -->
        <div id="previewSection" class="mt-4 d-flex flex-column justify-content-center align-items-center">
            <h3 class="text-center">Template Preview</h3>
            <div id="templateInfo" class="template-info"></div>
        </div>
    </div>

    <script>
        let selectedTemplate = '';
        const campaignData = <?= json_encode($campaignData); ?>; // Pass PHP data to JavaScript

        function selectTemplate(templateId) {
            document.querySelectorAll('.template').forEach(t => t.classList.remove('selected'));
            document.getElementById(templateId).classList.add('selected');
            selectedTemplate = templateId;
            displayPreview(templateId);
        }

        function displayPreview(template) {
            const { campaignTitle, campaignRunBy, campaignDescription, amountToRaise, tags, mainImage, supportingImages } = campaignData;

            const templateInfo = document.getElementById('templateInfo');
            let content = '';

            // Add main image to preview
            let mainImageHTML = mainImage ? `<img src="${mainImage}" alt="Main Image">` : '';
            
            // Add supporting images to preview
            let supportingImagesHTML = '';
            if (supportingImages && supportingImages.length > 0) {
                supportingImagesHTML = `<div class="image-preview-container">`;
                supportingImages.forEach(image => {
                    supportingImagesHTML += `<img src="${image}" alt="Supporting Image">`;
                });
                supportingImagesHTML += `</div>`;
            }

            switch (template) {
                case 'template1':
                    content = `
                        <h3>${campaignTitle}</h3>
                        <p><strong>Run By:</strong> ${campaignRunBy}</p>
                        ${supportingImagesHTML}
                        <p><strong>Description:</strong> ${campaignDescription}</p>
                        <p><strong>Amount to Raise:</strong> ${amountToRaise}</p>
                        <p><strong>Tags:</strong> ${tags}</p>
                        ${mainImageHTML}
                        
                    `;
                    break;
                case 'template2':
                    content = `
                        ${mainImageHTML}
                        <h3>${campaignTitle}</h3>
                        <p><strong>Description:</strong> ${campaignDescription}</p>
                        <p><strong>Amount to Raise:</strong> ${amountToRaise}</p>
                        <p><strong>Tags:</strong> ${tags}</p>
                        <p><strong>Run By:</strong> ${campaignRunBy}</p>
                        ${supportingImagesHTML}
                    `;
                    break;
                case 'template3':
                    content = `
                        <h3>${campaignTitle}</h3>
                        <p><strong>Run By:</strong> ${campaignRunBy}</p>
                        <p><strong>Description:</strong> ${campaignDescription}</p>
                        ${mainImageHTML}
                        <p><strong>Amount to Raise:</strong> ${amountToRaise}</p>
                        <p><strong>Tags:</strong> ${tags}</p>
                        ${supportingImagesHTML}
                    `;
                    break;
                case 'template4':
                    content = `
                        ${mainImageHTML}
                        ${supportingImagesHTML}
                        <h3>${campaignTitle}</h3>
                        <p><strong>Run By:</strong> ${campaignRunBy}</p>
                        <p><strong>Description:</strong> ${campaignDescription}</p>
                        <p><strong>Amount to Raise:</strong> ${amountToRaise}</p>
                        <p><strong>Tags:</strong> ${tags}</p>
                        
                    `;
                    break;
                default:
                    content = '<p>Template not found.</p>';
            }

            templateInfo.innerHTML = content;
        }

        function submitTemplate() {
            if (selectedTemplate) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'UserSTA.php';
                
                Object.entries(campaignData).forEach(([key, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                });

                const templateInput = document.createElement('input');
                templateInput.type = 'hidden';
                templateInput.name = 'selectedTemplate';
                templateInput.value = selectedTemplate;
                form.appendChild(templateInput);

                document.body.appendChild(form);
                form.submit();
            } else {
                alert('Please select a template before submitting.');
            }
        }
    </script>
</body>
</html>
