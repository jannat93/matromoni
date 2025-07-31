<?php
session_start();
include_once("includes/dbconn.php");
include_once("functions.php");

// Check if user is logged in
if (!isloggedin()) {
    error_log("User not logged in, redirecting to login.php");
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['id'];

// Fetch current user's phone number and username if not in session
if (!isset($_SESSION['phone']) || !isset($_SESSION['username'])) {
    $sql = "SELECT phone, username FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        if ($user) {
            if (!empty($user['phone'])) {
                $_SESSION['phone'] = $user['phone'];
            }
            if (!empty($user['username'])) {
                $_SESSION['username'] = $user['username'];
            }
        } else {
            error_log("No user found for ID: " . $userId);
            header("Location: login.php"); // Redirect if user not found
            exit();
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Prepare failed for phone/username fetch: " . mysqli_error($conn));
    }
}


// Fetch unread message count
$sql = "SELECT COUNT(*) as unread_count FROM massage WHERE receiver_id = ? AND is_read = 0";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    error_log("Prepare failed for unread messages: " . mysqli_error($conn));
    $unreadCount = 0;
} else {
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $unread = mysqli_fetch_assoc($result);
    $unreadCount = $unread['unread_count'];
    mysqli_stmt_close($stmt);
}

// Handle search
$searchUsername = isset($_GET['username']) ? trim($_GET['username']) : '';
$searchedUser = null;

if (!empty($searchUsername) && $searchUsername != $_SESSION['username']) {
    $sql = "SELECT id, username, photo, birth_date, religion, gender 
            FROM users WHERE username = ? AND id != ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $searchUsername, $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $searchedUser = mysqli_fetch_assoc($result);
        if ($searchedUser) {
            $searchedUser['age'] = date_diff(date_create($searchedUser['birth_date']), date_create('today'))->y;
        } else {
            error_log("No user found for username: " . $searchUsername);
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Prepare failed for search: " . mysqli_error($conn));
    }
}

// Twilio notification (disabled for now due to potential undefined $twilio)
/*
if ($searchedUser && isset($twilio)) {
    $sql = "SELECT phone FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $searchedUser['id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $targetUser = mysqli_fetch_assoc($result);
        if ($targetUser && !empty($targetUser['phone'])) {
            try {
                $twilio->messages->create(
                    $targetUser['phone'],
                    [
                        'from' => TWILIO_PHONE_NUMBER,
                        'body' => "Someone viewed your profile on MatchMingle! Check your messages."
                    ]
                );
                error_log("Profile view notification sent for user ID " . $searchedUser['id']);
            } catch (Exception $e) {
                error_log("Failed to send Twilio notification: " . $e->getMessage());
            }
        }
        mysqli_stmt_close($stmt);
    }
}
*/

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchMingle - Search by Username</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href='//fonts.googleapis.com/css?family=Oswald:300,400,700' rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=Ubuntu:300,400,500,700' rel='stylesheet' type='text/css'>
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            background: linear-gradient(45deg, #c32143, #f1b458, #c32143, #f1b458);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            color: #333;
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center center;
            padding-top: 0;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

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
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 0.5em;
            font-family: 'Oswald', sans-serif;
        }

        .search-section {
            padding: 3em 0;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .search-section h2 {
            color: #c32143;
            text-align: center;
            margin-bottom: 1.5em;
            font-family: 'Oswald', sans-serif;
        }

        .search-form {
            max-width: 500px;
            margin: 0 auto 2em;
            display: flex;
            gap: 10px;
        }

        .search-form input {
            flex-grow: 1;
            padding: 0.5em;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .search-form button {
            background-color: #c32143;
            color: #fff;
            border: none;
            padding: 0.5em 1em;
            border-radius: 5px;
            cursor: pointer;
        }

        .search-form button:hover {
            background-color: #f1b458;
            color: #333;
        }

        .profile-card {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }

        .profile-card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1em;
        }

        .profile-card h3 {
            color: #c32143;
            font-size: 1.6em;
            margin-bottom: 0.5em;
        }

        .profile-card p {
            color: #555;
            font-size: 1.1em;
            margin-bottom: 0.5em;
        }

        .profile-card .btn {
            background-color: #c32143;
            color: #fff;
            border: none;
            padding: 0.5em 1em;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 10px;
        }

        .profile-card .btn:hover {
            background-color: #f1b458;
            color: #333;
        }

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

        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
                gap: 15px;
            }

            .search-form input {
                width: 100%;
            }

            .profile-card {
                padding: 15px;
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
                        <a class="nav-link" href="messages.php">
                            Messages
                            <?php if ($unreadCount > 0): ?>
                                <span class="notification-badge"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="matchright.php">Browse Profiles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="search-username.php">Search by Username</a>
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
        <h1>Search by Username</h1>
    </div>

    <!-- Search Section -->
    <div class="search-section">
        <div class="container">
            <h2>Find a Profile</h2>
            <form class="search-form" method="GET" action="search-id.php">
                <input type="text" name="username" placeholder="Enter Username" value="<?php echo htmlspecialchars($searchUsername); ?>" required>
                <button type="submit">Search</button>
            </form>

            <?php if (!empty($searchUsername)): ?>
                <?php if ($searchedUser): ?>
                    <div class="profile-card">
                        <img src="<?php echo !empty($searchedUser['photo']) ? 'uploads/profiles/' . htmlspecialchars($searchedUser['photo']) : 'images/default_profile.jpg'; ?>" alt="Profile">
                        <h3><?php echo htmlspecialchars($searchedUser['username']); ?>, <?php echo $searchedUser['age']; ?></h3>
                        <p>Gender: <?php echo htmlspecialchars($searchedUser['gender']); ?></p>
                        <p>Religion: <?php echo htmlspecialchars($searchedUser['religion']); ?></p>
                        <a href="profile.php?username=<?php echo $searchedUser['username']; ?>" class="btn" onclick="return confirm('View full profile?')">View Full Profile</a>
                        <a href="message.php?username=<?php echo $searchedUser['username']; ?>" class="btn" onclick="return confirm('Send a message?')">Message</a>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #555;">No user found with username '<?php echo htmlspecialchars($searchUsername); ?>'.</p>
                <?php endif; ?>
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