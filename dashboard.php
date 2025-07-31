<?php
// Start session for navigation
session_start();

// Placeholder for isloggedin() function
function isloggedin() {
    return isset($_SESSION['id']) && !empty($_SESSION['id']);
}

// Redirect to login if not logged in
if (!isloggedin()) {
    header("Location: login.php");
    exit();
}

// Placeholder user data (replace with actual database queries)
$user_id = $_SESSION['id'];
$user_name = "John Doe"; // Example, fetch from database
$profile_completion = 75; // Example percentage
$recent_matches = [
    ["name" => "Jane Smith", "id" => "12345"],
    ["name" => "Michael Brown", "id" => "67890"]
];
$recent_messages = [
    ["sender" => "Jane Smith", "preview" => "Hi, I liked your profile! Let's chat."],
    ["sender" => "Michael Brown", "preview" => "Are you free this weekend?"]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchMingle - Dashboard</title>
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
            left: -250px;
            width: 250px;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            transition: left 0.3s ease;
            z-index: 1000;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar-header {
            padding: 1.5em;
            background-color: #c32143;
            text-align: center;
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

        /* Dashboard Content */
        .dashboard-content {
            margin-left: 0;
            padding: 3em 1em;
            background-color: rgba(255, 255, 255, 0.9);
            min-height: 100vh;
            max-height: 100vh;
            overflow-y: auto;
            transition: margin-left 0.3s ease;
            display: none;
        }

        .dashboard-content.active {
            display: block;
        }

        .dashboard-content h1 {
            color: #c32143;
            font-size: 2.5em;
            margin-bottom: 1em;
            font-family: 'Oswald', sans-serif;
            text-align: center;
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            margin-bottom: 1.5em;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background-color: #f1b458;
            color: #333;
            font-size: 1.2em;
            font-family: 'Ubuntu', sans-serif;
            padding: 1em;
            border-radius: 8px 8px 0 0;
        }

        .card-body {
            padding: 1.5em;
            font-size: 1em;
            color: #555;
        }

        .profile-completion {
            text-align: center;
        }

        .progress {
            height: 20px;
            margin: 1em 0;
            border-radius: 5px;
            background-color: #eee;
        }

        .progress-bar {
            background-color: #c32143;
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
        }

        .btn-action:hover {
            background-color: #f1b458;
            color: #333;
        }

        .list-group-item {
            border: none;
            padding: 0.8em 0;
            font-size: 1em;
        }

        .list-group-item a {
            color: #c32143;
            text-decoration: none;
        }

        .list-group-item a:hover {
            color: #f1b458;
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
                left: -200px;
            }

            .sidebar.active {
                left: 0;
            }

            .dashboard-content {
                padding: 2em 1em;
            }

            .dashboard-content h1 {
                font-size: 2em;
            }

            .card-header {
                font-size: 1.1em;
            }

            .card-body {
                font-size: 0.9em;
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
            <li><a href="userhome.php?id=<?php echo $user_id; ?>"><i class="fa fa-user"></i> Profile</a></li>
            <li><a href="view-profile.php?id=<?php echo $user_id; ?>"><i class="fa fa-eye"></i> View Profile</a></li>
            <li><a href="search.php"><i class="fa fa-search"></i> View Matches</a></li>
            <li><a href="search-id.php"><i class="fa fa-id-card"></i> Search by Profile ID</a></li>
            <li><a href="services.php"><i class="fa fa-cogs"></i> Services</a></li>
            <li><a href="blog.php"><i class="fa fa-rss"></i> Blog</a></li>
            <li><a href="media.php"><i class="fa fa-camera"></i> Media</a></li>
            <li><a href="subscription.php"><i class="fa fa-star"></i> Subscription</a></li>
            <li><a href="contact-admin.php"><i class="fa fa-envelope"></i> Contact Admin</a></li>
            <li><a href="team.php"><i class="fa fa-users"></i> Team</a></li>
            <li><a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Dashboard Content -->
    <div class="dashboard-content" id="dashboardContent">
        <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
        <div class="container">
            <div class="row">
                <!-- Profile Overview -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            Profile Overview
                        </div>
                        <div class="card-body">
                            <div class="profile-completion">
                                <p>Profile Completion: <?php echo $profile_completion; ?>%</p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $profile_completion; ?>%;" aria-valuenow="<?php echo $profile_completion; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <a href="edit-profile.php" class="btn-action"><i class="fa fa-edit"></i> Complete Profile</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Recent Matches -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            Recent Matches
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php foreach ($recent_matches as $match): ?>
                                    <li class="list-group-item">
                                        <a href="profile.php?id=<?php echo htmlspecialchars($match['id']); ?>">
                                            <?php echo htmlspecialchars($match['name']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (empty($recent_matches)): ?>
                                    <li class="list-group-item">No recent matches found.</li>
                                <?php endif; ?>
                            </ul>
                            <a href="search.php" class="btn-action mt-3"><i class="fa fa-search"></i> Find More Matches</a>
                        </div>
                    </div>
                </div>
                <!-- Recent Messages -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            Recent Messages
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php foreach ($recent_messages as $message): ?>
                                    <li class="list-group-item">
                                        <strong><?php echo htmlspecialchars($message['sender']); ?>:</strong>
                                        <?php echo htmlspecialchars($message['preview']); ?>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (empty($recent_messages)): ?>
                                    <li class="list-group-item">No new messages.</li>
                                <?php endif; ?>
                            </ul>
                            <a href="messages.php" class="btn-action mt-3"><i class="fa fa-envelope"></i> View All Messages</a>
                        </div>
                    </div>
                </div>
                <!-- Account Settings -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            Account Settings
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="edit-profile.php" class="btn-action w-100 mb-2"><i class="fa fa-user"></i> Edit Profile</a>
                                </div>
                                <div class="col-md-3">
                                    <a href="change-password.php" class="btn-action w-100 mb-2"><i class="fa fa-lock"></i> Change Password</a>
                                </div>
                                <div class="col-md-3">
                                    <a href="privacy-settings.php" class="btn-action w-100 mb-2"><i class="fa fa-shield"></i> Privacy Settings</a>
                                </div>
                                <div class="col-md-3">
                                    <a href="delete-account.php" class="btn-action w-100 mb-2" style="background-color: #555;"><i class="fa fa-trash"></i> Delete Account</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('dashboardContent').classList.toggle('active');
        }
    </script>
</body>
</html>