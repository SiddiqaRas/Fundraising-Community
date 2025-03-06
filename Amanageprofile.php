<?php
// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: Login.html");
    exit();
}

// Database connection
$servername = "localhost";  
$username = "root";        
$password = "";             
$dbname = "fundraisingcommunity"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch admin data from the database
$adminId = $_SESSION['admin_id'];
$sql = "SELECT * FROM admin_signup WHERE admin_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$profileImage = !empty($admin['profile_picture']) ? "profilepic/admin/" . $admin['profile_picture'] : "profilepic/MP.jpeg";

$errors = [
    'name' => '',
    'email' => '',
    'currentPassword' => '',
    'newPassword' => ''
];

// Update personal information
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_personal_info'])) {
    $_SESSION['active_section'] = 'personal-info-section';
    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);

    // Validate name
    if (empty($fullName) || !preg_match("/^[a-zA-Z0-9\s]{3,20}$/", $fullName)) {
        $errors['name'] = "Name must be between 3 and 20 characters and can only contain letters, numbers, and spaces.";
    }

    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } else {
        // Check if email already exists
        $checkEmailSql = "SELECT * FROM admin_signup WHERE email = ? AND admin_id != ?";
        $checkEmailStmt = $conn->prepare($checkEmailSql);
        $checkEmailStmt->bind_param("si", $email, $adminId);
        $checkEmailStmt->execute();
        $emailResult = $checkEmailStmt->get_result();

        if ($emailResult->num_rows > 0) {
            $errors['email'] = "This email address is already associated with another account.";
        }
    }

    if (empty($errors['name']) && empty($errors['email'])) {
        $updateSql = "UPDATE admin_signup SET name = ?, email = ? WHERE admin_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ssi", $fullName, $email, $adminId);

        if ($updateStmt->execute()) {
            $_SESSION['update_message'] = "Personal information updated successfully.";
            header("Location: Amanageprofile.php");
            exit();
        } else {
            $_SESSION['update_message'] = "Error updating personal information.";
        }
    }
}

// Handle Profile Image Upload & Database Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_picture"])) {
    $_SESSION['active_section'] = 'profilepic-section';
    $targetDir = "profilepic/admin/"; // Ensure this folder exists
    $filename = time() . "_" . basename($_FILES["profile_picture"]["name"]);
    $targetFilePath = $targetDir . $filename;

    // Debugging - Check if file was uploaded properly
    if ($_FILES["profile_picture"]["error"] != 0) {
        die("Error uploading file: " . $_FILES["profile_picture"]["error"]);
    }

    // Ensure the folder exists and has correct permissions
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true); // Create folder if it doesn't exist
    }

    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)) {
        // Update the database with the new filename
        $query = "UPDATE admin_signup SET profile_picture = ? WHERE admin_id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $filename, $adminId);
        if ($stmt->execute()) {
            $_SESSION['update_message'] = "Profile picture updated successfully.";

            // Refresh admin data after updating
            $sql = "SELECT * FROM admin_signup WHERE admin_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $adminId);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();
            
            // Update session variable for immediate effect
            $_SESSION['profile_picture'] = $admin['profile_picture'];
            header("Location: Amanageprofile.php");
            exit();
        } else {
            $_SESSION['update_message'] = "Error updating database.";
        }
    } else {
        die("Failed to move uploaded file.");
    }
}


// Update account information
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_account_info'])) {
    $_SESSION['active_section'] = 'account-management-section';
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];

    // Validate current password
    if (empty($currentPassword) || !password_verify($currentPassword, $admin['password'])) {
        $errors['currentPassword'] = "Current password is incorrect.";
    }

    // Validate new password
    if (empty($newPassword) || !preg_match("/^(?=.[A-Z])(?=.[a-z])(?=.\d)(?=.[\W_])[A-Za-z\d\W_]{8,20}$/", $newPassword)) {
        $errors['newPassword'] = "Password must be 8-20 characters long, contain at least one uppercase letter, one lowercase letter, one digit, and one special character.";
    }

    if (empty($errors['currentPassword']) && empty($errors['newPassword'])) {
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateSql = "UPDATE admin_signup SET password = ? WHERE admin_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("si", $newPasswordHash, $adminId);

        if ($updateStmt->execute()) {
            $_SESSION['update_message'] = "Password updated successfully.";
            header("Location: Amanageprofile.php");
            exit();
        } else {
            $_SESSION['update_message'] = "Error updating password.";
        }
    }
}

