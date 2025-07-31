<?php
// matches.php
session_start();
include_once("includes/dbconn.php");
include_once("functions.php");

if (!isloggedin()) {
    error_log("User not logged in, redirecting to login.php");
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['id'];

// Fetch unread message count
$sql = "SELECT COUNT(*) as unread_count FROM massage WHERE receiver_id = ? AND is_read = 0";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$unread = mysqli_fetch_assoc($result);
$unreadCount = $unread['unread_count'];
mysqli_stmt_close($stmt);

// Fetch matches
$sql = "SELECT u.id, u.username, u.photo
        FROM matches m
        JOIN users u ON (u.id = m.user1_id OR u.id = m.user2_id)
        WHERE (m.user1_id = ? OR m.user2_id = ?) AND u.id != ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iii", $userId, $userId, $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$matches = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchMingle - Matches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href='//fonts.googleapis.com/css?family=Oswald:300,400,700' rel='stylesheet' type='text/css'>
    <link href='//fonts.googleapis.com/css?family=Ubuntu:300,400,500,700' rel='stylesheet' type='text/css'>
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            background: linear-gradient(135deg, #c32143, #f1b458);
            color: #333;
        }
        .matches-section {
            padding: 3em 0;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            margin: 2em auto;
        }
        .match-card {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            transition: transform 0.2s;
        }
        .match-card:hover {
            transform: scale(1.02);
        }
        .match-card img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
        }
        .match-card h3 {
            color: #c32143;
            font-size: 1.4em;
            margin-bottom: 0.5em;
        }
        .match-card a {
            margin-left: auto;
            background-color: #c32143;
            color: #fff;
            padding: 0.5em 1em;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .match-card a:hover {
            background-color: #f1b458;
            color: #333;
        }
        .notification-badge {
            background-color: #f1b458;
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 0.8em;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">MatchMingle</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blog.php">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">
                            Inbox
                            <?php if ($unreadCount > 0): ?>
                                <span class="notification-badge"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="matchright.php">Browse Profiles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="matches.php">Matches</a>
                    </li>
                    <?php if ($_SESSION['is_admin'] == 1): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_swap_approval.php">Swap Approvals</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="header">
        <h1>Your Matches</h1>
    </div>

    <div class="matches-section">
        <div class="container">
            <h2>Your Matches</h2>
            <?php if (empty($matches)): ?>
                <p style="text-align: center; color: #555;">No matches yet. Send swap requests and wait for admin approval!</p>
            <?php else: ?>
                <?php foreach ($matches as $match): ?>
                    <div class="match-card">
                        <img src="<?php echo !empty($match['photo']) ? 'uploads/profiles/' . htmlspecialchars($match['photo']) : 'images/default.jpg'; ?>" alt="photo">
                        <div>
                            <h3><?php echo htmlspecialchars($match['username']); ?></h3>
                        </div>
                        <a href="message.php?id=<?php echo $match['id']; ?>">Message</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col_2">
                    <h4>About Us</h4>
                    <p>MatchMingle is a trusted matrimony platform helping individuals find meaningful and lasting relationships.</p>
                </div>
                <div class="col-md-2 col_2">
                    <h4>Help & Support</h4>
                    <ul class="footer_links">
                        <li><a href="livehelp.php">Live Help</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="#">Feedback</a></li>
                        <li><a href="faq.php">FAQs</a></li>
                    </ul>
                </div>
                <div class="col-md-2 col_2">
                    <h4>Quick Links</h4>
                    <ul class="footer_links">
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="terms.php">Terms</a></li>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>