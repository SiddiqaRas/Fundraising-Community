<?php
// Start the session
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: Login.html");
    exit();
}

// Get the campaign ID from the query parameter
$campaign_id = isset($_GET['id']) ? $_GET['id'] : null;
$blockchainId = isset($_GET['blockchain_id']) ? $_GET['blockchain_id'] : null;

// Check if blockchain_id is passed correctly
if (empty($blockchainId)) {
    echo "Blockchain ID is missing or empty!";
    exit();
}

// Check if campaign_id is passed correctly
if (empty($campaign_id)) {
    echo "Campaign ID is missing!";
    exit();
}


if ($campaign_id) {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "fundraisingcommunity";  // Replace with your database name

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch campaign and images based on the ID
    $sql = "
        SELECT c.id, c.campaignTitle, c.campaignRunBy, c.campaignDescription, c.amountToRaise, c.tags, c.template_id, c.user_id,
               ci.image_url, ci.image_type
        FROM campaigns c
        LEFT JOIN campaign_images ci ON c.id = ci.campaign_id
        WHERE c.id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $campaign_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the campaign and images
    $campaign = null;
    $mainImageHTML = '';
    $supportingImagesHTML = '';
    $images = [];
    while ($row = $result->fetch_assoc()) {
        if (!$campaign) {
            $campaign = [
                'id' => $row['id'],
                'campaignTitle' => $row['campaignTitle'],
                'campaignRunBy' => $row['campaignRunBy'],
                'campaignDescription' => $row['campaignDescription'],
                'amountToRaise' => $row['amountToRaise'],
                'tags' => $row['tags'],
                'blockchain_id'=> $blockchainId,
                'user_id' => $row['user_id'],  // Store user_id
                'template_id' => $row['template_id'],
            ];
        }

        // Collect images based on type and add the main image to the images array
        if ($row['image_type'] === 'main') {
            $mainImageHTML = "<img src='{$row['image_url']}' alt='Main Image' class='img-fluid'>";
            $images[] = ['image_url' => $row['image_url'], 'image_type' => 'main'];  // Add main image to the images array
        } else {
            $supportingImagesHTML .= "<img src='{$row['image_url']}' alt='Supporting Image' class='img-thumbnail'>";
            $images[] = ['image_url' => $row['image_url'], 'image_type' => 'supporting'];  // Add supporting images to the array
        }
    }

    $stmt->close();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['disapprove'])) {
        $stmt1 = $conn->prepare("
            INSERT INTO disapproved_campaigns (
                dcampaignTitle, dcampaignRunBy, dcampaignDescription, 
                damountToRaise, dtags, dblockchain_id, 
                duser_id_campaign, dtemplate_id
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
    
        $stmt1->bind_param(
            "sssdssis",
            $campaign['campaignTitle'],
            $campaign['campaignRunBy'],
            $campaign['campaignDescription'],
            $campaign['amountToRaise'],
            $campaign['tags'],
            $campaign['blockchain_id'],
            $campaign['user_id'],
            $campaign['template_id']
        );
    
        $stmt1->execute();
    
        // Get the last inserted ID for approved_campaigns
        $disapproved_campaign_id = $stmt1->insert_id;
    
        // Insert data into the second new table (approved_campaign_images)
        $stmt2 = $conn->prepare("
            INSERT INTO disapproved_campaign_images (
                disapproved_campaign_id, dimage_url, dimage_type
            ) 
            VALUES (?, ?, ?)
        ");
    
        // Insert the main image first
        foreach ($images as $image) {
            $stmt2->bind_param(
                "iss",
                $disapproved_campaign_id,
                $image['image_url'],
                $image['image_type']
            );
            $stmt2->execute();
        }
    
        // Delete the campaign from the campaigns table
        $deleteCampaignStmt = $conn->prepare("DELETE FROM campaigns WHERE id = ?");
        $deleteCampaignStmt->bind_param("i", $campaign['id']);
        $deleteCampaignStmt->execute();

        // Delete the images from the campaign_images table
        $deleteImagesStmt = $conn->prepare("DELETE FROM campaign_images WHERE campaign_id = ?");
        $deleteImagesStmt->bind_param("i", $campaign['id']);
        $deleteImagesStmt->execute();

        // Close the statements
        $deleteCampaignStmt->close();
        $deleteImagesStmt->close();

        // Redirect or display a success message
        echo "<script>alert('Campaign disapproved and data deleted successfully!'); window.location.href='AHome.php';</script>";
        exit();
    }
    $conn->close();
  } else {
        echo "No campaign ID provided.";
        exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Display Campaign</title>
    <script src="ether.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ethers/5.7.2/ethers.umd.min.js"></script>

    <style>
        /* General container styling */
        .container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Campaign title styling */
        h2 {
            font-size: 2rem;
            color: #343a40;
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

        /* Cross button styling */
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

        .approve-btn {
            background-color: #28a745;
            color: white;
            position: absolute;
            top: 10px;
            right: 10px;
            margin-top: 10px;
            margin-right: 140px;
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .disapprove-btn {
            background-color: #dc3545;
            color: white;
            position: absolute;
            top: 10px;
            right: 10px;
            margin-top: 10px;
            margin-right: 10px;
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .approve-btn:hover {
            background-color: #218838;
        }

        .disapprove-btn:hover {
            background-color: #c82333;
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
            
    </style>
    
</head>
<body>
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
    

        // Check if MetaMask is available
        if (typeof window.ethereum !== 'undefined') {
            const provider = new ethers.providers.Web3Provider(window.ethereum);
            const signer = provider.getSigner();
            const contract = new ethers.Contract(contractAddress, contractABI, signer);

            // Event listener for the approve button
            document.addEventListener('DOMContentLoaded', (event) => {
                const approveBtn = document.querySelector('.approve-btn');
                if(approveBtn){
                    approveBtn.addEventListener('click', async (e) => {
                        e.preventDefault(); // Prevent form submission
                        const blockchain_Id = "<?php echo $blockchainId; ?>"; // Get blockchainId from PHP variable
                          
                        // Admin wallet address hardcoded from the contract
                        const adminWallet = "0x0ECb73E8adbF506c986D89Bd016B1f6A9E1882c1";

                        try {
                            // Request MetaMask account access
                            await window.ethereum.request({ method: 'eth_requestAccounts' });

                            const accounts = await window.ethereum.request({ method: 'eth_accounts' });
                            const currentAccount = accounts[0];

                            // Check if the current account is the admin wallet
                            if (currentAccount.toLowerCase() !== adminWallet.toLowerCase()) {
                                alert("You are not authorized to approve this campaign.");
                                return;
                            }

                            // Call the smart contract method
                            const tx = await contract.confirmCampaign(blockchain_Id);
                            console.log("Transaction Sent: ", tx);

                            // Wait for transaction confirmation
                            await tx.wait();
                            console.log("Transaction Confirmed!");

                            // Optionally, you can update the UI or notify the admin of the success
                            alert("Campaign approved successfully!");

                            // Optionally, you can reload or redirect
                            window.location.href = `UserApproval.php?id=${<?php echo $campaign_id; ?>}&blockchain_id=${encodeURIComponent('<?php echo $blockchainId; ?>')}`;
                            
                        } catch (error) {
                            console.error("Error in transaction: ", error);
                            alert("Error approving campaign");
                        }
                    });
                } else {
                    console.error("Approve button not found");
                }
            });
            
            
        } else {
            alert("Please install MetaMask to interact with the blockchain.");
        }
</script>
    <div class="container mt-5">
        <!-- Close button -->
        <button class="close-button" onclick="window.location.href='AHome.php'">&times;</button>
        <!-- Approve and Disapprove buttons -->
        <form method="POST">
            <button type="submit" class="approve-btn" name="approve">Connect Wallet</button>
            <button class="disapprove-btn" name="disapprove" onclick="alert('Campaign disapproved!');">Disapprove</button>
        </form>
        
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
    </div>
</body>
</html>