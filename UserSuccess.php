 <?php
 session_start();
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: Login.html");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fundraisingcommunity"; // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the blockchain_id from the URL
$blockchainId = isset($_GET['blockchain_id']) ? $_GET['blockchain_id'] : null;

if ($blockchainId) {
    // SQL query to fetch the campaign amount and user wallet based on blockchain_id
    $sql = "SELECT campaigns.amountToRaise, users.wallet 
            FROM campaigns 
            JOIN users ON campaigns.user_id = users.id 
            WHERE campaigns.blockchain_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $blockchainId); // Assuming blockchain_id is a string, adjust type accordingly

    $stmt->execute();
    $stmt->bind_result($amountToRaise, $userWallet);
    $stmt->fetch();
    $stmt->close();

    if ($amountToRaise !== null && $userWallet !== null) {
        // Format the amount to be raised if necessary (e.g., to show decimal points)
        $formattedAmount = number_format($amountToRaise / 100, 2); // Assuming it was stored in cents
    } else {
        echo "Campaign data not found!";
        exit();
    }
} else {
    echo "No blockchain ID provided!";
    exit();
}

$conn->close();



// Redirect here after campaign submission is successful
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Submitted</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .message-box {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 90%;
            max-width: 400px;
        }
        .message-box h1 {
            font-size: 24px;
            color: #4CAF50;
            margin-bottom: 15px;
        }
        .message-box p {
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
        }
        .hidden {
            display: none !important;
        }
        .close-btn {
            display: inline-block;
            font-size: 16px;
            color: #fff;
            background-color: #4CAF50;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        #confirm-transaction-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
			opacity: 1;
            
        }
        #back-to-home {
            background-color: #4CAF50;
            color: white;
        }
        .close-btn:hover {
            background-color: #45a049;
        }
        .close-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            color: #888;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .close-icon:hover {
            color: #333;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ethers/5.7.2/ethers.umd.min.js"></script>

</head>
<body>
    <div class="message-box">
        <div class="close-icon" onclick="window.location.href='UHome.php';">&times;</div>
        <h1>Campaign Submitted!</h1>
        <p>Your campaign has been successfully sent to the admin for approval.</p>
        <a class="hidden close-btn" id="back-to-home" href="UHome.php">Back to Home</a>
        <button class="close-btn" id="confirm-transaction-btn">Confirm Transaction</button>
    </div>
	
    <script>
        const contractAddress = "0x5e0ff7587e8C63A09D15a6460Ec44Cec9d7D1a4F"; // Add your deployed contract address here
        const contractABI = [
	{
		"inputs": [
			{
				"internalType": "string",
				"name": "_blockchainId",
				"type": "string"
			},
			{
				"internalType": "address",
				"name": "_userWallet",
				"type": "address"
			},
			{
				"internalType": "uint256",
				"name": "_amountToRaise",
				"type": "uint256"
			}
		],
		"name": "addCampaign",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"anonymous": false,
		"inputs": [
			{
				"indexed": false,
				"internalType": "string",
				"name": "blockchainId",
				"type": "string"
			},
			{
				"indexed": false,
				"internalType": "address",
				"name": "userWallet",
				"type": "address"
			},
			{
				"indexed": false,
				"internalType": "uint256",
				"name": "amountToRaise",
				"type": "uint256"
			}
		],
		"name": "CampaignAdded",
		"type": "event"
	},
	{
		"anonymous": false,
		"inputs": [
			{
				"indexed": false,
				"internalType": "string",
				"name": "blockchainId",
				"type": "string"
			}
		],
		"name": "CampaignConfirmed",
		"type": "event"
	},
	{
		"inputs": [
			{
				"internalType": "string",
				"name": "_blockchainId",
				"type": "string"
			}
		],
		"name": "confirmCampaign",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [],
		"name": "adminWallet",
		"outputs": [
			{
				"internalType": "address",
				"name": "",
				"type": "address"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "string",
				"name": "",
				"type": "string"
			}
		],
		"name": "campaigns",
		"outputs": [
			{
				"internalType": "string",
				"name": "blockchainId",
				"type": "string"
			},
			{
				"internalType": "address",
				"name": "userWallet",
				"type": "address"
			},
			{
				"internalType": "uint256",
				"name": "amountToRaise",
				"type": "uint256"
			},
			{
				"internalType": "bool",
				"name": "isActive",
				"type": "bool"
			}
		],
		"stateMutability": "view",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "string",
				"name": "_blockchainId",
				"type": "string"
			}
		],
		"name": "getCampaign",
		"outputs": [
			{
				"internalType": "string",
				"name": "",
				"type": "string"
			},
			{
				"internalType": "address",
				"name": "",
				"type": "address"
			},
			{
				"internalType": "uint256",
				"name": "",
				"type": "uint256"
			},
			{
				"internalType": "bool",
				"name": "",
				"type": "bool"
			}
		],
		"stateMutability": "view",
		"type": "function"
	}
]
        
        // PHP variables passed to JavaScript
        const blockchainId = "<?php echo $blockchainId; ?>";
        const userWallet = "<?php echo $userWallet; ?>";
        const amountToRaise = "<?php echo $amountToRaise; ?>";
        document.addEventListener('DOMContentLoaded', async () => {

            const confirmButton = document.getElementById('confirm-transaction-btn');
            const backToHomeButton = document.getElementById('back-to-home');
            
            confirmButton.addEventListener('click', async () => {
                try {
                    if (!window.ethereum) {
                        alert("MetaMask is not installed!");
                        return;
                    }

                    // Request account access
                    await ethereum.request({ method: 'eth_requestAccounts' });
                    const provider = new ethers.providers.Web3Provider(window.ethereum);
                    const signer = provider.getSigner();

                    // Create contract instance
                    const contract = new ethers.Contract(contractAddress, contractABI, signer);

                    // Call the addCampaign function
                    const transaction = await contract.addCampaign(blockchainId, userWallet, ethers.utils.parseEther(amountToRaise));
                    alert("Transaction sent. Waiting for confirmation...");

                    // Wait for transaction to be mined
                    const receipt = await transaction.wait();
                    alert("Transaction confirmed!" );
                    // + JSON.stringify(receipt);

                    confirmButton.classList.add('hidden');
                    backToHomeButton.classList.remove('hidden');

                } catch (error) {
                    console.error("Error:", error);

                    let errorMessage = "Transaction failed!";

					// Check if the error contains a reason in the data
					if (error && error.data && error.data.message) {
						// Extract the reason from the error
						const reason = error.data.message;
						errorMessage = `Error: ${reason}`;
					} else if (error && error.message) {
						// If no specific reason is found, fallback to the default error message
						errorMessage = `Error: ${error.message}`;
					}

					alert(errorMessage); // Show the reason in the alert
                }
            });
        });

    </script>

   
</body>
</html>