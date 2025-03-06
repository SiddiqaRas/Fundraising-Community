<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="Guidestyle.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Guide</title>
    <style>
        body {
    background-color: #f8f9fa; /* Light background for readability */
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
}
.container {
    max-width: 800px;
    margin: 20px auto;
    background-color: #ffffff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    font-family: 'Arial', sans-serif;
    color: #444;
    text-align: justify;
}

h1 {
    text-align: center;
    color: black;
    font-size: 2.5rem;
    margin-bottom: 25px;
    font-weight: bold;
}

ol {
    list-style: decimal inside;
    margin: 0;
    padding: 0;
}

li {
    margin-bottom: 20px;
    font-size: 1.1rem;
    line-height: 1.8;
}

.highlight {
    font-weight: bold;
    color: #black;
}

ul {
    list-style: disc inside;
    margin-top: 10px;
    padding-left: 30px;
}

ul li {
    margin-bottom: 10px;
    font-size: 1rem;
    line-height: 1.6;
}

strong {
    color: #333;
}

body {
    background-color: #f8f9fa; /* Light background for better readability */
    font-family: 'Arial', sans-serif;
}
    </style>
</head>
<body>
    <!-- NAVBAR STARTS -->
    <nav class="navbar sticky-top navbar-expand-lg navbar-dark" style="background-color: black;">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="Home.php">
                <img src="logo.PNG" alt="" width="50" height="40">
                <span class="ms-2">FundRaisingC.</span>
            </a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="Home.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-expanded="false">
                            Campaign
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <li><a class="dropdown-item" href="OngoingC.php">Ongoing Campaign</a></li>
                            <li><a class="dropdown-item" href="CompletedC.php">Completed Campaign</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Guide.php">Guide</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ContactUs.html">Contact Us</a>
                    </li>
                    <li>
                        <div class="search-container">
                            <form id="searchForm" action="Search.php" method="GET">
                                    <input type="text" id="searchInput" placeholder="Search.." style="width: 240px; height: 40px;" name="search">
                                    <button type="submit" style="border: none; position: relative; right: 45px; top: 0; height: 100%; width: 40px; background-color: white; color: black;">
                                        <i class="fa fa-search"></i>
                                    </button>
                            </form>        
                        </div>
                    </li>
                </ul>
            </div>
            <div>
                <a class="btn " href="USignup.php" role="button">Sign Up</a>
                <a class="btn" href="Login.html" role="button">Log In</a>
                
            </div>
        </div>
    </nav>
    <!-- NAVBAR ENDS -->
    <div class="container">
        <h1>How to Create a Campaign</h1>
        <ol>
            <li>
                <span class="highlight">Log In to Your Account:</span> Go to the <strong>Login</strong> page, enter your credentials, and sign in. If you’re not registered, create an account first.
            </li>
            <li>
                <span class="highlight">Access the Home Page:</span> Navigate to the <strong>Create Campaign</strong> button.
            </li>
            <li>
                <span class="highlight">Fill in Campaign Details:</span> Provide all necessary information, including:
                <ul>
                    <li>Campaign Title</li>
                    <li>Campaign Run By</li>
                    <li>Description</li>
                    <li>Target Amount</li>
                </ul>
            </li>
            <li>
                <span class="highlight">Add  Main & Supporting Media:</span> Upload relevant images to make your campaign more appealing.
            </li>
            <li>
                <span class="highlight">Submit:</span> Click the <strong>Submit</strong> button to send your campaign data.
            </li>
            <li>
                <span class="highlight">Select Template:</span> Select a Template and Preview it and submit.
            </li>
            <li>
                <span class="highlight">MetaMask Connection & Transaction:</span> Open Metamask extension in  your browser and choose the wallet you added while signing up .
            </li>
            <li>
                <span class="highlight">Confirm Transaction:</span> Once you click the button it will open up a window and confirm the transaction.
            </li>
            <li>
                <span class="highlight">Sent For Approval:</span> Your Campaign has been send to Admin for Approval.
            </li>
        </ol>
    </div>
    <div class="container">
        <h1>Donate to Campaign</h1>
        <ol>
            <li>
                <span class="highlight">Log In to Your Account:</span> Go to the <strong>Login</strong> page, enter your credentials, and sign in. If you’re not registered, create an account first.
            </li>
            <li>
                <span class="highlight">Access the Home Page:</span> Navigate to the <strong>Campaigns</strong> in navbar.
            </li>
            <li>
                <span class="highlight"> Select Ongoing Campaign:</span> Choose a Campaign you want to donate to.
                
            </li>
            <li>
                <span class="highlight">See Donors, Share or Donate:</span> Data of campaign choosen will be displayed along with buttons.
            </li>
            <li>
                <span class="highlight">Donate:</span> It will open a page you should connect your wallet first through metamask extensin then add amount and submit and you will see the progress.
            </li>
        </ol>
    </div>
    <div>
    
    

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
        document.getElementById("searchInput").addEventListener("focus", function() {
            window.location.href = "Search.php";
        });
    </script>

</body>
</html>
