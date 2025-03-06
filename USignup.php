<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // Extract input values
    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password'];
    $wallet = $data['wallet'];

    // Database credentials
    $servername = "localhost";
    $username = "root";
    $dbpassword = ""; // Database password
    $dbname = "fundraisingcommunity";

    // Connect to the database
    $conn = new mysqli($servername, $username, $dbpassword, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
    }

    // Array to store validation errors
    $errors = [];

    // Validation checks
    // Check for empty fields
    if (empty($name) || empty($email) || empty($password) || empty($wallet)) {
        $errors[] = "Error: All fields are required.";
    }

    // Name validation
    if (!preg_match("/^[a-zA-Z0-9\s]{3,20}$/", $name)) {
        $errors[] = "Error: Name must be between 3 and 20 characters and can only contain letters, numbers, and spaces.";
    }
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Error: Invalid email format.";
    }

    // Password strength validation
    if (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,20}$/", $password)) {
        $errors[] = "Error: Password must be 8-20 characters long, contain at least one uppercase letter, one lowercase letter, one digit, and one special character.";
    }
    

    // Check for duplicate email or wallet
    $sql = "SELECT id FROM users WHERE email = ? OR wallet = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $wallet);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errors[] = "Error: An account with this email or wallet already exists.";
    }

    // If there are validation errors, display them
    if (!empty($errors)) {
        echo json_encode(["errors" => $errors]);
    } else {
        // No errors, proceed with inserting user data

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert user data
        $sql = "INSERT INTO users (name, email, password, wallet) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $hashedPassword, $wallet);

        if ($stmt->execute()) {
            echo json_encode(["success" => "User registered successfully!"]);
            exit;
        } else {
            echo json_encode(["error" => "Error: " . $stmt->error]);
        }
    }

    // Close connections
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="Signup.css" rel="stylesheet">
    <title>Sign Up</title>
    <script src="ether.js"></script>
    
