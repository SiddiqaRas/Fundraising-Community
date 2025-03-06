<?php
session_start();
// Checking if admin is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: Login.html");
    exit();
}

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

// Mark as read (update is_read column) if mark_as_read_completed parameter is passed for completed campaigns
if (isset($_GET['mark_as_read_disapproved'])) {
    $campaign_id = intval($_GET['mark_as_read_disapproved']);

    $stmt = $conn->prepare("UPDATE disapproved_campaigns SET is_read = 1 WHERE dcampaign_id = ?");
    $stmt->bind_param("i", $campaign_id);

    if ($stmt->execute()) {
        header("Location: notifications.php");
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }
    $stmt->close();
}






// if (isset($_GET['mark_as_read_disapproved'])) {
//     $campaign_id = intval($_GET['mark_as_read_disapproved']);
    
//     // Update the is_read column to 1 for the specified completed campaign
//     $update_sql_completed = "UPDATE disapproved_campaigns SET is_read = 1 WHERE dcampaign_id = $campaign_id";
    
//     if ($conn->query($update_sql_completed) === TRUE) {
//         // Redirect back to the same page after updating
//         header("Location: notifications.php");
//         exit();
//     } else {
//         echo "Error updating record: " . $conn->error;
//     }
// }

// Fetch notifications for the logged-in user
$user_id = $_SESSION['user_id']; // Assuming user ID is stored in the session