// Handle theme selection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['theme'])) {
    $_SESSION['theme'] = $_POST['theme'];
}

$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: black;
            padding: 20px;
            color: white;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .sidebar h2 {
            font-size: 1.5em;
            margin-bottom: 40px;
            text-align: center;
        }
        .sidebar button {
            background-color: transparent;
            border: none;
            color: white;
            font-size: 1.1em;
            text-align: left;
            cursor: pointer;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }
        .sidebar button:hover {
            transition: background-color 0.3s ease;
        }
        .sidebar button.active {
            font-weight: bold;
        }
        .content {
            flex: 1;
            padding: 40px;
            background-color: <?php echo ($theme === 'dark') ? '#3e3636' : '#f4f4f4'; ?>;
            color: <?php echo ($theme === 'dark') ? '#fff' : '#000'; ?>;
            overflow-y: auto;
        }
        .content h3 {
            margin-bottom: 20px;
            color: <?php echo ($theme === 'dark') ? '#fff' : '#333'; ?>;
            border-left: 5px solid gray;
            padding-left: 10px;
        }
        .profile-field {
            margin-bottom: 20px;
        }
        .profile-field label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .profile-field input {
            width: 50%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }
        .btn-group {
            text-align: center;
            margin-top: 30px;
        }
        .btn-group button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-save, .btn-cancel {
            background-color: black !important;
            color: white;
        }
        .section {
            display: none; /* Hide all sections initially */
        }
        .active-section {
            display: block; /* Show only active section */
        }
        
        .theme-option {
            margin-bottom: 10px;
        }
        .close-icon{
            color: white !important;
        }
        .alert-danger {
            display: none;
            font-size: 14px;
            color: red;
            background-color: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: left;
        }
    .error-border {
            border-color: red !important;
        }
    .error-message{
        font-size: 14px;
        color: red;
        margin-bottom: 20px;
    }
    .settings{
        display: flex;
        justify-content: start ;
        align-items: center;
        gap:10px;
        padding: 15px;
        /* margin-bottom: 10px;
        border-bottom: 1px solid #a7a7a7; */
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);

    }
    .left-profile{
        width: 60% ; 
    }
    .right-profile {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        width: 40%; 
        overflow: hidden;
    }
    .img-box{
        display: flex;
        justify-content: center;
        align-items: center;
        height: 200px;
        width: 200px; 
    }

    .profile-img {
        width: 100%;
        height: 100%;
        object-fit: center;
        border-radius: 50%;
    }

    </style>