</head>
<body>
    <!-- NAVBAR STARTS -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand d-flex align-items-center" href="Home.php">
            <img src="logo.PNG" alt="" width="50" height="40">
            <span>FundRaisingC.</span>
        </a>
    </nav>
    <!-- NAVBAR ENDS -->

    <!-- SIGNUP FORM STARTS -->
    <div class="login-container">
        <h2>Sign Up</h2>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="user-tab" data-bs-toggle="tab" data-bs-target="#user" type="button" role="tab" aria-controls="user" aria-selected="true">User</button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="user" role="tabpanel" aria-labelledby="user-tab">
                <form id="signupForm" action="USignup.php" method="POST">
                    <div id="userError1" class="alert alert-danger" style="display: none;"></div>
                    <div class="form-group mt-3">
                        <input type="text" class="form-control" placeholder="Enter Name" id="name">
                    </div>
                    <div id="userError2" class="alert alert-danger" style="display: none;"></div>
                    <div class="form-group mt-3">
                        <input type="text" class="form-control" placeholder="Enter Wallet Address" id="wallet">
                    </div>
                    <div id="userError3" class="alert alert-danger" style="display: none;"></div>
                    <div class="form-group mt-3">
                        <input type="email" class="form-control" placeholder="Enter Email Address" id="email">
                    </div>
                    <div id="userError4" class="alert alert-danger" style="display: none;"></div>
                    <div class="form-group mt-3">
                        <input type="password" class="form-control" placeholder="Enter Password" id="password">
                    </div>
                    <div id="userError5" class="alert alert-danger" style="display: none;"></div>
                    <div class="form-group mt-3">
                        <input type="password" class="form-control" placeholder="Confirm Password" id="usercPassword">
                    </div>
                    <div id="userError6" class="alert alert-danger" style="display: none;"></div>
                    <div class="form-group form-check mt-3">
                        <input type="checkbox" class="form-check-input" id="userTerms">
                        <label class="form-check-label" for="userTerms">I agree to the <a href="#">Terms and Conditions</a></label>
                    </div>
                    <button type="button" id="connectWallet">Connect Wallet</button><br>
                    <button type="submit" id="submitUser" class="btn btn-primary mt-3">Sign Up</button>
                    <div class="text-center mt-3">
                        Already have an account? <a href="Login.html">Log In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- SIGNUP FORM ENDS -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Validation Regular Expressions
    const nameRegex = /^[a-zA-Z0-9\s]{3,20}$/;
    const walletRegex = /^0x[a-fA-F0-9]{40}$/;
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    const passwordRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,20}$/;

    // Show Error Message
    function showErrorMessage(elementId, message, inputId) {
        const errorElement = document.getElementById(elementId);
        errorElement.style.display = 'block';
        errorElement.textContent = message;

        const inputElement = document.getElementById(inputId);
        inputElement.classList.add('error-border');
    }

    // Hide All Error Messages
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

    // Form Validation
    function validateUserForm() {
        hideAllErrorMessages();

        const form = document.getElementById('signupForm');
        const name = form.querySelector('#name').value.trim();
        const wallet = form.querySelector('#wallet').value.trim();
        const email = form.querySelector('#email').value.trim();
        const password = form.querySelector('#password').value;
        const confirmPassword = form.querySelector('#usercPassword').value;
        const termsChecked = form.querySelector('#userTerms').checked;

        let isValid = true;

        if (name === "") {
            showErrorMessage('userError1', 'Name cannot be empty.', 'name');
            isValid = false;
        } else if (!nameRegex.test(name)) {
            showErrorMessage('userError1', 'Name must be between 3 and 20 characters and can only contain letters, numbers, and spaces.', 'name');
            isValid = false;
        }
        if (wallet === "") {
            showErrorMessage('userError2', 'Wallet address cannot be empty.', 'wallet');
            isValid = false;
        } else if (!walletRegex.test(wallet)) {
            showErrorMessage('userError2', 'Invalid Ethereum wallet address.', 'wallet');
            isValid = false;
        }
        if (email === "") {
            showErrorMessage('userError3', 'Email address cannot be empty.', 'email');
            isValid = false;
        } else if (!emailRegex.test(email)) {
            showErrorMessage('userError3', 'Invalid email address.', 'email');
            isValid = false;
        }
        if (password === "") {
            showErrorMessage('userError4', 'Password cannot be empty.', 'password');
            isValid = false;
        } else if (!passwordRegex.test(password)) {
            showErrorMessage('userError4', 'Password must be 8-20 characters long, contain at least one uppercase letter, one lowercase letter, one digit, and one special character.', 'password');
            isValid = false;
        }
        if (confirmPassword === "") {
            showErrorMessage('userError5', 'Confirm password cannot be empty.', 'usercPassword');
            isValid = false;
        } else if (password !== confirmPassword) {
            showErrorMessage('userError5', 'Passwords do not match.', 'usercPassword');
            isValid = false;
        }
        if (!termsChecked) {
            showErrorMessage('userError6', 'You must agree to the Terms and Conditions.', 'userTerms');
            isValid = false;
        }

        return isValid;
    }

    // MetaMask Wallet Connection
    document.getElementById("connectWallet").addEventListener("click", async () => {
        if (window.ethereum) {
            try {
                const accounts = await ethereum.request({ method: "eth_requestAccounts" });
                document.getElementById("wallet").value = accounts[0];
                console.log("Connected wallet:", accounts[0]);
            } catch (error) {
                console.error("Error connecting wallet:", error);
                alert("Failed to connect wallet.");
            }
        } else {
            alert("MetaMask is not installed.");
        }
    });

    const rpcURL = "HTTP://127.0.0.1:7545"; // Ganache RPC URL
    const contractAddress = "0x2AD02fc06A5fe9035342D3Cc73f127FDC4cd2dD8"; // Replace with your deployed contract address
    const abi = [
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "user",
				"type": "address"
			}
		],
		"name": "registerUser",
		"outputs": [],
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"inputs": [
			{
				"internalType": "address",
				"name": "user",
				"type": "address"
			}
		],
		"name": "isRegistered",
		"outputs": [
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


    // Handle Signup Form Submission
    document.getElementById("signupForm").addEventListener("submit", async (e) => {
        e.preventDefault();

        if (!validateUserForm()) return;

        const name = document.getElementById("name").value;
        const email = document.getElementById("email").value;
        const password = document.getElementById("password").value;
        const wallet = document.getElementById("wallet").value;

        if (!wallet) {
            alert("Please connect your wallet first.");
            return;
        }

        const isRegistered = await checkWalletOnChain(wallet);
        if (isRegistered) {
            alert("This wallet is already registered on the blockchain.");
        } else {
            const registrationSuccess = await registerWalletOnChain(wallet);
            if (registrationSuccess) {
                await registerUserToDatabase(name, email, password, wallet);
            }
        }
    });

    async function checkWalletOnChain(wallet) {
        const provider = new ethers.providers.JsonRpcProvider(rpcURL);
        const contract = new ethers.Contract(contractAddress, abi, provider);
        return await contract.isRegistered(wallet);
    }

    async function registerWalletOnChain(wallet) {
        const provider = new ethers.providers.JsonRpcProvider(rpcURL);
        const signer = provider.getSigner();
        const contract = new ethers.Contract(contractAddress, abi, signer);

        try {
            const tx = await contract.registerUser(wallet);
            await tx.wait();
            console.log("Transaction successful:", tx);
            return true;
        } catch (error) {
            console.error("Error during registration:", error);
            return false;
        }
    }

    async function registerUserToDatabase(name, email, password, wallet) {
        const requestData = { name, email, password, wallet };

        try {
            const response = await fetch("USignup.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(requestData),
            });
            const data = await response.text();
            alert(data);
        } catch (error) {
            console.error("Error registering user in database:", error);
        }
    }
</script>

    
    
</body>
</html>

