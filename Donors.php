<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fundraisingcommunity";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $campaign_id = $_GET['id'];

    // Query to fetch donors for the campaign
    $sql = "
        SELECT u.name, d.amount, d.donation_date
        FROM donations d
        JOIN users u ON d.user_id = u.id
        WHERE d.campaign_id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $campaign_id);
    $stmt->execute();
    $result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donors for Campaign</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            height: 100%;
        }

        .content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
        }
    </style>
</head>
<body>
<nav class="navbar sticky-top navbar-expand-lg navbar-dark" style="background-color: black;">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="UHome.php">
                <img src="logo.PNG" alt="" width="50" height="40">
                <span class="ms-2">FundRaisingC.</span>
            </a>
        </div>
</nav>
<div class="container mt-5">
    <h2>Donors for Campaign</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Donor Name</th>
                <th>Amount Donated</th>
                <th>Donation Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Output the donors in table rows
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['amount']) . "</td>";
                echo "<td>" . htmlspecialchars($row['donation_date']) . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>

<?php
} else {
    echo "Invalid campaign ID.";
}

// Close the connection
$conn->close();
?>