$sql_notifications = "(SELECT 
                        'campaign' AS type, 
                        c.campaign_id AS id, 
                        c.campaignTitle AS title, 
                        c.created_at AS notification_date, 
                        '' AS amount, 
                        c.user_id_campaign AS user_id,
                        c.is_read, c.status
                        FROM approved_campaigns c
                        WHERE c.user_id_campaign = '$user_id' AND c.is_read = 0)

                        UNION

                        (SELECT 
                        'donation' AS type, 
                        d.donation_id AS id, 
                        c.campaignTitle AS title, 
                        d.donation_date AS notification_date, 
                        d.amount AS amount, 
                        d.user_id AS user_id,
                        d.is_read,
                        '' AS status  -- We still need this to match the number of columns with other queries
                        FROM donations d
                        LEFT JOIN approved_campaigns c ON d.campaign_id = c.campaign_id
                        WHERE c.user_id_campaign = '$user_id' AND d.is_read = 0)

                        UNION

                        (SELECT 
                        'completed_campaign' AS type, 
                        c.campaign_id AS id, 
                        c.campaignTitle AS title, 
                        '' AS notification_date, -- Added empty value for the 'notification_date' column
                        '' AS amount, 
                        c.user_id_campaign AS user_id,
                        c.is_read, c.status
                        FROM approved_campaigns c
                        WHERE c.user_id_campaign = '$user_id' AND c.status = 1 AND c.is_read = 0)

                        UNION

                        (SELECT 
                            'disapproved_campaign' AS type, 
                            d.dcampaign_id AS id, 
                            d.dcampaignTitle AS title, 
                            d.dcreated_at AS notification_date, 
                            '' AS amount, 
                            d.duser_id_campaign AS user_id,
                            d.is_read, 
                            '' AS status  -- We still need this to match the number of columns with other queries
                            FROM disapproved_campaigns d
                            WHERE d.duser_id_campaign = '$user_id' AND d.is_read = 0)

                        ORDER BY notification_date DESC";


    // Execute the combined query
    $notifications_result = $conn->query($sql_notifications);

    if (!$notifications_result) {
        // Query failed, output the error message for debugging
        die("Error in SQL query: " . $conn->error);
    }


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .notification {
            width: 80%;
            margin: auto;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            border-bottom: 1px solid #a7a7a7;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            flex-wrap: wrap; /* Ensures that the content wraps neatly if needed */

        }
        .notification .content {
            flex-grow: 1; /* Makes the content container grow to fill the available space */
        }

        .notification .action {
            margin-left: 10px; /* Adjust the margin as necessary */
        }

        .notification .image-container img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
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
        </div>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="d-flex align-items-center ms-auto">
                <a href="notifications.php" class="me-3">
                    <svg xmlns="http://www.w3.org/2000/svg" style="margin-right: 20px;" width="16" height="16" fill="white" class="bi bi-bell-fill" viewBox="0 0 16 16">
                        <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zm.5-14a5.5 5.5 0 0 1 5.5 5.5v.9l.943 2.83A1 1 0 0 1 14 12H2a1 1 0 0 1-.943-1.27l.943-2.83v-.9A5.5 5.5 0 0 1 7.5 2z"/>
                    </svg>
                </a>
                <a href="ManageP.html"><svg xmlns="http://www.w3.org/2000/svg" style="margin-right: 20px;" width="16" height="16" fill="white" class="bi bi-person-fill" viewBox="0 0 16 16">
                    <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                        </svg>
                </a>
                        
                <a href="logout.php" class="btn btn-outline-danger btn-sm px-4 mx-4" style="border-radius: 20px;" onclick="return confirm('Are you sure you want to log out?')">
                    <i class="fa fa-sign-out"></i> Logout
                </a>
            </div>               
        </div>

            
    </nav>
    <!-- NAVBAR ENDS -->

    <div id="approved-campaigns" class="container mt-5">
        <h1 class="mb-5" style="text-align: center;">Your Notifications</h1>
        <?php
        // Check if there are any notifications
        if ($notifications_result->num_rows > 0) {
    
            while ($row = $notifications_result->fetch_assoc()) {
                // Determine the type of notification
                if ($row['type'] === 'campaign') {
                    // Handle campaign approval notification
                    $approve_image = '/uploads/notifications/approved.jpeg';
                    $formatted_time = (new DateTime($row['notification_date']))->format('M-d \a\t H:i');
                    ?>
                    <div class="notification d-flex align-items-center">
                        <div class="image-container me-3">
                            <img src="<?php echo $approve_image; ?>" alt="Campaign Notification">
                        </div>
                        <div class="content flex-grow-1">
                            <h3 class="mb-1">Campaign Approved</h3>
                            <p class="mb-1">Approved on <?php echo $formatted_time; ?></p>
                            <p class="mb-1"><strong>Campaign Title:</strong> <?php echo htmlspecialchars($row['title']); ?></p>
                            <a href="UOngoingC.php?campaign_id=<?php echo $row['id']; ?>" class="btn btn-dark px-3 btn-sm">View</a>
                        </div>
                        <div class="action ms-3">
                            <a href="UOngoingC.php?mark_as_read=<?php echo $row['id']; ?>" class="btn btn-dark btn-sm" onclick="removeNotificationAndUpdateStatus(event, <?php echo $row['id']; ?>)">Delete</a>
                        </div>
                    </div>
                    <?php
                } elseif ($row['type'] === 'donation') {
                    // Handle donation notification
                    $donate_image = '/uploads/notifications/donate.jpeg';
                    $donation_datetime = new DateTime($row['notification_date']);
                    $donation_formatted_time = $donation_datetime->format('M-d \a\t H:i');
                    
                    // Fetch the donor's name
                    $user_sql = "SELECT name FROM users WHERE id = " . $row['user_id'];
                    $user_result = $conn->query($user_sql);
                    $username = ($user_result && $user_result->num_rows > 0) ? $user_result->fetch_assoc()['name'] : "Unknown";
                    ?>
                    <div class="notification d-flex align-items-center">
                        <div class="image-container me-3">
                            <img src="<?php echo $donate_image; ?>" alt="Donation Notification">
                        </div>
                        <div class="content flex-grow-1">
                            <h3 class="mb-1">Donation</h3>
                            <p class="mb-1">Donated on <?php echo $donation_formatted_time; ?></p>
                            <p class="mb-1"><?php echo htmlspecialchars($username); ?> donated <b><?php echo $row['amount']; ?> ETH</b> in your campaign: <?php echo htmlspecialchars($row['title']); ?></p>
                            <a href="UOngoingC.php?campaign_id=<?php echo $row['id']; ?>" class="btn btn-dark px-3 btn-sm">View</a>
                        </div>
                        <div class="action ms-3">
                            <a href="UOngoingC.php?mark_as_read_donation=<?php echo $row['id']; ?>" class="btn btn-dark btn-sm" onclick="removeNotificationAndUpdateStatus(event, <?php echo $row['id']; ?>)">Delete</a>
                        </div>
                    </div>
                    <?php
                } elseif ($row['type'] === 'completed_campaign') {
                    // Handle completed campaign notification (status = 1 and is_read = 0)
                    $approve_image = '/uploads/notifications/completed.jpeg'; // Assuming you have an image for completed campaigns
                    $formatted_time = (new DateTime($row['notification_date']))->format('M-d \a\t H:i');
                    ?>
                    <div class="notification d-flex align-items-center">
                        <div class="image-container me-3">
                            <img src="<?php echo $approve_image; ?>" alt="Campaign Notification">
                        </div>
                        <div class="content flex-grow-1">
                            <h3 class="mb-1">Campaign Completed</h3>
                            <p class="mb-1"><strong>Campaign Title:</strong> <?php echo htmlspecialchars($row['title']); ?></p>
                            <a href="UCompletedC.php?campaign_id=<?php echo $row['id']; ?>" class="btn btn-dark px-3 btn-sm">View</a>
                        </div>
                        <div class="action ms-3">
                            <a href="UCompletedC.php?mark_as_read_completed=<?php echo $row['id']; ?>" class="btn btn-dark btn-sm" onclick="removeNotificationAndUpdateStatus(event, <?php echo $row['id']; ?>)">Delete</a>
                        </div>
                    </div>
                    <?php
                } elseif ($row['type'] === 'disapproved_campaign') {
                    // Handle completed campaign notification (status = 1 and is_read = 0)
                    $approve_image = '/uploads/notifications/disapproved.jpeg'; // Assuming you have an image for completed campaigns
                    $formatted_time = (new DateTime($row['notification_date']))->format('M-d \a\t H:i');
                    ?>
                    <div class="notification d-flex align-items-center">
                        <div class="image-container me-3">
                            <img src="<?php echo $approve_image; ?>" alt="Campaign Notification">
                        </div>
                        <div class="content flex-grow-1">
                            <h3 class="mb-1">Campaign Disapproved</h3>
                            <p class="mb-1">Disapproved on <?php echo $formatted_time; ?></p>
                            <p class="mb-1"><strong>Campaign Title:</strong> <?php echo htmlspecialchars($row['title']); ?></p>
                        </div>
                        <div class="action ms-3">
                            <a href="notifications.php?mark_as_read_disapproved=<?php echo $row['id']; ?>" class="btn btn-dark btn-sm" onclick="removeNotificationAndUpdateStatus(event, <?php echo $row['id']; ?>)">Delete</a>
                        </div>
                    </div>
                    <?php
                }
                
            }
        } else {
            echo "<h4 style='text-align:center'>No notifications found.</h4>";
        }
        ?>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    
        // Pass the PHP session user_id to JavaScript
        const userId = <?php echo json_encode($_SESSION['user_id']); ?>;

        // Print the user_id in the browser console
        console.log("User ID from session:", userId);

        function removeNotificationAndUpdateStatus(event, notificationId) {
            event.preventDefault(); // Prevents the default action of the link (navigation)


            // Dynamically decide which query parameter to use based on the notification type
            let url;
            if (event.target.closest('.notification').querySelector('a').href.includes('mark_as_read')) {
                url = `UOngoingC.php?mark_as_read=${notificationId}`;
            } else if (event.target.closest('.notification').querySelector('a').href.includes('mark_as_read_donation')) {
                url = `UOngoingC.php?mark_as_read_donation=${notificationId}`;
            } else if (event.target.closest('.notification').querySelector('a').href.includes('mark_as_read_completed')) {
                url = `UCompletedC.php?mark_as_read_completed=${notificationId}`;
            } else if (event.target.closest('.notification').querySelector('a').href.includes('mark_as_read_disapproved')) {
                url = `notifications.php?mark_as_read_disapproved=${notificationId}`;
            }

            // Update the 'is_read' status via AJAX (without reloading the page)
            fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.text())
            .then(data => {
                console.log('Server response: ', data); // This will print the success message or error
                if (data.includes("Success")) {  // Ensure the update was successful before removing the notification
                const notification = event.target.closest('.notification');
                if (notification) {
                    notification.remove();
                }
                console.log('Notification removed from the front-end.');
            }
            })
            .catch(error => {
                console.error('Error updating notification status:', error);
            });

            // Find the closest .notification parent and remove it
            const notification = event.target.closest('.notification'); // Get the closest parent element with class .notification
            if (notification) {
                notification.remove(); // Remove the notification from the DOM
            }

            console.log('Notification removed from the front-end.');
        }
    </script>
</body>
</html>