<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: Login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create a Campaign</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="CreateC.css" rel="stylesheet">
</head>
<body>
    <a href="UHome.php"><button class="back-button" onclick="window.history.back();">&larr;</button></a>
    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4">Create a Campaign</h2>
            <form id="campaignForm" action="CSA.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="campaignTitle">Campaign Title</label>
                    <input type="text" class="form-control" id="campaignTitle" name="campaignTitle" required>
                </div>
                <div class="form-group">
                    <label for="campaignRunBy">Campaign Run By</label>
                    <input type="text" class="form-control" id="campaignRunBy" name="campaignRunBy" required>
                </div>
                <div class="form-group">
                    <label for="campaignDescription">Description</label>
                    <textarea class="form-control" id="campaignDescription" name="campaignDescription" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="amountToRaise">Amount to Raise</label>
                    <input type="number" class="form-control" id="amountToRaise" name="amountToRaise" min="0.01" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="tags">Tags</label>
                    <input type="text" class="form-control" id="tags" name="tags" required>
                </div>
                <div class="form-group">
                    <label for="mainImage">Main Image</label>
                    <input type="file" class="form-control-file" id="mainImage" name="mainImage" accept="image/*" required>
                </div>
                <div class="form-group">
                    <label for="supportingImages">Supporting Images</label>
                    <input type="file" class="form-control-file" id="supportingImages" name="supportingImages[]" multiple accept="image/*" required>
                    <div id="supportingImagesPreview" class="mt-2"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Submit</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('campaignForm');
    const supportingImagesInput = document.getElementById('supportingImages');
    const supportingImagesPreview = document.getElementById('supportingImagesPreview');
    const mainImageInput = document.getElementById('mainImage');

    const patterns = {
        campaignTitle: /^[a-zA-Z0-9\s\-]{3,25}$/, // Allows 3-15 characters: letters, numbers, spaces, hyphens
        campaignRunBy: /^[a-zA-Z0-9\s\-]{3,15}$/, // Same as campaignTitle
        campaignDescription: /^.{10,1000}$/, // Minimum 10 characters
        amountToRaise: /^(0|[1-9]\d*)(\.\d{1,2})?$/,
        tags: /^[a-zA-Z0-9\s,]+$/, // Comma-separated words
    };

    const messages = {
        campaignTitle: "Title must be 3-25 characters long and can include letters, numbers, spaces, and hyphens.",
        campaignRunBy: "Run By field must be 3-15 characters long and can include letters, numbers, spaces, and hyphens.",
        campaignDescription: "Description must be at least 10 characters long.",
        amountToRaise: "Invalid Amount",
        tags: "Tags must be a comma-separated list of words (e.g., 'education, charity').",
        mainImage: "Please upload a main image.",
        supportingImages: "Please upload at least one supporting image.",
    };

    function validateField(field, value) {
        if (!patterns[field].test(value)) {
            return messages[field];
        }
        return '';
    }

    function displayError(input, message) {
        let errorDiv = input.parentElement.querySelector('.error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message text-danger mt-1';
            input.parentElement.insertBefore(errorDiv, input.nextSibling);
        }
        errorDiv.textContent = message;
    }

    function clearError(input) {
        const errorDiv = input.parentElement.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    form.addEventListener('input', function (event) {
        const input = event.target;
        const field = input.name;

        if (field === 'mainImage' || field === 'supportingImages') {
            if (input.files.length > 0) {
                clearError(input);
            }
            return;
        }

        if (patterns[field]) {
            const errorMessage = validateField(field, input.value);
            if (errorMessage) {
                displayError(input, errorMessage);
            } else {
                clearError(input);
            }
        }
    });

    supportingImagesInput.addEventListener('change', function () {
        supportingImagesPreview.innerHTML = '';
        if (this.files.length > 0) {
            clearError(this);
            Array.from(this.files).forEach(file => {
                displayImagePreview(file, supportingImagesPreview);
            });
        } else {
            displayError(this, messages.supportingImages);
        }
    });

    mainImageInput.addEventListener('change', function () {
        if (this.files.length > 0) {
            clearError(this);
        } else {
            displayError(this, messages.mainImage);
        }
    });

    form.addEventListener('submit', function (event) {
        let isValid = true;

        // Validate all fields
        for (const field in patterns) {
            const input = form.querySelector(`[name="${field}"]`);
            const errorMessage = validateField(field, input.value);
            if (errorMessage) {
                isValid = false;
                displayError(input, errorMessage);
            } else {
                clearError(input);
            }
        }

        // Validate file inputs
        if (mainImageInput.files.length === 0) {
            isValid = false;
            displayError(mainImageInput, messages.mainImage);
        }
        if (supportingImagesInput.files.length === 0) {
            isValid = false;
            displayError(supportingImagesInput, messages.supportingImages);
        }

        // Prevent form submission if any field is invalid
        if (!isValid) {
            event.preventDefault();
        }
    });

    function displayImagePreview(file, previewContainer) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'img-thumbnail';
            img.style.maxWidth = '100px';
            img.style.marginRight = '5px';
            previewContainer.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
});

    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
