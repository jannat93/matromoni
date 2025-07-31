<?php
session_start();
include_once("includes/dbconn.php");
include_once("functions.php");

if (!isloggedin()) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_SESSION['id']) ? $_SESSION['id'] : 0);
$plan = isset($_GET['plan']) ? htmlspecialchars($_GET['plan']) : '';

if ($id <= 0 || !in_array($plan, ['basic', 'premium', 'gold'])) {
    header("Location: subscription.php");
    exit();
}

// Define plan details
$plans = [
    'basic' => ['name' => 'Basic Plan', 'price' => 1098.9],
    'premium' => ['name' => 'Premium Plan', 'price' => 2198.9],
    'gold' => ['name' => 'Gold Plan', 'price' => 3298.9]
];

$planName = $plans[$plan]['name'];
$planPrice = $plans[$plan]['price'];

// Convert USD to BDT (approximate rate as of May 2025, e.g., 1 USD = 110 BDT)
$exchangeRate = 110;
$priceInBDT = $planPrice * $exchangeRate;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bkashNumber = isset($_POST['bkash_number']) ? htmlspecialchars($_POST['bkash_number']) : '';
    $transactionId = isset($_POST['transaction_id']) ? htmlspecialchars($_POST['transaction_id']) : '';

    // Basic validation
    if (empty($bkashNumber) || empty($transactionId)) {
        $error = "Please provide both bKash number and Transaction ID.";
    } elseif (!preg_match('/^01[3-9]\d{8}$/', $bkashNumber)) {
        $error = "Invalid bKash number. It should be a valid 11-digit Bangladeshi mobile number starting with 018-019.";
    } else {
        // Save payment request to database
        $query = "INSERT INTO payment_requests (user_id, plan_type, bkash_number, transaction_id, amount, status) VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('isssd', $id, $plan, $bkashNumber, $transactionId, $priceInBDT);
        if ($stmt->execute()) {
            $success = "Payment request submitted successfully! An admin will review and approve it soon.";
        } else {
            $error = "Failed to submit payment request. Please try again.";
        }
        $stmt->close();
    }
}

