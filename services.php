<?php
// Start session for user authentication
session_start();
// Placeholder for isloggedin() function
function isloggedin() {
    return isset($_SESSION['id']) && !empty($_SESSION['id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explore MatchMingle's services to find your perfect life partner with free and premium features, profile verification, and personalized matchmaking.">
    <title>Services - MatchMingle</title>
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

        /* Navigation Bar */
        .navbar {
            background-color: rgba(0, 0, 0, 0.7);
            font-family: 'Ubuntu', sans-serif;
        }

        .navbar-brand {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8em;
            color: rgb(240, 63, 23) !important;
            transition: color 0.3s ease;
        }

        .navbar-brand:hover {
            color: #c32143 !important;
        }

        .navbar-nav .nav-link {
            color: #fff !important;
            font-size: 1.1em;
            margin-left: 1em;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #f1b458 !important;
        }

        .navbar-toggler {
            border-color: #f1b458;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(241, 180, 88, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Dropdown Styles */
        .dropdown-menu {
            background-color: #333;
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .dropdown-menu li a {
            color: #fff;
            padding: 10px 20px;
            font-size: 1em;
        }

        .dropdown-menu li a:hover {
            background-color: #f1b458;
            color: #333;
        }

        /* Services Section */
        .services-section {
            padding: 3em 0;
            background-color: rgba(255, 255, 255, 0.9);
            text-align: center;
        }

        .services-section h1 {
            color: #c32143;
            font-size: 2.5em;
            margin-bottom: 1.5em;
            font-family: 'Oswald', sans-serif;
        }

        .service-card {
            border: 2px solid transparent;
            margin-bottom: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            border-color: #f1b458;
        }

        .service-card .card-body {
            padding: 2em;
        }

        .service-card .card-title {
            font-size: 1.3em;
            color: #333;
            font-family: 'Ubuntu', sans-serif;
            margin-bottom: 0.8em;
        }

        .service-card .card-text {
            font-size: 1em;
            color: #555;
            line-height: 1.6;
        }

        /* Distinct colors for each service card */
        .service-card:nth-child(1) {
            background-color: #ffe6e6; /* Light red for Free Membership */
            border-color: #c32143;
        }

        .service-card:nth-child(2) {
            background-color: #fff5e6; /* Light yellow for Premium Membership */
            border-color: #f1b458;
        }

        .service-card:nth-child(3) {
            background-color: #e6f0ff; /* Light blue for Profile Verification */
            border-color: #4a90e2;
        }

        .service-card:nth-child(4) {
            background-color: #e6ffe6; /* Light green for Personalized Matchmaking */
            border-color: #2ecc71;
        }

        .service-card:nth-child(5) {
            background-color: #f0e6ff; /* Light purple for Privacy & Security */
            border-color: #9b59b6;
        }

        .service-card:nth-child(6) {
            background-color: #ffe6f0; /* Light pink for Mobile App Access */
            border-color: #e91e63;
        }

        .btn-custom {
            background-color: #c32143;
            color: #fff;
            border: none;
            padding: 0.8em 1.5em;
            border-radius: 5px;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #f1b458;
            color: #333;
        }

        /* Back Button Container and Styles */
        .back-button-container {
            text-align: center;
            padding: 2em 0;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            padding: 0.8em 1.5em;
            background-color: #c32143;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #f1b458;
            color: #333;
        }

        .back-button i {
            margin-right: 0.5em;
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .services-section {
                padding: 2em 0;
            }

            .services-section h1 {
                font-size: 2em;
            }

            .service-card .card-title {
                font-size: 1.1em;
            }

            .service-card .card-text {
                font-size: 0.9em;
            }

            .back-button-container {
                padding: 1.5em 0;
            }

            .back-button {
                font-size: 0.9em;
                padding: 0.6em 1.2em;
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

            .navbar-nav {
                text-align: center;
            }

            .navbar-nav .nav-link {
                margin: 0.5em 0;
            }

            .dropdown-menu {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">MatchMingle</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="nav navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fa fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php"><i class="fa fa-info-circle"></i> About</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-search"></i> Search
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="search.php">Regular Search</a></li>
                            <li><a class="dropdown-item" href="search-id.php">Search By Profile ID</a></li>
                            <li><a class="dropdown-item" href="faq.php">Faq</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php"><i class="fa fa-envelope"></i> Contacts</a>
                    </li>
                    <?php 
                    if (isloggedin()) {
                        $id = $_SESSION['id'];
                        echo "<li class='nav-item'><a class='nav-link' href='userhome.php?id=$id'><i class='fa fa-user'></i> Profile</a></li>";
                        echo "<li class='nav-item'><a class='nav-link' href='logout.php'><i class='fa fa-sign-out'></i> Logout</a></li>";
                    } else {
                        echo "<li class='nav-item'><a class='nav-link' href='login.php'><i class='fa fa-sign-in'></i> Login</a></li>";
                        echo "<li class='nav-item'><a class='nav-link' href='register.php'><i class='fa fa-user-plus'></i> Register</a></li>";
                    }
                    ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Services Section -->
    <div class="services-section">
        <h1>Our Services</h1>
        <div class="container">
            <div class="row g-4">
                <!-- Service 1: Free Membership -->
                <div class="col-md-4">
                    <div class="card service-card">
                        <div class="card-body">
                            <h5 class="card-title">Free Membership</h5>
                            <p class="card-text">Sign up, create a profile, and browse matches based on preferences like age, religion, and location at no cost.</p>
                            <a href="register.php" class="btn btn-custom">Join Now</a>
                        </div>
                    </div>
                </div>
                <!-- Service 2: Premium Membership -->
                <div class="col-md-4">
                    <div class="card service-card">
                        <div class="card-body">
                            <h5 class="card-title">Premium Membership</h5>
                            <p class="card-text">Access exclusive features like messaging, contact details, and priority matches with a premium plan.</p>
                            <a href="pricing.php" class="btn btn-custom">Upgrade Now</a>
                        </div>
                    </div>
                </div>
                <!-- Service 3: Profile Verification -->
                <div class="col-md-4">
                    <div class="card service-card">
                        <div class="card-body">
                            <h5 class="card-title">Profile Verification</h5>
                            <p class="card-text">Build trust with our ID and social media verification process to ensure authentic profiles.</p>
                            <a href="verification.php" class="btn btn-custom">Learn More</a>
                        </div>
                    </div>
                </div>
                <!-- Service 4: Personalized Matchmaking -->
                <div class="col-md-4">
                    <div class="card service-card">
                        <div class="card-body">
                            <h5 class="card-title">Personalized Matchmaking</h5>
                            <p class="card-text">Receive curated match suggestions based on your lifestyle, values, and preferences.</p>
                            <a href="matchmaking.php" class="btn btn-custom">Get Started</a>
                        </div>
                    </div>
                </div>
                <!-- Service 5: Privacy and Security -->
                <div class="col-md-4">
                    <div class="card service-card">
                        <div class="card-body">
                            <h5 class="card-title">Privacy & Security</h5>
                            <p class="card-text">Your data is protected with encryption and strict privacy policies for a safe experience.</p>
                            <a href="privacy.php" class="btn btn-custom">Read More</a>
                        </div>
                    </div>
                </div>
                <!-- Service 6: Mobile App Access -->
                <div class="col-md-4">
                    <div class="card service-card">
                        <div class="card-body">
                            <h5 class="card-title">Mobile App Access</h5>
                            <p class="card-text">Connect with matches anytime, anywhere via our Android and iOS mobile app.</p>
                            <a href="download.php" class="btn btn-custom">Download Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button Before Footer -->
    <div class="back-button-container">
        <button class="back-button" onclick="window.history.back()">
            <i class="fa fa-arrow-left"></i> Back
        </button>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col_2">
                    <h4>About Us</h4>
                    <p>MatchMingle is a trusted matrimony platform helping individuals find meaningful and lasting relationships. We connect verified profiles from diverse backgrounds to make partner search easy, safe, and efficient. Our goal is to simplify matchmaking using smart tools and a secure environment. Whether you're looking for love or a life partner, we're here to support your journey. Join MatchMingle — where meaningful connections begin.</p>
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
                <p>Copyright © <?php echo date("Y"); ?> Marital. All Rights Reserved | Design by <a href="#">Team NBP</a></p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>