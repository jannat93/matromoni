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
    <title>MatchMingle - Media Gallery</title>
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
            min-height: 200vh;
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

        .media-section {
            padding: 3em 0;
            background-color: rgba(255, 255, 255, 0.9);
            min-height: 200vh;
        }

        .section-title {
            color: #c32143;
            font-family: 'Oswald', sans-serif;
            text-align: center;
            margin-bottom: 2em;
            font-size: 2em;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 0 15px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .gallery-item {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            padding: 15px;
        }

        .gallery-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 5px;
        }

        .gallery-item h3 {
            color: #c32143;
            font-size: 1.5em;
            margin: 1em 0 0.5em;
        }

        .gallery-item p {
            color: #555;
            font-size: 1em;
            line-height: 1.5;
        }

        .gallery-item:hover {
            transform: scale(1.05);
        }

        .user-media, .testimonials, .events {
            margin-top: 4em;
        }

        .user-media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 0 15px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .user-media-item {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .user-media-item img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 1em;
        }

        .user-media-item h4 {
            color: #c32143;
            font-size: 1.2em;
            margin-bottom: 0.5em;
        }

        .user-media-item p {
            color: #555;
            font-size: 1em;
        }

        .testimonial-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto 20px;
            text-align: center;
        }

        .testimonial-card p:first-child {
            font-style: italic;
            color: #333;
            font-size: 1.1em;
            margin-bottom: 1em;
        }

        .testimonial-card p:last-child {
            color: #c32143;
            font-weight: bold;
        }

        .event-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto 20px;
            text-align: center;
        }

        .event-card h4 {
            color: #c32143;
            margin-bottom: 1em;
            font-size: 1.5em;
        }

        .event-card p {
            color: #555;
            font-size: 1.1em;
            margin-bottom: 0.5em;
        }

        .event-card .btn {
            background-color: #c32143;
            color: #fff;
            border: none;
            padding: 0.5em 1em;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .event-card .btn:hover {
            background-color: #f1b458;
            color: #333;
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
            .gallery-grid, .user-media-grid {
                grid-template-columns: 1fr;
            }
            .section-title {
                font-size: 1.5em;
            }
            .gallery-item img {
                height: 150px;
            }
            .user-media-item img {
                width: 150px;
                height: 150px;
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
                        <a class="nav-link" href="search-username.php">Search by Username</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="are-you-attending.php">Are You Attending</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="media.php">Media</a>
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
        <h1>Media Gallery</h1>
    </div>

    <!-- Media Section -->
    <div class="media-section">
        <div class="container">
            <!-- Gallery Section -->
            <h2 class="section-title">Gallery</h2>
            <div class="gallery-grid">
                <div class="gallery-item">
                  <img src="images/Wedding1.jpg" alt="Wedding1.jpg">
                    <h3>Wedding 1</h3>
                    <p>A beautiful outdoor wedding ceremony held on June 10, 2025, featuring stunning floral decorations and a heartfelt vow exchange. Captured by our official photographer, this moment showcases the joy of love.</p>
                </div>
                <div class="gallery-item">
                    <img src="images/Wedding2.jpg" alt="Wedding2.jpg">
                    <h3>Wedding 2</h3>
                    <p>An elegant indoor wedding on June 20, 2025, with classic decor and a grand reception. This event highlighted cultural traditions and a memorable first dance.</p>
                </div>
                <div class="gallery-item">
    <img src="images/Wedding3.jpg" alt="Wedding3.jpg">
    <h3>Wedding 3</h3>
    <p>A beautiful beachside wedding on July 12, 2025, where the couple exchanged vows at sunset. The serene setting made it a magical day for everyone in attendance.</p>
</div>

<div class="gallery-item">
    <img src="images/Wedding4.jpg" alt="Wedding4.jpg">
    <h3>Wedding 4</h3>
    <p>A traditional village wedding on August 5, 2025, full of vibrant colors, music, and rituals. Friends and family came together to celebrate love and heritage.</p>
</div>

<div class="gallery-item">
    <img src="images/Wedding5.jpg" alt="Wedding5.jpg">
    <h3>Wedding 5</h3>
    <p>An intimate rooftop ceremony held on September 1, 2025, under a starlit sky. The couple celebrated with close friends and personalized touches throughout the evening.</p>
</div>

<div class="gallery-item">
    <img src="images/Wedding6.jpg" alt="Wedding6.jpg">
    <h3>Wedding 6</h3>
    <p>A grand hall wedding on October 10, 2025, where tradition met modern elegance. The dazzling decor and joyful moments made it an unforgettable celebration of love.</p>
</div>

                <div class="gallery-item">
                    <img src="images/event1.jpg" alt="event1.jpg">
                    <h3>Event 1</h3>
                    <p>A community matchmaking event on May 15, 2025, where singles connected through fun activities and professional counseling. A vibrant celebration of new beginnings!</p>
                </div>
                <div class="gallery-item">
                    <img src="images/event2.jpg" alt="event2.jpg">
                    <h3>Event 2</h3>
                    <p>An online webinar on June 5, 2025, focused on relationship advice, featuring expert speakers and interactive Q&A sessions for engaged couples.</p>
                </div>
                <div class="gallery-item">
                    <img src="images/event3.jpg" alt="event3.jpg">
                    <h3>Event 3</h3>
                    <p>On February 20, 2025, MatchMingle hosted a virtual speed dating night, bringing together young professionals from across Bangladesh. The event sparked meaningful conversations and several exciting new matches!</p>
                </div>
                    <div class="gallery-item">
                    <img src="images/event4.jpg" alt="event4.jpg">
                    <h3>Event 4</h3>
                    <p>A cultural evening on March 8, 2025, featured live music, traditional games, and heartfelt storytelling. Participants bonded over shared values, creating a warm and memorable atmosphere for potential connections.</p>
                </div>
                <div class="gallery-item"> 
    <img src="images/event5.jpg" alt="event5.jpg">
    <h3>Event 5</h3>
    <p>On February 20, 2025, MatchMingle hosted a virtual speed dating night, bringing together young professionals from across Bangladesh. The event sparked meaningful conversations and several exciting new matches!</p>
</div>

<div class="gallery-item"> 
    <img src="images/event6.jpg" alt="event6.jpg">
    <h3>Event 6</h3>
    <p>on April 10, 2025, offered families and singles a chance to relax, enjoy local cuisine, and meet others in a warm, friendly setting. Connections bloomed under the spring sun.</p>
</div>

<div class="gallery-item"> 
    <img src="images/event7.jpg" alt="event7.jpg">
    <h3>Event 7</h3>
    <p>A professional relationship-building workshop on May 25, 2025, guided participants through communication skills, compatibility exercises, and self-discovery, paving the way for meaningful matches.</p>
</div>
<div class="gallery-item"> 
    <img src="images/event8.jpg" alt="event8.jpg">
    <h3>Event 8</h3>
    <p>An inspiring success stories night on June 1, 2025, where happily married couples shared their journeys. The event encouraged hopeful singles and celebrated lasting connections.</p>
</div>

<div class="gallery-item"> 
    <img src="images/event9.jpg" alt="event9.jpg">
    <h3>Event 9</h3>
    <p>A festive wedding reunion on June 8, 2025, brought community members together with food, music, and fun activities. It was a joyful opportunity to meet new people in a relaxed setting.</p>
</div>


            </div>
            <br>

            <!-- User Media Section -->
            <h2 class="section-title">User Uploaded Media</h2>
            <div class="user-media-grid">
                <div class="user-media-item">
                    <img src="images/userup2.jpg" alt="userup2.jpg">
                    <h4>User Photo 1</h4>
                    <p>Uploaded by: Rahul<br>A candid shot from Rahul's engagement party, shared to inspire others on their journey to love.</p>
                </div>
                <div class="user-media-item">
                    <img src="images/userup3.jpg" alt="userup3.jpg">
                    <h4>User Photo 2</h4>
                    <p>Uploaded by: Priya <br>Priya 's wedding portrait, showcasing her stunning traditional attire from her big day.</p>
                </div>
                <div class="user-media-item">
                    <img src="images/userup4.jpg" alt="userup4.jpg">
                    <h4>User Photo 3</h4>
                    <p>Uploaded by: Ali<br>Ali's family gathering photo, a heartwarming moment shared with the MatchMingle community.</p>
                </div>
                <div class="user-media-item">
    <img src="images/user5.jpg" alt="user5.jpg">
    <h4>User Photo 4</h4>
    <p>Uploaded by: Sara<br>A beautiful candid from Sara's Mehendi night, capturing the joy and laughter shared with friends and family.</p>
</div>

<div class="user-media-item">
    <img src="images/user6.jpg" alt="user6.jpg">
    <h4>User Photo 5</h4>
    <p>Uploaded by: Rafi<br>Rafi’s Nikah ceremony, showcasing elegant traditional attire and heartfelt moments with loved ones.</p>
</div>

<div class="user-media-item">
    <img src="images/user7.jpg" alt="user7.jpg">
    <h4>User Photo 6</h4>
    <p>Uploaded by: Mehnaz<br>Mehnaz’s wedding stage decor — a dreamy floral setup that blended tradition and modern elegance perfectly.</p>
</div>

<div class="user-media-item">
    <img src="images/user8.jpg" alt="user8.jpg">
    <h4>User Photo 7</h4>
    <p>Uploaded by: Tanvir<br>A group photo from Tanvir’s Walima celebration, reflecting the unity of two families in joy and celebration.</p>
</div>

<div class="user-media-item">
    <img src="images/user9.jpg" alt="user9.jpg">
    <h4>User Photo 8</h4>
    <p>Uploaded by: Nusrat<br>A heartfelt moment as Nusrat shares a laugh with her bridesmaids on her big day — love and friendship combined.</p>
</div>

<div class="user-media-item">
    <img src="images/user10.jpg" alt="user10.jpg">
    <h4>User Photo 9</h4>
    <p>Uploaded by: Fahim<br>Fahim’s family portrait during the wedding reception — a memory full of pride, tradition, and celebration.</p>
</div>
<div class="user-media-item">
    <img src="images/user11.jpg" alt="user11.jpg">
    <h4>User Photo 10</h4>
    <p>Uploaded by: Ayesha<br>A radiant smile captured during Ayesha’s bridal entry — a timeless memory from her special day.</p>
</div>

<div class="user-media-item">
    <img src="images/user12.jpg" alt="user12.jpg">
    <h4>User Photo 11</h4>
    <p>Uploaded by: Nabil<br>Nabil’s musical night featuring close friends and family — an evening of laughter, music, and unforgettable memories.</p>
</div>

<div class="user-media-item">
    <img src="images/user13.jpg" alt="user13.jpg">
    <h4>User Photo 12</h4>
    <p>Uploaded by: Tamanna<br>A touching moment from Tamanna’s wedding reception as the newlyweds shared their first dance under soft lights.</p>
</div>

                
            </div>
            <br>

            <!-- Testimonials Section -->
            <h2 class="section-title">Testimonials</h2>
           

    <div class="testimonial-card">
        <p>"আমি অনেকদিন ধরে একজন দায়িত্বশীল জীবনসঙ্গী খুঁজছিলাম। MatchMingle-এর মাধ্যমে সহজেই যোগাযোগ করতে পেরেছি এবং পরিবারও খুব সন্তুষ্ট।"</p>
        <p>- Rakibul Hasan, Chattogram</p>
    </div>

    <div class="testimonial-card">
        <p>"এই সাইটে ব্যবহারকারীর ছবি, ইভেন্ট আপডেট এবং প্রোফাইল বিশ্লেষণ দেখে সত্যিই বিশ্বাসযোগ্য মনে হয়েছে। আমার বিয়ের পরিকল্পনার অনেক আইডিয়াও এখান থেকেই পেয়েছি।"</p>
        <p>- Farhana Akter, Rajshahi</p>
    </div>

    <div class="testimonial-card">
        <p>"আমি ও আমার স্ত্রী MatchMingle-এ পরিচিত হয়েছিলাম। আমাদের পরিবার মিলে ফেব্রুয়ারি ২০২৫-এ বিয়ে হয়। এই সাইট আমাদের জীবনের এক নতুন অধ্যায় শুরু করতে সাহায্য করেছে।"</p>
        <p>- Mamun Chowdhury, Sylhet</p>
    </div>

    <div class="testimonial-card">
        <p>"MatchMingle-এর কাস্টমার সার্ভিস ও সিকিউরিটি সিস্টেম দেখে আমি অভিভূত। আমার প্রোফাইল একদম সিরিয়াস ইউজারদের কাছে পৌঁছেছে।"</p>
        <p>- Sharmin Nahar, Barisal</p>
    </div>

    <div class="testimonial-card">
        <p>"অন্যান্য প্ল্যাটফর্মের চেয়ে MatchMingle বেশি পারিবারিক মূল্যবোধ ভিত্তিক। আমার পছন্দ ও পার্টনার পছন্দ মিলিয়ে অসাধারণ ম্যাচ পেয়েছি।"</p>
        <p>- Arif Mahmud, Khulna</p>
    </div>

    <div class="testimonial-card">
        <p>"আমি অনেক দ্বিধায় ছিলাম এই ধরণের ওয়েবসাইট ব্যবহার করতে। কিন্তু MatchMingle-এর সফল বিয়ের গল্পগুলো আমাকে সাহস দিয়েছে।"</p>
        <p>- Lima Khatun, Rangpur</p>
    </div>

    <div class="testimonial-card">
        <p>"আমার পরিবার MatchMingle-এর মাধ্যমে পাত্র খুঁজেছে এবং দুই পক্ষেই খুব সহজে যোগাযোগ করা গেছে। আমরা এখন বিয়ের প্রস্তুতিতে ব্যস্ত!"</p>
        <p>- Tanjila Hossain, Mymensingh</p>
    </div>

    <div class="testimonial-card">
        <p>"MatchMingle আমাকে আমার স্বপ্নের জীবনসঙ্গী খুঁজে দিয়েছে। আমি সত্যিই কৃতজ্ঞ এই দারুণ প্ল্যাটফর্মের জন্য।"</p>
        <p>- Riaz Ahmed, Cumilla</p>
    </div>

    <div class="testimonial-card">
        <p>"সৎ ও শিক্ষিত জীবনসঙ্গী খোঁজার জন্য MatchMingle ছিল আমার সেরা সিদ্ধান্ত। এই প্ল্যাটফর্ম আমার জীবনের মোড় ঘুরিয়ে দিয়েছে।"</p>
        <p>- Nazmun Nahar, Narayanganj</p>
    </div>

</div>

            </div>

            <!-- Upcoming Events Section -->
            <h2 class="section-title">Upcoming Events</h2>
            <div class="events">

    <!-- Original Events -->
    <div class="event-card">
        <h4>Matrimonial Celebration 2025</h4>
        <p>Date: July 15, 2025 | 6:00 PM</p>
        <p>Location: Grand Hall, Dhaka</p>
        <p>Join us for an unforgettable evening of love and connection, featuring live music, delicious cuisine, and opportunities to meet your match!</p>
        <a href="are-you-attending.php" class="btn">RSVP Now</a>
    </div>

    <div class="event-card">
        <h4>Love Connect Workshop</h4>
        <p>Date: August 10, 2025 | 3:00 PM</p>
        <p>Location: Online</p>
        <p>A virtual workshop with relationship experts, offering tips on communication, planning, and building a strong foundation for love.</p>
        <a href="are-you-attending.php" class="btn">RSVP Now</a>
    </div>

    <!-- New Events -->
    <div class="event-card">
        <h4>Bride & Groom Meetup – Sylhet</h4>
        <p>Date: September 2, 2025 | 4:00 PM</p>
        <p>Location: Sylhet Convention Center</p>
        <p>Meet other verified members in person! An afternoon of networking, culture, and connections.</p>
        <a href="are-you-attending.php" class="btn">RSVP Now</a>
    </div>

    <div class="event-card">
        <h4>Family Introduction Day</h4>
        <p>Date: September 15, 2025 | 11:00 AM</p>
        <p>Location: Rajshahi Club</p>
        <p>A formal day for families to interact and discuss proposals in a respectful and private environment.</p>
        <a href="are-you-attending.php" class="btn">RSVP Now</a>
    </div>

    <div class="event-card">
        <h4>MatchMingle Eid Reunion</h4>
        <p>Date: October 5, 2025 | 5:00 PM</p>
        <p>Location: Chattogram Garden Hall</p>
        <p>Celebrate Eid with the community! An event full of joy, bonding, and traditional food.</p>
        <a href="are-you-attending.php" class="btn">RSVP Now</a>
    </div>

    <div class="event-card">
        <h4>Online Counseling Session</h4>
        <p>Date: October 12, 2025 | 8:00 PM</p>
        <p>Location: Zoom (Link after RSVP)</p>
        <p>One-on-one sessions with certified marriage counselors to guide you through relationship readiness.</p>
        <a href="are-you-attending.php" class="btn">RSVP Now</a>
    </div>

    <div class="event-card">
        <h4>Success Story Showcase</h4>
        <p>Date: November 3, 2025 | 7:00 PM</p>
        <p>Location: Online</p>
        <p>Join this inspiring evening as successful couples from MatchMingle share their journey from first match to marriage.</p>
        <a href="are-you-attending.php" class="btn">RSVP Now</a>
    </div>

    <div class="event-card">
        <h4>Single’s Day Tea Party</h4>
        <p>Date: November 14, 2025 | 4:00 PM</p>
        <p>Location: The Tea Lounge, Barisal</p>
        <p>A cozy and fun gathering for singles to mingle over snacks, games, and friendly chats.</p>
        <a href="are-you-attending.php" class="btn">RSVP Now</a>
    </div>

    <div class="event-card">
        <h4>Pre-Marriage Workshop</h4>
        <p>Date: December 1, 2025 | 6:00 PM</p>
        <p>Location: Mymensingh Training Hall</p>
        <p>Gain practical guidance on managing expectations, in-laws, finances, and more before stepping into marriage.</p>
        <a href="are-you-attending.php" class="btn">RSVP Now</a>
    </div>

    <div class="event-card">
        <h4>Winter Matrimonial Fair</h4>
        <p>Date: December 20, 2025 | 10:00 AM</p>
        <p>Location: Rangpur Expo Center</p>
        <p>A festive full-day event with matchmaking booths, cultural performances, and expert sessions.</p>
        <a href="are-you-attending.php" class="btn">RSVP Now</a>
    </div>

    <div class="event-card">
        <h4>New Year Love Launch</h4>
        <p>Date: January 5, 2026 | 6:30 PM</p>
        <p>Location: Dhaka Riverside Club</p>
        <p>Start the year with love! Enjoy music, couple games, and a celebration of new connections and beginnings.</p>
        <a href="are-you-attending.php" class="btn">RSVP Now</a>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>