$pageTitle = "MatchMingle Payment – $planName";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Google Fonts -->
    <link href='//fonts.googleapis.com/css?family=Oswald:300,400,700' rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=Ubuntu:300,400,500,700' rel='stylesheet' type='text/css'>
    <style>
        body {
            margin: 0;
            font-family: 'Ubuntu', sans-serif;
            background: linear-gradient(45deg, #c32143, #f1b458, #c32143, #f1b458);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            color: #333;
            background-image: url('images/pic1.avif');
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center center;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            transition: left 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
            max-height: calc(100vh - 60px);
        }

        .sidebar.collapsed {
            left: -250px;
        }

        .sidebar-header {
            padding: 1.5em;
            background-color: #c32143;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 1001;
        }

        .sidebar-header h3 {
            color: #fff;
            font-family: 'Oswald', sans-serif;
            margin: 0;
            font-size: 1.5em;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav li {
            border-bottom: 1px solid #444;
        }

        .sidebar-nav li a {
            display: flex;
            align-items: center;
            padding: 1em;
            color: #fff;
            text-decoration: none;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }

        .sidebar-nav li a:hover {
            background-color: #f1b458;
            color: #333;
        }

        .sidebar-nav li a i {
            margin-right: 0.5em;
            font-size: 1.2em;
        }

        /* Toggle Button */
        .toggle-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1100;
            background-color: #c32143;
            color: #fff;
            border: none;
            padding: 0.5em;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .toggle-btn:hover {
            background-color: #f1b458;
        }

        /* Content */
        .content {
            margin-left: 250px;
            padding: 3em 1em;
            background-color: rgba(255, 255, 255, 0.9);
            min-height: 100vh;
            max-height: 100vh;
            overflow-y: auto;
            transition: margin-left 0.3s ease;
        }

        .content.collapsed {
            margin-left: 0;
        }

        .content h1 {
            color: #c32143;
            font-size: 2.5em;
            margin-bottom: 1em;
            font-family: 'Oswald', sans-serif;
            text-align: center;
        }

        .content h2 {
            color: #c32143;
            text-align: center;
            margin-bottom: 1.5em;
            font-family: 'Oswald', sans-serif;
        }

        .payment-form {
            max-width: 500px;
            margin: 0 auto;
            padding: 2em;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .payment-form label {
            display: block;
            margin-bottom: 0.5em;
            font-size: 1.1em;
            color: #333;
        }

        .payment-form input {
            width: 100%;
            padding: 0.8em;
            margin-bottom: 1em;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
        }

        .btn-action {
            background-color: #c32143;
            color: #fff;
            padding: 0.6em 1.2em;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            text-align: center;
        }

        .btn-action:hover {
            background-color: #f1b458;
            color: #333;
        }

        .alert {
            padding: 1em;
            margin-bottom: 1em;
            border-radius: 5px;
            font-size: 1em;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Footer Styles */
        .footer {
            background-color: #333;
            color: #fff;
            padding: 2em 0;
            font-size: 0.9em;
        }

        .footer .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .footer h4 {
            color: #f1b458;
            font-size: 1.2em;
            margin-bottom: 1em;
            font-family: 'Oswald', sans-serif;
        }

        .footer p {
            font-size: 0.9em;
            line-height: 1.6;
            color: #ccc;
        }

        .footer_links,
        .footer_social {
            list-style: none;
            padding: 0;
        }

        .footer_links li,
        .footer_social li {
            margin-bottom: 0.5em;
        }

        .footer_links li a,
        .footer_social li a {
            color: #fff;
            text-decoration: none;
            font-size: 0.9em;
            transition: color 0.3s ease;
        }

        .footer_links li a:hover,
        .footer_social li a:hover {
            color: #f1b458;
        }

        .footer_social .fa {
            font-size: 1.2em;
            margin-right: 0.5em;
        }

        .copy {
            text-align: center;
            margin-top: 2em;
            padding-top: 1em;
            border-top: 1px solid #555;
        }

        .copy p {
            margin: 0;
            color: #ccc;
        }

        .copy a {
            color: #f1b458;
            text-decoration: none;
        }

        .copy a:hover {
            color: #c32143;
        }

        .clearfix {
            clear: both;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
                left: 0;
            }

            .sidebar.collapsed {
                left: -200px;
            }

            .content {
                margin-left: 0;
                padding: 2em 1em;
            }

            .content.collapsed {
                margin-left: 0;
            }

            .content h1, .content h2 {
                font-size: 2em;
            }

            .payment-form {
                padding: 1.5em;
            }

            .btn-action {
                font-size: 0.9em;
                padding: 0.5em 1em;
            }

            .footer .col-md-4,
            .footer .col-md-2 {
                margin-bottom: 1.5em;
                text-align: center;
            }

            .footer_social {
                display: flex;
                justify-content: center;
                gap: 1em;
            }
        }
    </style>
</head>
<body>
    <!-- Toggle Button -->
    <button class="toggle-btn" onclick="toggleSidebar()">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>MatchMingle</h3>
        </div>
        <ul class="sidebar-nav">
            <li><a href="javascript:history.back()"><i class="fa fa-arrow-left"></i> Back</a></li>
            <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="userhome.php?id=<?php echo $id; ?>"><i class="fa fa-user"></i> Profile</a></li>
            <li><a href="view-profile.php?id=<?php echo $id; ?>"><i class="fa fa-eye"></i> View Profile</a></li>
            <li><a href="matchright.php?id=<?php echo $id; ?>"><i class="fa fa-search"></i> View Matches</a></li>
            <li><a href="search-id.php"><i class="fa fa-id-card"></i> Search by Profile ID</a></li>
            <li><a href="services.php"><i class="fa fa-cogs"></i> Services</a></li>
            <li><a href="blog.php?id=<?php echo $id; ?>"><i class="fa fa-rss"></i> Blog</a></li>
            <li><a href="media.php"><i class="fa fa-camera"></i> Media</a></li>
            <li><a href="subscription.php"><i class="fa fa-star"></i> Subscription</a></li>
            <li><a href="dashboard.php"><i class="fa fa-tachometer"></i> Dashboard</a></li>
            <li><a href="contact-admin.php"><i class="fa fa-envelope"></i> Contact Admin</a></li>
            <li><a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Content -->
    <div class="content" id="content">
        <h1><?php echo $pageTitle; ?></h1>
        <div class="container">
            <div class="payment-form">
                <h2>Pay with bKash</h2>
                <p>You have selected the <strong><?php echo $planName; ?></strong>. Amount to pay: <strong><?php echo $priceInBDT; ?> BDT</strong> (approximately $<?php echo $planPrice; ?>).</p>
                <p>Please send the payment to the following bKash number: <strong>01700-123456</strong> (MatchMingle Merchant Account).</p>
                <p>After sending the payment, enter your bKash number and the Transaction ID below. An admin will review and approve your request.</p>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                    <a href="userhome.php?id=<?php echo $id; ?>" class="btn-action">Go to Profile</a>
                <?php elseif (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if (!isset($success)): ?>
                    <form method="POST">
                        <label for="bkash_number">Your bKash Number</label>
                        <input type="text" id="bkash_number" name="bkash_number" placeholder="e.g., 01712345678" required>
                        <label for="transaction_id">Transaction ID</label>
                        <input type="text" id="transaction_id" name="transaction_id" placeholder="Enter Transaction ID" required>
                        <button type="submit" class="btn-action">Submit for Review</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col_2">
                    <h4>About Us</h4>
                    <p>MatchMingle is a trusted matrimony platform helping individuals find meaningful and lasting relationships. We connect verified profiles from diverse backgrounds to make partner search easy, safe, and efficient.</p>
                </div>
                <div class="col-md-2 col_2">
                    <h4>Help & Support</h4>
                    <ul class="footer_links">
                        <li><a href="livehelp.php">24x7 Live help</a></li>
                        <li><a href="contact.php">Contact us</a></li>
                        <li><a href="#">Feedback</a></li>
                        <li><a href="faq.php">FAQs</a></li>
                    </ul>
                </div>
                <div class="col-md-2 col_2">
                    <h4>Quick Links</h4>
                    <ul class="footer_links">
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="terms.php">Terms and Conditions</a></li>
                        <li><a href="services.php">Services</a></li>
                    </ul>
                </div>
                <div class="col-md-2 col_2">
                    <h4>Social</h4>
                    <ul class="footer_social">
                        <li><a href="#"><i class="fa fa-facebook fa1"></i></a></li>
                        <li><a href="#"><i class="fa fa-twitter fa1"></i></a></li>
                        <li><a href="#"><i class="fa fa-google-plus fa1"></i></a></li>
                        <li><a href="#"><i class="fa fa-youtube fa1"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="copy">
                <p>Copyright © 2025 Marital. All Rights Reserved | Design by <a href="#">Team NBP</a></p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('content').classList.toggle('collapsed');
        }
    </script>
</body>
</html>