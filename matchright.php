<?php
session_start();
include_once("includes/dbconn.php");
include_once("functions.php");

// Log session ID for debugging
error_log("Session ID: " . session_id());

// 1. User Authentication Check
if (!isloggedin()) {
    error_log("User not logged in, redirecting to login.php");
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['id'];

// Fetch current user's username
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    error_log("Prepare failed for username fetch: " . mysqli_error($conn));
    die("Database error");
}
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$currentUser = mysqli_fetch_assoc($result);
if (!$currentUser) {
    error_log("No user found with ID: $userId");
    header("Location: login.php");
    exit();
}
$username = htmlspecialchars($currentUser['username']);
mysqli_stmt_close($stmt);

// Fetch unread message count
$sql = "SELECT COUNT(*) as unread_count FROM massage WHERE receiver_id = ? AND is_read = 0";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    error_log("Prepare failed for unread messages: " . mysqli_error($conn));
    $unreadCount = 0; // Default to 0 on failure
} else {
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $unread = mysqli_fetch_assoc($result);
    $unreadCount = $unread['unread_count'];
    mysqli_stmt_close($stmt);
}

// 2. Fetch current user's preferences and gender
$sql = "SELECT * FROM partnerprefs WHERE custId = ?";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    error_log("Prepare failed for partnerprefs: " . mysqli_error($conn));
    $prefs = []; // Default to empty on failure
} else {
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $prefs = mysqli_fetch_assoc($result) ?: [];
    mysqli_stmt_close($stmt);
}

$sql = "SELECT gender FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    error_log("Prepare failed for users gender: " . mysqli_error($conn));
    $currentUser = ['gender' => '']; // Default to empty on failure
} else {
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $currentUser = mysqli_fetch_assoc($result) ?: ['gender' => ''];
    mysqli_stmt_close($stmt);
}
$opposite_gender = ($currentUser['gender'] == 'male') ? 'female' : 'male';

// Use preferences if available, otherwise use defaults
$agemin = isset($prefs['agemin']) ? (int)$prefs['agemin'] : 18;
$agemax = isset($prefs['agemax']) ? (int)$prefs['agemax'] : 60;
$religion = isset($prefs['religion']) ? $prefs['religion'] : '%';

// 3. Filters and Search Options
$ageFilter = isset($_GET['age']) ? trim($_GET['age']) : '';
$religionFilter = isset($_GET['religion']) ? trim($_GET['religion']) : '';
$sortBy = isset($_GET['sort']) ? trim($_GET['sort']) : 'compatibility';

// Build the SQL query with filters
$sql = "SELECT id, username, TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) AS age, religion, photo 
        FROM users 
        WHERE gender = ? AND id != ?";
$params = [$opposite_gender, $userId];
$types = "si";

// Apply filters
if (!empty($ageFilter)) {
    if ($ageFilter == '18-25') {
        $sql .= " AND TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 18 AND 25";
    } elseif ($ageFilter == '26-35') {
        $sql .= " AND TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN 26 AND 35";
    } elseif ($ageFilter == '36+') {
        $sql .= " AND TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= 36";
    }
} else {
    $sql .= " AND TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) BETWEEN ? AND ?";
    $params[] = $agemin;
    $params[] = $agemax;
    $types .= "ii";
}

if (!empty($religionFilter)) {
    $sql .= " AND religion = ?";
    $params[] = $religionFilter;
    $types .= "s";
} else {
    $sql .= " AND religion LIKE ?";
    $params[] = $religion;
    $types .= "s";
}

// Sorting and Compatibility
if ($sortBy == 'age') {
    $sql .= " ORDER BY TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) ASC";
} elseif ($sortBy == 'username') {
    $sql .= " ORDER BY username ASC";
} else {
    // No ORDER BY in SQL; sort in PHP later
}

