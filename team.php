<?php
session_start();
include_once("includes/dbconn.php");
include_once("functions.php");

// Check if user is logged in
if (!isloggedin()) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['id'];

// Fetch unread message count (for navbar badge)
$sql = "SELECT COUNT(*) as unread_count FROM massage WHERE receiver_id = ? AND is_read = 0";
$stmt = mysqli_prepare($conn, $sql);
$unreadCount = 0;
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $unread = mysqli_fetch_assoc($result);
    $unreadCount = $unread['unread_count'];
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchMingle - Our Team</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href='//fonts.googleapis.com/css?family=Oswald:300,400,700' rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=Ubuntu:300,400,500,700' rel='stylesheet' type='text/css'>
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            background: linear-gradient(135deg, #fff5e1, #ffcccc, #ffd700); /* Wedding-inspired: cream, rose, gold */
            background-size: 150% 150%; /* Reduced size to minimize glitching */
            animation: weddingGradient 100s ease infinite; /* Slower animation for smoothness */
            color: #333;
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center center;
            padding-top: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        @keyframes weddingGradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 50% 50%; } /* Reduced range to reduce flicker */
            100% { background-position: 0% 50%; }
        }

        .navbar {
            background-color: rgba(0, 0, 0, 0.6);
            width: 100%;
        }

        .navbar-brand {
            font-family: 'Oswald', sans-serif;
            font-size: 1.8em;
            color: #ffd700 !important;
            transition: color 0.3s ease;
        }

        .navbar-brand:hover {
            color: #ffcccc !important;
        }

        .navbar-nav .nav-link {
            color: #fff !important;
            font-size: 1.1em;
            margin-left: 1em;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #ffd700 !important;
        }

        .navbar-nav .nav-link.active {
            color: #ffd700 !important;
            font-weight: bold;
        }

        .navbar-toggler {
            border-color: #ffd700;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 215, 0, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .header {
            background-color: rgba(0, 0, 0, 0.6);
            color: #fff;
            padding: 2em 0;
            text-align: center;
            width: 100%;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 0.5em;
            font-family: 'Oswald', sans-serif;
        }

        .team-section {
            flex: 1;
            padding: 3em 0;
            background-color: rgba(255, 245, 225, 0.9);
        }

        .team-title {
            color: rgb(244, 23, 177);
            font-family: 'Oswald', sans-serif;
            text-align: center;
            margin-bottom: 2em;
            font-size: 2em;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            padding: 0 15px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .team-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .team-card:hover {
            transform: scale(1.05);
        }

        .team-card img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1em;
            border: 3px solid #ffd700;
        }

        .team-card h4 {
            color: rgb(234, 8, 167);
            font-size: 1.5em;
            margin-bottom: 0.5em;
        }

        .team-card p {
            color: #555;
            font-size: 1em;
            line-height: 1.5;
        }

        .footer {
            background-color: #333;
            color: #fff;
            padding: 2em 0;
            font-size: 0.9em;
            width: 100%;
            margin-top: auto;
        }

        .footer .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .footer h4 {
            color: #ffd700;
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
            color: #ffd700;
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
            color: #ffd700;
            text-decoration: none;
        }

        .copy a:hover {
            color: rgb(206, 22, 120);
        }

        @media (max-width: 768px) {
            .team-grid {
                grid-template-columns: 1fr;
            }
            .header h1 {
                font-size: 2em;
            }
            .team-card img {
                width: 120px;
                height: 120px;
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
                <ul class="navbar-nav">
                    
                    
                    <li class="nav-item">
                        <a class="nav-link" href="blog.php">View Blog</a>
                    </li>
                    
                    
                    <li class="nav-item">
                        <a class="nav-link" href="media.php">Media</a>
                    </li>
                   <li class="nav-item">
                         <a class="nav-link" href="javascript:history.back()"></i> Back</a>
                    </li>
                
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="header">
        <h1>Our Team</h1>
    </div>

    <!-- Team Section -->
    <div class="team-section">
        <h2 class="team-title">Meet the MatchMingle Team</h2>
        <div class="team-grid">
            <div class="team-card">
                <img src="nurr.jpg" alt="Nusura Nur Nowrin smiling confidently in a professional setting with a warm and inviting atmosphere suggesting leadership and innovation" width="150" height="150" onerror="this.src='images/nurr.jpg';">
                <h4>Nusura Nur Nowrin</h4>
                <p>Role: Founder & CEO<br>Experience: 2+ years in matchmaking and tech innovation<br>Passion: Creating lasting love stories with cutting-edge technology and a personal touch.</p>
            </div>
            <div class="team-card">
                <img src="biva.jpg" alt="Sohain Tabassum Biva smiling warmly in a professional setting with a soft background suggesting a collaborative and welcoming team environment" width="150" height="150" onerror="this.src='images/biva.jpg';">
                <h4>Sohain Tabassum Biva</h4>
                <p>Role:Founder & Customer Success Lead<br>Experience: 2 years in client relations and event planning<br>Passion: Ensuring every user finds their perfect match with heartfelt support.</p>
            </div>
            <div class="team-card">
            <img src="girl1.jpeg" alt="Admin smiling professionally in a structured office environment, symbolizing authority and support" width="150" height="150" onerror="this.src='/matrimonial1/images/girl1.jpeg';">
            <h4>Suma</h4>
            <p>Role: System Administrator<br>Experience: 1 years in IT and system management<br>Passion: Maintaining a secure and efficient platform for all users.</p>
        </div>
        </div>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>