</head>
<body>
    <div class="sidebar">
        <a href="AHome.php" class="close-icon"><i class="fas fa-times"></i></a>
        <h2>Manage Profile</h2>
        <button onclick="showSection('profilepic-section')">Profile Picture</button>
        <button onclick="showSection('personal-info-section')">Personal Information</button>
        <button onclick="showSection('account-management-section')">Account Management</button>
        <button onclick="showSection('theme-selection-section')">Themes</button>

    </div>
    <?php if (isset($_SESSION['update_message'])): ?>
        <div class="alert alert-info" role="alert">
            <?php echo $_SESSION['update_message']; unset($_SESSION['update_message']); ?>
        </div>
        
    <?php endif; ?>

    <div class="content">
        <div id="profilepic-section" class="section" class="settings mb-3">
            <div class="left-profile" >
                        <h3>Profile Picture</h3>
                        <div class="img-box" > 
                            <img id="profileImage" src="<?php echo $profileImage; ?>" alt="Profile Picture" class="profile-img">
                        </div>
                        <div class="btn-group">
                            <form method="POST" action="Amanageprofile.php" enctype="multipart/form-data">
                                <input type="file" name="profile_picture" id="fileInput" accept="image/*" style="display: none;" onchange="previewImage(event)">
                                <button type="button" class="btn-save" onclick="document.getElementById('fileInput').click();">Upload Picture</button>
                                <button type="submit" name="update_profile_picture" class="btn-save">Save Changes</button>
                            </form>
                        
                        </div>

            </div>
        </div>
        <!-- Personal Information Section -->
        <div id="personal-info-section" class="section" class="settings mb-3">
            <div class="left-profile">
                <h3>Personal Information</h3>
                <form method="POST" action="Amanageprofile.php" id="personalinfo">
                    <div class="profile-field">
                        <label for="fullName">Full Name</label>
                        <?php if (!empty($errors['name'])): ?>
                            <div class="error-message"><?php echo $errors['name']; ?></div>
                        <?php endif; ?>
                        <input type="text" class="form-control <?php echo !empty($errors['name']) ? 'error-border' : ''; ?>" 
                            id="adminNamepi" name="fullName" 
                            value="<?php echo htmlspecialchars($admin['name']); ?>">
                    </div>

                    <div class="profile-field">
                        <label for="email">Email Address</label>
                        <?php if (!empty($errors['email'])): ?>
                            <div class="error-message"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                        <input type="text" class="form-control <?php echo !empty($errors['email']) ? 'error-border' : ''; ?>" 
                            id="adminEmailpi" name="email" 
                            value="<?php echo htmlspecialchars($admin['email']); ?>">
                    </div>

                    <div class="btn-group">
                        <button type="submit" name="update_personal_info" class="btn-save">Save Changes</button>
                        <button type="button" class="btn-cancel" onclick="hideAllErrorMessages()">Cancel</button>
                    </div>
                </form>
            </div>
            
            
        </div>
        <div id="account-management-section" class="section" class="settings mb-3">
            <div>
                <h3>Account Management</h3>
                <form method="POST"style="width: 500px;" action="Amanageprofile.php" id="accountinfo">
                    <div class="profile-field" >
                        <label for="currentPassword">Current Password</label>
                        <?php if (!empty($errors['currentPassword'])): ?>
                            <div class="error-message"><?php echo $errors['currentPassword']; ?></div>
                        <?php endif; ?>
                        <input type="password" style="width: 110%" class="form-control  <?php echo !empty($errors['currentPassword']) ? 'error-border' : ''; ?>" 
                            id="currentpassword" name="currentPassword">
                    </div>

                    <div class="profile-field">
                        <label for="newPassword">New Password</label>
                        <?php if (!empty($errors['newPassword'])): ?>
                            <div class="error-message"><?php echo $errors['newPassword']; ?></div>
                        <?php endif; ?>
                        <input type="password" style="width: 110%" class="form-control <?php echo !empty($errors['newPassword']) ? 'error-border' : ''; ?>" 
                            id="newpassword" name="newPassword">
                    </div>

                    <div class="btn-group">
                        <button type="submit" name="update_account_info" class="btn-save">Update Password</button>
                        <button type="button" class="btn-cancel" onclick="hideAllErrorMessages()">Cancel</button>
                    </div>
                    
                </form>

            </div>
            
        </div>

        <!-- Theme Selection Section -->
        <div id="theme-selection-section" class="section" class=" settings mb-3">
            <div>
                <h3>Theme Selection</h3>
                <form style="width: 500px;" id="themeForm" method="POST" action="Amanageprofile.php">
                    <div class="profile-field">
                        <label for="theme">Choose a Theme</label>
                        <select class="form-control" id="theme" name="theme">
                            <option value="light"  <?php if ($theme == 'light') echo 'selected'; ?>>Light</option>
                            <option value="dark"  <?php if ($theme == 'dark') echo 'selected'; ?>>Dark</option>
                        </select>
                        <small>Choose your preferred theme for the user interface.</small>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="update_theme" class="btn-save">Save Theme</button>
                        <button type="button" class="btn-cancel" >Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById("profileImage").src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
        
    // Function to show the active section based on sectionId
    function showSection(sectionId) {
        // Hide all sections first
        var sections = document.querySelectorAll('.section');
        sections.forEach(function(section) {
            section.classList.remove('active-section');
        });

        // Show the selected section
        var selectedSection = document.getElementById(sectionId);
        if (selectedSection) {
            selectedSection.classList.add('active-section');
        }
    }

    // Hide all error messages
    function hideAllErrorMessages() {
        var errorMessages = document.querySelectorAll('.alert-danger');
        errorMessages.forEach(function(errorMessage) {
            errorMessage.style.display = 'none';
        });
    }

    // Initialize the active section based on session or default
    document.addEventListener("DOMContentLoaded", function() {
        // Check if PHP session has set an active section
        <?php if (isset($_SESSION['active_section'])): ?>
            // Show the active section from the PHP session
            showSection('<?php echo $_SESSION['active_section']; ?>');
        <?php else: ?>
            // Default to the first section if no session value
            showSection('profilepic-section');
        <?php endif; ?>

        // Optionally, hide error messages when switching sections
        hideAllErrorMessages();
    });
        const nameRegex = /^[a-zA-Z0-9\s]{3,20}$/;
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        const passwordRegex = /^(?=.[A-Z])(?=.[a-z])(?=.\d)(?=.[\W_])[A-Za-z\d\W_]{8,20}$/;

        function showErrorMessage(elementId, message, inputId) {
            const errorElement = document.getElementById(elementId);
            errorElement.style.display = 'block';
            errorElement.textContent = message;

            const inputElement = document.getElementById(inputId);
            inputElement.classList.add('error-border');
        }

        function hideAllErrorMessages() {
            const errorElements = document.querySelectorAll('.alert-danger');
            errorElements.forEach(element => {
                element.style.display = 'none';
            });

            const inputElements = document.querySelectorAll('.form-control');
            inputElements.forEach(element => {
                element.classList.remove('error-border');
            });
        }

        function validatePersonalInfo() {
            hideAllErrorMessages();

            const form = document.getElementById('personalinfo');
            const name = form.querySelector('#adminNamepi').value.trim();
            const email = form.querySelector('#adminEmailpi').value.trim();

            let isFormValid = true; // Tracks overall form validity

            // Validate Name
            if (name === "") {
                showErrorMessage('piError1', 'Name cannot be empty.', 'adminNamepi');
                isFormValid = false;
            } else if (!nameRegex.test(name)) {
                showErrorMessage('piError1', 'Invalid name format. Only letters,digits and spaces allowed.', 'adminNamepi');
                isFormValid = false;
            } else {
                document.getElementById('adminNamepi').style.borderColor = ''; // Reset border
            }

            // Validate Email
            if (email === "") {
                showErrorMessage('piError2', 'Email cannot be empty.', 'adminEmailpi');
                isFormValid = false;
            } else if (!emailRegex.test(email)) {
                showErrorMessage('piError2', 'Invalid email format.', 'adminEmailpi');
                isFormValid = false;
            } else {
                document.getElementById('adminEmailpi').style.borderColor = ''; // Reset border
            }

            if (!isFormValid) {
                alert("Please correct the errors before submitting.");
            }

            return isFormValid; // Return true only if all validations pass
        }


        function validateAdminInfo() {
            hideAllErrorMessages();

            const form = document.getElementById('accountinfo');
            const currentPassword = form.querySelector('#currentpassword').value.trim();
            const newPassword = form.querySelector('#newpassword').value.trim();

            let isFormValid = true; // Tracks overall form validity

            // Validate Current Password
            if (currentPassword === "") {
                showErrorMessage('amError1', 'Current password cannot be empty.', 'currentpassword');
                isFormValid = false;
            } else if (!passwordRegex.test(currentPassword)) {
                showErrorMessage('amError1', 'Invalid password format.Password must be 8-20 characters long, contain at least one uppercase letter, one lowercase letter, one digit, and one special character. ', 'currentpassword');
                isFormValid = false;
            } else {
                document.getElementById('currentpassword').style.borderColor = ''; // Reset border
            }

            // Validate New Password
            if (newPassword === "") {
                showErrorMessage('amError2', 'New password cannot be empty.', 'newpassword');
                isFormValid = false;
            } else if (!passwordRegex.test(newPassword)) {
                showErrorMessage('amError2', 'Invalid password format.Password must be 8-20 characters long, contain at least one uppercase letter, one lowercase letter, one digit, and one special character. ', 'newpassword');
                isFormValid = false;
            } 

            if (!isFormValid) {
                alert("Please correct the errors before submitting.");
            }

            return isFormValid; // Return true only if all validations pass
        }


        document.getElementById('personalinfo').addEventListener('submit', function (event) {
            if (!validatePersonalInfo()) {
                event.preventDefault();
            }
        });

        document.getElementById('accountinfo').addEventListener('submit', function (event) {
            if (!validateAdminInfo()) {
                event.preventDefault();
            }
        });


    </script>
</body>
</html>