// Fetch all matches
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    error_log("Prepare failed for matches: " . mysqli_error($conn));
    $matches = []; // Default to empty on failure
} else {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Execute failed: " . mysqli_stmt_error($stmt));
        $matches = []; // Default to empty on failure
    } else {
        $result = mysqli_stmt_get_result($stmt);
        $matches = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Basic compatibility score: 50% base + 25% if age matches preference + 25% if religion matches
            $ageMatch = ($agemin <= $row['age'] && $row['age'] <= $agemax) ? 25 : 0;
            $religionMatch = (empty($religionFilter) || $row['religion'] == $religionFilter || $row['religion'] == $religion) ? 25 : 0;
            $row['compatibility_score'] = 50 + $ageMatch + $religionMatch;
            $matches[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

// Sort matches in PHP based on sortBy
if ($sortBy == 'compatibility') {
    usort($matches, function($a, $b) {
        return $b['compatibility_score'] - $a['compatibility_score'];
    });
} elseif ($sortBy == 'age') {
    usort($matches, function($a, $b) {
        return $a['age'] - $b['age'];
    });
} elseif ($sortBy == 'username') {
    usort($matches, function($a, $b) {
        return strcmp($a['username'], $b['username']);
    });
}

// 4. Pagination
$postsPerPage = 5;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$totalMatches = count($matches);
$totalPages = ceil($totalMatches / $postsPerPage);
$start = ($currentPage - 1) * $postsPerPage;
$matchesPage = array_slice($matches, $start, $postsPerPage);

// Fetch all matches
$stmt = mysqli_prepare($conn, $sql);
if (!$stmt) {
    error_log("Prepare failed for matches: " . mysqli_error($conn));
    $matches = []; // Default to empty on failure
} else {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Execute failed: " . mysqli_stmt_error($stmt));
        $matches = []; // Default to empty on failure
    } else {
        $result = mysqli_stmt_get_result($stmt);
        $matches = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Basic compatibility score: 50% base + 25% if age matches preference + 25% if religion matches
            $ageMatch = ($agemin <= $row['age'] && $row['age'] <= $agemax) ? 25 : 0;
            $religionMatch = (empty($religionFilter) || $row['religion'] == $religionFilter || $row['religion'] == $religion) ? 25 : 0;
            $row['compatibility_score'] = 50 + $ageMatch + $religionMatch;
            $matches[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

// 4. Pagination
$postsPerPage = 5;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$totalMatches = count($matches);
$totalPages = ceil($totalMatches / $postsPerPage);
$start = ($currentPage - 1) * $postsPerPage;
$matchesPage = array_slice($matches, $start, $postsPerPage);

// 5. Twilio Notification
if (!empty($matchesPage) && isset($twilio) && isset($_SESSION['phone'])) {
    try {
        $twilio->messages->create(
            $_SESSION['phone'], // Assuming phone is in session
            [
                'from' => TWILIO_PHONE_NUMBER,
                'body' => "You have new matches on MatchMingle! Check them out now."
            ]
        );
        error_log("Match notification SMS sent for user ID $userId");
    } catch (Exception $e) {
        error_log("Failed to send Twilio notification: " . $e->getMessage());
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchMingle - Your Matches</title>
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

        .notification-badge {
            background-color: #c32143;
            color: #fff;
            border-radius: 50%;
            padding: 0.2em 0.6em;
            font-size: 0.8em;
            position: relative;
            top: -10px;
            left: -5px;
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

        .header .username {
            font-size: 1.8em;
            margin-bottom: 0.2em;
            font-family: 'Oswald', sans-serif;
        }

        .match-section {
            padding: 3em 0;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .match-section h2 {
            color: #c32143;
            text-align: center;
            margin-bottom: 1.5em;
            font-family: 'Oswald', sans-serif;
        }

        .match-card {
            background: #fff;
            padding: 25px;
            margin-bottom: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
        }

        .match-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
        }

        .match-card h3 {
            color: #c32143;
            font-size: 1.6em;
            margin-bottom: 0.7em;
        }

        .match-card p {
            color: #555;
            font-size: 1.1em;
            line-height: 1.6;
            margin-bottom: 1em;
        }

        .match-card .btn {
            background-color: #c32143;
            color: #fff;
            border: none;
            padding: 0.6em 1.2em;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            cursor: pointer;
            margin-right: 10px;
        }

        .match-card .btn:hover {
            background-color: #f1b458;
            color: #333;
        }

        .filter-section {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-section select {
            padding: 0.5em;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 200px;
        }

        .filter-section button {
            background-color: #c32143;
            color: #fff;
            border: none;
            padding: 0.6em 1.2em;
            border-radius: 5px;
            cursor: pointer;
        }

        .filter-section button:hover {
            background-color: #f1b458;
            color: #333;
        }

        .pagination {
            text-align: center;
            margin: 30px 0;
        }

        .pagination a {
            color: #c32143;
            padding: 8px 12px;
            text-decoration: none;
            border: 1px solid #c32143;
            margin: 0 4px;
            border-radius: 5px;
        }

        .pagination a:hover {
            background-color: #f1b458;
            color: #333;
        }

        .pagination .active {
            background-color: #c32143;
            color: #fff;
            border: 1px solid #c32143;
        }

        .pagination .disabled {
            color: #ccc;
            padding: 8px 12px;
            border: 1px solid #ccc;
            margin: 0 4px;
            border-radius: 5px;
            pointer-events: none;
        }

        .cta-section {
            text-align: center;
            padding: 2em 0;
            background-color: rgba(0, 0, 0, 0.7);
            color: #fff;
        }

        .cta-section h3 {
            font-size: 1.8em;
            margin-bottom: 1em;
        }

        .cta-section .btn {
            background-color: #f1b458;
            color: #333;
            padding: 1em 2em;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
        }

        .cta-section .btn:hover {
            background-color: #c32143;
            color: #fff;
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
            .match-card {
                padding: 20px;
                flex-direction: column;
                align-items: flex-start;
            }

            .match-card img {
                margin-bottom: 15px;
                margin-right: 0;
            }

            .match-card h3 {
                font-size: 1.4em;
            }

            .match-card p {
                font-size: 1em;
            }

            .filter-section select {
                width: 100%;
                margin-bottom: 10px;
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
                        <a class="nav-link active" href="matchright.php">Browse Profiles</a>
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
        <h1 class="username"><?php echo $username; ?></h1>
        <h1>Your Matches</h1>
    </div>

    <!-- Match Section -->
    <div class="match-section">
        <div class="container">
            <!-- Filters and Search Options -->
            <div class="filter-section">
                <form method="GET" action="matchright.php">
                    <select name="age">
                        <option value="">Select Age Range</option>
                        <option value="18-25" <?php echo $ageFilter == '18-25' ? 'selected' : ''; ?>>18-25</option>
                        <option value="26-35" <?php echo $ageFilter == '26-35' ? 'selected' : ''; ?>>26-35</option>
                        <option value="36+" <?php echo $ageFilter == '36+' ? 'selected' : ''; ?>>36+</option>
                    </select>
                    <select name="religion">
                        <option value="">Select Religion</option>
                        <option value="Islam" <?php echo $religionFilter == 'Islam' ? 'selected' : ''; ?>>Islam</option>
                        <option value="Hinduism" <?php echo $religionFilter == 'Hinduism' ? 'selected' : ''; ?>>Hinduism</option>
                        <option value="Christianity" <?php echo $religionFilter == 'Christianity' ? 'selected' : ''; ?>>Christianity</option>
                        <option value="Other" <?php echo $religionFilter == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                    <select name="sort">
                        <option value="compatibility" <?php echo $sortBy == 'compatibility' ? 'selected' : ''; ?>>Sort by Compatibility</option>
                        <option value="age" <?php echo $sortBy == 'age' ? 'selected' : ''; ?>>Sort by Age</option>
                        <option value="username" <?php echo $sortBy == 'username' ? 'selected' : ''; ?>>Sort by Username</option>
                    </select>
                    <button type="submit">Apply Filters</button>
                </form>
            </div>

            <!-- Match Display -->
            <?php
            if (empty($matches)) {
                echo "<p style='text-align: center; color: #555;'>No matches found. Update your preferences to find more potential partners!</p>";
            } else {
                foreach ($matchesPage as $match) {
                    $matchId = $match['id'];
                    $photo = !empty($match['photo']) ? "uploads/profiles/" . htmlspecialchars($match['photo']) : "images/default_profile.jpg";
                    $name = htmlspecialchars($match['username']);
                    echo "<div class='row'><div class='col-md-12'><div class='match-card'>";
                    echo "<img src='{$photo}' alt='Profile'>";
                    echo "<div>";
                    echo "<h3>{$name}, {$match['age']}</h3>";
                    echo "<p>Religion: " . htmlspecialchars($match['religion']) . "<br>Compatibility Score: {$match['compatibility_score']}%</p>";
                    echo "<a href='profile.php?id={$match['id']}' class='btn'>View Profile</a>";
                    echo "<a href='message.php?id={$match['id']}' class='btn'>Message</a>";
                    echo "<a href='send_interest.php?id={$match['id']}' class='btn' onclick=\"return confirm('Are you sure you want to send an interest to {$name}?');\">Send Interest</a>";
                    echo "</div></div></div></div>";
                }
            }
            ?>

            <!-- Pagination -->
            <div class="pagination">
                <?php
                $queryParams = $_GET;
                unset($queryParams['page']); // Remove page to rebuild query string
                $prevQuery = http_build_query(array_merge($queryParams, ['page' => max(1, $currentPage - 1)]));
                $nextQuery = http_build_query(array_merge($queryParams, ['page' => min($totalPages, $currentPage + 1)]));

                if ($totalPages > 1) {
                    echo $currentPage > 1 ? "<a href='?{$prevQuery}'>Previous</a> " : "<span class='disabled'>Previous</span> ";
                    for ($i = 1; $i <= $totalPages; $i++) {
                        $pageQuery = http_build_query(array_merge($queryParams, ['page' => $i]));
                        echo "<a href='?{$pageQuery}' " . ($i == $currentPage ? "class='active'" : "") . ">$i</a> ";
                    }
                    echo $currentPage < $totalPages ? "<a href='?{$nextQuery}'>Next</a>" : "<span class='disabled'>Next</span>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="cta-section">
        <h3>Found Your Match? Take the Next Step!</h3>
        <a href="editprofile.php" class='btn'>Update Your Profile</a>
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