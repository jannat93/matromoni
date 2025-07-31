<?php
session_start();
include_once("includes/dbconn.php");

// Basic form processing
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $looking_for = $_GET['looking_for'] ?? '';
    $age = $_GET['age'] ?? '';
    $religion = $_GET['religion'] ?? '';

    // Initialize search results array
    $search_results = [];

    // Prepare the SQL query with conditions based on input
    $query = "SELECT id, username, age, religion, photo FROM users WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($looking_for)) {
        // Map "bride" to "female" and "groom" to "male" in gender column
        $gender_value = ($looking_for == 'bride') ? 'female' : (($looking_for == 'groom') ? 'male' : '');
        if ($gender_value) {
            $query .= " AND gender = ?";
            $params[] = $gender_value;
            $types .= "s";
        }
    }
    if (!empty($age)) {
        // Define age range based on selection
        $age_range = explode('-', $age);
        $min_age = 0;
        $max_age = 100; // Default max age
        if ($age == '18-25') {
            $min_age = 18;
            $max_age = 25;
        } elseif ($age == '26-35') {
            $min_age = 26;
            $max_age = 35;
        } elseif ($age == '36+') {
            $min_age = 36;
            $max_age = 100;
        }
        $query .= " AND age BETWEEN ? AND ?";
        $params[] = $min_age;
        $params[] = $max_age;
        $types .= "ii"; // 'i' for integer
    }
    if (!empty($religion)) {
        $query .= " AND religion = ?";
        $params[] = $religion;
        $types .= "s";
    }

    // Debug output
    echo "<!-- Debug: Query = $query, Params = " . implode(", ", $params) . ", Types = $types -->";

    // Execute the query
    if (!empty($params)) {
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $row_count = $result->num_rows;
            echo "<!-- Debug: Rows found = $row_count -->";
            while ($row = $result->fetch_assoc()) {
                $search_results[] = $row;
            }
            $stmt->close();
        } else {
            $search_message = "Error preparing query: " . $conn->error;
            echo "<!-- Debug: Error = $search_message -->";
        }
    } else {
        $search_message = "Please select at least one filter to search.";
        echo "<!-- Debug: No params, message = $search_message -->";
    }

    // Limit to 2 results for non-logged-in users
    $is_logged_in = isset($_SESSION['user_id']);
    if (!$is_logged_in && !empty($search_results)) {
        $search_results = array_slice($search_results, 0, 2);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MatchMingle</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for social icons -->
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
            animation: gradient 15s ease infinite; /* Fixed animation syntax */
            color: #333;
            background-image: url('images/pic1.avif');
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

        .header {
            background-color: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 2em 0;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 0.5em;
            font-family: 'Oswald', sans-serif;
        }

        .header p {
            font-size: 1.2em;
            margin-bottom: 1.5em;
        }

        .search-form {
            background-color: #f9f9f9;
            padding: 2em;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 600px;
            margin: 2em auto;
        }

        .search-form h2 {
            text-align: center;
            margin-bottom: 1.5em;
            color: #c32143;
        }

        .form-group {
            margin-bottom: 1.2em;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5em;
            font-weight: bold;
            color: #555;
        }

        .form-group select,
        .form-group input[type="text"] {
            width: calc(100% - 12px);
            padding: 0.8em;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
        }

        .search-form button {
            display: block;
            width: 100%;
            padding: 1em;
            background-color: #c32143;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-form button:hover {
            background-color: #f1b458;
            color: #333;
        }

        .search-results {
            min-height: 300px; /* Prevents layout shifts */
            padding: 2em;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 1000px;
            margin: 2em auto;
            text-align: center;
        }

        .search-results h3 {
            color: #c32143;
            margin-bottom: 1em;
        }

        .result-card {
            display: inline-block;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin: 1em;
            padding: 1.5em;
            width: 200px;
            text-align: center;
        }

        .result-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 0.5em;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .result-card img.onload {
            opacity: 1;
        }

        .notification {
            padding: 1em;
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            border-radius: 5px;
            margin: 1em auto;
            width: 80%;
            max-width: 600px;
            text-align: center;
        }

        .notification a {
            color: #c32143;
            text-decoration: underline;
            font-weight: bold;
        }

        .notification a:hover {
            color: #f1b458;
        }

        .featured-profiles {
            padding: 2em 0;
            text-align: center;
        }

        .featured-profiles h2 {
            color: #c32143;
            margin-bottom: 1.5em;
        }

        .profile-card {
            display: inline-block;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin: 1em;
            padding: 1.5em;
            width: 200px;
            text-align: center;
        }

        .profile-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 0.5em;
        }

        .profile-card h3 {
            font-size: 1.1em;
            margin-bottom: 0.3em;
            color: #333;
        }

        .profile-card p {
            font-size: 0.9em;
            color: #777;
        }

        /* New Sections */
        .special-someone {
            padding: 3em 0;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .special-someone h2 {
            color: #c32143;
            font-size: 2em;
            margin-bottom: 1.5em;
        }

        .steps-container {
            display: flex;
            justify-content: center;
            gap: 2em;
            flex-wrap: wrap;
            margin-bottom: 2em;
        }

        .step {
            width: 200px;
            text-align: center;
        }

        .step h3 {
            color: #333;
            font-size: 1.2em;
            margin-bottom: 0.5em;
        }

        .step p {
            color: #555;
            font-size: 0.9em;
        }

        .success-stories {
            text-align: center;
            padding: 2em 0;
        }

        .success-stories h3 {
            color: #c32143;
            font-size: 1.5em;
            margin-bottom: 1em;
        }

        .success-stories .button {
            display: inline-block;
            padding: 1em 2em;
            text-decoration: none;
            color: #fff;
            background-color: #f1b458;
            border-radius: 5px;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
        }

        .success-stories .button:hover {
            background-color: #c32143;
        }

        .explore-profiles {
            padding: 2em 0;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .explore-profiles h2 {
            color: #c32143;
            font-size: 1.8em;
            margin-bottom: 1em;
        }

        .explore-profiles ul {
            list-style: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1em;
        }

        .explore-profiles ul li {
            font-size: 1em;
            color: #333;
        }

        .explore-profiles ul li a {
            text-decoration: none;
            color: #c32143;
            transition: color 0.3s ease;
        }

        .explore-profiles ul li a:hover {
            color: #f1b458;
        }

        .app-download {
            padding: 2em 0;
            text-align: center;
        }

        .app-download h2 {
            color: #c32143;
            font-size: 1.8em;
            margin-bottom: 1em;
        }

        .app-download .store-buttons {
            display: flex;
            justify-content: center;
            gap: 1em;
        }

        .app-download .store-button {
            display: inline-block;
            padding: 0.5em 1em;
            text-decoration: none;
            color: #fff;
            background-color: #333;
            border-radius: 5px;
            font-size: 1em;
        }

        .app-download .store-button:hover {
            background-color: #f1b458;
            color: #333;
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
            .search-form {
                width: 95%;
                margin: 1em auto;
                padding: 1.5em;
            }

            .search-results {
                width: 95%;
            }

            .featured-profiles .profile-card {
                width: 150px;
                margin: 0.5em;
                padding: 1em;
            }

            .featured-profiles h2 {
                font-size: 1.5em;
            }

            .steps-container {
                flex-direction: column;
                align-items: center;
            }

            .step {
                width: 80%;
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

        /* Chatbot Styles */
        .chatbot-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 15px 30px;
            background-color: #c32143;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Ubuntu', sans-serif;
            transition: background-color 0.3s ease;
        }

        .chatbot-button:hover {
            background-color: #f1b458;
            color: #333;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">MatchMingle</a>
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
                </ul>
            </div>
        </div>
    </nav>

    <div class="header">
        <h1>Find Your Perfect Match with MatchMingle</h1>
        <p>Connecting singles worldwide.</p>
    </div>

    <div class="search-form">
        <h2>Find Your Partner</h2>
        <?php if (isset($search_message)): ?>
            <p style="color: #c32143; text-align: center;"><?php echo htmlspecialchars($search_message); ?></p>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
            <div class="form-group">
                <label for="looking_for">Looking For:</label>
                <select id="looking_for" name="looking_for">
                    <option value="">Any</option>
                    <option value="bride">Bride</option>
                    <option value="groom">Groom</option>
                </select>
            </div>
            <div class="form-group">
                <label for="age">Age:</label>
                <select id="age" name="age">
                    <option value="">Any</option>
                    <option value="18-25">18-25</option>
                    <option value="26-35">26-35</option>
                    <option value="36+">36+</option>
                </select>
            </div>
            <div class="form-group">
                <label for="religion">Religion:</label>
                <select id="religion" name="religion">
                    <option value="">Any</option>
                    <option value="islam">Islam</option>
                    <option value="hinduism">Hinduism</option>
                    <option value="christianity">Christianity</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <button type="submit">Search Now</button>
        </form>
    </div>

    <?php if (!empty($search_results)): ?>
        <div class="search-results">
            <h3>Search Results</h3>
            <?php foreach ($search_results as $result): ?>
                <div class="result-card">
                    <img src="/matrimonial1/images/<?php echo htmlspecialchars($result['photo'] ?? 'default.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($result['username']); ?>" 
                         style="opacity: 0; transition: opacity 0.3s ease;" 
                         onload="this.style.opacity = 1;">
                    <h3><?php echo htmlspecialchars($result['username']); ?></h3>
                    <p><?php echo htmlspecialchars($result['age']); ?> Years, <?php echo htmlspecialchars($result['religion']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (!$is_logged_in): ?>
            <div class="notification">
                Only 2 profiles are shown. For more results, <a href="register.php">register</a> with MatchMingle and buy a subscription!
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="featured-profiles">
        <h2>Featured Profiles</h2>
        <div class="profile-card">
            <img src="images/girl1.jpeg" alt="Profile 1">
            <h3>Ayesha</h3>
            <p>28 Years</p>
        </div>
        <div class="profile-card">
            <img src="images/girl2.jpeg" alt="Profile 2">
            <h3>Rahman</h3>
            <p>32 Years</p>
        </div>
        <div class="profile-card">
            <img src="images/girl3.jpeg" alt="Profile 3">
            <h3>Farzana</h3>
            <p>25 Years</p>
        </div>
    </div>

    <!-- New Sections -->
    <div class="special-someone">
        <h2>Find Your Special Someone</h2>
        <div class="steps-container">
            <div class="step">
                <h3>Sign Up</h3>
                <p>Register for free & put up your Matrimony Profile</p>
            </div>
            <div class="step">
                <h3>Connect</h3>
                <p>Select & Connect with Matches you like</p>
            </div>
            <div class="step">
                <h3>Interact</h3>
                <p>Become a Premium Member & Start a Conversation</p>
            </div>
        </div>
    </div>

    <div class="success-stories">
        <h3>Matrimony Service with Millions of Success Stories</h3>
        <p>Your story is waiting to happen!</p>
        <a href="register.php" class="button">Get Started</a>
    </div>

    <div class="explore-profiles">
        <h2>Explore Matrimonial Profiles By</h2>
        <ul>
            <li><a href="#">Dating</a></li>
            <li><a href="#">Horoscope</a></li>
            <li><a href="#">kufu Matching</a></li>
            <li><a href="#">location Matching</a></li>
            <li><a href="#">age Matching</a></li>
            <li><a href="#">choice Matching</a></li>
            <li><a href="#">job choice</a></li>
            <li><a href="#"> Matching 1</a></li>
            <li><a href="#">Matching 2</a></li>
            <li><a href="#">Community Matrimony Services</a></li>
        </ul>
    </div>

    <div class="app-download">
        <h2>Download the Matrimony App</h2>
        <div class="store-buttons">
            <a href="https://play.google.com/store" class="store-button">Play Store</a>
            <a href="https://www.apple.com/app-store/" class="store-button">App Store</a>
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

    <!-- Chatbot Interface -->
    <button class="chatbot-button" onclick="toggleChat()">Chat with AI</button>
    <div id="chatbox" style="display: none; position: fixed; bottom: 60px; right: 20px; width: 300px; height: 400px; background-color: white; border: 1px solid #ccc; padding: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <div id="chat-messages" style="height: 80%; overflow-y: auto; border-bottom: 1px solid #ccc;"></div>
        <input type="text" id="chat-input" placeholder="Type your message..." onkeypress="if(event.key === 'Enter') sendMessage()" style="width: 100%; margin-top: 10px;">
    </div>
    <script>
        let chatVisible = false;
        function toggleChat() {
            const chatbox = document.getElementById('chatbox');
            chatVisible = !chatVisible;
            chatbox.style.display = chatVisible ? 'block' : 'none';
        }

        function sendMessage() {
            const input = document.getElementById('chat-input');
            const message = input.value.trim();
            if (message) {
                const chatMessages = document.getElementById('chat-messages');
                chatMessages.innerHTML += `<p><strong>You:</strong> ${message}</p>`;
                chatMessages.innerHTML += `<p><strong>AI:</strong> Processing...</p>`;
                chatMessages.scrollTop = chatMessages.scrollHeight;
                input.value = '';
                getAIResponse(message);
            }
        }

        function getAIResponse(userMessage) {
            const chatMessages = document.getElementById('chat-messages');
            const lastMessage = chatMessages.lastChild;
            fetch('api_proxy.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: userMessage })
            })
            .then(response => response.json())
            .then(data => {
                lastMessage.innerHTML = `<p><strong>AI:</strong> ${data.response || data.error}</p>`;
                chatMessages.scrollTop = chatMessages.scrollHeight;
            })
            .catch(error => {
                lastMessage.innerHTML = `<p><strong>AI:</strong> Error: Unable to get response. Please try again later.</p>`;
                console.error('Error:', error);
            });
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>