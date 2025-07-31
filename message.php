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
$receiverId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sendMessage = '';
$sendError = '';

// Validate receiver ID
if ($receiverId <= 0 || $receiverId == $userId) {
    error_log("Invalid receiver ID: $receiverId or attempting to message self");
    header("Location: matchright.php");
    exit();
}

// Fetch receiver's details
$sql = "SELECT username, photo FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    error_log("Prepare failed for receiver fetch: " . mysqli_error($conn));
    die("Database error");
}
mysqli_stmt_bind_param($stmt, "i", $receiverId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$receiver = mysqli_fetch_assoc($result);
if (!$receiver) {
    error_log("No user found with ID: $receiverId");
    header("Location: matchright.php");
    exit();
}
$receiverUsername = htmlspecialchars($receiver['username']);
$receiverPhoto = !empty($receiver['photo']) ? "uploads/profiles/" . htmlspecialchars($receiver['photo']) : "images/default_profile.jpg";
mysqli_stmt_close($stmt);

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (empty($message)) {
        $sendError = "<p style='color: red;'>Message cannot be empty.</p>";
    } elseif (strlen($message) > 1000) {
        $sendError = "<p style='color: red;'>Message is too long (max 1000 characters).</p>";
    } else {
        $sql = "INSERT INTO massage (sender_id, receiver_id, message) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iis", $userId, $receiverId, $message);
            if (mysqli_stmt_execute($stmt)) {
                $sendMessage = "<p style='color: green;'>Message sent successfully!</p>";
            } else {
                $sendError = "<p style='color: red;'>Failed to send message.</p>";
                error_log("Message insert failed: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
        } else {
            $sendError = "<p style='color: red;'>Database error.</p>";
            error_log("Prepare failed for message insert: " . mysqli_error($conn));
        }
    }
}

// Mark messages as read
$sql = "UPDATE massage SET is_read = 1 WHERE receiver_id = ? AND sender_id = ?";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $userId, $receiverId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} else {
    error_log("Prepare failed for marking messages as read: " . mysqli_error($conn));
}

// Fetch conversation history
$sql = "SELECT m.*, u.username, u.photo 
        FROM massage m 
        JOIN users u ON m.sender_id = u.id 
        WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?) 
        ORDER BY m.sent_at ASC";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    error_log("Prepare failed for conversation fetch: " . mysqli_error($conn));
    die("Database error");
}
mysqli_stmt_bind_param($stmt, "iiii", $userId, $receiverId, $receiverId, $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$messages = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Fetch unread message count for navigation
$sql = "SELECT COUNT(*) as unread_count FROM massage WHERE receiver_id = ? AND is_read = 0";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    error_log("Prepare failed for unread messages: " . mysqli_error($conn));
    die("Database error");
}
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$unread = mysqli_fetch_assoc($result);
$unreadCount = $unread['unread_count'];
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchMingle - Message</title>
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

        .message-section {
            padding: 3em 0;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .message-section h2 {
            color: #c32143;
            text-align: center;
            margin-bottom: 1.5em;
            font-family: 'Oswald', sans-serif;
        }

        .message-form {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 25px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .message-form .form-group {
            margin-bottom: 1.5em;
        }

        .message-form label {
            font-weight: bold;
            color: #333;
        }

        .message-form textarea {
            width: 100%;
            padding: 0.5em;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: vertical;
        }

        .message-form button {
            background-color: #c32143;
            color: #fff;
            border: none;
            padding: 0.6em 1.2em;
            border-radius: 5px;
            cursor: pointer;
        }

        .message-form button:hover {
            background-color: #f1b458;
            color: #333;
        }

        .conversation {
            max-width: 600px;
            margin: 2em auto;
        }

        .message {
            margin-bottom: 1em;
            padding: 1em;
            border-radius: 5px;
        }

        .message.sent {
            background-color: #f1b458;
            margin-left: 10%;
            text-align: right;
        }

        .message.received {
            background-color: #e0e0e0;
            margin-right: 10%;
            text-align: left;
        }

        .message img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            vertical-align: middle;
            margin-right: 10px;
        }

        .message p {
            margin: 0;
            display: inline-block;
        }

        .message .timestamp {
            font-size: 0.8em;
            color: #555;
            display: block;
            margin-top: 0.5em;
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
            .message-form {
                padding: 20px;
            }

            .message.sent,
            .message.received {
                margin-left: 5%;
                margin-right: 5%;
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
                            Inbox
                            <?php if ($unreadCount > 0): ?>
                                <span class="notification-badge"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="matchright.php">Back</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="header">
        <h1>Message <?php echo $receiverUsername; ?></h1>
    </div>

    <!-- Message Section -->
    <div class="message-section">
        <div class="container">
            <div class="message-form">
                <h2>Send a Message</h2>
                <?php echo $sendMessage; ?>
                <?php echo $sendError; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="message">Your Message</label>
                        <textarea name="message" id="message" rows="5" required></textarea>
                    </div>
                    <button type="submit">Send Message</button>
                </form>
            </div>

            <div class="conversation">
                <h3>Conversation History</h3>
                <?php if (empty($messages)): ?>
                    <p>No messages yet. Start the conversation!</p>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message <?php echo $msg['sender_id'] == $userId ? 'sent' : 'received'; ?>">
                            <img src="<?php echo !empty($msg['photo']) ? 'uploads/profiles/' . htmlspecialchars($msg['photo']) : 'images/default_profile.jpg'; ?>" alt="<?php echo htmlspecialchars($msg['username']); ?>">
                            <p><?php echo htmlspecialchars($msg['message']); ?></p>
                            <span class="timestamp"><?php echo date('M d, Y H:i', strtotime($msg['sent_at'])); ?></span>
                        </div>
                    <?php endforeach; ?>
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