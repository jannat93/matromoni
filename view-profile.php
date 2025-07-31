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
$profileId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch profile details
$profile = null;
if ($profileId > 0) {
    $sql = "SELECT id, username, email, birth_date, gender, status, created_at, religion, photo, 
            maritalstatus, caste, district, state, country, education, occupation, aboutme, 
            plan_type, subscription_status, subscription_expiry, is_admin, swap_requests_sent, phone 
            FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $profileId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $profile = mysqli_fetch_assoc($result);
        if ($profile) {
            $profile['age'] = date_diff(date_create($profile['birth_date']), date_create('today'))->y;
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchMingle - View Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href='//fonts.googleapis.com/css?family=Oswald:300,400,700' rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=Ubuntu:300,400,500,700' rel='stylesheet' type='text/css'>
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            background: linear-gradient(45deg, #c32143, #f1b458, #c32143, #f1b458);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .navbar {
            background-color: rgba(0, 0, 0, 0.7);
            width: 100%;
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

        .navbar-nav .nav-link.active {
            color: #f1b458 !important;
            font-weight: bold;
        }

        .navbar-toggler {
            border-color: #f1b458;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(241, 180, 88, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        .header {
            background-color: rgba(0, 0, 0, 0.7);
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

        .profile-section {
            flex: 1;
            padding: 3em 0;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .profile-card {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2.5em;
        }

        .profile-header img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #f1b458;
            margin-bottom: 1em;
        }

        .profile-header h2 {
            color: #c32143;
            font-family: 'Oswald', sans-serif;
            font-size: 2em;
            margin-bottom: 0.5em;
        }

        .profile-header .btn {
            background-color: #c32143;
            color: #fff;
            border: none;
            padding: 0.6em 1.8em;
            border-radius: 5px;
            text-decoration: none;
            margin: 0 10px;
            font-size: 1em;
        }

        .profile-header .btn:hover {
            background-color: #f1b458;
            color: #333;
        }

        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2em;
        }

        .profile-section-card {
            background: #fafafa;
            padding: 1.5em;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .profile-section-card h3 {
            color: #c32143;
            font-family: 'Oswald', sans-serif;
            font-size: 1.5em;
            margin-bottom: 1.2em;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5em;
        }

        .profile-section-card p {
            margin: 0.8em 0;
            font-size: 1em;
            color: #555;
            display: flex;
            align-items: center;
            line-height: 1.4;
        }

        .profile-section-card p i {
            margin-right: 0.8em;
            color: #f1b458;
            width: 1.2em;
            text-align: center;
        }

        .profile-section-card p span {
            font-weight: 500;
            color: #333;
            margin-right: 0.5em;
            min-width: 100px;
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

        @media (max-width: 768px) {
            .profile-card {
                padding: 20px;
            }

            .profile-header h2 {
                font-size: 1.5em;
            }

            .profile-details {
                grid-template-columns: 1fr;
            }

            .profile-section-card h3 {
                font-size: 1.3em;
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
                        <a class="nav-link" href="messages.php">Messages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="matchright.php">Browse Profiles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="search-username.php">Search by Username</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="are-you-attending.php">Are You Attending</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                    <li class="nav-item">
                         <a class="nav-link" href="javascript:history.back()"></i> Back</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="header">
        <h1>View Profile</h1>
    </div>

    <!-- Profile Section -->
    <div class="profile-section">
        <div class="profile-card">
            <?php if ($profile): ?>
                <div class="profile-header">
                    <img src="<?php echo !empty($profile['photo']) ? 'uploads/profiles/' . htmlspecialchars($profile['photo']) : 'images/default_profile.jpg'; ?>" alt="Profile Photo">
                    <h2><?php echo htmlspecialchars($profile['username']); ?>, <?php echo $profile['age']; ?></h2>
                    <a href="message.php?id=<?php echo $profileId; ?>" class="btn" onclick="return confirm('Send a message?')">Message</a>
                    <?php if ($userId == $profileId): ?>
                        <a href="editprofile.php" class="btn">Edit Profile</a>
                    <?php endif; ?>
                </div>
                <div class="profile-details">
                    <!-- Basic Info -->
                    <div class="profile-section-card">
                        <h3>Basic Info</h3>
                        <p><i class="fas fa-user"></i> <span>Username:</span> <?php echo htmlspecialchars($profile['username']); ?></p>
                        <p><i class="fas fa-venus-mars"></i> <span>Gender:</span> <?php echo htmlspecialchars($profile['gender'] ?? 'Not provided'); ?></p>
                        <p><i class="fas fa-calendar-alt"></i> <span>Age:</span> <?php echo $profile['age']; ?> years</p>
                        <p><i class="fas fa-ring"></i> <span>Marital Status:</span> <?php echo htmlspecialchars($profile['maritalstatus'] ?? 'Not provided'); ?></p>
                        <p><i class="fas fa-pray"></i> <span>Religion:</span> <?php echo htmlspecialchars($profile['religion'] ?? 'Not provided'); ?></p>
                        <p><i class="fas fa-users"></i> <span>Caste:</span> <?php echo htmlspecialchars($profile['caste'] ?? 'Not provided'); ?></p>
                    </div>
                    <!-- Personal Details -->
                    <div class="profile-section-card">
                        <h3>Personal Details</h3>
                        <p><i class="fas fa-briefcase"></i> <span>Occupation:</span> <?php echo htmlspecialchars($profile['occupation'] ?? 'Not provided'); ?></p>
                        <p><i class="fas fa-graduation-cap"></i> <span>Education:</span> <?php echo htmlspecialchars($profile['education'] ?? 'Not provided'); ?></p>
                        <p><i class="fas fa-info-circle"></i> <span>About Me:</span> <?php echo htmlspecialchars($profile['aboutme'] ?? 'Not provided'); ?></p>
                    </div>
                    <!-- Contact Info -->
                    <div class="profile-section-card">
                        <h3>Contact Info</h3>
                        <p><i class="fas fa-envelope"></i> <span>Email:</span> <?php echo htmlspecialchars($profile['email'] ?? 'Not provided'); ?></p>
                        <p><i class="fas fa-phone"></i> <span>Phone:</span> <?php echo htmlspecialchars($profile['phone'] ?? 'Not provided'); ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <span>Location:</span> 
                            <?php 
                            $location = array_filter([
                                $profile['district'] ?? null,
                                $profile['state'] ?? null,
                                $profile['country'] ?? null
                            ]);
                            echo htmlspecialchars(!empty($location) ? implode(', ', $location) : 'Not provided');
                            ?>
                        </p>
                    </div>
                    <!-- Account Info -->
                    <div class="profile-section-card">
                        <h3>Account Info</h3>
                        <p><i class="fas fa-id-card"></i> <span>User ID:</span> <?php echo htmlspecialchars($profile['id']); ?></p>
                        <p><i class="fas fa-user-check"></i> <span>Status:</span> <?php echo htmlspecialchars($profile['status'] ?? 'Not provided'); ?></p>
                        <p><i class="fas fa-calendar-plus"></i> <span>Joined:</span> <?php echo htmlspecialchars(date('F j, Y', strtotime($profile['created_at']))); ?></p>
                        <p><i class="fas fa-star"></i> <span>Plan Type:</span> <?php echo htmlspecialchars($profile['plan_type'] ?? 'Not provided'); ?></p>
                        <p><i class="fas fa-clock"></i> <span>Subscription Status:</span> <?php echo htmlspecialchars($profile['subscription_status'] ?? 'Not provided'); ?></p>
                        <?php if ($profile['subscription_expiry']): ?>
                            <p><i class="fas fa-calendar-times"></i> <span>Subscription Expiry:</span> <?php echo htmlspecialchars(date('F j, Y', strtotime($profile['subscription_expiry']))); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-center">No profile found for ID <?php echo $profileId; ?>.</p>
            <?php endif; ?>
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
                        <li><a href="#"><i class="fab fa-facebook-f fa1"></i></a></li>
                        <li><a href="#"><i class="fab fa-twitter fa1"></i></a></li>
                        <li><a href="#"><i class="fab fa-google-plus-g fa1"></i></a></li>
                        <li><a href="#"><i class="fab fa-youtube fa1"></i></a></li>
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