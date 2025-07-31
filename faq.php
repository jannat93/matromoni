<?php
// Simulate session for navigation
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
    <title>MatchMingle - FAQs</title>
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

        /* FAQ Section */
        .faq-section {
            padding: 3em 0;
            background-color: rgba(255, 255, 255, 0.9);
            text-align: center;
        }

        .faq-section h1 {
            color: #c32143;
            font-size: 2.5em;
            margin-bottom: 1.5em;
            font-family: 'Oswald', sans-serif;
        }

        .accordion {
            max-width: 800px;
            margin: 0 auto;
        }

        .accordion-item {
            border: none;
            margin-bottom: 10px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .accordion-button {
            font-size: 1.1em;
            color: #333;
            background-color: #fff;
            padding: 1.2em;
            border-radius: 8px;
            font-family: 'Ubuntu', sans-serif;
        }

        .accordion-button:not(.collapsed) {
            background-color: #f1b458;
            color: #333;
        }

        .accordion-button:focus {
            box-shadow: none;
        }

        .accordion-body {
            font-size: 1em;
            color: #555;
            padding: 1.5em;
            line-height: 1.6;
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
            .faq-section {
                padding: 2em 0;
            }

            .faq-section h1 {
                font-size: 2em;
            }

            .accordion-button {
                font-size: 1em;
                padding: 1em;
            }

            .accordion-body {
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

    <!-- FAQ Section -->
    <div class="faq-section">
        <h1>Frequently Asked Questions</h1>
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        How do I create a profile on MatchMingle?
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        To create a profile, click on the "Register" link in the navigation menu. Complete the registration form with your personal details, verify your email address, and add information about yourself, such as preferences, education, and lifestyle, to build a comprehensive profile.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        Is MatchMingle free to use?
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        MatchMingle offers free registration, profile creation, and basic search features. Advanced features, such as sending messages, viewing contact details, and accessing premium search filters, require a paid membership. Visit our pricing page for more information.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingThree">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        How does MatchMingle ensure my privacy and security?
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        We use advanced encryption to protect your data and strict verification processes to ensure profile authenticity. You can customize privacy settings to control who sees your profile, photos, and personal details. Our Privacy Policy provides more details.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingFour">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                        What is the difference between Regular Search and Search by Profile ID?
                    </button>
                </h2>
                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Regular Search lets you find matches based on criteria like age, religion, location, and preferences. Search by Profile ID allows you to directly view a specific user’s profile by entering their unique profile ID, which is useful if someone shares their ID with you.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingFive">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                        How do I contact customer support?
                    </button>
                </h2>
                <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Our 24x7 customer support is available via the "Contact Us" page, email, or live chat. You can also submit feedback or queries through the "Feedback" link in the footer. Visit the "Help & Support" section for more options.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingSix">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                        Can I use MatchMingle on my mobile device?
                    </button>
                </h2>
                <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes, MatchMingle is fully mobile-friendly. You can access the website via your mobile browser or download our dedicated app from the Google Play Store or Apple App Store for a seamless experience.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingSeven">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                        What are the benefits of a premium membership?
                    </button>
                </h2>
                <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Premium members enjoy unlimited messaging, access to contact details, advanced search filters, priority profile visibility, and exclusive features like viewing who visited your profile. Check our membership plans for details.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingEight">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
                        Does MatchMingle offer Kundali matching services?
                    </button>
                </h2>
                <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes, MatchMingle provides Kundali matching services for users interested in astrological compatibility. You can access this feature through the "Explore Matrimonial Profiles By" section, including regional options like Tamil, Telugu, and Marathi Kundali matching.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingNine">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNine" aria-expanded="false" aria-controls="collapseNine">
                        How does MatchMingle verify profiles?
                    </button>
                </h2>
                <div id="collapseNine" class="accordion-collapse collapse" aria-labelledby="headingNine" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        We verify profiles through email, phone number, and optional document submission (e.g., ID proof). Our team manually reviews profiles to ensure authenticity, and verified profiles display a badge for trust.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTen">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTen" aria-expanded="false" aria-controls="collapseTen">
                        How can I delete my MatchMingle account?
                    </button>
                </h2>
                <div id="collapseTen" class="accordion-collapse collapse" aria-labelledby="headingTen" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        To delete your account, log in and go to your account settings. Select the "Delete Account" option and follow the prompts. You can also contact customer support for assistance. Note that deletion is permanent and removes all your data.
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
                <p>Copyright © 2025 Marital. All Rights Reserved | Design by <a href="#">Team NBP</a></p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>