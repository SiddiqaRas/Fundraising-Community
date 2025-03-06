<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.html");
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);



// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fundraisingcommunity";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("<div style='color: red; font-weight: bold;'>Connection failed: " . $conn->connect_error . "</div>");
}

// Initialize error message variable
$error_message = "";
// Fetch the logged-in user's wallet address from the users table
$user_id = $_SESSION['user_id'];
$sql_wallet = "SELECT wallet FROM users WHERE id = ?";
$stmt_wallet = $conn->prepare($sql_wallet);
$stmt_wallet->bind_param("i", $user_id);
$stmt_wallet->execute();
$result_wallet = $stmt_wallet->get_result();
$wallet = $result_wallet->fetch_assoc();

if ($wallet) {
    $user_wallet_address = $wallet['wallet'];
} else {
    $error_message = "No wallet address found for the user.";
    exit();
}
$stmt_wallet->close();  // Close the wallet query statement

// Check if campaign data is passed via GET
if (isset($_GET['campaign_id']) && is_numeric($_GET['campaign_id'])) {
    $campaign_id = $_GET['campaign_id'];

    // Fetch campaign details and calculate total donations dynamically
    $sql = "
        SELECT ac.*, 
               COALESCE(SUM(d.amount), 0) AS total_donations
        FROM approved_campaigns ac
        LEFT JOIN donations d ON ac.campaign_id = d.campaign_id
        WHERE ac.campaign_id = ? 
        GROUP BY ac.campaign_id
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("<div style='color: red; font-weight: bold;'>Error preparing statement: " . $conn->error . "</div>");
    }

    $stmt->bind_param("i", $campaign_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $campaign = [];
    if ($row = $result->fetch_assoc()) {
        $campaign = $row;
    } else {
        $error_message = "Campaign not found.";
    }
} else {
    $error_message = "Invalid campaign ID.";
}

