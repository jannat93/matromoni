<?php
session_start();
include_once("includes/dbconn.php");
include_once("functions.php");

if (!isloggedin()) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_SESSION['id']) ? $_SESSION['id'] : 0);
if ($id <= 0) {
    header("Location: login.php");
    exit();
}

$pageTitle = "MatchMingle Blog – Your Guide to Love and Marriage";
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$postsPerPage = 5;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
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
            left: 0;
            width: 250px;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            transition: left 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
            max-height: calc(100vh - 60px); /* Adjust for header and toggle button */
        }

        .sidebar.collapsed {
            left: -250px;
        }

        .sidebar-header {
            padding: 1.5em;
            background-color: #c32143;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 1001;
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

        /* Content */
        .content {
            margin-left: 250px;
            padding: 3em 1em;
            background-color: rgba(255, 255, 255, 0.9);
            min-height: 100vh;
            max-height: 100vh;
            overflow-y: auto;
            transition: margin-left 0.3s ease;
        }

        .content.collapsed {
            margin-left: 0;
        }

        .content h1 {
            color: #c32143;
            font-size: 2.5em;
            margin-bottom: 1em;
            font-family: 'Oswald', sans-serif;
            text-align: center;
        }

        .content h2 {
            color: #c32143;
            text-align: center;
            margin-bottom: 1.5em;
            font-family: 'Oswald', sans-serif;
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

        .blog-post img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 20px;
        }

        .blog-post h3 {
            color: #c32143;
            font-size: 1.6em;
            margin-bottom: 0.7em;
        }

        .blog-post p {
            color: #555;
            font-size: 1.1em;
            line-height: 1.6;
            margin-bottom: 1em;
        }

        .blog-post .more-text {
            display: none;
            font-size: 1.1em;
            line-height: 1.6;
            color: #555;
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
            text-decoration: none;
            display: inline-block;
        }

        .btn-action:hover {
            background-color: #f1b458;
            color: #333;
        }

        .quiz-container, .poll-container {
            display: none;
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }

        .quiz-container label, .poll-container label {
            display: block;
            margin-bottom: 10px;
            font-size: 1.1em;
            color: #555;
        }

        .quiz-container input, .poll-container input {
            margin-right: 10px;
        }

        .quiz-container .submit-btn, .poll-container .submit-btn {
            background-color: #c32143;
            color: #fff;
            border: none;
            padding: 0.6em 1.2em;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .quiz-container .submit-btn:hover, .poll-container .submit-btn:hover {
            background-color: #f1b458;
            color: #333;
        }

        .quiz-result, .poll-result {
            margin-top: 15px;
            font-size: 1.1em;
            color: #c32143;
            display: none;
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

        .cta-section .btn-action {
            background-color: #f1b458;
            color: #333;
            padding: 1em 2em;
            border-radius: 5px;
            font-size: 1.1em;
        }

        .cta-section .btn-action:hover {
            background-color: #c32143;
            color: #fff;
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
                left: 0;
            }

            .sidebar.collapsed {
                left: -200px;
            }

            .content {
                margin-left: 0;
                padding: 2em 1em;
            }

            .content.collapsed {
                margin-left: 0;
            }

            .content h1, .content h2 {
                font-size: 2em;
            }

            .card-header {
                font-size: 1.1em;
            }

            .card-body {
                font-size: 0.9em;
            }

            .blog-post {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .blog-post img {
                margin-right: 0;
                margin-bottom: 15px;
            }

            .btn-action {
                font-size: 0.9em;
                padding: 0.5em 1em;
            }

            .quiz-container label, .poll-container label {
                font-size: 1em;
            }

            .quiz-container .submit-btn, .poll-container .submit-btn {
                padding: 0.5em 1em;
            }

            .quiz-result, .poll-result {
                font-size: 1em;
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
            <li><a href="userhome.php?id=<?php echo $id; ?>"><i class="fa fa-user"></i> Profile</a></li>
            <li><a href="view-profile.php?id=<?php echo $id; ?>"><i class="fa fa-eye"></i> View Profile</a></li>
            <li><a href="matchright.php?id=<?php echo $id; ?>"><i class="fa fa-search"></i> View Matches</a></li>
            <li><a href="search-id.php"><i class="fa fa-id-card"></i> Search by Profile ID</a></li>
            <li><a href="services.php"><i class="fa fa-cogs"></i> Services</a></li>
            <li><a href="blog.php?id=<?php echo $id; ?>"><i class="fa fa-rss"></i> Blog</a></li>
            <li><a href="media.php"><i class="fa fa-camera"></i> Media</a></li>
            <li><a href="subscription.php"><i class="fa fa-star"></i> Subscription</a></li>
            <li><a href="contact-admin.php"><i class="fa fa-envelope"></i> Contact Admin</a></li>
            <li><a href="team.php"><i class="fa fa-users"></i> Team</a></li>
            <li><a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Content -->
    <div class="content" id="content">
        <h1><?php echo $pageTitle; ?></h1>
        <div class="container">
            <!-- Relationship and Marriage Advice -->
            <div class="row">
                <h2>Relationship & Marriage Advice</h2>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            5 Tips to Strengthen Your Relationship Before Marriage
                        </div>
                        <div class="card-body">
                            <p>Published on May 02, 2025 – Building a strong foundation is key to a happy marriage. Learn how to communicate effectively, set mutual goals, and more.</p>
                            <a href="#" class="btn-action">Read More</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            How to Navigate Cultural Differences in a Matrimonial Match
                        </div>
                        <div class="card-body">
                            <p>Published on April 20, 2025 – Cultural differences can be a challenge. Here’s how to embrace them and build a stronger bond.</p>
                            <a href="#" class="btn-action">Read More</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Stories -->
            <div class="row">
                <h2>Success Stories</h2>
                <?php
                $successStories = [
                    ["How Ayesha and Rahul Found Love on MatchMingle", "May 01, 2025", "Ayesha and Rahul’s journey is a testament to the power of connection.", "They met through a mutual interest in literature and bonded over long conversations. After months of dating, they tied the knot in a beautiful ceremony surrounded by family.", "images/success1.jpg"],
                    ["From Online Chat to a Lifetime Together: Priya & Arjun", "April 15, 2025", "Priya and Arjun met through MatchMingle and are now happily married.", "Their first chat turned into daily conversations, and within a year, they were engaged. Their wedding was a blend of both their cultures, filled with joy.", "images/success2.jpg"],
                    ["Sara and Ahmed’s Interfaith Love Story", "April 14, 2025", "An inspiring tale of love bridging cultural gaps.", "Sara and Ahmed navigated their interfaith relationship with grace, celebrating both their traditions in a unique wedding ceremony.", "images/success3.jpg"],
                    ["Zainab and Omar’s Dream Wedding", "April 13, 2025", "A couple who found their soulmate with MatchMingle’s help.", "Their wedding was a dream come true, with a traditional Nikah followed by a vibrant Walima celebration.", "images/success4.jpg"],
                    ["Neha and Vikram’s Journey to Happiness", "April 12, 2025", "A story of perseverance and love.", "Despite initial challenges, Neha and Vikram’s commitment to each other led to a joyful marriage filled with love.", "images/success5.jpg"],
                    ["Fatima and Hassan’s Match Made Online", "April 11, 2025", "How a simple search led to a lifetime commitment.", "Fatima’s search for a partner with shared values brought her to Hassan, and they’ve been inseparable since.", "images/success6.jpg"],
                    ["Riya and Karan’s Modern Romance", "April 10, 2025", "A contemporary love story born on MatchMingle.", "Riya and Karan’s modern approach to love led to a trendy wedding with a minimalist theme.", "images/success7.jpg"],
                    ["Amina and Yusuf’s Cultural Blend", "April 09, 2025", "A beautiful union of two rich traditions.", "Amina and Yusuf blended their cultural backgrounds in a wedding that honored both their heritages.", "images/success8.jpg"],
                    ["Priyanka and Rohan’s Fairytale Ending", "April 08, 2025", "A match that turned into a lifelong partnership.", "Priyanka and Rohan’s story is like a fairytale, with a magical wedding that felt straight out of a storybook.", "images/success9.jpg"],
                    ["Hina and Asif’s Long-Distance Love", "April 07, 2025", "Overcoming distance to find true love.", "Hina and Asif’s long-distance relationship thrived through constant communication, leading to a heartfelt reunion and marriage.", "images/success10.jpg"],
                    ["Meera and Sameer’s Quick Connection", "April 06, 2025", "A fast friendship that blossomed into marriage.", "Meera and Sameer clicked instantly, and within months, they were planning their future together.", "images/success11.jpg"],
                    ["Salma and Ibrahim’s Shared Faith", "April 05, 2025", "A bond strengthened by common values.", "Salma and Ibrahim’s shared faith and values made their marriage a natural and blessed union.", "images/success12.jpg"],
                    ["Anjali and Arjun’s Family Approval", "April 04, 2025", "A match supported by both families.", "With the blessings of both families, Anjali and Arjun’s wedding was a celebration of unity and love.", "images/success13.jpg"],
                    ["Nadia and Tariq’s Adventure Begins", "April 03, 2025", "A couple ready to explore life together.", "Nadia and Tariq’s love for adventure brought them together, and their wedding marked the start of a new journey.", "images/success14.jpg"],
                    ["Sneha and Vikram’s Second Chance", "April 02, 2025", "Love found again through MatchMingle.", "After previous heartbreaks, Sneha and Vikram found a second chance at love and happiness.", "images/success15.jpg"],
                    ["Zara and Ali’s Serendipitous Meeting", "April 01, 2025", "A chance encounter that led to forever.", "Zara and Ali’s unexpected connection on MatchMingle turned into a lifelong commitment.", "images/success16.jpg"],
                    ["Kavita and Raj’s Traditional Match", "March 31, 2025", "A classic arranged marriage with a modern twist.", "Kavita and Raj’s traditional match was enhanced by modern communication, leading to a perfect union.", "images/success18.jpg"],
                    ["Layla and Hassan’s Joyful Union", "March 30, 2025", "A celebration of love and commitment.", "Layla and Hassan’s wedding was filled with joy, music, and the love of their families.", "images/success19.jpg"],
                    ["Deepika and Amit’s Shared Dreams", "March 29, 2025", "A couple with aligned life goals.", "Deepika and Amit’s shared vision for the future made their marriage a strong partnership.", "images/success1.jpg"],
                    ["Sana and Omar’s Digital Romance", "March 28, 2025", "Love that started with a click.", "Sana and Omar’s online chats turned into a deep connection, culminating in a beautiful wedding.", "images/success2.jpg"],
                    ["Rina and Sanjay’s Cultural Fusion", "March 27, 2025", "A blend of traditions in a modern marriage.", "Rina and Sanjay’s wedding was a fusion of their cultural traditions, creating a unique celebration.", "images/success3.jpg"],
                    ["Aisha and Faisal’s Faith Journey", "March 26, 2025", "A spiritual connection that grew stronger.", "Aisha and Faisal’s shared faith journey led to a marriage filled with spiritual harmony.", "images/success4.jpg"],
                    ["Tara and Vikrant’s Mutual Respect", "March 25, 2025", "A relationship built on understanding.", "Tara and Vikrant’s mutual respect and understanding laid the foundation for a lasting marriage.", "images/success5.jpg"],
                    ["Huma and Zaid’s Family Ties", "March 24, 2025", "Love supported by a strong family network.", "Huma and Zaid’s families played a key role in their union, making their wedding a family affair.", "images/success6.jpg"],
                    ["Shalini and Rohit’s Shared Humor", "March 23, 2025", "A couple bonded by laughter.", "Shalini and Rohit’s shared sense of humor brought them closer, leading to a joyful marriage.", "images/success7.jpg"],
                    ["Nazia and Imran’s Long Courtship", "March 22, 2025", "A slow build to a lasting relationship.", "Nazia and Imran took their time to build a strong foundation, resulting in a beautiful marriage.", "images/success8.jpg"],
                    ["Pooja and Anil’s Dream Home", "March 21, 2025", "A match that led to a shared future.", "Pooja and Anil’s shared dream of building a home together came true after their wedding.", "images/success9.jpg"],
                    ["Fatima and Ahmed’s Travel Love", "March 20, 2025", "A couple united by their love for adventure.", "Fatima and Ahmed’s love for travel brought them together, and their wedding was a new adventure.", "images/success10.jpg"],
                    ["Kiran and Ravi’s Quiet Love", "March 19, 2025", "A subtle connection that turned into marriage.", "Kiran and Ravi’s quiet and steady love led to a peaceful and happy marriage.", "images/success11.jpg"],
                    ["Zoya and Hamza’s Perfect Match", "March 18, 2025", "A story of compatibility and love.", "Zoya and Hamza’s perfect compatibility made their marriage a match made in heaven.", "images/success12.jpg"]
                ];

                $totalSuccessPages = ceil(count($successStories) / $postsPerPage);
                $start = ($currentPage - 1) * $postsPerPage;
                $successStoriesPage = array_slice($successStories, $start, $postsPerPage);
                foreach ($successStoriesPage as $index => $story) {
                    $postId = $start + $index + 1;
                    echo "<div class='col-md-12'><div class='card blog-post' id='post-$postId'><div class='card-header'>{$story[0]}</div><div class='card-body'><img src='{$story[4]}' alt='Couple'><div><p>Published on {$story[1]} – {$story[2]}</p><p class='more-text' id='more-text-$postId'>{$story[3]}</p><button class='btn-action' onclick='toggleReadMore($postId)'>Read More</button></div></div></div></div>";
                }
                ?>
                <div class="pagination">
                    <?php
                    if ($totalSuccessPages > 1) {
                        echo $currentPage > 1 ? "<a href='?page=" . ($currentPage - 1) . "&id=$id'>Previous</a> " : "<span class='disabled'>Previous</span> ";
                        for ($i = 1; $i <= $totalSuccessPages; $i++) {
                            echo "<a href='?page=$i&id=$id' " . ($i == $currentPage ? "class='active'" : "") . ">$i</a> ";
                        }
                        echo $currentPage < $totalSuccessPages ? "<a href='?page=" . ($currentPage + 1) . "&id=$id'>Next</a>" : "<span class='disabled'>Next</span>";
                    }
                    ?>
                </div>
            </div>

            <!-- Cultural and Regional Insights -->
            <div class="row">
                <h2>Cultural & Regional Insights</h2>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Understanding Islamic Kufu Matching for a Perfect Match
                        </div>
                        <div class="card-body">
                            <p>Published on April 30, 2025 – Learn about the Islamic concept of Kufu and how it ensures compatibility in marriage.</p>
                            <a href="#" class="btn-action">Read More</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Traditional Muslim Wedding Rituals Explained
                        </div>
                        <div class="card-body">
                            <p>Published on April 10, 2025 – Dive into the beautiful traditions of a Muslim wedding, from Nikah to Walima.</p>
                            <a href="#" class="btn-action">Read More</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Safety and Privacy Tips -->
            <div class="row">
                <h2>Safety & Privacy Tips</h2>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            How to Spot a Fake Profile on Matrimonial Sites
                        </div>
                        <div class="card-body">
                            <p>Published on April 28, 2025 – Stay safe by learning the red flags to watch out for when browsing profiles.</p>
                            <a href="#" class="btn-action">Read More</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Tips for a Secure Online Dating Experience
                        </div>
                        <div class="card-body">
                            <p>Published on April 05, 2025 – Protect your personal information and ensure a safe journey to finding love online.</p>
                            <a href="#" class="btn-action">Read More</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Guides -->
            <div class="row">
                <h2>User Guides</h2>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            How to Create the Perfect MatchMingle Profile
                        </div>
                        <div class="card-body">
                            <p>Published on April 25, 2025 – Follow these steps to make your profile stand out and attract the right match.</p>
                            <a href="#" class="btn-action">Read More</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            What to Expect After Submitting Your Search
                        </div>
                        <div class="card-body">
                            <p>Published on April 01, 2025 – A guide to understanding search results and connecting with potential matches.</p>
                            <a href="#" class="btn-action">Read More</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trending Topics & News -->
            <div class="row">
                <h2>Trending Topics & News</h2>
                <?php
                $trendingTopics = [
                    ["2025 Marriage Trends: What Singles Are Looking For", "May 02, 2025", "Explore the latest trends in matrimony, from virtual dating to eco-friendly weddings."],
                    ["New Matrimonial Laws to Know in 2025", "April 18, 2025", "Stay informed about recent changes in marriage laws that could affect your journey."],
                    ["Rise of Interfaith Marriages in 2025", "April 17, 2025", "A look at the growing acceptance of interfaith unions."],
                    ["Virtual Weddings: The Future of Matrimony", "April 16, 2025", "How technology is transforming wedding ceremonies."],
                    ["Eco-Friendly Weddings Gain Popularity", "April 15, 2025", "Couples are choosing sustainable options for their big day."],
                    ["The Impact of Social Media on Matchmaking", "April 14, 2025", "How platforms like MatchMingle leverage social trends."],
                    ["2025 Dating Statistics Revealed", "April 13, 2025", "Insights into the changing landscape of online dating."],
                    ["Legal Rights in Modern Marriages", "April 12, 2025", "Understanding your rights as a married couple in 2025."],
                    ["The Role of Family in Arranged Matches", "April 11, 2025", "How families influence modern matrimonial decisions."],
                    ["Innovative Wedding Trends to Watch", "April 10, 2025", "From drone photography to unique themes, see what’s new."]
                ];

                $totalTrendingPages = ceil(count($trendingTopics) / $postsPerPage);
                $start = ($currentPage - 1) * $postsPerPage;
                $trendingTopicsPage = array_slice($trendingTopics, $start, $postsPerPage);
                foreach ($trendingTopicsPage as $topic) {
                    echo "<div class='col-md-12'><div class='card'><div class='card-header'>{$topic[0]}</div><div class='card-body'><p>Published on {$topic[1]} – {$topic[2]}</p><a href='#' class='btn-action'>Read More</a></div></div></div>";
                }
                ?>
                <div class="pagination">
                    <?php
                    if ($totalTrendingPages > 1) {
                        echo $currentPage > 1 ? "<a href='?page=" . ($currentPage - 1) . "&id=$id'>Previous</a> " : "<span class='disabled'>Previous</span> ";
                        for ($i = 1; $i <= $totalTrendingPages; $i++) {
                            echo "<a href='?page=$i&id=$id' " . ($i == $currentPage ? "class='active'" : "") . ">$i</a> ";
                        }
                        echo $currentPage < $totalTrendingPages ? "<a href='?page=" . ($currentPage + 1) . "&id=$id'>Next</a>" : "<span class='disabled'>Next</span>";
                    }
                    ?>
                </div>
            </div>

            <!-- Engagement Content -->
            <div class="row">
                <h2>Engagement Content</h2>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Quiz: Are You Ready for Marriage?
                        </div>
                        <div class="card-body">
                            <p>Published on April 22, 2025 – Take our fun quiz to find out if you’re ready to tie the knot!</p>
                            <button class="btn-action" onclick="showQuiz()">Take Quiz</button>
                            <div class="quiz-container" id="quiz">
                                <h4>Question 1: How often do you discuss future plans with your partner?</h4>
                                <label><input type="radio" name="q1" value="3"> Always</label>
                                <label><input type="radio" name="q1" value="2"> Sometimes</label>
                                <label><input type="radio" name="q1" value="1"> Rarely</label>
                                <h4>Question 2: Are you comfortable resolving conflicts together?</h4>
                                <label><input type="radio" name="q2" value="3"> Yes, always</label>
                                <label><input type="radio" name="q2" value="2"> Sometimes</label>
                                <label><input type="radio" name="q2" value="1"> Not really</label>
                                <h4>Question 3: Do you share similar life goals?</h4>
                                <label><input type="radio" name="q3" value="3"> Definitely</label>
                                <label><input type="radio" name="q3" value="2"> Somewhat</label>
                                <label><input type="radio" name="q3" value="1"> Not at all</label>
                                <button class="submit-btn" onclick="submitQuiz()">Submit</button>
                                <div class="quiz-result" id="quiz-result"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            What’s Your Ideal Wedding Destination?
                        </div>
                        <div class="card-body">
                            <p>Published on April 12, 2025 – Share your dream wedding location and see what others are choosing!</p>
                            <button class="btn-action" onclick="showPoll()">Join the Poll</button>
                            <div class="poll-container" id="poll">
                                <h4>Select your ideal wedding destination:</h4>
                                <label><input type="radio" name="poll-option" value="Beach"> Beach</label>
                                <label><input type="radio" name="poll-option" value="Mountains"> Mountains</label>
                                <label><input type="radio" name="poll-option" value="City"> City</label>
                                <label><input type="radio" name="poll-option" value="Countryside"> Countryside</label>
                                <button class="submit-btn" onclick="submitPoll()">Vote</button>
                                <div class="poll-result" id="poll-result"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Call to Action -->
        <div class="cta-section">
            <h3>Ready to Find Your Match?</h3>
            <a href="register.php" class="btn-action">Sign Up Now</a>
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
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('content').classList.toggle('collapsed');
        }

        function toggleReadMore(postId) {
            const moreText = document.getElementById('more-text-' + postId);
            const btn = document.querySelector('#post-' + postId + ' .btn-action');
            if (moreText.style.display === 'none' || moreText.style.display === '') {
                moreText.style.display = 'block';
                btn.textContent = 'Read Less';
            } else {
                moreText.style.display = 'none';
                btn.textContent = 'Read More';
            }
        }

        function showQuiz() {
            const quizContainer = document.getElementById('quiz');
            const quizResult = document.getElementById('quiz-result');
            quizContainer.style.display = 'block';
            quizResult.style.display = 'none';
            document.querySelectorAll('#quiz input[type="radio"]').forEach(input => input.checked = false);
        }

        function submitQuiz() {
            const q1 = parseInt(document.querySelector('input[name="q1"]:checked')?.value || 0);
            const q2 = parseInt(document.querySelector('input[name="q2"]:checked')?.value || 0);
            const q3 = parseInt(document.querySelector('input[name="q3"]:checked')?.value || 0);
            const score = q1 + q2 + q3;
            const resultDiv = document.getElementById('quiz-result');

            let resultText = '';
            if (score >= 7) {
                resultText = "You’re ready for marriage! It looks like you’re well-prepared for this big step.";
            } else if (score >= 4) {
                resultText = "You’re almost there! A bit more preparation might help you feel fully ready.";
            } else {
                resultText = "You might need more time. Take it slow and focus on building a strong foundation.";
            }

            resultDiv.innerHTML = resultText + ' <button class="submit-btn" onclick="showQuiz()">Retake Quiz</button>';
            resultDiv.style.display = 'block';
            document.getElementById('quiz').style.display = 'none';
        }

        function showPoll() {
            const pollContainer = document.getElementById('poll');
            const pollResult = document.getElementById('poll-result');
            pollContainer.style.display = 'block';
            pollResult.style.display = 'none';
            document.querySelectorAll('#poll input[type="radio"]').forEach(input => input.checked = false);
        }

        function submitPoll() {
            const selectedOption = document.querySelector('input[name="poll-option"]:checked')?.value;
            const resultDiv = document.getElementById('poll-result');

            if (!selectedOption) {
                resultDiv.innerHTML = "Please select an option to vote!";
                resultDiv.style.display = 'block';
                return;
            }

            const mockResults = {
                "Beach": 40,
                "Mountains": 30,
                "City": 20,
                "Countryside": 10
            };

            const percentage = mockResults[selectedOption] || 0;
            resultDiv.innerHTML = `You chose ${selectedOption}! ${percentage}% of users also chose this destination. <button class="submit-btn" onclick="showPoll()">Vote Again</button>`;
            resultDiv.style.display = 'block';
            document.getElementById('poll').style.display = 'none';
        }
    </script>
</body>
</html>