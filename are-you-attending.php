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

// Fetch current user's details
if (!isset($_SESSION['username']) || !isset($_SESSION['phone'])) {
    $sql = "SELECT username, phone FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        if ($user) {
            $_SESSION['username'] = $user['username'];
            if (!empty($user['phone'])) {
                $_SESSION['phone'] = $user['phone'];
            }
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch unread message count
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

// Handle attendance submission
$attending = isset($_POST['attending']) ? (int)$_POST['attending'] : null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $attending !== null) {
    $eventId = 1; // Hardcoded event ID
    $sql = "INSERT INTO attendance (user_id, event_id, attending) VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE attending = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iiii", $userId, $eventId, $attending, $attending);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Thank you! Your attendance has been recorded as " . ($attending ? "Yes" : "No") . ".";
        } else {
            $message = "Error recording attendance. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch attendance status
$eventId = 1;
$sql = "SELECT attending FROM attendance WHERE user_id = ? AND event_id = ?";
$stmt = mysqli_prepare($conn, $sql);
$attendanceStatus = null;
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $userId, $eventId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $attendance = mysqli_fetch_assoc($result);
    if ($attendance) {
        $attendanceStatus = $attendance['attending'];
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchMingle - Are You Attending</title>
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

        .attending-section {
            flex: 1;
            padding: 3em 0;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .attending-section h2 {
            color: #c32143;
            text-align: center;
            margin-bottom: 1.5em;
            font-family: 'Oswald', sans-serif;
        }

        .attending-form {
            max-width: 500px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .attending-form label {
            font-weight: bold;
            color: #555;
        }

        .attending-form input[type="radio"] {
            margin-right: 10px;
        }

        .attending-form button {
            background-color: #c32143;
            color: #fff;
            border: none;
            padding: 0.5em 1em;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .attending-form button:hover {
            background-color: #f1b458;
            color: #333;
        }

        .message {
            text-align: center;
            color: #c32143;
            margin-top: 10px;
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

        .clearfix {
            clear: both;
        }

        @media (max-width: 768px) {
            .attending-section {
                padding: 1em 0;
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
                        <a class="nav-link" href="register.php">Register Now</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
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
                        <a class="nav-link" href="search-username.php">Search by Username</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="matchright.php">Matchright</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="are-you-attending.php">Are You Attending</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="header">
        <h1>Are You Attending</h1>
    </div>

    <!-- Main Content -->
    <div class="attending-section">
        <h2>RSVP for the Event</h2>
        <form class="attending-form" method="POST" action="are-you-attending.php">
            <div>
                <label>Are you attending the matrimonial event?</label><br>
                <input type="radio" name="attending" value="1" <?php echo $attendanceStatus === 1 ? 'checked' : ''; ?>> Yes<br>
                <input type="radio" name="attending" value="0" <?php echo $attendanceStatus === 0 ? 'checked' : ''; ?>> No
            </div>
            <button type="submit">Submit</button>
        </form>
        <?php if ($message): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
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
</html>S