// Handle donation via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents("php://input"), true);
    $donation_amount = (float)$input['donation_amount']; // Ensure donation amount is treated as a float
    $user_id = $_SESSION['user_id'];
    $response = [];

    // Check if donation amount exceeds the remaining target amount
    if ($donation_amount > $campaign['amountToRaise'] - $campaign['total_donations']) {
        $response['success'] = false;
        $response['message'] = "Donation amount cannot exceed the remaining target amount.";
    } else {
        // Prepare the insert statement for the donation
        $donation_sql = "INSERT INTO donations (campaign_id, user_id, amount) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($donation_sql);
        if ($stmt) {
            // Use 'idi' to bind the parameters (i = integer, d = decimal)
            $stmt->bind_param("iid", $campaign['campaign_id'], $user_id, $donation_amount);
            if ($stmt->execute()) {
                // Fetch updated donation totals
                $sql_check = "
                    SELECT SUM(amount) AS total_donations
                    FROM donations
                    WHERE campaign_id = ? 
                ";
                $stmt_check = $conn->prepare($sql_check);
                if ($stmt_check) {
                    $stmt_check->bind_param("i", $campaign['campaign_id']);
                    $stmt_check->execute();
                    $result_check = $stmt_check->get_result();
                    if ($row = $result_check->fetch_assoc()) {
                        // Check if the campaign goal is reached
                        $total_donations = $row['total_donations'];
                        $amount_to_raise = $campaign['amountToRaise'];

                        // Update the campaign status if the goal is reached
                        if ($total_donations >= $amount_to_raise) {
                            // Update the status to 1 (completed)
                            $update_status_sql = "UPDATE approved_campaigns SET status = 1 WHERE campaign_id = ?";
                            $stmt_status = $conn->prepare($update_status_sql);
                            if ($stmt_status) {
                                $stmt_status->bind_param("i", $campaign['campaign_id']);
                                $stmt_status->execute();
                            }
                        }

                        // Return success response
                        $response['success'] = true;
                        $response['total_donations'] = number_format($total_donations, 2); // Format to 2 decimal places
                        $response['amount_to_raise'] = number_format($amount_to_raise, 2); // Format to 2 decimal places
                    }
                }
            } else {
                // Error processing donation
                $response['success'] = false;
                $response['message'] = "Error processing donation: " . $conn->error;
            }
        }
    }

    // Return the response as JSON
    echo json_encode($response);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ethers/5.7.2/ethers.umd.min.js"></script>
    <title>Donate to Campaign</title>
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
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .error-message {
            color: red;
            font-weight: bold;
        }
        .alert-success{
            color:black !important;
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
<div class="container">
    <?php if ($error_message): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <?php if (empty($user_wallet_address)): ?>
        <div class="error-message">Error: No wallet address found for the logged-in user.</div>
    <?php endif; ?>

    <?php if (isset($campaign) && !empty($campaign)): ?>
        <h2 class="text-center">Donate to Campaign: <?php echo $campaign['campaignTitle']; ?></h2>
        <div class="mb-3">
            <h4>Target Amount: <?php echo number_format(floatval($campaign['amountToRaise']), 2); ?></h4>
            <h4>Amount Collected: <span class="amount-collected"><?php echo number_format(floatval($campaign['total_donations']), 2); ?></span></h4>

            <div class="progress" style="height: 30px;">
                <?php
                $amountToRaise = floatval($campaign['amountToRaise']);
                $totalDonations = floatval($campaign['total_donations']);
                $progressPercentage = ($amountToRaise > 0.01) ? (($totalDonations / $amountToRaise) * 100) : 0;
                ?>
                <div class="progress-bar" style="width: <?php echo $progressPercentage; ?>%;"></div>
            </div>
        </div>

        <?php if ($campaign['total_donations'] < $campaign['amountToRaise']): ?>
            <form id="donationForm" method="POST">
                <div class="mb-3">
                    <label for="donationAmount" class="form-label">Donation Amount </label>
                    <input type="number" name="donation_amount" class="form-control" id="donationAmount" min="0.01" step="0.01" required>
                </div>
                <button type="submit" class="btn btn-primary">Donate</button>
            </form>
        <?php else: ?>
            <div class="alert alert-success">The campaign goal has been reached. Thank you for your support!</div>
        <?php endif; ?>
    <?php endif; ?>
</div>

    <script>
         // Pass the wallet address to JavaScript
        const userWalletAddress = "<?php echo $user_wallet_address; ?>";
        console.log(userWalletAddress); // Debugging: Check the wallet address value
        

        const form = document.getElementById("donationForm");
        form.addEventListener("submit", async function (e) {
            e.preventDefault();

            const donationAmount = document.getElementById("donationAmount").value;
             // Ensure the wallet address is available
             if (!userWalletAddress) {
                alert("Error: No wallet address found for the logged-in user.");
                return;
             }
             if (donationAmount < 0.01) {
                alert("Donation amount must be at least 0.01.");
                return;
            }

            const remainingTarget = <?php echo $campaign['amountToRaise'] - $campaign['total_donations']; ?>;

            // Check if the donation amount exceeds the remaining target
            if (donationAmount > remainingTarget) {
                alert("Donation amount cannot exceed the remaining target amount.");
                return;
            }

            // blockchain integration
            const campaignId = "<?php echo $campaign['blockchain_id']; ?>"; // Blockchain campaign ID
            //  const currentWalletAddress = await signer.getAddress();
            // Check if MetaMask is installed
            if (typeof window.ethereum !== "undefined") {
                const provider = new ethers.providers.Web3Provider(window.ethereum);
                const signer = provider.getSigner();

                // Ensure the wallet used for transaction is the correct one (same as userWalletAddress)
                const currentWalletAddress = await signer.getAddress();

                const normalizedCurrentWalletAddress = currentWalletAddress.toLowerCase();
                const normalizedUserWalletAddress = userWalletAddress.toLowerCase();
                if (normalizedCurrentWalletAddress !== normalizedUserWalletAddress) {
                    alert("The connected MetaMask wallet address doesn't match the logged-in user's wallet.");
                    return;
                }


                // Contract address and ABI
                const contractAddress = "0x99f45fD8241782C560d1C7183e57490f0CCDCF5C";
                const abi = [
	{
		"inputs": [
			{
				"internalType": "string",
				"name": "_blockchainId",
				"type": "string"
			}
		],
		"name": "donate",
		"outputs": [],
		"stateMutability": "payable",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "_campaignContractAddress",
				"type": "address"
			}
		],
		"stateMutability": "nonpayable",
		"type": "constructor"
	},
	{
		"anonymous": false,
		"inputs": [
			{
				"indexed": true,
				"internalType": "string",
				"name": "blockchainId",
				"type": "string"
			},
			{
				"indexed": true,
				"internalType": "address",
				"name": "donor",
				"type": "address"
			},
			{
				"indexed": false,
				"internalType": "uint256",
				"name": "amount",
				"type": "uint256"
			}
		],
		"name": "DonationMade",
		"type": "event"
	},
	{
		"inputs": [],
		"name": "campaignContract",
		"outputs": [
			{
				"internalType": "contract CampaignContract",
				"name": "",
				"type": "address"
			}
		],
		"stateMutability": "view",
		"type": "function"
	}
]

                const contract = new ethers.Contract(contractAddress, abi, signer);

                try {
                    // Request the user to connect their wallet
                    await provider.send("eth_requestAccounts", []);

                    // Convert the donation amount to Wei (smallest unit)
                    const donationInWei = ethers.utils.parseEther(donationAmount);

                    // Call the donate function on the smart contract
                    const tx = await contract.donate(campaignId, { value: donationInWei });

                    // Wait for the transaction to be mined
                    await tx.wait();

                    // Update the front-end with new donation amount
                    alert("Donation successful!");
                    window.location.reload(); // Refresh the page to update the progress
                } catch (error) {
                    console.error("Error:", error);
                    alert("Donation failed. Please try again.");
                }
            } else {
                alert("Please install MetaMask to donate.");
            }
            fetch("", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ donation_amount: donationAmount })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the progress bar dynamically
                    const progressBar = document.querySelector(".progress-bar");
                    const newPercentage = (data.total_donations / data.amount_to_raise) * 100;
                    progressBar.style.width = `${newPercentage}%`;

                    // Update the amount collected text immediately
                    const amountCollectedElement = document.querySelector(".amount-collected");
                    if (amountCollectedElement) {
                        amountCollectedElement.innerText = `$${data.total_donations.toFixed(2)}`;
                    }

                    // Optionally: Smoothly animate the progress bar update
                    progressBar.style.transition = "width 0.5s ease-in-out";
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => console.error("Error:", error));
        });
    </script>
</body>
</html>